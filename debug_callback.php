<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug OAuth Callback</h1>";
echo "<pre>";

echo "1. Parámetros recibidos:\n";
foreach ($_GET as $key => $value) {
    echo "$key: " . htmlspecialchars($value) . "\n";
}

echo "\n2. Parámetros de sesión:\n";
foreach ($_SESSION as $key => $value) {
    if (strpos($key, 'oauth') !== false) {
        echo "$key: " . htmlspecialchars($value) . "\n";
    }
}

echo "\n3. Simulando oauth-callback.php...\n";

$provider = $_GET['provider'] ?? '';
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';
$error = $_GET['error'] ?? '';

echo "Provider: $provider\n";
echo "Code: " . substr($code, 0, 20) . "...\n";
echo "State: " . substr($state, 0, 20) . "...\n";
echo "Error: $error\n";

if ($error) {
    echo "❌ Hay un error OAuth: $error\n";
    exit;
}

if (!$provider || !$code) {
    echo "❌ Faltan parámetros OAuth\n";
    exit;
}

try {
    echo "\n4. Cargando archivos...\n";
    require_once __DIR__ . '/config/database.php';
    echo "✅ database.php\n";
    
    require_once __DIR__ . '/config/config.php';
    echo "✅ config.php\n";
    
    require_once __DIR__ . '/config/oauth.php';
    echo "✅ oauth.php\n";
    
    require_once __DIR__ . '/includes/functions.php';
    echo "✅ functions.php\n";
    
    require_once __DIR__ . '/includes/oauth/OAuthManager.php';
    echo "✅ OAuthManager.php\n";
    
    echo "\n5. Probando OAuthManager...\n";
    
    $oauth = new OAuthManager($pdo);
    echo "✅ OAuthManager instanciado\n";
    
    // Ver si existe el método exchangeCodeForToken
    if (method_exists($oauth, 'exchangeCodeForToken')) {
        echo "✅ Método exchangeCodeForToken existe\n";
        
        echo "\n6. Intentando intercambiar código por token...\n";
        $tokenData = $oauth->exchangeCodeForToken($provider, $code, $state);
        
        if ($tokenData && isset($tokenData['access_token'])) {
            echo "✅ Token obtenido: " . substr($tokenData['access_token'], 0, 20) . "...\n";
            
            echo "\n7. Obteniendo información del usuario...\n";
            $userInfo = $oauth->getUserInfo($provider, $tokenData['access_token']);
            
            if ($userInfo) {
                echo "✅ Información del usuario obtenida:\n";
                echo "Email: " . ($userInfo['email'] ?? 'No disponible') . "\n";
                echo "Nombre: " . ($userInfo['name'] ?? 'No disponible') . "\n";
            } else {
                echo "❌ No se pudo obtener información del usuario\n";
            }
            
        } else {
            echo "❌ No se pudo obtener token de acceso\n";
        }
        
    } else {
        echo "❌ Método exchangeCodeForToken no existe\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";

echo "<p><a href='login.php'>← Volver al login</a></p>";
?>