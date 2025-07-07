<?php
require_once 'includes/functions.php';
session_start();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Ejemplos de Seguridad CSRF</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .example { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .before { background-color: #f8d7da; border-color: #f5c6cb; }
        .after { background-color: #d4edda; border-color: #c3e6cb; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: 'Courier New', monospace; margin: 10px 0; }
        .highlight { background-color: #fff3cd; padding: 2px 4px; border-radius: 2px; }
        h1 { color: #333; text-align: center; }
        h2 { color: #666; border-bottom: 2px solid #eee; padding-bottom: 5px; }
        h3 { color: #888; }
    </style>
</head>
<body>";

echo "<h1>🔒 Guía de Implementación CSRF - Proyecto E-commerce</h1>";

echo "<div class='example'>";
echo "<h2>📋 Resumen de la Implementación</h2>";
echo "<p>Se ha implementado protección CSRF completa en el sistema de gestión de contenido PHP. Esta protección incluye:</p>";
echo "<ul>";
echo "<li><strong>Funciones CSRF:</strong> Generación, validación e invalidación de tokens</li>";
echo "<li><strong>Middleware de seguridad:</strong> Verificación automática de métodos HTTP y tokens</li>";
echo "<li><strong>Sanitización de datos:</strong> Limpieza automática de todas las entradas</li>";
echo "<li><strong>Sistema de roles:</strong> Control de acceso jerárquico</li>";
echo "<li><strong>Formularios protegidos:</strong> Todos los formularios críticos incluyen tokens CSRF</li>";
echo "</ul>";
echo "</div>";

echo "<div class='example before'>";
echo "<h2>❌ ANTES: Formulario Vulnerable</h2>";
echo "<h3>login.php (versión original insegura)</h3>";
echo "<div class='code'>";
echo htmlspecialchars('<?php
if ($_POST) {
    $email = $_POST["email"];     // ❌ Datos sin sanitizar
    $password = $_POST["password"];
    
    // ❌ Sin validación CSRF
    if (authenticate_user($email, $password)) {
        redirect("index.php");
    }
}
?>
<form method="POST" action="">  <!-- ❌ Sin token CSRF -->
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Iniciar Sesión</button>
</form>');
echo "</div>";
echo "<p><strong>Problemas:</strong></p>";
echo "<ul>";
echo "<li>❌ No hay token CSRF</li>";
echo "<li>❌ Datos no sanitizados</li>";
echo "<li>❌ Vulnerable a ataques de sitio cruzado</li>";
echo "</ul>";
echo "</div>";

echo "<div class='example after'>";
echo "<h2>✅ DESPUÉS: Formulario Protegido</h2>";
echo "<h3>login.php (versión segura implementada)</h3>";
echo "<div class='code'>";
echo htmlspecialchars('<?php
if ($_POST) {
    // ✅ Validar CSRF token
    if (!isset($_POST["csrf_token"]) || !validate_csrf_token($_POST["csrf_token"])) {
        flash_message("error", "Token de seguridad inválido. Por favor, intenta de nuevo.");
    } else {
        $email = sanitize_input($_POST["email"], "email");    // ✅ Datos sanitizados
        $password = $_POST["password"];
        
        if (empty($email) || empty($password)) {
            flash_message("error", "Por favor completa todos los campos");
        } else {
            if (authenticate_user($email, $password)) {
                invalidate_csrf_token();  // ✅ Invalidar token después de uso
                flash_message("success", "Bienvenido de vuelta!");
                redirect("index.php");
            } else {
                flash_message("error", "Email o contraseña incorrectos");
            }
        }
    }
}
?>
<form method="POST" action="">
    <?php echo csrf_field(); ?>  <!-- ✅ Token CSRF incluido -->
    <input type="email" name="email" required 
           value="<?php echo isset($_POST[\'email\']) ? htmlspecialchars($_POST[\'email\'], ENT_QUOTES, \'UTF-8\') : \'\'; ?>">
    <input type="password" name="password" required>
    <button type="submit">Iniciar Sesión</button>
</form>');
echo "</div>";
echo "<p><strong>Mejoras implementadas:</strong></p>";
echo "<ul>";
echo "<li>✅ Token CSRF generado y validado</li>";
echo "<li>✅ Datos sanitizados con funciones específicas</li>";
echo "<li>✅ Protección contra XSS en outputs</li>";
echo "<li>✅ Invalidación de token después de uso exitoso</li>";
echo "</ul>";
echo "</div>";

echo "<div class='example'>";
echo "<h2>🔧 Funciones CSRF Implementadas</h2>";
echo "<h3>1. Generación de Token</h3>";
echo "<div class='code'>";
echo htmlspecialchars('function generate_csrf_token() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Generar token único con datos del servidor y tiempo
    $token = bin2hex(random_bytes(32));
    $timestamp = time();
    
    // Almacenar en sesión con timestamp para expiración
    $_SESSION["csrf_token"] = $token;
    $_SESSION["csrf_token_time"] = $timestamp;
    
    return $token;
}');
echo "</div>";

echo "<h3>2. Validación de Token</h3>";
echo "<div class='code'>";
echo htmlspecialchars('function validate_csrf_token($token) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar que exista token en sesión
    if (!isset($_SESSION["csrf_token"]) || !isset($_SESSION["csrf_token_time"])) {
        return false;
    }
    
    // Verificar que el token no haya expirado (30 minutos)
    if (time() - $_SESSION["csrf_token_time"] > 1800) {
        unset($_SESSION["csrf_token"]);
        unset($_SESSION["csrf_token_time"]);
        return false;
    }
    
    // Verificar que el token coincida (resistente a timing attacks)
    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        return false;
    }
    
    return true;
}');
echo "</div>";

echo "<h3>3. Campo de Formulario</h3>";
echo "<div class='code'>";
echo htmlspecialchars('function csrf_field() {
    $token = generate_csrf_token();
    return \'<input type="hidden" name="csrf_token" value="\' . htmlspecialchars($token, ENT_QUOTES, \'UTF-8\') . \'">\';
}');
echo "</div>";
echo "</div>";

echo "<div class='example'>";
echo "<h2>🛡️ Middleware de Seguridad</h2>";
echo "<div class='code'>";
echo htmlspecialchars('function security_middleware($allowed_methods = [\'POST\']) {
    // Verificar método HTTP
    if (!in_array($_SERVER[\'REQUEST_METHOD\'], $allowed_methods)) {
        http_response_code(405);
        die(\'Método no permitido\');
    }
    
    // Para métodos POST, verificar CSRF
    if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {
        if (!isset($_POST[\'csrf_token\'])) {
            http_response_code(400);
            die(\'Token CSRF faltante\');
        }
        
        if (!validate_csrf_token($_POST[\'csrf_token\'])) {
            http_response_code(403);
            die(\'Token CSRF inválido o expirado\');
        }
    }
    
    return true;
}');
echo "</div>";
echo "<p><strong>Uso:</strong> Llamar al inicio de scripts que procesan formularios importantes.</p>";
echo "</div>";

echo "<div class='example'>";
echo "<h2>🧹 Sanitización de Datos</h2>";
echo "<div class='code'>";
echo htmlspecialchars('function sanitize_input($data, $type = \'string\') {
    if (is_array($data)) {
        return array_map(function($item) use ($type) {
            return sanitize_input($item, $type);
        }, $data);
    }
    
    switch ($type) {
        case \'email\':
            return filter_var($data, FILTER_SANITIZE_EMAIL);
        case \'url\':
            return filter_var($data, FILTER_SANITIZE_URL);
        case \'int\':
            return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case \'float\':
            return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case \'string\':
        default:
            return htmlspecialchars(trim($data), ENT_QUOTES, \'UTF-8\');
    }
}');
echo "</div>";
echo "<p><strong>Ejemplos de uso:</strong></p>";
echo "<ul>";
echo "<li><code>sanitize_input(\$_POST['email'], 'email')</code></li>";
echo "<li><code>sanitize_input(\$_POST['username'], 'string')</code></li>";
echo "<li><code>sanitize_input(\$_POST['price'], 'float')</code></li>";
echo "</ul>";
echo "</div>";

echo "<div class='example'>";
echo "<h2>🔐 Sistema de Roles y Permisos</h2>";
echo "<div class='code'>";
echo htmlspecialchars('function has_role($required_role) {
    if (!isset($_SESSION[\'role\'])) {
        return false;
    }
    
    $role_hierarchy = [
        \'admin\' => 3,
        \'moderator\' => 2,
        \'user\' => 1
    ];
    
    $user_level = $role_hierarchy[$_SESSION[\'role\']] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

function require_admin($required_role = \'admin\') {
    if (!isset($_SESSION[\'user_id\']) || !has_role($required_role)) {
        header(\'Location: /proyecto/login.php?error=access_denied\');
        exit;
    }
}');
echo "</div>";
echo "</div>";

echo "<div class='example'>";
echo "<h2>📝 Formularios Protegidos Implementados</h2>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'>";
echo "<th style='text-align: left; padding: 8px; border: 1px solid #ddd;'>Archivo</th>";
echo "<th style='text-align: left; padding: 8px; border: 1px solid #ddd;'>Descripción</th>";
echo "<th style='text-align: left; padding: 8px; border: 1px solid #ddd;'>Protecciones</th>";
echo "</tr>";

$protectedForms = [
    'login.php' => [
        'desc' => 'Formulario de inicio de sesión',
        'protections' => 'CSRF, Sanitización email, XSS'
    ],
    'register.php' => [
        'desc' => 'Formulario de registro de usuarios',
        'protections' => 'CSRF, Sanitización, Validación, XSS'
    ],
    'checkout.php' => [
        'desc' => 'Proceso de compra del e-commerce',
        'protections' => 'CSRF, Sanitización, Validación multi-paso'
    ],
    'admin/manage-users.php' => [
        'desc' => 'Gestión de usuarios (admin)',
        'protections' => 'CSRF, Control roles, Sanitización, POST forms'
    ],
    'process-checkout.php' => [
        'desc' => 'Procesamiento de pedidos',
        'protections' => 'CSRF, Transacciones DB, Sanitización'
    ]
];

foreach ($protectedForms as $file => $info) {
    echo "<tr>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'><code>$file</code></td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$info['desc']}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$info['protections']}</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

echo "<div class='example'>";
echo "<h2>⚡ Flujo de Seguridad en Acción</h2>";
echo "<h3>Ejemplo: Procesamiento de Login Seguro</h3>";
echo "<div class='code'>";
echo htmlspecialchars('1. Usuario accede a login.php
   ↓
2. Sistema genera token CSRF único
   ↓  
3. Token se incluye en formulario como campo hidden
   ↓
4. Usuario completa y envía formulario
   ↓
5. Script valida token CSRF
   ↓
6. Si token es válido → sanitizar datos de entrada
   ↓
7. Procesar autenticación con datos limpios
   ↓
8. Si login exitoso → invalidar token CSRF usado
   ↓
9. Redirigir a página principal');
echo "</div>";
echo "<p><strong>Puntos de falla bloqueados:</strong></p>";
echo "<ul>";
echo "<li>❌ Formularios sin token CSRF → Rechazados</li>";
echo "<li>❌ Tokens expirados (>30 min) → Rechazados</li>";
echo "<li>❌ Tokens falsos o modificados → Rechazados</li>";
echo "<li>❌ Datos no sanitizados → Limpiados automáticamente</li>";
echo "<li>❌ Acceso sin permisos → Redirigido a login</li>";
echo "</ul>";
echo "</div>";

echo "<div class='example after'>";
echo "<h2>🎯 Recomendaciones para Producción</h2>";
echo "<h3>Headers de Seguridad Adicionales</h3>";
echo "<div class='code'>";
echo htmlspecialchars('// Agregar a .htaccess o código PHP
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
Header always set Content-Security-Policy "default-src \'self\'"');
echo "</div>";

echo "<h3>Configuraciones Adicionales</h3>";
echo "<ul>";
echo "<li><strong>HTTPS obligatorio:</strong> Usar SSL/TLS en producción</li>";
echo "<li><strong>Rate limiting:</strong> Limitar intentos de login por IP</li>";
echo "<li><strong>Logging:</strong> Registrar intentos de ataques CSRF</li>";
echo "<li><strong>Backup de seguridad:</strong> Copias de respaldo automáticas</li>";
echo "<li><strong>Monitoring:</strong> Alertas de actividad sospechosa</li>";
echo "</ul>";

echo "<h3>Archivos de Testing Creados</h3>";
echo "<ul>";
echo "<li><code>test-csrf.php</code> - Test básico de funciones CSRF</li>";
echo "<li><code>test-csrf-security.php</code> - Test completo de seguridad</li>";
echo "<li><code>security-examples.php</code> - Esta guía de implementación</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;'>";
echo "<h2>✅ Implementación CSRF Completada</h2>";
echo "<p><strong>Estado del Proyecto:</strong> PROTEGIDO CONTRA ATAQUES CSRF</p>";
echo "<p>Todos los formularios críticos han sido actualizados con protección completa.</p>";
echo "<p><em>Fecha de implementación: " . date('d/m/Y H:i:s') . "</em></p>";
echo "</div>";

echo "</body></html>";
?>