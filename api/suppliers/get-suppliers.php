<?php
/**
 * Suppliers API - Get Suppliers
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
    
    // Create suppliers table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type ENUM('printful', 'gooten', 'printify', 'teespring', 'custom_local') NOT NULL,
            api_key TEXT,
            api_secret TEXT,
            base_url VARCHAR(500),
            contact_email VARCHAR(255),
            phone VARCHAR(50),
            is_active BOOLEAN DEFAULT TRUE,
            sync_status ENUM('connected', 'disconnected', 'error') DEFAULT 'disconnected',
            last_sync TIMESTAMP NULL,
            product_count INT DEFAULT 0,
            order_count INT DEFAULT 0,
            request_count INT DEFAULT 0,
            settings JSON,
            webhook_url VARCHAR(500),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_type (type),
            INDEX idx_status (sync_status),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create some sample data if table is empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM suppliers");
    $stmt->execute();
    $count = intval($stmt->fetchColumn());
    
    if ($count === 0) {
        // Insert sample suppliers
        $sampleSuppliers = [
            [
                'name' => 'Printful Argentina',
                'type' => 'printful',
                'api_key' => 'pk_test_' . bin2hex(random_bytes(16)),
                'contact_email' => 'soporte@printful.com',
                'is_active' => 1,
                'sync_status' => 'connected',
                'product_count' => 245,
                'order_count' => 156,
                'request_count' => 1240
            ],
            [
                'name' => 'Gooten Local',
                'type' => 'gooten',
                'api_key' => 'gt_' . bin2hex(random_bytes(20)),
                'contact_email' => 'api@gooten.com',
                'is_active' => 1,
                'sync_status' => 'connected',
                'product_count' => 189,
                'order_count' => 98,
                'request_count' => 856
            ],
            [
                'name' => 'Printify Plus',
                'type' => 'printify',
                'api_key' => 'py_' . bin2hex(random_bytes(18)),
                'contact_email' => 'support@printify.com',
                'is_active' => 1,
                'sync_status' => 'error',
                'product_count' => 312,
                'order_count' => 67,
                'request_count' => 234
            ],
            [
                'name' => 'Imprenta Local Buenos Aires',
                'type' => 'custom_local',
                'api_key' => 'local_' . bin2hex(random_bytes(12)),
                'base_url' => 'https://imprentalocal.com.ar/api',
                'contact_email' => 'ventas@imprentalocal.com.ar',
                'phone' => '+54 11 4567-8901',
                'is_active' => 1,
                'sync_status' => 'connected',
                'product_count' => 45,
                'order_count' => 23,
                'request_count' => 78
            ]
        ];
        
        $insertStmt = $pdo->prepare("
            INSERT INTO suppliers 
            (name, type, api_key, base_url, contact_email, phone, is_active, sync_status, product_count, order_count, request_count) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleSuppliers as $supplier) {
            $insertStmt->execute([
                $supplier['name'],
                $supplier['type'],
                $supplier['api_key'],
                $supplier['base_url'] ?? null,
                $supplier['contact_email'],
                $supplier['phone'] ?? null,
                $supplier['is_active'],
                $supplier['sync_status'],
                $supplier['product_count'],
                $supplier['order_count'],
                $supplier['request_count']
            ]);
        }
        
        // Update last_sync for connected suppliers
        $pdo->exec("UPDATE suppliers SET last_sync = NOW() WHERE sync_status = 'connected'");
    }
    
    // Get suppliers
    $stmt = $pdo->prepare("
        SELECT * FROM suppliers 
        ORDER BY is_active DESC, name ASC
    ");
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process suppliers
    $processedSuppliers = array_map(function($supplier) {
        return [
            'id' => intval($supplier['id']),
            'name' => $supplier['name'],
            'type' => $supplier['type'],
            'apiKey' => $supplier['api_key'],
            'apiSecret' => $supplier['api_secret'],
            'baseUrl' => $supplier['base_url'],
            'contactEmail' => $supplier['contact_email'],
            'phone' => $supplier['phone'],
            'isActive' => (bool)$supplier['is_active'],
            'syncStatus' => $supplier['sync_status'],
            'lastSync' => $supplier['last_sync'],
            'productCount' => intval($supplier['product_count']),
            'orderCount' => intval($supplier['order_count']),
            'requestCount' => intval($supplier['request_count']),
            'settings' => json_decode($supplier['settings'] ?: '{}', true),
            'webhookUrl' => $supplier['webhook_url'],
            'notes' => $supplier['notes'],
            'createdAt' => $supplier['created_at'],
            'updatedAt' => $supplier['updated_at']
        ];
    }, $suppliers);
    
    // Calculate summary statistics
    $summary = [
        'total_suppliers' => count($suppliers),
        'active_suppliers' => 0,
        'api_requests_today' => 0,
        'pending_orders' => 0,
        'success_rate' => 0,
        'last_sync' => null
    ];
    
    foreach ($suppliers as $supplier) {
        if ($supplier['is_active']) {
            $summary['active_suppliers']++;
        }
        if ($supplier['sync_status'] === 'connected') {
            $summary['api_requests_today'] += intval($supplier['request_count']);
        }
        $summary['pending_orders'] += intval($supplier['order_count']);
    }
    
    // Calculate success rate
    $connectedSuppliers = array_filter($suppliers, function($s) { return $s['sync_status'] === 'connected'; });
    if (count($suppliers) > 0) {
        $summary['success_rate'] = round((count($connectedSuppliers) / count($suppliers)) * 100, 1);
    }
    
    // Get latest sync time
    $stmt = $pdo->prepare("SELECT MAX(last_sync) FROM suppliers WHERE last_sync IS NOT NULL");
    $stmt->execute();
    $summary['last_sync'] = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'suppliers' => $processedSuppliers,
        'summary' => $summary
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
    error_log("Suppliers API Error: " . $e->getMessage());
}
?>