<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '📦 Estadísticas de Productos - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <!-- Main Content -->
    <div class="modern-admin-main">
        <!-- Header -->
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-box"></i> Estadísticas de Productos</h1>
                <p class="header-subtitle">Análisis de rendimiento y ventas por producto</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="exportProductStats()">
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
                <h2>Métricas Principales</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon visits">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-content">
                            <h3>248</h3>
                            <p>Productos totales</p>
                            <span class="stat-trend positive">+8 nuevos este mes</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon sales">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3>1,247</h3>
                            <p>Productos vendidos</p>
                            <span class="stat-trend positive">+18% vs mes anterior</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon billing">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-content">
                            <h3>12,450</h3>
                            <p>Visualizaciones</p>
                            <span class="stat-trend positive">+12% vs mes anterior</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon conversion">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-content">
                            <h3>10.1%</h3>
                            <p>Tasa conversión</p>
                            <span class="stat-trend positive">+1.2% vs anterior</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon ticket">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3>4.8</h3>
                            <p>Valoración promedio</p>
                            <span class="stat-trend positive">+0.3 vs anterior</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon customers">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                        <div class="stat-content">
                            <h3>92%</h3>
                            <p>Satisfacción cliente</p>
                            <span class="stat-trend positive">+2% vs anterior</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Análisis Avanzado -->
            <section class="advanced-metrics">
                <h2>Análisis Avanzado</h2>
                
                <div class="metrics-row">
                    <div class="metric-card">
                        <h3>Top 5 Productos por Ventas</h3>
                        <div class="products-ranking">
                            <div class="product-rank-item">
                                <div class="rank-number">1</div>
                                <div class="product-info">
                                    <img src="../assets/images/products/remera.svg" alt="Remera" class="product-thumb">
                                    <div class="product-details">
                                        <span class="product-name">Remera Personalizada</span>
                                        <span class="product-category">Remeras</span>
                                    </div>
                                </div>
                                <div class="product-stats">
                                    <span class="product-sales">156 ventas</span>
                                    <span class="product-revenue">$93,600</span>
                                </div>
                            </div>
                            
                            <div class="product-rank-item">
                                <div class="rank-number">2</div>
                                <div class="product-info">
                                    <img src="../assets/images/products/buzo.svg" alt="Buzo" class="product-thumb">
                                    <div class="product-details">
                                        <span class="product-name">Buzo Personalizado</span>
                                        <span class="product-category">Buzos</span>
                                    </div>
                                </div>
                                <div class="product-stats">
                                    <span class="product-sales">89 ventas</span>
                                    <span class="product-revenue">$115,711</span>
                                </div>
                            </div>
                            
                            <div class="product-rank-item">
                                <div class="rank-number">3</div>
                                <div class="product-info">
                                    <img src="../assets/images/products/taza.svg" alt="Taza" class="product-thumb">
                                    <div class="product-details">
                                        <span class="product-name">Taza Personalizada</span>
                                        <span class="product-category">Tazas</span>
                                    </div>
                                </div>
                                <div class="product-stats">
                                    <span class="product-sales">134 ventas</span>
                                    <span class="product-revenue">$46,866</span>
                                </div>
                            </div>
                            
                            <div class="product-rank-item">
                                <div class="rank-number">4</div>
                                <div class="product-info">
                                    <img src="../assets/images/products/mousepad.svg" alt="Mouse Pad" class="product-thumb">
                                    <div class="product-details">
                                        <span class="product-name">Mouse Pad Personalizado</span>
                                        <span class="product-category">Accesorios</span>
                                    </div>
                                </div>
                                <div class="product-stats">
                                    <span class="product-sales">67 ventas</span>
                                    <span class="product-revenue">$20,133</span>
                                </div>
                            </div>
                            
                            <div class="product-rank-item">
                                <div class="rank-number">5</div>
                                <div class="product-info">
                                    <img src="../assets/images/products/funda.svg" alt="Funda" class="product-thumb">
                                    <div class="product-details">
                                        <span class="product-name">Funda Personalizada</span>
                                        <span class="product-category">Accesorios</span>
                                    </div>
                                </div>
                                <div class="product-stats">
                                    <span class="product-sales">43 ventas</span>
                                    <span class="product-revenue">$21,457</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <h3>Categorías por Ventas</h3>
                        <div class="categories-chart">
                            <canvas id="categoriesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Provincias -->
                <div class="metric-card provinces-card">
                    <h3>Estado del Stock por Categoría</h3>
                    <div class="provinces-list">
                        <div class="province-item">
                            <span class="province-name">Remeras</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 85%;"></div>
                            </div>
                            <span class="province-value">89 productos</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Buzos</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 72%;"></div>
                            </div>
                            <span class="province-value">56 productos</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Tazas</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 58%;"></div>
                            </div>
                            <span class="province-value">43 productos</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Accesorios</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 35%;"></div>
                            </div>
                            <span class="province-value">28 productos</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Hogar</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 22%;"></div>
                            </div>
                            <span class="province-value">18 productos</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Análisis de Inventario -->
            <section class="inventory-section">
                <div class="metrics-row">
                    <div class="metric-card">
                        <h3>Estado del Stock</h3>
                        <div class="stock-overview">
                            <div class="stock-item">
                                <div class="stock-icon good">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stock-info">
                                    <span class="stock-label">Stock bueno</span>
                                    <span class="stock-count">189 productos</span>
                                </div>
                            </div>
                            <div class="stock-item">
                                <div class="stock-icon warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stock-info">
                                    <span class="stock-label">Stock bajo</span>
                                    <span class="stock-count">34 productos</span>
                                </div>
                            </div>
                            <div class="stock-item">
                                <div class="stock-icon danger">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stock-info">
                                    <span class="stock-label">Sin stock</span>
                                    <span class="stock-count">12 productos</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <h3>Productos con Stock Bajo</h3>
                        <div class="low-stock-list">
                            <div class="low-stock-item">
                                <img src="../assets/images/products/almohada.svg" alt="Almohada" class="product-thumb">
                                <div class="product-details">
                                    <span class="product-name">Almohada Personalizada</span>
                                    <span class="stock-level warning">5 unidades</span>
                                </div>
                            </div>
                            <div class="low-stock-item">
                                <img src="../assets/images/products/mousepad.svg" alt="Mouse Pad" class="product-thumb">
                                <div class="product-details">
                                    <span class="product-name">Mouse Pad XL</span>
                                    <span class="stock-level warning">8 unidades</span>
                                </div>
                            </div>
                            <div class="low-stock-item">
                                <img src="../assets/images/products/funda.svg" alt="Funda" class="product-thumb">
                                <div class="product-details">
                                    <span class="product-name">Funda iPhone 15</span>
                                    <span class="stock-level danger">2 unidades</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Gráficos -->
            <section class="charts-section">
                <div class="charts-row">
                    <div class="chart-card">
                        <h3>Productos más vendidos</h3>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <h3>Evolución de stock</h3>
                        <div class="chart-container">
                            <canvas id="stockChart"></canvas>
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
    initializeProductCharts();
    setupEventListeners();
});

function setupEventListeners() {
    // Botones de período
    document.querySelectorAll('.time-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateProductsPeriod(this.dataset.period);
        });
    });

    // Toggle de comparación
    document.getElementById('compareToggle').addEventListener('change', function() {
        toggleProductsComparison(this.checked);
    });
}

function updateProductsPeriod(period) {
    console.log('Período actualizado:', period);
    // Aquí se actualizarían las estadísticas según el período
}

function toggleProductsComparison(enabled) {
    console.log('Comparación:', enabled ? 'activada' : 'desactivada');
}

function initializeProductCharts() {
    // Gráfico de categorías
    const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
    new Chart(categoriesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Remeras', 'Buzos', 'Tazas', 'Accesorios', 'Hogar'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6f42c1'
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

    // Gráfico de ventas
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Productos vendidos',
                data: [35, 42, 28, 38, 45, 52, 48],
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

    // Gráfico de stock
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    new Chart(stockCtx, {
        type: 'doughnut',
        data: {
            labels: ['Stock Bueno', 'Stock Bajo', 'Sin Stock'],
            datasets: [{
                data: [189, 34, 12],
                backgroundColor: [
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

function exportProductStats() {
    console.log('Exportando estadísticas de productos...');
}
</script>

<style>
.products-ranking {
    space-y: 1rem;
}

.product-rank-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.rank-number {
    width: 30px;
    height: 30px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

.product-thumb {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 6px;
}

.product-details {
    display: flex;
    flex-direction: column;
}

.product-name {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.product-category {
    font-size: 0.8rem;
    color: #666;
}

.product-stats {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.product-sales {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.product-revenue {
    font-size: 0.8rem;
    color: #28a745;
    font-weight: 600;
}

.stock-overview {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stock-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.stock-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.stock-icon.good { background: #28a745; }
.stock-icon.warning { background: #ffc107; }
.stock-icon.danger { background: #dc3545; }

.stock-info {
    display: flex;
    flex-direction: column;
}

.stock-label {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.stock-count {
    font-size: 0.8rem;
    color: #666;
}

.low-stock-list {
    space-y: 0.75rem;
}

.low-stock-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

.stock-level {
    font-size: 0.8rem;
    font-weight: 600;
}

.stock-level.warning { color: #ffc107; }
.stock-level.danger { color: #dc3545; }

/* ULTRA COMPACTO - MÁXIMA OPTIMIZACIÓN */
.modern-admin-main { padding: 1rem !important; }
.tiendanube-header { padding: 0.5rem 1rem !important; margin-bottom: 1rem !important; min-height: 40px !important; }
.header-left h1 { font-size: 1rem !important; }
.header-subtitle { font-size: 0.7rem !important; }
.tn-btn { padding: 0.3rem 0.6rem !important; font-size: 0.7rem !important; height: 24px !important; }

.stats-dashboard { padding: 0.75rem !important; }
.metrics-section h2 { margin-bottom: 0.5rem !important; font-size: 1rem !important; }
.stats-grid { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important; gap: 0.5rem !important; margin-bottom: 1rem !important; }
.stat-card { padding: 0.5rem !important; gap: 0.4rem !important; }
.stat-icon { width: 28px !important; height: 28px !important; font-size: 0.8rem !important; }
.stat-content h3 { font-size: 1.1rem !important; margin: 0 !important; line-height: 1.2 !important; }
.stat-content p { font-size: 0.7rem !important; margin: 0 !important; }
.stat-trend { font-size: 0.6rem !important; }

.top-products-section, .inventory-section { margin: 0.75rem 0 !important; }
.metrics-row { gap: 0.75rem !important; margin-bottom: 0.75rem !important; }
.metric-card { padding: 0.5rem !important; }
.metric-card h3 { margin-bottom: 0.5rem !important; font-size: 0.85rem !important; }

.product-rank-item { 
    padding: 0.4rem !important; 
    margin-bottom: 0.3rem !important; 
    gap: 0.4rem !important;
    border-radius: 4px !important;
}
.rank-number { width: 18px !important; height: 18px !important; font-size: 0.65rem !important; }
.product-info { gap: 0.3rem !important; }
.product-thumb { width: 24px !important; height: 24px !important; border-radius: 3px !important; }
.product-name { font-size: 0.7rem !important; line-height: 1.2 !important; }
.product-category { font-size: 0.6rem !important; }
.product-sales { font-size: 0.7rem !important; }
.product-revenue { font-size: 0.6rem !important; }

.stock-item { padding: 0.3rem !important; gap: 0.4rem !important; border-radius: 4px !important; }
.stock-icon { width: 24px !important; height: 24px !important; font-size: 0.7rem !important; }
.stock-label { font-size: 0.7rem !important; }
.stock-count { font-size: 0.6rem !important; }

.low-stock-item { 
    padding: 0.3rem !important; 
    gap: 0.3rem !important; 
    margin-bottom: 0.25rem !important;
    border-radius: 4px !important;
}
.low-stock-item .product-thumb { width: 20px !important; height: 20px !important; }
.low-stock-item .product-name { font-size: 0.65rem !important; }
.stock-level { font-size: 0.6rem !important; }
</style>

</body>
</html>