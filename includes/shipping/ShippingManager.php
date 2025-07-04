<?php
require_once __DIR__ . '/UberDirectAPI.php';
require_once __DIR__ . '/AndreaniAPI.php';
require_once __DIR__ . '/../../config/shipping_apis.php';

/**
 * Gestor principal de envíos
 * Coordina entre Uber Direct y Andreani según la ubicación y disponibilidad
 */
class ShippingManager {
    private $uberDirect;
    private $andreani;

    public function __construct() {
        $this->uberDirect = new UberDirectAPI();
        $this->andreani = new AndreaniAPI();
    }

    /**
     * Obtener cotizaciones de envío disponibles
     */
    public function getShippingQuotes($destinationAddress, $items = []) {
        $quotes = [];
        $errors = [];

        // Validar dirección
        $addressErrors = validate_address($destinationAddress);
        if (!empty($addressErrors)) {
            return [
                'success' => false,
                'errors' => $addressErrors
            ];
        }

        // Verificar si el subtotal califica para envío gratis
        $subtotal = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $items));

        $freeShippingApplies = $subtotal >= FREE_SHIPPING_THRESHOLD;

        // Verificar si las APIs están configuradas correctamente
        $configErrors = validate_shipping_config();
        $useTestMode = !empty($configErrors);

        if ($useTestMode) {
            // Modo de prueba - generar cotizaciones simuladas
            log_shipping('Using test mode for shipping quotes', 'INFO', ['config_errors' => $configErrors]);
            return $this->getTestShippingQuotes($destinationAddress, $items, $freeShippingApplies);
        }

        try {
            // Intentar cotización con Uber Direct
            if ($this->uberDirect->isServiceAvailable($destinationAddress)) {
                try {
                    $uberQuote = $this->uberDirect->getQuote(PICKUP_ADDRESS, $destinationAddress, $items);
                    
                    if ($uberQuote['success']) {
                        $finalPrice = $freeShippingApplies ? 0 : $uberQuote['price'] * (1 + SHIPPING_MARKUP_PERCENTAGE);
                        
                        $quotes[] = [
                            'provider' => 'uber_direct',
                            'name' => 'Uber Direct - Entrega rápida',
                            'description' => 'Entrega en el día (1-4 horas)',
                            'price' => $finalPrice,
                            'original_price' => $uberQuote['price'],
                            'currency' => $uberQuote['currency'],
                            'estimated_delivery' => $uberQuote['estimated_delivery'],
                            'duration_minutes' => $uberQuote['duration_minutes'],
                            'free_shipping' => $freeShippingApplies,
                            'quote_data' => $uberQuote
                        ];
                    }
                } catch (Exception $e) {
                    log_shipping('Uber Direct API error, adding test quote', 'WARNING', ['error' => $e->getMessage()]);
                    // Agregar cotización de prueba para Uber Direct
                    $quotes[] = $this->getTestUberDirectQuote($freeShippingApplies);
                }
            }

            // Intentar cotización con Andreani
            if ($this->andreani->isServiceAvailable($destinationAddress)) {
                try {
                    $andreaniQuote = $this->andreani->calculateShippingRate($destinationAddress, $items);
                    
                    if ($andreaniQuote['success']) {
                        $finalPrice = $freeShippingApplies ? 0 : $andreaniQuote['price'] * (1 + SHIPPING_MARKUP_PERCENTAGE);
                        
                        $quotes[] = [
                            'provider' => 'andreani',
                            'name' => 'Andreani - Envío estándar',
                            'description' => 'Entrega en ' . ($andreaniQuote['estimated_days'] ?? '3-5') . ' días hábiles',
                            'price' => $finalPrice,
                            'original_price' => $andreaniQuote['price'],
                            'currency' => $andreaniQuote['currency'],
                            'estimated_days' => $andreaniQuote['estimated_days'],
                            'service_type' => $andreaniQuote['service_type'],
                            'free_shipping' => $freeShippingApplies,
                            'quote_data' => $andreaniQuote
                        ];
                    }
                } catch (Exception $e) {
                    log_shipping('Andreani API error, adding test quote', 'WARNING', ['error' => $e->getMessage()]);
                    // Agregar cotización de prueba para Andreani
                    $quotes[] = $this->getTestAndreaniQuote($destinationAddress, $freeShippingApplies);
                }
            }

        } catch (Exception $e) {
            log_shipping('Error getting shipping quotes: ' . $e->getMessage(), 'ERROR');
            $errors[] = $e->getMessage();
        }

        // Agregar opción de retiro en sucursal (siempre disponible)
        $quotes[] = [
            'provider' => 'pickup',
            'name' => 'Retiro en sucursal',
            'description' => 'Retirá tu pedido en nuestro local - Calle Sargento Acosta 3947, Posadas',
            'price' => 0,
            'currency' => 'ARS',
            'estimated_days' => 1,
            'pickup_address' => PICKUP_ADDRESS,
            'free_shipping' => true
        ];

        // Si no hay cotizaciones de APIs, agregar cotizaciones de prueba
        if (count($quotes) === 1) { // Solo retiro en sucursal
            $quotes = array_merge($quotes, $this->getTestShippingQuotes($destinationAddress, $items, $freeShippingApplies)['quotes']);
        }

        // Ordenar por precio (gratis primero, luego de menor a mayor)
        usort($quotes, function($a, $b) {
            if ($a['price'] == 0 && $b['price'] > 0) return -1;
            if ($b['price'] == 0 && $a['price'] > 0) return 1;
            return $a['price'] <=> $b['price'];
        });

        return [
            'success' => true,
            'quotes' => $quotes,
            'free_shipping_threshold' => FREE_SHIPPING_THRESHOLD,
            'free_shipping_applies' => $freeShippingApplies,
            'errors' => $errors,
            'test_mode' => $useTestMode
        ];
    }

    /**
     * Generar cotizaciones de prueba cuando las APIs no están disponibles
     */
    private function getTestShippingQuotes($destinationAddress, $items, $freeShippingApplies) {
        $quotes = [];
        
        $weight = calculate_shipping_weight($items);
        $subtotal = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $items));

        // Determinar si es envío local o nacional
        $isLocal = ($destinationAddress['state'] === 'Misiones' && 
                   in_array($destinationAddress['city'], ['Posadas', 'posadas']));

        if ($isLocal) {
            // Cotización de prueba para Uber Direct (local)
            $quotes[] = $this->getTestUberDirectQuote($freeShippingApplies);
        } else {
            // Cotización de prueba para Andreani (nacional)
            $quotes[] = $this->getTestAndreaniQuote($destinationAddress, $freeShippingApplies);
        }

        return [
            'success' => true,
            'quotes' => $quotes,
            'free_shipping_threshold' => FREE_SHIPPING_THRESHOLD,
            'free_shipping_applies' => $freeShippingApplies,
            'test_mode' => true
        ];
    }

    /**
     * Cotización de prueba para Uber Direct
     */
    private function getTestUberDirectQuote($freeShippingApplies) {
        $basePrice = 1200; // Precio base simulado
        $finalPrice = $freeShippingApplies ? 0 : $basePrice;
        
        return [
            'provider' => 'uber_direct',
            'name' => 'Uber Direct - Entrega rápida',
            'description' => 'Entrega en el día (1-4 horas) - MODO PRUEBA',
            'price' => $finalPrice,
            'original_price' => $basePrice,
            'currency' => 'ARS',
            'estimated_delivery' => date('Y-m-d H:i:s', strtotime('+3 hours')),
            'duration_minutes' => 180,
            'free_shipping' => $freeShippingApplies,
            'test_mode' => true
        ];
    }

    /**
     * Cotización de prueba para Andreani
     */
    private function getTestAndreaniQuote($destinationAddress, $freeShippingApplies) {
        // Calcular precio base según distancia (simulado)
        $basePrice = 2500; // Precio base para envío nacional
        
        // Ajustar precio según provincia
        $distanceMultiplier = 1.0;
        $estimatedDays = 3;
        
        switch (strtolower($destinationAddress['state'])) {
            case 'buenos aires':
            case 'córdoba':
            case 'santa fe':
                $distanceMultiplier = 1.2;
                $estimatedDays = 3;
                break;
            case 'mendoza':
            case 'san juan':
                $distanceMultiplier = 1.5;
                $estimatedDays = 4;
                break;
            case 'patagonia':
            case 'tierra del fuego':
                $distanceMultiplier = 2.0;
                $estimatedDays = 7;
                break;
        }
        
        $calculatedPrice = $basePrice * $distanceMultiplier;
        $finalPrice = $freeShippingApplies ? 0 : $calculatedPrice;
        
        return [
            'provider' => 'andreani',
            'name' => 'Andreani - Envío estándar',
            'description' => "Entrega en {$estimatedDays} días hábiles - MODO PRUEBA",
            'price' => $finalPrice,
            'original_price' => $calculatedPrice,
            'currency' => 'ARS',
            'estimated_days' => $estimatedDays,
            'service_type' => 'ESTANDAR',
            'free_shipping' => $freeShippingApplies,
            'test_mode' => true
        ];
    }

    /**
     * Crear envío
     */
    public function createShipment($provider, $destinationAddress, $customerInfo, $items = [], $quoteData = null) {
        try {
            switch ($provider) {
                case 'uber_direct':
                    return $this->uberDirect->createDelivery(
                        PICKUP_ADDRESS,
                        $destinationAddress,
                        $customerInfo,
                        $items
                    );
                
                case 'andreani':
                    return $this->andreani->createShipment(
                        $destinationAddress,
                        $customerInfo,
                        $items
                    );
                
                case 'pickup':
                    // Para retiro en sucursal, solo generar un número de orden
                    return [
                        'success' => true,
                        'pickup_number' => 'PICKUP_' . time() . '_' . rand(1000, 9999),
                        'pickup_address' => PICKUP_ADDRESS,
                        'estimated_ready' => date('Y-m-d', strtotime('+1 day'))
                    ];
                
                default:
                    throw new Exception('Unknown shipping provider: ' . $provider);
            }

        } catch (Exception $e) {
            log_shipping('Error creating shipment: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estado de envío
     */
    public function getShipmentStatus($provider, $trackingId) {
        try {
            switch ($provider) {
                case 'uber_direct':
                    return $this->uberDirect->getDeliveryStatus($trackingId);
                
                case 'andreani':
                    return $this->andreani->getShipmentStatus($trackingId);
                
                case 'pickup':
                    // Para retiro en sucursal, estado simple
                    return [
                        'success' => true,
                        'status' => 'ready_for_pickup',
                        'pickup_number' => $trackingId,
                        'pickup_address' => PICKUP_ADDRESS
                    ];
                
                default:
                    throw new Exception('Unknown shipping provider: ' . $provider);
            }

        } catch (Exception $e) {
            log_shipping('Error getting shipment status: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancelar envío
     */
    public function cancelShipment($provider, $trackingId, $reason = 'Customer request') {
        try {
            switch ($provider) {
                case 'uber_direct':
                    return $this->uberDirect->cancelDelivery($trackingId, $reason);
                
                case 'andreani':
                    // Andreani no siempre permite cancelación automática
                    log_shipping('Andreani cancellation requested manually', 'INFO', [
                        'tracking_id' => $trackingId,
                        'reason' => $reason
                    ]);
                    return [
                        'success' => true,
                        'message' => 'Cancellation request logged. Manual intervention may be required.'
                    ];
                
                case 'pickup':
                    return [
                        'success' => true,
                        'status' => 'cancelled'
                    ];
                
                default:
                    throw new Exception('Unknown shipping provider: ' . $provider);
            }

        } catch (Exception $e) {
            log_shipping('Error cancelling shipment: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener etiqueta de envío
     */
    public function getShippingLabel($provider, $trackingId) {
        try {
            switch ($provider) {
                case 'andreani':
                    return $this->andreani->getShippingLabel($trackingId);
                
                case 'uber_direct':
                    // Uber Direct no requiere etiquetas físicas
                    return [
                        'success' => false,
                        'error' => 'Uber Direct does not require physical labels'
                    ];
                
                case 'pickup':
                    // Generar etiqueta simple para retiro
                    return $this->generatePickupLabel($trackingId);
                
                default:
                    throw new Exception('Unknown shipping provider: ' . $provider);
            }

        } catch (Exception $e) {
            log_shipping('Error getting shipping label: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Procesar webhooks
     */
    public function processWebhook($provider, $payload, $signature) {
        try {
            switch ($provider) {
                case 'uber_direct':
                    return $this->uberDirect->processWebhook($payload, $signature);
                
                case 'andreani':
                    return $this->andreani->processWebhook($payload, $signature);
                
                default:
                    throw new Exception('Unknown webhook provider: ' . $provider);
            }

        } catch (Exception $e) {
            log_shipping('Error processing webhook: ' . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener sucursales de Andreani cercanas
     */
    public function getNearbyBranches($postalCode, $radius = 10) {
        return $this->andreani->getNearbyBranches($postalCode, $radius);
    }

    /**
     * Programar retiro con Andreani
     */
    public function schedulePickup($date, $timeSlot = 'morning') {
        return $this->andreani->schedulePickup($date, $timeSlot);
    }

    /**
     * Generar etiqueta simple para retiro
     */
    private function generatePickupLabel($pickupNumber) {
        $labelContent = "
        RETIRO EN SUCURSAL
        
        " . EMPRESA_NOMBRE . "
        
        Número de retiro: $pickupNumber
        Dirección: " . PICKUP_ADDRESS['street'] . "
        " . PICKUP_ADDRESS['city'] . ", " . PICKUP_ADDRESS['state'] . "
        CP: " . PICKUP_ADDRESS['postal_code'] . "
        
        Teléfono: " . EMPRESA_TELEFONO . "
        
        Presentar esta etiqueta al retirar su pedido
        ";

        return [
            'success' => true,
            'label_content' => $labelContent,
            'filename' => 'retiro_' . $pickupNumber . '.txt'
        ];
    }

    /**
     * Validar configuración de APIs
     */
    public function validateConfiguration() {
        return validate_shipping_config();
    }

    /**
     * Obtener estadísticas de envíos
     */
    public function getShippingStats($dateFrom = null, $dateTo = null) {
        // Aquí podrías consultar la base de datos para obtener estadísticas
        // de envíos realizados, costos, tiempos de entrega, etc.
        
        return [
            'total_shipments' => 0,
            'uber_direct_shipments' => 0,
            'andreani_shipments' => 0,
            'pickup_orders' => 0,
            'average_delivery_time' => 0,
            'total_shipping_cost' => 0
        ];
    }
}
?>