<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug oauth-login.php</h1>";
echo "<pre>";

// Simular el acceso a oauth-login.php con debug detallado
$_GET['provider'] = 'google';

echo "1. Verificando archivos...\n";

try {
    echo "Cargando database.php... ";
    require_once __DIR__ . '/config/database.php';
    echo "✅\n";
    
    echo "Cargando config.php... ";
    require_once __DIR__ . '/config/config.php';
    echo "✅\n";
    
    echo "Cargando oauth.php... ";
    require_once __DIR__ . '/config/oauth.php';
    echo "✅\n";
    
    echo "Cargando functions.php... ";
    require_once __DIR__ . '/includes/functions.php';
    echo "✅\n";
    
    echo "Cargando OAuthManager.php... ";
    require_once __DIR__ . '/includes/oauth/OAuthManager.php';
    echo "✅\n";
    
} catch (Exception $e) {
    echo "❌ Error cargando archivos: " . $e->getMessage() . "\n";
    exit;
}

echo "\n2. Verificando provider...\n";
$provider = $_GET['provider'] ?? '';
echo "Provider recibido: '$provider'\n";

if (!$provider) {
    echo "❌ Provider vacío - esto causaría redirect a login.php\n";
    exit;
} else {
    echo "✅ Provider válido\n";
}

echo "\n3. Verificando configuración OAuth...\n";

if (function_exists('getOAuthConfig')) {
    $config = getOAuthConfig($provider);
    if ($config) {
        echo "✅ Configuración encontrada\n";
        echo "Enabled: " . ($config['enabled'] ? 'SÍ' : 'NO') . "\n";
        echo "Client ID: " . substr($config['client_id'], 0, 20) . "...\n";
    } else {
        echo "❌ Configuración no encontrada o deshabilitada\n";
        echo "Esto causaría que OAuthManager falle\n";
    }
} else {
    echo "❌ Función getOAuthConfig no existe\n";
}

echo "\n4. Probando OAuthManager...\n";

try {
    echo "Instanciando OAuthManager... ";
    $oauth = new OAuthManager($pdo);
    echo "✅\n";
    
    echo "Generando URL de autenticación... ";
    $authUrl = $oauth->generateAuthUrl($provider);
    echo "✅\n";
    
    echo "URL generada: $authUrl\n";
    
    if ($authUrl && strpos($authUrl, 'accounts.google.com') !== false) {
        echo "✅ URL válida de Google OAuth\n";
        echo "\n5. La redirección debería funcionar:\n";
        echo "header('Location: $authUrl');\n";
    } else {
        echo "❌ URL inválida o vacía\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en OAuthManager: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    echo "\nEsto causaría redirect a login.php con mensaje de error\n";
}

echo "</pre>";

echo "<h2>Solución:</h2>";
if (isset($authUrl) && $authUrl) {
    echo "<p>✅ El OAuth debería funcionar. <a href='$authUrl'>Probar URL generada</a></p>";
} else {
    echo "<p>❌ Hay un problema en la generación de URL. Revisar configuración.</p>";
}

echo "<p><a href='login.php'>← Volver al login</a></p>";
?>