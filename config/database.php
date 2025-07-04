<?php
// Configuración de base de datos para desarrollo y producción

// Detectar entorno
$is_production = isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production';

if ($is_production) {
    // Configuración para Google Cloud SQL
    $host = $_ENV['DB_HOST'] ?? '/cloudsql/INSTANCE_CONNECTION_NAME';
    $dbname = $_ENV['DB_NAME'] ?? 'proyecto_web';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    // Conexión para Cloud SQL con socket Unix
    if (strpos($host, '/cloudsql/') === 0) {
        $dsn = "mysql:unix_socket=$host;dbname=$dbname;charset=utf8mb4";
    } else {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    }
} else {
    // Configuración para desarrollo local (XAMPP)
    $host = 'localhost';
    $dbname = 'proyecto_web';
    $username = 'root';
    $password = '';
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ]);
    
    // Log de conexión exitosa
    if ($is_production) {
        error_log("Conexión exitosa a Cloud SQL");
    }
    
} catch(PDOException $e) {
    $error_msg = "Error de conexión a la base de datos";
    
    if (!$is_production) {
        // En desarrollo, mostrar detalles del error
        $error_msg .= ": " . $e->getMessage();
    } else {
        // En producción, solo registrar en logs
        error_log("Database connection error: " . $e->getMessage());
    }
    
    die($error_msg);
}
?>