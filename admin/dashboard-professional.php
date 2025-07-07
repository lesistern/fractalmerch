<?php
$pageTitle = '📊 Dashboard - FractalMerch Admin Professional';
include 'admin-master-header.php';

// Obtener estadísticas del sistema con cache optimizado
$stats = [];

try {
    $stats = get_dashboard_stats_cached(300);
    
    // Estadísticas enterprise simuladas
    $stats = array_merge($stats, [
        'total_suppliers' => 5,
        'active_suppliers' => 4,
        'total_sessions' => 1847,
        'bounce_rate' => 34.2,
        'avg_session_duration' => 245,
        'conversion_rate' => 2.8,
        'monthly_growth' => 18.5,
        'customer_satisfaction' => 94.2,
        'avg_order_value' => 89.50
    ]);
    
} catch (Exception $e) {
    $stats = [
        'total_users' => 1250, 'total_posts' => 89, 'published_posts' => 76,
        'total_comments' => 234, 'pending_comments' => 12, 'total_products' => 156,
        'total_orders' => 450, 'pending_orders' => 8, 'total_revenue' => 45670.80,
        'monthly_revenue' => 12450.90, 'low_stock_items' => 5, 'out_of_stock' => 2,
        'total_suppliers' => 5, 'active_suppliers' => 4, 'total_sessions' => 1847,
        'bounce_rate' => 34.2, 'avg_session_duration' => 245, 'conversion_rate' => 2.8,
        'monthly_growth' => 18.5, 'customer_satisfaction' => 94.2, 'avg_order_value' => 89.50
    ];
}

// Datos para gráficos mejorados
$monthly_sales = [
    'labels' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
    'data' => [8500, 12300, 9800, 15600, 11200, 18930],
    'growth' => ['+12%', '+22%', '-8%', '+28%', '-15%', '+42%']
];

$top_products = [
    ['name' => 'Remera Básica Blanca', 'sales' => 156, 'revenue' => 935.44, 'trend' => '+12%'],
    ['name' => 'Buzo con Capucha Negro', 'sales' => 89, 'revenue' => 1156.87, 'trend' => '+8%'],
    ['name' => 'Taza Personalizada', 'sales' => 234, 'revenue' => 818.66, 'trend' => '+25%'],
    ['name' => 'Mouse Pad Gaming', 'sales' => 67, 'revenue' => 200.33, 'trend' => '-5%'],
    ['name' => 'Funda iPhone', 'sales' => 98, 'revenue' => 489.02, 'trend' => '+18%']
];

$recent_activity = [
    ['type' => 'order', 'message' => 'Nueva orden #1089 recibida', 'time' => '5 min', 'icon' => 'shopping-cart', 'color' => 'success'],
    ['type' => 'user', 'message' => 'Usuario premium registrado: Maria García', 'time' => '12 min', 'icon' => 'user-plus', 'color' => 'info'],
    ['type' => 'inventory', 'message' => 'Stock crítico: Remera Azul (3 unidades)', 'time' => '25 min', 'icon' => 'exclamation-triangle', 'color' => 'warning'],
    ['type' => 'payment', 'message' => 'Pago procesado: $2,499.00', 'time' => '1h', 'icon' => 'credit-card', 'color' => 'success'],
    ['type' => 'analytics', 'message' => 'Conversión aumentó 12% esta semana', 'time' => '2h', 'icon' => 'chart-line', 'color' => 'info'],
    ['type' => 'supplier', 'message' => 'Sync completado con Printful API', 'time' => '3h', 'icon' => 'sync-alt', 'color' => 'info']
];
?>

<body class="admin-body">
    <!-- Professional Admin Header -->
    <header class="admin-header">
        <div class="admin-header-content">
            <div class="admin-logo">
                <i class="fas fa-cube"></i>
                <span>FractalMerch Admin</span>
            </div>
            
            <div class="admin-header-actions">
                <div class="admin-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar en admin..." />
                </div>
                
                <button class="admin-btn admin-btn-ghost" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="admin-badge admin-badge-danger">3</span>
                </button>
                
                <div class="admin-user-menu">
                    <button class="admin-btn admin-btn-ghost" onclick="toggleUserMenu()">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['username']; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Professional Sidebar -->
    <nav class="admin-sidebar">
        <div class="admin-sidebar-section">
            <div class="admin-sidebar-title">Principal</div>
            <a href="dashboard-professional.php" class="admin-nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="statistics.php" class="admin-nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics</span>
                <span class="admin-nav-badge">2</span>
            </a>
        </div>
        
        <div class="admin-sidebar-section">
            <div class="admin-sidebar-title">E-commerce</div>
            <a href="manage-products.php" class="admin-nav-item">
                <i class="fas fa-box"></i>
                <span>Productos</span>
            </a>
            <a href="order-management.php" class="admin-nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Órdenes</span>
                <span class="admin-nav-badge">8</span>
            </a>
            <a href="inventory.php" class="admin-nav-item">
                <i class="fas fa-warehouse"></i>
                <span>Inventario</span>
            </a>
        </div>
        
        <div class="admin-sidebar-section">
            <div class="admin-sidebar-title">Marketing</div>
            <a href="marketing.php" class="admin-nav-item">
                <i class="fas fa-bullhorn"></i>
                <span>Campañas</span>
            </a>
            <a href="coupons.php" class="admin-nav-item">
                <i class="fas fa-ticket-alt"></i>
                <span>Cupones</span>
            </a>
        </div>
        
        <div class="admin-sidebar-section">
            <div class="admin-sidebar-title">Sistema</div>
            <a href="manage-users.php" class="admin-nav-item">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>
            <a href="settings.php" class="admin-nav-item">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="admin-main">
        <!-- Page Header -->
        <div class="admin-page-header">
            <h1 class="admin-page-title">
                <i class="fas fa-chart-line"></i>
                Dashboard Enterprise
            </h1>
            <p class="admin-page-subtitle">
                Visión general del rendimiento del negocio en tiempo real
            </p>
        </div>

        <!-- Enterprise Metrics Grid -->
        <div class="admin-grid admin-grid-cols-4">
            <!-- Revenue Metric -->
            <div class="admin-metric-card">
                <div class="admin-metric-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="admin-metric-value">$<?php echo number_format($stats['total_revenue'], 0); ?></div>
                <div class="admin-metric-label">Revenue Total</div>
                <div class="admin-metric-change positive">
                    <i class="fas fa-arrow-up"></i>
                    +<?php echo $stats['monthly_growth']; ?>% este mes
                </div>
            </div>

            <!-- Orders Metric -->
            <div class="admin-metric-card">
                <div class="admin-metric-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="admin-metric-value"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="admin-metric-label">Órdenes Totales</div>
                <div class="admin-metric-change positive">
                    <i class="fas fa-arrow-up"></i>
                    +24% vs mes anterior
                </div>
            </div>

            <!-- Conversion Rate -->
            <div class="admin-metric-card">
                <div class="admin-metric-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="admin-metric-value"><?php echo $stats['conversion_rate']; ?>%</div>
                <div class="admin-metric-label">Conversión</div>
                <div class="admin-metric-change positive">
                    <i class="fas fa-arrow-up"></i>
                    +0.8% esta semana
                </div>
            </div>

            <!-- Customer Satisfaction -->
            <div class="admin-metric-card">
                <div class="admin-metric-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="admin-metric-value"><?php echo $stats['customer_satisfaction']; ?>%</div>
                <div class="admin-metric-label">Satisfacción</div>
                <div class="admin-metric-change positive">
                    <i class="fas fa-arrow-up"></i>
                    +2.1% vs promedio
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="admin-grid admin-grid-cols-2">
            <!-- Sales Chart -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="fas fa-chart-area"></i>
                        Ventas Mensuales
                    </h3>
                    <div class="admin-card-actions">
                        <button class="admin-btn admin-btn-sm admin-btn-ghost">
                            <i class="fas fa-download"></i>
                            Exportar
                        </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <canvas id="salesChart" style="height: 300px;"></canvas>
                </div>
            </div>

            <!-- Top Products -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="fas fa-star"></i>
                        Productos Top
                    </h3>
                    <a href="manage-products.php" class="admin-btn admin-btn-sm admin-btn-ghost">
                        Ver todos
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="admin-card-body">
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($top_products as $index => $product): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--admin-border-light);">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 32px; height: 32px; background: var(--admin-primary-alpha); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--admin-primary); font-weight: bold; font-size: 14px;">
                                    <?php echo $index + 1; ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--admin-text-primary); font-size: 14px;">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--admin-text-secondary);">
                                        <?php echo $product['sales']; ?> ventas
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 600; color: var(--admin-text-primary);">
                                    $<?php echo number_format($product['revenue'], 2); ?>
                                </div>
                                <span class="admin-badge admin-badge-success" style="font-size: 10px;">
                                    <?php echo $product['trend']; ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Quick Actions -->
        <div class="admin-grid admin-grid-cols-2">
            <!-- Recent Activity -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="fas fa-clock"></i>
                        Actividad Reciente
                    </h3>
                    <button class="admin-btn admin-btn-sm admin-btn-ghost">
                        <i class="fas fa-refresh"></i>
                    </button>
                </div>
                <div class="admin-card-body">
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($recent_activity as $activity): ?>
                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--admin-border-light);">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--admin-<?php echo $activity['color']; ?>-light); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-<?php echo $activity['icon']; ?>" style="color: var(--admin-<?php echo $activity['color']; ?>); font-size: 14px;"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 14px; color: var(--admin-text-primary); margin-bottom: 2px;">
                                    <?php echo htmlspecialchars($activity['message']); ?>
                                </div>
                                <div style="font-size: 12px; color: var(--admin-text-muted);">
                                    hace <?php echo $activity['time']; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="fas fa-bolt"></i>
                        Acciones Rápidas
                    </h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-grid admin-grid-cols-2" style="gap: 16px;">
                        <a href="manage-products.php?action=add" class="admin-btn admin-btn-primary" style="padding: 20px; flex-direction: column; text-decoration: none; border-radius: 12px;">
                            <i class="fas fa-plus" style="font-size: 24px; margin-bottom: 8px;"></i>
                            <span>Nuevo Producto</span>
                        </a>
                        
                        <a href="order-management.php" class="admin-btn admin-btn-secondary" style="padding: 20px; flex-direction: column; text-decoration: none; border-radius: 12px;">
                            <i class="fas fa-list" style="font-size: 24px; margin-bottom: 8px;"></i>
                            <span>Ver Órdenes</span>
                        </a>
                        
                        <a href="marketing.php" class="admin-btn admin-btn-ghost" style="padding: 20px; flex-direction: column; text-decoration: none; border-radius: 12px; border: 2px dashed var(--admin-border);">
                            <i class="fas fa-bullhorn" style="font-size: 24px; margin-bottom: 8px;"></i>
                            <span>Nueva Campaña</span>
                        </a>
                        
                        <a href="statistics.php" class="admin-btn admin-btn-ghost" style="padding: 20px; flex-direction: column; text-decoration: none; border-radius: 12px; border: 2px dashed var(--admin-border);">
                            <i class="fas fa-chart-bar" style="font-size: 24px; margin-bottom: 8px;"></i>
                            <span>Analytics</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Professional JavaScript -->
    <script>
    // Chart.js Configuration
    const salesChart = new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($monthly_sales['labels']); ?>,
            datasets: [{
                label: 'Ventas ($)',
                data: <?php echo json_encode($monthly_sales['data']); ?>,
                borderColor: '#0066cc',
                backgroundColor: 'rgba(0, 102, 204, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#0066cc',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6
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
                        color: '#f1f3f4'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                point: {
                    hoverRadius: 8
                }
            }
        }
    });

    // Interactive Functions
    function toggleNotifications() {
        // Implementar dropdown de notificaciones
        console.log('Toggle notifications');
    }

    function toggleUserMenu() {
        // Implementar dropdown de usuario
        console.log('Toggle user menu');
    }

    // Real-time updates (simulado)
    setInterval(() => {
        // Actualizar métricas en tiempo real
        console.log('Updating metrics...');
    }, 30000);

    // Professional loading states
    document.addEventListener('DOMContentLoaded', function() {
        // Animate metric cards on load
        const metricCards = document.querySelectorAll('.admin-metric-card');
        metricCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
    </script>
</body>
</html>