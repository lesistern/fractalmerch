<?php
require_once __DIR__ . '/../../config/shipping_apis.php';

/**
 * Clase para integración con Uber Direct API
 * Documentación: https://developer.uber.com/docs/deliveries/overview
 */
class UberDirectAPI {
    private $accessToken;
    private $tokenExpiry;

    public function __construct() {
        $this->accessToken = null;
        $this->tokenExpiry = null;
    }

    /**
     * Autenticación OAuth 2.0 con Uber Direct
     */
    public function authenticate() {
        try {
            $response = make_http_request(
                UBER_DIRECT_AUTH_URL,
                'POST',
                [
                    'client_id' => UBER_DIRECT_CLIENT_ID,
                    'client_secret' => UBER_DIRECT_CLIENT_SECRET,
                    'grant_type' => 'client_credentials',
                    'scope' => 'eats.deliveries'
                ],
                ['Content-Type: application/x-www-form-urlencoded']
            );

            if (isset($response['body']['access_token'])) {
                $this->accessToken = $response['body']['access_token'];
                $this->tokenExpiry = time() + ($response['body']['expires_in'] ?? 3600);
                
                log_shipping('Uber Direct authenticated successfully', 'INFO');
                return true;
            }

            throw new Exception('No access token received');

        } catch (Exception $e) {
            log_shipping('Uber Direct authentication failed: ' . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Verificar si el token está válido
     */
    private function isTokenValid() {
        return $this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry;
    }

    /**
     * Asegurar autenticación válida
     */
    private function ensureAuthenticated() {
        if (!$this->isTokenValid()) {
            if (!$this->authenticate()) {
                throw new Exception('Failed to authenticate with Uber Direct');
            }
        }
    }

    /**
     * Obtener cotización de delivery
     */
    public function getQuote($pickupAddress, $dropoffAddress, $items = []) {
        try {
            $this->ensureAuthenticated();

            $quoteData = [
                'pickup_address' => $this->formatAddressForUber($pickupAddress),
                'dropoff_address' => $this->formatAddressForUber($dropoffAddress),
                'pickup_ready_dt' => date('c'), // ISO 8601 format
                'pickup_deadline_dt' => date('c', strtotime('+2 hours')),
                'dropoff_ready_dt' => date('c', strtotime('+30 minutes')),
                'dropoff_deadline_dt' => date('c', strtotime('+4 hours')),
                'manifest_items' => $this->formatItemsForUber($items)
            ];

            $response = make_http_request(
                UBER_DIRECT_QUOTES_URL,
                'POST',
                $quoteData,
                ['Authorization: Bearer ' . $this->accessToken]
            );

            if (isset($response['body']['quotes']) && !empty($response['body']['quotes'])) {
                $quote = $response['body']['quotes'][0]; // Tomar la primera cotización
                
                return [
                    'success' => true,
                    'quote_id' => $response['body']['id'],
                    'price' => $quote['fee'] ?? 0,
                    'currency' => $quote['currency_code'] ?? 'ARS',
                    'estimated_pickup' => $quote['pickup_eta'] ?? null,
                    'estimated_delivery' => $quote['dropoff_eta'] ?? null,
                    'duration_minutes' => $quote['duration'] ?? null,
                    'distance_km' => $quote['distance'] ?? null,
                    'raw_response' => $response['body']
                ];
            }

            return [
                'success' => false,
                'error' => 'No quotes available for this route'
            ];

        } catch (Exception $e) {
            log_shipping('Uber Direct quote error: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear orden de delivery
     */
    public function createDelivery($pickupAddress, $dropoffAddress, $customerInfo, $items = []) {
        try {
            $this->ensureAuthenticated();

            $deliveryData = [
                'pickup_address' => $this->formatAddressForUber($pickupAddress),
                'dropoff_address' => $this->formatAddressForUber($dropoffAddress),
                'pickup_ready_dt' => date('c'),
                'pickup_deadline_dt' => date('c', strtotime('+2 hours')),
                'dropoff_ready_dt' => date('c', strtotime('+30 minutes')),
                'dropoff_deadline_dt' => date('c', strtotime('+4 hours')),
                'manifest_items' => $this->formatItemsForUber($items),
                'pickup_instructions' => 'Retirar productos personalizados - ' . EMPRESA_NOMBRE,
                'dropoff_instructions' => 'Entregar al cliente',
                'pickup_contact' => [
                    'name' => EMPRESA_NOMBRE,
                    'phone' => [
                        'number' => EMPRESA_TELEFONO,
                        'sms_enabled' => true
                    ]
                ],
                'dropoff_contact' => [
                    'name' => $customerInfo['firstName'] . ' ' . $customerInfo['lastName'],
                    'phone' => [
                        'number' => $customerInfo['phone'],
                        'sms_enabled' => true
                    ]
                ],
                'external_id' => 'ORDER_' . time() . '_' . rand(1000, 9999)
            ];

            $response = make_http_request(
                UBER_DIRECT_DELIVERIES_URL,
                'POST',
                $deliveryData,
                ['Authorization: Bearer ' . $this->accessToken]
            );

            if (isset($response['body']['id'])) {
                log_shipping('Uber Direct delivery created successfully', 'INFO', [
                    'delivery_id' => $response['body']['id'],
                    'external_id' => $deliveryData['external_id']
                ]);

                return [
                    'success' => true,
                    'delivery_id' => $response['body']['id'],
                    'external_id' => $deliveryData['external_id'],
                    'status' => $response['body']['status'] ?? 'pending',
                    'tracking_url' => $response['body']['tracking_url'] ?? null,
                    'fee' => $response['body']['fee'] ?? 0,
                    'currency' => $response['body']['currency_code'] ?? 'ARS',
                    'raw_response' => $response['body']
                ];
            }

            throw new Exception('No delivery ID received');

        } catch (Exception $e) {
            log_shipping('Uber Direct delivery creation failed: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estado de delivery
     */
    public function getDeliveryStatus($deliveryId) {
        try {
            $this->ensureAuthenticated();

            $response = make_http_request(
                UBER_DIRECT_DELIVERIES_URL . '/' . $deliveryId,
                'GET',
                null,
                ['Authorization: Bearer ' . $this->accessToken]
            );

            if (isset($response['body']['id'])) {
                return [
                    'success' => true,
                    'delivery_id' => $response['body']['id'],
                    'status' => $response['body']['status'],
                    'driver_name' => $response['body']['courier']['name'] ?? null,
                    'driver_phone' => $response['body']['courier']['phone'] ?? null,
                    'driver_location' => [
                        'lat' => $response['body']['courier']['location']['lat'] ?? null,
                        'lng' => $response['body']['courier']['location']['lng'] ?? null
                    ],
                    'tracking_url' => $response['body']['tracking_url'] ?? null,
                    'pickup_eta' => $response['body']['pickup_eta'] ?? null,
                    'dropoff_eta' => $response['body']['dropoff_eta'] ?? null,
                    'raw_response' => $response['body']
                ];
            }

            throw new Exception('Delivery not found');

        } catch (Exception $e) {
            log_shipping('Uber Direct status check failed: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancelar delivery
     */
    public function cancelDelivery($deliveryId, $reason = 'Customer request') {
        try {
            $this->ensureAuthenticated();

            $response = make_http_request(
                UBER_DIRECT_DELIVERIES_URL . '/' . $deliveryId . '/cancel',
                'POST',
                ['reason' => $reason],
                ['Authorization: Bearer ' . $this->accessToken]
            );

            log_shipping('Uber Direct delivery cancelled', 'INFO', [
                'delivery_id' => $deliveryId,
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'delivery_id' => $deliveryId,
                'status' => 'cancelled',
                'raw_response' => $response['body']
            ];

        } catch (Exception $e) {
            log_shipping('Uber Direct cancellation failed: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Formatear dirección para Uber API
     */
    private function formatAddressForUber($address) {
        return [
            'street_address' => [$address['street']],
            'city' => $address['city'],
            'state' => $address['state'],
            'zip_code' => $address['postal_code'],
            'country' => $address['country'] ?? 'AR'
        ];
    }

    /**
     * Formatear items para Uber API
     */
    private function formatItemsForUber($items) {
        $manifestItems = [];
        
        foreach ($items as $item) {
            $manifestItems[] = [
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'size' => 'medium', // small, medium, large
                'price' => ($item['price'] * 100), // En centavos
                'dimensions' => [
                    'length' => SHIPPING_DIMENSIONS['length'],
                    'width' => SHIPPING_DIMENSIONS['width'],
                    'height' => SHIPPING_DIMENSIONS['height']
                ],
                'weight' => SHIPPING_WEIGHT_PER_ITEM * $item['quantity']
            ];
        }

        return $manifestItems;
    }

    /**
     * Procesar webhook de Uber Direct
     */
    public function processWebhook($payload, $signature) {
        try {
            // Verificar signature del webhook
            $expectedSignature = hash_hmac('sha256', $payload, WEBHOOK_SECRET);
            
            if (!hash_equals($expectedSignature, $signature)) {
                throw new Exception('Invalid webhook signature');
            }

            $data = json_decode($payload, true);
            
            if (!$data) {
                throw new Exception('Invalid JSON payload');
            }

            log_shipping('Uber Direct webhook received', 'INFO', [
                'event_type' => $data['event_type'] ?? 'unknown',
                'delivery_id' => $data['data']['id'] ?? 'unknown'
            ]);

            // Procesar diferentes tipos de eventos
            switch ($data['event_type'] ?? '') {
                case 'deliveries.delivery_status':
                    return $this->handleStatusUpdate($data['data']);
                case 'deliveries.courier_update':
                    return $this->handleCourierUpdate($data['data']);
                default:
                    log_shipping('Unknown webhook event type', 'WARNING', $data);
                    return ['success' => false, 'error' => 'Unknown event type'];
            }

        } catch (Exception $e) {
            log_shipping('Webhook processing failed: ' . $e->getMessage(), 'ERROR');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Manejar actualización de estado
     */
    private function handleStatusUpdate($data) {
        // Aquí puedes actualizar la base de datos con el nuevo estado
        // Ejemplo: update orders set delivery_status = ? where delivery_id = ?
        
        return ['success' => true, 'message' => 'Status updated'];
    }

    /**
     * Manejar actualización de courier
     */
    private function handleCourierUpdate($data) {
        // Aquí puedes actualizar información del repartidor
        
        return ['success' => true, 'message' => 'Courier info updated'];
    }

    /**
     * Verificar disponibilidad del servicio
     */
    public function isServiceAvailable($address) {
        // Uber Direct disponible en principales ciudades argentinas
        // Incluye Posadas (Misiones) donde estamos ubicados
        $availablePostalCodes = [
            // CABA y GBA
            '1000', '1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008', '1009',
            '1010', '1011', '1012', '1013', '1014', '1015', '1016', '1017', '1018', '1019',
            '1020', '1021', '1022', '1023', '1024', '1025', '1026', '1027', '1028', '1029',
            '1030', '1031', '1032', '1033', '1034', '1035', '1036', '1037', '1038', '1039',
            '1040', '1041', '1042', '1043', '1044', '1045', '1046', '1047', '1048', '1049',
            // Posadas, Misiones (nuestra ubicación)
            '3300', '3301', '3302', '3303', '3304', '3305',
            // Córdoba Capital
            '5000', '5001', '5002', '5003', '5004', '5005',
            // Rosario, Santa Fe
            '2000', '2001', '2002', '2003', '2004', '2005',
            // La Plata
            '1900', '1901', '1902', '1903', '1904', '1905'
        ];

        $postalCode = substr($address['postal_code'], 0, 4);
        return in_array($postalCode, $availablePostalCodes);
    }
}
?>