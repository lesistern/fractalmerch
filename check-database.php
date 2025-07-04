<?php
// Script para verificar y crear las tablas necesarias
require_once 'config/database.php';

echo "<h2>Verificación de Base de Datos</h2>";

try {
    // Verificar conexión
    echo "<p>✅ Conexión a base de datos exitosa</p>";
    
    // Verificar si existe la tabla generated_images
    $stmt = $pdo->query("SHOW TABLES LIKE 'generated_images'");
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        echo "<p>⚠️ Tabla 'generated_images' no existe. Creándola...</p>";
        
        // Crear tabla generated_images
        $sql = "
        CREATE TABLE generated_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            prompt TEXT NOT NULL,
            style VARCHAR(50) DEFAULT 'realistic',
            size VARCHAR(20) DEFAULT '1024x1024',
            category VARCHAR(50) DEFAULT 'otros',
            generated_by INT,
            is_real_image BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $pdo->exec($sql);
        
        // Crear índices
        $pdo->exec("CREATE INDEX idx_generated_images_category ON generated_images(category)");
        $pdo->exec("CREATE INDEX idx_generated_images_created_at ON generated_images(created_at)");
        $pdo->exec("CREATE INDEX idx_generated_images_generated_by ON generated_images(generated_by)");
        
        echo "<p>✅ Tabla 'generated_images' creada exitosamente</p>";
    } else {
        echo "<p>✅ Tabla 'generated_images' ya existe</p>";
    }
    
    // Verificar otras tablas necesarias
    $required_tables = ['users', 'posts', 'comments', 'categories'];
    
    foreach ($required_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Tabla '$table' existe</p>";
        } else {
            echo "<p>❌ Tabla '$table' NO existe - Ejecutar database.sql</p>";
        }
    }
    
    // Verificar usuario admin
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'admin@proyecto.com'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>✅ Usuario admin existe (ID: {$admin['id']})</p>";
    } else {
        echo "<p>⚠️ Usuario admin no existe. Creándolo...</p>";
        
        $hashed_password = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@proyecto.com', $hashed_password, 'admin']);
        
        echo "<p>✅ Usuario admin creado - Email: admin@proyecto.com, Password: password</p>";
    }
    
    echo "<h3>✅ Base de datos lista para usar</h3>";
    echo "<p><a href='admin/generate-images.php'>Ir al Generador de Imágenes</a></p>";
    echo "<p><a href='login.php'>Login</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>