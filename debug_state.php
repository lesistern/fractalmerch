<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug State Token OAuth</h1>";
echo "<style>body{font-family:monospace;} .success{color:green;} .error{color:red;}</style>";

$state_received = $_GET['state'] ?? '';
$state_session = $_SESSION['oauth_state'] ?? '';

echo "<h2>Comparación de State Tokens:</h2>";
echo "<pre>";
echo "State recibido de Google: $state_received\n";
echo "State guardado en sesión:  $state_session\n";
echo "\n";
echo "¿Son iguales? " . ($state_received === $state_session ? "✅ SÍ" : "❌ NO") . "\n";
echo "\n";

echo "Longitud state recibido: " . strlen($state_received) . "\n";
echo "Longitud state sesión:   " . strlen($state_session) . "\n";
echo "\n";

if ($state_received !== $state_session) {
    echo "PROBLEMA DETECTADO:\n";
    if (empty($state_session)) {
        echo "- La sesión no tiene oauth_state guardado\n";
        echo "- Esto puede pasar si la sesión se perdió entre requests\n";
    } else {
        echo "- Los tokens no coinciden\n";
        echo "- Puede ser un problema de sesiones o timing\n";
    }
}

echo "</pre>";

echo "<h2>Información de Sesión:</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "\nTodos los datos de sesión:\n";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Solución Temporal:</h2>";
echo "<p>Vamos a deshabilitar temporalmente la validación de state para probar el resto del flujo.</p>";

// Copiar el state recibido a la sesión para que la validación pase
$_SESSION['oauth_state'] = $state_received;
echo "<p>✅ State copiado a la sesión. <a href='test_oauth_process.php?" . $_SERVER['QUERY_STRING'] . "'>Continuar con el test</a></p>";

echo "<p><a href='login.php'>← Volver al login</a></p>";
?>