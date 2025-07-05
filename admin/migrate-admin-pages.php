<?php
/**
 * Script de migración para actualizar todas las páginas admin al nuevo sistema
 * Ejecutar una vez para convertir todas las páginas al sistema unificado
 */

// Solo permitir ejecución desde línea de comandos o admin autorizado
if (!isset($_GET['migrate']) || $_GET['migrate'] !== 'admin2025') {
    die('Acceso denegado. Usa: migrate-admin-pages.php?migrate=admin2025');
}

echo "<h1>🔄 Migrando páginas admin al sistema unificado</h1>";
echo "<pre>";

// Lista de páginas a migrar
$admin_pages = [
    'manage-users.php',
    'manage-posts.php', 
    'manage-comments.php',
    'manage-categories.php',
    'manage-products.php',
    'inventory.php',
    'sales.php',
    'marketing.php',
    'email-marketing.php',
    'social-media.php',
    'settings.php',
    'stats-traffic.php',
    'stats-payments.php',
    'stats-products.php',
    'stats-shipping.php',
    'pos.php',
    'coupons.php',
    'promotions.php'
];

$migrated = 0;
$errors = 0;

foreach ($admin_pages as $page) {
    $file_path = __DIR__ . '/' . $page;
    
    echo "\n📄 Procesando: $page\n";
    
    if (!file_exists($file_path)) {
        echo "❌ Archivo no encontrado: $page\n";
        continue;
    }
    
    // Leer contenido actual
    $content = file_get_contents($file_path);
    
    if (!$content) {
        echo "❌ Error leyendo: $page\n";
        $errors++;
        continue;
    }
    
    // Backup del archivo original
    $backup_path = __DIR__ . '/' . str_replace('.php', '-backup.php', $page);
    if (!file_exists($backup_path)) {
        file_put_contents($backup_path, $content);
        echo "💾 Backup creado: " . basename($backup_path) . "\n";
    }
    
    // Patrones de reemplazo para migración
    $patterns = [
        // Reemplazar headers antiguos
        '/require_once \'\.\.\/includes\/functions\.php\';\s*require_once \'\.\.\/config\/database\.php\';\s*if \(!is_logged_in\(\) \|\| !is_admin\(\)\) \{[^}]*\}/s' => '',
        '/require_once \'\.\.\/includes\/functions\.php\';\s*if \(!is_logged_in\(\) \|\| !is_admin\(\)\) \{[^}]*\}/s' => '',
        '/include \'admin-dashboard-header\.php\';\s*\?>/s' => 'include \'admin-master-header.php\';\n?>',
        '/include \'includes\/admin-sidebar\.php\';\s*\?>/s' => 'include \'admin-master-header.php\';\n?>',
        
        // Reemplazar containers antiguos
        '/<div class="modern-admin-container">/s' => '',
        '/<div class="modern-admin-main">/s' => '',
        '/<\/div>\s*<\/div>\s*<\/body>/s' => '',
        
        // Reemplazar headers de página
        '/<div class="tiendanube-header">[^<]*<\/div>/s' => '',
        
        // Agregar pageTitle si no existe
        '/(\$page_title = [^;]*;)/s' => '$pageTitle = $1',
        
        // Footer
        '/<\/body>\s*<\/html>/s' => "<?php include 'admin-master-footer.php'; ?>"
    ];
    
    $new_content = $content;
    
    // Aplicar transformaciones
    foreach ($patterns as $pattern => $replacement) {
        $new_content = preg_replace($pattern, $replacement, $new_content);
    }
    
    // Si el archivo no tiene el nuevo header, agregarlo al inicio
    if (strpos($new_content, 'admin-master-header.php') === false) {
        // Extraer la parte PHP del inicio
        if (preg_match('/^(<\?php.*?\$pageTitle = [^;]*;)/s', $new_content, $matches)) {
            $php_part = $matches[1];
            $rest = substr($new_content, strlen($matches[1]));
            
            $new_content = $php_part . "\ninclude 'admin-master-header.php';\n?>\n\n" . 
                          preg_replace('/^\s*\?>\s*/', '', $rest);
        }
    }
    
    // Envolver contenido en page-header si no lo tiene
    if (strpos($new_content, 'page-header') === false && strpos($new_content, '<h1>') !== false) {
        $new_content = preg_replace(
            '/(<h1[^>]*>.*?<\/h1>)/s',
            '<div class="page-header">$1<p>Panel de administración</p></div>',
            $new_content
        );
    }
    
    // Envolver contenido principal en content-card si es necesario
    if (strpos($new_content, 'content-card') === false) {
        // Buscar el contenido principal después del page-header
        $new_content = preg_replace(
            '/(page-header.*?<\/div>)\s*(.*?)(<\?php include.*?footer.*?\?>)/s',
            '$1<div class="content-card">$2</div>$3',
            $new_content
        );
    }
    
    // Asegurar que termina con el footer correcto
    if (strpos($new_content, 'admin-master-footer.php') === false) {
        $new_content = rtrim($new_content);
        $new_content .= "\n\n<?php include 'admin-master-footer.php'; ?>";
    }
    
    // Escribir archivo actualizado
    if (file_put_contents($file_path, $new_content)) {
        echo "✅ Migrado exitosamente: $page\n";
        $migrated++;
    } else {
        echo "❌ Error escribiendo: $page\n";
        $errors++;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 MIGRACIÓN COMPLETADA\n";
echo "✅ Páginas migradas: $migrated\n";
echo "❌ Errores: $errors\n";
echo "💾 Backups creados en: *-backup.php\n";
echo "\n🔧 PÁGINAS ACTUALIZADAS AL SISTEMA UNIFICADO:\n";
echo "   • Header: admin-master-header.php\n";
echo "   • Footer: admin-master-footer.php\n";
echo "   • Navegación: Sidebar integrado\n";
echo "   • Estilos: CSS unificado\n";
echo "   • JavaScript: AdminUtils global\n";

echo "\n🌐 PRÓXIMOS PASOS:\n";
echo "1. Verificar funcionamiento en: http://localhost/proyecto/admin/\n";
echo "2. Revisar cada página migrada\n";
echo "3. Eliminar archivos backup una vez confirmado\n";
echo "4. Actualizar enlaces rotos si los hay\n";

echo "</pre>";

// Crear un archivo de estado de migración
$migration_status = [
    'date' => date('Y-m-d H:i:s'),
    'migrated' => $migrated,
    'errors' => $errors,
    'pages' => $admin_pages,
    'version' => '2.1.0'
];

file_put_contents(__DIR__ . '/migration-status.json', json_encode($migration_status, JSON_PRETTY_PRINT));

echo "<p><strong>✅ Migración completada. Estado guardado en migration-status.json</strong></p>";
?>