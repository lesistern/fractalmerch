<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🛒 Ventas - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <!-- Main Content -->
    <div class="modern-admin-main">
        <!-- Header Tiendanube Style -->
        <div class="tiendanube-header">
            <div class="header-left">
                <h1>Ventas</h1>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="exportSales()">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
                <button class="tn-btn tn-btn-primary" onclick="createSale()">
                    <i class="fas fa-plus"></i>
                    Nueva venta
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="tiendanube-search-bar">
            <div class="tn-search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="salesSearch" placeholder="Busca por número de orden, cliente o email">
            </div>
            <div class="tn-filters">
                <button class="tn-filter-btn" onclick="toggleSalesFilters()">
                    <i class="fas fa-filter"></i>
                    Filtrar
                </button>
                <button class="tn-filter-btn" onclick="toggleSalesOptions()">
                    <i class="fas fa-calendar"></i>
                    Fecha
                </button>
            </div>
        </div>
        
        <div class="tn-products-counter">
            <span id="salesCount">3 órdenes</span>
        </div>

        <!-- Sales Section -->
        <div class="sales-section">
            <div style="overflow-x: auto;">
                <table class="tn-products-table">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAllSales">
                            </th>
                            <th>Orden</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th width="120">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="tn-product-row">
                            <td><input type="checkbox" class="sales-checkbox"></td>
                            <td class="tn-product-info">
                                <div class="tn-product-name">
                                    <a href="#" class="tn-product-link">#001</a>
                                </div>
                            </td>
                            <td>Juan Pérez</td>
                            <td>03/07/2025</td>
                            <td class="tn-price-cell">$15,999</td>
                            <td><span class="status-badge completed">Completada</span></td>
                            <td class="tn-actions-cell">
                                <div class="tn-actions">
                                    <button class="tn-action-btn" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="tn-action-btn" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button class="tn-action-btn" title="Enviar por email">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="tn-product-row">
                            <td><input type="checkbox" class="sales-checkbox"></td>
                            <td class="tn-product-info">
                                <div class="tn-product-name">
                                    <a href="#" class="tn-product-link">#002</a>
                                </div>
                            </td>
                            <td>María García</td>
                            <td>02/07/2025</td>
                            <td class="tn-price-cell">$8,500</td>
                            <td><span class="status-badge pending">Pendiente</span></td>
                            <td class="tn-actions-cell">
                                <div class="tn-actions">
                                    <button class="tn-action-btn" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="tn-action-btn" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button class="tn-action-btn" title="Enviar por email">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="tn-product-row">
                            <td><input type="checkbox" class="sales-checkbox"></td>
                            <td class="tn-product-info">
                                <div class="tn-product-name">
                                    <a href="#" class="tn-product-link">#003</a>
                                </div>
                            </td>
                            <td>Carlos López</td>
                            <td>01/07/2025</td>
                            <td class="tn-price-cell">$12,750</td>
                            <td><span class="status-badge processing">Procesando</span></td>
                            <td class="tn-actions-cell">
                                <div class="tn-actions">
                                    <button class="tn-action-btn" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="tn-action-btn" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button class="tn-action-btn" title="Enviar por email">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications Container -->
<div id="toastContainer" class="toast-container"></div>

<!-- Scripts -->
<script src="../assets/js/modern-admin.js?v=<?php echo time(); ?>"></script>
<script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>
<script>
// Page-specific initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sales page loaded');
    
    // Initialize sales-specific features
    enhancedSalesSearch();
    updateSalesCounter();
});
</script>

</body>
</html>