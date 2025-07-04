<?php
require_once 'config/database.php';

try {
    // Crear tabla de productos
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        cost DECIMAL(10,2) DEFAULT 0.00,
        sku VARCHAR(100) UNIQUE,
        main_image_url VARCHAR(500),
        category_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (category_id),
        INDEX idx_sku (sku),
        INDEX idx_created (created_at)
    )";
    $pdo->exec($sql);
    echo "✅ Tabla 'products' creada exitosamente.\n";

    // Crear tabla de variantes de productos
    $sql = "CREATE TABLE IF NOT EXISTS product_variants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        size VARCHAR(50),
        color VARCHAR(50),
        measure VARCHAR(50),
        stock INT NOT NULL DEFAULT 0,
        image_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        INDEX idx_product (product_id),
        INDEX idx_stock (stock)
    )";
    $pdo->exec($sql);
    echo "✅ Tabla 'product_variants' creada exitosamente.\n";

    // Verificar si ya hay productos
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insertar productos de ejemplo
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, cost, sku, main_image_url, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $products = [
            ['Remera Personalizada', 'Remera de algodón 100% personalizable con tu diseño', 5999.00, 2500.00, 'REM-001', 'assets/images/products/remera.svg', 1],
            ['Buzo con Capucha', 'Buzo de algodón con capucha, ideal para personalizar', 12999.00, 6000.00, 'BUZ-001', 'assets/images/products/buzo.svg', 1],
            ['Taza Mágica', 'Taza que cambia de color con el calor, personalizable', 3499.00, 1200.00, 'TAZ-001', 'assets/images/products/taza.svg', 2],
            ['Mouse Pad Gaming', 'Mouse pad de alta calidad para gaming, personalizable', 2999.00, 800.00, 'MOUSE-001', 'assets/images/products/mousepad.svg', 3],
            ['Funda de Celular', 'Funda protectora personalizable para diferentes modelos', 4999.00, 1500.00, 'FUNDA-001', 'assets/images/products/funda.svg', 3],
            ['Almohada Decorativa', 'Almohada decorativa con funda personalizable', 6999.00, 2800.00, 'ALM-001', 'assets/images/products/almohada.svg', 4]
        ];
        
        foreach ($products as $product) {
            $stmt->execute($product);
        }
        echo "✅ Productos de ejemplo insertados.\n";

        // Insertar variantes de ejemplo
        $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, size, color, stock) VALUES (?, ?, ?, ?)");
        
        $variants = [
            // Remera Personalizada (ID: 1)
            [1, 'S', 'Blanco', 15],
            [1, 'M', 'Blanco', 20],
            [1, 'L', 'Blanco', 18],
            [1, 'XL', 'Blanco', 10],
            [1, 'S', 'Negro', 12],
            [1, 'M', 'Negro', 25],
            [1, 'L', 'Negro', 22],
            [1, 'XL', 'Negro', 8],
            // Buzo con Capucha (ID: 2)
            [2, 'S', 'Gris', 10],
            [2, 'M', 'Gris', 15],
            [2, 'L', 'Gris', 12],
            [2, 'XL', 'Gris', 6],
            [2, 'S', 'Azul Marino', 8],
            [2, 'M', 'Azul Marino', 12],
            [2, 'L', 'Azul Marino', 10],
            // Taza Mágica (ID: 3)
            [3, 'Único', 'Blanco', 30],
            [3, 'Único', 'Negro', 25],
            // Mouse Pad Gaming (ID: 4)
            [4, 'Mediano', 'Negro', 20],
            [4, 'Grande', 'Negro', 15],
            // Funda de Celular (ID: 5)
            [5, 'iPhone 13', 'Transparente', 18],
            [5, 'iPhone 14', 'Transparente', 22],
            [5, 'Samsung S23', 'Transparente', 15],
            // Almohada Decorativa (ID: 6)
            [6, '40x40cm', 'Blanco', 12],
            [6, '50x50cm', 'Blanco', 8]
        ];
        
        foreach ($variants as $variant) {
            $stmt->execute($variant);
        }
        echo "✅ Variantes de ejemplo insertadas.\n";
    } else {
        echo "ℹ️ Ya existen productos en la base de datos. No se insertaron ejemplos.\n";
    }

    // Mostrar resumen
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.price, p.sku, COUNT(pv.id) as variants_count, SUM(pv.stock) as total_stock
        FROM products p 
        LEFT JOIN product_variants pv ON p.id = pv.product_id 
        GROUP BY p.id
    ");
    $products = $stmt->fetchAll();

    echo "\n📊 RESUMEN DE PRODUCTOS:\n";
    echo "ID | Nombre | Precio | SKU | Variantes | Stock Total\n";
    echo "---|---------|--------|-----|-----------|------------\n";
    foreach ($products as $product) {
        printf("%2d | %-20s | $%7.2f | %-8s | %9d | %11d\n", 
            $product['id'], 
            substr($product['name'], 0, 20), 
            $product['price'], 
            $product['sku'], 
            $product['variants_count'], 
            $product['total_stock']
        );
    }

    echo "\n🎉 ¡Configuración de productos completada exitosamente!\n";
    echo "📍 Puedes acceder al panel de administración en: admin/manage-products.php\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>