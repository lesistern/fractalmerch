<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test LAN Access</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>🎉 ¡LAN Access Working!</h1>
    <p><strong>Accediste correctamente desde:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
    <p><strong>URL base calculada:</strong> <?php echo SITE_URL; ?></p>
    <p><strong>IP del servidor:</strong> <?php echo $_SERVER['SERVER_ADDR'] ?? 'N/A'; ?></p>
    <p><strong>Tu IP:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'N/A'; ?></p>
    
    <h2>Enlaces de prueba:</h2>
    <ul>
        <li><a href="index.php">Ir al Index Principal</a></li>
        <li><a href="customize-shirt.php">Ir al Personalizador</a></li>
        <li><a href="debug.php">Ver Info de Debug</a></li>
    </ul>
    
    <h2>Test desde diferentes URLs:</h2>
    <ul>
        <li><a href="http://192.168.0.145/proyecto/test.php">http://192.168.0.145/proyecto/test.php</a></li>
        <li><a href="http://localhost/proyecto/test.php">http://localhost/proyecto/test.php</a></li>
    </ul>
</body>
</html>