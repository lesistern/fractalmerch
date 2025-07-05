<?php
/**
 * Orders API - Get Orders
 */

require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create orders table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS customer_orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) UNIQUE NOT NULL,
            customer_id INT,
            customer_name VARCHAR(255),
            customer_email VARCHAR(255),
            status ENUM('pending', 'confirmed', 'processing', 'production', 'quality_check', 'packaging', 'shipped', 'delivered', 'cancelled', 'returned') DEFAULT 'pending',
            payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
            total_amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'ARS',
            items JSON,
            shipping_address JSON,
            billing_address JSON,
            payment_method VARCHAR(50),
            shipping_method VARCHAR(50),
            tracking_number VARCHAR(100),
            estimated_delivery DATETIME,
            actual_delivery DATETIME,
            notes TEXT,
            status_history JSON,
            custom_data JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_customer (customer_id),
            INDEX idx_status (status),
            INDEX idx_payment (payment_status),
            INDEX idx_order_number (order_number),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create some sample data if table is empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM customer_orders");
    $stmt->execute();
    $count = intval($stmt->fetchColumn());
    
    if ($count === 0) {
        // Insert sample orders
        $sampleOrders = [
            [
                'order_number' => 'ORD-2025-001',
                'customer_name' => 'Juan Pérez',
                'customer_email' => 'juan.perez@email.com',
                'status' => 'processing',
                'payment_status' => 'paid',
                'total_amount' => 15999.99,
                'items' => json_encode([
                    ['name' => 'Remera Personalizada', 'quantity' => 2, 'price' => 5999.99],
                    ['name' => 'Buzo con Logo', 'quantity' => 1, 'price' => 12999.99]
                ]),
                'shipping_address' => json_encode([
                    'address1' => 'Av. Corrientes 1234',
                    'city' => 'Buenos Aires',
                    'state' => 'CABA',
                    'zip' => '1043'
                ])
            ],
            [
                'order_number' => 'ORD-2025-002',
                'customer_name' => 'María García',
                'customer_email' => 'maria.garcia@email.com',
                'status' => 'shipped',
                'payment_status' => 'paid',
                'total_amount' => 8999.99,
                'items' => json_encode([
                    ['name' => 'Taza Personalizada', 'quantity' => 3, 'price' => 3499.99]
                ]),
                'tracking_number' => 'FM001234567890'
            ],
            [
                'order_number' => 'ORD-2025-003',
                'customer_name' => 'Carlos López',
                'customer_email' => 'carlos.lopez@email.com',
                'status' => 'pending',
                'payment_status' => 'pending',
                'total_amount' => 22999.99,
                'items' => json_encode([
                    ['name' => 'Kit Completo Empresarial', 'quantity' => 1, 'price' => 22999.99]
                ])
            ]
        ];
        
        $insertStmt = $pdo->prepare("
            INSERT INTO customer_orders 
            (order_number, customer_name, customer_email, status, payment_status, total_amount, items, shipping_address, tracking_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleOrders as $order) {
            $insertStmt->execute([
                $order['order_number'],
                $order['customer_name'],
                $order['customer_email'],
                $order['status'],
                $order['payment_status'],
                $order['total_amount'],
                $order['items'],
                $order['shipping_address'] ?? null,
                $order['tracking_number'] ?? null
            ]);
        }
    }
    
    // Get orders
    $stmt = $pdo->prepare("
        SELECT * FROM customer_orders 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process orders
    $processedOrders = array_map(function($order) {
        return [
            'id' => intval($order['id']),
            'orderNumber' => $order['order_number'],
            'customerId' => intval($order['customer_id']),
            'customerName' => $order['customer_name'],
            'customerEmail' => $order['customer_email'],
            'status' => $order['status'],
            'paymentStatus' => $order['payment_status'],
            'totalAmount' => floatval($order['total_amount']),
            'currency' => $order['currency'],
            'items' => json_decode($order['items'] ?: '[]', true),
            'shippingAddress' => json_decode($order['shipping_address'] ?: '{}', true),
            'billingAddress' => json_decode($order['billing_address'] ?: '{}', true),
            'paymentMethod' => $order['payment_method'],
            'shippingMethod' => $order['shipping_method'],
            'trackingNumber' => $order['tracking_number'],
            'estimatedDelivery' => $order['estimated_delivery'],
            'actualDelivery' => $order['actual_delivery'],
            'notes' => $order['notes'],
            'statusHistory' => json_decode($order['status_history'] ?: '[]', true),
            'customData' => json_decode($order['custom_data'] ?: '{}', true),
            'createdAt' => $order['created_at'],
            'updatedAt' => $order['updated_at']
        ];
    }, $orders);
    
    // Calculate summary statistics
    $summary = [
        'pending' => 0,
        'processing' => 0,
        'shipped' => 0,
        'delivered' => 0,
        'total_revenue' => 0,
        'average_order_value' => 0
    ];
    
    // Count by status
    $stmt = $pdo->prepare("
        SELECT 
            status,
            COUNT(*) as count,
            SUM(total_amount) as revenue
        FROM customer_orders 
        GROUP BY status
    ");
    $stmt->execute();
    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($statusCounts as $statusCount) {
        $summary[$statusCount['status']] = intval($statusCount['count']);
        $summary['total_revenue'] += floatval($statusCount['revenue']);
    }
    
    // Calculate average order value
    if (count($orders) > 0) {
        $summary['average_order_value'] = $summary['total_revenue'] / count($orders);
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $processedOrders,
        'summary' => $summary
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
    error_log("Orders API Error: " . $e->getMessage());
}
?>