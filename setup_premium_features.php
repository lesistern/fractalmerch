<?php
/**
 * Script para configurar las funcionalidades Premium
 * Ejecutar una sola vez: http://localhost/proyecto/setup_premium_features.php
 */

require_once 'config/database.php';

echo "<h1>🚀 Configuración Premium Features - FractalMerch</h1>";
echo "<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #28a745; }
    .error { color: #dc3545; }
    .info { color: #17a2b8; }
    .warning { color: #ffc107; }
    .section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff; }
    .progress { background: #e9ecef; border-radius: 5px; overflow: hidden; margin: 10px 0; }
    .progress-bar { background: #007bff; color: white; text-align: center; padding: 5px; }
</style>";

echo "<div class='container'>";
echo "<pre>";

try {
    // Verificar conexión
    $pdo->query("SELECT 1");
    echo "✅ <span class='success'>Conexión a base de datos exitosa</span>\n\n";
    
    echo "=== 🎯 CREANDO FUNCIONALIDADES PREMIUM ===\n\n";
    
    // Leer y ejecutar el archivo SQL
    $sql_content = file_get_contents(__DIR__ . '/database/premium_features.sql');
    
    // Dividir en comandos individuales
    $commands = array_filter(
        array_map('trim', explode(';', $sql_content)),
        function($cmd) { return !empty($cmd) && !preg_match('/^\s*--/', $cmd); }
    );
    
    $total_commands = count($commands);
    $executed = 0;
    
    echo "<div class='section'>";
    echo "<h3>📊 Progreso de Instalación</h3>";
    
    foreach ($commands as $command) {
        if (empty(trim($command))) continue;
        
        try {
            $pdo->exec($command);
            $executed++;
            
            // Determinar qué se está creando
            if (preg_match('/CREATE TABLE.*?(\w+)\s*\(/i', $command, $matches)) {
                echo "✅ <span class='success'>Tabla {$matches[1]} creada</span>\n";
            } elseif (preg_match('/ALTER TABLE.*?(\w+)/i', $command, $matches)) {
                echo "✅ <span class='info'>Tabla {$matches[1]} modificada</span>\n";
            } elseif (preg_match('/INSERT INTO.*?(\w+)/i', $command, $matches)) {
                echo "✅ <span class='info'>Datos insertados en {$matches[1]}</span>\n";
            } else {
                echo "✅ <span class='success'>Comando ejecutado</span>\n";
            }
            
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "→ <span class='warning'>Ya existe (omitido)</span>\n";
            } else {
                echo "✗ <span class='error'>Error: " . $e->getMessage() . "</span>\n";
            }
        }
    }
    
    $progress_percentage = ($executed / $total_commands) * 100;
    echo "<div class='progress'>";
    echo "<div class='progress-bar' style='width: {$progress_percentage}%'>";
    echo round($progress_percentage, 1) . "%";
    echo "</div></div>";
    
    echo "</div>";
    
    echo "\n=== 🎨 CREANDO DIRECTORIOS DE UPLOADS ===\n\n";
    
    // Crear directorios necesarios
    $directories = [
        'assets/images/profiles',
        'assets/images/covers',
        'assets/images/payments',
        'assets/documents/tickets',
        'assets/temp'
    ];
    
    foreach ($directories as $dir) {
        $full_path = __DIR__ . '/' . $dir;
        if (!is_dir($full_path)) {
            if (mkdir($full_path, 0755, true)) {
                echo "✅ <span class='success'>Directorio $dir creado</span>\n";
            } else {
                echo "✗ <span class='error'>Error creando directorio $dir</span>\n";
            }
        } else {
            echo "→ <span class='warning'>Directorio $dir ya existe</span>\n";
        }
        
        // Crear archivo .htaccess para seguridad
        $htaccess_content = "Options -Indexes\n<Files *.php>\nDeny from all\n</Files>";
        file_put_contents($full_path . '/.htaccess', $htaccess_content);
    }
    
    echo "\n=== 🔧 CONFIGURANDO DATOS INICIALES ===\n\n";
    
    // Insertar cupones de ejemplo
    $sample_coupons = [
        [
            'code' => 'PREMIUM10',
            'name' => 'Descuento Premium',
            'description' => 'Descuento del 10% para usuarios premium',
            'type' => 'percentage',
            'value' => 10.00,
            'min_order_amount' => 2000.00,
            'max_uses' => 100,
            'valid_from' => date('Y-m-d H:i:s'),
            'valid_until' => date('Y-m-d H:i:s', strtotime('+3 months'))
        ],
        [
            'code' => 'WELCOME500',
            'name' => 'Bienvenido',
            'description' => '$500 de descuento en tu primera compra',
            'type' => 'fixed_amount',
            'value' => 500.00,
            'min_order_amount' => 1500.00,
            'max_uses' => 1000,
            'valid_from' => date('Y-m-d H:i:s'),
            'valid_until' => date('Y-m-d H:i:s', strtotime('+6 months'))
        ]
    ];
    
    $coupon_stmt = $pdo->prepare("
        INSERT IGNORE INTO global_coupons 
        (code, name, description, type, value, min_order_amount, max_uses, valid_from, valid_until) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($sample_coupons as $coupon) {
        try {
            $coupon_stmt->execute([
                $coupon['code'], $coupon['name'], $coupon['description'],
                $coupon['type'], $coupon['value'], $coupon['min_order_amount'],
                $coupon['max_uses'], $coupon['valid_from'], $coupon['valid_until']
            ]);
            echo "✅ <span class='success'>Cupón {$coupon['code']} creado</span>\n";
        } catch (Exception $e) {
            echo "→ <span class='warning'>Cupón {$coupon['code']} ya existe</span>\n";
        }
    }
    
    // Generar códigos de referido para usuarios existentes
    echo "\n=== 👥 GENERANDO CÓDIGOS DE REFERIDO ===\n\n";
    
    $users_stmt = $pdo->query("SELECT id, username FROM users WHERE referral_code IS NULL");
    $update_stmt = $pdo->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
    
    while ($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
        $referral_code = strtoupper(substr(md5($user['username'] . $user['id']), 0, 8));
        try {
            $update_stmt->execute([$referral_code, $user['id']]);
            echo "✅ <span class='success'>Código de referido para {$user['username']}: $referral_code</span>\n";
        } catch (Exception $e) {
            echo "✗ <span class='error'>Error generando código para {$user['username']}</span>\n";
        }
    }
    
    echo "\n=== 📊 VERIFICANDO INSTALACIÓN ===\n\n";
    
    // Verificar todas las tablas premium
    $premium_tables = [
        'user_payment_methods', 'user_oauth_accounts', 'user_subscriptions',
        'global_coupons', 'coupon_usage', 'points_history', 'support_tickets',
        'support_ticket_responses', 'user_wishlists', 'wishlist_items',
        'user_activity_log', 'user_preferences'
    ];
    
    foreach ($premium_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ <span class='success'>Tabla $table existe</span>\n";
        } else {
            echo "✗ <span class='error'>Tabla $table NO existe</span>\n";
        }
    }
    
    // Verificar columnas agregadas a users
    $user_columns = [
        'profile_photo', 'cover_photo', 'subscription_tier', 'profile_completion_percentage',
        'referral_code', 'referred_by', 'two_factor_enabled', 'two_factor_secret',
        'last_active_at', 'is_premium', 'premium_until'
    ];
    
    $columns_stmt = $pdo->query("DESCRIBE users");
    $existing_columns = [];
    while ($column = $columns_stmt->fetch(PDO::FETCH_ASSOC)) {
        $existing_columns[] = $column['Field'];
    }
    
    foreach ($user_columns as $column) {
        if (in_array($column, $existing_columns)) {
            echo "✅ <span class='success'>Columna users.$column existe</span>\n";
        } else {
            echo "✗ <span class='error'>Columna users.$column NO existe</span>\n";
        }
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🎉 <span class='success'>¡CONFIGURACIÓN PREMIUM COMPLETADA!</span>\n";
    echo "\n🚀 <strong>Nuevas funcionalidades disponibles:</strong>\n";
    echo "✅ Sistema de métodos de pago\n";
    echo "✅ Vinculación de múltiples cuentas OAuth\n";
    echo "✅ Suscripciones y planes premium\n";
    echo "✅ Sistema de cupones globales\n";
    echo "✅ Historial de puntos detallado\n";
    echo "✅ Sistema de tickets de soporte\n";
    echo "✅ Wishlists avanzadas\n";
    echo "✅ Log de actividad de usuario\n";
    echo "✅ Preferencias personalizables\n";
    echo "✅ Subida de fotos de perfil\n";
    echo "✅ Códigos de referido\n";
    echo "✅ Autenticación de dos factores\n";
    echo "\n" . str_repeat("=", 70) . "\n";
    
} catch (Exception $e) {
    echo "\n❌ <span class='error'>ERROR: " . $e->getMessage() . "</span>\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "</div>";

echo "<div style='margin-top: 20px; text-align: center;'>";
echo "<a href='profile.php' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block; margin: 10px;'>";
echo "🚀 VER PERFIL PREMIUM";
echo "</a>";
echo "<a href='login.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block; margin: 10px;'>";
echo "← Volver al Login";
echo "</a>";
echo "</div>";
?>