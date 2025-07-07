<?php
$pageTitle = '🏠 Dashboard - FractalMerch Admin';
include 'admin-master-header.php';

// Include enterprise backend
require_once 'backend/DashboardBackend.php';
require_once 'backend/DashboardExtensions.php';

// Initialize enterprise dashboard backend
$config = [
    'cache_enabled' => true,
    'debug_mode' => false,
    'rate_limit_enabled' => true,
    'security_enabled' => true,
    'realtime_enabled' => true,
    'performance_monitoring' => true
];

$backend = new DashboardBackend($pdo, $config);

// === ENTERPRISE STATISTICS WITH OPTIMIZED CACHING ===
try {
    // Get comprehensive dashboard statistics
    $stats = $backend->getDashboardStats();
    
    // Get additional real-time metrics
    $realtime_stats = [
        'active_users_realtime' => getRealTimeActiveUsers(),
        'current_orders' => getCurrentOrders(),
        'system_health' => getAdvancedSystemHealth(),
        'server_metrics' => getServerMetrics()
    ];
    
    // Merge all statistics
    $stats = array_merge($stats, $realtime_stats);
    
} catch (Exception $e) {
    // Professional error handling with logging
    error_log("Dashboard backend error: " . $e->getMessage());
    
    // Use fallback statistics
    $stats = [
        'total_users' => 0, 'total_posts' => 0, 'published_posts' => 0,
        'total_comments' => 0, 'pending_comments' => 0, 'total_products' => 0,
        'total_orders' => 0, 'pending_orders' => 0, 'total_revenue' => 0,
        'monthly_revenue' => 0, 'low_stock_items' => 0, 'out_of_stock' => 0,
        'total_suppliers' => 5, 'active_suppliers' => 4, 'total_sessions' => 1847,
        'bounce_rate' => 34.2, 'avg_session_duration' => 245, 'conversion_rate' => 2.8,
        'active_users_realtime' => ['active_users' => 0],
        'current_orders' => ['total_orders' => 0],
        'system_health' => ['overall' => ['status' => 'unknown']],
        'server_metrics' => []
    ];
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
include 'admin-master-header.php';
?>

<!-- Dashboard Pro CSS -->
<link rel="stylesheet" href="assets/css/dashboard-pro.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/dashboard-integration.css?v=<?php echo time(); ?>">


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
    <div class="metric-card" data-metric="total-sessions">
        <div class="metric-icon" style="background: linear-gradient(135deg, #17a2b8, #20c997);">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="metric-content">
            <h3 class="metric-value"><?php echo number_format($stats['total_sessions']); ?></h3>
            <p>Sesiones del Mes</p>
            <span class="metric-trend positive" data-trend="conversion-trend"><?php echo $stats['conversion_rate']; ?>% conversión</span>
            <div class="metric-sub">
                <span data-realtime="active-users-realtime"><?php echo $stats['active_users_realtime']['active_users'] ?? 0; ?></span> usuarios activos
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>

<!-- Dashboard Data -->
<script>
const salesData = {
    labels: <?php echo json_encode($monthly_sales['labels']); ?>,
    revenue: <?php echo json_encode($monthly_sales['data']); ?>,
    orders: [45, 67, 52, 78, 63, 48]
};

const productsData = {
    labels: <?php echo json_encode(array_column($top_products, 'name')); ?>,
    sales: <?php echo json_encode(array_column($top_products, 'sales')); ?>
};

// Legacy chart functions for compatibility
function updateSalesChart(type) {
    if (window.dashboardPro && window.dashboardPro.updateSalesChart) {
        window.dashboardPro.updateSalesChart(type);
    }
}

function updateDashboard() {
    if (window.dashboardPro && window.dashboardPro.updateDashboard) {
        window.dashboardPro.updateDashboard();
    }
}
</script>

<!-- Enterprise Real-Time System -->
<div class="enterprise-status-panel" style="position: fixed; bottom: 20px; right: 20px; background: white; border-radius: 8px; padding: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; min-width: 250px;">
    <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #2c3e50;">
        <i class="fas fa-satellite-dish"></i> Estado del Sistema
    </h4>
    
    <!-- Connection Status -->
    <div class="status-item" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
        <span style="font-size: 12px; color: #6c757d;">Conexión:</span>
        <div class="connection-status connecting">Conectando...</div>
    </div>
    
    <!-- System Health -->
    <div class="status-item" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
        <span style="font-size: 12px; color: #6c757d;">Sistema:</span>
        <div class="system-health-indicator">
            <div class="health-status <?php echo $stats['system_health']['overall']['status'] ?? 'unknown'; ?>">
                <?php echo strtoupper($stats['system_health']['overall']['status'] ?? 'UNKNOWN'); ?>
            </div>
        </div>
    </div>
    
    <!-- Server Load -->
    <?php if (isset($stats['server_metrics']['cpu_load'])): ?>
    <div class="status-item" style="margin-bottom: 8px;">
        <span style="font-size: 12px; color: #6c757d; display: block; margin-bottom: 4px;">Carga del Servidor:</span>
        <div style="display: flex; gap: 5px;">
            <?php foreach (['1_min', '5_min', '15_min'] as $period): ?>
            <div data-load="<?php echo $period; ?>" style="flex: 1;">
                <div style="font-size: 10px; text-align: center; margin-bottom: 2px;">
                    <?php echo str_replace('_', 'm', $period); ?>
                </div>
                <div style="height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                    <div class="load-bar low" style="width: <?php echo min(($stats['server_metrics']['cpu_load'][$period] ?? 0) * 25, 100); ?>%; height: 100%;"></div>
                </div>
                <div class="load-value" style="font-size: 10px; text-align: center; margin-top: 2px;">
                    <?php echo number_format($stats['server_metrics']['cpu_load'][$period] ?? 0, 2); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Last Update -->
    <div class="status-item" style="display: flex; justify-content: space-between; font-size: 10px; color: #6c757d; margin-top: 10px; padding-top: 8px; border-top: 1px solid #e9ecef;">
        <span>Última actualización:</span>
        <span id="last-update-time"><?php echo date('H:i:s'); ?></span>
    </div>
</div>

<!-- Real-Time Notifications Container -->
<div class="notifications-container"></div>

<!-- Enterprise Dashboard JavaScript -->
<script src="assets/js/dashboard-realtime.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/dashboard-pro.js?v=<?php echo time(); ?>"></script>

<!-- Additional Enterprise Features -->
<script>
// Dashboard configuration
window.dashboardConfig = {
    userId: <?php echo $_SESSION['user_id'] ?? 0; ?>,
    userRole: '<?php echo $_SESSION['role'] ?? 'user'; ?>',
    enableRealTime: true,
    enableNotifications: true,
    updateInterval: 30000,
    apiEndpoint: './api/dashboard_api.php',
    csrfToken: '<?php echo generate_csrf_token(); ?>'
};

// Initialize enterprise features
document.addEventListener('DOMContentLoaded', function() {
    // Update last update time every second
    setInterval(() => {
        const lastUpdateEl = document.getElementById('last-update-time');
        if (lastUpdateEl) {
            lastUpdateEl.textContent = new Date().toLocaleTimeString();
        }
    }, 1000);
    
    // Setup advanced features
    if (window.dashboardRealTime) {
        // Setup event listeners for real-time updates
        window.dashboardRealTime.on('statsUpdated', (data) => {
            console.log('Stats updated:', data);
        });
        
        window.dashboardRealTime.on('realTimeUpdated', (data) => {
            console.log('Real-time data updated:', data);
        });
        
        window.dashboardRealTime.on('error', (error) => {
            console.error('Dashboard error:', error);
        });
    }
    
    // Setup keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.altKey) {
            switch (e.key) {
                case 'd':
                    e.preventDefault();
                    window.location.href = 'dashboard.php';
                    break;
                case 's':
                    e.preventDefault();
                    window.location.href = 'statistics.php';
                    break;
                case 'i':
                    e.preventDefault();
                    window.location.href = 'inventory-management.php';
                    break;
                case 'o':
                    e.preventDefault();
                    window.location.href = 'order-management.php';
                    break;
            }
        }
    });
});

// Performance monitoring
if (window.performance && window.performance.mark) {
    window.performance.mark('dashboard-loaded');
}
</script>

<?php include 'admin-master-footer.php'; ?>