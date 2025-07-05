<?php
/**
 * Script para configurar las tablas de e-commerce
 * Ejecutar una sola vez: http://localhost/proyecto/setup_ecommerce_database.php
 */

require_once 'config/database.php';

echo "<h1>Configuración de Base de Datos E-commerce - FractalMerch</h1>";
echo "<style>body{font-family:monospace;} .success{color:green;} .error{color:red;}</style>";
echo "<pre>";

try {
    // Verificar conexión
    $pdo->query("SELECT 1");
    echo "✅ Conexión a base de datos exitosa\n\n";
    
    echo "=== CREANDO TABLAS DE E-COMMERCE ===\n\n";
    
    // 1. Tabla de direcciones de usuario
    echo "1. Creando tabla user_addresses...\n";
    $sql = "CREATE TABLE IF NOT EXISTS user_addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        address_line_1 VARCHAR(255) NOT NULL,
        address_line_2 VARCHAR(255) NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        postal_code VARCHAR(20) NOT NULL,
        country VARCHAR(100) DEFAULT 'Argentina',
        phone VARCHAR(20) NULL,
        is_default BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_addresses (user_id)
    )";
    $pdo->exec($sql);
    echo "  ✅ user_addresses creada\n";
    
    // 2. Tabla de pedidos
    echo "2. Creando tabla orders...\n";
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(20) UNIQUE NOT NULL,
        status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        subtotal DECIMAL(10,2) NOT NULL,
        tax_amount DECIMAL(10,2) DEFAULT 0,
        shipping_amount DECIMAL(10,2) DEFAULT 0,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        total_amount DECIMAL(10,2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'ARS',
        payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
        payment_method VARCHAR(50) NULL,
        shipping_address_id INT NULL,
        billing_address_id INT NULL,
        tracking_number VARCHAR(100) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (shipping_address_id) REFERENCES user_addresses(id) ON DELETE SET NULL,
        FOREIGN KEY (billing_address_id) REFERENCES user_addresses(id) ON DELETE SET NULL,
        INDEX idx_orders_user (user_id),
        INDEX idx_orders_status (status),
        INDEX idx_orders_date (created_at)
    )";
    $pdo->exec($sql);
    echo "  ✅ orders creada\n";
    
    // 3. Tabla de items de pedidos
    echo "3. Creando tabla order_items...\n";
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_variant_id INT NULL,
        product_name VARCHAR(255) NOT NULL,
        product_sku VARCHAR(100) NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        product_options JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
        INDEX idx_order_items_order (order_id),
        INDEX idx_order_items_product (product_id)
    )";
    $pdo->exec($sql);
    echo "  ✅ order_items creada\n";
    
    // 4. Tabla de favoritos
    echo "4. Creando tabla user_favorites...\n";
    $sql = "CREATE TABLE IF NOT EXISTS user_favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_product (user_id, product_id),
        INDEX idx_favorites_user (user_id)
    )";
    $pdo->exec($sql);
    echo "  ✅ user_favorites creada\n";
    
    // 5. Tabla de reseñas
    echo "5. Creando tabla product_reviews...\n";
    $sql = "CREATE TABLE IF NOT EXISTS product_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        order_id INT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        title VARCHAR(255) NULL,
        review_text TEXT NULL,
        is_verified_purchase BOOLEAN DEFAULT FALSE,
        is_approved BOOLEAN DEFAULT TRUE,
        helpful_votes INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
        UNIQUE KEY unique_user_product_review (user_id, product_id),
        INDEX idx_reviews_product (product_id),
        INDEX idx_reviews_user (user_id),
        INDEX idx_reviews_rating (rating)
    )";
    $pdo->exec($sql);
    echo "  ✅ product_reviews creada\n";
    
    // 6. Tabla de cupones
    echo "6. Creando tabla user_coupons...\n";
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
        expires_at TIMESTAMP NOT NULL,
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
        echo "9. Agregando columna $column a users...\n";
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
    
    echo "\n=== VERIFICANDO ESTRUCTURA FINAL ===\n\n";
    
    // Verificar tablas creadas
    $ecommerce_tables = [
        'user_addresses', 'orders', 'order_items', 'user_favorites', 
        'product_reviews', 'user_coupons', 'user_points', 'user_notifications'
    ];
    
    foreach ($ecommerce_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla $table existe\n";
        } else {
            echo "✗ Tabla $table NO existe\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 ¡CONFIGURACIÓN E-COMMERCE COMPLETADA!\n";
    echo "\nAhora puedes:\n";
    echo "1. Usar el perfil de usuario modernizado\n";
    echo "2. Gestionar pedidos y favoritos\n";
    echo "3. Sistema de reseñas y puntos\n";
    echo "4. Notificaciones y cupones\n";
    echo "\n" . str_repeat("=", 60) . "\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<p><a href='profile.php'>← Ver perfil modernizado</a> | <a href='login.php'>Ir al login →</a></p>";
?>