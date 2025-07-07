<?php
// Script simplificado que NO requiere extensión GD
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function cleanOldTempFiles($tempDir) {
    if (!is_dir($tempDir)) return 0;
    
    // SECURITY: Validar directorio permitido
    $allowedDir = realpath('assets/images/temp/');
    $actualDir = realpath($tempDir);
    
    if ($actualDir !== $allowedDir) {
        error_log("SECURITY: Attempt to access unauthorized directory: $tempDir");
        return 0;
    }
    
    $files = glob($tempDir . 'temp_*.dat');
    $oneHourAgo = time() - 3600;
    $cleaned = 0;
    
    foreach ($files as $file) {
        // SECURITY: Validar que el archivo está en el directorio permitido
        $realFile = realpath($file);
        if (!$realFile || strpos($realFile, $allowedDir) !== 0) {
            continue;
        }
        
        if (is_file($file) && filemtime($file) < $oneHourAgo) {
            if (unlink($file)) {
                $cleaned++;
            }
        }
    }
    
    return $cleaned;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'error' => 'Only POST method allowed'], 405);
    }
    
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        jsonResponse(['success' => false, 'error' => 'No input data received']);
    }
    
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse(['success' => false, 'error' => 'Invalid JSON input: ' . json_last_error_msg()]);
    }
    
    // Manejar limpieza
    if (isset($input['action']) && $input['action'] === 'cleanup') {
        $tempDir = __DIR__ . '/assets/images/temp/';
        $cleaned = cleanOldTempFiles($tempDir);
        jsonResponse(['success' => true, 'message' => "Cleaned $cleaned temporary files"]);
    }
    
    if (!isset($input['imageData']) || !isset($input['filename'])) {
        jsonResponse(['success' => false, 'error' => 'Missing imageData or filename']);
    }
    
    $imageData = $input['imageData'];
    $originalFilename = $input['filename'];
    
    // Validar que sea un dataURL válido
    if (!preg_match('/^data:image\/(jpeg|jpg|png|gif|webp);base64,/', $imageData)) {
        jsonResponse(['success' => false, 'error' => 'Invalid image format']);
    }
    
    // Generar nombre único
    $timestamp = time();
    $randomId = bin2hex(random_bytes(8));
    $safeFilename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', pathinfo($originalFilename, PATHINFO_FILENAME));
    $safeFilename = substr($safeFilename, 0, 50);
    $tempFilename = "temp_{$timestamp}_{$randomId}_{$safeFilename}.dat";
    
    // Configurar rutas
    $tempDir = __DIR__ . '/assets/images/temp/';
    $tempPath = $tempDir . $tempFilename;
    
    // Crear directorio si no existe
    if (!is_dir($tempDir)) {
        if (!mkdir($tempDir, 0755, true)) {
            jsonResponse(['success' => false, 'error' => 'Failed to create temp directory']);
        }
    }
    
    // Verificar permisos
    if (!is_writable($tempDir)) {
        jsonResponse(['success' => false, 'error' => 'Temp directory is not writable']);
    }
    
    // Guardar el dataURL completo como archivo de texto
    $success = file_put_contents($tempPath, $imageData);
    
    if ($success === false) {
        jsonResponse(['success' => false, 'error' => 'Failed to save image data']);
    }
    
    // Verificar que se creó
    if (!file_exists($tempPath)) {
        jsonResponse(['success' => false, 'error' => 'File was not created']);
    }
    
    // URL para acceso (será procesada por JavaScript)
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
        'format' => 'dataurl',
        'method' => 'raw_storage',
        'timestamp' => $timestamp
    ]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => 'Server error: ' . $e->getMessage()], 500);
} catch (Error $e) {
    jsonResponse(['success' => false, 'error' => 'PHP error: ' . $e->getMessage()], 500);
}
?>