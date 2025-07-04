<?php
echo "<h1>Debug Info</h1>";
echo "<h2>Request Info:</h2>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'undefined') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'undefined') . "<br>";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'undefined') . "<br>";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'undefined') . "<br>";

echo "<h2>Config Info:</h2>";
require_once 'config/config.php';
echo "SITE_URL: " . SITE_URL . "<br>";
echo "SITE_NAME: " . SITE_NAME . "<br>";

echo "<h2>Session Info:</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Logged in: " . (is_logged_in() ? 'Yes' : 'No') . "<br>";

echo "<h2>Headers Sent:</h2>";
if (headers_sent()) {
    echo "Headers already sent!<br>";
} else {
    echo "Headers not sent yet<br>";
}

echo "<h2>All Server Variables:</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";
?>

<br><br>
<a href="index.php">Go to Index</a> | 
<a href="customize-shirt.php">Go to Customize Shirt</a>