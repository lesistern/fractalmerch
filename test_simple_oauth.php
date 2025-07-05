<?php
// Test súper simple de OAuth sin dependencias
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Súper Simple OAuth</h1>";

// URL manual de Google OAuth
$client_id = '437669890238-g5rsrqqmb28vmntiv84dedl0dqh3manm.apps.googleusercontent.com';
$redirect_uri = 'http://localhost/proyecto/auth/oauth-callback.php?provider=google';
$scope = 'openid email profile';
$state = bin2hex(random_bytes(16));

$auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'scope' => $scope,
    'response_type' => 'code',
    'state' => $state
]);

echo "<p>URL generada manualmente:</p>";
echo "<p style='word-break: break-all; background: #f0f0f0; padding: 10px;'>$auth_url</p>";

echo "<h2>Tests:</h2>";
echo "<p><a href='$auth_url' target='_blank' style='background: #4285f4; color: white; padding: 10px; text-decoration: none;'>🔗 TEST MANUAL GOOGLE</a></p>";

echo "<p><a href='auth/oauth-login.php?provider=google' style='background: #db4437; color: white; padding: 10px; text-decoration: none;'>🔗 TEST CON ARCHIVO ACTUAL</a></p>";

// Test con JavaScript para ver si hay interferencia
echo "<h2>Test con JavaScript:</h2>";
echo "<button onclick='window.location.href=\"$auth_url\"' style='background: #4285f4; color: white; padding: 10px; border: none; cursor: pointer;'>🔗 TEST CON JS</button>";

echo "<h2>Debug info:</h2>";
echo "<pre>";
echo "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
echo "Referrer: " . ($_SERVER['HTTP_REFERER'] ?? 'No referrer') . "\n";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "</pre>";

echo "<p><a href='login.php'>← Volver al login</a></p>";
?>