<?php
/**
 * A/B Testing Analytics API Endpoint
 * Receives and stores A/B test data for analysis
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
    
    // Validate required fields
    if (!isset($data['type']) || !isset($data['data'])) {
        throw new Exception('Missing required fields: type, data');
    }
    
    $type = sanitize_string($data['type']);
    $testData = $data['data'];
    
    // Validate test data
    $requiredFields = ['userId', 'sessionId', 'testName', 'variant', 'timestamp'];
    foreach ($requiredFields as $field) {
        if (!isset($testData[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Connect to database
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create table if not exists
    createABTestTable($pdo);
    
    // Insert A/B test data
    $sql = "INSERT INTO ab_test_events (
        user_id, session_id, test_name, variant, event_type, action, value,
        url, user_agent, ip_address, timestamp, created_at
    ) VALUES (
        :user_id, :session_id, :test_name, :variant, :event_type, :action, :value,
        :url, :user_agent, :ip_address, :timestamp, NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => sanitize_string($testData['userId']),
        'session_id' => sanitize_string($testData['sessionId']),
        'test_name' => sanitize_string($testData['testName']),
        'variant' => sanitize_string($testData['variant']),
        'event_type' => $type,
        'action' => sanitize_string($testData['action'] ?? ''),
        'value' => floatval($testData['value'] ?? 0),
        'url' => sanitize_string($testData['url'] ?? ''),
        'user_agent' => sanitize_string($testData['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? ''),
        'ip_address' => getUserIP(),
        'timestamp' => intval($testData['timestamp'])
    ]);
    
    // Response
    echo json_encode([
        'success' => true,
        'message' => 'A/B test data recorded successfully',
        'event_id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
    
    // Log error
    error_log("A/B Test API Error: " . $e->getMessage());
}

/**
 * Create A/B test table if it doesn't exist
 */
function createABTestTable($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS ab_test_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(100) NOT NULL,
        session_id VARCHAR(100) NOT NULL,
        test_name VARCHAR(100) NOT NULL,
        variant VARCHAR(100) NOT NULL,
        event_type ENUM('ab_test_exposure', 'ab_test_conversion') NOT NULL,
        action VARCHAR(100) DEFAULT '',
        value DECIMAL(10,2) DEFAULT 0,
        url TEXT,
        user_agent TEXT,
        ip_address VARCHAR(45),
        timestamp BIGINT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_test_variant (test_name, variant),
        INDEX idx_user_session (user_id, session_id),
        INDEX idx_timestamp (timestamp),
        INDEX idx_event_type (event_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
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
            
            // Validate IP
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
?>