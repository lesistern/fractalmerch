<?php
echo "<h1>Test OAuth Debug</h1>";

try {
    echo "<p>1. Verificando archivos...</p>";
    
    if (file_exists('config/database.php')) {
        echo "✅ config/database.php existe<br>";
        require_once 'config/database.php';
    } else {
        echo "❌ config/database.php NO existe<br>";
    }
    
    if (file_exists('config/config.php')) {
        echo "✅ config/config.php existe<br>";
        require_once 'config/config.php';
    } else {
        echo "❌ config/config.php NO existe<br>";
    }
    
    if (file_exists('config/oauth.php')) {
        echo "✅ config/oauth.php existe<br>";
        require_once 'config/oauth.php';
    } else {
        echo "❌ config/oauth.php NO existe<br>";
    }
    
    if (file_exists('includes/functions.php')) {
        echo "✅ includes/functions.php existe<br>";
        require_once 'includes/functions.php';
    } else {
        echo "❌ includes/functions.php NO existe<br>";
    }
    
    if (file_exists('includes/oauth/OAuthManager.php')) {
        echo "✅ includes/oauth/OAuthManager.php existe<br>";
        require_once 'includes/oauth/OAuthManager.php';
    } else {
        echo "❌ includes/oauth/OAuthManager.php NO existe<br>";
    }
    
    echo "<p>2. Verificando configuración OAuth...</p>";
    
    if (function_exists('getOAuthConfig')) {
        $googleConfig = getOAuthConfig('google');
        if ($googleConfig) {
            echo "✅ Configuración Google OAuth cargada<br>";
            echo "Client ID: " . substr($googleConfig['client_id'], 0, 20) . "...<br>";
        } else {
            echo "❌ Configuración Google OAuth no disponible<br>";
        }
    } else {
        echo "❌ Función getOAuthConfig no existe<br>";
    }
    
    echo "<p>3. Probando OAuthManager...</p>";
    
    if (class_exists('OAuthManager')) {
        echo "✅ Clase OAuthManager existe<br>";
        $oauth = new OAuthManager($pdo);
        echo "✅ OAuthManager instanciado correctamente<br>";
        
        $authUrl = $oauth->generateAuthUrl('google');
        echo "✅ URL de autenticación generada: <br>";
        echo "<a href='$authUrl' target='_blank'>$authUrl</a><br>";
        
    } else {
        echo "❌ Clase OAuthManager no existe<br>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p><a href='login.php'>← Volver al login</a></p>";
?>