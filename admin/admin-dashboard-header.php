<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>
</head>
<body class="admin-page admin-body">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="../index.php">Sublime</a>
            </div>
            <div class="nav-menu admin-nav-menu">
                <!-- Botón de ingresar/perfil -->
                <div class="user-container">
                    <?php if (is_logged_in()): ?>
                        <div class="user-dropdown">
                            <button class="nav-btn user-btn" onclick="toggleUserMenu()">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="user-menu">
                                <a href="../profile.php"><i class="fas fa-user"></i> Mi Perfil</a>
                                <a href="../create-post.php"><i class="fas fa-edit"></i> Escribir</a>
                                <?php if (is_admin()): ?>
                                    <a href="dashboard.php"><i class="fas fa-cog"></i> Admin</a>
                                <?php endif; ?>
                                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="../login.php" class="nav-btn login-btn">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Ingresar</span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Toggle de modo oscuro/claro -->
                <button class="nav-btn theme-toggle" onclick="toggleAdminTheme()" id="theme-toggle">
                    <i class="fas fa-sun" id="theme-icon"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Admin Navigation Breadcrumb -->
    <div class="admin-breadcrumb-nav">
        <div class="container">
            <nav class="breadcrumb">
                <a href="../index.php" class="breadcrumb-item">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">
                    <i class="fas fa-tachometer-alt"></i> Panel de Administración
                </span>
            </nav>
        </div>
    </div>

    <!-- Mostrar mensajes flash -->
    <?php 
    $flash_message = get_flash_message();
    if ($flash_message): 
    ?>
        <div class="flash-messages-container">
            <div class="container">
                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible">
                    <?php
                    $icon = 'info-circle';
                    switch($flash_message['type']) {
                        case 'success': $icon = 'check-circle'; break;
                        case 'error': $icon = 'exclamation-circle'; break;
                        case 'warning': $icon = 'exclamation-triangle'; break;
                    }
                    ?>
                    <i class="fas fa-<?php echo $icon; ?>"></i> 
                    <?php echo htmlspecialchars($flash_message['message']); ?>
                    <button type="button" class="alert-close" onclick="this.parentElement.parentElement.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

<style>
/* Estilos específicos para el admin dashboard */
.admin-body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    transition: all 0.3s ease;
}

.admin-body.dark-mode {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.admin-body.dark-mode .admin-sidebar,
.admin-body.dark-mode .admin-main {
    background: rgba(20, 20, 30, 0.95);
    border: 1px solid rgba(255,255,255,0.1);
    color: #e2e8f0;
}

.admin-body.dark-mode .admin-sidebar h3,
.admin-body.dark-mode .admin-main h2 {
    color: #e2e8f0;
}

.admin-body.dark-mode .admin-menu a {
    color: #cbd5e0;
}

.admin-body.dark-mode .admin-menu a:hover {
    background: rgba(102, 126, 234, 0.2);
    color: #a3bffa;
}

.admin-body.dark-mode .stat-card,
.admin-body.dark-mode .admin-section {
    background: rgba(30, 30, 40, 0.8);
    border: 1px solid rgba(255,255,255,0.1);
    color: #e2e8f0;
}

.admin-body.dark-mode .admin-table th {
    background: rgba(40, 40, 50, 0.8);
    color: #e2e8f0;
}

.admin-body.dark-mode .admin-table tr:hover {
    background: rgba(40, 40, 50, 0.5);
}

.admin-body.dark-mode .admin-table a {
    color: #a3bffa;
}

.admin-body.dark-mode .admin-table td {
    border-bottom-color: rgba(255,255,255,0.1);
}

.admin-body.dark-mode .admin-section h3 {
    border-bottom-color: rgba(255,255,255,0.2);
}

.admin-body.dark-mode .admin-sidebar h3 {
    border-bottom-color: rgba(255,255,255,0.2);
}

.admin-body.dark-mode .navbar {
    background: rgba(20, 20, 30, 0.3);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.admin-body.dark-mode .admin-breadcrumb-nav {
    background: rgba(20, 20, 30, 0.4);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.nav-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.7rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.nav-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    transform: rotate(15deg) scale(1.1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.admin-body.dark-mode .nav-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.admin-body.dark-mode .nav-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}

.admin-nav-menu {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.theme-toggle {
    padding: 0.6rem;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    justify-content: center;
}

.user-btn {
    padding: 0.5rem 0.8rem;
}

.user-btn span {
    font-size: 0.85rem;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Header Admin Ultra Compacto */
.admin-body .navbar {
    padding: 0.15rem 0; /* Ultra compacto */
}

.admin-body .nav-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    padding: 1px 1rem; /* Solo 1px arriba y abajo */
    height: 36px; /* Altura mínima */
    position: relative;
    gap: 2rem;
    overflow: visible;
}

/* Eliminar breadcrumb completamente */
.admin-breadcrumb-nav {
    display: none; /* Ocultar breadcrumb para más espacio */
}

.admin-breadcrumb-nav {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.2);
    padding: 1rem 0;
    margin-bottom: 2rem;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: white;
}

.breadcrumb-item {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb-item:hover {
    color: white;
}

.breadcrumb-separator {
    color: rgba(255,255,255,0.5);
}

.breadcrumb-current {
    color: white;
    font-weight: 500;
}

.flash-messages-container {
    margin-bottom: 2rem;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.3);
    color: #065f46;
    backdrop-filter: blur(10px);
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
    color: #991b1b;
    backdrop-filter: blur(10px);
}

.alert-warning {
    background: rgba(245, 158, 11, 0.1);
    border-color: rgba(245, 158, 11, 0.3);
    color: #92400e;
    backdrop-filter: blur(10px);
}

.alert-dismissible {
    padding-right: 3rem;
}

.alert-close {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.alert-close:hover {
    opacity: 1;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Estilos para el dashboard admin */
.admin-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem 2rem 1rem;
    min-height: calc(100vh - 200px);
}

.admin-sidebar {
    background: rgba(255,255,255,0.95);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.admin-sidebar h3 {
    color: #1f2937;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    font-weight: 600;
    text-align: center;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
}

.admin-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.admin-menu li {
    margin-bottom: 0.5rem;
}

.admin-menu a {
    display: block;
    padding: 0.75rem 1rem;
    color: #374151;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.admin-menu a:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    transform: translateX(5px);
}

.admin-menu a.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.admin-main {
    background: rgba(255,255,255,0.95);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
}

.admin-main h2 {
    color: #1f2937;
    margin-bottom: 2rem;
    font-size: 2rem;
    font-weight: 600;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.stat-card.alert {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.stat-card h3 {
    font-size: 2.5rem;
    font-weight: bold;
    color: #667eea;
    margin: 0 0 0.5rem 0;
}

.stat-card.alert h3 {
    color: #92400e;
}

.stat-card p {
    color: #6b7280;
    margin: 0;
    font-weight: 500;
}

.admin-sections {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.admin-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border: 1px solid #e5e7eb;
}

.admin-section h3 {
    color: #1f2937;
    margin-bottom: 1rem;
    font-size: 1.25rem;
    font-weight: 600;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e5e7eb;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.admin-table th,
.admin-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.admin-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.admin-table tr:hover {
    background: #f9fafb;
}

.admin-table a {
    color: #667eea;
    text-decoration: none;
    margin-right: 0.5rem;
    font-weight: 500;
}

.admin-table a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .admin-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .admin-sidebar {
        position: relative;
        top: 0;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .admin-sections {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .admin-table {
        font-size: 0.875rem;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 0.5rem;
    }
}
</style>

<script>
// Scripts del header admin
function toggleUserMenu() {
    const dropdown = document.querySelector('.user-dropdown');
    dropdown.classList.toggle('active');
}

// Cerrar dropdowns al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-dropdown')) {
        document.querySelector('.user-dropdown')?.classList.remove('active');
    }
});

// Función para alternar tema en admin
function toggleAdminTheme() {
    const html = document.documentElement;
    const body = document.body;
    const themeIcon = document.getElementById('theme-icon');
    const isDarkMode = html.classList.contains('dark-mode');
    
    if (isDarkMode) {
        // Cambiar a modo claro
        html.classList.remove('dark-mode');
        body.classList.remove('dark-mode');
        themeIcon.className = 'fas fa-sun';
        localStorage.setItem('adminDarkMode', 'false');
        localStorage.setItem('darkMode', 'false');
    } else {
        // Cambiar a modo oscuro
        html.classList.add('dark-mode');
        body.classList.add('dark-mode');
        themeIcon.className = 'fas fa-moon';
        localStorage.setItem('adminDarkMode', 'true');
        localStorage.setItem('darkMode', 'true');
    }
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tema
    const html = document.documentElement;
    const body = document.body;
    const themeIcon = document.getElementById('theme-icon');
    const savedTheme = localStorage.getItem('adminDarkMode') || localStorage.getItem('darkMode');
    
    if (savedTheme === 'true') {
        html.classList.add('dark-mode');
        body.classList.add('dark-mode');
        if (themeIcon) themeIcon.className = 'fas fa-moon';
    } else {
        html.classList.remove('dark-mode');
        body.classList.remove('dark-mode');
        if (themeIcon) themeIcon.className = 'fas fa-sun';
    }
    
    // Backup sidebar initialization in case admin-functions.js doesn't load
    setTimeout(function() {
        if (!window.AdminFunctions) {
            console.log('Initializing fallback sidebar functionality');
            
            // Remove existing listeners first
            document.querySelectorAll('.nav-expandable').forEach(navExpandable => {
                const newNavExpandable = navExpandable.cloneNode(true);
                navExpandable.parentNode.replaceChild(newNavExpandable, navExpandable);
            });
            
            // Add fresh event listeners
            document.querySelectorAll('.nav-expandable').forEach(navExpandable => {
                navExpandable.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const parent = this.closest('.nav-item-expandable');
                    if (parent) {
                        const menuName = parent.querySelector('span')?.textContent.trim() || 'Unknown';
                        const wasActive = parent.classList.contains('active');
                        
                        parent.classList.toggle('active');
                        console.log(`Sidebar menu "${menuName}" ${wasActive ? 'collapsed' : 'expanded'} (fallback)`);
                        
                        // Force CSS recalculation
                        const submenu = parent.querySelector('.nav-submenu');
                        if (submenu) {
                            submenu.style.display = 'block';
                            void submenu.offsetHeight; // Trigger reflow
                            submenu.style.display = '';
                        }
                    }
                });
            });
            
            console.log('✅ Fallback sidebar initialization complete');
        } else {
            console.log('AdminFunctions loaded, skipping fallback');
        }
    }, 200);
});
</script>