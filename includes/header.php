<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</head>
<body class="">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">Sublime</a>
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
                <?php if (is_logged_in()): ?>
                    <div class="user-menu">
                        <button class="user-btn" onclick="toggleUserMenu()">
                            <i class="fas fa-user"></i>
                            <span><?php echo $_SESSION['username']; ?></span>
                        </button>
                        <div class="user-dropdown">
                            <a href="profile.php"><i class="fas fa-user-circle"></i> Mi Perfil</a>
                            <a href="create-post.php"><i class="fas fa-edit"></i> Escribir</a>
                            <?php if (is_admin() || is_moderator()): ?>
                                <a href="admin/dashboard.php"><i class="fas fa-cog"></i> Admin</a>
                            <?php endif; ?>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Ingresar</span>
                    </a>
                <?php endif; ?>
                
                <!-- Botón de carrito -->
                <div class="cart-container">
                    <button class="cart-btn" onclick="toggleCart()">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge">0</span>
                    </button>
                    <div class="cart-dropdown">
                        <div class="cart-header">
                            <h4>Carrito de compras</h4>
                        </div>
                        <div class="cart-items">
                            <p class="cart-empty">Tu carrito está vacío</p>
                        </div>
                        <div class="cart-footer">
                            <button class="btn btn-primary">Ver carrito</button>
                        </div>
                    </div>
                </div>

                <!-- Toggle de modo oscuro/claro -->
                <div class="theme-switch-wrapper">
                    <label class="theme-switch" for="checkbox">
                        <input type="checkbox" id="checkbox" />
                        <div class="slider round">
                            <span class="icon-container">
                                <i class="fas fa-sun theme-icon sun-icon"></i>
                                <i class="fas fa-moon theme-icon moon-icon"></i>
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <?php
        $messages = get_flash_messages();
        foreach ($messages as $message):
        ?>
            <div class="alert alert-<?php echo $message['type']; ?>">
                <?php echo $message['message']; ?>
            </div>
        <?php endforeach; ?>

<script>
// Funcionalidades del header
function toggleSearch() {
    const container = document.querySelector('.search-input-container');
    const input = document.querySelector('.search-input');
    
    container.classList.toggle('expanded');
    
    if (container.classList.contains('expanded')) {
        setTimeout(() => input.focus(), 300);
    }
}

function performSearch() {
    const searchTerm = document.querySelector('.search-input').value.trim();
    if (searchTerm) {
        window.location.href = `index.php?search=${encodeURIComponent(searchTerm)}`;
    }
}

function toggleUserMenu() {
    const dropdown = document.querySelector('.user-dropdown');
    dropdown.classList.toggle('show');
}

function toggleCart() {
    const dropdown = document.querySelector('.cart-dropdown');
    dropdown.classList.toggle('show');
}

// Cerrar dropdowns al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-menu')) {
        document.querySelector('.user-dropdown')?.classList.remove('show');
    }
    if (!e.target.closest('.cart-container')) {
        document.querySelector('.cart-dropdown')?.classList.remove('show');
    }
    if (!e.target.closest('.search-container')) {
        document.querySelector('.search-input-container')?.classList.remove('expanded');
    }
});

// Búsqueda con Enter
document.querySelector('.search-input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        performSearch();
    }
});
</script>