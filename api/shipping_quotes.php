<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar OPTIONS request para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../includes/shipping/ShippingManager.php';

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }

    // Obtener datos del request
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST; // Fallback para form data
    }

    // Validar datos requeridos
    if (empty($input['address'])) {
        throw new Exception('Address is required');
    }

    if (empty($input['items']) || !is_array($input['items'])) {
        throw new Exception('Items array is required');
    }

    // Extraer datos
    $address = $input['address'];
    $items = $input['items'];

    // Validar estructura de la dirección
    $requiredAddressFields = ['street', 'city', 'state', 'postal_code'];
    foreach ($requiredAddressFields as $field) {
        if (empty($address[$field])) {
            throw new Exception("Address field '$field' is required");
        }
    }

    // Validar estructura de items
    foreach ($items as $index => $item) {
        if (empty($item['name']) || empty($item['price']) || empty($item['quantity'])) {
            throw new Exception("Item $index is missing required fields (name, price, quantity)");
        }
        
        if (!is_numeric($item['price']) || !is_numeric($item['quantity'])) {
            throw new Exception("Item $index price and quantity must be numeric");
        }
    }

    // Crear manager de envíos
    $shippingManager = new ShippingManager();

    // Obtener cotizaciones
    $result = $shippingManager->getShippingQuotes($address, $items);

    if (!$result['success']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to get shipping quotes',
            'details' => $result['errors'] ?? []
        ]);
        exit();
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'quotes' => $result['quotes'],
        'free_shipping_threshold' => $result['free_shipping_threshold'],
        'free_shipping_applies' => $result['free_shipping_applies'],
        'timestamp' => time()
    ]);

} catch (Exception $e) {
    // Log del error
    log_shipping('Shipping quotes API error: ' . $e->getMessage(), 'ERROR', [
        'input' => $_POST,
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);

    // Respuesta de error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'timestamp' => time()
    ]);
}
?>