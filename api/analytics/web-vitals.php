<?php
/**
 * Web Vitals Analytics Endpoint
 * Recibe y almacena métricas de Core Web Vitals
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
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

require_once '../../config/database.php';

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $required_fields = ['metric', 'value', 'url', 'timestamp'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Sanitize and validate data
    $metric = htmlspecialchars($data['metric'], ENT_QUOTES, 'UTF-8');
    $value = floatval($data['value']);
    $url = filter_var($data['url'], FILTER_SANITIZE_URL);
    $timestamp = intval($data['timestamp']);
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8') : '';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Connection info (optional)
    $connection_type = isset($data['connection']['effectiveType']) ? $data['connection']['effectiveType'] : null;
    $connection_downlink = isset($data['connection']['downlink']) ? floatval($data['connection']['downlink']) : null;
    
    // Validate metric values
    $valid_metrics = ['LCP', 'FID', 'CLS', 'FCP', 'TTFB'];
    if (!in_array($metric, $valid_metrics)) {
        throw new Exception('Invalid metric name');
    }
    
    // Validate metric ranges
    switch ($metric) {
        case 'LCP':
        case 'FCP':
        case 'TTFB':
            if ($value < 0 || $value > 60000) { // 0-60 seconds
                throw new Exception('Invalid timing value');
            }
            break;
        case 'FID':
            if ($value < 0 || $value > 10000) { // 0-10 seconds
                throw new Exception('Invalid FID value');
            }
            break;
        case 'CLS':
            if ($value < 0 || $value > 10) { // 0-10 (very high CLS)
                throw new Exception('Invalid CLS value');
            }
            break;
    }
    
    // Create table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS web_vitals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            metric VARCHAR(10) NOT NULL,
            value DECIMAL(10, 2) NOT NULL,
            url VARCHAR(500) NOT NULL,
            timestamp BIGINT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            user_agent TEXT,
            ip_address VARCHAR(45),
            connection_type VARCHAR(20),
            connection_downlink DECIMAL(5, 2),
            INDEX idx_metric (metric),
            INDEX idx_url (url(100)),
            INDEX idx_timestamp (timestamp),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($createTableSQL);
    
    // Insert the metric
    $stmt = $pdo->prepare("
        INSERT INTO web_vitals 
        (metric, value, url, timestamp, user_agent, ip_address, connection_type, connection_downlink) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $metric,
        $value,
        $url,
        $timestamp,
        $user_agent,
        $ip_address,
        $connection_type,
        $connection_downlink
    ]);
    
    if (!$result) {
        throw new Exception('Failed to save metric');
    }
    
    // Calculate performance score
    $score = calculatePerformanceScore($metric, $value);
    
    // Get recent metrics summary for this URL
    $recentMetrics = getRecentMetrics($pdo, $url);
    
    // Response with success and additional info
    echo json_encode([
        'success' => true,
        'metric' => $metric,
        'value' => $value,
        'score' => $score,
        'grade' => getPerformanceGrade($score),
        'recent_metrics' => $recentMetrics,
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Calculate performance score based on Google's thresholds
 */
function calculatePerformanceScore($metric, $value) {
    $thresholds = [
        'LCP' => ['good' => 2500, 'poor' => 4000],
        'FID' => ['good' => 100, 'poor' => 300],
        'CLS' => ['good' => 0.1, 'poor' => 0.25],
        'FCP' => ['good' => 1800, 'poor' => 3000],
        'TTFB' => ['good' => 800, 'poor' => 1800]
    ];
    
    if (!isset($thresholds[$metric])) {
        return 0;
    }
    
    $good = $thresholds[$metric]['good'];
    $poor = $thresholds[$metric]['poor'];
    
    if ($value <= $good) {
        return 100;
    } elseif ($value >= $poor) {
        return 0;
    } else {
        // Linear interpolation between good and poor
        return round(100 - (($value - $good) / ($poor - $good)) * 100);
    }
}

/**
 * Get performance grade based on score
 */
function getPerformanceGrade($score) {
    if ($score >= 90) return 'A';
    if ($score >= 75) return 'B';
    if ($score >= 50) return 'C';
    return 'D';
}

/**
 * Get recent metrics summary for URL
 */
function getRecentMetrics($pdo, $url) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                metric,
                AVG(value) as avg_value,
                COUNT(*) as count,
                MIN(value) as min_value,
                MAX(value) as max_value
            FROM web_vitals 
            WHERE url = ? 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY metric
        ");
        
        $stmt->execute([$url]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
?>