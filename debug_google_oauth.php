<?php
echo "<h1>Debug Google OAuth</h1>";
echo "<pre>";

try {
    echo "1. Cargando archivos de configuración...\n";
    
    require_once 'config/database.php';
    echo "✅ database.php cargado\n";
    
    require_once 'config/config.php';
    echo "✅ config.php cargado\n";
    
    require_once 'config/oauth.php';
    echo "✅ oauth.php cargado\n";
    
    require_once 'includes/functions.php';
    echo "✅ functions.php cargado\n";
    
    require_once 'includes/oauth/OAuthManager.php';
    echo "✅ OAuthManager.php cargado\n";
    
    echo "\n2. Verificando configuración Google...\n";
    
    if (function_exists('getOAuthConfig')) {
        $googleConfig = getOAuthConfig('google');
        if ($googleConfig) {
            echo "✅ Configuración Google encontrada\n";
            echo "Client ID: " . substr($googleConfig['client_id'], 0, 30) . "...\n";
            echo "Client Secret: " . substr($googleConfig['client_secret'], 0, 15) . "...\n";
            echo "Enabled: " . ($googleConfig['enabled'] ? 'SÍ' : 'NO') . "\n";
            echo "Redirect URI: " . $googleConfig['redirect_uri'] . "\n";
        } else {
            echo "❌ Configuración Google no disponible\n";
            echo "Verificando oauth_config global...\n";
            global $oauth_config;
            print_r($oauth_config['google']);
        }
    } else {
        echo "❌ Función getOAuthConfig no existe\n";
    }
    
    echo "\n3. Probando OAuthManager...\n";
    
    if (class_exists('OAuthManager')) {
        echo "✅ Clase OAuthManager existe\n";
        
        $oauth = new OAuthManager($pdo);
        echo "✅ OAuthManager instanciado\n";
        
        echo "\n4. Generando URL de autenticación...\n";
        
        $authUrl = $oauth->generateAuthUrl('google');
        
        if ($authUrl) {
            echo "✅ URL generada exitosamente:\n";
            echo "URL: $authUrl\n\n";
            
            echo "5. Analizando URL:\n";
            $parsedUrl = parse_url($authUrl);
            echo "Host: " . $parsedUrl['host'] . "\n";
            echo "Path: " . $parsedUrl['path'] . "\n";
            
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $params);
                echo "Parámetros:\n";
                foreach ($params as $key => $value) {
                    echo "  $key: " . substr($value, 0, 50) . (strlen($value) > 50 ? '...' : '') . "\n";
                }
            }
            
            echo "\n6. Test directo:\n";
            echo "<a href='$authUrl' target='_blank' style='background: #4285f4; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔗 PROBAR GOOGLE OAUTH</a>\n";
            
        } else {
            echo "❌ No se pudo generar URL de autenticación\n";
        }
        
    } else {
        echo "❌ Clase OAuthManager no existe\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n\n7. Test del enlace actual en login.php:\n";
echo "<a href='auth/oauth-login.php?provider=google' style='background: #4285f4; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔗 PROBAR ENLACE ACTUAL</a>\n";

echo "</pre>";
echo "<p><a href='login.php'>← Volver al login</a></p>";
?>