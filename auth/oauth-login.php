<?php
// Usar rutas absolutas basadas en __DIR__
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/oauth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/oauth/OAuthManager.php';

$provider = $_GET['provider'] ?? '';

if (!$provider) {
    flash_message('error', 'Proveedor OAuth no especificado');
    header('Location: ../login.php');
    exit;
}

try {
    $oauth = new OAuthManager($pdo);
    $authUrl = $oauth->generateAuthUrl($provider);
    header('Location: ' . $authUrl);
    exit;
} catch (Exception $e) {
    error_log("OAuth Error: " . $e->getMessage());
    flash_message('error', 'Error al inicializar autenticación con ' . ucfirst($provider));
    header('Location: ../login.php');
    exit;
}
?>