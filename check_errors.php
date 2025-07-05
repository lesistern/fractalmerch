<?php
// Mostrar todos los errores PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Verificación de Errores</h1>";
echo "<pre>";

echo "1. Configuración PHP:\n";
echo "Error reporting: " . error_reporting() . "\n";
echo "Display errors: " . ini_get('display_errors') . "\n";
echo "Log errors: " . ini_get('log_errors') . "\n";
echo "Error log: " . ini_get('error_log') . "\n";

echo "\n2. Probando auth/oauth-login.php directamente:\n";

try {
    // Simular la llamada a oauth-login.php
    $_GET['provider'] = 'google';
    
    ob_start();
    include 'auth/oauth-login.php';
    $output = ob_get_clean();
    
    echo "Salida capturada: " . strlen($output) . " caracteres\n";
    if ($output) {
        echo "Contenido: " . htmlspecialchars(substr($output, 0, 200)) . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. Verificando headers enviados:\n";
if (headers_sent($file, $line)) {
    echo "⚠️ Headers ya enviados en $file línea $line\n";
} else {
    echo "✅ Headers no enviados aún\n";
}

echo "\n4. Test de redirección manual:\n";
echo "Ejecutando redirect('../login.php')...\n";

// Test manual de redirect
function test_redirect($url) {
    echo "Intentando redireccionar a: $url\n";
    if (!headers_sent()) {
        header("Location: $url");
        echo "Header enviado correctamente\n";
    } else {
        echo "No se puede enviar header - ya enviados\n";
    }
}

echo "</pre>";

echo "<h2>Test de Links Directos</h2>";
echo "<p><a href='auth/oauth-login.php?provider=google' target='_blank'>🔗 Test oauth-login.php directo</a></p>";
echo "<p><a href='login.php'>← Volver al login</a></p>";
?>