<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test OAuth Process Step by Step</h1>";
echo "<style>body{font-family:monospace;} .success{color:green;} .error{color:red;}</style>";

// Cargar archivos
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'config/oauth.php';
require_once 'includes/functions.php';
require_once 'includes/oauth/OAuthManager.php';

$provider = $_GET['provider'] ?? 'google';
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

echo "<h2>Parámetros recibidos:</h2>";
echo "<pre>";
echo "Provider: $provider\n";
echo "Code: " . substr($code, 0, 30) . "...\n";
echo "State: " . substr($state, 0, 30) . "...\n";
echo "</pre>";

try {
    echo "<h2>Paso 1: Instanciar OAuthManager</h2>";
    $oauth = new OAuthManager($pdo);
    echo "<span class='success'>✅ OAuthManager creado</span><br><br>";
    
    echo "<h2>Paso 2: Verificar configuración</h2>";
    $config = getOAuthConfig($provider);
    if ($config) {
        echo "<span class='success'>✅ Configuración encontrada</span><br>";
        echo "Enabled: " . ($config['enabled'] ? 'SÍ' : 'NO') . "<br>";
        echo "Client ID: " . substr($config['client_id'], 0, 20) . "...<br><br>";
    } else {
        echo "<span class='error'>❌ No hay configuración para $provider</span><br><br>";
    }
    
    if ($code) {
        echo "<h2>Paso 3: Intercambiar código por token</h2>";
        
        // Verificar si el método existe
        if (method_exists($oauth, 'exchangeCodeForToken')) {
            echo "<span class='success'>✅ Método exchangeCodeForToken existe</span><br>";
            
            try {
                $tokenData = $oauth->exchangeCodeForToken($provider, $code, $state);
                
                if ($tokenData && isset($tokenData['access_token'])) {
                    echo "<span class='success'>✅ Token obtenido exitosamente</span><br>";
                    echo "Access Token: " . substr($tokenData['access_token'], 0, 20) . "...<br><br>";
                    
                    echo "<h2>Paso 4: Obtener información del usuario</h2>";
                    
                    if (method_exists($oauth, 'getUserInfo')) {
                        echo "<span class='success'>✅ Método getUserInfo existe</span><br>";
                        
                        try {
                            $userInfo = $oauth->getUserInfo($provider, $tokenData['access_token']);
                            
                            if ($userInfo) {
                                echo "<span class='success'>✅ Información del usuario obtenida</span><br>";
                                echo "<pre>";
                                print_r($userInfo);
                                echo "</pre>";
                                
                                echo "<h2>Paso 5: Crear/actualizar usuario</h2>";
                                
                                if (method_exists($oauth, 'createOrUpdateUser')) {
                                    echo "<span class='success'>✅ Método createOrUpdateUser existe</span><br>";
                                    
                                    try {
                                        $userId = $oauth->createOrUpdateUser($provider, $userInfo, $tokenData);
                                        
                                        if ($userId) {
                                            echo "<span class='success'>✅ Usuario creado/actualizado con ID: $userId</span><br>";
                                            
                                            echo "<h2>Paso 6: Iniciar sesión</h2>";
                                            
                                            // Obtener datos del usuario
                                            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                                            $stmt->execute([$userId]);
                                            $user = $stmt->fetch(PDO::FETCH_ASSOC);
                                            
                                            if ($user) {
                                                echo "<span class='success'>✅ Usuario encontrado en BD</span><br>";
                                                echo "Email: " . $user['email'] . "<br>";
                                                echo "Nombre: " . ($user['name'] ?? $user['username']) . "<br>";
                                                
                                                // Simular inicio de sesión
                                                $_SESSION['user_id'] = $user['id'];
                                                $_SESSION['user_name'] = $user['name'] ?? $user['username'];
                                                $_SESSION['user_email'] = $user['email'];
                                                $_SESSION['role'] = $user['role'];
                                                
                                                echo "<span class='success'>✅ Sesión iniciada correctamente</span><br>";
                                                echo "<br><a href='index.php' style='background:green;color:white;padding:10px;text-decoration:none;'>🎉 IR AL INICIO</a>";
                                                
                                            } else {
                                                echo "<span class='error'>❌ Usuario no encontrado en BD</span><br>";
                                            }
                                            
                                        } else {
                                            echo "<span class='error'>❌ Error al crear/actualizar usuario</span><br>";
                                        }
                                        
                                    } catch (Exception $e) {
                                        echo "<span class='error'>❌ Error en createOrUpdateUser: " . $e->getMessage() . "</span><br>";
                                    }
                                    
                                } else {
                                    echo "<span class='error'>❌ Método createOrUpdateUser no existe</span><br>";
                                }
                                
                            } else {
                                echo "<span class='error'>❌ No se pudo obtener información del usuario</span><br>";
                            }
                            
                        } catch (Exception $e) {
                            echo "<span class='error'>❌ Error en getUserInfo: " . $e->getMessage() . "</span><br>";
                        }
                        
                    } else {
                        echo "<span class='error'>❌ Método getUserInfo no existe</span><br>";
                    }
                    
                } else {
                    echo "<span class='error'>❌ No se pudo obtener access token</span><br>";
                    echo "<pre>";
                    print_r($tokenData);
                    echo "</pre>";
                }
                
            } catch (Exception $e) {
                echo "<span class='error'>❌ Error en exchangeCodeForToken: " . $e->getMessage() . "</span><br>";
                echo "<pre>" . $e->getTraceAsString() . "</pre>";
            }
            
        } else {
            echo "<span class='error'>❌ Método exchangeCodeForToken no existe</span><br>";
        }
        
    } else {
        echo "<h2>No hay código OAuth para procesar</h2>";
        echo "<p>Ve a <a href='login.php'>login.php</a> y prueba OAuth con Google</p>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Error general: " . $e->getMessage() . "</span><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p><a href='login.php'>← Volver al login</a></p>";
?>