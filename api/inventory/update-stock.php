<?php
/**
 * Inventory Management API - Update Stock
 * Handles stock updates, purchases, adjustments, and movements
 */

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Connect to database
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $pdo->beginTransaction();
    
    $updatedStock = [];
    $movements = [];
    
    // Process each stock update
    foreach ($data['updates'] as $update) {
        $result = processStockUpdate($pdo, $update);
        if ($result) {
            $updatedStock[$update['productId']] = $result['newStock'];
            $movements[] = $result['movement'];
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Send real-time notifications
    sendStockUpdateNotifications($movements);
    
    echo json_encode([
        'success' => true,
        'message' => 'Stock updated successfully',
        'updatedStock' => $updatedStock,
        'movements' => $movements,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
    error_log("Stock Update API Error: " . $e->getMessage());
}

/**
 * Process individual stock update
 */
function processStockUpdate($pdo, $update) {
    // Validate required fields
    $required = ['productId', 'quantity', 'type'];
    foreach ($required as $field) {
        if (!isset($update[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    $productId = sanitize_string($update['productId']);
    $quantity = intval($update['quantity']);
    $type = sanitize_string($update['type']);
    $orderId = sanitize_string($update['orderId'] ?? '');
    $reason = sanitize_string($update['reason'] ?? '');
    $unitCost = floatval($update['unitCost'] ?? 0);
    $performedBy = intval($update['performedBy'] ?? 0);
    
    // Get current inventory item
    $stmt = $pdo->prepare("
        SELECT * FROM inventory_items 
        WHERE id = :product_id OR sku = :sku OR product_id = :alt_product_id
        LIMIT 1
    ");
    $stmt->execute([
        'product_id' => $productId,
        'sku' => $productId,
        'alt_product_id' => $productId
    ]);
    
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        throw new Exception("Product not found: {$productId}");
    }
    
    $itemId = $item['id'];
    $stockBefore = intval($item['current_stock']);
    $reservedBefore = intval($item['reserved_stock']);
    
    // Calculate new stock based on movement type
    $stockChange = 0;
    $reservedChange = 0;
    
    switch ($type) {
        case 'purchase':
        case 'sale':
            if ($quantity > ($stockBefore - $reservedBefore)) {
                throw new Exception("Insufficient available stock. Available: " . ($stockBefore - $reservedBefore) . ", Requested: {$quantity}");
            }
            $stockChange = -$quantity;
            $reservedChange = -min($reservedBefore, $quantity); // Reduce reservations up to quantity sold
            break;
            
        case 'restock':
        case 'adjustment_positive':
            $stockChange = $quantity;
            break;
            
        case 'adjustment_negative':
            if ($quantity > $stockBefore) {
                throw new Exception("Cannot reduce stock below zero");
            }
            $stockChange = -$quantity;
            break;
            
        case 'damaged':
        case 'expired':
            if ($quantity > $stockBefore) {
                throw new Exception("Cannot remove more stock than available");
            }
            $stockChange = -$quantity;
            break;
            
        case 'transfer_out':
            if ($quantity > ($stockBefore - $reservedBefore)) {
                throw new Exception("Insufficient available stock for transfer");
            }
            $stockChange = -$quantity;
            break;
            
        case 'transfer_in':
            $stockChange = $quantity;
            break;
            
        default:
            throw new Exception("Invalid movement type: {$type}");
    }
    
    $newStock = $stockBefore + $stockChange;
    $newReserved = max(0, $reservedBefore + $reservedChange);
    
    // Validate final stock levels
    if ($newStock < 0) {
        throw new Exception("Stock cannot be negative");
    }
    
    if ($newReserved > $newStock) {
        $newReserved = $newStock; // Cap reservations at available stock
    }
    
    // Update inventory item
    $stmt = $pdo->prepare("
        UPDATE inventory_items 
        SET current_stock = :new_stock,
            reserved_stock = :new_reserved,
            updated_at = NOW()
        WHERE id = :item_id
    ");
    
    $stmt->execute([
        'new_stock' => $newStock,
        'new_reserved' => $newReserved,
        'item_id' => $itemId
    ]);
    
    // Record stock movement
    $movementId = recordStockMovement($pdo, [
        'inventory_item_id' => $itemId,
        'movement_type' => $type,
        'quantity_change' => $stockChange,
        'stock_before' => $stockBefore,
        'stock_after' => $newStock,
        'unit_cost' => $unitCost,
        'total_cost' => abs($stockChange) * $unitCost,
        'reference_type' => $orderId ? 'order' : 'manual',
        'reference_id' => $orderId,
        'reason' => $reason ?: ucfirst($type),
        'performed_by' => $performedBy
    ]);
    
    // Check for low stock alerts
    checkStockAlerts($pdo, $itemId, $newStock, $newReserved);
    
    return [
        'itemId' => $itemId,
        'oldStock' => $stockBefore,
        'newStock' => $newStock,
        'stockChange' => $stockChange,
        'movement' => [
            'id' => $movementId,
            'type' => $type,
            'quantity' => abs($stockChange),
            'item_name' => $item['name'],
            'sku' => $item['sku']
        ]
    ];
}

/**
 * Record stock movement
 */
function recordStockMovement($pdo, $data) {
    $stmt = $pdo->prepare("
        INSERT INTO stock_movements (
            inventory_item_id, movement_type, quantity_change, stock_before, stock_after,
            unit_cost, total_cost, reference_type, reference_id, reason, performed_by
        ) VALUES (
            :inventory_item_id, :movement_type, :quantity_change, :stock_before, :stock_after,
            :unit_cost, :total_cost, :reference_type, :reference_id, :reason, :performed_by
        )
    ");
    
    $stmt->execute($data);
    return $pdo->lastInsertId();
}

/**
 * Check for stock alerts
 */
function checkStockAlerts($pdo, $itemId, $currentStock, $reservedStock) {
    // Get item details
    $stmt = $pdo->prepare("
        SELECT name, sku, reorder_point, reorder_quantity, supplier_id
        FROM inventory_items 
        WHERE id = :item_id
    ");
    $stmt->execute(['item_id' => $itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) return;
    
    $availableStock = $currentStock - $reservedStock;
    $reorderPoint = intval($item['reorder_point']);
    
    // Create alert if stock is critical
    if ($availableStock <= 0) {
        createStockAlert($pdo, $itemId, 'out_of_stock', "Product {$item['name']} is out of stock");
        
        // Trigger automatic reorder if configured
        triggerAutomaticReorder($pdo, $itemId, $item);
        
    } elseif ($availableStock <= 5) {
        createStockAlert($pdo, $itemId, 'critical_stock', "Product {$item['name']} has critical stock ({$availableStock} remaining)");
        
    } elseif ($availableStock <= $reorderPoint) {
        createStockAlert($pdo, $itemId, 'low_stock', "Product {$item['name']} has low stock ({$availableStock} remaining)");
        
        // Consider reorder
        considerReorder($pdo, $itemId, $item);
    }
}

/**
 * Create stock alert
 */
function createStockAlert($pdo, $itemId, $alertType, $message) {
    // Check if similar alert already exists recently
    $stmt = $pdo->prepare("
        SELECT id FROM stock_alerts 
        WHERE inventory_item_id = :item_id 
        AND alert_type = :alert_type 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        LIMIT 1
    ");
    $stmt->execute(['item_id' => $itemId, 'alert_type' => $alertType]);
    
    if ($stmt->fetch()) {
        return; // Alert already exists recently
    }
    
    // Create alerts table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS stock_alerts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            inventory_item_id INT NOT NULL,
            alert_type ENUM('low_stock', 'critical_stock', 'out_of_stock', 'reorder_triggered') NOT NULL,
            message TEXT NOT NULL,
            priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            is_acknowledged BOOLEAN DEFAULT FALSE,
            acknowledged_by INT NULL,
            acknowledged_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
            INDEX idx_item (inventory_item_id),
            INDEX idx_type (alert_type),
            INDEX idx_created (created_at),
            INDEX idx_acknowledged (is_acknowledged)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Insert alert
    $priority = ($alertType === 'out_of_stock') ? 'critical' : 
                (($alertType === 'critical_stock') ? 'high' : 'medium');
    
    $stmt = $pdo->prepare("
        INSERT INTO stock_alerts (inventory_item_id, alert_type, message, priority)
        VALUES (:item_id, :alert_type, :message, :priority)
    ");
    
    $stmt->execute([
        'item_id' => $itemId,
        'alert_type' => $alertType,
        'message' => $message,
        'priority' => $priority
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Trigger automatic reorder
 */
function triggerAutomaticReorder($pdo, $itemId, $item) {
    // Check system settings for auto-reorder
    $autoReorderEnabled = getSetting($pdo, 'auto_reorder_enabled', false);
    if (!$autoReorderEnabled) {
        return;
    }
    
    // Check if reorder already pending
    $stmt = $pdo->prepare("
        SELECT id FROM purchase_orders po
        JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
        WHERE poi.inventory_item_id = :item_id 
        AND po.status IN ('pending', 'sent', 'confirmed')
        LIMIT 1
    ");
    $stmt->execute(['item_id' => $itemId]);
    
    if ($stmt->fetch()) {
        return; // Reorder already pending
    }
    
    // Create automatic reorder
    $reorderQuantity = intval($item['reorder_quantity']) ?: 50;
    $supplierId = intval($item['supplier_id']);
    
    if (!$supplierId) {
        error_log("Cannot create auto-reorder for item {$itemId}: No supplier configured");
        return;
    }
    
    createAutomaticPurchaseOrder($pdo, $supplierId, [
        'inventory_item_id' => $itemId,
        'quantity' => $reorderQuantity,
        'unit_cost' => getLatestUnitCost($pdo, $itemId),
        'urgent' => true
    ]);
}

/**
 * Consider reorder (non-automatic)
 */
function considerReorder($pdo, $itemId, $item) {
    // Create reorder suggestion alert
    createStockAlert($pdo, $itemId, 'reorder_suggested', 
        "Consider reordering {$item['name']} (Suggested quantity: {$item['reorder_quantity']})");
}

/**
 * Create automatic purchase order
 */
function createAutomaticPurchaseOrder($pdo, $supplierId, $items) {
    // Generate order number
    $orderNumber = 'AUTO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Calculate total amount
    $totalAmount = 0;
    if (!is_array($items)) {
        $items = [$items];
    }
    
    foreach ($items as $item) {
        $totalAmount += $item['quantity'] * $item['unit_cost'];
    }
    
    // Create purchase order
    $stmt = $pdo->prepare("
        INSERT INTO purchase_orders (
            order_number, supplier_id, status, total_amount, 
            expected_delivery_date, created_by, notes
        ) VALUES (
            :order_number, :supplier_id, 'pending', :total_amount,
            DATE_ADD(NOW(), INTERVAL 7 DAY), 0, 'Automatic reorder triggered by low stock'
        )
    ");
    
    $stmt->execute([
        'order_number' => $orderNumber,
        'supplier_id' => $supplierId,
        'total_amount' => $totalAmount
    ]);
    
    $purchaseOrderId = $pdo->lastInsertId();
    
    // Add items to purchase order
    $stmt = $pdo->prepare("
        INSERT INTO purchase_order_items (
            purchase_order_id, inventory_item_id, quantity_ordered, unit_cost, total_cost
        ) VALUES (
            :purchase_order_id, :inventory_item_id, :quantity_ordered, :unit_cost, :total_cost
        )
    ");
    
    foreach ($items as $item) {
        $stmt->execute([
            'purchase_order_id' => $purchaseOrderId,
            'inventory_item_id' => $item['inventory_item_id'],
            'quantity_ordered' => $item['quantity'],
            'unit_cost' => $item['unit_cost'],
            'total_cost' => $item['quantity'] * $item['unit_cost']
        ]);
    }
    
    // Send notification
    sendReorderNotification($pdo, $purchaseOrderId);
    
    return $purchaseOrderId;
}

/**
 * Get system setting
 */
function getSetting($pdo, $key, $default = null) {
    try {
        $stmt = $pdo->prepare("SELECT value FROM system_settings WHERE setting_key = :key");
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Get latest unit cost for item
 */
function getLatestUnitCost($pdo, $itemId) {
    $stmt = $pdo->prepare("
        SELECT unit_cost FROM stock_movements 
        WHERE inventory_item_id = :item_id 
        AND unit_cost > 0 
        ORDER BY performed_at DESC 
        LIMIT 1
    ");
    $stmt->execute(['item_id' => $itemId]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        return floatval($result['unit_cost']);
    }
    
    // Fallback to item's unit cost
    $stmt = $pdo->prepare("SELECT unit_cost FROM inventory_items WHERE id = :item_id");
    $stmt->execute(['item_id' => $itemId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? floatval($result['unit_cost']) : 0.00;
}

/**
 * Send stock update notifications
 */
function sendStockUpdateNotifications($movements) {
    // This would integrate with notification systems
    // For now, just log the movements
    foreach ($movements as $movement) {
        error_log("Stock Movement: {$movement['type']} - {$movement['item_name']} ({$movement['sku']}) - Quantity: {$movement['quantity']}");
    }
}

/**
 * Send reorder notification
 */
function sendReorderNotification($pdo, $purchaseOrderId) {
    // This would send email/SMS/Slack notification
    // For now, just log
    error_log("Automatic reorder created: Purchase Order ID {$purchaseOrderId}");
}

/**
 * Sanitize string input
 */
function sanitize_string($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}
?>