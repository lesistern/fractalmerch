<?php
// Debug súper simple para capturar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Simple Debug OAuth</h1>";
echo "<style>body{font-family:monospace;}</style>";

echo "<h2>1. Parámetros GET:</h2>";
echo "<pre>";
foreach ($_GET as $key => $value) {
    echo htmlspecialchars("$key = $value") . "\n";
}
echo "</pre>";

echo "<h2>2. Test de archivos:</h2>";
echo "<pre>";

$files = [
    'config/database.php',
    'config/config.php', 
    'config/oauth.php',
    'includes/functions.php',
    'includes/oauth/OAuthManager.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe\n";
    } else {
        echo "❌ $file NO existe\n";
    }
}

echo "</pre>";

echo "<h2>3. Test básico de carga de archivos:</h2>";
echo "<pre>";

try {
    echo "Cargando database.php... ";
    require_once 'config/database.php';
    echo "✅\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

try {
    echo "Cargando config.php... ";
    require_once 'config/config.php';
    echo "✅\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

try {
    echo "Verificando conexión PDO... ";
    if (isset($pdo)) {
        echo "✅ PDO conectado\n";
    } else {
        echo "❌ PDO no disponible\n";
    }
} catch (Exception $e) {
    echo "❌ Error PDO: " . $e->getMessage() . "\n";
}

echo "</pre>";

echo "<h2>4. URL completa para copiar:</h2>";
$currentUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
echo "<input type='text' value='" . htmlspecialchars($currentUrl) . "' style='width:100%; padding:5px;' readonly>";

echo "<p><a href='login.php'>← Volver al login</a></p>";
?>