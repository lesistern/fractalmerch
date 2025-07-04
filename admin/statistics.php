<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

// Simulación de datos de estadísticas (en un sistema real esto vendría de la base de datos)
$stats_data = [
    'visits' => [
        'total' => 3247,
        'variation' => -2,
        'previous' => 3312
    ],
    'sales' => [
        'total' => 152,
        'variation' => 12,
        'previous' => 136
    ],
    'billing' => [
        'total' => 247890,
        'variation' => 8.5,
        'previous' => 228426
    ],
    'average_ticket' => [
        'total' => 1631,
        'variation' => -3.2,
        'previous' => 1685
    ],
    'conversion' => [
        'total' => 4.68,
        'variation' => 0.8,
        'previous' => 4.64
    ],
    'new_customers' => [
        'total' => 89,
        'variation' => 15,
        'previous' => 77
    ]
];

$page_title = '📊 Estadísticas - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <!-- Main Content -->
    <div class="modern-admin-main">
        <!-- Header Tiendanube Style -->
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-chart-bar"></i> Estadísticas</h1>
                <p class="header-subtitle">Panel completo de métricas y análisis</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="exportStats()">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Filtros de Tiempo -->
        <div class="time-filters">
            <div class="filter-group">
                <label>Período:</label>
                <div class="time-buttons">
                    <button class="time-btn" data-period="today">Hoy</button>
                    <button class="time-btn active" data-period="week">Esta semana</button>
                    <button class="time-btn" data-period="month">Este mes</button>
                    <button class="time-btn" data-period="quarter">Trimestre</button>
                    <button class="time-btn" data-period="year">Año</button>
                </div>
            </div>
            <div class="comparison-toggle">
                <label class="switch">
                    <input type="checkbox" id="compareToggle">
                    <span class="slider"></span>
                </label>
                <span>Comparar períodos</span>
            </div>
        </div>

        <!-- Stats Content -->
        <div class="stats-dashboard">
            <!-- Métricas Básicas (Disponibles en todos los planes) -->
            <section class="metrics-section">
                <h2>Métricas Principales</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon visits">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats_data['visits']['total']); ?></h3>
                            <p>Visitas únicas</p>
                            <span class="stat-trend <?php echo $stats_data['visits']['variation'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo ($stats_data['visits']['variation'] >= 0 ? '+' : '') . $stats_data['visits']['variation']; ?>% vs período anterior
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon sales">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats_data['sales']['total']); ?></h3>
                            <p>Ventas</p>
                            <span class="stat-trend <?php echo $stats_data['sales']['variation'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo ($stats_data['sales']['variation'] >= 0 ? '+' : '') . $stats_data['sales']['variation']; ?>% vs período anterior
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon billing">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3>$<?php echo number_format($stats_data['billing']['total']); ?></h3>
                            <p>Facturación</p>
                            <span class="stat-trend <?php echo $stats_data['billing']['variation'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo ($stats_data['billing']['variation'] >= 0 ? '+' : '') . $stats_data['billing']['variation']; ?>% vs período anterior
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon ticket">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="stat-content">
                            <h3>$<?php echo number_format($stats_data['average_ticket']['total']); ?></h3>
                            <p>Ticket promedio</p>
                            <span class="stat-trend <?php echo $stats_data['average_ticket']['variation'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo ($stats_data['average_ticket']['variation'] >= 0 ? '+' : '') . $stats_data['average_ticket']['variation']; ?>% vs período anterior
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon conversion">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats_data['conversion']['total']; ?>%</h3>
                            <p>Conversión</p>
                            <span class="stat-trend <?php echo $stats_data['conversion']['variation'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo ($stats_data['conversion']['variation'] >= 0 ? '+' : '') . $stats_data['conversion']['variation']; ?>% vs período anterior
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon customers">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats_data['new_customers']['total']); ?></h3>
                            <p>Nuevos clientes</p>
                            <span class="stat-trend <?php echo $stats_data['new_customers']['variation'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo ($stats_data['new_customers']['variation'] >= 0 ? '+' : '') . $stats_data['new_customers']['variation']; ?>% vs período anterior
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Embudo de Conversión -->
            <section class="funnel-section">
                <h2>Embudo de Conversión</h2>
                <div class="funnel-chart">
                    <div class="funnel-step">
                        <div class="funnel-bar" style="width: 100%;">
                            <span class="funnel-label">Visitas</span>
                            <span class="funnel-value">3,247</span>
                        </div>
                    </div>
                    <div class="funnel-step">
                        <div class="funnel-bar" style="width: 35%;">
                            <span class="funnel-label">Productos vistos</span>
                            <span class="funnel-value">1,136</span>
                        </div>
                    </div>
                    <div class="funnel-step">
                        <div class="funnel-bar" style="width: 18%;">
                            <span class="funnel-label">Agregados al carrito</span>
                            <span class="funnel-value">584</span>
                        </div>
                    </div>
                    <div class="funnel-step">
                        <div class="funnel-bar" style="width: 8%;">
                            <span class="funnel-label">Iniciaron checkout</span>
                            <span class="funnel-value">260</span>
                        </div>
                    </div>
                    <div class="funnel-step">
                        <div class="funnel-bar" style="width: 4.68%;">
                            <span class="funnel-label">Compraron</span>
                            <span class="funnel-value">152</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Métricas Avanzadas -->
            <section class="advanced-metrics">
                <h2>Análisis Avanzado</h2>
                
                <!-- Métodos de Pago -->
                <div class="metrics-row">
                    <div class="metric-card">
                        <h3>Métodos de Pago</h3>
                        <div class="payment-methods">
                            <div class="payment-item">
                                <span class="payment-label">Tarjeta de Crédito</span>
                                <div class="payment-bar">
                                    <div class="payment-fill" style="width: 65%;"></div>
                                </div>
                                <span class="payment-percent">65%</span>
                            </div>
                            <div class="payment-item">
                                <span class="payment-label">Transferencia</span>
                                <div class="payment-bar">
                                    <div class="payment-fill" style="width: 25%;"></div>
                                </div>
                                <span class="payment-percent">25%</span>
                            </div>
                            <div class="payment-item">
                                <span class="payment-label">MercadoPago</span>
                                <div class="payment-bar">
                                    <div class="payment-fill" style="width: 10%;"></div>
                                </div>
                                <span class="payment-percent">10%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fuentes de Tráfico -->
                    <div class="metric-card">
                        <h3>Fuentes de Tráfico</h3>
                        <div class="traffic-sources">
                            <div class="traffic-item">
                                <i class="fab fa-google"></i>
                                <span class="traffic-label">Búsqueda</span>
                                <span class="traffic-value">45%</span>
                            </div>
                            <div class="traffic-item">
                                <i class="fas fa-link"></i>
                                <span class="traffic-label">Directo</span>
                                <span class="traffic-value">28%</span>
                            </div>
                            <div class="traffic-item">
                                <i class="fab fa-facebook"></i>
                                <span class="traffic-label">Social</span>
                                <span class="traffic-value">15%</span>
                            </div>
                            <div class="traffic-item">
                                <i class="fas fa-envelope"></i>
                                <span class="traffic-label">Email</span>
                                <span class="traffic-value">12%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Provincias -->
                <div class="metric-card provinces-card">
                    <h3>Top 5 Provincias por Facturación</h3>
                    <div class="provinces-list">
                        <div class="province-item">
                            <span class="province-name">Buenos Aires</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 85%;"></div>
                            </div>
                            <span class="province-value">$210,456</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Córdoba</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 42%;"></div>
                            </div>
                            <span class="province-value">$104,230</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Santa Fe</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 35%;"></div>
                            </div>
                            <span class="province-value">$87,650</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Mendoza</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 28%;"></div>
                            </div>
                            <span class="province-value">$69,420</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Misiones</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 22%;"></div>
                            </div>
                            <span class="province-value">$54,890</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Gráficos -->
            <section class="charts-section">
                <div class="charts-row">
                    <div class="chart-card">
                        <h3>Ventas por día</h3>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <h3>Productos más vendidos</h3>
                        <div class="chart-container">
                            <canvas id="productsChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Toast Notifications Container -->
<div id="toastContainer" class="toast-container"></div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/js/modern-admin.js?v=<?php echo time(); ?>"></script>
<script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>

<script>
// Variables globales
let compareMode = false;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    initializeStatistics();
    initializeCharts();
    setupEventListeners();
});

function initializeStatistics() {
    console.log('Statistics dashboard loaded');
}

function setupEventListeners() {

    // Botones de período
    document.querySelectorAll('.time-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateStatsPeriod(this.dataset.period);
        });
    });

    // Toggle de comparación
    document.getElementById('compareToggle').addEventListener('change', function() {
        compareMode = this.checked;
        toggleComparisonMode(compareMode);
    });
}


function updateStatsPeriod(period) {
    showToast('Período actualizado: ' + period, 'info');
    // Aquí se actualizarían las estadísticas según el período
    // En un sistema real, se haría una llamada AJAX
}

function toggleComparisonMode(enabled) {
    if (enabled) {
        document.body.classList.add('comparison-mode');
        showToast('Modo comparación activado', 'info');
    } else {
        document.body.classList.remove('comparison-mode');
        showToast('Modo comparación desactivado', 'info');
    }
}

function initializeCharts() {
    // Gráfico de ventas
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Ventas',
                data: [12, 19, 3, 5, 2, 3, 9],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfico de productos
    const productsCtx = document.getElementById('productsChart').getContext('2d');
    new Chart(productsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Remeras', 'Buzos', 'Tazas', 'Accesorios'],
            datasets: [{
                data: [45, 25, 20, 10],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function exportStats() {
    showToast('Exportando estadísticas...', 'info');
    // Aquí iría la lógica de exportación
    setTimeout(() => {
        showToast('Estadísticas exportadas correctamente', 'success');
    }, 2000);
}

// Funciones de utilidad
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation' : 'info'}-circle"></i>
        ${message}
    `;
    
    document.getElementById('toastContainer').appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<style>
/* Estilos específicos para estadísticas */
.stats-dashboard {
    padding: 1.5rem;
}

.time-filters {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 1.5rem 2rem;
    margin-bottom: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-group label {
    font-weight: 600;
    margin-right: 1rem;
    color: #333;
}

.time-buttons {
    display: flex;
    gap: 0.5rem;
}

.time-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.time-btn:hover,
.time-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.comparison-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #007bff;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.metrics-section h2 {
    margin-bottom: 1rem;
    color: #333;
    font-size: 1.3rem;
    font-weight: 600;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
}

.stat-icon.visits { background: #007bff; }
.stat-icon.sales { background: #28a745; }
.stat-icon.billing { background: #ffc107; }
.stat-icon.ticket { background: #17a2b8; }
.stat-icon.conversion { background: #6f42c1; }
.stat-icon.customers { background: #fd7e14; }

.stat-content h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: #333;
}

.stat-content p {
    margin: 0.25rem 0;
    color: #666;
    font-size: 0.85rem;
}

.stat-trend {
    font-size: 0.75rem;
    font-weight: 600;
}

.stat-trend.positive { color: #28a745; }
.stat-trend.negative { color: #dc3545; }
.stat-trend.neutral { color: #6c757d; }

/* Embudo de conversión */
.funnel-section {
    margin: 1.5rem 0;
}

.funnel-section h2 {
    margin-bottom: 1rem;
    color: #333;
    font-size: 1.3rem;
    font-weight: 600;
}

.funnel-chart {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.funnel-step {
    margin-bottom: 0.5rem;
}

.funnel-bar {
    background: linear-gradient(90deg, #007bff, #0056b3);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    font-size: 0.9rem;
}

.funnel-label {
    font-weight: 600;
}

.funnel-value {
    font-weight: 700;
}

/* Métricas avanzadas */
.advanced-metrics {
    margin: 1.5rem 0;
}

.advanced-metrics h2 {
    margin-bottom: 1rem;
    color: #333;
    font-size: 1.3rem;
    font-weight: 600;
}

.metrics-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.metric-card {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.metric-card h3 {
    margin-bottom: 1rem;
    color: #333;
    font-size: 1.1rem;
}

.payment-methods,
.provinces-list {
    space-y: 0.5rem;
}

.payment-item,
.province-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.payment-label,
.province-name {
    min-width: 100px;
    font-size: 0.85rem;
    color: #666;
}

.payment-bar,
.province-bar {
    flex: 1;
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.payment-fill,
.province-fill {
    height: 100%;
    background: #007bff;
    transition: width 0.3s ease;
}

.payment-percent,
.province-value {
    font-weight: 600;
    font-size: 0.85rem;
    color: #333;
    min-width: 50px;
}

.traffic-sources {
    space-y: 0.5rem;
}

.traffic-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    border-radius: 4px;
    background: #f8f9fa;
    margin-bottom: 0.5rem;
}

.traffic-item i {
    width: 20px;
    color: #007bff;
    font-size: 0.9rem;
}

.traffic-label {
    flex: 1;
    font-size: 0.85rem;
    color: #666;
}

.traffic-value {
    font-weight: 600;
    color: #333;
    font-size: 0.85rem;
}

.provinces-card {
    grid-column: 1 / -1;
    margin-top: 1rem;
}

.provinces-card h3 {
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.province-value {
    min-width: 80px;
    text-align: right;
}

/* Gráficos */
.charts-section {
    margin-top: 1.5rem;
}

.charts-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.chart-card {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chart-card h3 {
    margin-bottom: 0.75rem;
    color: #333;
    font-size: 1.1rem;
}

.chart-container {
    height: 250px;
    position: relative;
}

/* Toast notifications */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
}

.toast {
    background: white;
    border-left: 4px solid #007bff;
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-radius: 4px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 300px;
}

.toast.show {
    opacity: 1;
    transform: translateX(0);
}

.toast-success {
    border-left-color: #28a745;
    color: #28a745;
}

.toast-error {
    border-left-color: #dc3545;
    color: #dc3545;
}

.toast-info {
    border-left-color: #17a2b8;
    color: #17a2b8;
}

/* Responsive */
@media (max-width: 768px) {
    .time-filters {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .time-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .metrics-row,
    .charts-row {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>

</body>
</html>