<?php
/**
 * Webhook endpoint para Andreani
 * Procesa notificaciones de estado de envío en tiempo real
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/shipping/ShippingManager.php';

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit();
    }

    // Obtener payload y signature
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_X_ANDREANI_SIGNATURE'] ?? '';

    if (empty($payload)) {
        http_response_code(400);
        echo json_encode(['error' => 'Empty payload']);
        exit();
    }

    // Crear instancia del gestor de envíos
    $shippingManager = new ShippingManager();

    // Procesar webhook
    $result = $shippingManager->processWebhook('andreani', $payload, $signature);

    if ($result['success']) {
        // Log del webhook exitoso
        log_shipping('Andreani webhook processed successfully', 'INFO', [
            'payload_size' => strlen($payload),
            'result' => $result
        ]);

        // Respuesta exitosa
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Webhook processed successfully'
        ]);
    } else {
        // Error en el procesamiento
        log_shipping('Andreani webhook processing failed', 'ERROR', [
            'error' => $result['error'],
            'payload_size' => strlen($payload)
        ]);

        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $result['error']
        ]);
    }

} catch (Exception $e) {
    // Error general
    log_shipping('Andreani webhook exception: ' . $e->getMessage(), 'ERROR', [
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
?>