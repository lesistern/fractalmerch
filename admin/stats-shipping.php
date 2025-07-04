<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🚚 Estadísticas de Envíos - Panel Admin';
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
                <h1><i class="fas fa-shipping-fast"></i> Estadísticas de Envíos</h1>
                <p class="header-subtitle">Análisis de métodos de envío y entregas</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="exportShippingStats()">
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
            <!-- Métricas Principales -->
            <section class="metrics-section">
                <h2>Métricas de Envíos</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon sales">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-content">
                            <h3>152</h3>
                            <p>Envíos totales</p>
                            <span class="stat-trend positive">+12% vs período anterior</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon conversion">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>2.8</h3>
                            <p>Días promedio entrega</p>
                            <span class="stat-trend positive">-0.5 días vs anterior</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon billing">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3>$18,450</h3>
                            <p>Costo total envíos</p>
                            <span class="stat-trend negative">+5.2% vs anterior</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon customers">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="stat-content">
                            <h3>89</h3>
                            <p>Envíos gratuitos</p>
                            <span class="stat-trend positive">+25% vs anterior</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon visits">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>98.5%</h3>
                            <p>Tasa de entrega</p>
                            <span class="stat-trend positive">+2.1% vs anterior</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon ticket">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3>4.7</h3>
                            <p>Satisfacción promedio</p>
                            <span class="stat-trend positive">+0.3 vs anterior</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Métodos de Envío -->
            <section class="advanced-metrics">
                <h2>Análisis de Envíos</h2>
                
                <div class="metrics-row">
                    <div class="metric-card">
                        <h3>Métodos de Envío</h3>
                        <div class="payment-methods">
                            <div class="payment-item">
                                <span class="payment-label">Envío Express</span>
                                <div class="payment-bar">
                                    <div class="payment-fill" style="width: 45%;"></div>
                                </div>
                                <span class="payment-percent">45%</span>
                            </div>
                            <div class="payment-item">
                                <span class="payment-label">Envío Estándar</span>
                                <div class="payment-bar">
                                    <div class="payment-fill" style="width: 35%;"></div>
                                </div>
                                <span class="payment-percent">35%</span>
                            </div>
                            <div class="payment-item">
                                <span class="payment-label">Retiro en Local</span>
                                <div class="payment-bar">
                                    <div class="payment-fill" style="width: 20%;"></div>
                                </div>
                                <span class="payment-percent">20%</span>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <h3>Estados de Envío</h3>
                        <div class="traffic-sources">
                            <div class="traffic-item">
                                <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                <span class="traffic-label">Entregado</span>
                                <span class="traffic-value">65%</span>
                            </div>
                            <div class="traffic-item">
                                <i class="fas fa-truck" style="color: #007bff;"></i>
                                <span class="traffic-label">En tránsito</span>
                                <span class="traffic-value">20%</span>
                            </div>
                            <div class="traffic-item">
                                <i class="fas fa-box" style="color: #ffc107;"></i>
                                <span class="traffic-label">Preparando</span>
                                <span class="traffic-value">10%</span>
                            </div>
                            <div class="traffic-item">
                                <i class="fas fa-clock" style="color: #dc3545;"></i>
                                <span class="traffic-label">Pendiente</span>
                                <span class="traffic-value">5%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Provincias -->
                <div class="metric-card provinces-card">
                    <h3>Top 5 Provincias por Envíos</h3>
                    <div class="provinces-list">
                        <div class="province-item">
                            <span class="province-name">Buenos Aires</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 85%;"></div>
                            </div>
                            <span class="province-value">64 envíos</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Córdoba</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 42%;"></div>
                            </div>
                            <span class="province-value">32 envíos</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Santa Fe</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 35%;"></div>
                            </div>
                            <span class="province-value">27 envíos</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Mendoza</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 28%;"></div>
                            </div>
                            <span class="province-value">21 envíos</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Misiones</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 22%;"></div>
                            </div>
                            <span class="province-value">17 envíos</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Gráficos -->
            <section class="charts-section">
                <div class="charts-row">
                    <div class="chart-card">
                        <h3>Envíos por día</h3>
                        <div class="chart-container">
                            <canvas id="shippingChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <h3>Costos de envío</h3>
                        <div class="chart-container">
                            <canvas id="costsChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/js/modern-admin.js?v=<?php echo time(); ?>"></script>
<script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeShippingCharts();
    setupEventListeners();
});

function setupEventListeners() {
    // Botones de período
    document.querySelectorAll('.time-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateShippingPeriod(this.dataset.period);
        });
    });

    // Toggle de comparación
    document.getElementById('compareToggle').addEventListener('change', function() {
        toggleShippingComparison(this.checked);
    });
}

function updateShippingPeriod(period) {
    console.log('Período actualizado:', period);
    // Aquí se actualizarían las estadísticas según el período
}

function toggleShippingComparison(enabled) {
    console.log('Comparación:', enabled ? 'activada' : 'desactivada');
}

function initializeShippingCharts() {
    // Gráfico de envíos por día
    const shippingCtx = document.getElementById('shippingChart').getContext('2d');
    new Chart(shippingCtx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Envíos',
                data: [12, 19, 15, 8, 22, 18, 14],
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

    // Gráfico de costos
    const costsCtx = document.getElementById('costsChart').getContext('2d');
    new Chart(costsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Express', 'Estándar', 'Retiro', 'Otros'],
            datasets: [{
                data: [45, 35, 15, 5],
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

function exportShippingStats() {
    console.log('Exportando estadísticas de envíos...');
}
</script>

</body>
</html>