<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../mcp-integration.php';

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Galería de Placeholders - Panel Admin";
$mcpGenerator = new MCPImageGenerator($pdo);

// Procesar acciones
$success_message = '';
$error_message = '';

if ($_POST) {
    if (isset($_POST['delete_image'])) {
        $image_id = (int)$_POST['image_id'];
        $result = $mcpGenerator->deleteGeneratedImage($image_id, $_SESSION['user_id']);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['error'];
        }
    }
}

// Obtener filtros
$category_filter = $_GET['category'] ?? '';
$limit = 50;

// Obtener imágenes
$images = $mcpGenerator->getGeneratedImages($limit, $category_filter ?: null);

// Obtener estadísticas
$stats = $mcpGenerator->getStats();

// Categorías disponibles
$categories = [
    'productos' => 'Productos',
    'logos' => 'Logos',
    'banners' => 'Banners', 
    'backgrounds' => 'Fondos',
    'marketing' => 'Marketing',
    'social' => 'Redes Sociales',
    'web' => 'Web Design',
    'otros' => 'Otros'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gallery-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .gallery-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .gallery-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .gallery-filters {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filter-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-group label {
            font-weight: 500;
            color: #374151;
        }
        
        .filter-group select {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            min-width: 150px;
        }
        
        .filter-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .image-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }
        
        .image-card:hover {
            transform: translateY(-2px);
        }
        
        .image-preview {
            height: 200px;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .image-type-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }
        
        .real-image {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }
        
        .mock-image .image-type-badge {
            background: rgba(239, 68, 68, 0.8);
        }
        
        .real-image .image-type-badge {
            background: rgba(34, 197, 94, 0.8);
        }
        
        .image-info {
            padding: 1rem;
        }
        
        .image-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1f2937;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .image-meta {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        
        .image-tags {
            display: flex;
            gap: 0.5rem;
            margin: 0.5rem 0;
        }
        
        .tag {
            background: #e5e7eb;
            color: #374151;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
        }
        
        .tag.category-productos { background: #dbeafe; color: #1e40af; }
        .tag.category-logos { background: #f3e8ff; color: #7c3aed; }
        .tag.category-banners { background: #fed7d7; color: #c53030; }
        .tag.category-backgrounds { background: #d1fae5; color: #065f46; }
        .tag.category-marketing { background: #fef3c7; color: #92400e; }
        .tag.category-social { background: #ede9fe; color: #6b46c1; }
        .tag.category-web { background: #ecfdf5; color: #047857; }
        
        .image-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .image-filename {
            font-family: monospace;
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d1fae5;
            border: 1px solid #34d399;
            color: #065f46;
        }
        
        .alert-error {
            background: #fee2e2;
            border: 1px solid #f87171;
            color: #991b1b;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }
    </style>
</head>
<body>
    <div class="gallery-container">
        <div class="gallery-header">
            <h1><i class="fas fa-images"></i> Galería de Placeholders</h1>
            <div style="display: flex; gap: 1rem;">
                <a href="generate-images.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Generar Nueva Imagen
                </a>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="gallery-stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Imágenes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #10b981;"><?php echo $stats['real']; ?></div>
                <div class="stat-label">Imágenes Reales</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #ef4444;"><?php echo $stats['mock']; ?></div>
                <div class="stat-label">Placeholders Mock</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($stats['by_category']); ?></div>
                <div class="stat-label">Categorías Activas</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="gallery-filters">
            <form method="GET" class="filter-row">
                <div class="filter-group">
                    <label>Categoría:</label>
                    <select name="category" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo $category_filter === $key ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="placeholder-gallery.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Galería de imágenes -->
        <?php if (empty($images)): ?>
            <div class="empty-state">
                <i class="fas fa-image"></i>
                <h3>No hay imágenes generadas</h3>
                <p>¡Genera tu primera imagen placeholder!</p>
                <a href="generate-images.php" class="btn btn-primary">
                    <i class="fas fa-magic"></i> Generar Imagen
                </a>
            </div>
        <?php else: ?>
            <div class="images-grid">
                <?php foreach ($images as $image): ?>
                    <div class="image-card">
                        <div class="image-preview <?php echo $image['is_real_image'] ? 'real-image' : 'mock-image'; ?>">
                            <i class="fas fa-image fa-3x" style="color: #9ca3af;"></i>
                            <div class="image-type-badge">
                                <?php echo $image['is_real_image'] ? 'Real' : 'Mock'; ?>
                            </div>
                        </div>
                        
                        <div class="image-info">
                            <h4 class="image-title"><?php echo htmlspecialchars($image['prompt']); ?></h4>
                            
                            <div class="image-meta">
                                <strong>Estilo:</strong> <?php echo ucfirst($image['style']); ?> | 
                                <strong>Tamaño:</strong> <?php echo $image['size']; ?>
                            </div>
                            
                            <div class="image-tags">
                                <span class="tag category-<?php echo $image['category']; ?>">
                                    <?php echo $categories[$image['category']] ?? ucfirst($image['category']); ?>
                                </span>
                            </div>
                            
                            <div class="image-meta">
                                <strong>Generado:</strong> <?php echo date('d/m/Y H:i', strtotime($image['created_at'])); ?>
                                <?php if ($image['username']): ?>
                                    por <?php echo htmlspecialchars($image['username']); ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="image-actions">
                                <span class="image-filename"><?php echo htmlspecialchars($image['filename']); ?></span>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar esta imagen?');">
                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                    <button type="submit" name="delete_image" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-refresh cada 30 segundos si hay generaciones en progreso
        let autoRefresh = false;
        
        // Confirmar eliminación
        function confirmDelete(filename) {
            return confirm(`¿Estás seguro de eliminar la imagen "${filename}"?\n\nEsta acción no se puede deshacer.`);
        }
        
        // Copiar nombre de archivo al clipboard
        function copyFilename(filename) {
            navigator.clipboard.writeText(filename).then(function() {
                // Mostrar mensaje temporal
                const toast = document.createElement('div');
                toast.textContent = 'Nombre copiado al portapapeles';
                toast.style.cssText = 'position:fixed;top:20px;right:20px;background:#10b981;color:white;padding:1rem;border-radius:8px;z-index:1000';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 2000);
            });
        }
        
        // Añadir click para copiar filename
        document.querySelectorAll('.image-filename').forEach(el => {
            el.style.cursor = 'pointer';
            el.title = 'Click para copiar';
            el.addEventListener('click', () => copyFilename(el.textContent));
        });
    </script>
</body>
</html>