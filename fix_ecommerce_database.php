<?php
/**
 * Script para corregir y completar las tablas de e-commerce
 */

require_once 'config/database.php';

echo "<h1>Corrección Base de Datos E-commerce</h1>";
echo "<style>body{font-family:monospace;} .success{color:green;} .error{color:red;}</style>";
echo "<pre>";

try {
    echo "=== CORRIGIENDO TABLAS FALTANTES ===\n\n";
    
    // 6. Tabla de cupones (corregida)
    echo "6. Creando tabla user_coupons (corregida)...\n";
    $sql = "CREATE TABLE IF NOT EXISTS user_coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        code VARCHAR(50) NOT NULL,
        type ENUM('percentage', 'fixed_amount') NOT NULL,
        value DECIMAL(10,2) NOT NULL,
        min_order_amount DECIMAL(10,2) DEFAULT 0,
        max_discount_amount DECIMAL(10,2) NULL,
        is_used BOOLEAN DEFAULT FALSE,
        used_at TIMESTAMP NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_code (user_id, code),
        INDEX idx_coupons_user (user_id),
        INDEX idx_coupons_expires (expires_at)
    )";
    $pdo->exec($sql);
    echo "  ✅ user_coupons creada\n";
    
    // 7. Tabla de puntos
    echo "7. Creando tabla user_points...\n";
    $sql = "CREATE TABLE IF NOT EXISTS user_points (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        points INT NOT NULL,
        type ENUM('earned', 'redeemed') NOT NULL,
        description VARCHAR(255) NOT NULL,
        order_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
        INDEX idx_points_user (user_id),
        INDEX idx_points_type (type)
    )";
    $pdo->exec($sql);
    echo "  ✅ user_points creada\n";
    
    // 8. Tabla de notificaciones
    echo "8. Creando tabla user_notifications...\n";
    $sql = "CREATE TABLE IF NOT EXISTS user_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('order_update', 'promotion', 'system', 'review_reminder') NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        action_url VARCHAR(500) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        read_at TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_notifications_user (user_id),
        INDEX idx_notifications_read (is_read),
        INDEX idx_notifications_date (created_at)
    )";
    $pdo->exec($sql);
    echo "  ✅ user_notifications creada\n";
    
    echo "\n=== AGREGANDO COLUMNAS ADICIONALES A USERS ===\n\n";
    
    // Agregar columnas adicionales a users
    $additional_columns = [
        'phone' => "ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email",
        'date_of_birth' => "ALTER TABLE users ADD COLUMN date_of_birth DATE NULL AFTER phone", 
        'gender' => "ALTER TABLE users ADD COLUMN gender ENUM('male', 'female', 'other', 'prefer_not_to_say') NULL AFTER date_of_birth",
        'newsletter_subscribed' => "ALTER TABLE users ADD COLUMN newsletter_subscribed BOOLEAN DEFAULT TRUE AFTER gender",
        'loyalty_points' => "ALTER TABLE users ADD COLUMN loyalty_points INT DEFAULT 0 AFTER newsletter_subscribed",
        'preferred_language' => "ALTER TABLE users ADD COLUMN preferred_language VARCHAR(5) DEFAULT 'es' AFTER loyalty_points"
    ];
    
    foreach ($additional_columns as $column => $sql) {
        echo "Agregando columna $column a users...\n";
        try {
            $pdo->exec($sql);
            echo "  ✅ Columna $column agregada\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "  → Columna $column ya existe\n";
            } else {
                echo "  ✗ Error agregando $column: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n=== CREANDO ÍNDICES ADICIONALES ===\n\n";
    
    // Crear índices adicionales para users
    $indexes = [
        "ALTER TABLE users ADD INDEX idx_users_phone (phone)",
        "ALTER TABLE users ADD INDEX idx_users_newsletter (newsletter_subscribed)", 
        "ALTER TABLE users ADD INDEX idx_users_points (loyalty_points)"
    ];
    
    foreach ($indexes as $index_sql) {
        try {
            $pdo->exec($index_sql);
            echo "✅ Índice creado\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "→ Índice ya existe\n";
            } else {
                echo "✗ Error creando índice: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n=== VERIFICANDO ESTRUCTURA FINAL ===\n\n";
    
    // Verificar todas las tablas
    $all_tables = [
        'users', 'products', 'product_variants', 'user_addresses', 'orders', 
        'order_items', 'user_favorites', 'product_reviews', 'user_coupons', 
        'user_points', 'user_notifications'
    ];
    
    foreach ($all_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla $table existe\n";
        } else {
            echo "✗ Tabla $table NO existe\n";
        }
    }
    
    echo "\n=== INSERTANDO DATOS DE EJEMPLO ===\n\n";
    
    // Insertar algunos datos de ejemplo para testing
    
    // Ejemplo de cupón
    echo "Creando cupón de ejemplo...\n";
    try {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO user_coupons (user_id, code, type, value, min_order_amount, expires_at)
            VALUES (?, 'BIENVENIDO10', 'percentage', 10.00, 1000.00, DATE_ADD(NOW(), INTERVAL 30 DAY))
        ");
        $stmt->execute([$_SESSION['user_id'] ?? 1]);
        echo "  ✅ Cupón de ejemplo creado\n";
    } catch (Exception $e) {
        echo "  → Cupón ya existe o error: " . $e->getMessage() . "\n";
    }
    
    // Ejemplo de notificación
    echo "Creando notificación de ejemplo...\n";
    try {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO user_notifications (user_id, type, title, message)
            VALUES (?, 'system', '¡Bienvenido a FractalMerch!', 'Tu cuenta ha sido configurada exitosamente. Explora nuestros productos personalizados.')
        ");
        $stmt->execute([$_SESSION['user_id'] ?? 1]);
        echo "  ✅ Notificación de ejemplo creada\n";
    } catch (Exception $e) {
        echo "  → Error creando notificación: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 ¡CONFIGURACIÓN E-COMMERCE COMPLETADA EXITOSAMENTE!\n";
    echo "\nSistema listo con:\n";
    echo "✅ Gestión de pedidos y direcciones\n";
    echo "✅ Sistema de favoritos y reseñas\n";
    echo "✅ Cupones y sistema de puntos\n";
    echo "✅ Notificaciones de usuario\n";
    echo "✅ Perfil de usuario completo\n";
    echo "\n" . str_repeat("=", 60) . "\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<p><a href='profile.php' style='background:green;color:white;padding:10px;text-decoration:none;'>🚀 VER PERFIL MODERNIZADO</a></p>";
echo "<p><a href='login.php'>← Volver al login</a></p>";
?>