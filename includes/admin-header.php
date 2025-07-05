<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin Panel - FractalMerch'; ?></title>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    
    <!-- Admin-specific CSS -->
    <link rel="stylesheet" href="../assets/css/admin-notifications.css?v=<?php echo time(); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/icon.ico">
    <link rel="icon" type="image/png" href="../assets/images/icon.png">
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header-top">
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
                
                <div class="admin-notifications">
                    <button class="admin-notifications-btn" id="notifications-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span>
                    </button>
                </div>
                
                <div class="admin-user-menu">
                    <button class="admin-user-btn" id="user-menu-btn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['username'] ?? 'Admin'; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <div class="admin-user-dropdown" id="user-dropdown">
                        <a href="profile.php"><i class="fas fa-user"></i> Mi Perfil</a>
                        <a href="settings.php"><i class="fas fa-cog"></i> Configuración</a>
                        <div class="dropdown-divider"></div>
                        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Sitio</a>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Admin Layout -->
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php include 'admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-content">

<style>
/* Admin Header Styles */
.admin-body {
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #f8f9fa;
    color: #2c3e50;
}

.admin-header-top {
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

.admin-notifications {
    position: relative;
}

.admin-notifications-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.2s ease;
    position: relative;
}

.admin-notifications-btn:hover {
    background: #f8f9fa;
}

.admin-notifications-btn i {
    font-size: 18px;
    color: #6c757d;
}

.notification-count {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 10px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

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

.admin-user-btn i.fa-chevron-down {
    font-size: 12px;
    transition: transform 0.2s ease;
}

.admin-user-btn.active i.fa-chevron-down {
    transform: rotate(180deg);
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

.admin-user-dropdown a:first-child {
    border-radius: 8px 8px 0 0;
}

.admin-user-dropdown a:last-child {
    border-radius: 0 0 8px 8px;
}

.dropdown-divider {
    height: 1px;
    background: #e9ecef;
    margin: 4px 0;
}

/* Admin Layout */
.admin-layout {
    display: flex;
    margin-top: 60px;
    min-height: calc(100vh - 60px);
}

.admin-main {
    flex: 1;
    margin-left: 200px;
    transition: margin-left 0.3s ease;
}

.admin-content {
    padding: 30px;
    max-width: 1200px;
}

/* Admin Container */
.admin-container {
    background: transparent;
}

.admin-header {
    margin-bottom: 30px;
}

.admin-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-header h1 i {
    font-size: 32px;
    color: #007bff;
}

.admin-header p {
    color: #6c757d;
    margin: 0 0 20px 0;
    font-size: 16px;
}

.header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

/* Summary Cards */
.summary-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.card-icon {
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

.card-content {
    flex: 1;
}

.card-content h3 {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 5px 0;
    line-height: 1;
}

.card-content p {
    color: #6c757d;
    margin: 0 0 8px 0;
    font-size: 14px;
    font-weight: 500;
}

.trend {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
}

.trend.positive { 
    background: rgba(40, 167, 69, 0.1); 
    color: #28a745; 
}

.trend.negative { 
    background: rgba(220, 53, 69, 0.1); 
    color: #dc3545; 
}

.trend.warning { 
    background: rgba(255, 193, 7, 0.1); 
    color: #ffc107; 
}

.trend.critical { 
    background: rgba(220, 53, 69, 0.1); 
    color: #dc3545; 
}

/* Controls */
.control-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 150px;
}

.control-group label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.control-group input,
.control-group select {
    padding: 8px 12px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.control-group input:focus,
.control-group select:focus {
    outline: none;
    border-color: #007bff;
}

/* Tabs */
.tab-buttons {
    display: flex;
    background: white;
    border-radius: 8px 8px 0 0;
    border-bottom: 1px solid #e9ecef;
    overflow-x: auto;
}

.tab-btn {
    flex: 1;
    padding: 15px 20px;
    border: none;
    background: transparent;
    color: #6c757d;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    font-size: 14px;
}

.tab-btn:hover {
    background: #f8f9fa;
    color: #495057;
}

.tab-btn.active {
    background: transparent;
    color: #007bff;
    border-bottom: 3px solid #007bff;
    margin-bottom: -1px;
}

.tab-content {
    display: none;
    background: white;
    border-radius: 0 0 8px 8px;
    min-height: 400px;
}

.tab-content.active {
    display: block;
}

/* Responsive design */
@media (max-width: 768px) {
    .admin-header-content {
        padding: 0 15px;
    }
    
    .admin-search input {
        width: 180px;
    }
    
    .admin-search input:focus {
        width: 220px;
    }
    
    .admin-main {
        margin-left: 0;
    }
    
    .admin-content {
        padding: 20px 15px;
    }
    
    .header-actions {
        flex-direction: column;
    }
    
    .admin-header h1 {
        font-size: 24px;
    }
    
    .summary-card {
        padding: 20px;
    }
    
    .card-content h3 {
        font-size: 24px;
    }
}

@media (max-width: 480px) {
    .admin-user-btn span {
        display: none;
    }
    
    .admin-search {
        display: none;
    }
}
</style>

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
            userMenuBtn.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            userDropdown.classList.remove('show');
            userMenuBtn.classList.remove('active');
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
    
    // Notifications (placeholder)
    const notificationsBtn = document.getElementById('notifications-btn');
    if (notificationsBtn) {
        notificationsBtn.addEventListener('click', function() {
            // Implement notifications dropdown
            console.log('Show notifications');
        });
    }
});
</script>