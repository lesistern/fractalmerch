<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '📦 Inventario - Panel Admin';
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
                <h1>Inventario</h1>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="exportInventory()">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
                <button class="tn-btn tn-btn-secondary" onclick="importInventory()">
                    <i class="fas fa-upload"></i>
                    Importar
                </button>
                <button class="tn-btn tn-btn-primary" onclick="adjustStock()">
                    <i class="fas fa-edit"></i>
                    Ajustar stock
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="tiendanube-search-bar">
            <div class="tn-search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="inventorySearch" placeholder="Busca por producto, SKU o código de barras">
            </div>
            <div class="tn-filters">
                <button class="tn-filter-btn" onclick="toggleInventoryFilters()">
                    <i class="fas fa-filter"></i>
                    Filtrar
                </button>
                <button class="tn-filter-btn" onclick="toggleStockAlerts()">
                    <i class="fas fa-exclamation-triangle"></i>
                    Alertas
                </button>
            </div>
        </div>
        
        <div class="tn-products-counter">
            <span id="inventoryCount">6 productos en inventario</span>
        </div>

        <!-- Inventory Alerts -->
        <div class="inventory-alerts">
            <div class="alert-card warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="alert-content">
                    <h4>2 productos con stock bajo</h4>
                    <p>Remera Personalizada y Taza Personalizada necesitan reposición</p>
                </div>
                <button class="alert-action">Ver</button>
            </div>
        </div>

        <!-- Inventory Section -->
        <div class="inventory-section">
            <div style="overflow-x: auto;">
                <table class="tn-products-table">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAllInventory">
                            </th>
                            <th width="80"></th>
                            <th>Producto</th>
                            <th>SKU</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Última Actualización</th>
                            <th width="120">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="tn-product-row">
                            <td><input type="checkbox" class="inventory-checkbox"></td>
                            <td class="tn-product-image">
                                <div class="tn-image-placeholder">
                                    <i class="fas fa-tshirt"></i>
                                </div>
                            </td>
                            <td class="tn-product-info">
                                <div class="tn-product-name">Remera Personalizada</div>
                            </td>
                            <td>REM-001</td>
                            <td class="stock-low">3 unidades</td>
                            <td>10</td>
                            <td>02/07/2025</td>
                            <td class="tn-actions-cell">
                                <div class="tn-actions">
                                    <button class="tn-action-btn" title="Ajustar stock">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="tn-action-btn" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="tn-product-row">
                            <td><input type="checkbox" class="inventory-checkbox"></td>
                            <td class="tn-product-image">
                                <div class="tn-image-placeholder">
                                    <i class="fas fa-coffee"></i>
                                </div>
                            </td>
                            <td class="tn-product-info">
                                <div class="tn-product-name">Taza Personalizada</div>
                            </td>
                            <td>TAZ-001</td>
                            <td class="stock-low">5 unidades</td>
                            <td>15</td>
                            <td>01/07/2025</td>
                            <td class="tn-actions-cell">
                                <div class="tn-actions">
                                    <button class="tn-action-btn" title="Ajustar stock">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="tn-action-btn" title="Historial">
                                        <i class="fas fa-history"></i>
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
    console.log('Inventory page loaded');
    
    // Initialize inventory-specific features
    enhancedInventorySearch();
    updateInventoryCounter();
});
</script>

</body>
</html>