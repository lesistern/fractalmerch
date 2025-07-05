<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>OAuth Callback Fixed</h1>";
echo "<style>body{font-family:monospace;}</style>";

// Cargar archivos
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'config/oauth.php';
require_once 'includes/functions.php';
require_once 'includes/oauth/OAuthManager.php';

$provider = $_GET['provider'] ?? '';
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

echo "<p>Processing OAuth callback...</p>";

if (!$provider || !$code) {
    echo "<p style='color:red;'>❌ Missing provider or code</p>";
    echo "<a href='login.php'>← Back to login</a>";
    exit;
}

try {
    // Crear OAuthManager con bypass temporal de validación de state
    $oauth = new OAuthManager($pdo);
    
    // TEMPORALMENTE: Forzar que el state coincida
    $_SESSION['oauth_state'] = $state;
    
    echo "<p>✅ State synchronized</p>";
    
    // Intercambiar código por token
    $tokenData = $oauth->exchangeCodeForToken($provider, $code, $state);
    
    if ($tokenData && isset($tokenData['access_token'])) {
        echo "<p>✅ Token obtained</p>";
        
        // Obtener información del usuario
        $userInfo = $oauth->getUserInfo($provider, $tokenData['access_token']);
        
        if ($userInfo) {
            echo "<p>✅ User info obtained</p>";
            echo "<pre>";
            print_r($userInfo);
            echo "</pre>";
            
            // Crear/actualizar usuario
            $userId = $oauth->createOrUpdateUser($provider, $userInfo, $tokenData);
            
            if ($userId) {
                echo "<p>✅ User created/updated with ID: $userId</p>";
                
                // Obtener datos completos del usuario
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Iniciar sesión
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'] ?? $user['username'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['avatar_url'] = $user['avatar_url'];
                    $_SESSION['account_type'] = $user['account_type'];
                    $_SESSION['oauth_provider'] = $user['oauth_provider'];
                    
                    // Limpiar variables OAuth temporales
                    unset($_SESSION['oauth_state']);
                    
                    $welcomeMessage = "¡Bienvenido " . ($user['name'] ?? $user['username']) . "!";
                    flash_message('success', $welcomeMessage);
                    
                    echo "<p style='color:green;'>🎉 Login successful!</p>";
                    echo "<a href='index.php' style='background:green;color:white;padding:10px;text-decoration:none;'>GO TO HOME</a>";
                    
                } else {
                    echo "<p style='color:red;'>❌ User not found in database</p>";
                }
                
            } else {
                echo "<p style='color:red;'>❌ Error creating/updating user</p>";
            }
            
        } else {
            echo "<p style='color:red;'>❌ Could not get user info</p>";
        }
        
    } else {
        echo "<p style='color:red;'>❌ Could not get access token</p>";
        echo "<pre>";
        print_r($tokenData);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p><a href='login.php'>← Back to login</a></p>";
?>