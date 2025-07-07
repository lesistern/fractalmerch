<?php
/**
 * ADMIN PANEL PERFORMANCE TESTING SCRIPT
 * Use this to test performance improvements
 */

require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    die('Access denied');
}

// Performance measurement functions
function measureExecutionTime($callback, $label = '') {
    $start = microtime(true);
    $result = $callback();
    $end = microtime(true);
    $execution_time = ($end - $start) * 1000; // Convert to milliseconds
    
    return [
        'result' => $result,
        'execution_time' => $execution_time,
        'label' => $label
    ];
}

$tests = [];

// Test 1: Dashboard Statistics (Old vs New)
$tests['dashboard_stats_old'] = measureExecutionTime(function() {
    global $pdo;
    
    // Simulate old method with multiple queries
    $stats = [];
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $stats['total_posts'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'");
    $stats['published_posts'] = $stmt->fetchColumn();
    
    return $stats;
}, 'Dashboard Stats (Old Method)');

$tests['dashboard_stats_new'] = measureExecutionTime(function() {
    return get_dashboard_stats_cached(300);
}, 'Dashboard Stats (New Cached Method)');

// Test 2: Product Pagination
$tests['products_all'] = measureExecutionTime(function() {
    return get_products(); // Old method without pagination
}, 'All Products (No Pagination)');

$tests['products_paginated'] = measureExecutionTime(function() {
    return get_products_paginated(20, 0, ''); // New paginated method
}, 'Products Paginated (20 items)');

// Test 3: Search Performance
$tests['search_like'] = measureExecutionTime(function() {
    return get_products_paginated(20, 0, 'remera'); // Search with LIKE
}, 'Product Search (LIKE query)');

// Test 4: Memory Usage
$memory_start = memory_get_usage();
$memory_peak = memory_get_peak_usage();

$pageTitle = '⚡ Performance Test - Admin Panel';
include 'admin-master-header.php';
?>

<style>
.performance-test {
    background: white;
    padding: 30px;
    border-radius: 8px;
    margin: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.test-results {
    display: grid;
    gap: 20px;
    margin-top: 30px;
}

.test-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.test-card.fast {
    border-left-color: #28a745;
}

.test-card.medium {
    border-left-color: #ffc107;
}

.test-card.slow {
    border-left-color: #dc3545;
}

.test-title {
    font-weight: 600;
    margin-bottom: 10px;
    color: #2c3e50;
}

.test-time {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}

.test-time.fast { color: #28a745; }
.test-time.medium { color: #ffc107; }
.test-time.slow { color: #dc3545; }

.improvement {
    background: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 4px;
    margin-top: 10px;
    font-weight: 600;
}

.memory-stats {
    background: #e3f2fd;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.comparison-chart {
    display: flex;
    gap: 20px;
    align-items: flex-end;
    margin-top: 20px;
    height: 200px;
}

.chart-bar {
    background: linear-gradient(to top, #007bff, #0056b3);
    color: white;
    padding: 10px;
    border-radius: 4px 4px 0 0;
    min-width: 80px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    font-weight: 600;
    font-size: 12px;
}

.chart-bar.improved {
    background: linear-gradient(to top, #28a745, #1e7e34);
}
</style>

<div class="performance-test">
    <h1><i class="fas fa-tachometer-alt"></i> Performance Test Results</h1>
    <p>Testing admin panel optimizations and improvements</p>
    
    <div class="test-results">
        <?php foreach ($tests as $key => $test): ?>
            <?php 
            $time_class = 'fast';
            if ($test['execution_time'] > 100) $time_class = 'medium';
            if ($test['execution_time'] > 500) $time_class = 'slow';
            ?>
            <div class="test-card <?php echo $time_class; ?>">
                <div class="test-title"><?php echo $test['label']; ?></div>
                <div class="test-time <?php echo $time_class; ?>">
                    <?php echo number_format($test['execution_time'], 2); ?>ms
                </div>
                <small>
                    <?php if ($time_class === 'fast'): ?>
                        ✅ Excellent performance
                    <?php elseif ($time_class === 'medium'): ?>
                        ⚠️ Good performance
                    <?php else: ?>
                        ❌ Needs optimization
                    <?php endif; ?>
                </small>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Performance Comparison -->
    <div style="margin-top: 40px;">
        <h3>Performance Improvements</h3>
        <div class="comparison-chart">
            <div class="chart-bar" style="height: <?php echo min(100, $tests['dashboard_stats_old']['execution_time']); ?>px;">
                Old Method<br>
                <?php echo number_format($tests['dashboard_stats_old']['execution_time'], 1); ?>ms
            </div>
            <div class="chart-bar improved" style="height: <?php echo min(100, $tests['dashboard_stats_new']['execution_time']); ?>px;">
                New Cached<br>
                <?php echo number_format($tests['dashboard_stats_new']['execution_time'], 1); ?>ms
            </div>
        </div>
        
        <?php 
        $improvement = (($tests['dashboard_stats_old']['execution_time'] - $tests['dashboard_stats_new']['execution_time']) 
                       / $tests['dashboard_stats_old']['execution_time']) * 100;
        ?>
        
        <?php if ($improvement > 0): ?>
        <div class="improvement">
            🚀 Performance improved by <?php echo number_format($improvement, 1); ?>% with caching
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Memory Usage -->
    <div class="memory-stats">
        <h3><i class="fas fa-memory"></i> Memory Usage</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <strong>Current Usage:</strong><br>
                <?php echo number_format(memory_get_usage() / 1024 / 1024, 2); ?> MB
            </div>
            <div>
                <strong>Peak Usage:</strong><br>
                <?php echo number_format($memory_peak / 1024 / 1024, 2); ?> MB
            </div>
            <div>
                <strong>Memory Limit:</strong><br>
                <?php echo ini_get('memory_limit'); ?>
            </div>
        </div>
    </div>
    
    <!-- Security Features -->
    <div style="margin-top: 30px; background: #fff3cd; padding: 20px; border-radius: 8px;">
        <h3><i class="fas fa-shield-alt"></i> Security Enhancements</h3>
        <ul style="margin: 0; padding-left: 20px;">
            <li>✅ CSRF Protection implemented on all forms</li>
            <li>✅ Advanced file upload validation (MIME type checking)</li>
            <li>✅ Rate limiting for admin actions</li>
            <li>✅ Enhanced input sanitization</li>
            <li>✅ SQL injection prevention with prepared statements</li>
        </ul>
    </div>
    
    <!-- UX Improvements -->
    <div style="margin-top: 20px; background: #d1ecf1; padding: 20px; border-radius: 8px;">
        <h3><i class="fas fa-user-friends"></i> UX Enhancements</h3>
        <ul style="margin: 0; padding-left: 20px;">
            <li>⌨️ Keyboard shortcuts (Alt+D for Dashboard, Alt+S for Stats, etc.)</li>
            <li>🔍 Debounced search with live filtering</li>
            <li>📄 Pagination for better performance</li>
            <li>🎯 Quick access toolbar</li>
            <li>⚡ Lazy loading of Chart.js on analytics pages only</li>
            <li>📱 Responsive design improvements</li>
        </ul>
    </div>
    
    <!-- Recommendations -->
    <div style="margin-top: 20px; background: #f8d7da; padding: 20px; border-radius: 8px;">
        <h3><i class="fas fa-lightbulb"></i> Additional Recommendations</h3>
        <ul style="margin: 0; padding-left: 20px;">
            <li>🗄️ Implement Redis/Memcached for distributed caching</li>
            <li>📊 Add database query monitoring and optimization</li>
            <li>🔄 Implement lazy loading for large product lists</li>
            <li>📈 Add performance monitoring dashboard</li>
            <li>🛡️ Consider implementing two-factor authentication</li>
            <li>📝 Add audit logging for admin actions</li>
        </ul>
    </div>
</div>

<script>
// Auto refresh every 30 seconds
setTimeout(() => {
    window.location.reload();
}, 30000);

// Show timing in console
console.log('Performance Test Results:', <?php echo json_encode($tests); ?>);
</script>

<?php include 'admin-master-footer.php'; ?>