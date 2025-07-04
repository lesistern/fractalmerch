<?php
class OAuthSecurity {
    private $db;
    private $config;
    
    public function __construct($database, $config = []) {
        $this->db = $database;
        $this->config = array_merge([
            'rate_limit_attempts' => 5,
            'rate_limit_window' => 300, // 5 minutos
            'state_lifetime' => 600,    // 10 minutos
            'max_failed_attempts' => 10,
            'lockout_duration' => 900   // 15 minutos
        ], $config);
    }
    
    /**
     * Validar rate limiting para OAuth
     */
    public function checkRateLimit($identifier, $action = 'oauth_attempt') {
        $ip = $this->getClientIP();
        $window_start = date('Y-m-d H:i:s', time() - $this->config['rate_limit_window']);
        
        // Contar intentos en la ventana de tiempo
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as attempts 
            FROM login_attempts 
            WHERE (ip_address = ? OR email = ?) 
            AND created_at > ? 
            AND success = 0
        ");
        $stmt->execute([$ip, $identifier, $window_start]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['attempts'] >= $this->config['rate_limit_attempts']) {
            $this->logSecurityEvent('rate_limit_exceeded', [
                'ip' => $ip,
                'identifier' => $identifier,
                'attempts' => $result['attempts']
            ]);
            
            throw new Exception("Demasiados intentos. Intenta nuevamente en " . 
                               ($this->config['rate_limit_window'] / 60) . " minutos.");
        }
        
        return true;
    }
    
    /**
     * Generar state token seguro para CSRF protection
     */
    public function generateSecureState($provider) {
        $state = [
            'token' => bin2hex(random_bytes(32)),
            'provider' => $provider,
            'timestamp' => time(),
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Almacenar en sesión con timestamp
        $_SESSION['oauth_states'][$state['token']] = $state;
        
        // Limpiar states expirados
        $this->cleanExpiredStates();
        
        return $state['token'];
    }
    
    /**
     * Validar state token
     */
    public function validateState($token, $provider) {
        if (!isset($_SESSION['oauth_states'][$token])) {
            $this->logSecurityEvent('invalid_state_token', [
                'token' => substr($token, 0, 8) . '...',
                'provider' => $provider
            ]);
            throw new Exception("Token de estado inválido");
        }
        
        $state = $_SESSION['oauth_states'][$token];
        
        // Validar timestamp
        if (time() - $state['timestamp'] > $this->config['state_lifetime']) {
            unset($_SESSION['oauth_states'][$token]);
            throw new Exception("Token de estado expirado");
        }
        
        // Validar proveedor
        if ($state['provider'] !== $provider) {
            $this->logSecurityEvent('provider_mismatch', [
                'expected' => $provider,
                'received' => $state['provider']
            ]);
            throw new Exception("Proveedor OAuth inconsistente");
        }
        
        // Validar IP (opcional, puede causar problemas con proxies)
        if ($this->config['strict_ip_validation'] ?? false) {
            if ($state['ip'] !== $this->getClientIP()) {
                $this->logSecurityEvent('ip_mismatch', [
                    'original_ip' => $state['ip'],
                    'current_ip' => $this->getClientIP()
                ]);
                throw new Exception("Validación de IP fallida");
            }
        }
        
        // Limpiar state usado
        unset($_SESSION['oauth_states'][$token]);
        
        return true;
    }
    
    /**
     * Validar redirect URI
     */
    public function validateRedirectURI($uri, $allowedDomains = []) {
        $parsed = parse_url($uri);
        
        if (!$parsed || !isset($parsed['host'])) {
            throw new Exception("URI de redirección inválida");
        }
        
        // Validar esquema
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            throw new Exception("Esquema de URI no permitido");
        }
        
        // Validar dominio
        if (!empty($allowedDomains) && !in_array($parsed['host'], $allowedDomains)) {
            $this->logSecurityEvent('invalid_redirect_domain', [
                'attempted_domain' => $parsed['host'],
                'allowed_domains' => $allowedDomains
            ]);
            throw new Exception("Dominio de redirección no autorizado");
        }
        
        return true;
    }
    
    /**
     * Sanitizar datos de usuario OAuth
     */
    public function sanitizeUserData($data, $provider) {
        $sanitized = [];
        
        // Campos permitidos por proveedor
        $allowedFields = [
            'google' => ['id', 'name', 'email', 'picture', 'verified_email'],
            'facebook' => ['id', 'name', 'email', 'picture'],
            'github' => ['id', 'login', 'name', 'email', 'avatar_url'],
            'apple' => ['sub', 'email', 'name'],
            'microsoft' => ['id', 'displayName', 'mail', 'userPrincipalName']
        ];
        
        $fields = $allowedFields[$provider] ?? [];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = $this->sanitizeValue($data[$field]);
            }
        }
        
        // Validaciones específicas
        if (isset($sanitized['email'])) {
            if (!filter_var($sanitized['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email inválido recibido del proveedor OAuth");
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Detectar intentos de account takeover
     */
    public function detectSuspiciousActivity($email, $provider, $userInfo) {
        $suspicious = false;
        $reasons = [];
        
        // Verificar si el email ya existe con otro proveedor
        $stmt = $this->db->prepare("
            SELECT oauth_provider, last_login, created_at 
            FROM users 
            WHERE email = ? AND oauth_provider != ?
        ");
        $stmt->execute([$email, $provider]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            $suspicious = true;
            $reasons[] = "Email ya registrado con " . $existingUser['oauth_provider'];
        }
        
        // Verificar múltiples intentos desde la misma IP
        $recentAttempts = $this->getRecentAttempts($this->getClientIP(), 300); // 5 minutos
        if ($recentAttempts > 3) {
            $suspicious = true;
            $reasons[] = "Múltiples intentos desde la misma IP";
        }
        
        // Verificar cambios en información básica del usuario
        if ($existingUser) {
            $stmt = $this->db->prepare("SELECT name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $storedUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($storedUser && isset($userInfo['name'])) {
                $similarity = similar_text(
                    strtolower($storedUser['name']), 
                    strtolower($userInfo['name'])
                );
                
                if ($similarity < 0.7) { // Menos del 70% de similitud
                    $suspicious = true;
                    $reasons[] = "Cambio significativo en el nombre del usuario";
                }
            }
        }
        
        if ($suspicious) {
            $this->logSecurityEvent('suspicious_oauth_activity', [
                'email' => $email,
                'provider' => $provider,
                'reasons' => $reasons,
                'user_info' => array_intersect_key($userInfo, array_flip(['name', 'id']))
            ]);
            
            // Enviar notificación de seguridad (implementar según necesidades)
            $this->sendSecurityAlert($email, $reasons);
        }
        
        return ['suspicious' => $suspicious, 'reasons' => $reasons];
    }
    
    /**
     * Limpiar states expirados
     */
    private function cleanExpiredStates() {
        if (!isset($_SESSION['oauth_states'])) {
            return;
        }
        
        $currentTime = time();
        foreach ($_SESSION['oauth_states'] as $token => $state) {
            if ($currentTime - $state['timestamp'] > $this->config['state_lifetime']) {
                unset($_SESSION['oauth_states'][$token]);
            }
        }
    }
    
    /**
     * Obtener IP del cliente (considerando proxies)
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Tomar la primera IP si hay múltiples
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Sanitizar valor individual
     */
    private function sanitizeValue($value) {
        if (is_string($value)) {
            return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }
    
    /**
     * Obtener intentos recientes desde una IP
     */
    private function getRecentAttempts($ip, $window) {
        $windowStart = date('Y-m-d H:i:s', time() - $window);
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as attempts 
            FROM login_attempts 
            WHERE ip_address = ? AND created_at > ?
        ");
        $stmt->execute([$ip, $windowStart]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['attempts'] ?? 0;
    }
    
    /**
     * Registrar evento de seguridad
     */
    private function logSecurityEvent($event, $details = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_attempts (
                    email, provider, ip_address, user_agent, 
                    success, error_message, created_at
                ) VALUES (?, ?, ?, ?, 0, ?, NOW())
            ");
            
            $stmt->execute([
                $details['email'] ?? 'security_event',
                $event,
                $this->getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                json_encode($details)
            ]);
        } catch (Exception $e) {
            error_log("Failed to log security event: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar alerta de seguridad
     */
    private function sendSecurityAlert($email, $reasons) {
        // Implementar según las necesidades:
        // - Email notification
        // - Slack webhook
        // - Log centralizado
        // - etc.
        
        error_log("SECURITY ALERT - OAuth suspicious activity for {$email}: " . implode(', ', $reasons));
    }
    
    /**
     * Generar hash seguro para tokens
     */
    public function generateSecureHash($data) {
        return hash_hmac('sha256', $data, $_ENV['APP_KEY'] ?? 'fallback_key');
    }
    
    /**
     * Validar integridad de token
     */
    public function validateTokenIntegrity($token, $expectedHash) {
        return hash_equals($expectedHash, $this->generateSecureHash($token));
    }
}
?>