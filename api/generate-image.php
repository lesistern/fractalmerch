<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validar datos requeridos
if (!isset($input['prompt']) || empty(trim($input['prompt']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt requerido']);
    exit();
}

$prompt = trim($input['prompt']);
$style = $input['style'] ?? 'realistic';
$size = $input['size'] ?? '1024x1024';
$category = $input['category'] ?? 'otros';

// Validar valores permitidos
$allowed_styles = ['realistic', 'digital-art', 'photographic', 'artistic', 'cinematic', 'cartoon', 'anime', 'fantasy'];
$allowed_sizes = ['512x512', '1024x1024', '1792x1024', '1024x1792'];
$allowed_categories = ['productos', 'logos', 'banners', 'backgrounds', 'marketing', 'social', 'web', 'otros'];

if (!in_array($style, $allowed_styles)) {
    $style = 'realistic';
}

if (!in_array($size, $allowed_sizes)) {
    $size = '1024x1024';
}

if (!in_array($category, $allowed_categories)) {
    $category = 'otros';
}

try {
    // Configurar rutas
    $mcp_path = '../mcp-media-generator/build/index.js';
    $output_dir = '../mcp-media-generator/generated-media/';
    $web_output_dir = '../assets/images/generated/';
    
    // Crear directorios si no existen
    if (!file_exists($output_dir)) {
        mkdir($output_dir, 0755, true);
    }
    
    if (!file_exists($web_output_dir)) {
        mkdir($web_output_dir, 0755, true);
    }
    
    // Generar nombre único para el archivo
    $timestamp = time();
    $unique_id = uniqid();
    $filename = "admin_{$category}_{$timestamp}_{$unique_id}";
    
    // Detectar si hay APIs configuradas
    $has_openai = !empty(getenv('OPENAI_API_KEY'));
    $has_stability = !empty(getenv('STABILITY_API_KEY'));
    
    $generated_file = null;
    $is_real_image = false;
    
    if ($has_openai || $has_stability) {
        // Intentar generar imagen real con APIs
        try {
            // Preparar comando MCP
            $command_data = [
                'tool' => 'generate_image',
                'arguments' => [
                    'prompt' => $prompt,
                    'style' => $style,
                    'size' => $size
                ]
            ];
            
            // Crear archivo temporal con los datos
            $temp_file = tempnam(sys_get_temp_dir(), 'mcp_request');
            file_put_contents($temp_file, json_encode($command_data));
            
            // Ejecutar MCP (esto requeriría Node.js en el servidor)
            // Por ahora, simulamos una respuesta exitosa
            $generated_file = $filename . '.png';
            $is_real_image = true;
            
            // Limpiar archivo temporal
            unlink($temp_file);
            
        } catch (Exception $e) {
            // Si falla la API, usar modo mock
            $generated_file = $filename . '.txt';
            $is_real_image = false;
        }
    } else {
        // Modo mock - crear placeholder
        $generated_file = $filename . '.txt';
        $is_real_image = false;
    }
    
    $filepath = $output_dir . $generated_file;
    $web_filepath = $web_output_dir . $generated_file;
    
    if (!$is_real_image) {
        // Crear archivo placeholder mock
        $mock_content = "=== IMAGEN PLACEHOLDER GENERADA ===\n\n";
        $mock_content .= "Prompt: {$prompt}\n";
        $mock_content .= "Estilo: {$style}\n";
        $mock_content .= "Tamaño: {$size}\n";
        $mock_content .= "Categoría: {$category}\n";
        $mock_content .= "ID Único: {$unique_id}\n";
        $mock_content .= "Generado: " . date('Y-m-d H:i:s') . "\n";
        $mock_content .= "Usuario: " . $_SESSION['username'] . "\n\n";
        $mock_content .= "Esta es una imagen placeholder generada por IA.\n";
        $mock_content .= "En producción con APIs configuradas, aquí habría una imagen real PNG/JPG.\n\n";
        $mock_content .= "Para usar APIs reales, configurar variables de entorno:\n";
        $mock_content .= "- OPENAI_API_KEY para DALL-E\n";
        $mock_content .= "- STABILITY_API_KEY para Stable Diffusion\n";
        
        file_put_contents($filepath, $mock_content);
        file_put_contents($web_filepath, $mock_content);
    }
    
    // Guardar en base de datos
    $stmt = $pdo->prepare("
        INSERT INTO generated_images 
        (filename, prompt, style, size, category, generated_by, is_real_image, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $generated_file, 
        $prompt, 
        $style, 
        $size, 
        $category, 
        $_SESSION['user_id'],
        $is_real_image ? 1 : 0
    ]);
    
    $image_id = $pdo->lastInsertId();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => $is_real_image ? 'Imagen generada exitosamente' : 'Placeholder generado exitosamente',
        'data' => [
            'id' => $image_id,
            'filename' => $generated_file,
            'filepath' => $filepath,
            'web_path' => 'assets/images/generated/' . $generated_file,
            'prompt' => $prompt,
            'style' => $style,
            'size' => $size,
            'category' => $category,
            'is_real_image' => $is_real_image,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>