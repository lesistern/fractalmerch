<?php
require_once __DIR__ . '/../../config/shipping_apis.php';

/**
 * Clase para integración con Andreani API
 * Documentación: https://developers.andreani.com/
 */
class AndreaniAPI {
    private $accessToken;
    private $tokenExpiry;

    public function __construct() {
        $this->accessToken = null;
        $this->tokenExpiry = null;
    }

    /**
     * Autenticación con Andreani API
     */
    public function authenticate() {
        try {
            $response = make_http_request(
                ANDREANI_AUTH_URL,
                'POST',
                [
                    'grant_type' => 'client_credentials',
                    'client_id' => ANDREANI_CLIENT_ID,
                    'client_secret' => ANDREANI_CLIENT_SECRET,
                    'scope' => 'shipping'
                ],
                ['Content-Type: application/x-www-form-urlencoded']
            );

            if (isset($response['body']['access_token'])) {
                $this->accessToken = $response['body']['access_token'];
                $this->tokenExpiry = time() + ($response['body']['expires_in'] ?? 3600);
                
                log_shipping('Andreani authenticated successfully', 'INFO');
                return true;
            }

            throw new Exception('No access token received');

        } catch (Exception $e) {
            log_shipping('Andreani authentication failed: ' . $e->getMessage(), 'ERROR');
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
                throw new Exception('Failed to authenticate with Andreani');
            }
        }
    }

    /**
     * Calcular tarifa de envío
     */
    public function calculateShippingRate($destinationAddress, $items = []) {
        try {
            $this->ensureAuthenticated();

            $weight = calculate_shipping_weight($items);
            $dimensions = calculate_package_dimensions($items);
            
            $tarifaData = [
                'cpOrigen' => PICKUP_ADDRESS['postal_code'],
                'cpDestino' => $destinationAddress['postal_code'],
                'peso' => $weight,
                'volumen' => ($dimensions['length'] * $dimensions['width'] * $dimensions['height']) / 1000, // dm³
                'categoria' => 'PAQUETE',
                'contrato' => 'ESTANDAR',
                'modalidad' => 'DOMICILIO'
            ];

            $response = make_http_request(
                ANDREANI_TARIFAS_URL,
                'POST',
                $tarifaData,
                [
                    'Authorization: Bearer ' . $this->accessToken,
                    'X-API-Key: ' . ANDREANI_API_KEY
                ]
            );

            if (isset($response['body']['tarifas']) && !empty($response['body']['tarifas'])) {
                $tarifa = $response['body']['tarifas'][0];
                
                return [
                    'success' => true,
                    'price' => $tarifa['precio'],
                    'currency' => 'ARS',
                    'service_type' => $tarifa['servicio'],
                    'estimated_days' => $tarifa['plazoEntrega'] ?? null,
                    'service_description' => $tarifa['descripcion'] ?? '',
                    'raw_response' => $response['body']
                ];
            }

            return [
                'success' => false,
                'error' => 'No shipping rates available for this destination'
            ];

        } catch (Exception $e) {
            log_shipping('Andreani rate calculation error: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear envío
     */
    public function createShipment($destinationAddress, $customerInfo, $items = [], $serviceType = 'ESTANDAR') {
        try {
            $this->ensureAuthenticated();

            $weight = calculate_shipping_weight($items);
            $dimensions = calculate_package_dimensions($items);
            $externalId = 'SHIP_' . time() . '_' . rand(1000, 9999);

            $shipmentData = [
                'numeroDeEnvio' => $externalId,
                'fechaDeEnvio' => date('Y-m-d'),
                'contrato' => $serviceType,
                'origen' => [
                    'nombre' => EMPRESA_NOMBRE,
                    'telefono' => EMPRESA_TELEFONO,
                    'email' => EMPRESA_EMAIL,
                    'direccion' => [
                        'calle' => PICKUP_ADDRESS['street'],
                        'ciudad' => PICKUP_ADDRESS['city'],
                        'provincia' => PICKUP_ADDRESS['state'],
                        'codigoPostal' => PICKUP_ADDRESS['postal_code']
                    ]
                ],
                'destino' => [
                    'nombre' => $customerInfo['firstName'] . ' ' . $customerInfo['lastName'],
                    'telefono' => $customerInfo['phone'],
                    'email' => $customerInfo['email'],
                    'direccion' => [
                        'calle' => $destinationAddress['street'],
                        'ciudad' => $destinationAddress['city'],
                        'provincia' => $destinationAddress['state'],
                        'codigoPostal' => $destinationAddress['postal_code']
                    ]
                ],
                'paquete' => [
                    'peso' => $weight,
                    'dimensiones' => [
                        'largo' => $dimensions['length'],
                        'ancho' => $dimensions['width'],
                        'alto' => $dimensions['height']
                    ],
                    'categoria' => 'PAQUETE',
                    'contenido' => $this->formatItemsForAndreani($items)
                ],
                'valorDeclarado' => array_sum(array_map(function($item) {
                    return $item['price'] * $item['quantity'];
                }, $items))
            ];

            $response = make_http_request(
                ANDREANI_ENVIOS_URL,
                'POST',
                $shipmentData,
                [
                    'Authorization: Bearer ' . $this->accessToken,
                    'X-API-Key: ' . ANDREANI_API_KEY
                ]
            );

            if (isset($response['body']['numeroDeGuia'])) {
                log_shipping('Andreani shipment created successfully', 'INFO', [
                    'tracking_number' => $response['body']['numeroDeGuia'],
                    'external_id' => $externalId
                ]);

                return [
                    'success' => true,
                    'tracking_number' => $response['body']['numeroDeGuia'],
                    'external_id' => $externalId,
                    'status' => 'created',
                    'label_url' => $response['body']['urlEtiqueta'] ?? null,
                    'estimated_delivery' => $response['body']['fechaEstimadaEntrega'] ?? null,
                    'raw_response' => $response['body']
                ];
            }

            throw new Exception('No tracking number received');

        } catch (Exception $e) {
            log_shipping('Andreani shipment creation failed: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estado de envío
     */
    public function getShipmentStatus($trackingNumber) {
        try {
            $this->ensureAuthenticated();

            $response = make_http_request(
                ANDREANI_ENVIOS_URL . '/' . $trackingNumber . '/estado',
                'GET',
                null,
                [
                    'Authorization: Bearer ' . $this->accessToken,
                    'X-API-Key: ' . ANDREANI_API_KEY
                ]
            );

            if (isset($response['body']['estado'])) {
                return [
                    'success' => true,
                    'tracking_number' => $trackingNumber,
                    'status' => $response['body']['estado'],
                    'status_description' => $response['body']['descripcionEstado'] ?? '',
                    'location' => $response['body']['ubicacion'] ?? '',
                    'estimated_delivery' => $response['body']['fechaEstimadaEntrega'] ?? null,
                    'events' => $response['body']['eventos'] ?? [],
                    'raw_response' => $response['body']
                ];
            }

            throw new Exception('Shipment not found');

        } catch (Exception $e) {
            log_shipping('Andreani status check failed: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener etiqueta de envío (PDF)
     */
    public function getShippingLabel($trackingNumber) {
        try {
            $this->ensureAuthenticated();

            $response = make_http_request(
                ANDREANI_ENVIOS_URL . '/' . $trackingNumber . '/etiqueta',
                'GET',
                null,
                [
                    'Authorization: Bearer ' . $this->accessToken,
                    'X-API-Key: ' . ANDREANI_API_KEY,
                    'Accept: application/pdf'
                ]
            );

            return [
                'success' => true,
                'pdf_content' => $response['body'],
                'filename' => 'etiqueta_' . $trackingNumber . '.pdf'
            ];

        } catch (Exception $e) {
            log_shipping('Andreani label download failed: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener sucursales cercanas
     */
    public function getNearbyBranches($postalCode, $radius = 10) {
        try {
            $this->ensureAuthenticated();

            $response = make_http_request(
                ANDREANI_SUCURSALES_URL . '?' . http_build_query([
                    'codigoPostal' => $postalCode,
                    'radio' => $radius
                ]),
                'GET',
                null,
                [
                    'Authorization: Bearer ' . $this->accessToken,
                    'X-API-Key: ' . ANDREANI_API_KEY
                ]
            );

            if (isset($response['body']['sucursales'])) {
                return [
                    'success' => true,
                    'branches' => $response['body']['sucursales'],
                    'count' => count($response['body']['sucursales'])
                ];
            }

            return [
                'success' => false,
                'error' => 'No branches found'
            ];

        } catch (Exception $e) {
            log_shipping('Andreani branches lookup failed: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Programar retiro
     */
    public function schedulePickup($pickupDate, $timeSlot = 'morning') {
        try {
            $this->ensureAuthenticated();

            $pickupData = [
                'fecha' => $pickupDate,
                'horario' => $timeSlot === 'morning' ? 'MANANA' : 'TARDE',
                'direccion' => [
                    'calle' => PICKUP_ADDRESS['street'],
                    'ciudad' => PICKUP_ADDRESS['city'],
                    'provincia' => PICKUP_ADDRESS['state'],
                    'codigoPostal' => PICKUP_ADDRESS['postal_code']
                ],
                'contacto' => [
                    'nombre' => EMPRESA_NOMBRE,
                    'telefono' => EMPRESA_TELEFONO,
                    'email' => EMPRESA_EMAIL
                ]
            ];

            $response = make_http_request(
                ANDREANI_BASE_URL . '/v1/retiros',
                'POST',
                $pickupData,
                [
                    'Authorization: Bearer ' . $this->accessToken,
                    'X-API-Key: ' . ANDREANI_API_KEY
                ]
            );

            if (isset($response['body']['numeroDeRetiro'])) {
                log_shipping('Andreani pickup scheduled', 'INFO', [
                    'pickup_number' => $response['body']['numeroDeRetiro'],
                    'date' => $pickupDate,
                    'time_slot' => $timeSlot
                ]);

                return [
                    'success' => true,
                    'pickup_number' => $response['body']['numeroDeRetiro'],
                    'scheduled_date' => $pickupDate,
                    'time_slot' => $timeSlot,
                    'raw_response' => $response['body']
                ];
            }

            throw new Exception('No pickup number received');

        } catch (Exception $e) {
            log_shipping('Andreani pickup scheduling failed: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Formatear items para Andreani API
     */
    private function formatItemsForAndreani($items) {
        $itemList = [];
        
        foreach ($items as $item) {
            $itemList[] = [
                'descripcion' => $item['name'],
                'cantidad' => $item['quantity'],
                'valor' => $item['price'],
                'peso' => SHIPPING_WEIGHT_PER_ITEM
            ];
        }

        return implode(', ', array_map(function($item) {
            return $item['cantidad'] . 'x ' . $item['descripcion'];
        }, $itemList));
    }

    /**
     * Procesar webhook de Andreani
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

            log_shipping('Andreani webhook received', 'INFO', [
                'event_type' => $data['evento'] ?? 'unknown',
                'tracking_number' => $data['numeroDeGuia'] ?? 'unknown'
            ]);

            // Procesar diferentes tipos de eventos
            switch ($data['evento'] ?? '') {
                case 'estado_actualizado':
                    return $this->handleStatusUpdate($data);
                case 'entrega_realizada':
                    return $this->handleDeliveryCompleted($data);
                case 'excepcion':
                    return $this->handleException($data);
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
        return ['success' => true, 'message' => 'Status updated'];
    }

    /**
     * Manejar entrega completada
     */
    private function handleDeliveryCompleted($data) {
        // Aquí puedes marcar el pedido como entregado
        return ['success' => true, 'message' => 'Delivery completed'];
    }

    /**
     * Manejar excepciones de entrega
     */
    private function handleException($data) {
        // Aquí puedes manejar problemas de entrega
        return ['success' => true, 'message' => 'Exception handled'];
    }

    /**
     * Verificar disponibilidad del servicio
     */
    public function isServiceAvailable($address) {
        // Andreani cubre todo el territorio argentino
        return strlen($address['postal_code']) >= 4;
    }

    /**
     * Validar código postal
     */
    public function validatePostalCode($postalCode) {
        // Validación básica de código postal argentino
        return preg_match('/^[A-Z]?\d{4}[A-Z]{0,3}$/', strtoupper($postalCode));
    }
}
?>