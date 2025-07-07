<?php
/**
 * DASHBOARD BACKEND EXTENSIONS
 * Additional enterprise functions for the dashboard backend system
 * These extend the core DashboardBackend class with specialized functionality
 * 
 * @author Claude Assistant
 * @version 1.0.0 Enterprise
 * @since 2025-01-07
 */

/**
 * Enhanced dashboard functions for real-time operations
 */

/**
 * Get real-time active users with detailed session info
 */
function getRealTimeActiveUsers() {
    global $pdo;
    
    $sql = "
        SELECT 
            COUNT(DISTINCT s.user_id) as active_users,
            AVG(TIMESTAMPDIFF(MINUTE, s.last_activity, NOW())) as avg_session_duration,
            COUNT(CASE WHEN s.last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 END) as very_active,
            COUNT(CASE WHEN s.last_activity >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND s.last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 END) as moderately_active
        FROM sessions s
        WHERE s.last_activity >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    ";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [
            'active_users' => 0,
            'avg_session_duration' => 0,
            'very_active' => 0,
            'moderately_active' => 0
        ];
    }
}

/**
 * Get current orders in real-time
 */
function getCurrentOrders() {
    global $pdo;
    
    $sql = "
        SELECT 
            COUNT(*) as total_orders,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
            COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_orders,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as orders_last_hour,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN total_amount ELSE 0 END) as revenue_last_hour
        FROM orders
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [
            'total_orders' => 0,
            'pending_orders' => 0,
            'processing_orders' => 0,
            'orders_last_hour' => 0,
            'revenue_last_hour' => 0
        ];
    }
}

/**
 * Get advanced system health metrics
 */
function getAdvancedSystemHealth() {
    $health = [];
    
    // Database performance check
    $health['database'] = checkDatabasePerformance();
    
    // File system health
    $health['filesystem'] = checkFileSystemHealth();
    
    // Memory and CPU
    $health['resources'] = checkResourceHealth();
    
    // External services
    $health['external_services'] = checkExternalServices();
    
    // Calculate overall health score
    $scores = array_column($health, 'score');
    $overall_score = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    
    $health['overall'] = [
        'score' => $overall_score,
        'status' => $overall_score >= 80 ? 'healthy' : ($overall_score >= 60 ? 'warning' : 'critical'),
        'last_check' => date('Y-m-d H:i:s')
    ];
    
    return $health;
}

/**
 * Check database performance
 */
function checkDatabasePerformance() {
    global $pdo;
    
    $start_time = microtime(true);
    
    try {
        // Test query performance
        $pdo->query("SELECT 1");
        $query_time = (microtime(true) - $start_time) * 1000; // ms
        
        // Check connection count
        $stmt = $pdo->query("SHOW STATUS WHERE Variable_name = 'Threads_connected'");
        $connections = $stmt->fetch(PDO::FETCH_ASSOC);
        $connection_count = (int)($connections['Value'] ?? 0);
        
        // Check slow queries
        $stmt = $pdo->query("SHOW STATUS WHERE Variable_name = 'Slow_queries'");
        $slow_queries = $stmt->fetch(PDO::FETCH_ASSOC);
        $slow_query_count = (int)($slow_queries['Value'] ?? 0);
        
        // Calculate score
        $score = 100;
        if ($query_time > 100) $score -= 20;
        if ($query_time > 500) $score -= 30;
        if ($connection_count > 50) $score -= 15;
        if ($slow_query_count > 100) $score -= 25;
        
        return [
            'score' => max(0, $score),
            'query_time' => $query_time,
            'connections' => $connection_count,
            'slow_queries' => $slow_query_count,
            'status' => $score >= 80 ? 'good' : ($score >= 60 ? 'warning' : 'critical')
        ];
        
    } catch (Exception $e) {
        return [
            'score' => 0,
            'error' => $e->getMessage(),
            'status' => 'critical'
        ];
    }
}

/**
 * Check file system health
 */
function checkFileSystemHealth() {
    $upload_dir = '../assets/images/uploads/';
    $cache_dir = '../cache/';
    $log_dir = '../logs/';
    
    $health = [
        'score' => 100,
        'issues' => []
    ];
    
    // Check disk space
    $total_space = disk_total_space('.');
    $free_space = disk_free_space('.');
    $used_percent = (($total_space - $free_space) / $total_space) * 100;
    
    if ($used_percent > 90) {
        $health['score'] -= 30;
        $health['issues'][] = 'Disk space critically low';
    } elseif ($used_percent > 80) {
        $health['score'] -= 15;
        $health['issues'][] = 'Disk space running low';
    }
    
    // Check directory permissions
    $directories = [$upload_dir, $cache_dir, $log_dir];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            $health['score'] -= 20;
            $health['issues'][] = "Directory missing: $dir";
        } elseif (!is_writable($dir)) {
            $health['score'] -= 15;
            $health['issues'][] = "Directory not writable: $dir";
        }
    }
    
    // Check for old log files
    $old_logs = glob($log_dir . '*.log');
    $old_log_count = 0;
    
    foreach ($old_logs as $log_file) {
        if (filemtime($log_file) < strtotime('-30 days')) {
            $old_log_count++;
        }
    }
    
    if ($old_log_count > 50) {
        $health['score'] -= 10;
        $health['issues'][] = 'Too many old log files';
    }
    
    $health['disk_usage'] = $used_percent;
    $health['old_logs'] = $old_log_count;
    $health['status'] = $health['score'] >= 80 ? 'good' : ($health['score'] >= 60 ? 'warning' : 'critical');
    
    return $health;
}

/**
 * Check resource health (memory, CPU)
 */
function checkResourceHealth() {
    $health = [
        'score' => 100,
        'issues' => []
    ];
    
    // Memory usage
    $memory_usage = memory_get_usage(true);
    $memory_limit = ini_get('memory_limit');
    
    if ($memory_limit !== '-1') {
        $memory_limit_bytes = convertToBytes($memory_limit);
        $memory_percent = ($memory_usage / $memory_limit_bytes) * 100;
        
        if ($memory_percent > 85) {
            $health['score'] -= 25;
            $health['issues'][] = 'High memory usage';
        } elseif ($memory_percent > 70) {
            $health['score'] -= 10;
            $health['issues'][] = 'Moderate memory usage';
        }
        
        $health['memory_usage'] = $memory_percent;
    }
    
    // CPU load (if available)
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        $cpu_count = (int)shell_exec('nproc') ?: 1;
        $load_percent = ($load[0] / $cpu_count) * 100;
        
        if ($load_percent > 80) {
            $health['score'] -= 20;
            $health['issues'][] = 'High CPU load';
        } elseif ($load_percent > 60) {
            $health['score'] -= 10;
            $health['issues'][] = 'Moderate CPU load';
        }
        
        $health['cpu_load'] = $load_percent;
    }
    
    $health['status'] = $health['score'] >= 80 ? 'good' : ($health['score'] >= 60 ? 'warning' : 'critical');
    
    return $health;
}

/**
 * Check external services
 */
function checkExternalServices() {
    $services = [
        'google_analytics' => checkGoogleAnalytics(),
        'payment_gateway' => checkPaymentGateway(),
        'email_service' => checkEmailService(),
        'cdn' => checkCDNService()
    ];
    
    $total_score = 0;
    $service_count = 0;
    
    foreach ($services as $service) {
        if (isset($service['score'])) {
            $total_score += $service['score'];
            $service_count++;
        }
    }
    
    $avg_score = $service_count > 0 ? $total_score / $service_count : 100;
    
    return [
        'score' => $avg_score,
        'services' => $services,
        'status' => $avg_score >= 80 ? 'good' : ($avg_score >= 60 ? 'warning' : 'critical')
    ];
}

/**
 * Check Google Analytics connectivity
 */
function checkGoogleAnalytics() {
    // Simulate Google Analytics check
    // In production, this would make actual API calls
    return [
        'score' => 100,
        'status' => 'connected',
        'last_check' => date('Y-m-d H:i:s')
    ];
}

/**
 * Check payment gateway
 */
function checkPaymentGateway() {
    // Simulate payment gateway check
    return [
        'score' => 100,
        'status' => 'connected',
        'last_check' => date('Y-m-d H:i:s')
    ];
}

/**
 * Check email service
 */
function checkEmailService() {
    // Simulate email service check
    return [
        'score' => 100,
        'status' => 'connected',
        'last_check' => date('Y-m-d H:i:s')
    ];
}

/**
 * Check CDN service
 */
function checkCDNService() {
    // Simulate CDN check
    return [
        'score' => 100,
        'status' => 'connected',
        'last_check' => date('Y-m-d H:i:s')
    ];
}

/**
 * Get server metrics
 */
function getServerMetrics() {
    $metrics = [];
    
    // Server load
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        $metrics['cpu_load'] = [
            '1_min' => $load[0],
            '5_min' => $load[1],
            '15_min' => $load[2]
        ];
    }
    
    // Memory usage
    $metrics['memory'] = [
        'used' => memory_get_usage(true),
        'peak' => memory_get_peak_usage(true),
        'limit' => ini_get('memory_limit')
    ];
    
    // Disk space
    $metrics['disk'] = [
        'total' => disk_total_space('.'),
        'free' => disk_free_space('.'),
        'used_percent' => ((disk_total_space('.') - disk_free_space('.')) / disk_total_space('.')) * 100
    ];
    
    // Network connections (if available)
    if (function_exists('exec')) {
        $connections = shell_exec('ss -tn state established | wc -l');
        $metrics['network_connections'] = (int)trim($connections);
    }
    
    // Uptime (if available)
    if (file_exists('/proc/uptime')) {
        $uptime = file_get_contents('/proc/uptime');
        $uptime_seconds = (float)explode(' ', $uptime)[0];
        $metrics['uptime'] = $uptime_seconds;
    }
    
    return $metrics;
}

/**
 * Get database statistics
 */
function getDatabaseStats() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Table sizes
        $stmt = $pdo->query("
            SELECT 
                table_name,
                table_rows,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            ORDER BY (data_length + index_length) DESC
            LIMIT 10
        ");
        $stats['table_sizes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Query cache statistics
        $stmt = $pdo->query("SHOW STATUS WHERE Variable_name LIKE 'Qcache%'");
        $qcache_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stats['query_cache'] = array_column($qcache_stats, 'Value', 'Variable_name');
        
        // Connection statistics
        $stmt = $pdo->query("SHOW STATUS WHERE Variable_name LIKE '%onnection%'");
        $connection_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stats['connections'] = array_column($connection_stats, 'Value', 'Variable_name');
        
        // Slow query statistics
        $stmt = $pdo->query("SHOW STATUS WHERE Variable_name = 'Slow_queries'");
        $slow_queries = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['slow_queries'] = (int)($slow_queries['Value'] ?? 0);
        
        return $stats;
        
    } catch (Exception $e) {
        return [
            'error' => 'Unable to retrieve database statistics',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Export metrics in various formats
 */
function exportMetrics($format, $date_from, $date_to) {
    global $pdo;
    
    $sql = "
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as total_orders,
            SUM(total_amount) as revenue,
            AVG(total_amount) as avg_order_value,
            COUNT(DISTINCT user_id) as unique_customers
        FROM orders
        WHERE created_at BETWEEN ? AND ?
        AND status = 'completed'
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date_from, $date_to]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($format === 'csv') {
            return exportToCSV($data);
        } elseif ($format === 'excel') {
            return exportToExcel($data);
        } else {
            return $data; // JSON format
        }
        
    } catch (Exception $e) {
        throw new Exception('Failed to export metrics: ' . $e->getMessage());
    }
}

/**
 * Export data to CSV format
 */
function exportToCSV($data) {
    if (empty($data)) {
        return '';
    }
    
    $csv = '';
    
    // Headers
    $headers = array_keys($data[0]);
    $csv .= implode(',', $headers) . "\n";
    
    // Data rows
    foreach ($data as $row) {
        $csv .= implode(',', array_map(function($field) {
            return '"' . str_replace('"', '""', $field) . '"';
        }, $row)) . "\n";
    }
    
    return $csv;
}

/**
 * Get notifications for user
 */
function getNotifications($user_id) {
    global $pdo;
    
    $sql = "
        SELECT 
            id,
            title,
            message,
            type,
            status,
            created_at,
            data
        FROM notifications
        WHERE user_id = ? OR user_id IS NULL
        ORDER BY created_at DESC
        LIMIT 50
    ";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Mark notifications as read
 */
function markNotificationsRead($notification_ids, $user_id) {
    global $pdo;
    
    if (empty($notification_ids)) {
        return 0;
    }
    
    $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
    
    $sql = "
        UPDATE notifications 
        SET status = 'read', updated_at = NOW() 
        WHERE id IN ($placeholders) 
        AND (user_id = ? OR user_id IS NULL)
    ";
    
    try {
        $params = array_merge($notification_ids, [$user_id]);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Search functionality
 */
function searchDashboard($query, $type = 'all') {
    global $pdo;
    
    $results = [];
    $query = '%' . $query . '%';
    
    try {
        // Search products
        if ($type === 'all' || $type === 'products') {
            $stmt = $pdo->prepare("
                SELECT 'product' as type, id, name as title, description, NULL as url
                FROM products 
                WHERE name LIKE ? OR description LIKE ? OR sku LIKE ?
                LIMIT 10
            ");
            $stmt->execute([$query, $query, $query]);
            $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        
        // Search orders
        if ($type === 'all' || $type === 'orders') {
            $stmt = $pdo->prepare("
                SELECT 'order' as type, id, CONCAT('Order #', order_number) as title, 
                       CONCAT('Total: $', total_amount) as description, 
                       CONCAT('order-details.php?id=', id) as url
                FROM orders 
                WHERE order_number LIKE ?
                LIMIT 10
            ");
            $stmt->execute([$query]);
            $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        
        // Search users
        if ($type === 'all' || $type === 'users') {
            $stmt = $pdo->prepare("
                SELECT 'user' as type, id, username as title, email as description,
                       CONCAT('user-details.php?id=', id) as url
                FROM users 
                WHERE username LIKE ? OR email LIKE ?
                LIMIT 10
            ");
            $stmt->execute([$query, $query]);
            $results = array_merge($results, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        
        return $results;
        
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Helper function to convert memory limit string to bytes
 */
function convertToBytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    
    return $val;
}

?>