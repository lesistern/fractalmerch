<?php
/**
 * Heatmap Event Tracking API
 * Handles user behavior events for heatmap and session recording analysis
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
    createHeatmapTables($pdo);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handleEventTracking($pdo);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        handleHeatmapAnalytics($pdo);
    } else {
        throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
    error_log("Heatmap API Error: " . $e->getMessage());
}

/**
 * Handle event tracking
 */
function handleEventTracking($pdo) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $required = ['type', 'sessionId', 'userId', 'timestamp'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Check rate limiting
    if (!checkRateLimit($pdo, $data['sessionId'])) {
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded']);
        return;
    }
    
    // Insert heatmap event
    $eventId = insertHeatmapEvent($pdo, $data);
    
    // Process special event types
    switch ($data['type']) {
        case 'click':
            processClickEvent($pdo, $eventId, $data);
            break;
        case 'scroll':
            processScrollEvent($pdo, $eventId, $data);
            break;
        case 'custom':
            processCustomEvent($pdo, $eventId, $data);
            break;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Event tracked successfully',
        'event_id' => $eventId
    ]);
}

/**
 * Insert heatmap event
 */
function insertHeatmapEvent($pdo, $data) {
    $sql = "INSERT INTO heatmap_events (
        session_id, user_id, event_type, event_data, page_url, 
        timestamp, ip_address, user_agent, viewport_width, viewport_height,
        created_at
    ) VALUES (
        :session_id, :user_id, :event_type, :event_data, :page_url,
        :timestamp, :ip_address, :user_agent, :viewport_width, :viewport_height,
        NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'session_id' => sanitize_string($data['sessionId']),
        'user_id' => sanitize_string($data['userId']),
        'event_type' => sanitize_string($data['type']),
        'event_data' => json_encode($data['data'] ?? []),
        'page_url' => sanitize_string($data['url'] ?? ''),
        'timestamp' => intval($data['timestamp']),
        'ip_address' => getUserIP(),
        'user_agent' => sanitize_string($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'viewport_width' => intval($data['data']['viewport']['width'] ?? 0),
        'viewport_height' => intval($data['data']['viewport']['height'] ?? 0)
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Process click event
 */
function processClickEvent($pdo, $eventId, $data) {
    $clickData = $data['data'];
    
    $sql = "INSERT INTO click_events (
        event_id, x_coordinate, y_coordinate, page_x, page_y,
        target_selector, target_text, tag_name, class_name, element_id,
        created_at
    ) VALUES (
        :event_id, :x_coord, :y_coord, :page_x, :page_y,
        :target_selector, :target_text, :tag_name, :class_name, :element_id,
        NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'event_id' => $eventId,
        'x_coord' => intval($clickData['x'] ?? 0),
        'y_coord' => intval($clickData['y'] ?? 0),
        'page_x' => intval($clickData['pageX'] ?? 0),
        'page_y' => intval($clickData['pageY'] ?? 0),
        'target_selector' => sanitize_string($clickData['target'] ?? ''),
        'target_text' => sanitize_string(substr($clickData['text'] ?? '', 0, 255)),
        'tag_name' => sanitize_string($clickData['tagName'] ?? ''),
        'class_name' => sanitize_string($clickData['className'] ?? ''),
        'element_id' => sanitize_string($clickData['id'] ?? '')
    ]);
}

/**
 * Process scroll event
 */
function processScrollEvent($pdo, $eventId, $data) {
    $scrollData = $data['data'];
    
    $sql = "INSERT INTO scroll_events (
        event_id, scroll_depth_percent, max_scroll_reached,
        time_to_depth, page_height, created_at
    ) VALUES (
        :event_id, :scroll_depth, :max_scroll, :time_to_depth, :page_height, NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'event_id' => $eventId,
        'scroll_depth' => intval($scrollData['depth_percent'] ?? 0),
        'max_scroll' => intval($scrollData['max_scroll_percent'] ?? 0),
        'time_to_depth' => intval($scrollData['time_to_depth'] ?? 0),
        'page_height' => intval($scrollData['page_height'] ?? 0)
    ]);
}

/**
 * Process custom event
 */
function processCustomEvent($pdo, $eventId, $data) {
    $customData = $data['data'];
    
    $sql = "INSERT INTO custom_events (
        event_id, event_name, event_properties, category, label, value,
        created_at
    ) VALUES (
        :event_id, :event_name, :event_properties, :category, :label, :value, NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'event_id' => $eventId,
        'event_name' => sanitize_string($customData['eventName'] ?? ''),
        'event_properties' => json_encode($customData['properties'] ?? []),
        'category' => sanitize_string($customData['properties']['category'] ?? ''),
        'label' => sanitize_string($customData['properties']['label'] ?? ''),
        'value' => floatval($customData['properties']['value'] ?? 0)
    ]);
}

/**
 * Check rate limiting
 */
function checkRateLimit($pdo, $sessionId) {
    $sql = "SELECT COUNT(*) as event_count 
            FROM heatmap_events 
            WHERE session_id = ? 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sessionId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Allow max 60 events per minute per session
    return $result['event_count'] < 60;
}

/**
 * Handle heatmap analytics requests
 */
function handleHeatmapAnalytics($pdo) {
    $type = $_GET['type'] ?? 'summary';
    $page = $_GET['page'] ?? '';
    $timeframe = $_GET['timeframe'] ?? '7d';
    
    switch ($type) {
        case 'summary':
            $data = getHeatmapSummary($pdo, $timeframe);
            break;
        case 'clicks':
            $data = getClickHeatmap($pdo, $page, $timeframe);
            break;
        case 'scrolls':
            $data = getScrollAnalysis($pdo, $page, $timeframe);
            break;
        case 'user_flow':
            $data = getUserFlow($pdo, $timeframe);
            break;
        case 'session_recordings':
            $data = getSessionRecordings($pdo, $timeframe);
            break;
        default:
            throw new Exception('Invalid analytics type');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'type' => $type,
        'timeframe' => $timeframe,
        'generated_at' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Get heatmap summary statistics
 */
function getHeatmapSummary($pdo, $timeframe) {
    $whereClause = getTimeframeClause($timeframe);
    
    // Total sessions and events
    $stmt = $pdo->query("
        SELECT 
            COUNT(DISTINCT session_id) as total_sessions,
            COUNT(*) as total_events,
            COUNT(DISTINCT user_id) as unique_users
        FROM heatmap_events 
        WHERE {$whereClause}
    ");
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Most clicked elements
    $stmt = $pdo->query("
        SELECT 
            ce.target_selector,
            ce.target_text,
            COUNT(*) as click_count
        FROM heatmap_events he
        JOIN click_events ce ON he.id = ce.event_id
        WHERE he.{$whereClause}
        GROUP BY ce.target_selector, ce.target_text
        ORDER BY click_count DESC
        LIMIT 10
    ");
    $topClicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top pages
    $stmt = $pdo->query("
        SELECT 
            page_url,
            COUNT(DISTINCT session_id) as session_count,
            COUNT(*) as event_count
        FROM heatmap_events 
        WHERE {$whereClause}
        GROUP BY page_url
        ORDER BY session_count DESC
        LIMIT 10
    ");
    $topPages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Average session duration
    $stmt = $pdo->query("
        SELECT 
            session_id,
            MIN(timestamp) as session_start,
            MAX(timestamp) as session_end
        FROM heatmap_events 
        WHERE {$whereClause}
        GROUP BY session_id
    ");
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalDuration = 0;
    $sessionCount = count($sessions);
    
    foreach ($sessions as $session) {
        $totalDuration += ($session['session_end'] - $session['session_start']);
    }
    
    $avgDuration = $sessionCount > 0 ? $totalDuration / $sessionCount : 0;
    
    return [
        'totals' => $totals,
        'top_clicks' => $topClicks,
        'top_pages' => $topPages,
        'avg_session_duration' => round($avgDuration / 1000), // Convert to seconds
        'session_count' => $sessionCount
    ];
}

/**
 * Get click heatmap data
 */
function getClickHeatmap($pdo, $page, $timeframe) {
    $whereClause = getTimeframeClause($timeframe);
    $pageClause = $page ? "AND he.page_url LIKE :page" : "";
    
    $sql = "
        SELECT 
            ce.x_coordinate,
            ce.y_coordinate,
            ce.target_selector,
            ce.target_text,
            he.viewport_width,
            he.viewport_height,
            COUNT(*) as click_count
        FROM heatmap_events he
        JOIN click_events ce ON he.id = ce.event_id
        WHERE he.{$whereClause} {$pageClause}
        GROUP BY ce.x_coordinate, ce.y_coordinate, ce.target_selector, 
                 he.viewport_width, he.viewport_height
        ORDER BY click_count DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    
    if ($page) {
        $stmt->execute(['page' => "%{$page}%"]);
    } else {
        $stmt->execute();
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get scroll analysis
 */
function getScrollAnalysis($pdo, $page, $timeframe) {
    $whereClause = getTimeframeClause($timeframe);
    $pageClause = $page ? "AND he.page_url LIKE :page" : "";
    
    $sql = "
        SELECT 
            se.scroll_depth_percent,
            COUNT(*) as user_count,
            AVG(se.time_to_depth) as avg_time_to_depth
        FROM heatmap_events he
        JOIN scroll_events se ON he.id = se.event_id
        WHERE he.{$whereClause} {$pageClause}
        GROUP BY se.scroll_depth_percent
        ORDER BY se.scroll_depth_percent
    ";
    
    $stmt = $pdo->prepare($sql);
    
    if ($page) {
        $stmt->execute(['page' => "%{$page}%"]);
    } else {
        $stmt->execute();
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get user flow analysis
 */
function getUserFlow($pdo, $timeframe) {
    $whereClause = getTimeframeClause($timeframe);
    
    // Get page transitions
    $sql = "
        SELECT 
            prev.page_url as from_page,
            curr.page_url as to_page,
            COUNT(DISTINCT curr.session_id) as transition_count
        FROM heatmap_events prev
        JOIN heatmap_events curr ON prev.session_id = curr.session_id 
            AND curr.timestamp > prev.timestamp
        WHERE prev.{$whereClause}
        AND NOT EXISTS (
            SELECT 1 FROM heatmap_events mid 
            WHERE mid.session_id = prev.session_id 
            AND mid.timestamp > prev.timestamp 
            AND mid.timestamp < curr.timestamp
        )
        GROUP BY prev.page_url, curr.page_url
        ORDER BY transition_count DESC
        LIMIT 50
    ";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get session recordings metadata
 */
function getSessionRecordings($pdo, $timeframe) {
    $whereClause = getTimeframeClause($timeframe);
    
    $sql = "
        SELECT 
            session_id,
            user_id,
            MIN(timestamp) as session_start,
            MAX(timestamp) as session_end,
            COUNT(*) as event_count,
            COUNT(DISTINCT page_url) as page_count
        FROM heatmap_events 
        WHERE {$whereClause}
        GROUP BY session_id, user_id
        HAVING event_count >= 10
        ORDER BY session_start DESC
        LIMIT 100
    ";
    
    $stmt = $pdo->query($sql);
    $recordings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add session duration
    foreach ($recordings as &$recording) {
        $recording['duration'] = $recording['session_end'] - $recording['session_start'];
        $recording['duration_formatted'] = formatDuration($recording['duration']);
    }
    
    return $recordings;
}

/**
 * Create heatmap tracking tables
 */
function createHeatmapTables($pdo) {
    // Main heatmap events table
    $sql = "CREATE TABLE IF NOT EXISTS heatmap_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(100) NOT NULL,
        user_id VARCHAR(100) NOT NULL,
        event_type VARCHAR(50) NOT NULL,
        event_data JSON,
        page_url TEXT,
        timestamp BIGINT NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        viewport_width INT,
        viewport_height INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_session (session_id),
        INDEX idx_user (user_id),
        INDEX idx_event_type (event_type),
        INDEX idx_page_url (page_url(100)),
        INDEX idx_timestamp (timestamp),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    // Click events table
    $sql = "CREATE TABLE IF NOT EXISTS click_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        x_coordinate INT NOT NULL,
        y_coordinate INT NOT NULL,
        page_x INT NOT NULL,
        page_y INT NOT NULL,
        target_selector VARCHAR(255),
        target_text VARCHAR(255),
        tag_name VARCHAR(50),
        class_name VARCHAR(255),
        element_id VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES heatmap_events(id) ON DELETE CASCADE,
        INDEX idx_coordinates (x_coordinate, y_coordinate),
        INDEX idx_selector (target_selector),
        INDEX idx_tag (tag_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    // Scroll events table
    $sql = "CREATE TABLE IF NOT EXISTS scroll_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        scroll_depth_percent INT NOT NULL,
        max_scroll_reached INT DEFAULT 0,
        time_to_depth INT DEFAULT 0,
        page_height INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES heatmap_events(id) ON DELETE CASCADE,
        INDEX idx_depth (scroll_depth_percent),
        INDEX idx_time (time_to_depth)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    // Custom events table
    $sql = "CREATE TABLE IF NOT EXISTS custom_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        event_name VARCHAR(100) NOT NULL,
        event_properties JSON,
        category VARCHAR(50),
        label VARCHAR(100),
        value DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES heatmap_events(id) ON DELETE CASCADE,
        INDEX idx_name (event_name),
        INDEX idx_category (category),
        INDEX idx_label (label)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    // Session summaries table for faster analytics
    $sql = "CREATE TABLE IF NOT EXISTS session_summaries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(100) NOT NULL UNIQUE,
        user_id VARCHAR(100) NOT NULL,
        start_time BIGINT NOT NULL,
        end_time BIGINT NOT NULL,
        duration INT NOT NULL,
        page_count INT DEFAULT 0,
        click_count INT DEFAULT 0,
        scroll_events INT DEFAULT 0,
        custom_events INT DEFAULT 0,
        user_agent TEXT,
        referrer TEXT,
        device_type VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_session (session_id),
        INDEX idx_user (user_id),
        INDEX idx_duration (duration),
        INDEX idx_device (device_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
}

/**
 * Get timeframe WHERE clause
 */
function getTimeframeClause($timeframe) {
    switch ($timeframe) {
        case '1d':
            return "created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
        case '7d':
            return "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        case '30d':
            return "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        case '90d':
            return "created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
        default:
            return "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    }
}

/**
 * Get user's real IP address
 */
function getUserIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Sanitize string input
 */
function sanitize_string($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Format duration in milliseconds to human readable
 */
function formatDuration($milliseconds) {
    $seconds = floor($milliseconds / 1000);
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes % 60, $seconds % 60);
    } elseif ($minutes > 0) {
        return sprintf('%d:%02d', $minutes, $seconds % 60);
    } else {
        return sprintf('0:%02d', $seconds);
    }
}
?>