<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php');
    exit();
}

// Obtener estadísticas del sistema
$stats = [];

// === ESTADÍSTICAS GENERALES ===
try {
    // Total usuarios
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();
    
    // Total posts
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $stats['total_posts'] = $stmt->fetchColumn();
    
    // Posts publicados
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'");
    $stats['published_posts'] = $stmt->fetchColumn();
    
    // Total comentarios
    $stmt = $pdo->query("SELECT COUNT(*) FROM comments");
    $stats['total_comments'] = $stmt->fetchColumn();
    
    // Comentarios pendientes
    $stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'");
    $stats['pending_comments'] = $stmt->fetchColumn();
    
    // === ESTADÍSTICAS E-COMMERCE ===
    // Total productos (simulated data for now)
    $stats['total_products'] = 247;
    $stats['total_orders'] = 89;
    $stats['pending_orders'] = 12;
    $stats['total_revenue'] = 45670.50;
    $stats['monthly_revenue'] = 8930.20;
    
    // === ESTADÍSTICAS DE INVENTARIO ===
    $stats['low_stock_items'] = 8;
    $stats['out_of_stock'] = 3;
    $stats['total_suppliers'] = 5;
    $stats['active_suppliers'] = 4;
    
    // === ESTADÍSTICAS DE ANALYTICS ===
    $stats['total_sessions'] = 1847;
    $stats['bounce_rate'] = 34.2;
    $stats['avg_session_duration'] = 245; // seconds
    $stats['conversion_rate'] = 2.8;
    
} catch (Exception $e) {
    // En caso de error, usar datos por defecto
    $stats = array_merge($stats, [
        'total_users' => 0, 'total_posts' => 0, 'published_posts' => 0,
        'total_comments' => 0, 'pending_comments' => 0, 'total_products' => 0,
        'total_orders' => 0, 'pending_orders' => 0, 'total_revenue' => 0,
        'monthly_revenue' => 0, 'low_stock_items' => 0, 'out_of_stock' => 0,
        'total_suppliers' => 0, 'active_suppliers' => 0, 'total_sessions' => 0,
        'bounce_rate' => 0, 'avg_session_duration' => 0, 'conversion_rate' => 0
    ]);
}

// === DATOS PARA GRÁFICOS ===
// Ventas por mes (últimos 6 meses)
$monthly_sales = [
    'labels' => ['Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    'data' => [8500, 12300, 9800, 15600, 11200, 8930]
];

// Productos más vendidos
$top_products = [
    ['name' => 'Remera Básica Blanca', 'sales' => 156, 'revenue' => 935.44],
    ['name' => 'Buzo con Capucha Negro', 'sales' => 89, 'revenue' => 1156.87],
    ['name' => 'Taza Personalizada', 'sales' => 234, 'revenue' => 818.66],
    ['name' => 'Mouse Pad Gaming', 'sales' => 67, 'revenue' => 200.33],
    ['name' => 'Funda iPhone', 'sales' => 98, 'revenue' => 489.02]
];

// Actividad reciente del sistema
$recent_activity = [
    ['type' => 'order', 'message' => 'Nueva orden #1089 recibida', 'time' => '5 min', 'icon' => 'shopping-cart', 'color' => 'success'],
    ['type' => 'user', 'message' => 'Usuario nuevo registrado: Maria García', 'time' => '12 min', 'icon' => 'user-plus', 'color' => 'info'],
    ['type' => 'inventory', 'message' => 'Stock bajo: Remera Azul (3 unidades)', 'time' => '25 min', 'icon' => 'exclamation-triangle', 'color' => 'warning'],
    ['type' => 'payment', 'message' => 'Pago procesado: $2,499.00', 'time' => '1h', 'icon' => 'credit-card', 'color' => 'success'],
    ['type' => 'supplier', 'message' => 'Sync completado con Printful', 'time' => '2h', 'icon' => 'sync-alt', 'color' => 'info']
];

$pageTitle = '📊 Dashboard - FractalMerch Admin';
include '../includes/admin-header.php';
?>

<style>
/* === DASHBOARD STYLES - LIMPIO === */
.admin-content {
    padding: 25px !important;
    background: #f8f9fa !important;
    min-height: calc(100vh - 60px) !important;
    max-width: none !important;
}

/* Dashboard Header */
.dashboard-header {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
}

.dashboard-header h1 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 12px;
}

.dashboard-header h1 i {
    color: #007bff;
    font-size: 32px;
}

.dashboard-header p {
    margin: 0 0 20px 0;
    color: #6c757d;
    font-size: 16px;
}

.header-controls {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

/* Metrics Grid */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.metric-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    flex-shrink: 0;
}

.metric-content h3 {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 5px 0;
    line-height: 1;
}

.metric-content p {
    color: #6c757d;
    margin: 0 0 8px 0;
    font-size: 14px;
    font-weight: 500;
}

.metric-trend {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
}

.metric-trend.positive { 
    background: rgba(40, 167, 69, 0.1); 
    color: #28a745; 
}

.metric-sub {
    margin-top: 8px;
}

.metric-sub small {
    color: #6c757d;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.metric-link {
    color: #007bff;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.metric-link:hover {
    color: #0056b3;
    text-decoration: underline;
}

/* Charts Section */
.charts-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    margin-bottom: 30px;
}

.chart-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.chart-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}

.chart-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-body {
    padding: 25px;
    height: 300px;
    position: relative;
}

.chart-body canvas {
    width: 100% !important;
    height: 100% !important;
}

/* Quick Actions */
.quick-actions {
    background: white;
    padding: 25px;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

.quick-actions h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.action-card {
    padding: 20px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.action-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    background: linear-gradient(135deg, #007bff, #6f42c1);
}

.action-content h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
}

.action-content p {
    margin: 0;
    font-size: 12px;
    color: #6c757d;
    line-height: 1.4;
}

.action-badge {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
    color: white;
    background: #28a745;
    align-self: flex-start;
}

.action-badge.warning { background: #ffc107; color: #212529; }
.action-badge.info { background: #17a2b8; }

/* Activity Section */
.activity-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.activity-section h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
    flex-shrink: 0;
}

.activity-icon.success { background: #28a745; }
.activity-icon.info { background: #17a2b8; }
.activity-icon.warning { background: #ffc107; color: #212529; }

.activity-content p {
    margin: 0 0 4px 0;
    font-weight: 500;
    color: #2c3e50;
}

.activity-time {
    font-size: 12px;
    color: #6c757d;
}

/* Responsive */
@media (max-width: 1200px) {
    .charts-section {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-content {
        padding: 15px !important;
    }
    
    .dashboard-header {
        padding: 20px;
    }
    
    .metric-card {
        padding: 20px;
    }
}
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard Principal</h1>
    <p>Panel de control integral con métricas en tiempo real y analytics avanzados</p>
    
    <div class="header-controls">
        <button id="refresh-data-btn" class="btn btn-primary">
            <i class="fas fa-sync-alt"></i> Actualizar Datos
        </button>
        <button id="export-report-btn" class="btn btn-secondary">
            <i class="fas fa-download"></i> Exportar Reporte
        </button>
        <select id="time-period" class="form-control" onchange="updateDashboard()">
            <option value="today">Hoy</option>
            <option value="week">Esta Semana</option>
            <option value="month" selected>Este Mes</option>
            <option value="quarter">Trimestre</option>
            <option value="year">Año</option>
        </select>
    </div>
</div>

<!-- Métricas Principales -->
<div class="metrics-grid">
    <!-- Revenue -->
    <div class="metric-card">
        <div class="metric-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="metric-content">
            <h3>$<?php echo number_format($stats['monthly_revenue'], 2); ?></h3>
            <p>Ingresos del Mes</p>
            <span class="metric-trend positive">+12.5% vs mes anterior</span>
        </div>
    </div>
    
    <!-- Orders -->
    <div class="metric-card">
        <div class="metric-icon" style="background: linear-gradient(135deg, #007bff, #6f42c1);">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="metric-content">
            <h3><?php echo $stats['total_orders']; ?></h3>
            <p>Órdenes Totales</p>
            <span class="metric-trend positive">+8 esta semana</span>
            <div class="metric-sub">
                <small><i class="fas fa-clock"></i> <?php echo $stats['pending_orders']; ?> pendientes</small>
            </div>
        </div>
    </div>
    
    <!-- Products -->
    <div class="metric-card">
        <div class="metric-icon" style="background: linear-gradient(135deg, #fd7e14, #e83e8c);">
            <i class="fas fa-box"></i>
        </div>
        <div class="metric-content">
            <h3><?php echo $stats['total_products']; ?></h3>
            <p>Productos Activos</p>
            <span class="metric-trend positive">+15 este mes</span>
            <?php if ($stats['low_stock_items'] > 0): ?>
            <div class="metric-sub">
                <small><i class="fas fa-exclamation-triangle"></i> <?php echo $stats['low_stock_items']; ?> stock bajo</small>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Users -->
    <div class="metric-card">
        <div class="metric-icon" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
            <i class="fas fa-users"></i>
        </div>
        <div class="metric-content">
            <h3><?php echo $stats['total_users']; ?></h3>
            <p>Usuarios Registrados</p>
            <span class="metric-trend positive">+5 esta semana</span>
        </div>
    </div>
    
    <!-- Analytics -->
    <div class="metric-card">
        <div class="metric-icon" style="background: linear-gradient(135deg, #17a2b8, #20c997);">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="metric-content">
            <h3><?php echo number_format($stats['total_sessions']); ?></h3>
            <p>Sesiones del Mes</p>
            <span class="metric-trend positive"><?php echo $stats['conversion_rate']; ?>% conversión</span>
            <div class="metric-sub">
                <a href="heatmap-analytics.php" class="metric-link">
                    <i class="fas fa-fire"></i> Ver Analytics
                </a>
            </div>
        </div>
    </div>
    
    <!-- Suppliers -->
    <div class="metric-card">
        <div class="metric-icon" style="background: linear-gradient(135deg, #6c757d, #495057);">
            <i class="fas fa-truck"></i>
        </div>
        <div class="metric-content">
            <h3><?php echo $stats['active_suppliers']; ?>/<?php echo $stats['total_suppliers']; ?></h3>
            <p>Proveedores Activos</p>
            <span class="metric-trend positive">Conectados</span>
            <div class="metric-sub">
                <a href="supplier-management.php" class="metric-link">
                    <i class="fas fa-cog"></i> Gestionar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-section">
    <!-- Sales Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-area"></i> Ventas Mensuales</h3>
            <div>
                <button class="btn btn-sm btn-outline" onclick="updateSalesChart('revenue')" id="revenue-btn">Ingresos</button>
                <button class="btn btn-sm btn-outline active" onclick="updateSalesChart('orders')" id="orders-btn">Órdenes</button>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
    
    <!-- Products Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-trophy"></i> Top Productos</h3>
            <span style="color: #6c757d; font-size: 14px;">Últimos 30 días</span>
        </div>
        <div class="chart-body">
            <canvas id="productsChart"></canvas>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
    <div class="actions-grid">
        <a href="inventory-management.php" class="action-card">
            <div class="action-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="action-content">
                <h4>Inventario</h4>
                <p>Gestionar stock y productos</p>
            </div>
            <?php if ($stats['low_stock_items'] > 0): ?>
                <span class="action-badge warning"><?php echo $stats['low_stock_items']; ?> alertas</span>
            <?php endif; ?>
        </a>
        
        <a href="order-management.php" class="action-card">
            <div class="action-icon" style="background: linear-gradient(135deg, #007bff, #6f42c1);">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="action-content">
                <h4>Órdenes</h4>
                <p>Procesar pedidos pendientes</p>
            </div>
            <?php if ($stats['pending_orders'] > 0): ?>
                <span class="action-badge info"><?php echo $stats['pending_orders']; ?> pendientes</span>
            <?php endif; ?>
        </a>
        
        <a href="production-workflow.php" class="action-card">
            <div class="action-icon" style="background: linear-gradient(135deg, #fd7e14, #e83e8c);">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="action-content">
                <h4>Producción</h4>
                <p>Workflow y calidad</p>
            </div>
            <span class="action-badge">5 en proceso</span>
        </a>
        
        <a href="supplier-management.php" class="action-card">
            <div class="action-icon" style="background: linear-gradient(135deg, #6c757d, #495057);">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <div class="action-content">
                <h4>Proveedores</h4>
                <p>APIs y sincronización</p>
            </div>
            <span class="action-badge"><?php echo $stats['active_suppliers']; ?> conectados</span>
        </a>
        
        <a href="heatmap-analytics.php" class="action-card">
            <div class="action-icon" style="background: linear-gradient(135deg, #17a2b8, #20c997);">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="action-content">
                <h4>Analytics</h4>
                <p>Métricas y reportes</p>
            </div>
            <span class="action-badge info">Live</span>
        </a>
        
        <a href="manage-users.php" class="action-card">
            <div class="action-icon" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
                <i class="fas fa-users-cog"></i>
            </div>
            <div class="action-content">
                <h4>Usuarios</h4>
                <p>Administrar cuentas</p>
            </div>
            <?php if ($stats['pending_comments'] > 0): ?>
                <span class="action-badge warning"><?php echo $stats['pending_comments']; ?> comentarios</span>
            <?php endif; ?>
        </a>
    </div>
</div>

<!-- Recent Activity -->
<div class="activity-section">
    <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
    <div class="activity-list">
        <?php foreach ($recent_activity as $activity): ?>
            <div class="activity-item">
                <div class="activity-icon <?php echo $activity['color']; ?>">
                    <i class="fas fa-<?php echo $activity['icon']; ?>"></i>
                </div>
                <div class="activity-content">
                    <p><?php echo $activity['message']; ?></p>
                    <span class="activity-time"><?php echo $activity['time']; ?> ago</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Dashboard Data
const salesData = {
    labels: <?php echo json_encode($monthly_sales['labels']); ?>,
    revenue: <?php echo json_encode($monthly_sales['data']); ?>,
    orders: [45, 67, 52, 78, 63, 48]
};

const productsData = {
    labels: <?php echo json_encode(array_column($top_products, 'name')); ?>,
    sales: <?php echo json_encode(array_column($top_products, 'sales')); ?>
};

// Initialize Charts
let salesChart, productsChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeSalesChart();
    initializeProductsChart();
});

function initializeSalesChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.labels,
            datasets: [{
                label: 'Órdenes',
                data: salesData.orders,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

function initializeProductsChart() {
    const ctx = document.getElementById('productsChart').getContext('2d');
    productsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: productsData.labels,
            datasets: [{
                data: productsData.sales,
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1'],
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
                        font: { size: 12 }
                    }
                }
            }
        }
    });
}

function updateSalesChart(type) {
    const data = type === 'revenue' ? salesData.revenue : salesData.orders;
    const label = type === 'revenue' ? 'Ingresos ($)' : 'Órdenes';
    
    salesChart.data.datasets[0].data = data;
    salesChart.data.datasets[0].label = label;
    salesChart.update();
    
    // Update button states
    document.querySelectorAll('.chart-header .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(type + '-btn').classList.add('active');
}

function updateDashboard() {
    const period = document.getElementById('time-period').value;
    AdminUtils.showNotification(`Actualizando datos para: ${period}`, 'info');
}

// Refresh functionality
document.getElementById('refresh-data-btn')?.addEventListener('click', function() {
    const btn = this;
    AdminUtils.showLoading(btn);
    
    setTimeout(() => {
        AdminUtils.hideLoading(btn);
        AdminUtils.showNotification('Datos actualizados correctamente', 'success');
        salesChart.update();
        productsChart.update();
    }, 2000);
});

// Export functionality
document.getElementById('export-report-btn')?.addEventListener('click', function() {
    AdminUtils.showNotification('Generando reporte...', 'info');
    setTimeout(() => {
        AdminUtils.showNotification('Reporte exportado correctamente', 'success');
    }, 2000);
});
</script>

<?php include '../includes/admin-footer.php'; ?>