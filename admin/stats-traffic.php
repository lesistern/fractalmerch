<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$pageTitle = '🌐 Estadísticas de Tráfico - Panel Admin';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Estadísticas de Tráfico</h1>
    <p>Análisis de fuentes de tráfico y comportamiento de usuarios</p>
    
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportTrafficStats()">
            <i class="fas fa-download"></i> Exportar
        </button>
    </div>
</div>

<!-- Filtros de Tiempo -->
<div class="content-card">
    <h3><i class="fas fa-calendar"></i> Filtros de Tiempo</h3>
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
</div>

<!-- Stats Content -->
        <div class="stats-dashboard">
            <!-- Métricas Principales -->
            <section class="metrics-section">
                <h2>Métricas Principales</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon visits">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3>3,247</h3>
                            <p>Visitantes únicos</p>
                            <span class="stat-trend negative">-2% vs mes anterior</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon sales">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-content">
                            <h3>8,456</h3>
                            <p>Páginas vistas</p>
                            <span class="stat-trend positive">+8% vs mes anterior</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon conversion">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>2:45</h3>
                            <p>Tiempo promedio</p>
                            <span class="stat-trend positive">+15s vs anterior</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon ticket">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <h3>68.5%</h3>
                            <p>Tasa de rebote</p>
                            <span class="stat-trend positive">-5.2% vs anterior</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon customers">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h3>55%</h3>
                            <p>Tráfico móvil</p>
                            <span class="stat-trend positive">+8% vs anterior</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon billing">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h3>4.2%</h3>
                            <p>Tasa de conversión</p>
                            <span class="stat-trend positive">+0.8% vs anterior</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Análisis Avanzado -->
            <section class="advanced-metrics">
                <h2>Análisis Avanzado</h2>
                
                <div class="metrics-row">
                    <div class="metric-card">
                        <h3>Fuentes de Tráfico</h3>
                        <div class="traffic-sources">
                            <div class="traffic-item">
                                <div class="traffic-icon search">
                                    <i class="fab fa-google"></i>
                                </div>
                                <div class="traffic-info">
                                    <span class="traffic-label">Búsqueda Orgánica</span>
                                    <span class="traffic-detail">Google, Bing, Yahoo</span>
                                </div>
                                <div class="traffic-stats">
                                    <span class="traffic-percent">45%</span>
                                    <span class="traffic-visitors">1,461 visitantes</span>
                                </div>
                            </div>
                            
                            <div class="traffic-item">
                                <div class="traffic-icon direct">
                                    <i class="fas fa-link"></i>
                                </div>
                                <div class="traffic-info">
                                    <span class="traffic-label">Tráfico Directo</span>
                                    <span class="traffic-detail">URL directa, marcadores</span>
                                </div>
                                <div class="traffic-stats">
                                    <span class="traffic-percent">28%</span>
                                    <span class="traffic-visitors">909 visitantes</span>
                                </div>
                            </div>
                            
                            <div class="traffic-item">
                                <div class="traffic-icon social">
                                    <i class="fab fa-facebook"></i>
                                </div>
                                <div class="traffic-info">
                                    <span class="traffic-label">Redes Sociales</span>
                                    <span class="traffic-detail">Facebook, Instagram, Twitter</span>
                                </div>
                                <div class="traffic-stats">
                                    <span class="traffic-percent">15%</span>
                                    <span class="traffic-visitors">487 visitantes</span>
                                </div>
                            </div>
                            
                            <div class="traffic-item">
                                <div class="traffic-icon email">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="traffic-info">
                                    <span class="traffic-label">Email Marketing</span>
                                    <span class="traffic-detail">Newsletters, promociones</span>
                                </div>
                                <div class="traffic-stats">
                                    <span class="traffic-percent">12%</span>
                                    <span class="traffic-visitors">390 visitantes</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <h3>Dispositivos</h3>
                        <div class="devices-chart">
                            <canvas id="devicesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Provincias -->
                <div class="metric-card provinces-card">
                    <h3>Top 5 Páginas Más Visitadas</h3>
                    <div class="provinces-list">
                        <div class="province-item">
                            <span class="province-name">Página Principal</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 85%;"></div>
                            </div>
                            <span class="province-value">2,450 vistas</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Catálogo Productos</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 65%;"></div>
                            </div>
                            <span class="province-value">1,890 vistas</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Detalle Producto</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 42%;"></div>
                            </div>
                            <span class="province-value">1,234 vistas</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Checkout</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 16%;"></div>
                            </div>
                            <span class="province-value">456 vistas</span>
                        </div>
                        <div class="province-item">
                            <span class="province-name">Contacto</span>
                            <div class="province-bar">
                                <div class="province-fill" style="width: 12%;"></div>
                            </div>
                            <span class="province-value">342 vistas</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Análisis de Páginas -->
            <section class="popular-pages-section">
                <div class="metrics-row">
                    <div class="metric-card">
                        <h3>Páginas Más Visitadas</h3>
                        <div class="pages-list">
                            <div class="page-item">
                                <div class="page-info">
                                    <span class="page-url">/</span>
                                    <span class="page-title">Página Principal</span>
                                </div>
                                <div class="page-stats">
                                    <span class="page-views">2,450 vistas</span>
                                    <span class="page-time">3:15 min</span>
                                </div>
                            </div>
                            
                            <div class="page-item">
                                <div class="page-info">
                                    <span class="page-url">/particulares.php</span>
                                    <span class="page-title">Catálogo de Productos</span>
                                </div>
                                <div class="page-stats">
                                    <span class="page-views">1,890 vistas</span>
                                    <span class="page-time">4:22 min</span>
                                </div>
                            </div>
                            
                            <div class="page-item">
                                <div class="page-info">
                                    <span class="page-url">/product-detail.php</span>
                                    <span class="page-title">Detalle de Producto</span>
                                </div>
                                <div class="page-stats">
                                    <span class="page-views">1,234 vistas</span>
                                    <span class="page-time">2:45 min</span>
                                </div>
                            </div>
                            
                            <div class="page-item">
                                <div class="page-info">
                                    <span class="page-url">/checkout.php</span>
                                    <span class="page-title">Checkout</span>
                                </div>
                                <div class="page-stats">
                                    <span class="page-views">456 vistas</span>
                                    <span class="page-time">5:30 min</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <h3>Ubicaciones Geográficas</h3>
                        <div class="locations-list">
                            <div class="location-item">
                                <div class="country-flag">🇦🇷</div>
                                <div class="location-info">
                                    <span class="country-name">Argentina</span>
                                    <span class="city-name">Buenos Aires</span>
                                </div>
                                <div class="location-stats">
                                    <span class="location-percent">65%</span>
                                    <span class="location-visitors">2,110 visitantes</span>
                                </div>
                            </div>
                            
                            <div class="location-item">
                                <div class="country-flag">🇦🇷</div>
                                <div class="location-info">
                                    <span class="country-name">Argentina</span>
                                    <span class="city-name">Córdoba</span>
                                </div>
                                <div class="location-stats">
                                    <span class="location-percent">15%</span>
                                    <span class="location-visitors">487 visitantes</span>
                                </div>
                            </div>
                            
                            <div class="location-item">
                                <div class="country-flag">🇦🇷</div>
                                <div class="location-info">
                                    <span class="country-name">Argentina</span>
                                    <span class="city-name">Rosario</span>
                                </div>
                                <div class="location-stats">
                                    <span class="location-percent">12%</span>
                                    <span class="location-visitors">390 visitantes</span>
                                </div>
                            </div>
                            
                            <div class="location-item">
                                <div class="country-flag">🇦🇷</div>
                                <div class="location-info">
                                    <span class="country-name">Argentina</span>
                                    <span class="city-name">Mendoza</span>
                                </div>
                                <div class="location-stats">
                                    <span class="location-percent">8%</span>
                                    <span class="location-visitors">260 visitantes</span>
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
                        <h3>Tráfico por hora del día</h3>
                        <div class="chart-container">
                            <canvas id="trafficTimeChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <h3>Embudo de conversión</h3>
                        <div class="chart-container">
                            <canvas id="conversionChart"></canvas>
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
    initializeTrafficCharts();
    setupEventListeners();
});

function setupEventListeners() {
    // Botones de período
    document.querySelectorAll('.time-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateTrafficPeriod(this.dataset.period);
        });
    });

    // Toggle de comparación
    document.getElementById('compareToggle').addEventListener('change', function() {
        toggleTrafficComparison(this.checked);
    });
}

function updateTrafficPeriod(period) {
    console.log('Período actualizado:', period);
    // Aquí se actualizarían las estadísticas según el período
}

function toggleTrafficComparison(enabled) {
    console.log('Comparación:', enabled ? 'activada' : 'desactivada');
}

function initializeTrafficCharts() {
    // Gráfico de dispositivos
    const devicesCtx = document.getElementById('devicesChart').getContext('2d');
    new Chart(devicesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Móvil', 'Desktop', 'Tablet'],
            datasets: [{
                data: [55, 35, 10],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107'
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

    // Gráfico de tráfico por hora
    const trafficTimeCtx = document.getElementById('trafficTimeChart').getContext('2d');
    new Chart(trafficTimeCtx, {
        type: 'line',
        data: {
            labels: ['00', '02', '04', '06', '08', '10', '12', '14', '16', '18', '20', '22'],
            datasets: [{
                label: 'Visitantes',
                data: [45, 32, 28, 35, 85, 120, 145, 180, 220, 195, 165, 98],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
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

    // Gráfico de conversión
    const conversionCtx = document.getElementById('conversionChart').getContext('2d');
    new Chart(conversionCtx, {
        type: 'bar',
        data: {
            labels: ['Visitas', 'Productos vistos', 'Carrito', 'Checkout', 'Compras'],
            datasets: [{
                label: 'Usuarios',
                data: [3247, 1136, 584, 260, 152],
                backgroundColor: [
                    '#007bff',
                    '#0056b3',
                    '#004085',
                    '#002752',
                    '#001a33'
                ]
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
}

function exportTrafficStats() {
    console.log('Exportando estadísticas de tráfico...');
}
</script>

<style>
.traffic-sources {
    space-y: 1rem;
}

.traffic-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.traffic-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.traffic-icon.search { background: #4285f4; }
.traffic-icon.direct { background: #34a853; }
.traffic-icon.social { background: #1877f2; }
.traffic-icon.email { background: #ea4335; }

.traffic-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.traffic-label {
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}

.traffic-detail {
    font-size: 0.8rem;
    color: #666;
}

.traffic-stats {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.traffic-percent {
    font-weight: 700;
    font-size: 1.1rem;
    color: #007bff;
}

.traffic-visitors {
    font-size: 0.8rem;
    color: #666;
}

.pages-list,
.locations-list {
    space-y: 0.75rem;
}

.page-item,
.location-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

.page-info,
.location-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.page-url {
    font-family: 'Courier New', monospace;
    font-size: 0.8rem;
    color: #007bff;
    background: #e3f2fd;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    display: inline-block;
    margin-bottom: 0.25rem;
}

.page-title,
.country-name {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.city-name {
    font-size: 0.8rem;
    color: #666;
}

.page-stats,
.location-stats {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.page-views,
.location-percent {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.page-time,
.location-visitors {
    font-size: 0.8rem;
    color: #666;
}

.country-flag {
    font-size: 1.5rem;
    width: 30px;
    text-align: center;
}

.traffic-time-chart {
    height: 300px;
}

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

.traffic-sources-section, .popular-pages-section, .traffic-time-section { margin: 0.75rem 0 !important; }
.metrics-row { gap: 0.75rem !important; margin-bottom: 0.75rem !important; }
.metric-card { padding: 0.5rem !important; }
.metric-card h3 { margin-bottom: 0.5rem !important; font-size: 0.85rem !important; }

.traffic-item { 
    padding: 0.4rem !important; 
    margin-bottom: 0.3rem !important; 
    gap: 0.4rem !important;
    border-radius: 4px !important;
}
.traffic-icon { width: 28px !important; height: 28px !important; font-size: 0.8rem !important; }
.traffic-label { font-size: 0.7rem !important; line-height: 1.2 !important; }
.traffic-detail { font-size: 0.6rem !important; }
.traffic-percent { font-size: 0.8rem !important; }
.traffic-visitors { font-size: 0.6rem !important; }

.page-item, .location-item { 
    padding: 0.3rem !important; 
    margin-bottom: 0.25rem !important; 
    gap: 0.4rem !important;
    border-radius: 4px !important;
}
.page-url { 
    font-size: 0.6rem !important; 
    padding: 0.15rem 0.3rem !important;
    border-radius: 3px !important;
}
.page-title, .country-name { font-size: 0.7rem !important; line-height: 1.2 !important; }
.city-name { font-size: 0.6rem !important; }
.page-views, .location-percent { font-size: 0.7rem !important; }
.page-time, .location-visitors { font-size: 0.6rem !important; }
.country-flag { font-size: 1rem !important; width: 20px !important; }

.traffic-time-chart { height: 180px !important; }
.traffic-time-section .metric-card { padding: 0.5rem !important; }
</style>

<?php include 'admin-master-footer.php'; ?>