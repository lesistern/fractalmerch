<?php
// Detectar la página actual para marcar como activa
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Admin Sidebar Unificado -->
<div class="admin-sidebar">
    <!-- Header del Sidebar -->
    <div class="sidebar-header">
        <h3><i class="fas fa-store"></i> Panel Admin</h3>
    </div>
    
    <!-- Navegación del Sidebar -->
    <nav class="sidebar-nav">
        <!-- Inicio -->
        <a href="dashboard.php" class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Inicio</span>
        </a>
        
        <!-- Estadísticas -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['statistics.php', 'stats-overview.php', 'stats-payments.php', 'stats-shipping.php', 'stats-products.php', 'stats-traffic.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-chart-bar"></i>
                <span>Estadísticas</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="statistics.php" class="nav-subitem <?php echo ($current_page == 'statistics.php') ? 'active' : ''; ?>">
                    <span>Visión general</span>
                </a>
                <a href="stats-payments.php" class="nav-subitem <?php echo ($current_page == 'stats-payments.php') ? 'active' : ''; ?>">
                    <span>Pagos</span>
                </a>
                <a href="stats-shipping.php" class="nav-subitem <?php echo ($current_page == 'stats-shipping.php') ? 'active' : ''; ?>">
                    <span>Envíos</span>
                </a>
                <a href="stats-products.php" class="nav-subitem <?php echo ($current_page == 'stats-products.php') ? 'active' : ''; ?>">
                    <span>Productos</span>
                </a>
                <a href="stats-traffic.php" class="nav-subitem <?php echo ($current_page == 'stats-traffic.php') ? 'active' : ''; ?>">
                    <span>Fuente de tráfico</span>
                </a>
            </div>
        </div>
        
        <!-- Categoría: Gestión -->
        <div class="sidebar-section-header">Gestión</div>
        
        <!-- Ventas -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['sales.php', 'sales-list.php', 'purchase-orders.php', 'abandoned-carts.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-shopping-cart"></i>
                <span>Ventas</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="sales.php" class="nav-subitem <?php echo ($current_page == 'sales.php') ? 'active' : ''; ?>">
                    <span>Lista de ventas</span>
                </a>
                <a href="purchase-orders.php" class="nav-subitem <?php echo ($current_page == 'purchase-orders.php') ? 'active' : ''; ?>">
                    <span>Órdenes de compra</span>
                </a>
                <a href="abandoned-carts.php" class="nav-subitem <?php echo ($current_page == 'abandoned-carts.php') ? 'active' : ''; ?>">
                    <span>Carritos abandonados</span>
                </a>
            </div>
        </div>
        
        <!-- Productos -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['manage-products.php', 'inventory.php', 'inventory-management.php', 'manage-categories.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-box"></i>
                <span>Productos</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="manage-products.php" class="nav-subitem <?php echo ($current_page == 'manage-products.php') ? 'active' : ''; ?>">
                    <span>Lista de productos</span>
                </a>
                <a href="inventory-management.php" class="nav-subitem <?php echo ($current_page == 'inventory-management.php') ? 'active' : ''; ?>">
                    <span>Gestión de Inventario</span>
                </a>
                <a href="inventory.php" class="nav-subitem <?php echo ($current_page == 'inventory.php') ? 'active' : ''; ?>">
                    <span>Inventario Básico</span>
                </a>
                <a href="manage-categories.php" class="nav-subitem <?php echo ($current_page == 'manage-categories.php') ? 'active' : ''; ?>">
                    <span>Categorías</span>
                </a>
            </div>
        </div>
        
        <!-- Pedidos -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['order-management.php', 'orders-list.php', 'order-tracking.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-shopping-bag"></i>
                <span>Pedidos</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="order-management.php" class="nav-subitem <?php echo ($current_page == 'order-management.php') ? 'active' : ''; ?>">
                    <span>Gestión de Pedidos</span>
                </a>
                <a href="orders-list.php" class="nav-subitem <?php echo ($current_page == 'orders-list.php') ? 'active' : ''; ?>">
                    <span>Lista de Pedidos</span>
                </a>
                <a href="order-tracking.php" class="nav-subitem <?php echo ($current_page == 'order-tracking.php') ? 'active' : ''; ?>">
                    <span>Seguimiento</span>
                </a>
            </div>
        </div>
        
        <!-- Producción -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['production-workflow.php', 'production-queue.php', 'quality-control.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-cogs"></i>
                <span>Producción</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="production-workflow.php" class="nav-subitem <?php echo ($current_page == 'production-workflow.php') ? 'active' : ''; ?>">
                    <span>Workflow de Producción</span>
                </a>
                <a href="production-queue.php" class="nav-subitem <?php echo ($current_page == 'production-queue.php') ? 'active' : ''; ?>">
                    <span>Cola de Producción</span>
                </a>
                <a href="quality-control.php" class="nav-subitem <?php echo ($current_page == 'quality-control.php') ? 'active' : ''; ?>">
                    <span>Control de Calidad</span>
                </a>
            </div>
        </div>
        
        <!-- Proveedores -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['supplier-management.php', 'supplier-orders.php', 'supplier-apis.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-truck"></i>
                <span>Proveedores</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="supplier-management.php" class="nav-subitem <?php echo ($current_page == 'supplier-management.php') ? 'active' : ''; ?>">
                    <span>Gestión de Proveedores</span>
                </a>
                <a href="supplier-orders.php" class="nav-subitem <?php echo ($current_page == 'supplier-orders.php') ? 'active' : ''; ?>">
                    <span>Órdenes de Proveedores</span>
                </a>
                <a href="supplier-apis.php" class="nav-subitem <?php echo ($current_page == 'supplier-apis.php') ? 'active' : ''; ?>">
                    <span>APIs & Integración</span>
                </a>
            </div>
        </div>
        
        <!-- Chat -->
        <a href="chat.php" class="nav-item <?php echo ($current_page == 'chat.php') ? 'active' : ''; ?>">
            <i class="fas fa-comment-dots"></i>
            <span>Chat</span>
        </a>
        
        <!-- Clientes -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['manage-users.php', 'client-messages.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="manage-users.php" class="nav-subitem <?php echo ($current_page == 'manage-users.php') ? 'active' : ''; ?>">
                    <span>Lista de clientes</span>
                </a>
                <a href="client-messages.php" class="nav-subitem <?php echo ($current_page == 'client-messages.php') ? 'active' : ''; ?>">
                    <span>Mensajes</span>
                </a>
            </div>
        </div>
        
        <!-- Descuentos -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['discounts.php', 'coupons.php', 'promotions.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-percent"></i>
                <span>Descuentos</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="coupons.php" class="nav-subitem <?php echo ($current_page == 'coupons.php') ? 'active' : ''; ?>">
                    <span>Cupones</span>
                </a>
                <a href="promotions.php" class="nav-subitem <?php echo ($current_page == 'promotions.php') ? 'active' : ''; ?>">
                    <span>Promociones</span>
                </a>
            </div>
        </div>
        
        <!-- Marketing -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['marketing.php', 'email-marketing.php', 'email-campaign-builder.php', 'social-media.php', 'google-ads.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-bullhorn"></i>
                <span>Marketing</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="marketing.php" class="nav-subitem <?php echo ($current_page == 'marketing.php') ? 'active' : ''; ?>">
                    <span>Centro de marketing</span>
                </a>
                <a href="email-marketing.php" class="nav-subitem <?php echo ($current_page == 'email-marketing.php' || $current_page == 'email-campaign-builder.php') ? 'active' : ''; ?>">
                    <span>Email marketing</span>
                </a>
                <a href="social-media.php" class="nav-subitem <?php echo ($current_page == 'social-media.php') ? 'active' : ''; ?>">
                    <span>Redes sociales</span>
                </a>
                <a href="google-ads.php" class="nav-subitem <?php echo ($current_page == 'google-ads.php') ? 'active' : ''; ?>">
                    <span>Google Ads</span>
                </a>
            </div>
        </div>
        
        <!-- Categoría: Canales de venta -->
        <div class="sidebar-section-header">Canales de venta</div>
        
        <!-- Tienda online -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['online-store.php', 'store-design.php', 'store-pages.php', 'store-blog.php', 'store-menus.php', 'store-filters.php', 'store-social-links.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-globe"></i>
                <span>Tienda online</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="store-design.php" class="nav-subitem <?php echo ($current_page == 'store-design.php') ? 'active' : ''; ?>">
                    <span>Diseño</span>
                </a>
                <a href="store-pages.php" class="nav-subitem <?php echo ($current_page == 'store-pages.php') ? 'active' : ''; ?>">
                    <span>Páginas</span>
                </a>
                <a href="store-blog.php" class="nav-subitem <?php echo ($current_page == 'store-blog.php') ? 'active' : ''; ?>">
                    <span>Blog</span>
                </a>
                <a href="store-menus.php" class="nav-subitem <?php echo ($current_page == 'store-menus.php') ? 'active' : ''; ?>">
                    <span>Menús</span>
                </a>
                <a href="store-filters.php" class="nav-subitem <?php echo ($current_page == 'store-filters.php') ? 'active' : ''; ?>">
                    <span>Filtros</span>
                </a>
                <a href="store-social-links.php" class="nav-subitem <?php echo ($current_page == 'store-social-links.php') ? 'active' : ''; ?>">
                    <span>Links de redes sociales</span>
                </a>
            </div>
        </div>
        
        <!-- Punto de venta -->
        <a href="pos.php" class="nav-item <?php echo ($current_page == 'pos.php') ? 'active' : ''; ?>">
            <i class="fas fa-cash-register"></i>
            <span>Punto de venta</span>
        </a>
        
        <!-- Redes sociales -->
        <div class="nav-item-expandable <?php echo (in_array($current_page, ['social-networks.php', 'facebook-meta.php', 'google-shopping.php'])) ? 'active' : ''; ?>">
            <a href="#" class="nav-item nav-expandable">
                <i class="fas fa-share-alt"></i>
                <span>Redes sociales</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu">
                <a href="facebook-meta.php" class="nav-subitem <?php echo ($current_page == 'facebook-meta.php') ? 'active' : ''; ?>">
                    <span>Facebook/Meta</span>
                </a>
                <a href="google-shopping.php" class="nav-subitem <?php echo ($current_page == 'google-shopping.php') ? 'active' : ''; ?>">
                    <span>Google Shopping</span>
                </a>
            </div>
        </div>
        
        <!-- Marketplaces -->
        <a href="marketplaces.php" class="nav-item <?php echo ($current_page == 'marketplaces.php') ? 'active' : ''; ?>">
            <i class="fas fa-store"></i>
            <span>Marketplaces</span>
        </a>
        
        <!-- Categoría: Potenciar -->
        <div class="sidebar-section-header">Potenciar</div>
        
        <!-- Aplicaciones -->
        <a href="applications.php" class="nav-item <?php echo ($current_page == 'applications.php') ? 'active' : ''; ?>">
            <i class="fas fa-th"></i>
            <span>Aplicaciones</span>
        </a>
        
        <!-- Divider final -->
        <div class="sidebar-divider"></div>
        
        <!-- Configuración -->
        <a href="settings.php" class="nav-item <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
    </nav>
</div>

