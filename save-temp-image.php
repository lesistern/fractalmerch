<?php
// Deshabilitar display de errores para evitar HTML en respuesta JSON
ini_set('display_errors', 0);
error_reporting(0);

// Cargar funciones de seguridad
require_once __DIR__ . '/config/config.php';

// Headers necesarios
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Función para respuesta JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para limpiar archivos temporales antiguos
function cleanOldTempFiles($tempDir) {
    if (!is_dir($tempDir)) return 0;
    
    // Buscar archivos temporales en ambos formatos
    $webpFiles = glob($tempDir . 'temp_*.webp');
    $pngFiles = glob($tempDir . 'temp_*.png');
    $files = array_merge($webpFiles, $pngFiles);
    
    $oneHourAgo = time() - 3600;
    $cleaned = 0;
    
    foreach ($files as $file) {
        if (is_file($file) && filemtime($file) < $oneHourAgo) {
            if (unlink($file)) {
                $cleaned++;
            }
        }
    }
    
    return $cleaned;
}

try {
    // Validar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'error' => 'Only POST method allowed'], 405);
    }
    
    // Obtener datos del POST
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        jsonResponse(['success' => false, 'error' => 'No input data received']);
    }
    
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse(['success' => false, 'error' => 'Invalid JSON input: ' . json_last_error_msg()]);
    }
    
    // Verificar soporte de formatos
    $supportsWebP = function_exists('imagewebp');
    $supportsPNG = function_exists('imagepng');
    
    if (!$supportsWebP && !$supportsPNG) {
        jsonResponse(['success' => false, 'error' => 'Neither WebP nor PNG support available in PHP']);
    }
    
    // Determinar formato a usar
    $format = $supportsWebP ? 'webp' : 'png';
    $quality = $supportsWebP ? 90 : 9; // WebP: 0-100, PNG: 0-9
    
    // Manejar acción de limpieza
    if (isset($input['action']) && $input['action'] === 'cleanup') {
        $tempDir = __DIR__ . '/assets/images/temp/';
        $cleaned = cleanOldTempFiles($tempDir);
        jsonResponse(['success' => true, 'message' => "Cleaned $cleaned temporary files"]);
    }
    
    // Validar datos requeridos
    if (!isset($input['imageData']) || !isset($input['filename'])) {
        jsonResponse(['success' => false, 'error' => 'Missing imageData or filename']);
    }
    
    $imageData = $input['imageData'];
    $originalFilename = $input['filename'];
    
    // Validar formato de imagen
    if (!preg_match('/^data:image\/(jpeg|jpg|png|gif|webp);base64,/', $imageData)) {
        jsonResponse(['success' => false, 'error' => 'Invalid image format. Expected data URL with image/*']);
    }
    
    // Extraer datos base64
    $base64Data = preg_replace('/^data:image\/[^;]+;base64,/', '', $imageData);
    $decodedImage = base64_decode($base64Data);
    
    if ($decodedImage === false || empty($decodedImage)) {
        jsonResponse(['success' => false, 'error' => 'Failed to decode base64 image data']);
    }
    
    // Crear imagen desde string
    $image = imagecreatefromstring($decodedImage);
    if ($image === false) {
        jsonResponse(['success' => false, 'error' => 'Failed to create image from decoded data']);
    }
    
    // Generar nombre único y seguro
    $timestamp = time();
    $randomId = bin2hex(random_bytes(8));
    // Sanitizar nombre de archivo más estrictamente
    $originalFilename = validate_and_sanitize_input($originalFilename, 'string');
    $safeFilename = preg_replace('/[^a-zA-Z0-9\-_]/', '_', pathinfo($originalFilename, PATHINFO_FILENAME));
    $safeFilename = substr($safeFilename, 0, 30); // Limitar longitud más estrictamente
    $tempFilename = "temp_{$timestamp}_{$randomId}_{$safeFilename}.{$format}";
    
    // Configurar rutas
    $tempDir = __DIR__ . '/assets/images/temp/';
    $tempPath = $tempDir . $tempFilename;
    
    // Crear directorio si no existe
    if (!is_dir($tempDir)) {
        if (!mkdir($tempDir, 0755, true)) {
            imagedestroy($image);
            jsonResponse(['success' => false, 'error' => 'Failed to create temp directory']);
        }
    }
    
    // Verificar permisos de escritura
    if (!is_writable($tempDir)) {
        imagedestroy($image);
        jsonResponse(['success' => false, 'error' => 'Temp directory is not writable']);
    }
    
    // Convertir y guardar en el formato disponible
    if ($format === 'webp') {
        $success = imagewebp($image, $tempPath, $quality);
    } else {
        $success = imagepng($image, $tempPath, $quality);
    }
    
    $imageWidth = imagesx($image);
    $imageHeight = imagesy($image);
    
    // Limpiar memoria
    imagedestroy($image);
    
    if (!$success) {
        jsonResponse(['success' => false, 'error' => "Failed to save {$format} image"]);
    }
    
    // Verificar que el archivo se creó
    if (!file_exists($tempPath)) {
        jsonResponse(['success' => false, 'error' => 'File was not created on disk']);
    }
    
    // Generar URL relativa
    $tempUrl = '/proyecto/assets/images/temp/' . $tempFilename;
    
    // Limpiar archivos antiguos
    cleanOldTempFiles($tempDir);
    
    // Respuesta exitosa
    jsonResponse([
        'success' => true,
        'tempPath' => $tempUrl,
        'filename' => $tempFilename,
        'originalFilename' => $originalFilename,
        'fileSize' => filesize($tempPath),
        'dimensions' => ['width' => $imageWidth, 'height' => $imageHeight],
        'format' => $format,
        'webpSupported' => $supportsWebP,
        'timestamp' => $timestamp
    ]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => 'Server error: ' . $e->getMessage()], 500);
} catch (Error $e) {
    jsonResponse(['success' => false, 'error' => 'PHP error: ' . $e->getMessage()], 500);
}
?>