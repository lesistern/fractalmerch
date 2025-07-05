<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Connect to database
try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get A/B test results
$testResults = getABTestResults($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados A/B Testing - FractalMerch Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .ab-test-dashboard {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .test-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }
        
        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .test-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .test-status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-paused {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .variant-results {
            margin: 1rem 0;
        }
        
        .variant-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            margin: 0.5rem 0;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 4px solid transparent;
        }
        
        .variant-winner {
            border-left-color: #10b981;
            background: #ecfdf5;
        }
        
        .variant-name {
            font-weight: 500;
            color: #374151;
        }
        
        .variant-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .conversion-rate {
            font-weight: 600;
            color: #059669;
        }
        
        .chart-container {
            position: relative;
            height: 200px;
            margin: 1rem 0;
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }
        
        .btn-secondary:hover {
            background: #d1d5db;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .confidence-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .confidence-bar {
            flex: 1;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981);
            transition: width 0.3s ease;
        }
        
        .confidence-text {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-sidebar.php'; ?>
    
    <div class="main-content">
        <div class="ab-test-dashboard">
            <div class="dashboard-header">
                <h1><i class="fas fa-flask"></i> A/B Testing Dashboard</h1>
                <div class="header-actions">
                    <button class="btn-action btn-primary" onclick="exportResults()">
                        <i class="fas fa-download"></i> Exportar Resultados
                    </button>
                    <button class="btn-action btn-secondary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
            
            <!-- Summary Statistics -->
            <div class="summary-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $testResults['total_tests']; ?></div>
                    <div class="stat-label">Tests Activos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($testResults['total_users']); ?></div>
                    <div class="stat-label">Usuarios en Tests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($testResults['total_conversions']); ?></div>
                    <div class="stat-label">Conversiones</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($testResults['avg_conversion_rate'], 2); ?>%</div>
                    <div class="stat-label">Tasa Conversión Promedio</div>
                </div>
            </div>
            
            <!-- Individual Test Results -->
            <div class="test-grid">
                <?php foreach ($testResults['tests'] as $testName => $test): ?>
                <div class="test-card">
                    <div class="test-header">
                        <h3 class="test-title"><?php echo ucfirst(str_replace('_', ' ', $testName)); ?></h3>
                        <span class="test-status status-active">Activo</span>
                    </div>
                    
                    <div class="variant-results">
                        <?php 
                        $maxConversions = max(array_column($test['variants'], 'conversions'));
                        foreach ($test['variants'] as $variant): 
                            $isWinner = $variant['conversions'] == $maxConversions && $maxConversions > 0;
                        ?>
                        <div class="variant-row <?php echo $isWinner ? 'variant-winner' : ''; ?>">
                            <div class="variant-name">
                                <?php echo $variant['variant']; ?>
                                <?php if ($isWinner): ?>
                                    <i class="fas fa-crown" style="color: #10b981; margin-left: 0.5rem;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="variant-stats">
                                <span>Exposiciones: <?php echo number_format($variant['exposures']); ?></span>
                                <span>Conversiones: <?php echo number_format($variant['conversions']); ?></span>
                                <span class="conversion-rate">
                                    <?php echo number_format($variant['conversion_rate'], 2); ?>%
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Chart Canvas -->
                    <div class="chart-container">
                        <canvas id="chart-<?php echo $testName; ?>"></canvas>
                    </div>
                    
                    <!-- Confidence Indicator -->
                    <div class="confidence-indicator">
                        <span class="confidence-text">Confianza:</span>
                        <div class="confidence-bar">
                            <div class="confidence-fill" style="width: <?php echo $test['confidence']; ?>%"></div>
                        </div>
                        <span class="confidence-text"><?php echo number_format($test['confidence'], 1); ?>%</span>
                    </div>
                    
                    <div class="actions">
                        <button class="btn-action btn-primary" onclick="viewDetails('<?php echo $testName; ?>')">
                            Ver Detalles
                        </button>
                        <button class="btn-action btn-secondary" onclick="pauseTest('<?php echo $testName; ?>')">
                            Pausar Test
                        </button>
                        <button class="btn-action btn-danger" onclick="endTest('<?php echo $testName; ?>')">
                            Finalizar
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize charts for each test
        const testData = <?php echo json_encode($testResults['tests']); ?>;
        
        Object.keys(testData).forEach(testName => {
            const test = testData[testName];
            const ctx = document.getElementById(`chart-${testName}`);
            
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: test.variants.map(v => v.variant),
                        datasets: [{
                            label: 'Tasa de Conversión (%)',
                            data: test.variants.map(v => v.conversion_rate),
                            backgroundColor: [
                                '#3b82f6',
                                '#10b981', 
                                '#f59e0b',
                                '#ef4444',
                                '#8b5cf6'
                            ],
                            borderColor: [
                                '#2563eb',
                                '#059669',
                                '#d97706',
                                '#dc2626',
                                '#7c3aed'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
        
        // Action functions
        function viewDetails(testName) {
            window.location.href = `ab-test-details.php?test=${testName}`;
        }
        
        function pauseTest(testName) {
            if (confirm(`¿Pausar el test "${testName}"?`)) {
                // Implement pause functionality
                console.log('Pausing test:', testName);
            }
        }
        
        function endTest(testName) {
            if (confirm(`¿Finalizar permanentemente el test "${testName}"?`)) {
                // Implement end functionality
                console.log('Ending test:', testName);
            }
        }
        
        function exportResults() {
            window.location.href = 'export-ab-results.php';
        }
        
        function refreshData() {
            window.location.reload();
        }
        
        // Auto-refresh every 5 minutes
        setInterval(refreshData, 300000);
    </script>
</body>
</html>

<?php
/**
 * Get A/B test results from database
 */
function getABTestResults($pdo) {
    $results = [
        'total_tests' => 0,
        'total_users' => 0,
        'total_conversions' => 0,
        'avg_conversion_rate' => 0,
        'tests' => []
    ];
    
    try {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'ab_test_events'");
        if ($stmt->rowCount() == 0) {
            return $results;
        }
        
        // Get summary stats
        $stmt = $pdo->query("
            SELECT 
                COUNT(DISTINCT test_name) as total_tests,
                COUNT(DISTINCT user_id) as total_users,
                SUM(CASE WHEN event_type = 'ab_test_conversion' THEN 1 ELSE 0 END) as total_conversions
            FROM ab_test_events 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $results['total_tests'] = $summary['total_tests'];
        $results['total_users'] = $summary['total_users'];
        $results['total_conversions'] = $summary['total_conversions'];
        
        // Get detailed test results
        $stmt = $pdo->query("
            SELECT 
                test_name,
                variant,
                SUM(CASE WHEN event_type = 'ab_test_exposure' THEN 1 ELSE 0 END) as exposures,
                SUM(CASE WHEN event_type = 'ab_test_conversion' THEN 1 ELSE 0 END) as conversions,
                CASE 
                    WHEN SUM(CASE WHEN event_type = 'ab_test_exposure' THEN 1 ELSE 0 END) > 0 
                    THEN (SUM(CASE WHEN event_type = 'ab_test_conversion' THEN 1 ELSE 0 END) * 100.0) / SUM(CASE WHEN event_type = 'ab_test_exposure' THEN 1 ELSE 0 END)
                    ELSE 0 
                END as conversion_rate
            FROM ab_test_events 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY test_name, variant
            ORDER BY test_name, variant
        ");
        
        $testResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by test name
        $totalConversionRate = 0;
        $testCount = 0;
        
        foreach ($testResults as $row) {
            $testName = $row['test_name'];
            
            if (!isset($results['tests'][$testName])) {
                $results['tests'][$testName] = [
                    'variants' => [],
                    'confidence' => 0
                ];
                $testCount++;
            }
            
            $results['tests'][$testName]['variants'][] = [
                'variant' => $row['variant'],
                'exposures' => $row['exposures'],
                'conversions' => $row['conversions'],
                'conversion_rate' => $row['conversion_rate']
            ];
            
            $totalConversionRate += $row['conversion_rate'];
        }
        
        // Calculate confidence levels and average conversion rate
        foreach ($results['tests'] as $testName => &$test) {
            $test['confidence'] = calculateConfidence($test['variants']);
        }
        
        if ($testCount > 0) {
            $results['avg_conversion_rate'] = $totalConversionRate / count($testResults);
        }
        
    } catch (Exception $e) {
        error_log("Error getting A/B test results: " . $e->getMessage());
    }
    
    return $results;
}

/**
 * Calculate statistical confidence for test variants
 */
function calculateConfidence($variants) {
    if (count($variants) < 2) return 0;
    
    // Simple confidence calculation based on sample size and conversion difference
    $totalExposures = array_sum(array_column($variants, 'exposures'));
    $conversionRates = array_column($variants, 'conversion_rate');
    
    $maxRate = max($conversionRates);
    $minRate = min($conversionRates);
    $difference = $maxRate - $minRate;
    
    // Basic confidence calculation (simplified)
    $confidence = min(95, ($totalExposures / 100) + ($difference * 2));
    
    return max(0, $confidence);
}
?>