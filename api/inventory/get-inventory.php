<?php
/**
 * Inventory Management API - Get Inventory
 * Returns current inventory status for all products
 */

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create inventory tables if not exist
    createInventoryTables($pdo);
    
    // Get filter parameters
    $category = $_GET['category'] ?? null;
    $lowStockOnly = isset($_GET['low_stock']) && $_GET['low_stock'] === 'true';
    $supplierId = $_GET['supplier_id'] ?? null;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if ($category) {
        $whereConditions[] = "i.category = :category";
        $params['category'] = $category;
    }
    
    if ($lowStockOnly) {
        $whereConditions[] = "(i.current_stock - i.reserved_stock) <= i.reorder_point";
    }
    
    if ($supplierId) {
        $whereConditions[] = "i.supplier_id = :supplier_id";
        $params['supplier_id'] = $supplierId;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get inventory data
    $sql = "
        SELECT 
            i.*,
            COALESCE(i.current_stock - i.reserved_stock, 0) as available_stock,
            s.name as supplier_name,
            s.lead_time_days,
            p.name as product_name,
            p.price as selling_price,
            CASE 
                WHEN (i.current_stock - i.reserved_stock) <= 0 THEN 'out_of_stock'
                WHEN (i.current_stock - i.reserved_stock) <= 5 THEN 'critical'
                WHEN (i.current_stock - i.reserved_stock) <= i.reorder_point THEN 'low'
                ELSE 'ok'
            END as stock_status
        FROM inventory_items i
        LEFT JOIN suppliers s ON i.supplier_id = s.id
        LEFT JOIN products p ON i.product_id = p.id
        {$whereClause}
        ORDER BY 
            CASE 
                WHEN (i.current_stock - i.reserved_stock) <= 0 THEN 1
                WHEN (i.current_stock - i.reserved_stock) <= 5 THEN 2
                WHEN (i.current_stock - i.reserved_stock) <= i.reorder_point THEN 3
                ELSE 4
            END,
            i.name ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get summary statistics
    $summaryStats = getInventorySummary($pdo);
    
    // Get recent stock movements
    $recentMovements = getRecentStockMovements($pdo, 20);
    
    // Get pending reorders
    $pendingReorders = getPendingReorders($pdo);
    
    echo json_encode([
        'success' => true,
        'inventory' => $inventory,
        'summary' => $summaryStats,
        'recent_movements' => $recentMovements,
        'pending_reorders' => $pendingReorders,
        'total_items' => count($inventory),
        'last_updated' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
    error_log("Inventory API Error: " . $e->getMessage());
}

/**
 * Create inventory management tables
 */
function createInventoryTables($pdo) {
    // Inventory items table
    $sql = "CREATE TABLE IF NOT EXISTS inventory_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        sku VARCHAR(100) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100),
        current_stock INT DEFAULT 0,
        reserved_stock INT DEFAULT 0,
        reorder_point INT DEFAULT 10,
        reorder_quantity INT DEFAULT 50,
        unit_cost DECIMAL(10,2) DEFAULT 0.00,
        supplier_id INT,
        location VARCHAR(100),
        barcode VARCHAR(255),
        weight DECIMAL(8,2),
        dimensions VARCHAR(100),
        notes TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_counted_at TIMESTAMP NULL,
        
        INDEX idx_sku (sku),
        INDEX idx_product (product_id),
        INDEX idx_category (category),
        INDEX idx_stock_status (current_stock, reserved_stock),
        INDEX idx_supplier (supplier_id),
        INDEX idx_reorder (reorder_point, current_stock),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    // Stock movements table
    $sql = "CREATE TABLE IF NOT EXISTS stock_movements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        inventory_item_id INT NOT NULL,
        movement_type ENUM('purchase', 'sale', 'adjustment', 'restock', 'reservation', 'cancellation', 'transfer', 'damaged', 'expired') NOT NULL,
        quantity_change INT NOT NULL,
        stock_before INT NOT NULL,
        stock_after INT NOT NULL,
        unit_cost DECIMAL(10,2),
        total_cost DECIMAL(10,2),
        reference_type VARCHAR(50),
        reference_id VARCHAR(100),
        reason VARCHAR(255),
        performed_by INT,
        performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        
        FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
        INDEX idx_item (inventory_item_id),
        INDEX idx_type (movement_type),
        INDEX idx_date (performed_at),
        INDEX idx_reference (reference_type, reference_id),
        INDEX idx_performer (performed_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    // Suppliers table
    $sql = "CREATE TABLE IF NOT EXISTS suppliers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        contact_person VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(50),
        address TEXT,
        api_endpoint VARCHAR(500),
        api_key VARCHAR(255),
        client_id VARCHAR(255),
        delivery_address TEXT,
        lead_time_days INT DEFAULT 7,
        minimum_order DECIMAL(10,2) DEFAULT 0.00,
        payment_terms VARCHAR(100),
        currency VARCHAR(3) DEFAULT 'ARS',
        tax_id VARCHAR(50),
        rating DECIMAL(3,2) DEFAULT 0.00,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_name (name),
        INDEX idx_active (is_active),
        INDEX idx_rating (rating)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    // Purchase orders table
    $sql = "CREATE TABLE IF NOT EXISTS purchase_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(100) UNIQUE NOT NULL,
        supplier_id INT NOT NULL,
        status ENUM('pending', 'sent', 'confirmed', 'partial_received', 'completed', 'cancelled') DEFAULT 'pending',
        total_amount DECIMAL(12,2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'ARS',
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expected_delivery_date DATE,
        actual_delivery_date DATE,
        supplier_order_id VARCHAR(255),
        created_by INT,
        approved_by INT,
        approved_at TIMESTAMP NULL,
        notes TEXT,
        
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
        INDEX idx_supplier (supplier_id),
        INDEX idx_status (status),
        INDEX idx_order_date (order_date),
        INDEX idx_delivery (expected_delivery_date),
        INDEX idx_number (order_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    // Purchase order items table
    $sql = "CREATE TABLE IF NOT EXISTS purchase_order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        purchase_order_id INT NOT NULL,
        inventory_item_id INT NOT NULL,
        quantity_ordered INT NOT NULL,
        quantity_received INT DEFAULT 0,
        unit_cost DECIMAL(10,2) NOT NULL,
        total_cost DECIMAL(10,2) NOT NULL,
        received_at TIMESTAMP NULL,
        
        FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
        FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id),
        INDEX idx_order (purchase_order_id),
        INDEX idx_item (inventory_item_id),
        INDEX idx_received (received_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    // Stock reservations table
    $sql = "CREATE TABLE IF NOT EXISTS stock_reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        inventory_item_id INT NOT NULL,
        session_id VARCHAR(255),
        user_id INT,
        order_id INT,
        quantity_reserved INT NOT NULL,
        reservation_type ENUM('cart', 'order', 'manual') DEFAULT 'cart',
        expires_at TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        released_at TIMESTAMP NULL,
        
        FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
        INDEX idx_item (inventory_item_id),
        INDEX idx_session (session_id),
        INDEX idx_user (user_id),
        INDEX idx_order (order_id),
        INDEX idx_expires (expires_at),
        INDEX idx_type (reservation_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    // Insert default data if tables are empty
    insertDefaultInventoryData($pdo);
}

/**
 * Insert default inventory data
 */
function insertDefaultInventoryData($pdo) {
    // Check if data already exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM inventory_items");
    if ($stmt->fetchColumn() > 0) {
        return;
    }
    
    // Insert default supplier
    $stmt = $pdo->prepare("
        INSERT INTO suppliers (name, contact_person, email, phone, lead_time_days, minimum_order)
        VALUES ('Proveedor Principal', 'Juan Pérez', 'contacto@proveedor.com', '+54 11 1234-5678', 5, 1000.00)
    ");
    $stmt->execute();
    $supplierId = $pdo->lastInsertId();
    
    // Insert default inventory items
    $defaultItems = [
        ['SKU-REM-001', 'Remera Blanca S', 'Remeras', 45, 5, 10, 50, 800.00],
        ['SKU-REM-002', 'Remera Blanca M', 'Remeras', 67, 8, 10, 50, 800.00],
        ['SKU-REM-003', 'Remera Blanca L', 'Remeras', 23, 3, 10, 50, 800.00],
        ['SKU-REM-004', 'Remera Blanca XL', 'Remeras', 34, 2, 10, 50, 800.00],
        ['SKU-REM-005', 'Remera Negra S', 'Remeras', 38, 6, 10, 50, 850.00],
        ['SKU-REM-006', 'Remera Negra M', 'Remeras', 52, 12, 10, 50, 850.00],
        ['SKU-REM-007', 'Remera Negra L', 'Remeras', 41, 7, 10, 50, 850.00],
        ['SKU-REM-008', 'Remera Negra XL', 'Remeras', 29, 4, 10, 50, 850.00],
        ['SKU-BUZ-001', 'Buzo Gris S', 'Buzos', 15, 2, 5, 25, 1800.00],
        ['SKU-BUZ-002', 'Buzo Gris M', 'Buzos', 22, 3, 5, 25, 1800.00],
        ['SKU-BUZ-003', 'Buzo Gris L', 'Buzos', 18, 1, 5, 25, 1800.00],
        ['SKU-BUZ-004', 'Buzo Gris XL', 'Buzos', 12, 0, 5, 25, 1800.00],
        ['SKU-TAZ-001', 'Taza Cerámica Blanca', 'Tazas', 75, 12, 15, 100, 450.00],
        ['SKU-TAZ-002', 'Taza Cerámica Negra', 'Tazas', 63, 8, 15, 100, 480.00],
        ['SKU-PAD-001', 'Mouse Pad Rectangular', 'Mouse Pads', 95, 15, 20, 100, 320.00],
        ['SKU-FUN-001', 'Funda iPhone 12', 'Fundas', 34, 6, 10, 50, 680.00],
        ['SKU-FUN-002', 'Funda Samsung Galaxy', 'Fundas', 28, 4, 10, 50, 680.00],
        ['SKU-ALM-001', 'Almohada 40x40cm', 'Almohadas', 22, 3, 8, 30, 950.00]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO inventory_items 
        (sku, name, category, current_stock, reserved_stock, reorder_point, reorder_quantity, unit_cost, supplier_id, location)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Depósito Principal')
    ");
    
    foreach ($defaultItems as $item) {
        $stmt->execute(array_merge($item, [$supplierId]));
    }
    
    echo "<!-- Default inventory data inserted -->";
}

/**
 * Get inventory summary statistics
 */
function getInventorySummary($pdo) {
    $sql = "
        SELECT 
            COUNT(*) as total_items,
            SUM(current_stock) as total_stock,
            SUM(current_stock * unit_cost) as total_value,
            SUM(CASE WHEN (current_stock - reserved_stock) <= 0 THEN 1 ELSE 0 END) as out_of_stock_items,
            SUM(CASE WHEN (current_stock - reserved_stock) <= 5 THEN 1 ELSE 0 END) as critical_stock_items,
            SUM(CASE WHEN (current_stock - reserved_stock) <= reorder_point THEN 1 ELSE 0 END) as low_stock_items,
            AVG(unit_cost) as avg_unit_cost
        FROM inventory_items 
        WHERE is_active = TRUE
    ";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get recent stock movements
 */
function getRecentStockMovements($pdo, $limit = 20) {
    $sql = "
        SELECT 
            sm.*,
            ii.name as item_name,
            ii.sku,
            u.username as performed_by_name
        FROM stock_movements sm
        JOIN inventory_items ii ON sm.inventory_item_id = ii.id
        LEFT JOIN users u ON sm.performed_by = u.id
        ORDER BY sm.performed_at DESC
        LIMIT :limit
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get pending reorders
 */
function getPendingReorders($pdo) {
    $sql = "
        SELECT 
            po.*,
            s.name as supplier_name,
            COUNT(poi.id) as item_count,
            SUM(poi.quantity_ordered) as total_quantity
        FROM purchase_orders po
        JOIN suppliers s ON po.supplier_id = s.id
        LEFT JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
        WHERE po.status IN ('pending', 'sent', 'confirmed')
        GROUP BY po.id
        ORDER BY po.order_date DESC
    ";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>