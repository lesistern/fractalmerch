<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Productos</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .result { background: #f0f0f0; padding: 10px; margin: 10px 0; border-left: 4px solid #007cba; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .success { border-left-color: #28a745; background: #d4edda; }
    </style>
</head>
<body>
    <h1>🧪 Test de Configuración de Productos</h1>
    
    <?php
    try {
        require_once 'config/database.php';
        echo "<div class='result success'>✅ Conexión a base de datos exitosa</div>";
        
        // Verificar si las tablas existen
        $tables = ['products', 'product_variants'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<div class='result success'>✅ Tabla '$table' existe</div>";
            } else {
                echo "<div class='result error'>❌ Tabla '$table' no existe</div>";
            }
        }
        
        // Intentar crear las tablas si no existen
        echo "<h2>🔧 Creando tablas...</h2>";
        
        // Crear tabla products
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
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "<div class='result success'>✅ Tabla 'products' creada/verificada</div>";
        
        // Crear tabla product_variants
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
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        $pdo->exec($sql);
        echo "<div class='result success'>✅ Tabla 'product_variants' creada/verificada</div>";
        
        // Verificar contenido
        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
        $productCount = $stmt->fetchColumn();
        echo "<div class='result'>📊 Productos en BD: $productCount</div>";
        
        if ($productCount == 0) {
            echo "<h3>🌱 Insertando productos de prueba...</h3>";
            
            // Insertar productos de prueba
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, cost, sku, main_image_url, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $products = [
                ['Remera Test', 'Remera de prueba para sistema admin', 5999.00, 2500.00, 'TEST-001', 'assets/images/products/remera.svg', 1],
                ['Buzo Test', 'Buzo de prueba para sistema admin', 12999.00, 6000.00, 'TEST-002', 'assets/images/products/buzo.svg', 1]
            ];
            
            foreach ($products as $product) {
                $stmt->execute($product);
                $productId = $pdo->lastInsertId();
                echo "<div class='result success'>✅ Producto '{$product[0]}' creado con ID: $productId</div>";
                
                // Insertar variantes
                $variantStmt = $pdo->prepare("INSERT INTO product_variants (product_id, size, color, stock) VALUES (?, ?, ?, ?)");
                $variants = [
                    [$productId, 'S', 'Blanco', 10],
                    [$productId, 'M', 'Blanco', 15],
                    [$productId, 'L', 'Negro', 12]
                ];
                
                foreach ($variants as $variant) {
                    $variantStmt->execute($variant);
                }
                echo "<div class='result'>📦 3 variantes agregadas al producto $productId</div>";
            }
        }
        
        // Mostrar productos existentes
        echo "<h3>📋 Productos actuales:</h3>";
        $stmt = $pdo->query("
            SELECT p.*, COUNT(pv.id) as variant_count, SUM(pv.stock) as total_stock 
            FROM products p 
            LEFT JOIN product_variants pv ON p.id = pv.product_id 
            GROUP BY p.id
        ");
        $products = $stmt->fetchAll();
        
        if (empty($products)) {
            echo "<div class='result'>ℹ️ No hay productos en la base de datos</div>";
        } else {
            echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>SKU</th><th>Precio</th><th>Variantes</th><th>Stock Total</th><th>Acción</th></tr>";
            foreach ($products as $product) {
                echo "<tr>";
                echo "<td>{$product['id']}</td>";
                echo "<td>{$product['name']}</td>";
                echo "<td>{$product['sku']}</td>";
                echo "<td>$" . number_format($product['price'], 2) . "</td>";
                echo "<td>{$product['variant_count']}</td>";
                echo "<td>{$product['total_stock']}</td>";
                echo "<td><a href='admin/manage-products.php?edit={$product['id']}' target='_blank'>✏️ Editar</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<div class='result success'>";
        echo "<h3>🎉 ¡Sistema listo!</h3>";
        echo "<p>✅ Base de datos configurada correctamente</p>";
        echo "<p>🔗 <a href='admin/manage-products.php' target='_blank'>Ir al Panel de Productos</a></p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='result error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    ?>
</body>
</html>