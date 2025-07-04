<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="light-mode admin-body">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="../index.php">Sublime</a>
            </div>
            <div class="nav-menu">
                <!-- Búsqueda expandible -->
                <div class="search-container">
                    <button class="search-btn" onclick="toggleSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                    <div class="search-input-container">
                        <input type="text" class="search-input" placeholder="¿Qué deseas buscar?">
                        <button class="search-submit" onclick="performSearch()">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Botón de ingresar/perfil -->
                <div class="user-container">
                    <?php if (is_logged_in()): ?>
                        <div class="user-dropdown">
                            <button class="user-btn" onclick="toggleUserMenu()">
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
                        <a href="../login.php" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Ingresar</span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Carrito -->
                <div class="cart-container">
                    <button class="cart-btn" onclick="toggleCart()">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge" id="cart-badge">0</span>
                    </button>
                    
                    <!-- Dropdown del carrito -->
                    <div class="cart-dropdown" id="cart-dropdown">
                        <div class="cart-header">
                            <h3>Carrito de compras</h3>
                        </div>
                        <div class="cart-items" id="cart-items">
                            <p>Tu carrito está vacío</p>
                        </div>
                        <div class="cart-footer">
                            <button class="btn-view-cart" onclick="viewCart()">Ver carrito</button>
                        </div>
                    </div>
                </div>
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
                <a href="dashboard.php" class="breadcrumb-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">
                    <i class="fas fa-magic"></i> Generador de Imágenes
                </span>
            </nav>
        </div>
    </div>

    <main class="main-content admin-main">
        <div class="container">
            
            <!-- Mostrar mensajes flash -->
            <?php 
            $flash_messages = get_flash_messages();
            foreach ($flash_messages as $flash): 
            ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible">
                    <?php
                    $icon = 'info-circle';
                    switch($flash['type']) {
                        case 'success': $icon = 'check-circle'; break;
                        case 'error': $icon = 'exclamation-circle'; break;
                        case 'warning': $icon = 'exclamation-triangle'; break;
                    }
                    ?>
                    <i class="fas fa-<?php echo $icon; ?>"></i> 
                    <?php echo htmlspecialchars($flash['message']); ?>
                    <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endforeach; ?>

<style>
/* Estilos específicos para el admin */
.admin-body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
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

.admin-main {
    padding: 0 1rem 2rem 1rem;
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
</style>

<script>
// Scripts del header del proyecto
function toggleSearch() {
    const container = document.querySelector('.search-container');
    const input = document.querySelector('.search-input');
    
    container.classList.toggle('active');
    
    if (container.classList.contains('active')) {
        setTimeout(() => input.focus(), 300);
    }
}

function performSearch() {
    const searchTerm = document.querySelector('.search-input').value.trim();
    if (searchTerm) {
        window.location.href = `../index.php?search=${encodeURIComponent(searchTerm)}`;
    }
}

function toggleUserMenu() {
    const dropdown = document.querySelector('.user-dropdown');
    dropdown.classList.toggle('active');
}

function toggleCart() {
    const dropdown = document.getElementById('cart-dropdown');
    dropdown.classList.toggle('active');
    updateCartDisplay();
}

function viewCart() {
    window.location.href = '../particulares.php#carrito';
}

// Actualizar badge del carrito
function updateCartBadge() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const badge = document.getElementById('cart-badge');
    if (badge) {
        badge.textContent = cart.length;
        badge.style.display = cart.length > 0 ? 'flex' : 'none';
    }
}

// Actualizar display del carrito
function updateCartDisplay() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const cartItems = document.getElementById('cart-items');
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p>Tu carrito está vacío</p>';
    } else {
        let html = '';
        cart.forEach(item => {
            html += `
                <div class="cart-item">
                    <span class="item-name">${item.name}</span>
                    <span class="item-price">$${item.price.toLocaleString()}</span>
                </div>
            `;
        });
        cartItems.innerHTML = html;
    }
}

// Cerrar dropdowns al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-container')) {
        document.querySelector('.search-container').classList.remove('active');
    }
    if (!e.target.closest('.user-dropdown')) {
        document.querySelector('.user-dropdown')?.classList.remove('active');
    }
    if (!e.target.closest('.cart-container')) {
        document.getElementById('cart-dropdown')?.classList.remove('active');
    }
});

// Inicializar carrito al cargar
document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge();
});
</script>