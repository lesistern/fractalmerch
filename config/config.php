<?php
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_NAME', 'FractalMerch');

// Detectar automáticamente la URL base para funcionar en LAN
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path = dirname($_SERVER['PHP_SELF']);
if ($path === '/' || $path === '\\') {
    $path = '';
}
define('SITE_URL', $protocol . $host . $path . '/');

define('ADMIN_EMAIL', 'admin@fractalmerch.com.ar');

// Configuración de dominio para producción
define('PRODUCTION_DOMAIN', 'fractalmerch.com.ar');
define('PRODUCTION_URL', 'https://fractalmerch.com.ar/');

define('POSTS_PER_PAGE', 10);
define('COMMENTS_PER_PAGE', 20);

define('UPLOAD_DIR', 'assets/images/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Configuración de email - Zoho Mail
define('SMTP_HOST', 'smtp.zoho.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'admin@fractalmerch.com.ar'); // Tu cuenta principal
define('SMTP_PASSWORD', 'TU_PASSWORD_ZOHO');
define('FROM_EMAIL', 'noreply@fractalmerch.com.ar');
define('FROM_NAME', 'FractalMerch');

// Funciones de sanitización ya están definidas en includes/functions.php

function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_moderator() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'moderator' || $_SESSION['role'] === 'admin');
}

function flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash_messages() {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// Funciones CSRF ya están definidas en includes/functions.php
// Las funciones CSRF (generate_csrf_token, validate_csrf_token, csrf_field, invalidate_csrf_token) 
// están implementadas en includes/functions.php con validación de seguridad avanzada

// Función json_response() ya está definida en includes/functions.php
// La función incluye headers de seguridad avanzados para respuestas JSON

// NOTA: La función check_rate_limit() ya está definida en includes/functions.php
// con funcionalidad completa incluyendo logs de seguridad

// Función para validar direcciones de email de forma más estricta
function validate_email_domain($email) {
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, "MX");
}

// Función para limpiar arrays recursivamente
function sanitize_array($array) {
    $clean = [];
    foreach ($array as $key => $value) {
        $clean_key = sanitize_input($key);
        if (is_array($value)) {
            $clean[$clean_key] = sanitize_array($value);
        } else {
            $clean[$clean_key] = sanitize_input($value);
        }
    }
    return $clean;
}
?>