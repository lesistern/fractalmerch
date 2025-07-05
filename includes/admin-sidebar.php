<!-- Admin Sidebar -->
<aside class="admin-sidebar">
    <nav class="admin-nav">
        <div class="nav-section">
            <h4><i class="fas fa-tachometer-alt"></i> Panel Principal</h4>
            <ul class="nav-list">
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <h4><i class="fas fa-shopping-cart"></i> E-commerce</h4>
            <ul class="nav-list">
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'inventory-management.php' ? 'active' : ''; ?>">
                    <a href="inventory-management.php">
                        <i class="fas fa-boxes"></i>
                        <span>Inventario</span>
                        <?php if (isset($stats['low_stock_items']) && $stats['low_stock_items'] > 0): ?>
                            <span class="nav-badge warning"><?php echo $stats['low_stock_items']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'order-management.php' ? 'active' : ''; ?>">
                    <a href="order-management.php">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Órdenes</span>
                        <?php if (isset($stats['pending_orders']) && $stats['pending_orders'] > 0): ?>
                            <span class="nav-badge info"><?php echo $stats['pending_orders']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'production-workflow.php' ? 'active' : ''; ?>">
                    <a href="production-workflow.php">
                        <i class="fas fa-cogs"></i>
                        <span>Producción</span>
                        <span class="nav-badge success">5</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'supplier-management.php' ? 'active' : ''; ?>">
                    <a href="supplier-management.php">
                        <i class="fas fa-truck"></i>
                        <span>Proveedores</span>
                        <span class="nav-badge success">4</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-products.php' ? 'active' : ''; ?>">
                    <a href="manage-products.php">
                        <i class="fas fa-box"></i>
                        <span>Productos</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <h4><i class="fas fa-users"></i> Gestión de Usuarios</h4>
            <ul class="nav-list">
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-users.php' ? 'active' : ''; ?>">
                    <a href="manage-users.php">
                        <i class="fas fa-users-cog"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-comments.php' ? 'active' : ''; ?>">
                    <a href="manage-comments.php">
                        <i class="fas fa-comments"></i>
                        <span>Comentarios</span>
                        <?php if (isset($stats['pending_comments']) && $stats['pending_comments'] > 0): ?>
                            <span class="nav-badge warning"><?php echo $stats['pending_comments']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <h4><i class="fas fa-edit"></i> Contenido</h4>
            <ul class="nav-list">
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-posts.php' ? 'active' : ''; ?>">
                    <a href="manage-posts.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Posts</span>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-categories.php' ? 'active' : ''; ?>">
                    <a href="manage-categories.php">
                        <i class="fas fa-tags"></i>
                        <span>Categorías</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <h4><i class="fas fa-chart-line"></i> Analytics</h4>
            <ul class="nav-list">
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'heatmap-analytics.php' ? 'active' : ''; ?>">
                    <a href="heatmap-analytics.php">
                        <i class="fas fa-fire"></i>
                        <span>Heatmaps</span>
                        <span class="nav-badge info">Live</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Ver Sitio</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- System Status -->
    <div class="sidebar-footer">
        <div class="system-status">
            <div class="status-item">
                <div class="status-indicator online"></div>
                <span>Sistema Online</span>
            </div>
            <div class="status-item">
                <div class="status-indicator"></div>
                <span>Última sincronización: 2 min</span>
            </div>
        </div>
    </div>
</aside>

<style>
/* Admin Sidebar Styles */
.admin-sidebar {
    width: 200px;
    background: white;
    border-right: 1px solid #e9ecef;
    position: fixed;
    left: 0;
    top: 60px;
    bottom: 0;
    overflow-y: auto;
    z-index: 999;
    display: flex;
    flex-direction: column;
}

.admin-nav {
    flex: 1;
    padding: 20px 0;
}

.nav-section {
    margin-bottom: 25px;
}

.nav-section h4 {
    padding: 0 20px 10px 20px;
    margin: 0 0 10px 0;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid #f1f3f4;
}

.nav-section h4 i {
    font-size: 12px;
}

.nav-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    margin: 0;
}

.nav-item a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: #495057;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
}

.nav-item a:hover {
    background: #f8f9fa;
    color: #007bff;
    text-decoration: none;
}

.nav-item.active a {
    background: linear-gradient(90deg, rgba(0, 123, 255, 0.1) 0%, transparent 100%);
    color: #007bff;
    border-right: 3px solid #007bff;
}

.nav-item a i {
    width: 16px;
    text-align: center;
    font-size: 14px;
}

.nav-item a span {
    flex: 1;
}

.nav-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 10px;
    color: white;
    min-width: 18px;
    text-align: center;
    line-height: 1.2;
}

.nav-badge.success { background: #28a745; }
.nav-badge.info { background: #17a2b8; }
.nav-badge.warning { background: #ffc107; color: #212529; }
.nav-badge.danger { background: #dc3545; }

/* Sidebar Footer */
.sidebar-footer {
    padding: 15px 20px;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}

.system-status {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: #6c757d;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #dc3545;
    animation: pulse 2s infinite;
}

.status-indicator.online {
    background: #28a745;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

/* Responsive behavior */
@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .admin-sidebar.open {
        transform: translateX(0);
    }
    
    .admin-main {
        margin-left: 0;
    }
}

/* Scrollbar styling */
.admin-sidebar::-webkit-scrollbar {
    width: 4px;
}

.admin-sidebar::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.admin-sidebar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.admin-sidebar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>