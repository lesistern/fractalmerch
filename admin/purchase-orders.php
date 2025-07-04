<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '📋 Órdenes de Compra - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <!-- Main Content -->
    <div class="modern-admin-main">
        <!-- Header -->
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-clipboard-list"></i> Órdenes de Compra</h1>
                <p class="header-subtitle">Gestión completa de pedidos y órdenes</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-primary" onclick="createNewOrder()">
                    <i class="fas fa-plus"></i>
                    Nueva Orden
                </button>
                <button class="tn-btn tn-btn-secondary" onclick="exportOrders()">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="orders-filters">
            <div class="filter-group">
                <label>Estado:</label>
                <select class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="pending">Pendiente</option>
                    <option value="confirmed">Confirmado</option>
                    <option value="processing">Procesando</option>
                    <option value="shipped">Enviado</option>
                    <option value="delivered">Entregado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Método de pago:</label>
                <select class="filter-select">
                    <option value="">Todos los métodos</option>
                    <option value="credit_card">Tarjeta de crédito</option>
                    <option value="transfer">Transferencia</option>
                    <option value="mercadopago">MercadoPago</option>
                    <option value="cash">Efectivo</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Fecha:</label>
                <input type="date" class="filter-input">
                <span class="filter-separator">a</span>
                <input type="date" class="filter-input">
            </div>
            <div class="filter-group">
                <input type="text" class="filter-search" placeholder="Buscar por cliente, ID de orden...">
                <button class="filter-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Resumen de Órdenes -->
        <div class="orders-summary">
            <div class="summary-card">
                <div class="summary-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="summary-content">
                    <h3>23</h3>
                    <p>Pendientes</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon processing">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="summary-content">
                    <h3>15</h3>
                    <p>Procesando</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon shipped">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="summary-content">
                    <h3>42</h3>
                    <p>Enviadas</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="summary-content">
                    <h3>128</h3>
                    <p>Completadas</p>
                </div>
            </div>
        </div>

        <!-- Lista de Órdenes -->
        <div class="orders-list">
            <div class="orders-table-container">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>ID Orden</th>
                            <th>Cliente</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Método Pago</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="order-id">#ORD-001</span>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name">Juan Pérez</span>
                                    <span class="customer-email">juan@email.com</span>
                                </div>
                            </td>
                            <td>
                                <div class="products-preview">
                                    <span class="product-count">3 productos</span>
                                    <div class="product-thumbnails">
                                        <img src="../assets/images/products/remera.svg" alt="Remera" class="product-thumb">
                                        <img src="../assets/images/products/taza.svg" alt="Taza" class="product-thumb">
                                        <span class="more-products">+1</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="order-total">$8,450</span>
                            </td>
                            <td>
                                <div class="payment-method">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Tarjeta</span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge shipped">Enviado</span>
                            </td>
                            <td>
                                <span class="order-date">15/12/2024</span>
                                <span class="order-time">14:30</span>
                            </td>
                            <td>
                                <div class="order-actions">
                                    <button class="action-btn view" onclick="viewOrder('ORD-001')" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn edit" onclick="editOrder('ORD-001')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn print" onclick="printOrder('ORD-001')" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <span class="order-id">#ORD-002</span>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name">María García</span>
                                    <span class="customer-email">maria@email.com</span>
                                </div>
                            </td>
                            <td>
                                <div class="products-preview">
                                    <span class="product-count">2 productos</span>
                                    <div class="product-thumbnails">
                                        <img src="../assets/images/products/buzo.svg" alt="Buzo" class="product-thumb">
                                        <img src="../assets/images/products/mousepad.svg" alt="Mouse Pad" class="product-thumb">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="order-total">$15,999</span>
                            </td>
                            <td>
                                <div class="payment-method">
                                    <i class="fas fa-university"></i>
                                    <span>Transferencia</span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge processing">Procesando</span>
                            </td>
                            <td>
                                <span class="order-date">15/12/2024</span>
                                <span class="order-time">13:15</span>
                            </td>
                            <td>
                                <div class="order-actions">
                                    <button class="action-btn view" onclick="viewOrder('ORD-002')" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn edit" onclick="editOrder('ORD-002')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn print" onclick="printOrder('ORD-002')" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <span class="order-id">#ORD-003</span>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name">Carlos López</span>
                                    <span class="customer-email">carlos@email.com</span>
                                </div>
                            </td>
                            <td>
                                <div class="products-preview">
                                    <span class="product-count">1 producto</span>
                                    <div class="product-thumbnails">
                                        <img src="../assets/images/products/almohada.svg" alt="Almohada" class="product-thumb">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="order-total">$6,999</span>
                            </td>
                            <td>
                                <div class="payment-method">
                                    <i class="fab fa-cc-mastercard"></i>
                                    <span>MercadoPago</span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge pending">Pendiente</span>
                            </td>
                            <td>
                                <span class="order-date">15/12/2024</span>
                                <span class="order-time">11:45</span>
                            </td>
                            <td>
                                <div class="order-actions">
                                    <button class="action-btn view" onclick="viewOrder('ORD-003')" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn edit" onclick="editOrder('ORD-003')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn print" onclick="printOrder('ORD-003')" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        <div class="pagination-container">
            <div class="pagination-info">
                Mostrando 1-3 de 152 órdenes
            </div>
            <div class="pagination">
                <button class="pagination-btn" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <span class="pagination-dots">...</span>
                <button class="pagination-btn">51</button>
                <button class="pagination-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../assets/js/modern-admin.js?v=<?php echo time(); ?>"></script>
<script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>

<script>
function createNewOrder() {
    alert('Funcionalidad para crear nueva orden en desarrollo');
}

function viewOrder(orderId) {
    alert('Ver detalles de la orden: ' + orderId);
}

function editOrder(orderId) {
    alert('Editar orden: ' + orderId);
}

function printOrder(orderId) {
    alert('Imprimir orden: ' + orderId);
}

function exportOrders() {
    alert('Exportando órdenes...');
}
</script>

<style>
.orders-filters {
    display: flex;
    gap: 1rem;
    padding: 1.5rem 2rem;
    background: white;
    border-radius: 8px;
    margin-bottom: 2rem;
    align-items: center;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
    min-width: max-content;
}

.filter-select,
.filter-input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.filter-separator {
    color: #666;
    margin: 0 0.5rem;
}

.filter-search {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px 0 0 4px;
    font-size: 0.9rem;
    min-width: 200px;
}

.filter-btn {
    padding: 0.5rem 1rem;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
}

.orders-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.summary-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.summary-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.summary-icon.pending { background: #ffc107; }
.summary-icon.processing { background: #007bff; }
.summary-icon.shipped { background: #17a2b8; }
.summary-icon.completed { background: #28a745; }

.summary-content h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    color: #333;
}

.summary-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.orders-list {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.orders-table-container {
    overflow-x: auto;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    background: #f8f9fa;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 1px solid #dee2e6;
}

.orders-table td {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.order-id {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #007bff;
}

.customer-info {
    display: flex;
    flex-direction: column;
}

.customer-name {
    font-weight: 600;
    color: #333;
}

.customer-email {
    font-size: 0.8rem;
    color: #666;
}

.products-preview {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.product-count {
    font-size: 0.9rem;
    color: #666;
}

.product-thumbnails {
    display: flex;
    gap: 0.25rem;
    align-items: center;
}

.product-thumb {
    width: 30px;
    height: 30px;
    object-fit: cover;
    border-radius: 4px;
}

.more-products {
    background: #f8f9fa;
    color: #666;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}

.order-total {
    font-weight: 700;
    font-size: 1.1rem;
    color: #28a745;
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.status-badge.processing {
    background: #cce5ff;
    color: #004085;
}

.status-badge.shipped {
    background: #d1ecf1;
    color: #0c5460;
}

.status-badge.delivered {
    background: #d4edda;
    color: #155724;
}

.order-date {
    font-weight: 600;
    color: #333;
}

.order-time {
    font-size: 0.8rem;
    color: #666;
    display: block;
}

.order-actions {
    display: flex;
    gap: 0.25rem;
}

.action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.action-btn.view {
    background: #e3f2fd;
    color: #1976d2;
}

.action-btn.edit {
    background: #fff3e0;
    color: #f57c00;
}

.action-btn.print {
    background: #f3e5f5;
    color: #7b1fa2;
}

.action-btn:hover {
    opacity: 0.8;
}

.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    background: white;
    border-radius: 8px;
    margin-top: 2rem;
}

.pagination-info {
    color: #666;
    font-size: 0.9rem;
}

.pagination {
    display: flex;
    gap: 0.25rem;
    align-items: center;
}

.pagination-btn {
    width: 36px;
    height: 36px;
    border: 1px solid #ddd;
    background: white;
    color: #666;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.pagination-btn:hover:not(:disabled) {
    background: #f8f9fa;
}

.pagination-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-dots {
    color: #666;
    padding: 0 0.5rem;
}

@media (max-width: 768px) {
    .orders-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        flex-wrap: wrap;
    }
    
    .orders-summary {
        grid-template-columns: 1fr;
    }
    
    .orders-table {
        font-size: 0.8rem;
    }
    
    .orders-table th,
    .orders-table td {
        padding: 0.5rem;
    }
    
    .pagination-container {
        flex-direction: column;
        gap: 1rem;
    }
}

/* Optimización compacta para purchase-orders */
.orders-filters { padding: 1rem 1.5rem !important; margin-bottom: 1.5rem !important; gap: 0.75rem !important; }
.filter-group label { font-size: 0.85rem !important; }
.filter-select, .filter-input { padding: 0.4rem !important; font-size: 0.85rem !important; }
.filter-search { padding: 0.4rem !important; font-size: 0.85rem !important; min-width: 180px !important; }
.filter-btn { padding: 0.4rem 0.8rem !important; }
.orders-summary { gap: 1rem !important; margin-bottom: 1.5rem !important; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important; }
.summary-card { padding: 1rem !important; gap: 0.75rem !important; }
.summary-icon { width: 40px !important; height: 40px !important; font-size: 1rem !important; }
.summary-content h3 { font-size: 1.4rem !important; }
.summary-content p { font-size: 0.85rem !important; }
.orders-table th { padding: 0.75rem !important; font-size: 0.9rem !important; }
.orders-table td { padding: 0.75rem !important; font-size: 0.85rem !important; }
.customer-email { font-size: 0.75rem !important; }
.product-count { font-size: 0.8rem !important; }
.product-thumb { width: 25px !important; height: 25px !important; }
.more-products { font-size: 0.75rem !important; padding: 0.2rem 0.4rem !important; }
.order-total { font-size: 1rem !important; }
.status-badge { padding: 0.2rem 0.6rem !important; font-size: 0.75rem !important; }
.order-time { font-size: 0.75rem !important; }
.action-btn { width: 28px !important; height: 28px !important; font-size: 0.75rem !important; }
.pagination-container { padding: 1rem 1.5rem !important; margin-top: 1.5rem !important; }
.pagination-info { font-size: 0.85rem !important; }
.pagination-btn { width: 32px !important; height: 32px !important; font-size: 0.85rem !important; }
</style>

</body>
</html>