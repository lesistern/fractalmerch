<?php
class OAuthManager {
    private $db;
    private $providers;
    
    public function __construct($database) {
        $this->db = $database;
        $this->initializeProviders();
    }
    
    private function initializeProviders() {
        $this->providers = [
            'google' => [
                'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
                'token_url' => 'https://oauth2.googleapis.com/token',
                'user_info_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
                'scope' => 'openid email profile'
            ],
            'facebook' => [
                'auth_url' => 'https://www.facebook.com/v18.0/dialog/oauth',
                'token_url' => 'https://graph.facebook.com/v18.0/oauth/access_token',
                'user_info_url' => 'https://graph.facebook.com/me',
                'scope' => 'email,public_profile'
            ],
            'github' => [
                'auth_url' => 'https://github.com/login/oauth/authorize',
                'token_url' => 'https://github.com/login/oauth/access_token',
                'user_info_url' => 'https://api.github.com/user',
                'scope' => 'user:email'
            ],
            'apple' => [
                'auth_url' => 'https://appleid.apple.com/auth/authorize',
                'token_url' => 'https://appleid.apple.com/auth/token',
                'user_info_url' => null, // Apple envía datos en el token
                'scope' => 'name email'
            ],
            'microsoft' => [
                'auth_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
                'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
                'user_info_url' => 'https://graph.microsoft.com/v1.0/me',
                'scope' => 'openid email profile'
            ]
        ];
    }
    
    public function getOAuthConfig($provider) {
        $stmt = $this->db->prepare("SELECT * FROM oauth_config WHERE provider = ? AND is_active = 1");
        $stmt->execute([$provider]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function generateAuthUrl($provider) {
        $config = $this->getOAuthConfig($provider);
        if (!$config || !isset($this->providers[$provider])) {
            throw new Exception("Proveedor OAuth no configurado: $provider");
        }
        
        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_provider'] = $provider;
        
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'scope' => $config['scope'],
            'response_type' => 'code',
            'state' => $state
        ];
        
        // Parámetros específicos por proveedor
        switch ($provider) {
            case 'google':
                $params['access_type'] = 'offline';
                $params['prompt'] = 'consent';
                break;
            case 'apple':
                $params['response_mode'] = 'form_post';
                break;
            case 'microsoft':
                $params['prompt'] = 'select_account';
                break;
        }
        
        return $this->providers[$provider]['auth_url'] . '?' . http_build_query($params);
    }
    
    public function exchangeCodeForToken($provider, $code, $state) {
        // Validar state para prevenir CSRF
        if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
            throw new Exception("Estado OAuth inválido");
        }
        
        if (!isset($_SESSION['oauth_provider']) || $_SESSION['oauth_provider'] !== $provider) {
            throw new Exception("Proveedor OAuth inconsistente");
        }
        
        $config = $this->getOAuthConfig($provider);
        if (!$config) {
            throw new Exception("Configuración OAuth no encontrada");
        }
        
        $data = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $config['redirect_uri']
        ];
        
        $response = $this->makeHttpRequest($this->providers[$provider]['token_url'], $data, 'POST');
        
        if (!$response) {
            throw new Exception("Error al obtener token de acceso");
        }
        
        return json_decode($response, true);
    }
    
    public function getUserInfo($provider, $accessToken) {
        if (!isset($this->providers[$provider]['user_info_url'])) {
            // Para Apple, la información viene en el JWT token
            return $this->decodeAppleJWT($accessToken);
        }
        
        $headers = ["Authorization: Bearer $accessToken"];
        
        // GitHub requiere User-Agent
        if ($provider === 'github') {
            $headers[] = "User-Agent: FractalMerch-OAuth";
        }
        
        $userInfoUrl = $this->providers[$provider]['user_info_url'];
        
        // Facebook requiere fields específicos
        if ($provider === 'facebook') {
            $userInfoUrl .= '?fields=id,name,email,picture.type(large)';
        }
        
        $response = $this->makeHttpRequest($userInfoUrl, null, 'GET', $headers);
        
        if (!$response) {
            throw new Exception("Error al obtener información del usuario");
        }
        
        return json_decode($response, true);
    }
    
    public function createOrUpdateUser($provider, $userInfo, $tokenData) {
        // Mapear información del usuario según el proveedor
        $userData = $this->mapUserData($provider, $userInfo);
        
        // Buscar usuario existente por OAuth ID o email
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE (oauth_provider = ? AND oauth_id = ?) 
            OR (email = ? AND oauth_provider IS NOT NULL)
        ");
        $stmt->execute([$provider, $userData['oauth_id'], $userData['email']]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            // Actualizar usuario existente
            $stmt = $this->db->prepare("
                UPDATE users SET 
                    name = ?, 
                    avatar_url = ?, 
                    oauth_token = ?, 
                    email_verified = 1,
                    last_login = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $userData['name'], 
                $userData['avatar_url'], 
                json_encode($tokenData),
                $existingUser['id']
            ]);
            
            $userId = $existingUser['id'];
        } else {
            // Crear nuevo usuario
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    name, email, oauth_provider, oauth_id, 
                    oauth_token, avatar_url, email_verified, 
                    account_type, role, created_at, last_login
                ) VALUES (?, ?, ?, ?, ?, ?, 1, 'oauth', 'user', NOW(), NOW())
            ");
            $stmt->execute([
                $userData['name'],
                $userData['email'],
                $provider,
                $userData['oauth_id'],
                json_encode($tokenData),
                $userData['avatar_url']
            ]);
            
            $userId = $this->db->lastInsertId();
        }
        
        // Guardar/actualizar tokens
        $this->saveTokens($userId, $provider, $tokenData);
        
        // Registrar intento de login exitoso
        $this->logLoginAttempt($userData['email'], $provider, true);
        
        return $userId;
    }
    
    private function mapUserData($provider, $userInfo) {
        switch ($provider) {
            case 'google':
                return [
                    'oauth_id' => $userInfo['id'],
                    'name' => $userInfo['name'],
                    'email' => $userInfo['email'],
                    'avatar_url' => $userInfo['picture'] ?? null
                ];
                
            case 'facebook':
                return [
                    'oauth_id' => $userInfo['id'],
                    'name' => $userInfo['name'],
                    'email' => $userInfo['email'] ?? null,
                    'avatar_url' => $userInfo['picture']['data']['url'] ?? null
                ];
                
            case 'github':
                return [
                    'oauth_id' => $userInfo['id'],
                    'name' => $userInfo['name'] ?? $userInfo['login'],
                    'email' => $userInfo['email'],
                    'avatar_url' => $userInfo['avatar_url'] ?? null
                ];
                
            case 'microsoft':
                return [
                    'oauth_id' => $userInfo['id'],
                    'name' => $userInfo['displayName'],
                    'email' => $userInfo['mail'] ?? $userInfo['userPrincipalName'],
                    'avatar_url' => null
                ];
                
            default:
                throw new Exception("Proveedor no soportado para mapeo de datos");
        }
    }
    
    private function saveTokens($userId, $provider, $tokenData) {
        $stmt = $this->db->prepare("
            INSERT INTO oauth_tokens (
                user_id, provider, access_token, refresh_token, 
                token_expires, scope, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                access_token = VALUES(access_token),
                refresh_token = VALUES(refresh_token),
                token_expires = VALUES(token_expires),
                scope = VALUES(scope),
                updated_at = NOW()
        ");
        
        $expires = null;
        if (isset($tokenData['expires_in'])) {
            $expires = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
        }
        
        $stmt->execute([
            $userId,
            $provider,
            $tokenData['access_token'],
            $tokenData['refresh_token'] ?? null,
            $expires,
            $tokenData['scope'] ?? null
        ]);
    }
    
    private function logLoginAttempt($email, $provider, $success, $error = null) {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (
                email, provider, ip_address, user_agent, success, error_message
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $email,
            $provider,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $success,
            $error
        ]);
    }
    
    private function makeHttpRequest($url, $data = null, $method = 'GET', $headers = []) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return $response;
        }
        
        return false;
    }
    
    private function decodeAppleJWT($token) {
        // Apple JWT decoding - implementación simplificada
        // En producción, usar una librería JWT como firebase/jwt
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception("Token Apple inválido");
        }
        
        $payload = json_decode(base64_decode($parts[1]), true);
        
        return [
            'id' => $payload['sub'],
            'email' => $payload['email'] ?? null,
            'name' => $payload['name'] ?? 'Usuario Apple'
        ];
    }
    
    public function getProviders() {
        $stmt = $this->db->prepare("SELECT provider, client_id FROM oauth_config WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function disconnectOAuth($userId, $provider) {
        $stmt = $this->db->prepare("
            UPDATE users SET 
                oauth_provider = NULL, 
                oauth_id = NULL, 
                oauth_token = NULL 
            WHERE id = ? AND oauth_provider = ?
        ");
        $stmt->execute([$userId, $provider]);
        
        $stmt = $this->db->prepare("DELETE FROM oauth_tokens WHERE user_id = ? AND provider = ?");
        $stmt->execute([$userId, $provider]);
        
        return $stmt->rowCount() > 0;
    }
}
?>