<?php
/**
 * Admin - Recommendations Analytics Dashboard
 * Panel de análisis del sistema de recomendaciones
 */

require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/RecommendationEngine.php';
require_once '../includes/ABTestingEngine.php';

// Verificar autenticación de admin
check_admin_auth();

// Inicializar engines
$engine = new RecommendationEngine($pdo);
$abTesting = new ABTestingEngine($pdo);

// Obtener estadísticas
$recommendationStats = $engine->getRecommendationStats();
$abTestStats = [];

// Obtener estadísticas de A/B tests
$abTests = ['recommendation_algorithm', 'recommendation_display', 'recommendation_titles', 'price_based_prominence'];
foreach ($abTests as $testName) {
    $abTestStats[$testName] = $abTesting->getTestStats($testName);
}

$page_title = 'Analytics de Recomendaciones';
include '../includes/admin_header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-chart-line"></i> Analytics de Recomendaciones</h1>
        <p>Panel de análisis y optimización del sistema de recomendaciones</p>
    </div>

    <!-- Métricas Principales -->
    <div class="metrics-overview">
        <div class="metrics-grid">
            <div class="metric-card primary">
                <div class="metric-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="metric-content">
                    <h3>Impresiones</h3>
                    <div class="metric-number" id="total-impressions">-</div>
                    <div class="metric-change positive">+12.5% vs semana anterior</div>
                </div>
            </div>
            
            <div class="metric-card success">
                <div class="metric-icon">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <div class="metric-content">
                    <h3>Click Rate</h3>
                    <div class="metric-number" id="click-rate">-</div>
                    <div class="metric-change positive">+3.2% vs semana anterior</div>
                </div>
            </div>
            
            <div class="metric-card warning">
                <div class="metric-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="metric-content">
                    <h3>Conversion Rate</h3>
                    <div class="metric-number" id="conversion-rate">-</div>
                    <div class="metric-change negative">-1.8% vs semana anterior</div>
                </div>
            </div>
            
            <div class="metric-card info">
                <div class="metric-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="metric-content">
                    <h3>Revenue Atribuido</h3>
                    <div class="metric-number" id="attributed-revenue">-</div>
                    <div class="metric-change positive">+8.7% vs semana anterior</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance por Tipo de Recomendación -->
    <div class="analytics-section">
        <div class="section-header">
            <h2><i class="fas fa-chart-bar"></i> Performance por Tipo</h2>
            <div class="section-controls">
                <select id="timeframe-selector" class="form-control">
                    <option value="7">Últimos 7 días</option>
                    <option value="30" selected>Últimos 30 días</option>
                    <option value="90">Últimos 90 días</option>
                </select>
            </div>
        </div>
        
        <div class="recommendation-types-grid">
            <?php
            $recommendationTypes = [
                'frequently_bought_together' => ['name' => 'Comprados Juntos', 'icon' => 'fa-shopping-basket'],
                'similar_products' => ['name' => 'Productos Similares', 'icon' => 'fa-search'],
                'personalized' => ['name' => 'Personalizadas', 'icon' => 'fa-user-star'],
                'trending' => ['name' => 'Trending', 'icon' => 'fa-fire'],
                'price_based' => ['name' => 'Por Precio', 'icon' => 'fa-tag'],
                'seasonal' => ['name' => 'Estacionales', 'icon' => 'fa-calendar-alt']
            ];
            
            foreach ($recommendationTypes as $type => $config): ?>
            <div class="recommendation-type-card" data-type="<?php echo $type; ?>">
                <div class="type-header">
                    <div class="type-icon">
                        <i class="fas <?php echo $config['icon']; ?>"></i>
                    </div>
                    <h3><?php echo $config['name']; ?></h3>
                </div>
                <div class="type-metrics">
                    <div class="metric-row">
                        <span>Click Rate:</span>
                        <span class="metric-value" data-metric="click-rate">-</span>
                    </div>
                    <div class="metric-row">
                        <span>Conversiones:</span>
                        <span class="metric-value" data-metric="conversions">-</span>
                    </div>
                    <div class="metric-row">
                        <span>Revenue:</span>
                        <span class="metric-value" data-metric="revenue">-</span>
                    </div>
                </div>
                <div class="type-trend">
                    <canvas class="trend-chart" data-chart="<?php echo $type; ?>"></canvas>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- A/B Testing Results -->
    <div class="analytics-section">
        <div class="section-header">
            <h2><i class="fas fa-flask"></i> A/B Testing Results</h2>
            <div class="section-controls">
                <button class="btn btn-primary" onclick="refreshABTests()">
                    <i class="fas fa-sync"></i> Actualizar
                </button>
            </div>
        </div>
        
        <div class="ab-tests-grid">
            <?php foreach ($abTests as $testName): 
                $testStats = $abTestStats[$testName];
                $significance = $abTesting->calculateSignificance($testName);
            ?>
            <div class="ab-test-card" data-test="<?php echo $testName; ?>">
                <div class="test-header">
                    <h3><?php echo str_replace('_', ' ', ucfirst($testName)); ?></h3>
                    <div class="significance-badge <?php echo $significance['significant'] ? 'significant' : 'not-significant'; ?>">
                        <?php echo $significance['significant'] ? 'Significativo' : 'No Significativo'; ?>
                        <?php if ($significance['confidence'] > 0): ?>
                        <small>(<?php echo $significance['confidence']; ?>% confianza)</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="test-variants">
                    <?php foreach ($testStats as $variant => $stats): ?>
                    <div class="variant-stats">
                        <div class="variant-header">
                            <span class="variant-label">Variant <?php echo $variant; ?></span>
                            <span class="variant-users"><?php echo $stats['unique_users']; ?> usuarios</span>
                        </div>
                        <div class="variant-metrics">
                            <div class="metric-item">
                                <span class="metric-label">Click Rate:</span>
                                <span class="metric-value"><?php echo $stats['click_rate']; ?>%</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Conversion:</span>
                                <span class="metric-value"><?php echo $stats['conversion_rate']; ?>%</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Revenue:</span>
                                <span class="metric-value">$<?php echo number_format($stats['avg_revenue'], 0); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($significance['significant']): ?>
                <div class="test-winner">
                    <i class="fas fa-trophy"></i>
                    <span>Lift: <?php echo $significance['lift']; ?>%</span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Gráficos de Tendencias -->
    <div class="analytics-section">
        <div class="section-header">
            <h2><i class="fas fa-chart-line"></i> Tendencias Temporales</h2>
        </div>
        
        <div class="charts-container">
            <div class="chart-card">
                <h3>Click Rate por Día</h3>
                <canvas id="clickRateChart"></canvas>
            </div>
            
            <div class="chart-card">
                <h3>Conversion Rate por Día</h3>
                <canvas id="conversionChart"></canvas>
            </div>
            
            <div class="chart-card">
                <h3>Revenue Atribuido</h3>
                <canvas id="revenueChart"></canvas>
            </div>
            
            <div class="chart-card">
                <h3>Distribución por Tipo</h3>
                <canvas id="distributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products from Recommendations -->
    <div class="analytics-section">
        <div class="section-header">
            <h2><i class="fas fa-star"></i> Top Productos via Recomendaciones</h2>
        </div>
        
        <div class="top-products-table">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Impresiones</th>
                        <th>Clicks</th>
                        <th>Click Rate</th>
                        <th>Conversiones</th>
                        <th>Revenue</th>
                        <th>ROI</th>
                    </tr>
                </thead>
                <tbody id="topProductsTable">
                    <!-- Se carga dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Optimization Suggestions -->
    <div class="analytics-section">
        <div class="section-header">
            <h2><i class="fas fa-lightbulb"></i> Sugerencias de Optimización</h2>
        </div>
        
        <div class="optimization-suggestions">
            <div class="suggestion-card low-performance">
                <div class="suggestion-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="suggestion-content">
                    <h4>Baja Performance en Recomendaciones Estacionales</h4>
                    <p>Las recomendaciones estacionales tienen un CTR 23% menor que el promedio. 
                       Considera actualizar los criterios de selección o mejorar la relevancia temporal.</p>
                    <div class="suggestion-actions">
                        <button class="btn btn-sm btn-primary">Revisar Algoritmo</button>
                        <button class="btn btn-sm btn-outline">Ver Detalles</button>
                    </div>
                </div>
            </div>
            
            <div class="suggestion-card opportunity">
                <div class="suggestion-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="suggestion-content">
                    <h4>Oportunidad: Expandir "Comprados Juntos"</h4>
                    <p>Este tipo de recomendación tiene el mejor ROI (340%). 
                       Incrementar su visibilidad podría generar +15% más revenue.</p>
                    <div class="suggestion-actions">
                        <button class="btn btn-sm btn-success">Implementar</button>
                        <button class="btn btn-sm btn-outline">Calcular Impacto</button>
                    </div>
                </div>
            </div>
            
            <div class="suggestion-card test-suggestion">
                <div class="suggestion-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="suggestion-content">
                    <h4>Test Sugerido: Posición de Recomendaciones</h4>
                    <p>Probar mover las recomendaciones más arriba en la página podría 
                       mejorar la visibilidad y engagement.</p>
                    <div class="suggestion-actions">
                        <button class="btn btn-sm btn-warning">Crear Test A/B</button>
                        <button class="btn btn-sm btn-outline">Más Info</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js for analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-header {
    margin-bottom: 2rem;
    text-align: center;
}

.admin-header h1 {
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.admin-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

/* Métricas Overview */
.metrics-overview {
    margin-bottom: 3rem;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.metric-card {
    background: var(--bg-secondary);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-2px);
}

.metric-card.primary .metric-icon { background: #007bff; }
.metric-card.success .metric-icon { background: #28a745; }
.metric-card.warning .metric-icon { background: #ffc107; }
.metric-card.info .metric-icon { background: #17a2b8; }

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.metric-content h3 {
    margin: 0 0 0.5rem 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
}

.metric-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.metric-change {
    font-size: 0.8rem;
    font-weight: 500;
}

.metric-change.positive { color: #28a745; }
.metric-change.negative { color: #dc3545; }

/* Analytics Sections */
.analytics-section {
    background: var(--bg-secondary);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.section-header h2 {
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-controls {
    display: flex;
    gap: 1rem;
    align-items: center;
}

/* Recommendation Types Grid */
.recommendation-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.recommendation-type-card {
    background: var(--bg-tertiary);
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid var(--border-color);
}

.type-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.type-icon {
    width: 40px;
    height: 40px;
    background: var(--ecommerce-primary);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.type-header h3 {
    margin: 0;
    color: var(--text-primary);
}

.type-metrics {
    margin-bottom: 1rem;
}

.metric-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.metric-row span:first-child {
    color: var(--text-secondary);
}

.metric-value {
    font-weight: 600;
    color: var(--text-primary);
}

.type-trend {
    height: 60px;
}

.trend-chart {
    width: 100%;
    height: 100%;
}

/* A/B Testing */
.ab-tests-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}

.ab-test-card {
    background: var(--bg-tertiary);
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid var(--border-color);
}

.test-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.test-header h3 {
    margin: 0;
    color: var(--text-primary);
    text-transform: capitalize;
}

.significance-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
}

.significance-badge.significant {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.significance-badge.not-significant {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.test-variants {
    display: grid;
    gap: 1rem;
}

.variant-stats {
    background: var(--bg-primary);
    border-radius: 6px;
    padding: 1rem;
}

.variant-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.variant-label {
    font-weight: 600;
    color: var(--text-primary);
}

.variant-users {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.variant-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}

.metric-item {
    text-align: center;
}

.metric-label {
    display: block;
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
}

.metric-item .metric-value {
    font-weight: 700;
    color: var(--text-primary);
}

.test-winner {
    margin-top: 1rem;
    padding: 0.75rem;
    background: linear-gradient(135deg, #ffc107, #ffb300);
    border-radius: 6px;
    text-align: center;
    color: white;
    font-weight: 600;
}

/* Charts */
.charts-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

.chart-card {
    background: var(--bg-tertiary);
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid var(--border-color);
}

.chart-card h3 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
    font-size: 1.1rem;
}

.chart-card canvas {
    max-height: 300px;
}

/* Top Products Table */
.top-products-table {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    background: var(--bg-tertiary);
    border-radius: 8px;
    overflow: hidden;
}

.admin-table th,
.admin-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.admin-table th {
    background: var(--bg-primary);
    color: var(--text-primary);
    font-weight: 600;
}

.admin-table tbody tr:hover {
    background: var(--bg-primary);
}

/* Optimization Suggestions */
.optimization-suggestions {
    display: grid;
    gap: 1.5rem;
}

.suggestion-card {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid;
}

.suggestion-card.low-performance {
    background: #fff3cd;
    border-left-color: #ffc107;
}

.suggestion-card.opportunity {
    background: #d4edda;
    border-left-color: #28a745;
}

.suggestion-card.test-suggestion {
    background: #d1ecf1;
    border-left-color: #17a2b8;
}

.suggestion-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.suggestion-card.low-performance .suggestion-icon {
    background: #ffc107;
    color: white;
}

.suggestion-card.opportunity .suggestion-icon {
    background: #28a745;
    color: white;
}

.suggestion-card.test-suggestion .suggestion-icon {
    background: #17a2b8;
    color: white;
}

.suggestion-content h4 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}

.suggestion-content p {
    margin: 0 0 1rem 0;
    color: var(--text-secondary);
    line-height: 1.5;
}

.suggestion-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-container {
        padding: 1rem;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .metric-card {
        flex-direction: column;
        text-align: center;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .recommendation-types-grid,
    .ab-tests-grid,
    .charts-container {
        grid-template-columns: 1fr;
    }
    
    .suggestion-card {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAnalyticsData();
    initializeCharts();
    setupEventListeners();
});

async function loadAnalyticsData() {
    try {
        // Cargar métricas principales
        const response = await fetch('../api/recommendations/analytics.php');
        const data = await response.json();
        
        if (data.success) {
            updateMainMetrics(data.metrics);
            updateRecommendationTypes(data.by_type);
            updateTopProducts(data.top_products);
        }
    } catch (error) {
        console.error('Error loading analytics data:', error);
    }
}

function updateMainMetrics(metrics) {
    document.getElementById('total-impressions').textContent = 
        metrics.total_impressions?.toLocaleString() || '0';
    document.getElementById('click-rate').textContent = 
        (metrics.click_rate || 0).toFixed(1) + '%';
    document.getElementById('conversion-rate').textContent = 
        (metrics.conversion_rate || 0).toFixed(1) + '%';
    document.getElementById('attributed-revenue').textContent = 
        '$' + (metrics.attributed_revenue || 0).toLocaleString();
}

function updateRecommendationTypes(typeData) {
    Object.keys(typeData || {}).forEach(type => {
        const card = document.querySelector(`[data-type="${type}"]`);
        if (card && typeData[type]) {
            const data = typeData[type];
            card.querySelector('[data-metric="click-rate"]').textContent = 
                (data.click_rate || 0).toFixed(1) + '%';
            card.querySelector('[data-metric="conversions"]').textContent = 
                data.conversions || 0;
            card.querySelector('[data-metric="revenue"]').textContent = 
                '$' + (data.revenue || 0).toLocaleString();
        }
    });
}

function updateTopProducts(products) {
    const tbody = document.getElementById('topProductsTable');
    if (!products || products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No hay datos disponibles</td></tr>';
        return;
    }
    
    tbody.innerHTML = products.map(product => `
        <tr>
            <td>${product.name}</td>
            <td>${product.impressions?.toLocaleString() || 0}</td>
            <td>${product.clicks || 0}</td>
            <td>${(product.click_rate || 0).toFixed(1)}%</td>
            <td>${product.conversions || 0}</td>
            <td>$${(product.revenue || 0).toLocaleString()}</td>
            <td>${(product.roi || 0).toFixed(0)}%</td>
        </tr>
    `).join('');
}

function initializeCharts() {
    // Configuración común para todos los charts
    Chart.defaults.color = getComputedStyle(document.documentElement)
        .getPropertyValue('--text-primary').trim();
    Chart.defaults.backgroundColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--ecommerce-primary').trim();
    
    // Click Rate Chart
    const clickRateCtx = document.getElementById('clickRateChart').getContext('2d');
    new Chart(clickRateCtx, {
        type: 'line',
        data: {
            labels: [], // Se llenarán dinámicamente
            datasets: [{
                label: 'Click Rate %',
                data: [],
                borderColor: '#007bff',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10
                }
            }
        }
    });
    
    // Conversion Chart
    const conversionCtx = document.getElementById('conversionChart').getContext('2d');
    new Chart(conversionCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Conversion Rate %',
                data: [],
                borderColor: '#28a745',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5
                }
            }
        }
    });
    
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Revenue ($)',
                data: [],
                backgroundColor: '#ffc107'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Distribution Chart
    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Comprados Juntos', 'Similares', 'Personalizadas', 'Trending', 'Por Precio', 'Estacionales'],
            datasets: [{
                data: [25, 20, 15, 20, 10, 10],
                backgroundColor: [
                    '#007bff',
                    '#28a745', 
                    '#ffc107',
                    '#dc3545',
                    '#17a2b8',
                    '#6f42c1'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function setupEventListeners() {
    // Timeframe selector
    document.getElementById('timeframe-selector').addEventListener('change', function() {
        loadAnalyticsData();
    });
}

function refreshABTests() {
    // Recargar estadísticas de A/B tests
    window.location.reload();
}
</script>

<?php include '../includes/admin_footer.php'; ?>