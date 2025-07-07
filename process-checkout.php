<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Método no permitido'], 405);
}

// Validar CSRF token
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    json_response(['success' => false, 'error' => 'Token de seguridad inválido'], 403);
}

// Procesar datos del checkout
try {
    // Sanitizar datos de contacto
    $contactData = [
        'email' => validate_and_sanitize_input($_POST['email'] ?? '', 'email'),
        'firstName' => validate_and_sanitize_input($_POST['firstName'] ?? '', 'string'),
        'lastName' => validate_and_sanitize_input($_POST['lastName'] ?? '', 'string'),
        'phone' => validate_and_sanitize_input($_POST['phone'] ?? '', 'string')
    ];

    // Sanitizar datos de envío
    $shippingData = [
        'address' => validate_and_sanitize_input($_POST['address'] ?? '', 'string'),
        'city' => validate_and_sanitize_input($_POST['city'] ?? '', 'string'),
        'province' => validate_and_sanitize_input($_POST['province'] ?? '', 'string'),
        'postalCode' => validate_and_sanitize_input($_POST['postalCode'] ?? '', 'string'),
        'shippingMethod' => validate_and_sanitize_input($_POST['shipping'] ?? '', 'string')
    ];

    // Sanitizar datos de pago (nunca almacenar datos sensibles de tarjeta)
    $paymentData = [
        'paymentMethod' => validate_and_sanitize_input($_POST['payment'] ?? '', 'string'),
        'cardNumber' => preg_replace('/[^0-9]/', '', $_POST['cardNumber'] ?? ''), // Solo números, no almacenar
        'cardName' => validate_and_sanitize_input($_POST['cardName'] ?? '', 'string'),
        'expiryDate' => preg_replace('/[^0-9\/]/', '', $_POST['expiryDate'] ?? ''), // Solo números y /
        'cvv' => preg_replace('/[^0-9]/', '', $_POST['cvv'] ?? '') // Solo números, nunca almacenar
    ];

    // Validar datos requeridos
    $errors = [];
    
    // Validar contacto
    if (empty($contactData['email']) || !filter_var($contactData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    if (empty($contactData['firstName'])) {
        $errors[] = 'Nombre requerido';
    }
    if (empty($contactData['lastName'])) {
        $errors[] = 'Apellido requerido';
    }
    if (empty($contactData['phone'])) {
        $errors[] = 'Teléfono requerido';
    }

    // Validar envío
    if (empty($shippingData['address'])) {
        $errors[] = 'Dirección requerida';
    }
    if (empty($shippingData['city'])) {
        $errors[] = 'Ciudad requerida';
    }
    if (empty($shippingData['province'])) {
        $errors[] = 'Provincia requerida';
    }
    if (empty($shippingData['postalCode'])) {
        $errors[] = 'Código postal requerido';
    }

    // Validar pago
    if (empty($paymentData['paymentMethod'])) {
        $errors[] = 'Método de pago requerido';
    }

    // Validar datos de tarjeta si es necesario
    if ($paymentData['paymentMethod'] === 'credit-card') {
        if (empty($paymentData['cardNumber'])) {
            $errors[] = 'Número de tarjeta requerido';
        }
        if (empty($paymentData['cardName'])) {
            $errors[] = 'Nombre en la tarjeta requerido';
        }
        if (empty($paymentData['expiryDate'])) {
            $errors[] = 'Fecha de vencimiento requerida';
        }
        if (empty($paymentData['cvv'])) {
            $errors[] = 'CVV requerido';
        }
    }

    // Si hay errores, retornar
    if (!empty($errors)) {
        json_response(['success' => false, 'errors' => $errors], 400);
    }

    // Obtener datos del carrito (normalmente vendría de la sesión o cookie)
    $cartData = json_decode($_POST['cart_data'] ?? '[]', true);
    
    if (empty($cartData)) {
        json_response(['success' => false, 'error' => 'El carrito está vacío'], 400);
    }

    // Comenzar transacción
    $pdo->beginTransaction();

    // Generar número de orden único
    $orderNumber = 'ORDER-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));

    // Calcular totales
    $subtotal = 0;
    foreach ($cartData as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    $shippingCost = floatval($_POST['shipping_cost'] ?? 0);
    $taxAmount = $subtotal * 0.21; // IVA 21%
    $total = $subtotal + $shippingCost + $taxAmount;

    // Insertar orden principal
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, user_id, 
            contact_email, contact_first_name, contact_last_name, contact_phone,
            shipping_address, shipping_city, shipping_province, shipping_postal_code,
            shipping_method, payment_method,
            subtotal, shipping_cost, tax_amount, total_amount,
            order_status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->execute([
        $orderNumber,
        $_SESSION['user_id'] ?? null,
        $contactData['email'],
        $contactData['firstName'],
        $contactData['lastName'],
        $contactData['phone'],
        $shippingData['address'],
        $shippingData['city'],
        $shippingData['province'],
        $shippingData['postalCode'],
        $shippingData['shippingMethod'],
        $paymentData['paymentMethod'],
        $subtotal,
        $shippingCost,
        $taxAmount,
        $total
    ]);

    $orderId = $pdo->lastInsertId();

    // Insertar items de la orden
    foreach ($cartData as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id, product_name, product_price, quantity, 
                product_size, product_color, item_total
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $orderId,
            $item['name'],
            $item['price'],
            $item['quantity'],
            $item['size'] ?? null,
            $item['color'] ?? null,
            $item['price'] * $item['quantity']
        ]);
    }

    // Procesar pago (simulado)
    $paymentResult = processPayment($paymentData, $total, $orderNumber);

    if ($paymentResult['success']) {
        // Actualizar estado de la orden
        $stmt = $pdo->prepare("UPDATE orders SET order_status = 'paid', payment_transaction_id = ? WHERE id = ?");
        $stmt->execute([$paymentResult['transaction_id'], $orderId]);

        $pdo->commit();

        // Invalidar token CSRF
        invalidate_csrf_token();

        // Enviar email de confirmación (simulado)
        sendOrderConfirmationEmail($contactData['email'], $orderNumber, $total);

        json_response([
            'success' => true,
            'order_number' => $orderNumber,
            'message' => 'Pedido procesado exitosamente'
        ]);

    } else {
        $pdo->rollBack();
        json_response(['success' => false, 'error' => $paymentResult['error']], 400);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error processing checkout: " . $e->getMessage());
    json_response(['success' => false, 'error' => 'Error interno del servidor'], 500);
}

/**
 * Simula el procesamiento de pago
 * En un entorno real, aquí se integraría con MercadoPago, Stripe, etc.
 */
function processPayment($paymentData, $amount, $orderNumber) {
    // Simular procesamiento de pago
    sleep(1); // Simular latencia de API
    
    switch ($paymentData['paymentMethod']) {
        case 'credit-card':
            // Validar datos de tarjeta (simulado)
            if (strlen($paymentData['cardNumber']) < 15) {
                return ['success' => false, 'error' => 'Número de tarjeta inválido'];
            }
            
            // Generar ID de transacción simulado
            $transactionId = 'TXN-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 10));
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'payment_method' => 'credit-card'
            ];
            
        case 'mercado-pago':
            // Aquí iría la integración con MercadoPago API
            return [
                'success' => true,
                'transaction_id' => 'MP-' . uniqid(),
                'payment_method' => 'mercado-pago'
            ];
            
        case 'bank-transfer':
            // Para transferencia bancaria, marcar como pendiente
            return [
                'success' => true,
                'transaction_id' => 'BT-' . uniqid(),
                'payment_method' => 'bank-transfer',
                'status' => 'pending'
            ];
            
        default:
            return ['success' => false, 'error' => 'Método de pago no soportado'];
    }
}

/**
 * Simula el envío de email de confirmación
 */
function sendOrderConfirmationEmail($email, $orderNumber, $total) {
    // En un entorno real, aquí se enviaría el email
    error_log("Order confirmation email sent to: $email for order: $orderNumber (Total: $total)");
    return true;
}
?>