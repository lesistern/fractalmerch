<?php
/**
 * Performance Analytics Endpoint
 * Recibe y almacena métricas generales de performance
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/database.php';

// Handle GET requests for performance data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleGetRequest($pdo);
    exit();
}

// Handle POST requests for storing metrics
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
    if (!isset($data['metrics']) || !isset($data['url']) || !isset($data['timestamp'])) {
        throw new Exception('Missing required fields: metrics, url, timestamp');
    }
    
    // Sanitize data
    $url = filter_var($data['url'], FILTER_SANITIZE_URL);
    $timestamp = intval($data['timestamp']);
    $user_agent = isset($data['userAgent']) ? htmlspecialchars($data['userAgent'], ENT_QUOTES, 'UTF-8') : '';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Connection info
    $connection_type = isset($data['connection']['effectiveType']) ? $data['connection']['effectiveType'] : null;
    $connection_downlink = isset($data['connection']['downlink']) ? floatval($data['connection']['downlink']) : null;
    
    // Create table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS performance_metrics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            url VARCHAR(500) NOT NULL,
            timestamp BIGINT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            user_agent TEXT,
            ip_address VARCHAR(45),
            connection_type VARCHAR(20),
            connection_downlink DECIMAL(5, 2),
            dom_content_loaded DECIMAL(10, 2),
            load_complete DECIMAL(10, 2),
            ttfb DECIMAL(10, 2),
            dom_interactive DECIMAL(10, 2),
            first_paint DECIMAL(10, 2),
            first_contentful_paint DECIMAL(10, 2),
            largest_contentful_paint DECIMAL(10, 2),
            first_input_delay DECIMAL(10, 2),
            cumulative_layout_shift DECIMAL(10, 4),
            time_to_interactive DECIMAL(10, 2),
            INDEX idx_url (url(100)),
            INDEX idx_timestamp (timestamp),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($createTableSQL);
    
    // Extract metrics
    $metrics = $data['metrics'];
    
    // Insert the performance metrics
    $stmt = $pdo->prepare("
        INSERT INTO performance_metrics 
        (url, timestamp, user_agent, ip_address, connection_type, connection_downlink,
         dom_content_loaded, load_complete, ttfb, dom_interactive, first_paint,
         first_contentful_paint, largest_contentful_paint, first_input_delay,
         cumulative_layout_shift, time_to_interactive) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $url,
        $timestamp,
        $user_agent,
        $ip_address,
        $connection_type,
        $connection_downlink,
        isset($metrics['domContentLoaded']) ? floatval($metrics['domContentLoaded']) : null,
        isset($metrics['loadComplete']) ? floatval($metrics['loadComplete']) : null,
        isset($metrics['ttfb']) ? floatval($metrics['ttfb']) : null,
        isset($metrics['domInteractive']) ? floatval($metrics['domInteractive']) : null,
        isset($metrics['firstPaint']) ? floatval($metrics['firstPaint']) : null,
        isset($metrics['firstContentfulPaint']) ? floatval($metrics['firstContentfulPaint']) : null,
        isset($metrics['largestContentfulPaint']) ? floatval($metrics['largestContentfulPaint']) : null,
        isset($metrics['firstInputDelay']) ? floatval($metrics['firstInputDelay']) : null,
        isset($metrics['cumulativeLayoutShift']) ? floatval($metrics['cumulativeLayoutShift']) : null,
        isset($metrics['timeToInteractive']) ? floatval($metrics['timeToInteractive']) : null
    ]);
    
    if (!$result) {
        throw new Exception('Failed to save performance metrics');
    }
    
    // Calculate overall performance score
    $performanceScore = calculateOverallPerformanceScore($metrics);
    
    // Get performance insights
    $insights = generatePerformanceInsights($metrics);
    
    echo json_encode([
        'success' => true,
        'performance_score' => $performanceScore,
        'insights' => $insights,
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
 * Handle GET requests for performance data
 */
function handleGetRequest($pdo) {
    try {
        $url = isset($_GET['url']) ? filter_var($_GET['url'], FILTER_SANITIZE_URL) : null;
        $days = isset($_GET['days']) ? max(1, min(30, intval($_GET['days']))) : 7;
        
        $whereClause = '';
        $params = [];
        
        if ($url) {
            $whereClause = 'WHERE url = ?';
            $params[] = $url;
        }
        
        // Get performance summary
        $summarySQL = "
            SELECT 
                COUNT(*) as total_sessions,
                AVG(dom_content_loaded) as avg_dom_content_loaded,
                AVG(load_complete) as avg_load_complete,
                AVG(ttfb) as avg_ttfb,
                AVG(first_contentful_paint) as avg_fcp,
                AVG(largest_contentful_paint) as avg_lcp,
                AVG(first_input_delay) as avg_fid,
                AVG(cumulative_layout_shift) as avg_cls,
                MIN(created_at) as earliest_session,
                MAX(created_at) as latest_session
            FROM performance_metrics 
            $whereClause
            AND created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
        ";
        
        $stmt = $pdo->prepare($summarySQL);
        $stmt->execute($params);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get hourly breakdown
        $hourlySQL = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour,
                COUNT(*) as sessions,
                AVG(load_complete) as avg_load_time,
                AVG(largest_contentful_paint) as avg_lcp,
                AVG(first_input_delay) as avg_fid,
                AVG(cumulative_layout_shift) as avg_cls
            FROM performance_metrics 
            $whereClause
            AND created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')
            ORDER BY hour DESC
            LIMIT 24
        ";
        
        $stmt = $pdo->prepare($hourlySQL);
        $stmt->execute($params);
        $hourlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get device/connection breakdown
        $deviceSQL = "
            SELECT 
                connection_type,
                COUNT(*) as sessions,
                AVG(load_complete) as avg_load_time,
                AVG(largest_contentful_paint) as avg_lcp
            FROM performance_metrics 
            $whereClause
            AND created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
            AND connection_type IS NOT NULL
            GROUP BY connection_type
            ORDER BY sessions DESC
        ";
        
        $stmt = $pdo->prepare($deviceSQL);
        $stmt->execute($params);
        $deviceData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate performance grades
        $grades = [
            'lcp' => getPerformanceGrade(calculatePerformanceScore('LCP', $summary['avg_lcp'] ?? 0)),
            'fid' => getPerformanceGrade(calculatePerformanceScore('FID', $summary['avg_fid'] ?? 0)),
            'cls' => getPerformanceGrade(calculatePerformanceScore('CLS', $summary['avg_cls'] ?? 0)),
            'fcp' => getPerformanceGrade(calculatePerformanceScore('FCP', $summary['avg_fcp'] ?? 0)),
            'ttfb' => getPerformanceGrade(calculatePerformanceScore('TTFB', $summary['avg_ttfb'] ?? 0))
        ];
        
        echo json_encode([
            'success' => true,
            'summary' => $summary,
            'hourly_data' => $hourlyData,
            'device_data' => $deviceData,
            'grades' => $grades,
            'period_days' => $days
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Calculate overall performance score
 */
function calculateOverallPerformanceScore($metrics) {
    $scores = [];
    
    if (isset($metrics['largestContentfulPaint'])) {
        $scores[] = calculatePerformanceScore('LCP', $metrics['largestContentfulPaint']);
    }
    
    if (isset($metrics['firstInputDelay'])) {
        $scores[] = calculatePerformanceScore('FID', $metrics['firstInputDelay']);
    }
    
    if (isset($metrics['cumulativeLayoutShift'])) {
        $scores[] = calculatePerformanceScore('CLS', $metrics['cumulativeLayoutShift']);
    }
    
    if (isset($metrics['firstContentfulPaint'])) {
        $scores[] = calculatePerformanceScore('FCP', $metrics['firstContentfulPaint']);
    }
    
    if (isset($metrics['ttfb'])) {
        $scores[] = calculatePerformanceScore('TTFB', $metrics['ttfb']);
    }
    
    return empty($scores) ? 0 : round(array_sum($scores) / count($scores));
}

/**
 * Generate performance insights
 */
function generatePerformanceInsights($metrics) {
    $insights = [];
    
    // LCP insights
    if (isset($metrics['largestContentfulPaint'])) {
        $lcp = $metrics['largestContentfulPaint'];
        if ($lcp > 4000) {
            $insights[] = [
                'type' => 'warning',
                'metric' => 'LCP',
                'message' => 'Largest Contentful Paint is slow. Consider optimizing images and reducing server response times.',
                'recommendation' => 'Optimize images, use CDN, implement lazy loading'
            ];
        } elseif ($lcp <= 2500) {
            $insights[] = [
                'type' => 'success',
                'metric' => 'LCP',
                'message' => 'Excellent Largest Contentful Paint performance!',
                'recommendation' => 'Keep monitoring to maintain good performance'
            ];
        }
    }
    
    // FID insights
    if (isset($metrics['firstInputDelay'])) {
        $fid = $metrics['firstInputDelay'];
        if ($fid > 300) {
            $insights[] = [
                'type' => 'warning',
                'metric' => 'FID',
                'message' => 'First Input Delay is high. Consider reducing JavaScript execution time.',
                'recommendation' => 'Code splitting, defer non-critical JS, optimize event handlers'
            ];
        }
    }
    
    // CLS insights
    if (isset($metrics['cumulativeLayoutShift'])) {
        $cls = $metrics['cumulativeLayoutShift'];
        if ($cls > 0.25) {
            $insights[] = [
                'type' => 'warning',
                'metric' => 'CLS',
                'message' => 'High Cumulative Layout Shift detected. Set dimensions for images and ads.',
                'recommendation' => 'Set width/height for images, reserve space for dynamic content'
            ];
        }
    }
    
    // TTFB insights
    if (isset($metrics['ttfb'])) {
        $ttfb = $metrics['ttfb'];
        if ($ttfb > 1800) {
            $insights[] = [
                'type' => 'warning',
                'metric' => 'TTFB',
                'message' => 'Time to First Byte is slow. Optimize server response time.',
                'recommendation' => 'Optimize database queries, use caching, upgrade hosting'
            ];
        }
    }
    
    return $insights;
}

/**
 * Calculate performance score (same as web-vitals.php)
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
        return round(100 - (($value - $good) / ($poor - $good)) * 100);
    }
}

/**
 * Get performance grade
 */
function getPerformanceGrade($score) {
    if ($score >= 90) return 'A';
    if ($score >= 75) return 'B';
    if ($score >= 50) return 'C';
    return 'D';
}
?>