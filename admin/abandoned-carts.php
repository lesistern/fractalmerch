<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🛒 Carritos Abandonados - Panel Admin';
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
                <h1><i class="fas fa-shopping-cart"></i> Carritos Abandonados</h1>
                <p class="header-subtitle">Recuperación de ventas y análisis de abandono</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-primary" onclick="sendRecoveryEmails()">
                    <i class="fas fa-envelope"></i>
                    Enviar Recordatorios
                </button>
                <button class="tn-btn tn-btn-secondary" onclick="exportAbandonedCarts()">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="abandoned-stats">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h3>47</h3>
                    <p>Carritos abandonados</p>
                    <span class="stat-trend neutral">Últimos 30 días</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon billing">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>$68,450</h3>
                    <p>Valor potencial perdido</p>
                    <span class="stat-trend negative">-15% vs mes anterior</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon conversion">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <h3>73.2%</h3>
                    <p>Tasa de abandono</p>
                    <span class="stat-trend positive">-5.1% vs anterior</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon customers">
                    <i class="fas fa-undo"></i>
                </div>
                <div class="stat-content">
                    <h3>12</h3>
                    <p>Recuperaciones exitosas</p>
                    <span class="stat-trend positive">+4 este mes</span>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="carts-filters">
            <div class="filter-group">
                <label>Período abandono:</label>
                <select class="filter-select">
                    <option value="">Cualquier momento</option>
                    <option value="1h">Última hora</option>
                    <option value="24h">Últimas 24 horas</option>
                    <option value="7d">Últimos 7 días</option>
                    <option value="30d">Últimos 30 días</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Valor mínimo:</label>
                <input type="number" class="filter-input" placeholder="$0" min="0">
            </div>
            <div class="filter-group">
                <label>Estado email:</label>
                <select class="filter-select">
                    <option value="">Todos</option>
                    <option value="sent">Enviado</option>
                    <option value="not_sent">No enviado</option>
                    <option value="bounced">Rebotado</option>
                </select>
            </div>
            <div class="filter-group">
                <input type="text" class="filter-search" placeholder="Buscar por cliente, email...">
                <button class="filter-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Lista de Carritos Abandonados -->
        <div class="abandoned-carts-list">
            <div class="carts-table-container">
                <table class="carts-table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Cliente</th>
                            <th>Productos</th>
                            <th>Valor</th>
                            <th>Abandonado</th>
                            <th>Emails enviados</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="checkbox" class="row-select" value="cart-001">
                            </td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name">Ana Rodríguez</span>
                                    <span class="customer-email">ana@email.com</span>
                                    <div class="customer-details">
                                        <span class="customer-location">Buenos Aires</span>
                                        <span class="customer-visits">3 visitas</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="cart-products">
                                    <div class="products-preview">
                                        <img src="../assets/images/products/remera.svg" alt="Remera" class="product-thumb">
                                        <img src="../assets/images/products/buzo.svg" alt="Buzo" class="product-thumb">
                                        <span class="more-products">+1</span>
                                    </div>
                                    <span class="products-count">3 productos</span>
                                </div>
                            </td>
                            <td>
                                <span class="cart-value">$18,450</span>
                            </td>
                            <td>
                                <div class="abandoned-time">
                                    <span class="time-value">Hace 2 horas</span>
                                    <span class="time-exact">15/12/2024 16:30</span>
                                </div>
                            </td>
                            <td>
                                <div class="email-status">
                                    <span class="emails-sent">1 enviado</span>
                                    <div class="email-timeline">
                                        <span class="email-dot sent" title="Email enviado hace 1 hora"></span>
                                        <span class="email-dot pending" title="Próximo email en 22 horas"></span>
                                        <span class="email-dot pending" title="Email final en 6 días"></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge active">Activo</span>
                            </td>
                            <td>
                                <div class="cart-actions">
                                    <button class="action-btn view" onclick="viewCart('cart-001')" title="Ver carrito">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn email" onclick="sendEmail('cart-001')" title="Enviar email">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button class="action-btn convert" onclick="convertCart('cart-001')" title="Marcar como convertido">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <input type="checkbox" class="row-select" value="cart-002">
                            </td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name">Luis Martínez</span>
                                    <span class="customer-email">luis@email.com</span>
                                    <div class="customer-details">
                                        <span class="customer-location">Córdoba</span>
                                        <span class="customer-visits">1 visita</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="cart-products">
                                    <div class="products-preview">
                                        <img src="../assets/images/products/taza.svg" alt="Taza" class="product-thumb">
                                        <img src="../assets/images/products/mousepad.svg" alt="Mouse Pad" class="product-thumb">
                                    </div>
                                    <span class="products-count">2 productos</span>
                                </div>
                            </td>
                            <td>
                                <span class="cart-value">$6,498</span>
                            </td>
                            <td>
                                <div class="abandoned-time">
                                    <span class="time-value">Hace 1 día</span>
                                    <span class="time-exact">14/12/2024 20:15</span>
                                </div>
                            </td>
                            <td>
                                <div class="email-status">
                                    <span class="emails-sent">2 enviados</span>
                                    <div class="email-timeline">
                                        <span class="email-dot sent" title="Email enviado hace 18 horas"></span>
                                        <span class="email-dot sent" title="Email enviado hace 6 horas"></span>
                                        <span class="email-dot pending" title="Email final en 5 días"></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge active">Activo</span>
                            </td>
                            <td>
                                <div class="cart-actions">
                                    <button class="action-btn view" onclick="viewCart('cart-002')" title="Ver carrito">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn email" onclick="sendEmail('cart-002')" title="Enviar email">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button class="action-btn convert" onclick="convertCart('cart-002')" title="Marcar como convertido">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <input type="checkbox" class="row-select" value="cart-003">
                            </td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name">Carmen Silva</span>
                                    <span class="customer-email">carmen@email.com</span>
                                    <div class="customer-details">
                                        <span class="customer-location">Rosario</span>
                                        <span class="customer-visits">5 visitas</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="cart-products">
                                    <div class="products-preview">
                                        <img src="../assets/images/products/almohada.svg" alt="Almohada" class="product-thumb">
                                    </div>
                                    <span class="products-count">1 producto</span>
                                </div>
                            </td>
                            <td>
                                <span class="cart-value">$6,999</span>
                            </td>
                            <td>
                                <div class="abandoned-time">
                                    <span class="time-value">Hace 7 días</span>
                                    <span class="time-exact">08/12/2024 14:22</span>
                                </div>
                            </td>
                            <td>
                                <div class="email-status">
                                    <span class="emails-sent">3 enviados</span>
                                    <div class="email-timeline">
                                        <span class="email-dot sent" title="Email enviado hace 6 días"></span>
                                        <span class="email-dot sent" title="Email enviado hace 5 días"></span>
                                        <span class="email-dot sent" title="Email enviado hace 1 día"></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge completed">Completado</span>
                            </td>
                            <td>
                                <div class="cart-actions">
                                    <button class="action-btn view" onclick="viewCart('cart-003')" title="Ver carrito">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn archive" onclick="archiveCart('cart-003')" title="Archivar">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Acciones en lote -->
        <div class="bulk-actions" style="display: none;" id="bulkActions">
            <div class="bulk-info">
                <span id="selectedCount">0</span> carritos seleccionados
            </div>
            <div class="bulk-buttons">
                <button class="bulk-btn" onclick="bulkSendEmails()">
                    <i class="fas fa-envelope"></i>
                    Enviar emails
                </button>
                <button class="bulk-btn" onclick="bulkArchive()">
                    <i class="fas fa-archive"></i>
                    Archivar
                </button>
                <button class="bulk-btn danger" onclick="bulkDelete()">
                    <i class="fas fa-trash"></i>
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../assets/js/modern-admin.js?v=<?php echo time(); ?>"></script>
<script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>

<script>
let selectedCarts = [];

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const rowSelects = document.querySelectorAll('.row-select');
    
    rowSelects.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCarts();
}

function updateSelectedCarts() {
    const checkedBoxes = document.querySelectorAll('.row-select:checked');
    selectedCarts = Array.from(checkedBoxes).map(cb => cb.value);
    
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedCarts.length > 0) {
        bulkActions.style.display = 'flex';
        selectedCount.textContent = selectedCarts.length;
    } else {
        bulkActions.style.display = 'none';
    }
}

// Event listeners for checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.row-select');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCarts);
    });
});

function viewCart(cartId) {
    alert('Ver detalles del carrito: ' + cartId);
}

function sendEmail(cartId) {
    alert('Enviar email de recuperación para carrito: ' + cartId);
}

function convertCart(cartId) {
    if (confirm('¿Marcar este carrito como convertido?')) {
        alert('Carrito marcado como convertido: ' + cartId);
    }
}

function archiveCart(cartId) {
    if (confirm('¿Archivar este carrito?')) {
        alert('Carrito archivado: ' + cartId);
    }
}

function sendRecoveryEmails() {
    alert('Enviando emails de recuperación automáticos...');
}

function exportAbandonedCarts() {
    alert('Exportando carritos abandonados...');
}

function bulkSendEmails() {
    if (selectedCarts.length === 0) return;
    alert('Enviando emails a ' + selectedCarts.length + ' carritos seleccionados');
}

function bulkArchive() {
    if (selectedCarts.length === 0) return;
    if (confirm('¿Archivar ' + selectedCarts.length + ' carritos seleccionados?')) {
        alert('Carritos archivados');
    }
}

function bulkDelete() {
    if (selectedCarts.length === 0) return;
    if (confirm('¿Eliminar ' + selectedCarts.length + ' carritos seleccionados? Esta acción no se puede deshacer.')) {
        alert('Carritos eliminados');
    }
}
</script>

<style>
.abandoned-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.carts-filters {
    display: flex;
    gap: 1rem;
    padding: 1.5rem 2rem;
    background: white;
    border-radius: 8px;
    margin-bottom: 2rem;
    align-items: center;
    flex-wrap: wrap;
}

.abandoned-carts-list {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.carts-table-container {
    overflow-x: auto;
}

.carts-table {
    width: 100%;
    border-collapse: collapse;
}

.carts-table th {
    background: #f8f9fa;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 1px solid #dee2e6;
}

.carts-table td {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    vertical-align: top;
}

.customer-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.customer-name {
    font-weight: 600;
    color: #333;
}

.customer-email {
    font-size: 0.8rem;
    color: #666;
}

.customer-details {
    display: flex;
    gap: 1rem;
    font-size: 0.8rem;
    color: #888;
}

.cart-products {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.products-preview {
    display: flex;
    gap: 0.25rem;
    align-items: center;
}

.product-thumb {
    width: 32px;
    height: 32px;
    object-fit: cover;
    border-radius: 4px;
}

.more-products {
    background: #f8f9fa;
    color: #666;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
}

.products-count {
    font-size: 0.8rem;
    color: #666;
}

.cart-value {
    font-weight: 700;
    font-size: 1.1rem;
    color: #dc3545;
}

.abandoned-time {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.time-value {
    font-weight: 600;
    color: #333;
}

.time-exact {
    font-size: 0.8rem;
    color: #666;
}

.email-status {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.emails-sent {
    font-size: 0.9rem;
    color: #333;
}

.email-timeline {
    display: flex;
    gap: 0.25rem;
    align-items: center;
}

.email-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    cursor: help;
}

.email-dot.sent {
    background: #28a745;
}

.email-dot.pending {
    background: #dee2e6;
}

.status-badge.active {
    background: #fff3cd;
    color: #856404;
}

.status-badge.completed {
    background: #d4edda;
    color: #155724;
}

.cart-actions {
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

.action-btn.email {
    background: #e8f5e8;
    color: #2e7d32;
}

.action-btn.convert {
    background: #fff3e0;
    color: #f57c00;
}

.action-btn.archive {
    background: #f3e5f5;
    color: #7b1fa2;
}

.action-btn:hover {
    opacity: 0.8;
}

.bulk-actions {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    padding: 1rem 2rem;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 2rem;
    z-index: 1000;
}

.bulk-info {
    font-weight: 600;
    color: #333;
}

.bulk-buttons {
    display: flex;
    gap: 0.5rem;
}

.bulk-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    background: #007bff;
    color: white;
}

.bulk-btn:hover {
    opacity: 0.9;
}

.bulk-btn.danger {
    background: #dc3545;
}

@media (max-width: 768px) {
    .carts-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .abandoned-stats {
        grid-template-columns: 1fr;
    }
    
    .carts-table {
        font-size: 0.8rem;
    }
    
    .carts-table th,
    .carts-table td {
        padding: 0.5rem;
    }
    
    .bulk-actions {
        flex-direction: column;
        gap: 1rem;
        left: 1rem;
        right: 1rem;
        transform: none;
    }
}

/* Optimización compacta para abandoned-carts */
.abandoned-stats { gap: 1rem !important; margin-bottom: 1.5rem !important; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important; }
.carts-filters { padding: 1rem 1.5rem !important; margin-bottom: 1.5rem !important; gap: 0.75rem !important; }
.carts-table th { padding: 0.75rem !important; font-size: 0.9rem !important; }
.carts-table td { padding: 0.75rem !important; font-size: 0.85rem !important; }
.customer-email { font-size: 0.75rem !important; }
.customer-details { font-size: 0.75rem !important; }
.product-thumb { width: 28px !important; height: 28px !important; }
.more-products { font-size: 0.65rem !important; padding: 0.2rem 0.4rem !important; }
.products-count { font-size: 0.75rem !important; }
.cart-value { font-size: 1rem !important; }
.time-exact { font-size: 0.75rem !important; }
.emails-sent { font-size: 0.85rem !important; }
.email-dot { width: 10px !important; height: 10px !important; }
.action-btn { width: 28px !important; height: 28px !important; font-size: 0.75rem !important; }
.bulk-actions { padding: 0.75rem 1.5rem !important; }
.bulk-info { font-size: 0.9rem !important; }
.bulk-btn { padding: 0.4rem 0.8rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>