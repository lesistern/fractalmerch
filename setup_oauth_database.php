<?php
/**
 * Script para configurar la base de datos OAuth
 * Ejecutar una sola vez: http://localhost/proyecto/setup_oauth_database.php
 */

require_once 'config/database.php';

echo "<h1>Configuración de Base de Datos OAuth - FractalMerch</h1>";
echo "<pre>";

try {
    // Verificar conexión
    $pdo->query("SELECT 1");
    echo "✓ Conexión a base de datos exitosa\n\n";
    
    // 1. Agregar columnas OAuth a tabla users
    echo "1. Agregando columnas OAuth a tabla users...\n";
    
    $alterQueries = [
        "ALTER TABLE users ADD COLUMN oauth_provider VARCHAR(50) NULL AFTER password",
        "ALTER TABLE users ADD COLUMN oauth_id VARCHAR(255) NULL AFTER oauth_provider", 
        "ALTER TABLE users ADD COLUMN oauth_token TEXT NULL AFTER oauth_id",
        "ALTER TABLE users ADD COLUMN avatar_url VARCHAR(500) NULL AFTER oauth_token",
        "ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER avatar_url",
        "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL AFTER email_verified",
        "ALTER TABLE users ADD COLUMN account_type ENUM('local', 'oauth') DEFAULT 'local' AFTER last_login"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
            echo "  ✓ " . substr($query, 0, 50) . "...\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "  → Columna ya existe: " . substr($query, 0, 50) . "...\n";
            } else {
                echo "  ✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // 2. Crear índices
    echo "\n2. Creando índices...\n";
    
    $indexQueries = [
        "ALTER TABLE users ADD INDEX idx_oauth_provider_id (oauth_provider, oauth_id)",
        "ALTER TABLE users ADD INDEX idx_email_verified (email_verified)",
        "ALTER TABLE users ADD INDEX idx_account_type (account_type)"
    ];
    
    foreach ($indexQueries as $query) {
        try {
            $pdo->exec($query);
            echo "  ✓ " . substr($query, 0, 50) . "...\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "  → Índice ya existe: " . substr($query, 0, 50) . "...\n";
            } else {
                echo "  ✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // 3. Hacer password opcional
    echo "\n3. Haciendo password opcional para usuarios OAuth...\n";
    try {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL");
        echo "  ✓ Password ahora es opcional\n";
    } catch (PDOException $e) {
        echo "  → Ya configurado: " . $e->getMessage() . "\n";
    }
    
    // 4. Crear tabla oauth_tokens
    echo "\n4. Creando tabla oauth_tokens...\n";
    $createTokensTable = "
    CREATE TABLE IF NOT EXISTS oauth_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        provider VARCHAR(50) NOT NULL,
        access_token TEXT,
        refresh_token TEXT,
        token_expires TIMESTAMP NULL,
        scope TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_provider (user_id, provider)
    )";
    
    try {
        $pdo->exec($createTokensTable);
        echo "  ✓ Tabla oauth_tokens creada\n";
    } catch (PDOException $e) {
        echo "  → Tabla ya existe o error: " . $e->getMessage() . "\n";
    }
    
    // 5. Crear tabla oauth_config
    echo "\n5. Creando tabla oauth_config...\n";
    $createConfigTable = "
    CREATE TABLE IF NOT EXISTS oauth_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        provider VARCHAR(50) NOT NULL UNIQUE,
        client_id VARCHAR(255) NOT NULL,
        client_secret VARCHAR(255) NOT NULL,
        redirect_uri VARCHAR(500) NOT NULL,
        scope TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    try {
        $pdo->exec($createConfigTable);
        echo "  ✓ Tabla oauth_config creada\n";
    } catch (PDOException $e) {
        echo "  → Tabla ya existe o error: " . $e->getMessage() . "\n";
    }
    
    // 6. Crear tabla login_attempts
    echo "\n6. Creando tabla login_attempts...\n";
    $createAttemptsTable = "
    CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255),
        provider VARCHAR(50),
        ip_address VARCHAR(45),
        user_agent TEXT,
        success BOOLEAN,
        error_message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email_provider (email, provider),
        INDEX idx_ip_success (ip_address, success),
        INDEX idx_created_at (created_at)
    )";
    
    try {
        $pdo->exec($createAttemptsTable);
        echo "  ✓ Tabla login_attempts creada\n";
    } catch (PDOException $e) {
        echo "  → Tabla ya existe o error: " . $e->getMessage() . "\n";
    }
    
    // 7. Verificar estructura final
    echo "\n7. Verificando estructura de base de datos...\n";
    
    $tables = ['users', 'oauth_tokens', 'oauth_config', 'login_attempts'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "  ✓ Tabla $table existe\n";
        } else {
            echo "  ✗ Tabla $table NO existe\n";
        }
    }
    
    // Verificar columnas OAuth en users
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['oauth_provider', 'oauth_id', 'oauth_token', 'avatar_url', 'email_verified', 'last_login', 'account_type'];
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "  ✓ Columna users.$column existe\n";
        } else {
            echo "  ✗ Columna users.$column NO existe\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 ¡MIGRACIÓN OAUTH COMPLETADA EXITOSAMENTE!\n";
    echo "\nAhora puedes:\n";
    echo "1. Usar registro/login tradicional en: http://localhost/proyecto/register.php\n";
    echo "2. Configurar credenciales OAuth en: config/oauth.php\n";
    echo "3. Probar el sistema en: http://localhost/proyecto/login.php\n";
    echo "\n" . str_repeat("=", 60) . "\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<p><a href='login.php'>← Volver al login</a> | <a href='register.php'>Ir al registro →</a></p>";
?>