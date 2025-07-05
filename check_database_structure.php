<?php
require_once 'config/database.php';

echo "<h1>Verificación Estructura Base de Datos</h1>";
echo "<style>body{font-family:monospace;} .success{color:green;} .error{color:red;}</style>";

try {
    echo "<h2>1. Verificar tabla users:</h2>";
    
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $oauth_columns = ['oauth_provider', 'oauth_id', 'oauth_token', 'avatar_url', 'email_verified', 'last_login', 'account_type'];
    $found_oauth = [];
    
    foreach ($columns as $column) {
        $color = in_array($column['Field'], $oauth_columns) ? 'background:lightgreen;' : '';
        if (in_array($column['Field'], $oauth_columns)) {
            $found_oauth[] = $column['Field'];
        }
        
        echo "<tr style='$color'>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>2. Verificar columnas OAuth:</h2>";
    
    foreach ($oauth_columns as $col) {
        if (in_array($col, $found_oauth)) {
            echo "<span class='success'>✅ $col existe</span><br>";
        } else {
            echo "<span class='error'>❌ $col NO existe</span><br>";
        }
    }
    
    echo "<h2>3. Verificar otras tablas OAuth:</h2>";
    
    $oauth_tables = ['oauth_tokens', 'oauth_config', 'login_attempts'];
    
    foreach ($oauth_tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<span class='success'>✅ Tabla $table existe</span><br>";
            } else {
                echo "<span class='error'>❌ Tabla $table NO existe</span><br>";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ Error verificando $table: " . $e->getMessage() . "</span><br>";
        }
    }
    
    echo "<h2>4. Test de creación de usuario OAuth:</h2>";
    
    $test_data = [
        'email' => 'test@gmail.com',
        'name' => 'Test User',
        'oauth_provider' => 'google',
        'oauth_id' => '123456789',
        'account_type' => 'oauth'
    ];
    
    try {
        // Verificar si podemos insertar un usuario OAuth
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
            echo "<span class='success'>✅ Usuario OAuth test creado con ID: $userId</span><br>";
            
            // Limpiar el test
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            echo "<span class='success'>✅ Usuario test eliminado</span><br>";
        }
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Error creando usuario OAuth test: " . $e->getMessage() . "</span><br>";
        echo "<p>Esto indica que faltan columnas OAuth en la tabla users</p>";
    }
    
    echo "<h2>5. Solución si faltan columnas:</h2>";
    if (count($found_oauth) < count($oauth_columns)) {
        echo "<p><a href='setup_oauth_database.php' style='background:orange;color:white;padding:10px;text-decoration:none;'>🔧 EJECUTAR MIGRACIÓN OAUTH</a></p>";
    } else {
        echo "<p><span class='success'>✅ Base de datos OAuth correctamente configurada</span></p>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Error general: " . $e->getMessage() . "</span>";
}

echo "<p><a href='login.php'>← Volver al login</a></p>";
?>