<?php
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/oauth/OAuthManager.php';

$provider = $_GET['provider'] ?? '';

if (!$provider) {
    flash_message('error', 'Proveedor OAuth no especificado');
    redirect('login.php');
}

try {
    $oauth = new OAuthManager($pdo);
    $authUrl = $oauth->generateAuthUrl($provider);
    header('Location: ' . $authUrl);
    exit;
} catch (Exception $e) {
    error_log("OAuth Error: " . $e->getMessage());
    flash_message('error', 'Error al inicializar autenticación con ' . ucfirst($provider));
    redirect('login.php');
}
?>