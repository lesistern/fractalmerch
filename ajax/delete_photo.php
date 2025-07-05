<?php
/**
 * AJAX endpoint para eliminar fotos de perfil
 */

session_start();
require_once '../config/database.php';
require_once '../includes/photo_uploader.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipo de foto no especificado']);
    exit;
}

$user_id = $_SESSION['user_id'];
$photo_type = $input['type']; // 'profile' o 'cover'

// Validar tipo de foto
if (!in_array($photo_type, ['profile', 'cover'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipo de foto inválido']);
    exit;
}

try {
    $uploader = new PhotoUploader($pdo);
    $result = $uploader->deletePhoto($user_id, $photo_type);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>