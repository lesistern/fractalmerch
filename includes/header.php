<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Meta descripción y SEO -->
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'FractalMerch - Personalización de remeras y productos únicos. Diseña, crea y obtén productos personalizados de alta calidad.'; ?>">
    <meta name="keywords" content="personalización remeras, diseño, sublimación, productos personalizados, argentina, fractal merch">
    <meta name="author" content="FractalMerch">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo isset($page_description) ? $page_description : 'Personalización de remeras y productos únicos. Diseña, crea y obtén productos personalizados de alta calidad.'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>assets/images/Fractal_Merch_Header.png">
    <meta property="og:site_name" content="FractalMerch">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?>">
    <meta name="twitter:description" content="<?php echo isset($page_description) ? $page_description : 'Personalización de remeras y productos únicos.'; ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>assets/images/Fractal_Merch_Header.png">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/icon.ico">
    <link rel="icon" type="image/png" href="assets/images/icon.png">
    <link rel="apple-touch-icon" href="assets/images/icon.png">
    
    <!-- Critical CSS inline para FCP optimizado -->
    <style><?php 
        // Forzar recarga del critical.css para debugging
        $criticalCss = file_get_contents(__DIR__ . '/../assets/css/critical.css');
        echo $criticalCss;
    ?></style>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="assets/css/style.css?v=<?php echo time(); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="assets/images/centro1.png" as="image">
    <link rel="preload" href="assets/images/Fractal Background Light 2.png" as="image">
    
    <!-- Fallback para CSS no crítico -->
    <noscript>
        <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </noscript>
    <script>
        // Aplica el tema inmediatamente para evitar el parpadeo (FOUC)
        (function() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            if (isDarkMode) {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    
    <!-- Performance Optimizer -->
    <script src="assets/js/performance-optimizer.js?v=<?php echo time(); ?>"></script>
    
    <!-- Heatmap Analytics -->
    <script src="assets/js/heatmap-analytics.js?v=<?php echo time(); ?>"></script>
    
    <!-- Fase 4: Advanced Features -->
    <script src="assets/js/advanced-personalization.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/ab-testing.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/email-marketing.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/push-notifications.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/pwa-manager.js?v=<?php echo time(); ?>"></script>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- PWA Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="FractalMerch">
    
    <!-- PWA Icons -->
    <link rel="apple-touch-icon" href="assets/images/icon-152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/icon-180.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/icon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/icon-16.png">
</head>
<body class="">
    <nav class="navbar">
        <div class="nav-container">
            <!-- Búsqueda expandible - Izquierda -->
            <div class="search-container" id="search-container">
                <button class="search-btn" onclick="toggleSearchExpansion()" id="search-btn">
                    <i class="fas fa-search"></i>
                </button>
                <div class="search-input-wrapper" id="search-input-wrapper">
                    <input type="text" class="search-input" placeholder="¿Qué deseas buscar?" id="search-input">
                    <button class="search-submit" onclick="performSearch()">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
                <!-- Vista previa de búsqueda -->
                <div class="search-preview" id="search-preview">
                    <div class="search-results" id="search-results">
                        <!-- Los resultados aparecerán aquí -->
                    </div>
                </div>
            </div>
            
            <!-- Logo Centrado -->
            <div class="nav-logo">
                <a href="index.php">
                    <img src="assets/images/Fractal Header Light.png" alt="FractalMerch" class="logo-light">
                    <img src="assets/images/Fractal Header Dark.png" alt="FractalMerch" class="logo-dark">
                </a>
            </div>
            
            <!-- Botón hamburguesa para móvil -->
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            
            <!-- Menú de navegación - Derecha -->
            <div class="nav-menu" id="nav-menu">
                <!-- Botón de ingresar/perfil -->
                <?php if (is_logged_in()): ?>
                    <div class="user-menu">
                        <button class="nav-btn user-btn" onclick="toggleUserMenu()">
                            <i class="fas fa-user"></i>
                            <span class="btn-text"><?php echo $_SESSION['username']; ?></span>
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
                    <a href="login.php" class="nav-btn login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Ingresar</span>
                    </a>
                <?php endif; ?>
                
                <!-- Botón de carrito -->
                <div class="cart-container">
                    <button class="nav-btn cart-btn" onclick="showCartModal()">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                    <span class="cart-badge">0</span>
                </div>

                <!-- Toggle de modo oscuro/claro -->
                <button class="nav-btn theme-toggle" onclick="toggleTheme()" id="theme-toggle">
                    <i class="fas fa-sun" id="theme-icon"></i>
                </button>
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
let searchExpanded = false;
let searchData = [
    { name: "Remera Personalizada", price: "$5.999", category: "remeras" },
    { name: "Buzo Personalizado", price: "$12.999", category: "buzos" },
    { name: "Taza Personalizada", price: "$3.499", category: "tazas" },
    { name: "Mouse Pad Personalizado", price: "$2.999", category: "mousepad" },
    { name: "Funda Personalizada", price: "$4.999", category: "fundas" },
    { name: "Almohada Personalizada", price: "$6.999", category: "almohadas" }
];

function toggleSearchExpansion() {
    const container = document.getElementById('search-container');
    const wrapper = document.getElementById('search-input-wrapper');
    const input = document.getElementById('search-input');
    const preview = document.getElementById('search-preview');
    
    searchExpanded = !searchExpanded;
    
    if (searchExpanded) {
        container.classList.add('expanded');
        wrapper.classList.add('show');
        setTimeout(() => {
            input.focus();
        }, 300);
        // Mostrar vista previa si hay texto
        if (input.value.trim()) {
            showSearchPreview(input.value);
        }
    } else {
        // Solo colapsar si no hay texto
        if (!input.value.trim()) {
            container.classList.remove('expanded');
            wrapper.classList.remove('show');
            preview.classList.remove('show');
        }
    }
}

function collapseSearch() {
    const container = document.getElementById('search-container');
    const wrapper = document.getElementById('search-input-wrapper');
    const input = document.getElementById('search-input');
    const preview = document.getElementById('search-preview');
    
    // Solo colapsar si no hay texto
    if (!input.value.trim()) {
        searchExpanded = false;
        container.classList.remove('expanded');
        wrapper.classList.remove('show');
        preview.classList.remove('show');
    }
}

function showSearchPreview(query) {
    const preview = document.getElementById('search-preview');
    const results = document.getElementById('search-results');
    
    if (!query.trim()) {
        preview.classList.remove('show');
        return;
    }
    
    // Filtrar productos
    const filteredProducts = searchData.filter(product => 
        product.name.toLowerCase().includes(query.toLowerCase()) ||
        product.category.toLowerCase().includes(query.toLowerCase())
    );
    
    if (filteredProducts.length > 0) {
        results.innerHTML = filteredProducts.map(product => `
            <div class="search-result-item" onclick="redirectToProduct('${product.name}')">
                <div class="result-info">
                    <span class="result-name">${product.name}</span>
                    <span class="result-price">${product.price}</span>
                </div>
                <button class="result-btn" onclick="event.stopPropagation(); selectProduct('${product.name}')">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        `).join('');
        preview.classList.add('show');
    } else {
        results.innerHTML = '<div class="no-results">No se encontraron productos</div>';
        preview.classList.add('show');
    }
}

function selectProduct(productName) {
    const input = document.getElementById('search-input');
    input.value = productName;
    performSearch();
}

function redirectToProduct(productName) {
    // Redirigir directamente a la página de productos con el filtro
    window.location.href = `particulares.php?search=${encodeURIComponent(productName)}`;
}

function performSearch() {
    const searchTerm = document.getElementById('search-input').value.trim();
    if (searchTerm) {
        window.location.href = `particulares.php?search=${encodeURIComponent(searchTerm)}`;
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

function toggleMobileMenu() {
    const navMenu = document.getElementById('nav-menu');
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    
    navMenu.classList.toggle('mobile-active');
    mobileToggle.classList.toggle('active');
}

function toggleTheme() {
    const html = document.documentElement;
    html.classList.toggle('dark-mode');
    const isDarkMode = html.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDarkMode);
    updateThemeIcon(isDarkMode);
    
    // Debug: verificar que el modo oscuro se aplique
    console.log('Modo oscuro:', isDarkMode);
    console.log('Clases en html:', html.className);
    console.log('Color de fondo actual:', getComputedStyle(document.body).backgroundColor);
}

function updateThemeIcon(isDarkMode) {
    const themeIcon = document.getElementById('theme-icon');
    if (isDarkMode) {
        themeIcon.className = 'fas fa-moon';
    } else {
        themeIcon.className = 'fas fa-sun';
    }
}

// Inicializar tema al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    updateThemeIcon(isDarkMode);
});

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    
    // Búsqueda con Enter
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        // Búsqueda en tiempo real
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value;
            if (query.trim()) {
                showSearchPreview(query);
            } else {
                document.getElementById('search-preview').classList.remove('show');
            }
        });
    }
});

// Cerrar dropdowns al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-menu')) {
        document.querySelector('.user-dropdown')?.classList.remove('show');
    }
    if (!e.target.closest('.cart-container')) {
        document.querySelector('.cart-dropdown')?.classList.remove('show');
    }
    if (!e.target.closest('.nav-menu') && !e.target.closest('.mobile-menu-toggle')) {
        document.querySelector('.nav-menu')?.classList.remove('mobile-active');
        document.querySelector('.mobile-menu-toggle')?.classList.remove('active');
    }
    // Colapsar búsqueda si se hace click fuera
    if (!e.target.closest('.search-container')) {
        collapseSearch();
    }
});
</script>

<!-- Modal del Carrito - Global -->
<div id="cartModal" class="cart-modal" style="display: none;">
    <div class="cart-modal-content">
        <div class="cart-modal-header">
            <h2><i class="fas fa-shopping-cart"></i> Tu Carrito</h2>
            <button class="cart-modal-close" onclick="closeCartModal()">&times;</button>
        </div>
        <div class="cart-modal-body" id="cartModalBody">
            <!-- El contenido se llenará dinámicamente -->
        </div>
    </div>
</div>

<!-- Modal de checkout removido - ahora usa página independiente checkout.php -->

<!-- Enhanced Cart JS - Global -->
<script src="assets/js/enhanced-cart.js?v=<?php echo time(); ?>" onload="console.log('enhanced-cart.js loaded')" onerror="console.error('Error loading enhanced-cart.js')"></script>
<script>
// Inicializar carrito global
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco para asegurar que el script se haya cargado completamente
    setTimeout(function() {
        if (typeof EnhancedCart !== 'undefined') {
            window.cart = new EnhancedCart();
        }
    }, 100);
});

// Funciones globales para el carrito
function showCartModal() {
    if (window.cart && typeof window.cart.showCartModal === 'function') {
        window.cart.showCartModal();
    } else {
        // Fallback: intentar inicializar el carrito si no está disponible
        if (typeof EnhancedCart !== 'undefined' && !window.cart) {
            window.cart = new EnhancedCart();
            if (window.cart && typeof window.cart.showCartModal === 'function') {
                window.cart.showCartModal();
            }
        }
    }
}

// Verificar estado del carrito
function checkCartStatus() {
    console.log('=== CART STATUS ===');
    console.log('EnhancedCart class available:', typeof EnhancedCart !== 'undefined');
    console.log('window.cart instance:', !!window.cart);
    if (window.cart) {
        console.log('Cart items count:', window.cart.items.length);
        console.log('Cart methods available:', {
            showCartModal: typeof window.cart.showCartModal === 'function',
            addProduct: typeof window.cart.addProduct === 'function',
            clearCart: typeof window.cart.clearCart === 'function'
        });
    }
    console.log('==================');
}

function closeCartModal() {
    if (window.cart) {
        window.cart.closeCartModal();
    }
}

function closeCheckoutModal() {
    // Ya no se usa modal, redirige a página independiente
    window.location.href = 'checkout.php';
}
</script>