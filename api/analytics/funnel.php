<?php
/**
 * Conversion Funnel Analytics API
 * Handles funnel tracking and abandonment recovery data
 */

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if not exist
    createFunnelTables($pdo);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handleFunnelTracking($pdo);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        handleFunnelAnalytics($pdo);
    } else {
        throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
    error_log("Funnel API Error: " . $e->getMessage());
}

/**
 * Handle funnel event tracking
 */
function handleFunnelTracking($pdo) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $required = ['event', 'sessionId', 'userId', 'timestamp'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Insert funnel event
    $sql = "INSERT INTO funnel_events (
        session_id, user_id, event_type, event_data, step_name, 
        timestamp, url, referrer, user_agent, ip_address, created_at
    ) VALUES (
        :session_id, :user_id, :event_type, :event_data, :step_name,
        :timestamp, :url, :referrer, :user_agent, :ip_address, NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'session_id' => sanitize_string($data['sessionId']),
        'user_id' => sanitize_string($data['userId']),
        'event_type' => sanitize_string($data['event']),
        'event_data' => json_encode($data['data'] ?? []),
        'step_name' => sanitize_string($data['data']['step'] ?? ''),
        'timestamp' => intval($data['timestamp']),
        'url' => sanitize_string($data['url'] ?? ''),
        'referrer' => sanitize_string($data['referrer'] ?? ''),
        'user_agent' => sanitize_string($data['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? ''),
        'ip_address' => getUserIP()
    ]);
    
    // Handle cart abandonment events
    if ($data['event'] === 'cart_abandoned') {
        handleCartAbandonment($pdo, $data);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Funnel event tracked successfully',
        'event_id' => $pdo->lastInsertId()
    ]);
}

/**
 * Handle cart abandonment tracking
 */
function handleCartAbandonment($pdo, $data) {
    $abandonmentData = $data['data'];
    
    $sql = "INSERT INTO cart_abandonments (
        session_id, user_id, abandonment_reason, funnel_step, cart_value, 
        item_count, cart_items, time_in_funnel, previous_steps, 
        timestamp, created_at
    ) VALUES (
        :session_id, :user_id, :reason, :step, :cart_value,
        :item_count, :cart_items, :time_in_funnel, :previous_steps,
        :timestamp, NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'session_id' => sanitize_string($abandonmentData['sessionId']),
        'user_id' => sanitize_string($abandonmentData['userId']),
        'reason' => sanitize_string($abandonmentData['reason']),
        'step' => sanitize_string($abandonmentData['step']),
        'cart_value' => floatval($abandonmentData['cartValue'] ?? 0),
        'item_count' => intval($abandonmentData['itemCount'] ?? 0),
        'cart_items' => json_encode($abandonmentData['items'] ?? []),
        'time_in_funnel' => intval($abandonmentData['timeInFunnel'] ?? 0),
        'previous_steps' => json_encode($abandonmentData['previousSteps'] ?? []),
        'timestamp' => intval($abandonmentData['timestamp'])
    ]);
}

/**
 * Handle funnel analytics requests
 */
function handleFunnelAnalytics($pdo) {
    $type = $_GET['type'] ?? 'summary';
    $timeframe = $_GET['timeframe'] ?? '7d';
    
    switch ($type) {
        case 'summary':
            $data = getFunnelSummary($pdo, $timeframe);
            break;
        case 'conversion_rates':
            $data = getConversionRates($pdo, $timeframe);
            break;
        case 'abandonment_analysis':
            $data = getAbandonmentAnalysis($pdo, $timeframe);
            break;
        case 'user_segments':
            $data = getUserSegmentAnalysis($pdo, $timeframe);
            break;
        case 'step_performance':
            $data = getStepPerformance($pdo, $timeframe);
            break;
        default:
            throw new Exception('Invalid analytics type');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timeframe' => $timeframe,
        'generated_at' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Get funnel summary statistics
 */
function getFunnelSummary($pdo, $timeframe) {
    $whereClause = getTimeframeClause($timeframe);
    
    // Total sessions
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT session_id) as total_sessions
        FROM funnel_events 
        WHERE {$whereClause}
    ");
    $totalSessions = $stmt->fetch(PDO::FETCH_ASSOC)['total_sessions'];
    
    // Completed purchases
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT session_id) as completed_purchases
        FROM funnel_events 
        WHERE event_type = 'purchase_complete' AND {$whereClause}
    ");
    $completedPurchases = $stmt->fetch(PDO::FETCH_ASSOC)['completed_purchases'];
    
    // Cart abandonment rate
    $stmt = $pdo->query("
        SELECT COUNT(*) as total_abandonments
        FROM cart_abandonments 
        WHERE {$whereClause}
    ");
    $totalAbandonments = $stmt->fetch(PDO::FETCH_ASSOC)['total_abandonments'];
    
    // Average cart value
    $stmt = $pdo->query("
        SELECT AVG(cart_value) as avg_cart_value
        FROM cart_abandonments 
        WHERE {$whereClause} AND cart_value > 0
    ");
    $avgCartValue = $stmt->fetch(PDO::FETCH_ASSOC)['avg_cart_value'] ?? 0;
    
    // Conversion rate
    $conversionRate = $totalSessions > 0 ? ($completedPurchases / $totalSessions) * 100 : 0;
    
    // Abandonment rate
    $abandonmentRate = $totalSessions > 0 ? ($totalAbandonments / $totalSessions) * 100 : 0;
    
    return [
        'total_sessions' => $totalSessions,
        'completed_purchases' => $completedPurchases,
        'conversion_rate' => round($conversionRate, 2),
        'total_abandonments' => $totalAbandonments,
        'abandonment_rate' => round($abandonmentRate, 2),
        'avg_cart_value' => round($avgCartValue, 2)
    ];
}

/**
 * Get conversion rates by funnel step
 */
function getConversionRates($pdo, $timeframe) {
    $whereClause = getTimeframeClause($timeframe);
    
    $sql = "
        SELECT 
            event_type,
            COUNT(DISTINCT session_id) as sessions,
            COUNT(*) as events
        FROM funnel_events 
        WHERE {$whereClause}
        GROUP BY event_type
        ORDER BY 
            CASE event_type
                WHEN 'product_view' THEN 1
                WHEN 'add_to_cart' THEN 2
                WHEN 'cart_view' THEN 3
                WHEN 'checkout_start' THEN 4
                WHEN 'checkout_info' THEN 5
                WHEN 'checkout_shipping' THEN 6
                WHEN 'checkout_payment' THEN 7
                WHEN 'purchase_complete' THEN 8
                ELSE 9
            END
    ";
    
    $stmt = $pdo->query($sql);
    $steps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate conversion rates
    $totalSessions = 0;
    $result = [];
    
    foreach ($steps as $step) {
        if ($step['event_type'] === 'product_view') {
            $totalSessions = $step['sessions'];
        }
        
        $conversionRate = $totalSessions > 0 ? ($step['sessions'] / $totalSessions) * 100 : 0;
        
        $result[] = [
            'step' => $step['event_type'],
            'sessions' => $step['sessions'],
            'events' => $step['events'],
            'conversion_rate' => round($conversionRate, 2)
        ];
    }
    
    return $result;
}

/**
 * Get abandonment analysis
 */
function getAbandonmentAnalysis($pdo, $timeframe) {
    $whereClause = getTimeframeClause($timeframe);
    
    // Abandonment by reason
    $stmt = $pdo->query("
        SELECT 
            abandonment_reason,
            COUNT(*) as count,
            AVG(cart_value) as avg_cart_value,
            AVG(time_in_funnel) as avg_time_in_funnel
        FROM cart_abandonments 
        WHERE {$whereClause}
        GROUP BY abandonment_reason
        ORDER BY count DESC
    ");
    $byReason = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Abandonment by funnel step
    $stmt = $pdo->query("
        SELECT 
            funnel_step,
            COUNT(*) as count,
            AVG(cart_value) as avg_cart_value
        FROM cart_abandonments 
        WHERE {$whereClause}
        GROUP BY funnel_step
        ORDER BY count DESC
    ");
    $byStep = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Time analysis
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN time_in_funnel < 60000 THEN 'Under 1 min'
                WHEN time_in_funnel < 300000 THEN '1-5 mins'
                WHEN time_in_funnel < 900000 THEN '5-15 mins'
                WHEN time_in_funnel < 1800000 THEN '15-30 mins'
                ELSE 'Over 30 mins'
            END as time_range,
            COUNT(*) as count,
            AVG(cart_value) as avg_cart_value
        FROM cart_abandonments 
        WHERE {$whereClause}
        GROUP BY time_range
        ORDER BY 
            CASE time_range
                WHEN 'Under 1 min' THEN 1
                WHEN '1-5 mins' THEN 2
                WHEN '5-15 mins' THEN 3
                WHEN '15-30 mins' THEN 4
                WHEN 'Over 30 mins' THEN 5
            END
    ");
    $byTime = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'by_reason' => $byReason,
        'by_step' => $byStep,
        'by_time' => $byTime
    ];
}

/**
 * Get user segment analysis
 */
function getUserSegmentAnalysis($pdo, $timeframe) {
    $whereClause = getTimeframeClause($timeframe);
    
    // This would typically pull from user behavior data
    // For now, we'll simulate segments based on funnel behavior
    
    $sql = "
        SELECT 
            user_id,
            COUNT(DISTINCT session_id) as sessions,
            COUNT(CASE WHEN event_type = 'purchase_complete' THEN 1 END) as purchases,
            COUNT(CASE WHEN event_type = 'add_to_cart' THEN 1 END) as cart_adds,
            MIN(timestamp) as first_visit,
            MAX(timestamp) as last_visit
        FROM funnel_events 
        WHERE {$whereClause}
        GROUP BY user_id
    ";
    
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $segments = [
        'new_visitors' => 0,
        'returning_customers' => 0,
        'high_intent_non_buyers' => 0,
        'browsers' => 0
    ];
    
    foreach ($users as $user) {
        if ($user['sessions'] == 1 && $user['purchases'] == 0) {\n            $segments['new_visitors']++;\n        } elseif ($user['purchases'] > 0) {\n            $segments['returning_customers']++;\n        } elseif ($user['cart_adds'] > 2 && $user['purchases'] == 0) {\n            $segments['high_intent_non_buyers']++;\n        } else {\n            $segments['browsers']++;\n        }\n    }\n    \n    return $segments;\n}\n\n/**\n * Get step performance analysis\n */\nfunction getStepPerformance($pdo, $timeframe) {\n    $whereClause = getTimeframeClause($timeframe);\n    \n    $sql = \"\n        SELECT \n            event_type as step,\n            COUNT(*) as total_events,\n            COUNT(DISTINCT session_id) as unique_sessions,\n            AVG(CASE \n                WHEN JSON_EXTRACT(event_data, '$.timeOnPreviousStep') IS NOT NULL \n                THEN JSON_EXTRACT(event_data, '$.timeOnPreviousStep')\n                ELSE NULL \n            END) as avg_time_on_step,\n            COUNT(CASE \n                WHEN JSON_EXTRACT(event_data, '$.timeOnPreviousStep') > 60000 \n                THEN 1 \n            END) as long_duration_events\n        FROM funnel_events \n        WHERE {$whereClause}\n        GROUP BY event_type\n        ORDER BY \n            CASE event_type\n                WHEN 'product_view' THEN 1\n                WHEN 'add_to_cart' THEN 2\n                WHEN 'cart_view' THEN 3\n                WHEN 'checkout_start' THEN 4\n                WHEN 'checkout_info' THEN 5\n                WHEN 'checkout_shipping' THEN 6\n                WHEN 'checkout_payment' THEN 7\n                WHEN 'purchase_complete' THEN 8\n                ELSE 9\n            END\n    \";\n    \n    $stmt = $pdo->query($sql);\n    return $stmt->fetchAll(PDO::FETCH_ASSOC);\n}\n\n/**\n * Create funnel tracking tables\n */\nfunction createFunnelTables($pdo) {\n    // Funnel events table\n    $sql = \"CREATE TABLE IF NOT EXISTS funnel_events (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        session_id VARCHAR(100) NOT NULL,\n        user_id VARCHAR(100) NOT NULL,\n        event_type VARCHAR(50) NOT NULL,\n        event_data JSON,\n        step_name VARCHAR(50),\n        timestamp BIGINT NOT NULL,\n        url TEXT,\n        referrer TEXT,\n        user_agent TEXT,\n        ip_address VARCHAR(45),\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        INDEX idx_session (session_id),\n        INDEX idx_user (user_id),\n        INDEX idx_event (event_type),\n        INDEX idx_timestamp (timestamp),\n        INDEX idx_created (created_at)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\";\n    \n    $pdo->exec($sql);\n    \n    // Cart abandonments table\n    $sql = \"CREATE TABLE IF NOT EXISTS cart_abandonments (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        session_id VARCHAR(100) NOT NULL,\n        user_id VARCHAR(100) NOT NULL,\n        abandonment_reason VARCHAR(50) NOT NULL,\n        funnel_step VARCHAR(50) NOT NULL,\n        cart_value DECIMAL(10,2) DEFAULT 0,\n        item_count INT DEFAULT 0,\n        cart_items JSON,\n        time_in_funnel INT DEFAULT 0,\n        previous_steps JSON,\n        recovery_email_sent BOOLEAN DEFAULT FALSE,\n        recovery_email_opened BOOLEAN DEFAULT FALSE,\n        recovery_clicked BOOLEAN DEFAULT FALSE,\n        recovered BOOLEAN DEFAULT FALSE,\n        timestamp BIGINT NOT NULL,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        INDEX idx_session (session_id),\n        INDEX idx_user (user_id),\n        INDEX idx_reason (abandonment_reason),\n        INDEX idx_step (funnel_step),\n        INDEX idx_value (cart_value),\n        INDEX idx_created (created_at)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\";\n    \n    $pdo->exec($sql);\n    \n    // Email recovery tracking table\n    $sql = \"CREATE TABLE IF NOT EXISTS email_recovery (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        abandonment_id INT NOT NULL,\n        email_address VARCHAR(255) NOT NULL,\n        email_type ENUM('immediate', '1_hour', '24_hour', '72_hour') NOT NULL,\n        sent_at TIMESTAMP NULL,\n        opened_at TIMESTAMP NULL,\n        clicked_at TIMESTAMP NULL,\n        recovered_at TIMESTAMP NULL,\n        email_subject VARCHAR(255),\n        email_template VARCHAR(100),\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        FOREIGN KEY (abandonment_id) REFERENCES cart_abandonments(id),\n        INDEX idx_abandonment (abandonment_id),\n        INDEX idx_email (email_address),\n        INDEX idx_type (email_type),\n        INDEX idx_sent (sent_at)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\";\n    \n    $pdo->exec($sql);\n}\n\n/**\n * Get timeframe WHERE clause\n */\nfunction getTimeframeClause($timeframe) {\n    switch ($timeframe) {\n        case '1d':\n            return \"created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)\";\n        case '7d':\n            return \"created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)\";\n        case '30d':\n            return \"created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)\";\n        case '90d':\n            return \"created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)\";\n        default:\n            return \"created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)\";\n    }\n}\n\n/**\n * Get user's real IP address\n */\nfunction getUserIP() {\n    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];\n    \n    foreach ($ipKeys as $key) {\n        if (!empty($_SERVER[$key])) {\n            $ips = explode(',', $_SERVER[$key]);\n            $ip = trim($ips[0]);\n            \n            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {\n                return $ip;\n            }\n        }\n    }\n    \n    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';\n}\n\n/**\n * Sanitize string input\n */\nfunction sanitize_string($str) {\n    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');\n}\n?>