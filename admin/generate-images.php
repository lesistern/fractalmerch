<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Verificar que el usuario esté logueado y sea admin
if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al generador de imágenes');
    redirect('login.php');
}

$page_title = "🎨 Generador de Imágenes - Panel Admin";

// Procesar formulario de generación
if ($_POST && isset($_POST['generate_image'])) {
    $prompt = sanitize_input($_POST['prompt']);
    $style = sanitize_input($_POST['style']);
    $size = sanitize_input($_POST['size']);
    $category = sanitize_input($_POST['category']);
    
    // Validar entrada
    $validation_errors = validate_image_generation_input($prompt, $style, $size, $category);
    
    if (empty($validation_errors)) {
        try {
            // Crear directorio si no existe
            $output_dir = '../mcp-media-generator/generated-media/';
            if (!file_exists($output_dir)) {
                mkdir($output_dir, 0755, true);
            }
            
            // Generar nombre único del archivo
            $timestamp = time();
            $unique_id = uniqid();
            $filename = "admin_{$category}_{$timestamp}_{$unique_id}.txt";
            $filepath = $output_dir . $filename;
            
            // Crear archivo placeholder mock
            $mock_content = "=== IMAGEN PLACEHOLDER GENERADA ===\n\n";
            $mock_content .= "Prompt: {$prompt}\n";
            $mock_content .= "Estilo: {$style}\n";
            $mock_content .= "Tamaño: {$size}\n";
            $mock_content .= "Categoría: {$category}\n";
            $mock_content .= "Usuario: {$_SESSION['username']}\n";
            $mock_content .= "Generado: " . date('Y-m-d H:i:s') . "\n\n";
            $mock_content .= "Esta es una imagen placeholder generada por IA.\n";
            $mock_content .= "En producción con APIs configuradas, sería una imagen real PNG/JPG.\n\n";
            $mock_content .= "Para generar imágenes reales:\n";
            $mock_content .= "1. Configurar OPENAI_API_KEY o STABILITY_API_KEY\n";
            $mock_content .= "2. El sistema detectará automáticamente las APIs disponibles\n";
            
            if (file_put_contents($filepath, $mock_content)) {
                // Guardar en base de datos usando la función helper
                $image_id = save_generated_image($filename, $prompt, $style, $size, $category, $_SESSION['user_id'], false);
                
                if ($image_id) {
                    flash_message('success', "Imagen placeholder generada exitosamente. ID: {$image_id}");
                } else {
                    flash_message('warning', "Archivo creado pero error al guardar en base de datos");
                }
            } else {
                flash_message('error', "Error al crear el archivo placeholder");
            }
            
            redirect('admin/generate-images.php');
            
        } catch (Exception $e) {
            flash_message('error', "Error al generar imagen: " . $e->getMessage());
        }
    } else {
        // Mostrar errores de validación
        foreach ($validation_errors as $error) {
            flash_message('error', $error);
        }
    }
}

// Obtener imágenes generadas recientes usando la función helper
$recent_images = get_generated_images(10);

include 'admin-header.php';
?>

<style>
    .admin-generator-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    
    .generator-card {
        background: rgba(255,255,255,0.95);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        margin-bottom: 2rem;
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    .generator-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .generator-header h1 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    
    .generator-subtitle {
        color: #6b7280;
        font-size: 1.1rem;
    }
    
    .generator-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    
    .form-label {
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #374151;
        font-size: 1rem;
    }
    
    .form-input,
    .form-select,
    .form-textarea {
        padding: 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }
    
    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 120px;
        font-family: inherit;
    }
    
    .generate-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.25rem 2.5rem;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        grid-column: 1 / -1;
        justify-self: center;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .generate-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    
    .recent-images-section {
        background: rgba(255,255,255,0.95);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    .section-title {
        color: #1f2937;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .images-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    
    .image-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .image-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .image-placeholder {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        height: 150px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
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
    
    .category-badge {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        margin-top: 0.5rem;
    }
    
    
    .admin-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-bottom: 2rem;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    @media (max-width: 768px) {
        .generator-form {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .admin-actions {
            flex-direction: column;
        }
        
        .images-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="admin-generator-container">
    <!-- Acciones admin -->
    <div class="admin-actions">
        <a href="placeholder-gallery.php" class="btn btn-primary">
            <i class="fas fa-images"></i> Ver Galería Completa
        </a>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>

    <!-- Generador principal -->
    <div class="generator-card">
        <div class="generator-header">
            <h1><i class="fas fa-magic"></i> Generador de Imágenes IA</h1>
            <p class="generator-subtitle">Crea imágenes placeholder personalizadas para tu proyecto</p>
        </div>
        
        <form method="POST" class="generator-form">
            <div class="form-group full-width">
                <label class="form-label" for="prompt">
                    <i class="fas fa-pencil-alt"></i> Descripción de la Imagen *
                </label>
                <textarea 
                    name="prompt" 
                    id="prompt" 
                    class="form-textarea" 
                    placeholder="Ejemplo: Logo moderno para empresa tech con colores azul y blanco, diseño minimalista y profesional..."
                    required
                ></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="style">
                    <i class="fas fa-palette"></i> Estilo Visual
                </label>
                <select name="style" id="style" class="form-select">
                    <option value="realistic">🎯 Realista</option>
                    <option value="digital-art">🎨 Arte Digital</option>
                    <option value="photographic">📸 Fotográfico</option>
                    <option value="artistic">🖼️ Artístico</option>
                    <option value="cinematic">🎬 Cinematográfico</option>
                    <option value="cartoon">🎭 Cartoon</option>
                    <option value="anime">🌸 Anime</option>
                    <option value="fantasy">✨ Fantasía</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="size">
                    <i class="fas fa-expand-arrows-alt"></i> Dimensiones
                </label>
                <select name="size" id="size" class="form-select">
                    <option value="1024x1024">⬜ Cuadrado (1024x1024)</option>
                    <option value="1792x1024">📱 Horizontal (1792x1024)</option>
                    <option value="1024x1792">📱 Vertical (1024x1792)</option>
                    <option value="512x512">🔸 Pequeño (512x512)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="category">
                    <i class="fas fa-tags"></i> Categoría de Uso
                </label>
                <select name="category" id="category" class="form-select">
                    <option value="productos">📦 Productos</option>
                    <option value="logos">🏷️ Logos</option>
                    <option value="banners">🎪 Banners</option>
                    <option value="backgrounds">🌄 Fondos</option>
                    <option value="marketing">📢 Marketing</option>
                    <option value="social">📱 Redes Sociales</option>
                    <option value="web">🌐 Web Design</option>
                    <option value="otros">📂 Otros</option>
                </select>
            </div>
            
            <button type="submit" name="generate_image" class="generate-button">
                <i class="fas fa-magic"></i> Generar Imagen Placeholder
            </button>
        </form>
    </div>

    <!-- Imágenes recientes -->
    <div class="recent-images-section">
        <h2 class="section-title">
            <i class="fas fa-clock"></i> Imágenes Generadas Recientemente
        </h2>
        
        <?php if (empty($recent_images)): ?>
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <i class="fas fa-image fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3>No hay imágenes generadas aún</h3>
                <p>¡Genera tu primera imagen placeholder usando el formulario de arriba!</p>
            </div>
        <?php else: ?>
            <div class="images-grid">
                <?php foreach ($recent_images as $image): ?>
                    <div class="image-card">
                        <div class="image-placeholder">
                            <i class="fas fa-image fa-2x" style="color: #9ca3af;"></i>
                        </div>
                        <div class="image-title"><?php echo htmlspecialchars($image['prompt']); ?></div>
                        <div class="image-meta">
                            <strong>Estilo:</strong> <?php echo ucfirst($image['style']); ?>
                        </div>
                        <div class="image-meta">
                            <strong>Tamaño:</strong> <?php echo $image['size']; ?>
                        </div>
                        <div class="image-meta">
                            <strong>Generado:</strong> <?php echo date('d/m/Y H:i', strtotime($image['created_at'])); ?>
                        </div>
                        <span class="category-badge">
                            <?php echo ucfirst($image['category']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Auto-focus en el textarea
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('prompt').focus();
    });
    
    // Contador de caracteres y validación
    const promptTextarea = document.getElementById('prompt');
    if (promptTextarea) {
        promptTextarea.addEventListener('input', function() {
            const length = this.value.length;
            
            // Cambiar borde según longitud
            if (length > 500) {
                this.style.borderColor = '#f87171';
            } else if (length > 200) {
                this.style.borderColor = '#fbbf24';
            } else {
                this.style.borderColor = '#667eea';
            }
        });
    }
    
    // Animación del botón
    const generateBtn = document.querySelector('.generate-button');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
            this.disabled = true;
        });
    }
</script>

        </div>
    </main>

    <footer class="admin-footer">
        <div class="container">
            <p>&copy; 2025 <?php echo SITE_NAME; ?> - Panel de Administración</p>
        </div>
    </footer>

    <style>
    .admin-footer {
        background: rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        color: rgba(255,255,255,0.8);
        text-align: center;
        padding: 1rem 0;
        margin-top: 2rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    
    .admin-footer p {
        margin: 0;
        font-size: 0.9rem;
    }
    </style>
</body>
</html>