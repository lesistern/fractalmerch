<?php
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

$pageTitle = '📊 Estadísticas - Panel Admin';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-chart-bar"></i> Estadísticas</h1>
    <p>Panel completo de métricas y análisis de tu tienda</p>
    
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportStats()">
            <i class="fas fa-download"></i> Exportar
        </button>
        <div class="control-group">
            <label>Período:</label>
            <select id="time-period" onchange="updateStatsPeriod()">
                <option value="today">Hoy</option>
                <option value="week" selected>Esta semana</option>
                <option value="month">Este mes</option>
                <option value="quarter">Trimestre</option>
                <option value="year">Año</option>
            </select>
        </div>
        <div class="comparison-toggle">
            <label class="switch">
                <input type="checkbox" id="compareToggle">
                <span class="slider"></span>
            </label>
            <span>Comparar períodos</span>
        </div>
    </div>
</div>

<!-- Métricas Principales -->
<div class="content-card">
    <h3><i class="fas fa-chart-line"></i> Métricas Principales</h3>
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
</div>

<!-- Embudo de Conversión -->
<div class="content-card">
    <h3><i class="fas fa-filter"></i> Embudo de Conversión</h3>
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
</div>

<!-- Análisis Avanzado -->
<div class="analytics-grid">
    <!-- Métodos de Pago -->
    <div class="content-card">
        <h3><i class="fas fa-credit-card"></i> Métodos de Pago</h3>
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
    <div class="content-card">
        <h3><i class="fas fa-globe"></i> Fuentes de Tráfico</h3>
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
<div class="content-card">
    <h3><i class="fas fa-map-marked-alt"></i> Top 5 Provincias por Facturación</h3>
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

<!-- Gráficos -->
<div class="charts-grid">
    <div class="content-card">
        <h3><i class="fas fa-chart-area"></i> Ventas por día</h3>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
    
    <div class="content-card">
        <h3><i class="fas fa-chart-pie"></i> Productos más vendidos</h3>
        <div class="chart-container">
            <canvas id="productsChart"></canvas>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para estadísticas */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    flex-shrink: 0;
}

.stat-icon.visits { background: #007bff; }
.stat-icon.sales { background: #28a745; }
.stat-icon.billing { background: #ffc107; }
.stat-icon.ticket { background: #17a2b8; }
.stat-icon.conversion { background: #6f42c1; }
.stat-icon.customers { background: #fd7e14; }

.stat-content h3 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    color: #2c3e50;
}

.stat-content p {
    margin: 4px 0;
    color: #6c757d;
    font-size: 14px;
}

.stat-trend {
    font-size: 12px;
    font-weight: 600;
}

.stat-trend.positive { color: #28a745; }
.stat-trend.negative { color: #dc3545; }

.comparison-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
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

/* Funnel Chart */
.funnel-chart {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.funnel-step {
    position: relative;
}

.funnel-bar {
    background: linear-gradient(90deg, #007bff, #0056b3);
    color: white;
    padding: 12px 20px;
    border-radius: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    min-height: 20px;
}

.funnel-label {
    font-weight: 500;
}

.funnel-value {
    font-weight: 700;
}

/* Analytics Grid */
.analytics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

.payment-methods, .provinces-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.payment-item, .province-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.payment-label, .province-name {
    min-width: 120px;
    font-size: 14px;
    color: #495057;
}

.payment-bar, .province-bar {
    flex: 1;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.payment-fill, .province-fill {
    height: 100%;
    background: #007bff;
    transition: width 0.3s ease;
}

.payment-percent, .province-value {
    font-weight: 600;
    font-size: 14px;
    color: #2c3e50;
    min-width: 60px;
    text-align: right;
}

.traffic-sources {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.traffic-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 6px;
    background: #f8f9fa;
}

.traffic-item i {
    width: 20px;
    color: #007bff;
    font-size: 16px;
}

.traffic-label {
    flex: 1;
    font-size: 14px;
    color: #495057;
}

.traffic-value {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

/* Charts */
.charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

.chart-container {
    height: 300px;
    position: relative;
}

.chart-container canvas {
    width: 100% !important;
    height: 100% !important;
}

/* Responsive */
@media (max-width: 1200px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .analytics-grid {
        grid-template-columns: 1fr;
    }
    
    .page-actions {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
// Variables globales
let compareMode = false;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupEventListeners();
});

function setupEventListeners() {
    // Toggle de comparación
    document.getElementById('compareToggle').addEventListener('change', function() {
        compareMode = this.checked;
        toggleComparisonMode(compareMode);
    });
}

function updateStatsPeriod() {
    const period = document.getElementById('time-period').value;
    AdminUtils.showNotification('Período actualizado: ' + period, 'info');
    // Aquí se actualizarían las estadísticas según el período
}

function toggleComparisonMode(enabled) {
    if (enabled) {
        document.body.classList.add('comparison-mode');
        AdminUtils.showNotification('Modo comparación activado', 'info');
    } else {
        document.body.classList.remove('comparison-mode');
        AdminUtils.showNotification('Modo comparación desactivado', 'info');
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
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
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
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
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
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
}

function exportStats() {
    AdminUtils.showNotification('Exportando estadísticas...', 'info');
    // Aquí iría la lógica de exportación
    setTimeout(() => {
        AdminUtils.showNotification('Estadísticas exportadas correctamente', 'success');
    }, 2000);
}
</script>

<?php include 'admin-master-footer.php'; ?>