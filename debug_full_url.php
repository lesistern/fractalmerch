<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug URL Completa</h1>";
echo "<style>body{font-family:monospace;}</style>";

echo "<h2>URL Completa Recibida:</h2>";
$full_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
echo "<textarea style='width:100%; height:100px;'>$full_url</textarea>";

echo "<h2>Parámetros GET Sin Procesar:</h2>";
echo "<pre>";
echo "Query String Raw: " . $_SERVER['QUERY_STRING'] . "\n\n";

echo "Parámetros individuales:\n";
foreach ($_GET as $key => $value) {
    echo "$key = $value (longitud: " . strlen($value) . ")\n";
}
echo "</pre>";

echo "<h2>Test con State Completo:</h2>";
// Extraer state completo de la query string
parse_str($_SERVER['QUERY_STRING'], $params);
$full_state = $params['state'] ?? '';

echo "<pre>";
echo "State completo extraído: $full_state\n";
echo "Longitud: " . strlen($full_state) . "\n";
echo "State en sesión: " . ($_SESSION['oauth_state'] ?? 'NO EXISTE') . "\n";
echo "¿Coinciden ahora? " . ($full_state === ($_SESSION['oauth_state'] ?? '') ? "✅ SÍ" : "❌ NO") . "\n";
echo "</pre>";

if ($full_state && isset($_SESSION['oauth_state']) && $full_state === $_SESSION['oauth_state']) {
    echo "<h2>✅ State válido - Continuar con OAuth:</h2>";
    $new_url = "test_oauth_process.php?" . $_SERVER['QUERY_STRING'];
    echo "<a href='$new_url' style='background:green;color:white;padding:10px;text-decoration:none;'>🚀 CONTINUAR OAUTH</a>";
} else {
    echo "<h2>❌ State inválido - Forzar corrección:</h2>";
    $_SESSION['oauth_state'] = $full_state;
    $new_url = "test_oauth_process.php?" . $_SERVER['QUERY_STRING'];
    echo "<a href='$new_url' style='background:orange;color:white;padding:10px;text-decoration:none;'>🔧 FORZAR Y CONTINUAR</a>";
}

echo "<p><a href='login.php'>← Volver al login</a></p>";
?>