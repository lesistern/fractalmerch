<?php
session_start();

define('SITE_NAME', 'Mi Proyecto Web');

// Detectar automáticamente la URL base para funcionar en LAN
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path = dirname($_SERVER['PHP_SELF']);
if ($path === '/' || $path === '\\') {
    $path = '';
}
define('SITE_URL', $protocol . $host . $path . '/');

define('ADMIN_EMAIL', 'admin@proyecto.com');

define('POSTS_PER_PAGE', 10);
define('COMMENTS_PER_PAGE', 20);

define('UPLOAD_DIR', 'assets/images/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

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
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash_messages() {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}
?>