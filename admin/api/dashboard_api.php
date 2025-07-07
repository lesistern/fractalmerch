<?php
/**
 * ENTERPRISE DASHBOARD API ENDPOINTS
 * Professional REST API with rate limiting, authentication, and comprehensive error handling
 * 
 * Endpoints:
 * - GET /stats - Get dashboard statistics
 * - GET /stats/refresh - Force refresh statistics
 * - GET /sales-data - Get sales chart data
 * - GET /real-time - Get real-time metrics
 * - GET /performance - Get performance metrics
 * - POST /cache/clear - Clear cache
 * - GET /health - System health check
 * 
 * @author Claude Assistant
 * @version 1.0.0 Enterprise
 * @since 2025-01-07
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Start session and include dependencies
session_start();
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../backend/DashboardBackend.php';

// CORS headers for development (remove in production)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

/**
 * API Response Class
 */
class APIResponse {
    public static function success($data = null, $message = 'Success', $code = 200) {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c'),
            'request_id' => $_SERVER['REQUEST_ID'] ?? uniqid('req_')
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        exit;
    }
    
    public static function error($message = 'Error', $code = 400, $details = null) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'details' => $details,
            'timestamp' => date('c'),
            'request_id' => $_SERVER['REQUEST_ID'] ?? uniqid('req_')
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        exit;
    }
}

/**
 * Authentication middleware
 */
function requireAuthentication() {
    if (!is_logged_in() || !is_admin()) {
        APIResponse::error('Authentication required', 401);
    }
}

/**
 * Rate limiting middleware
 */
function checkRateLimit($endpoint, $max_requests = 100, $window = 3600) {
    $identifier = ($_SESSION['user_id'] ?? 'anonymous') . '_' . $_SERVER['REMOTE_ADDR'] . '_' . $endpoint;
    
    $cache_file = '../cache/rate_limit_' . md5($identifier) . '.json';
    
    $now = time();
    $requests = [];
    
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data && isset($data['requests'])) {
            $requests = $data['requests'];
        }
    }
    
    // Clean old requests
    $requests = array_filter($requests, function($timestamp) use ($now, $window) {
        return ($now - $timestamp) < $window;
    });
    
    // Check limit
    if (count($requests) >= $max_requests) {
        APIResponse::error('Rate limit exceeded', 429, [
            'limit' => $max_requests,
            'window' => $window,
            'retry_after' => $window - ($now - min($requests))
        ]);
    }
    
    // Add current request
    $requests[] = $now;
    
    // Save to cache
    if (!is_dir(dirname($cache_file))) {
        mkdir(dirname($cache_file), 0755, true);
    }
    file_put_contents($cache_file, json_encode(['requests' => $requests]), LOCK_EX);
}

/**
 * Validate request method
 */
function requireMethod($method) {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        APIResponse::error('Method not allowed', 405);
    }
}

/**
 * Get request data
 */
function getRequestData() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            APIResponse::error('Invalid JSON data', 400);
        }
        
        return $data;
    }
    
    return $_GET;
}

/**
 * Main API router
 */
try {
    // Set request ID for tracking
    $_SERVER['REQUEST_ID'] = uniqid('api_', true);
    
    // Get endpoint from URL
    $endpoint = $_GET['endpoint'] ?? '';
    
    // Initialize dashboard backend
    $config = [
        'cache_enabled' => true,
        'debug_mode' => false,
        'rate_limit_enabled' => true,
        'security_enabled' => true,
        'realtime_enabled' => true,
        'performance_monitoring' => true
    ];
    
    $backend = new DashboardBackend($pdo, $config);
    
    // Route endpoints
    switch ($endpoint) {
        case 'stats':
            requireAuthentication();
            requireMethod('GET');
            checkRateLimit('stats', 50, 60); // 50 requests per minute
            
            $force_refresh = isset($_GET['refresh']) && $_GET['refresh'] === 'true';
            $stats = $backend->getDashboardStats($force_refresh);
            
            APIResponse::success($stats, 'Statistics retrieved successfully');
            break;
            
        case 'stats/refresh':
            requireAuthentication();
            requireMethod('GET');
            checkRateLimit('refresh', 10, 300); // 10 requests per 5 minutes
            
            // Clear relevant cache
            $backend->clearCache('dashboard_stats');
            $stats = $backend->getDashboardStats(true);
            
            APIResponse::success($stats, 'Statistics refreshed successfully');
            break;
            
        case 'sales-data':
            requireAuthentication();
            requireMethod('GET');
            checkRateLimit('sales_data', 30, 60);
            
            $period = $_GET['period'] ?? 'monthly';
            $months = (int)($_GET['months'] ?? 12);
            
            // Validate parameters
            if (!in_array($period, ['daily', 'weekly', 'monthly', 'yearly'])) {
                APIResponse::error('Invalid period parameter', 400);
            }
            
            if ($months < 1 || $months > 24) {
                APIResponse::error('Invalid months parameter (1-24)', 400);
            }
            
            $salesData = $backend->getSalesData($period, $months);
            
            APIResponse::success($salesData, 'Sales data retrieved successfully');
            break;
            
        case 'real-time':
            requireAuthentication();
            requireMethod('GET');
            checkRateLimit('realtime', 120, 60); // Higher limit for real-time
            
            // Get only real-time data to reduce load
            $realTimeData = [
                'active_users' => $backend->getRealTimeActiveUsers(),
                'current_orders' => $backend->getCurrentOrders(),
                'system_health' => $backend->getSystemHealth(),
                'server_metrics' => $backend->getServerMetrics(),
                'timestamp' => time()
            ];
            
            APIResponse::success($realTimeData, 'Real-time data retrieved successfully');
            break;
            
        case 'performance':
            requireAuthentication();
            requireMethod('GET');
            checkRateLimit('performance', 20, 60);
            
            $performanceData = [
                'cache_stats' => $backend->getCacheStats(),
                'performance_stats' => $backend->getPerformanceStats(),
                'system_health' => $backend->getSystemHealth(),
                'database_stats' => $backend->getDatabaseStats()
            ];
            
            APIResponse::success($performanceData, 'Performance data retrieved successfully');
            break;
            
        case 'cache/clear':
            requireAuthentication();
            requireMethod('POST');
            checkRateLimit('cache_clear', 5, 300); // Very restrictive
            
            $data = getRequestData();
            $pattern = $data['pattern'] ?? null;
            
            $cleared = $backend->clearCache($pattern);
            
            APIResponse::success(['cleared_files' => $cleared], 'Cache cleared successfully');
            break;
            
        case 'health':
            // Health check doesn't require auth but has rate limiting
            requireMethod('GET');
            checkRateLimit('health', 30, 60);
            
            $health = $backend->getSystemHealth();
            
            // Return appropriate HTTP status based on health
            $status_code = 200;
            if ($health['status'] === 'critical') {
                $status_code = 503; // Service Unavailable
            } elseif ($health['status'] === 'warning') {
                $status_code = 200; // OK but with warnings
            }
            
            http_response_code($status_code);
            echo json_encode([
                'success' => $health['status'] !== 'critical',
                'health' => $health,
                'timestamp' => date('c'),
                'request_id' => $_SERVER['REQUEST_ID']
            ]);
            exit;
            break;
            
        case 'metrics/export':
            requireAuthentication();
            requireMethod('GET');
            checkRateLimit('export', 5, 3600); // 5 per hour
            
            $format = $_GET['format'] ?? 'json';
            $date_from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
            $date_to = $_GET['to'] ?? date('Y-m-d');
            
            $exportData = $backend->exportMetrics($format, $date_from, $date_to);
            
            if ($format === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="dashboard_metrics_' . date('Y-m-d') . '.csv"');
                echo $exportData;
                exit;
            }
            
            APIResponse::success($exportData, 'Metrics exported successfully');
            break;
            
        case 'notifications':
            requireAuthentication();
            requireMethod('GET');
            checkRateLimit('notifications', 60, 60);
            
            $notifications = $backend->getNotifications($_SESSION['user_id']);
            
            APIResponse::success($notifications, 'Notifications retrieved successfully');
            break;
            
        case 'notifications/mark-read':
            requireAuthentication();
            requireMethod('POST');
            checkRateLimit('mark_read', 30, 60);
            
            $data = getRequestData();
            $notification_ids = $data['ids'] ?? [];
            
            if (!is_array($notification_ids)) {
                APIResponse::error('Invalid notification IDs', 400);
            }
            
            $marked = $backend->markNotificationsRead($notification_ids, $_SESSION['user_id']);
            
            APIResponse::success(['marked' => $marked], 'Notifications marked as read');
            break;
            
        case 'search':
            requireAuthentication();
            requireMethod('GET');
            checkRateLimit('search', 30, 60);
            
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? 'all';
            
            if (strlen($query) < 2) {
                APIResponse::error('Search query too short', 400);
            }
            
            $results = $backend->search($query, $type);
            
            APIResponse::success($results, 'Search completed successfully');
            break;
            
        default:
            APIResponse::error('Endpoint not found', 404);
    }
    
} catch (Exception $e) {
    // Log the error
    error_log('Dashboard API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    // Return generic error in production
    if (isset($config) && $config['debug_mode']) {
        APIResponse::error('Internal server error: ' . $e->getMessage(), 500, [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        APIResponse::error('Internal server error', 500);
    }
}

/**
 * Additional methods for DashboardBackend class
 */

// Add these methods to the DashboardBackend class (extend functionality)
if (!method_exists('DashboardBackend', 'getRealTimeActiveUsers')) {
    // This would be added to the main class file
}

// Helper functions for the API
function sanitizeSearchQuery($query) {
    return trim(htmlspecialchars($query, ENT_QUOTES, 'UTF-8'));
}

function validateDateRange($from, $to) {
    $from_timestamp = strtotime($from);
    $to_timestamp = strtotime($to);
    
    if (!$from_timestamp || !$to_timestamp) {
        return false;
    }
    
    if ($from_timestamp > $to_timestamp) {
        return false;
    }
    
    // Max 1 year range
    if (($to_timestamp - $from_timestamp) > (365 * 24 * 3600)) {
        return false;
    }
    
    return true;
}

function logAPIAccess($endpoint, $user_id, $ip, $duration) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'endpoint' => $endpoint,
        'user_id' => $user_id,
        'ip' => $ip,
        'duration' => $duration,
        'memory_peak' => memory_get_peak_usage(true),
        'request_id' => $_SERVER['REQUEST_ID'] ?? null
    ];
    
    $log_line = json_encode($log_entry) . "\n";
    $log_file = '../logs/api_access_' . date('Y-m-d') . '.log';
    
    file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
}

?>