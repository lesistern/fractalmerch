<?php
// Usar rutas absolutas basadas en __DIR__
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/oauth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/oauth/OAuthManager.php';

$provider = $_GET['provider'] ?? '';
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';
$error = $_GET['error'] ?? '';

// Manejar errores del proveedor OAuth
if ($error) {
    $errorMessages = [
        'access_denied' => 'Acceso denegado por el usuario',
        'invalid_request' => 'Solicitud inválida',
        'unauthorized_client' => 'Cliente no autorizado',
        'unsupported_response_type' => 'Tipo de respuesta no soportado',
        'invalid_scope' => 'Scope inválido',
        'server_error' => 'Error del servidor',
        'temporarily_unavailable' => 'Servicio temporalmente no disponible'
    ];
    
    $message = $errorMessages[$error] ?? 'Error desconocido en la autenticación';
    flash_message('error', $message);
    redirect('../login.php');
}

if (!$provider || !$code) {
    flash_message('error', 'Parámetros OAuth incompletos');
    redirect('../login.php');
}

try {
    $oauth = new OAuthManager($pdo);
    
    // Intercambiar código por token
    $tokenData = $oauth->exchangeCodeForToken($provider, $code, $state);
    
    if (!isset($tokenData['access_token'])) {
        throw new Exception('No se pudo obtener el token de acceso');
    }
    
    // Obtener información del usuario
    $userInfo = $oauth->getUserInfo($provider, $tokenData['access_token']);
    
    if (!$userInfo) {
        throw new Exception('No se pudo obtener la información del usuario');
    }
    
    // Crear o actualizar usuario
    $userId = $oauth->createOrUpdateUser($provider, $userInfo, $tokenData);
    
    // Obtener datos completos del usuario
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('Error al recuperar datos del usuario');
    }
    
    // Iniciar sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['oauth_provider'] = $provider;
    $_SESSION['avatar_url'] = $user['avatar_url'];
    
    // Limpiar variables temporales de OAuth
    unset($_SESSION['oauth_state']);
    unset($_SESSION['oauth_provider']);
    
    // Mensaje de bienvenida
    $welcomeMessage = $user['last_login'] ? 
        "¡Bienvenido de vuelta, {$user['name']}!" : 
        "¡Bienvenido a FractalMerch, {$user['name']}!";
    
    flash_message('success', $welcomeMessage);
    
    // Redirección según el rol
    $redirectUrl = '../index.php';
    if ($user['role'] === 'admin') {
        $redirectUrl = '../admin/dashboard.php';
    } elseif (isset($_SESSION['redirect_after_login'])) {
        $redirectUrl = '../' . $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
    }
    
    redirect($redirectUrl);
    
} catch (Exception $e) {
    error_log("OAuth Callback Error ({$provider}): " . $e->getMessage());
    
    $errorMessage = "Error en la autenticación con " . ucfirst($provider) . ": " . $e->getMessage();
    flash_message('error', $errorMessage);
    redirect('../login.php');
}
?>