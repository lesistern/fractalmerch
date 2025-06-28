<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Generador de Imágenes - Panel Admin";
include '../includes/header.php';
$success_message = '';
$error_message = '';

// Procesar formulario de generación
if ($_POST && isset($_POST['generate_image'])) {
    $prompt = trim($_POST['prompt']);
    $style = $_POST['style'];
    $size = $_POST['size'];
    $category = $_POST['category'];
    
    if (!empty($prompt)) {
        try {
            // Crear comando para ejecutar el generador MCP
            $mcp_path = '../mcp-media-generator/build/index.js';
            $output_dir = '../mcp-media-generator/generated-media/';
            
            // Crear directorio si no existe
            if (!file_exists($output_dir)) {
                mkdir($output_dir, 0755, true);
            }
            
            // Preparar el comando Node.js para generar imagen
            $command_data = json_encode([
                'tool' => 'generate_image',
                'arguments' => [
                    'prompt' => $prompt,
                    'style' => $style,
                    'size' => $size
                ]
            ]);
            
            // Crear archivo temporal con los datos
            $temp_file = tempnam(sys_get_temp_dir(), 'mcp_request');
            file_put_contents($temp_file, $command_data);
            
            // Ejecutar el generador (modo mock por ahora)
            $timestamp = time();
            $filename = "admin_generated_{$timestamp}.txt";
            $filepath = $output_dir . $filename;
            
            // Crear archivo placeholder mock
            $mock_content = "Imagen Generada por Admin\n";
            $mock_content .= "Prompt: {$prompt}\n";
            $mock_content .= "Estilo: {$style}\n";
            $mock_content .= "Tamaño: {$size}\n";
            $mock_content .= "Categoría: {$category}\n";
            $mock_content .= "Generado: " . date('Y-m-d H:i:s') . "\n";
            $mock_content .= "\nEsta es una imagen placeholder generada por IA.\n";
            $mock_content .= "En producción, aquí habría una imagen real PNG/JPG.";
            
            file_put_contents($filepath, $mock_content);
            
            // Guardar en base de datos
            $stmt = $pdo->prepare("INSERT INTO generated_images (filename, prompt, style, size, category, generated_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$filename, $prompt, $style, $size, $category, $_SESSION['user_id']]);
            
            // Limpiar archivo temporal
            unlink($temp_file);
            
            $success_message = "Imagen placeholder generada exitosamente: {$filename}";
            
        } catch (Exception $e) {
            $error_message = "Error al generar imagen: " . $e->getMessage();
        }
    } else {
        $error_message = "Por favor, ingresa una descripción para la imagen.";
    }
}

// Obtener imágenes generadas recientes
$stmt = $pdo->prepare("
    SELECT gi.*, u.username 
    FROM generated_images gi 
    LEFT JOIN users u ON gi.generated_by = u.id 
    ORDER BY gi.created_at DESC 
    LIMIT 20
");
$stmt->execute();
$recent_images = $stmt->fetchAll();
?>

<!-- Estilos adicionales para el generador -->
    <style>
        .image-generator {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .generator-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .generate-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            grid-column: 1 / -1;
            justify-self: center;
        }
        
        .generate-btn:hover {
            transform: translateY(-2px);
        }
        
        .recent-images {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .image-card {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            transition: border-color 0.3s;
        }
        
        .image-card:hover {
            border-color: #3b82f6;
        }
        
        .image-placeholder {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            height: 200px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .image-info h4 {
            margin: 0 0 0.5rem 0;
            color: #1f2937;
        }
        
        .image-meta {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
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
        
        .category-badge {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>

<!-- Contenido principal del generador -->
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-magic"></i> Generador de Imágenes Admin</h1>
            <div class="admin-nav">
                <a href="placeholder-gallery.php" class="btn btn-primary">
                    <i class="fas fa-images"></i> Ver Galería
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

        <div class="image-generator">
            <h2><i class="fas fa-wand-magic-sparkles"></i> Generar Nueva Imagen Placeholder</h2>
            <p>Genera imágenes placeholder con IA para usar temporalmente hasta tener imágenes reales.</p>
            
            <form method="POST" class="generator-form">
                <div class="form-group full-width">
                    <label for="prompt">Descripción de la Imagen *</label>
                    <textarea name="prompt" id="prompt" placeholder="Ej: Logo moderno para empresa tech, paisaje de montaña al atardecer, producto tecnológico elegante..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="style">Estilo</label>
                    <select name="style" id="style">
                        <option value="realistic">Realista</option>
                        <option value="digital-art">Arte Digital</option>
                        <option value="photographic">Fotográfico</option>
                        <option value="artistic">Artístico</option>
                        <option value="cinematic">Cinematográfico</option>
                        <option value="cartoon">Cartoon</option>
                        <option value="anime">Anime</option>
                        <option value="fantasy">Fantasía</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="size">Tamaño</label>
                    <select name="size" id="size">
                        <option value="1024x1024">Cuadrado (1024x1024)</option>
                        <option value="1792x1024">Horizontal (1792x1024)</option>
                        <option value="1024x1792">Vertical (1024x1792)</option>
                        <option value="512x512">Pequeño (512x512)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category">Categoría de Uso</label>
                    <select name="category" id="category">
                        <option value="productos">Productos</option>
                        <option value="logos">Logos</option>
                        <option value="banners">Banners</option>
                        <option value="backgrounds">Fondos</option>
                        <option value="marketing">Marketing</option>
                        <option value="social">Redes Sociales</option>
                        <option value="web">Web Design</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>
                
                <button type="submit" name="generate_image" class="generate-btn">
                    <i class="fas fa-magic"></i> Generar Imagen Placeholder
                </button>
            </form>
        </div>

        <div class="recent-images">
            <h2><i class="fas fa-images"></i> Imágenes Generadas Recientemente</h2>
            
            <?php if (empty($recent_images)): ?>
                <p>No hay imágenes generadas aún. ¡Genera tu primera imagen placeholder!</p>
            <?php else: ?>
                <div class="images-grid">
                    <?php foreach ($recent_images as $image): ?>
                        <div class="image-card">
                            <div class="image-placeholder">
                                <i class="fas fa-image fa-3x" style="color: #9ca3af;"></i>
                            </div>
                            <div class="image-info">
                                <h4><?php echo htmlspecialchars($image['prompt']); ?></h4>
                                <div class="image-meta">
                                    <strong>Archivo:</strong> <?php echo htmlspecialchars($image['filename']); ?>
                                </div>
                                <div class="image-meta">
                                    <strong>Estilo:</strong> <?php echo ucfirst($image['style']); ?> | 
                                    <strong>Tamaño:</strong> <?php echo $image['size']; ?>
                                </div>
                                <div class="image-meta">
                                    <span class="category-badge"><?php echo ucfirst($image['category']); ?></span>
                                </div>
                                <div class="image-meta">
                                    <strong>Generado:</strong> <?php echo date('d/m/Y H:i', strtotime($image['created_at'])); ?> por <?php echo htmlspecialchars($image['username']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-focus en el textarea al cargar
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('prompt').focus();
        });
        
        // Contador de caracteres para el prompt
        const promptTextarea = document.getElementById('prompt');
        if (promptTextarea) {
            promptTextarea.addEventListener('input', function() {
                const length = this.value.length;
                if (length > 500) {
                    this.style.borderColor = '#f87171';
                } else {
                    this.style.borderColor = '#e5e7eb';
                }
            });
        }
    </script>

<?php include '../includes/footer.php'; ?>