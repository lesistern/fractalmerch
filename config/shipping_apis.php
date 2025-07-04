<?php
/**
 * Configuración de APIs de envío
 * Uber Direct y Andreani para Argentina
 */

// Configuración de Uber Direct
define('UBER_DIRECT_CLIENT_ID', getenv('UBER_DIRECT_CLIENT_ID') ?: 'tu_client_id_aqui');
define('UBER_DIRECT_CLIENT_SECRET', getenv('UBER_DIRECT_CLIENT_SECRET') ?: 'tu_client_secret_aqui');
define('UBER_DIRECT_SANDBOX', getenv('UBER_DIRECT_SANDBOX') !== 'false'); // true para sandbox, false para producción

// URLs de Uber Direct
define('UBER_DIRECT_BASE_URL', UBER_DIRECT_SANDBOX ? 'https://sandbox-api.uber.com' : 'https://api.uber.com');
define('UBER_DIRECT_AUTH_URL', UBER_DIRECT_BASE_URL . '/oauth/v2/token');
define('UBER_DIRECT_DELIVERIES_URL', UBER_DIRECT_BASE_URL . '/v1/deliveries');
define('UBER_DIRECT_QUOTES_URL', UBER_DIRECT_BASE_URL . '/v1/deliveries/quotes');

// Configuración de Andreani
define('ANDREANI_API_KEY', getenv('ANDREANI_API_KEY') ?: 'tu_api_key_aqui');
define('ANDREANI_CLIENT_ID', getenv('ANDREANI_CLIENT_ID') ?: 'tu_client_id_aqui');
define('ANDREANI_CLIENT_SECRET', getenv('ANDREANI_CLIENT_SECRET') ?: 'tu_client_secret_aqui');
define('ANDREANI_SANDBOX', getenv('ANDREANI_SANDBOX') !== 'false'); // true para sandbox, false para producción

// URLs de Andreani
define('ANDREANI_BASE_URL', ANDREANI_SANDBOX ? 'https://sandbox.andreani.com' : 'https://api.andreani.com');
define('ANDREANI_AUTH_URL', ANDREANI_BASE_URL . '/oauth/token');
define('ANDREANI_TARIFAS_URL', ANDREANI_BASE_URL . '/v1/tarifas');
define('ANDREANI_ENVIOS_URL', ANDREANI_BASE_URL . '/v1/envios');
define('ANDREANI_SUCURSALES_URL', ANDREANI_BASE_URL . '/v1/sucursales');

// Configuración de empresa (para ambos servicios)
define('EMPRESA_NOMBRE', 'Sublime Personalización');
define('EMPRESA_TELEFONO', '+54 376 1234-5678');
define('EMPRESA_EMAIL', 'info@sublime.com');

// Dirección de pickup/origen (tu taller) - Posadas, Misiones
// Google Maps Plus Code: H3C9+4RF Posadas, Misiones
// Coordenadas: 27°25'46.9"S 55°55'49.6"W
define('PICKUP_ADDRESS', [
    'street' => 'Calle Sargento Acosta 3947',
    'city' => 'Posadas',
    'state' => 'Misiones',
    'postal_code' => '3300',
    'country' => 'AR',
    'latitude' => -27.4297, // 27°25'46.9"S
    'longitude' => -55.9304, // 55°55'49.6"W
    'plus_code' => 'H3C9+4RF'
]);

// Configuración de productos para envío
define('SHIPPING_WEIGHT_PER_ITEM', 0.3); // kg por producto promedio
define('SHIPPING_DIMENSIONS', [
    'length' => 25, // cm
    'width' => 20,  // cm
    'height' => 5   // cm
]);

// Configuración de precios
define('SHIPPING_MARKUP_PERCENTAGE', 0.1); // 10% de markup sobre el costo del envío
define('FREE_SHIPPING_THRESHOLD', 15000); // Envío gratis por encima de este monto

// Configuración de webhooks
define('WEBHOOK_SECRET', getenv('WEBHOOK_SECRET') ?: 'tu_webhook_secret_aqui');
define('WEBHOOK_UBER_URL', '/webhook/uber-direct');
define('WEBHOOK_ANDREANI_URL', '/webhook/andreani');

// Configuración de timeout para APIs
define('API_TIMEOUT', 30); // segundos
define('API_CONNECT_TIMEOUT', 10); // segundos

// Logs
define('SHIPPING_LOG_FILE', __DIR__ . '/../logs/shipping.log');
define('SHIPPING_DEBUG', getenv('SHIPPING_DEBUG') === 'true');

/**
 * Función para validar que las credenciales estén configuradas
 */
function validate_shipping_config() {
    $errors = [];
    
    // Validar Uber Direct
    if (UBER_DIRECT_CLIENT_ID === 'tu_client_id_aqui') {
        $errors[] = 'Uber Direct Client ID no configurado';
    }
    if (UBER_DIRECT_CLIENT_SECRET === 'tu_client_secret_aqui') {
        $errors[] = 'Uber Direct Client Secret no configurado';
    }
    
    // Validar Andreani
    if (ANDREANI_API_KEY === 'tu_api_key_aqui') {
        $errors[] = 'Andreani API Key no configurado';
    }
    if (ANDREANI_CLIENT_ID === 'tu_client_id_aqui') {
        $errors[] = 'Andreani Client ID no configurado';
    }
    if (ANDREANI_CLIENT_SECRET === 'tu_client_secret_aqui') {
        $errors[] = 'Andreani Client Secret no configurado';
    }
    
    return $errors;
}

/**
 * Función para logging de envíos
 */
function log_shipping($message, $level = 'INFO', $context = []) {
    if (!SHIPPING_DEBUG && $level === 'DEBUG') {
        return;
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] [$level] $message $contextStr" . PHP_EOL;
    
    // Crear directorio de logs si no existe
    $logDir = dirname(SHIPPING_LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents(SHIPPING_LOG_FILE, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Función para obtener headers HTTP comunes
 */
function get_http_headers($additional_headers = []) {
    $default_headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: Sublime-Personalizacion/1.0'
    ];
    
    return array_merge($default_headers, $additional_headers);
}

/**
 * Función para hacer peticiones HTTP con manejo de errores
 */
function make_http_request($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => API_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => API_CONNECT_TIMEOUT,
        CURLOPT_HTTPHEADER => get_http_headers($headers),
        CURLOPT_SSL_VERIFYPEER => !UBER_DIRECT_SANDBOX && !ANDREANI_SANDBOX,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3
    ]);
    
    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
            }
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
            }
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        log_shipping("HTTP Error: $error", 'ERROR', ['url' => $url, 'method' => $method]);
        throw new Exception("HTTP Error: $error");
    }
    
    $decodedResponse = json_decode($response, true);
    
    if ($httpCode >= 400) {
        log_shipping("HTTP $httpCode Error", 'ERROR', [
            'url' => $url,
            'method' => $method,
            'response' => $response
        ]);
        throw new Exception("HTTP $httpCode Error: " . ($decodedResponse['message'] ?? $response));
    }
    
    return [
        'status_code' => $httpCode,
        'body' => $decodedResponse ?: $response
    ];
}

/**
 * Función para calcular peso total del envío
 */
function calculate_shipping_weight($items) {
    $totalWeight = 0;
    foreach ($items as $item) {
        $totalWeight += SHIPPING_WEIGHT_PER_ITEM * $item['quantity'];
    }
    return $totalWeight;
}

/**
 * Función para calcular dimensiones del paquete
 */
function calculate_package_dimensions($items) {
    $totalItems = array_sum(array_column($items, 'quantity'));
    
    // Aproximación simple: aumentar altura según cantidad
    $dimensions = SHIPPING_DIMENSIONS;
    $dimensions['height'] = max(5, $dimensions['height'] * ceil($totalItems / 3));
    
    return $dimensions;
}

/**
 * Función para formatear dirección para APIs
 */
function format_address($address) {
    return [
        'street' => $address['street'] ?? '',
        'city' => $address['city'] ?? '',
        'state' => $address['state'] ?? $address['province'] ?? '',
        'postal_code' => $address['postal_code'] ?? $address['postalCode'] ?? '',
        'country' => $address['country'] ?? 'AR'
    ];
}

/**
 * Función para validar dirección
 */
function validate_address($address) {
    $required = ['street', 'city', 'state', 'postal_code'];
    $errors = [];
    
    foreach ($required as $field) {
        if (empty($address[$field])) {
            $errors[] = "Campo requerido: $field";
        }
    }
    
    // Validar código postal argentino
    if (!empty($address['postal_code']) && !preg_match('/^[A-Z]?\d{4}[A-Z]{0,3}$/', $address['postal_code'])) {
        $errors[] = 'Código postal inválido';
    }
    
    return $errors;
}

/**
 * Función para determinar si usar Uber Direct o Andreani
 */
function get_preferred_shipping_method($address, $items) {
    $weight = calculate_shipping_weight($items);
    $dimensions = calculate_package_dimensions($items);
    
    // Lógica para determinar el mejor método
    // Uber Direct para entregas rápidas en grandes ciudades
    // Andreani para envíos nacionales desde Misiones
    
    // Códigos postales de grandes ciudades donde Uber Direct está disponible
    $uber_available_postcodes = [
        // CABA
        '1000', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009',
        '1010', '1011', '1012', '1013', '1014', '1015', '1016', '1017', '1018', '1019',
        '1020', '1021', '1022', '1023', '1024', '1025', '1026', '1027', '1028', '1029',
        '1030', '1031', '1032', '1033', '1034', '1035', '1036', '1037', '1038', '1039',
        '1040', '1041', '1042', '1043', '1044', '1045', '1046', '1047', '1048', '1049',
        // Posadas y alrededores (donde estamos ubicados)
        '3300', '3301', '3302', '3303', '3304', '3305',
        // Córdoba Capital
        '5000', '5001', '5002', '5003', '5004', '5005',
        // Rosario
        '2000', '2001', '2002', '2003', '2004', '2005'
    ];
    
    $postal_code = substr($address['postal_code'], 0, 4);
    
    if (in_array($postal_code, $uber_available_postcodes)) {
        return 'uber_direct';
    }
    
    return 'andreani';
}
?>