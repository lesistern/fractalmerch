<?php
// Master Admin Header - Sistema Unificado para todas las páginas admin
// Incluye las dependencias necesarias

// Asegurar que las dependencias están cargadas
if (!function_exists('is_logged_in')) {
    require_once '../includes/functions.php';
}
if (!isset($pdo)) {
    require_once '../config/database.php';
}

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar permisos de admin
if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php');
    exit();
}

// Título por defecto si no está definido
if (!isset($pageTitle)) {
    $pageTitle = 'Admin Panel - FractalMerch';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js for analytics - Lazy loaded only when needed -->
    <?php if (in_array(basename($_SERVER['PHP_SELF']), ['dashboard.php', 'statistics.php', 'stats-payments.php', 'stats-shipping.php', 'stats-products.php', 'stats-traffic.php'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <?php endif; ?>
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    
    <!-- Admin Professional CSS -->
    <link rel="stylesheet" href="../assets/css/admin-professional.css?v=<?php echo time(); ?>">
    
    <!-- Admin CSS Unificado -->
    <link rel="stylesheet" href="../assets/css/admin-notifications.css?v=<?php echo time(); ?>">
    
    <!-- Admin Professional JavaScript -->
    <script src="assets/js/admin-professional.js?v=<?php echo time(); ?>" defer></script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/icon.ico">
    <link rel="icon" type="image/png" href="../assets/images/icon.png">
    
    <!-- Master Admin Styles -->
    <style>
    /* === ADMIN MASTER STYLES === */
    body.admin-body {
        margin: 0;
        padding: 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f8f9fa;
        color: #2c3e50;
        overflow-x: hidden;
    }

    /* Header principal */
    .admin-header-main {
        background: white;
        border-bottom: 1px solid #e9ecef;
        padding: 0 20px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        height: 60px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .admin-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 100%;
        max-width: 1400px;
        margin: 0 auto;
    }

    .admin-logo a {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: #2c3e50;
        font-weight: 700;
        font-size: 18px;
    }

    .admin-logo i {
        font-size: 24px;
        color: #007bff;
    }

    .admin-header-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    /* Search */
    .admin-search {
        position: relative;
        display: flex;
        align-items: center;
    }

    .admin-search i {
        position: absolute;
        left: 12px;
        color: #6c757d;
        z-index: 1;
    }

    .admin-search input {
        padding: 8px 12px 8px 40px;
        border: 2px solid #e9ecef;
        border-radius: 20px;
        width: 250px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .admin-search input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        width: 300px;
    }

    /* User Menu */
    .admin-user-menu {
        position: relative;
    }

    .admin-user-btn {
        background: none;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.2s ease;
        font-size: 14px;
        color: #2c3e50;
    }

    .admin-user-btn:hover {
        background: #f8f9fa;
    }

    .admin-user-btn i.fa-user-circle {
        font-size: 24px;
        color: #007bff;
    }

    .admin-user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 180px;
        z-index: 1001;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s ease;
        margin-top: 8px;
    }

    .admin-user-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .admin-user-dropdown a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        text-decoration: none;
        color: #2c3e50;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    .admin-user-dropdown a:hover {
        background: #f8f9fa;
    }

    /* Layout principal */
    .admin-layout {
        display: flex;
        margin-top: 60px;
        min-height: calc(100vh - 60px);
    }

    /* Sidebar */
    .admin-sidebar {
        width: 180px;
        background: white;
        border-right: 1px solid #e9ecef;
        position: fixed;
        left: 0;
        top: 60px;
        height: calc(100vh - 60px);
        overflow-y: auto;
        overflow-x: hidden;
        z-index: 999;
        display: flex;
        flex-direction: column;
        box-shadow: 2px 0 4px rgba(0,0,0,0.1);
    }

    .admin-nav {
        flex: 1;
        padding: 15px 0;
    }

    .nav-section {
        margin-bottom: 20px;
    }

    .nav-section h4 {
        padding: 0 15px 8px 15px;
        margin: 0 0 8px 0;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid #f1f3f4;
    }

    .nav-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .nav-item a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 15px;
        color: #495057;
        text-decoration: none;
        font-size: 13px;
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
        width: 14px;
        text-align: center;
        font-size: 12px;
        flex-shrink: 0;
    }

    .nav-badge {
        font-size: 9px;
        font-weight: 600;
        padding: 1px 4px;
        border-radius: 8px;
        color: white;
        min-width: 16px;
        text-align: center;
        line-height: 1.2;
        margin-left: auto;
    }

    .nav-badge.success { background: #28a745; }
    .nav-badge.info { background: #17a2b8; }
    .nav-badge.warning { background: #ffc107; color: #212529; }

    /* Content area */
    .admin-main {
        flex: 1;
        margin-left: 180px;
        transition: margin-left 0.3s ease;
    }

    .admin-content {
        padding: 20px;
        max-width: none;
        background: transparent;
    }

    /* Page headers unificados */
    .page-header {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #e9ecef;
    }

    .page-header h1 {
        margin: 0 0 6px 0;
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-header h1 i {
        color: #007bff;
        font-size: 26px;
    }

    .page-header p {
        margin: 0 0 15px 0;
        color: #6c757d;
        font-size: 14px;
    }

    .page-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    /* Content cards unificados */
    .content-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    .content-card h3 {
        margin: 0 0 15px 0;
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Responsive */
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
        
        .admin-content {
            padding: 15px;
        }
        
        .admin-search {
            display: none;
        }
        
        .admin-user-btn span {
            display: none;
        }
    }

    /* Compatibility fixes para páginas existentes */
    .modern-admin-container {
        display: contents;
    }

    .modern-admin-main {
        display: contents;
    }

    /* Override de estilos conflictivos */
    .admin-body .navbar {
        display: none !important;
    }

    .admin-body .nav-container {
        display: none !important;
    }

    /* Keyboard shortcuts indicator */
    .keyboard-shortcut {
        font-size: 10px;
        background: rgba(0,0,0,0.1);
        padding: 2px 4px;
        border-radius: 3px;
        margin-left: auto;
        opacity: 0.7;
    }

    /* Quick access toolbar */
    .quick-access-toolbar {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        z-index: 1000;
    }

    .quick-access-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #007bff;
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .quick-access-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0,123,255,0.4);
    }
    </style>
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header-main">
        <div class="admin-header-content">
            <div class="admin-logo">
                <a href="dashboard.php">
                    <i class="fas fa-store"></i>
                    <span>FractalMerch Admin</span>
                </a>
            </div>
            
            <div class="admin-header-actions">
                <div class="admin-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar en admin..." id="admin-search">
                </div>
                
                <div class="admin-user-menu">
                    <button class="admin-user-btn" id="user-menu-btn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['username'] ?? 'Admin'; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <div class="admin-user-dropdown" id="user-dropdown">
                        <a href="../profile.php"><i class="fas fa-user"></i> Mi Perfil</a>
                        <a href="settings.php"><i class="fas fa-cog"></i> Configuración</a>
                        <div style="height: 1px; background: #e9ecef; margin: 4px 0;"></div>
                        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Sitio</a>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Admin Layout -->
    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <nav class="admin-nav">
                <div class="nav-section">
                    <h4><i class="fas fa-tachometer-alt"></i> Panel Principal</h4>
                    <ul class="nav-list">
                        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                            <a href="dashboard.php">
                                <i class="fas fa-home"></i>
                                <span>Dashboard</span>
                                <span class="keyboard-shortcut">Alt+D</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'statistics.php' ? 'active' : ''; ?>">
                            <a href="statistics.php">
                                <i class="fas fa-chart-bar"></i>
                                <span>Estadísticas</span>
                                <span class="keyboard-shortcut">Alt+S</span>
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
                                <span class="nav-badge warning">8</span>
                                <span class="keyboard-shortcut">Alt+I</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'order-management.php' ? 'active' : ''; ?>">
                            <a href="order-management.php">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Órdenes</span>
                                <span class="nav-badge info">12</span>
                                <span class="keyboard-shortcut">Alt+O</span>
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
                        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'purchase-orders.php' ? 'active' : ''; ?>">
                            <a href="purchase-orders.php">
                                <i class="fas fa-receipt"></i>
                                <span>Órdenes Compra</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <h4><i class="fas fa-users"></i> Gestión</h4>
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
                            </a>
                        </li>
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
                    <h4><i class="fas fa-chart-line"></i> Reportes</h4>
                    <ul class="nav-list">
                        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'heatmap-analytics.php' ? 'active' : ''; ?>">
                            <a href="heatmap-analytics.php">
                                <i class="fas fa-fire"></i>
                                <span>Heatmaps</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'stats-traffic.php' ? 'active' : ''; ?>">
                            <a href="stats-traffic.php">
                                <i class="fas fa-chart-bar"></i>
                                <span>Tráfico</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'marketing-campaigns.php' ? 'active' : ''; ?>">
                            <a href="marketing-campaigns.php">
                                <i class="fas fa-bullhorn"></i>
                                <span>Marketing</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- System Status -->
            <div style="padding: 10px 15px; border-top: 1px solid #e9ecef; background: #f8f9fa;">
                <div style="display: flex; align-items: center; gap: 6px; font-size: 10px; color: #6c757d;">
                    <div style="width: 6px; height: 6px; border-radius: 50%; background: #28a745;"></div>
                    <span>Sistema Online</span>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-content">

<script>
// Admin Header JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // User menu toggle
    const userMenuBtn = document.getElementById('user-menu-btn');
    const userDropdown = document.getElementById('user-dropdown');
    
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            userDropdown.classList.remove('show');
        });
        
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Admin search functionality
    const adminSearch = document.getElementById('admin-search');
    if (adminSearch) {
        adminSearch.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            // Implement search functionality here
            console.log('Searching for:', query);
        });
    }
});
</script>