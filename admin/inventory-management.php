<?php
$pageTitle = '📦 Gestión de Inventario - Admin Panel';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-boxes"></i> Gestión de Inventario</h1>
    <p>Sistema completo de gestión de inventario en tiempo real</p>
    
    <div class="page-actions">
        <button id="add-item-btn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar Producto
        </button>
        <button id="bulk-update-btn" class="btn btn-secondary">
            <i class="fas fa-upload"></i> Actualización Masiva
        </button>
        <button id="export-inventory-btn" class="btn btn-outline">
            <i class="fas fa-download"></i> Exportar
        </button>
    </div>
</div>

<!-- Resumen de Inventario -->
<div class="content-card">
    <h3><i class="fas fa-chart-bar"></i> Resumen de Inventario</h3>
    <div class="inventory-summary">
        <div class="summary-card total-items">
            <div class="card-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="card-content">
                <h3 id="total-items-count">-</h3>
                <p>Total de Productos</p>
                <span class="trend" id="items-trend"></span>
            </div>
        </div>

        <div class="summary-card total-stock">
            <div class="card-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="card-content">
                <h3 id="total-stock-count">-</h3>
                <p>Stock Total</p>
                <span class="trend" id="stock-trend"></span>
            </div>
        </div>

        <div class="summary-card inventory-value">
            <div class="card-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="card-content">
                <h3 id="inventory-value">-</h3>
                <p>Valor del Inventario</p>
                <span class="trend" id="value-trend"></span>
            </div>
        </div>

        <div class="summary-card low-stock">
            <div class="card-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="card-content">
                <h3 id="low-stock-count">-</h3>
                <p>Stock Bajo</p>
                <span class="trend warning" id="low-stock-trend">Requiere atención</span>
            </div>
        </div>

        <div class="summary-card out-of-stock">
            <div class="card-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="card-content">
                <h3 id="out-of-stock-count">-</h3>
                <p>Sin Stock</p>
                <span class="trend critical" id="out-of-stock-trend">Crítico</span>
            </div>
        </div>

        <div class="summary-card pending-orders">
            <div class="card-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card-content">
                <h3 id="pending-orders-count">-</h3>
                <p>Pedidos Pendientes</p>
                <span class="trend" id="orders-trend"></span>
            </div>
        </div>
    </div>
</div>

    <!-- Inventory Controls -->
    <div class="inventory-controls">
        <div class="control-group">
            <label for="category-filter">Categoría:</label>
            <select id="category-filter">
                <option value="">Todas las categorías</option>
                <option value="Remeras">Remeras</option>
                <option value="Buzos">Buzos</option>
                <option value="Tazas">Tazas</option>
                <option value="Mouse Pads">Mouse Pads</option>
                <option value="Fundas">Fundas</option>
                <option value="Almohadas">Almohadas</option>
            </select>
        </div>

        <div class="control-group">
            <label for="stock-filter">Stock:</label>
            <select id="stock-filter">
                <option value="">Todos los niveles</option>
                <option value="ok">Stock OK</option>
                <option value="low">Stock Bajo</option>
                <option value="critical">Stock Crítico</option>
                <option value="out">Sin Stock</option>
            </select>
        </div>

        <div class="control-group">
            <label for="supplier-filter">Proveedor:</label>
            <select id="supplier-filter">
                <option value="">Todos los proveedores</option>
            </select>
        </div>

        <div class="control-group">
            <label for="search-inventory">Buscar:</label>
            <input type="text" id="search-inventory" placeholder="SKU, nombre o código...">
        </div>

        <div class="control-group">
            <button id="refresh-inventory" class="btn btn-outline">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Inventory Tabs -->
    <div class="inventory-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="inventory">Inventario</button>
            <button class="tab-btn" data-tab="movements">Movimientos</button>
            <button class="tab-btn" data-tab="alerts">Alertas</button>
            <button class="tab-btn" data-tab="orders">Pedidos</button>
            <button class="tab-btn" data-tab="suppliers">Proveedores</button>
        </div>

        <!-- Inventory Tab -->
        <div id="inventory-tab" class="tab-content active">
            <div class="inventory-table-container">
                <table id="inventory-table" class="inventory-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-items"></th>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Reservado</th>
                            <th>Stock Disponible</th>
                            <th>Punto de Reorden</th>
                            <th>Costo Unitario</th>
                            <th>Valor Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-tbody">
                        <!-- Inventory items will be loaded here -->
                    </tbody>
                </table>
            </div>

            <div class="table-pagination">
                <div class="pagination-info">
                    <span id="inventory-showing">Mostrando 0 de 0 productos</span>
                </div>
                <div class="pagination-controls">
                    <button id="prev-inventory-page" class="btn btn-sm" disabled>← Anterior</button>
                    <span id="inventory-page-numbers"></span>
                    <button id="next-inventory-page" class="btn btn-sm" disabled>Siguiente →</button>
                </div>
            </div>
        </div>

        <!-- Stock Movements Tab -->
        <div id="movements-tab" class="tab-content">
            <div class="movements-controls">
                <div class="control-group">
                    <label for="movement-type-filter">Tipo:</label>
                    <select id="movement-type-filter">
                        <option value="">Todos los movimientos</option>
                        <option value="purchase">Compras</option>
                        <option value="sale">Ventas</option>
                        <option value="restock">Reabastecimiento</option>
                        <option value="adjustment">Ajustes</option>
                        <option value="damaged">Dañados</option>
                        <option value="expired">Vencidos</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <label for="movement-date-from">Desde:</label>
                    <input type="date" id="movement-date-from">
                </div>
                
                <div class="control-group">
                    <label for="movement-date-to">Hasta:</label>
                    <input type="date" id="movement-date-to">
                </div>
            </div>

            <div class="movements-list" id="movements-list">
                <!-- Stock movements will be loaded here -->
            </div>
        </div>

        <!-- Alerts Tab -->
        <div id="alerts-tab" class="tab-content">
            <div class="alerts-controls">
                <div class="control-group">
                    <label for="alert-type-filter">Tipo:</label>
                    <select id="alert-type-filter">
                        <option value="">Todas las alertas</option>
                        <option value="low_stock">Stock Bajo</option>
                        <option value="critical_stock">Stock Crítico</option>
                        <option value="out_of_stock">Sin Stock</option>
                        <option value="reorder_triggered">Reorden Activada</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <button id="acknowledge-all-alerts" class="btn btn-secondary">
                        <i class="fas fa-check"></i> Marcar como Leídas
                    </button>
                </div>
            </div>

            <div class="alerts-list" id="alerts-list">
                <!-- Alerts will be loaded here -->
            </div>
        </div>

        <!-- Purchase Orders Tab -->
        <div id="orders-tab" class="tab-content">
            <div class="orders-header">
                <h3>Órdenes de Compra</h3>
                <button id="create-order-btn" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Orden
                </button>
            </div>

            <div class="orders-list" id="orders-list">
                <!-- Purchase orders will be loaded here -->
            </div>
        </div>

        <!-- Suppliers Tab -->
        <div id="suppliers-tab" class="tab-content">
            <div class="suppliers-header">
                <h3>Proveedores</h3>
                <button id="add-supplier-btn" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Agregar Proveedor
                </button>
            </div>

            <div class="suppliers-grid" id="suppliers-grid">
                <!-- Suppliers will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <div id="item-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="item-modal-title">Agregar Producto</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="item-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="item-sku">SKU *</label>
                            <input type="text" id="item-sku" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="item-name">Nombre del Producto *</label>
                            <input type="text" id="item-name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="item-category">Categoría</label>
                            <select id="item-category">
                                <option value="Remeras">Remeras</option>
                                <option value="Buzos">Buzos</option>
                                <option value="Tazas">Tazas</option>
                                <option value="Mouse Pads">Mouse Pads</option>
                                <option value="Fundas">Fundas</option>
                                <option value="Almohadas">Almohadas</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="item-current-stock">Stock Actual</label>
                            <input type="number" id="item-current-stock" min="0" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="item-reorder-point">Punto de Reorden</label>
                            <input type="number" id="item-reorder-point" min="0" value="10">
                        </div>
                        
                        <div class="form-group">
                            <label for="item-reorder-quantity">Cantidad de Reorden</label>
                            <input type="number" id="item-reorder-quantity" min="1" value="50">
                        </div>
                        
                        <div class="form-group">
                            <label for="item-unit-cost">Costo Unitario</label>
                            <input type="number" id="item-unit-cost" min="0" step="0.01" value="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label for="item-supplier">Proveedor</label>
                            <select id="item-supplier">
                                <option value="">Seleccionar proveedor...</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="item-location">Ubicación</label>
                            <input type="text" id="item-location" placeholder="Ej: Depósito A - Estante 1">
                        </div>
                        
                        <div class="form-group span-2">
                            <label for="item-notes">Notas</label>
                            <textarea id="item-notes" rows="3" placeholder="Información adicional..."></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancel-item">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div id="adjustment-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajustar Stock</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="adjustment-form">
                    <div class="adjustment-info">
                        <h4 id="adjustment-product-name"></h4>
                        <p>SKU: <span id="adjustment-product-sku"></span></p>
                        <p>Stock Actual: <span id="adjustment-current-stock"></span></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="adjustment-type">Tipo de Ajuste</label>
                        <select id="adjustment-type" required>
                            <option value="">Seleccionar tipo...</option>
                            <option value="restock">Reabastecimiento (+)</option>
                            <option value="adjustment_positive">Ajuste Positivo (+)</option>
                            <option value="adjustment_negative">Ajuste Negativo (-)</option>
                            <option value="damaged">Productos Dañados (-)</option>
                            <option value="expired">Productos Vencidos (-)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="adjustment-quantity">Cantidad</label>
                        <input type="number" id="adjustment-quantity" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="adjustment-unit-cost">Costo Unitario (opcional)</label>
                        <input type="number" id="adjustment-unit-cost" min="0" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label for="adjustment-reason">Motivo</label>
                        <textarea id="adjustment-reason" rows="3" placeholder="Describe el motivo del ajuste..." required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancel-adjustment">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Aplicar Ajuste</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.inventory-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
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
}

.total-items .card-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.total-stock .card-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.inventory-value .card-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.low-stock .card-icon { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #e67e22; }
.out-of-stock .card-icon { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #e74c3c; }
.pending-orders .card-icon { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #27ae60; }

.card-content h3 {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 5px 0;
}

.card-content p {
    color: #7f8c8d;
    margin: 0 0 10px 0;
    font-size: 14px;
}

.trend {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
}

.trend.positive { background: #d4edda; color: #155724; }
.trend.negative { background: #f8d7da; color: #721c24; }
.trend.warning { background: #fff3cd; color: #856404; }
.trend.critical { background: #f8d7da; color: #721c24; }

.inventory-controls {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    flex-wrap: wrap;
}

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

.control-group select,
.control-group input {
    padding: 8px 12px;
    border: 2px solid #e0e6ed;
    border-radius: 6px;
    font-size: 14px;
}

.inventory-tabs {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.tab-buttons {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.tab-btn {
    flex: 1;
    padding: 15px 20px;
    border: none;
    background: transparent;
    color: #6c757d;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.tab-btn.active {
    background: white;
    color: #007bff;
    border-bottom: 3px solid #007bff;
}

.tab-content {
    display: none;
    padding: 30px;
}

.tab-content.active {
    display: block;
}

.inventory-table-container {
    overflow-x: auto;
}

.inventory-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.inventory-table th,
.inventory-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
    white-space: nowrap;
}

.inventory-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    position: sticky;
    top: 0;
    z-index: 10;
}

.inventory-table tr:hover {
    background: #f8f9fa;
}

.stock-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.stock-status.ok { background: #d4edda; color: #155724; }
.stock-status.low { background: #fff3cd; color: #856404; }
.stock-status.critical { background: #f8d7da; color: #721c24; }
.stock-status.out { background: #f8d7da; color: #721c24; }

.item-actions {
    display: flex;
    gap: 8px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 4px;
}

.movements-list,
.alerts-list,
.orders-list {
    max-height: 600px;
    overflow-y: auto;
}

.movement-item,
.alert-item,
.order-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    transition: background 0.2s ease;
}

.movement-item:hover,
.alert-item:hover,
.order-item:hover {
    background: #f8f9fa;
}

.movement-icon,
.alert-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 16px;
    color: white;
}

.movement-icon.purchase { background: #28a745; }
.movement-icon.sale { background: #dc3545; }
.movement-icon.restock { background: #007bff; }
.movement-icon.adjustment { background: #6f42c1; }
.movement-icon.damaged { background: #fd7e14; }

.alert-icon.low_stock { background: #ffc107; color: #212529; }
.alert-icon.critical_stock { background: #fd7e14; }
.alert-icon.out_of_stock { background: #dc3545; }

.suppliers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.supplier-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.supplier-card h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow: hidden;
}

.modal-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group.span-2 {
    grid-column: span 2;
}

.form-group label {
    font-weight: 600;
    color: #2c3e50;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 10px;
    border: 2px solid #e0e6ed;
    border-radius: 6px;
    font-size: 14px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.table-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

@media (max-width: 768px) {
    .inventory-summary {
        grid-template-columns: 1fr;
    }
    
    .inventory-controls {
        flex-direction: column;
    }
    
    .tab-buttons {
        overflow-x: auto;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-group.span-2 {
        grid-column: span 1;
    }
}
</style>

<script>
class InventoryManagement {
    constructor() {
        this.currentTab = 'inventory';
        this.currentPage = 1;
        this.itemsPerPage = 25;
        this.currentFilters = {};
        this.inventory = [];
        this.suppliers = [];
        this.selectedItems = new Set();
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadInventoryData();
        this.loadSuppliers();
        this.setupModals();
        this.setupTabs();
        
        // Initialize inventory manager if available
        if (window.inventoryManager) {
            this.setupInventoryManagerIntegration();
        }
    }
    
    setupEventListeners() {
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });
        
        // Filters
        document.getElementById('category-filter').addEventListener('change', () => this.applyFilters());
        document.getElementById('stock-filter').addEventListener('change', () => this.applyFilters());
        document.getElementById('supplier-filter').addEventListener('change', () => this.applyFilters());
        document.getElementById('search-inventory').addEventListener('input', () => this.applyFilters());
        
        // Refresh button
        document.getElementById('refresh-inventory').addEventListener('click', () => {
            this.loadInventoryData();
        });
        
        // Add item button
        document.getElementById('add-item-btn').addEventListener('click', () => {
            this.showAddItemModal();
        });
        
        // Select all checkbox
        document.getElementById('select-all-items').addEventListener('change', (e) => {
            this.toggleSelectAll(e.target.checked);
        });
    }
    
    setupTabs() {
        // Initial tab setup is already done in HTML
    }
    
    switchTab(tabName) {
        // Update button states
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        
        // Update content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');
        
        this.currentTab = tabName;
        
        // Load tab-specific data
        switch(tabName) {
            case 'movements':
                this.loadStockMovements();
                break;
            case 'alerts':
                this.loadStockAlerts();
                break;
            case 'orders':
                this.loadPurchaseOrders();
                break;
            case 'suppliers':
                this.loadSuppliersTab();
                break;
        }
    }
    
    async loadInventoryData() {
        try {
            const response = await fetch('../api/inventory/get-inventory.php');
            const data = await response.json();
            
            if (data.success) {
                this.inventory = data.inventory;
                this.updateSummaryCards(data.summary);
                this.renderInventoryTable();
                this.updatePagination();
            }
        } catch (error) {
            console.error('Error loading inventory:', error);
        }
    }
    
    async loadSuppliers() {
        try {
            const response = await fetch('../api/suppliers/get-suppliers.php');
            const data = await response.json();
            
            if (data.success) {
                this.suppliers = data.suppliers;
                this.populateSupplierFilters();
            }
        } catch (error) {
            console.error('Error loading suppliers:', error);
        }
    }
    
    updateSummaryCards(summary) {
        document.getElementById('total-items-count').textContent = summary.total_items || 0;
        document.getElementById('total-stock-count').textContent = (summary.total_stock || 0).toLocaleString();
        document.getElementById('inventory-value').textContent = '$' + (summary.total_value || 0).toLocaleString();
        document.getElementById('low-stock-count').textContent = summary.low_stock_items || 0;
        document.getElementById('out-of-stock-count').textContent = summary.out_of_stock_items || 0;
        document.getElementById('pending-orders-count').textContent = summary.pending_orders || 0;
    }
    
    renderInventoryTable() {
        const tbody = document.getElementById('inventory-tbody');
        const filteredInventory = this.getFilteredInventory();
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const pageItems = filteredInventory.slice(startIndex, endIndex);
        
        tbody.innerHTML = pageItems.map(item => `
            <tr data-item-id="${item.id}">
                <td><input type="checkbox" class="item-checkbox" value="${item.id}"></td>
                <td><code>${item.sku}</code></td>
                <td>
                    <strong>${item.name}</strong>
                    ${item.product_name ? `<br><small>${item.product_name}</small>` : ''}
                </td>
                <td><span class="category-badge">${item.category || 'Sin categoría'}</span></td>
                <td><strong>${item.current_stock}</strong></td>
                <td><span class="reserved-stock">${item.reserved_stock || 0}</span></td>
                <td><strong class="available-stock">${item.available_stock}</strong></td>
                <td>${item.reorder_point}</td>
                <td>$${parseFloat(item.unit_cost || 0).toLocaleString()}</td>
                <td>$${(item.current_stock * parseFloat(item.unit_cost || 0)).toLocaleString()}</td>
                <td><span class="stock-status ${item.stock_status}">${this.getStatusText(item.stock_status)}</span></td>
                <td>
                    <div class="item-actions">
                        <button class="btn btn-sm btn-primary" onclick="inventoryManagement.editItem(${item.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="inventoryManagement.adjustStock(${item.id})">
                            <i class="fas fa-plus-minus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline" onclick="inventoryManagement.viewHistory(${item.id})">
                            <i class="fas fa-history"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
        // Update showing info
        document.getElementById('inventory-showing').textContent = 
            `Mostrando ${startIndex + 1}-${Math.min(endIndex, filteredInventory.length)} de ${filteredInventory.length} productos`;
    }
    
    getStatusText(status) {
        const statusMap = {
            'ok': 'Stock OK',
            'low': 'Stock Bajo',
            'critical': 'Crítico',
            'out_of_stock': 'Sin Stock'
        };
        return statusMap[status] || status;
    }
    
    getFilteredInventory() {
        let filtered = [...this.inventory];
        
        // Apply category filter
        if (this.currentFilters.category) {
            filtered = filtered.filter(item => item.category === this.currentFilters.category);
        }
        
        // Apply stock filter
        if (this.currentFilters.stock) {
            filtered = filtered.filter(item => item.stock_status === this.currentFilters.stock);
        }
        
        // Apply supplier filter
        if (this.currentFilters.supplier) {
            filtered = filtered.filter(item => item.supplier_id === this.currentFilters.supplier);
        }
        
        // Apply search filter
        if (this.currentFilters.search) {
            const search = this.currentFilters.search.toLowerCase();
            filtered = filtered.filter(item => 
                item.sku.toLowerCase().includes(search) ||
                item.name.toLowerCase().includes(search) ||
                (item.product_name && item.product_name.toLowerCase().includes(search))
            );
        }
        
        return filtered;
    }
    
    applyFilters() {
        this.currentFilters = {
            category: document.getElementById('category-filter').value,
            stock: document.getElementById('stock-filter').value,
            supplier: document.getElementById('supplier-filter').value,
            search: document.getElementById('search-inventory').value
        };
        
        this.currentPage = 1;
        this.renderInventoryTable();
        this.updatePagination();
    }
    
    updatePagination() {
        const filteredCount = this.getFilteredInventory().length;
        const totalPages = Math.ceil(filteredCount / this.itemsPerPage);
        
        document.getElementById('prev-inventory-page').disabled = this.currentPage <= 1;
        document.getElementById('next-inventory-page').disabled = this.currentPage >= totalPages;
        
        // Update page numbers
        const pageNumbers = document.getElementById('inventory-page-numbers');
        pageNumbers.innerHTML = `Página ${this.currentPage} de ${totalPages}`;
    }
    
    populateSupplierFilters() {
        const supplierFilter = document.getElementById('supplier-filter');
        const itemSupplierSelect = document.getElementById('item-supplier');
        
        // Clear existing options (except first)
        supplierFilter.innerHTML = '<option value="">Todos los proveedores</option>';
        itemSupplierSelect.innerHTML = '<option value="">Seleccionar proveedor...</option>';
        
        this.suppliers.forEach(supplier => {
            const option1 = new Option(supplier.name, supplier.id);
            const option2 = new Option(supplier.name, supplier.id);
            supplierFilter.appendChild(option1);
            itemSupplierSelect.appendChild(option2);
        });
    }
    
    showAddItemModal() {
        document.getElementById('item-modal-title').textContent = 'Agregar Producto';
        document.getElementById('item-form').reset();
        document.getElementById('item-modal').style.display = 'block';
    }
    
    editItem(itemId) {
        const item = this.inventory.find(i => i.id == itemId);
        if (!item) return;
        
        document.getElementById('item-modal-title').textContent = 'Editar Producto';
        document.getElementById('item-sku').value = item.sku;
        document.getElementById('item-name').value = item.name;
        document.getElementById('item-category').value = item.category || '';
        document.getElementById('item-current-stock').value = item.current_stock;
        document.getElementById('item-reorder-point').value = item.reorder_point;
        document.getElementById('item-reorder-quantity').value = item.reorder_quantity;
        document.getElementById('item-unit-cost').value = item.unit_cost;
        document.getElementById('item-supplier').value = item.supplier_id || '';
        document.getElementById('item-location').value = item.location || '';
        document.getElementById('item-notes').value = item.notes || '';
        
        document.getElementById('item-modal').style.display = 'block';
    }
    
    adjustStock(itemId) {
        const item = this.inventory.find(i => i.id == itemId);
        if (!item) return;
        
        document.getElementById('adjustment-product-name').textContent = item.name;
        document.getElementById('adjustment-product-sku').textContent = item.sku;
        document.getElementById('adjustment-current-stock').textContent = item.current_stock;
        document.getElementById('adjustment-form').reset();
        document.getElementById('adjustment-form').dataset.itemId = itemId;
        
        document.getElementById('adjustment-modal').style.display = 'block';
    }
    
    viewHistory(itemId) {
        // This would open a modal or navigate to history page
        console.log('View history for item:', itemId);
    }
    
    toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
            if (checked) {
                this.selectedItems.add(checkbox.value);
            } else {
                this.selectedItems.delete(checkbox.value);
            }
        });
    }
    
    setupModals() {
        // Close buttons
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.onclick = () => {
                closeBtn.closest('.modal').style.display = 'none';
            };
        });
        
        // Cancel buttons
        document.getElementById('cancel-item').onclick = () => {
            document.getElementById('item-modal').style.display = 'none';
        };
        
        document.getElementById('cancel-adjustment').onclick = () => {
            document.getElementById('adjustment-modal').style.display = 'none';
        };
        
        // Form submissions
        document.getElementById('item-form').onsubmit = (e) => {
            e.preventDefault();
            this.saveItem();
        };
        
        document.getElementById('adjustment-form').onsubmit = (e) => {
            e.preventDefault();
            this.saveAdjustment();
        };
        
        // Click outside to close
        window.onclick = (event) => {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    }
    
    async saveItem() {
        const formData = new FormData(document.getElementById('item-form'));
        const itemData = Object.fromEntries(formData.entries());
        
        try {
            const response = await fetch('../api/inventory/save-item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(itemData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('item-modal').style.display = 'none';
                this.loadInventoryData();
                this.showNotification('Producto guardado exitosamente', 'success');
            } else {
                this.showNotification(data.error || 'Error al guardar producto', 'error');
            }
        } catch (error) {
            console.error('Error saving item:', error);
            this.showNotification('Error al guardar producto', 'error');
        }
    }
    
    async saveAdjustment() {
        const form = document.getElementById('adjustment-form');
        const itemId = form.dataset.itemId;
        
        const adjustmentData = {
            updates: [{
                productId: itemId,
                type: document.getElementById('adjustment-type').value,
                quantity: parseInt(document.getElementById('adjustment-quantity').value),
                unitCost: parseFloat(document.getElementById('adjustment-unit-cost').value) || 0,
                reason: document.getElementById('adjustment-reason').value,
                performedBy: 1 // This would be the current user ID
            }]
        };
        
        try {
            const response = await fetch('../api/inventory/update-stock.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(adjustmentData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('adjustment-modal').style.display = 'none';
                this.loadInventoryData();
                this.showNotification('Ajuste de stock aplicado exitosamente', 'success');
            } else {
                this.showNotification(data.error || 'Error al aplicar ajuste', 'error');
            }
        } catch (error) {
            console.error('Error saving adjustment:', error);
            this.showNotification('Error al aplicar ajuste', 'error');
        }
    }
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    setupInventoryManagerIntegration() {
        // Listen for inventory events
        window.inventoryManager.on('stockUpdate', (data) => {
            this.handleStockUpdate(data);
        });
        
        window.inventoryManager.on('lowStock', (data) => {
            this.showNotification(`Stock bajo: ${data.product.name}`, 'warning');
        });
        
        window.inventoryManager.on('outOfStock', (data) => {
            this.showNotification(`Sin stock: ${data.product.name}`, 'error');
        });
    }
    
    handleStockUpdate(data) {
        // Update the inventory table if the product is visible
        const row = document.querySelector(`tr[data-item-id="${data.productId}"]`);
        if (row) {
            // Update stock displays
            const availableStock = row.querySelector('.available-stock');
            if (availableStock) {
                availableStock.textContent = data.newStock;
            }
        }
    }
    
    async loadStockMovements() {
        // Implementation for loading stock movements
        console.log('Loading stock movements...');
    }
    
    async loadStockAlerts() {
        // Implementation for loading stock alerts
        console.log('Loading stock alerts...');
    }
    
    async loadPurchaseOrders() {
        // Implementation for loading purchase orders
        console.log('Loading purchase orders...');
    }
    
    async loadSuppliersTab() {
        // Implementation for loading suppliers tab
        console.log('Loading suppliers...');
    }
}

// Initialize inventory management
let inventoryManagement;
document.addEventListener('DOMContentLoaded', () => {
    inventoryManagement = new InventoryManagement();
});
</script>

<?php include 'admin-master-footer.php'; ?>