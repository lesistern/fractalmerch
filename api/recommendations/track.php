<?php
/**
 * API Endpoint - Track Events
 * Tracking de eventos para el sistema de recomendaciones
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';
require_once '../../includes/RecommendationEngine.php';

try {
    // Solo permitir POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Obtener datos del tracking
    $input = file_get_contents('php://input');
    $trackingData = null;
    
    // Verificar si viene como FormData (sendBeacon) o JSON
    if (isset($_POST['tracking_data'])) {
        $trackingData = json_decode($_POST['tracking_data'], true);
    } else {
        $trackingData = json_decode($input, true);
    }
    
    if (!$trackingData) {
        throw new Exception('Invalid tracking data');
    }
    
    // Validar campos requeridos
    $eventType = $trackingData['event_type'] ?? '';
    $sessionId = $trackingData['session_id'] ?? '';
    $userId = $trackingData['user_id'] ?? null;
    $data = $trackingData['data'] ?? [];
    
    if (empty($eventType) || empty($sessionId)) {
        throw new Exception('Missing required fields: event_type, session_id');
    }
    
    // Inicializar recommendation engine
    $engine = new RecommendationEngine($pdo, $sessionId, $userId);
    
    // Procesar según tipo de evento
    switch ($eventType) {
        case 'product_view':
            $productId = $data['product_id'] ?? null;
            $sourcePage = $data['source_page'] ?? 'unknown';
            
            if ($productId) {
                $engine->trackProductView($productId, $sourcePage);
            }
            break;
            
        case 'product_view_duration':
            $productId = $data['product_id'] ?? null;
            $duration = intval($data['duration'] ?? 0);
            
            if ($productId && $duration > 0) {
                $engine->trackProductView($productId, 'duration_update', $duration);
            }
            break;
            
        case 'add_to_cart':
            $productId = $data['product_id'] ?? null;
            $variantDetails = $data['variant_details'] ?? null;
            $quantity = intval($data['quantity'] ?? 1);
            
            if ($productId) {
                $engine->trackCartAddition($productId, $variantDetails, $quantity);
            }
            break;
            
        case 'product_click':
        case 'recommendation_click':
        case 'recommendation_impression':
        case 'recommendation_conversion':
            // Estos eventos se almacenan en una tabla de analytics general
            $stmt = $pdo->prepare("
                INSERT INTO analytics_events (
                    event_type, 
                    session_id, 
                    user_id, 
                    event_data, 
                    created_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $eventType,
                $sessionId,
                $userId,
                json_encode($data)
            ]);
            break;
            
        case 'scroll_depth':
            // Evento de engagement - almacenar en analytics
            $stmt = $pdo->prepare("
                INSERT INTO analytics_events (
                    event_type, 
                    session_id, 
                    user_id, 
                    event_data, 
                    created_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $eventType,
                $sessionId,
                $userId,
                json_encode($data)
            ]);
            break;
            
        default:
            error_log("Unknown event type: " . $eventType);
            break;
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'event_type' => $eventType,
        'tracked_at' => date('c')
    ]);
    
} catch (Exception $e) {
    error_log("Tracking API Error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Crear tabla de analytics si no existe
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS analytics_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(100) NOT NULL,
            session_id VARCHAR(255) NOT NULL,
            user_id INT NULL,
            event_data JSON NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_analytics_type (event_type),
            INDEX idx_analytics_session (session_id),
            INDEX idx_analytics_user (user_id),
            INDEX idx_analytics_date (created_at)
        )
    ");
} catch (PDOException $e) {
    // Silencioso si la tabla ya existe
}
?>