<?php
require_once 'config/database.php';

echo "<h1>Agregar Columna 'name' a Users</h1>";
echo "<style>body{font-family:monospace;} .success{color:green;} .error{color:red;}</style>";

try {
    echo "<h2>Agregando columna 'name' a la tabla users...</h2>";
    
    // Agregar columna name después de email
    $sql = "ALTER TABLE users ADD COLUMN name VARCHAR(100) NULL AFTER email";
    $pdo->exec($sql);
    
    echo "<span class='success'>✅ Columna 'name' agregada exitosamente</span><br><br>";
    
    echo "<h2>Verificando estructura actualizada:</h2>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    foreach ($columns as $column) {
        $mark = $column['Field'] === 'name' ? ' <-- NUEVA' : '';
        echo $column['Field'] . " (" . $column['Type'] . ")" . $mark . "\n";
    }
    echo "</pre>";
    
    echo "<h2>Test de creación de usuario OAuth:</h2>";
    
    $test_data = [
        'email' => 'test_oauth@gmail.com',
        'name' => 'Test OAuth User',
        'oauth_provider' => 'google',
        'oauth_id' => '123456789_test',
        'account_type' => 'oauth'
    ];
    
    try {
        $sql = "INSERT INTO users (email, name, oauth_provider, oauth_id, account_type, email_verified, created_at) 
                VALUES (?, ?, ?, ?, ?, 1, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $test_data['email'],
            $test_data['name'],
            $test_data['oauth_provider'],
            $test_data['oauth_id'],
            $test_data['account_type']
        ]);
        
        if ($result) {
            $userId = $pdo->lastInsertId();
            echo "<span class='success'>✅ Usuario OAuth test creado exitosamente con ID: $userId</span><br>";
            
            // Verificar datos insertados
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h3>Datos del usuario creado:</h3>";
            echo "<pre>";
            foreach ($user as $key => $value) {
                if (in_array($key, ['email', 'name', 'oauth_provider', 'oauth_id', 'account_type', 'email_verified'])) {
                    echo "$key: $value\n";
                }
            }
            echo "</pre>";
            
            // Limpiar el test
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            echo "<span class='success'>✅ Usuario test eliminado</span><br>";
            
            echo "<h2>🎉 ¡Todo listo para OAuth!</h2>";
            echo "<p><a href='login.php' style='background:green;color:white;padding:10px;text-decoration:none;'>🚀 PROBAR OAUTH GOOGLE</a></p>";
        }
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Error en test final: " . $e->getMessage() . "</span><br>";
    }
    
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<span class='success'>✅ La columna 'name' ya existe</span><br>";
        echo "<p>Probando creación de usuario OAuth...</p>";
        
        // Hacer el test directamente
        $test_data = [
            'email' => 'test_oauth2@gmail.com',
            'name' => 'Test OAuth User 2',
            'oauth_provider' => 'google',
            'oauth_id' => '123456789_test2',
            'account_type' => 'oauth'
        ];
        
        try {
            $sql = "INSERT INTO users (email, name, oauth_provider, oauth_id, account_type, email_verified, created_at) 
                    VALUES (?, ?, ?, ?, ?, 1, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $test_data['email'],
                $test_data['name'],
                $test_data['oauth_provider'],
                $test_data['oauth_id'],
                $test_data['account_type']
            ]);
            
            if ($result) {
                $userId = $pdo->lastInsertId();
                echo "<span class='success'>✅ Test OAuth exitoso con ID: $userId</span><br>";
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
                echo "<span class='success'>✅ Test limpiado</span><br>";
                echo "<h2>🎉 ¡OAuth listo para usar!</h2>";
                echo "<p><a href='login.php' style='background:green;color:white;padding:10px;text-decoration:none;'>🚀 PROBAR OAUTH GOOGLE</a></p>";
            }
        } catch (Exception $e2) {
            echo "<span class='error'>❌ Error en test: " . $e2->getMessage() . "</span><br>";
        }
        
    } else {
        echo "<span class='error'>❌ Error agregando columna: " . $e->getMessage() . "</span><br>";
    }
}

echo "<p><a href='login.php'>← Volver al login</a></p>";
?>