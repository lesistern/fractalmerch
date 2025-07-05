<?php
$pageTitle = '🛒 Gestión de Pedidos - Admin Panel';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-shopping-cart"></i> Gestión de Pedidos</h1>
    <p>Sistema avanzado de gestión de pedidos y seguimiento</p>
    
    <div class="page-actions">
        <button id="create-order-btn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Pedido
        </button>
        <button id="bulk-actions-btn" class="btn btn-secondary">
            <i class="fas fa-tasks"></i> Acciones Masivas
        </button>
        <button id="export-orders-btn" class="btn btn-outline">
            <i class="fas fa-download"></i> Exportar
        </button>
    </div>
</div>

<!-- Order Summary Cards -->
<div class="content-card">
    <h3><i class="fas fa-chart-bar"></i> Resumen de Pedidos</h3>
    <div class="order-summary">
        <div class="summary-card pending-orders">
            <div class="card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-content">
                <h3 id="pending-orders-count">-</h3>
                <p>Pedidos Pendientes</p>
                <span class="trend" id="pending-trend"></span>
            </div>
        </div>

        <div class="summary-card processing-orders">
            <div class="card-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="card-content">
                <h3 id="processing-orders-count">-</h3>
                <p>En Proceso</p>
                <span class="trend" id="processing-trend"></span>
            </div>
        </div>

        <div class="summary-card shipped-orders">
            <div class="card-icon">
                <i class="fas fa-truck"></i>
            </div>
            <div class="card-content">
                <h3 id="shipped-orders-count">-</h3>
                <p>Enviados</p>
                <span class="trend positive" id="shipped-trend">+8% hoy</span>
            </div>
        </div>

        <div class="summary-card delivered-orders">
            <div class="card-icon">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="card-content">
                <h3 id="delivered-orders-count">-</h3>
                <p>Entregados</p>
                <span class="trend positive" id="delivered-trend">+12% semana</span>
            </div>
        </div>

        <div class="summary-card total-revenue">
            <div class="card-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="card-content">
                <h3 id="total-revenue">-</h3>
                <p>Ingresos del Mes</p>
                <span class="trend positive" id="revenue-trend">+15% vs mes anterior</span>
            </div>
        </div>

        <div class="summary-card average-order">
            <div class="card-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="card-content">
                <h3 id="average-order-value">-</h3>
                <p>Valor Promedio</p>
                <span class="trend" id="average-trend"></span>
            </div>
        </div>
    </div>
</div>

<!-- Order Controls -->
<div class="content-card">
    <div class="order-controls">
        <div class="control-group">
            <label for="order-status-filter">Estado:</label>
            <select id="order-status-filter">
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="confirmed">Confirmado</option>
                <option value="processing">Procesando</option>
                <option value="production">En Producción</option>
                <option value="quality_check">Control Calidad</option>
                <option value="packaging">Empaquetado</option>
                <option value="shipped">Enviado</option>
                <option value="delivered">Entregado</option>
                <option value="cancelled">Cancelado</option>
                <option value="returned">Devuelto</option>
            </select>
        </div>

        <div class="control-group">
            <label for="payment-status-filter">Pago:</label>
            <select id="payment-status-filter">
                <option value="">Todos</option>
                <option value="pending">Pendiente</option>
                <option value="paid">Pagado</option>
                <option value="refunded">Reembolsado</option>
                <option value="failed">Fallido</option>
            </select>
        </div>

        <div class="control-group">
            <label for="date-range-filter">Rango de Fechas:</label>
            <input type="date" id="date-from-filter">
            <input type="date" id="date-to-filter">
        </div>

        <div class="control-group">
            <label for="search-orders">Buscar:</label>
            <input type="text" id="search-orders" placeholder="Número de orden, cliente, email...">
        </div>

        <div class="control-group">
            <button id="refresh-orders" class="btn btn-outline">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>
</div>

<!-- Order Tabs -->
<div class="content-card">
    <div class="order-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="orders">Pedidos</button>
            <button class="tab-btn" data-tab="timeline">Timeline</button>
            <button class="tab-btn" data-tab="tracking">Seguimiento</button>
            <button class="tab-btn" data-tab="analytics">Analytics</button>
        </div>

        <!-- Orders Tab -->
        <div id="orders-tab" class="tab-content active">
            <div class="orders-table-container">
                <table id="orders-table" class="orders-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-orders"></th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Estimada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="orders-tbody">
                        <!-- Orders will be loaded here -->
                    </tbody>
                </table>
            </div>

            <div class="table-pagination">
                <div class="pagination-info">
                    <span id="orders-showing">Mostrando 0 de 0 pedidos</span>
                </div>
                <div class="pagination-controls">
                    <button id="prev-orders-page" class="btn btn-sm" disabled>← Anterior</button>
                    <span id="orders-page-numbers"></span>
                    <button id="next-orders-page" class="btn btn-sm" disabled>Siguiente →</button>
                </div>
            </div>
        </div>

        <!-- Timeline Tab -->
        <div id="timeline-tab" class="tab-content">
            <div class="timeline-container">
                <div class="timeline-controls">
                    <div class="control-group">
                        <label for="timeline-order-select">Pedido:</label>
                        <select id="timeline-order-select">
                            <option value="">Seleccionar pedido...</option>
                        </select>
                    </div>
                </div>
                
                <div class="timeline-view" id="timeline-view">
                    <!-- Timeline will be rendered here -->
                </div>
            </div>
        </div>

        <!-- Tracking Tab -->
        <div id="tracking-tab" class="tab-content">
            <div class="tracking-container">
                <div class="tracking-search">
                    <div class="control-group">
                        <label for="tracking-number">Número de Seguimiento:</label>
                        <input type="text" id="tracking-number" placeholder="Ingrese número de tracking...">
                        <button id="track-order-btn" class="btn btn-primary">Rastrear</button>
                    </div>
                </div>
                
                <div class="tracking-results" id="tracking-results">
                    <!-- Tracking results will be displayed here -->
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div id="analytics-tab" class="tab-content">
            <div class="analytics-dashboard">
                <div class="chart-container">
                    <canvas id="orders-chart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="revenue-chart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="status-chart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="customer-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
    <div id="order-modal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3 id="order-modal-title">Detalles del Pedido</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="order-details">
                    <!-- Order details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="status-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Actualizar Estado</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="status-form">
                    <div class="status-info">
                        <h4 id="status-order-number"></h4>
                        <p>Estado Actual: <span id="status-current-state"></span></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="new-status">Nuevo Estado:</label>
                        <select id="new-status" required>
                            <option value="">Seleccionar estado...</option>
                            <option value="pending">Pendiente</option>
                            <option value="confirmed">Confirmado</option>
                            <option value="processing">Procesando</option>
                            <option value="production">En Producción</option>
                            <option value="quality_check">Control de Calidad</option>
                            <option value="packaging">Empaquetado</option>
                            <option value="shipped">Enviado</option>
                            <option value="delivered">Entregado</option>
                            <option value="cancelled">Cancelado</option>
                            <option value="returned">Devuelto</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status-notes">Notas (opcional):</label>
                        <textarea id="status-notes" rows="3" placeholder="Comentarios sobre el cambio de estado..."></textarea>
                    </div>
                    
                    <div class="form-group" id="tracking-group" style="display: none;">
                        <label for="tracking-number-input">Número de Tracking:</label>
                        <input type="text" id="tracking-number-input" placeholder="Número de seguimiento">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancel-status">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<style>
.order-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.pending-orders .card-icon { background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); }
.processing-orders .card-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.shipped-orders .card-icon { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.delivered-orders .card-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.total-revenue .card-icon { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
.average-order .card-icon { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }

.order-controls {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    flex-wrap: wrap;
}

.orders-table-container {
    overflow-x: auto;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.orders-table th,
.orders-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
    white-space: nowrap;
}

.orders-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    position: sticky;
    top: 0;
    z-index: 10;
}

.orders-table tr:hover {
    background: #f8f9fa;
}

.order-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.order-status.pending { background: #fff3cd; color: #856404; }
.order-status.confirmed { background: #d1ecf1; color: #0c5460; }
.order-status.processing { background: #e2e3f3; color: #383d41; }
.order-status.production { background: #f3e5f5; color: #6f42c1; }
.order-status.quality_check { background: #d4edda; color: #155724; }
.order-status.packaging { background: #d1ecf1; color: #0c5460; }
.order-status.shipped { background: #d4edda; color: #155724; }
.order-status.delivered { background: #d4edda; color: #155724; }
.order-status.cancelled { background: #f8d7da; color: #721c24; }
.order-status.returned { background: #ffeaa7; color: #856404; }

.payment-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.payment-status.paid { background: #d4edda; color: #155724; }
.payment-status.pending { background: #fff3cd; color: #856404; }
.payment-status.failed { background: #f8d7da; color: #721c24; }
.payment-status.refunded { background: #d1ecf1; color: #0c5460; }

.order-actions {
    display: flex;
    gap: 8px;
}

.timeline-container,
.tracking-container {
    padding: 20px;
}

.timeline-view {
    margin-top: 30px;
}

.timeline-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-left: 3px solid #e9ecef;
    padding-left: 20px;
    margin-left: 10px;
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 20px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #e9ecef;
}

.timeline-item.completed::before {
    background: #28a745;
}

.timeline-item.active::before {
    background: #007bff;
}

.timeline-content {
    flex: 1;
}

.timeline-date {
    font-size: 12px;
    color: #6c757d;
    margin-left: auto;
}

.tracking-search {
    display: flex;
    gap: 20px;
    align-items: end;
    margin-bottom: 30px;
}

.tracking-results {
    min-height: 200px;
}

.analytics-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.chart-container {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-content.large {
    max-width: 800px;
    width: 95%;
}

.order-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.detail-section {
    margin-bottom: 30px;
}

.detail-section h4 {
    margin-bottom: 15px;
    color: #2c3e50;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 8px;
}

.item-detail {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.status-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.status-info h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

@media (max-width: 768px) {
    .order-summary {
        grid-template-columns: 1fr;
    }
    
    .order-controls {
        flex-direction: column;
    }
    
    .analytics-dashboard {
        grid-template-columns: 1fr;
    }
    
    .order-detail-grid {
        grid-template-columns: 1fr;
    }
    
    .tracking-search {
        flex-direction: column;
    }
}
</style>

<script src="../assets/js/order-management.js?v=<?php echo time(); ?>"></script>
<script>
class OrderManagementAdmin {
    constructor() {
        this.currentTab = 'orders';
        this.currentPage = 1;
        this.itemsPerPage = 25;
        this.currentFilters = {};
        this.orders = new Map();
        this.selectedOrders = new Set();
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadOrdersData();
        this.setupModals();
        this.setupCharts();
        
        // Initialize order management if available
        if (window.orderManagement) {
            this.setupOrderManagementIntegration();
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
        document.getElementById('order-status-filter').addEventListener('change', () => this.applyFilters());
        document.getElementById('payment-status-filter').addEventListener('change', () => this.applyFilters());
        document.getElementById('search-orders').addEventListener('input', () => this.applyFilters());
        
        // Refresh button
        document.getElementById('refresh-orders').addEventListener('click', () => {
            this.loadOrdersData();
        });
        
        // Select all checkbox
        document.getElementById('select-all-orders').addEventListener('change', (e) => {
            this.toggleSelectAll(e.target.checked);
        });
        
        // Status form new status change
        document.getElementById('new-status').addEventListener('change', (e) => {
            const trackingGroup = document.getElementById('tracking-group');
            if (e.target.value === 'shipped') {
                trackingGroup.style.display = 'block';
            } else {
                trackingGroup.style.display = 'none';
            }
        });
        
        // Track order button
        document.getElementById('track-order-btn').addEventListener('click', () => {
            this.trackOrder();
        });
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
            case 'timeline':
                this.loadTimeline();
                break;
            case 'tracking':
                this.loadTracking();
                break;
            case 'analytics':
                this.updateCharts();
                break;
        }
    }
    
    async loadOrdersData() {
        try {
            const response = await fetch('../api/orders/get-orders.php');
            const data = await response.json();
            
            if (data.success) {
                this.orders = new Map(data.orders.map(o => [o.id, o]));
                this.updateSummaryCards(data.summary);
                this.renderOrdersTable();
                this.updatePagination();
                this.populateTimelineSelect();
            }
        } catch (error) {
            console.error('Error loading orders:', error);
        }
    }
    
    updateSummaryCards(summary) {
        document.getElementById('pending-orders-count').textContent = summary.pending || 0;
        document.getElementById('processing-orders-count').textContent = summary.processing || 0;
        document.getElementById('shipped-orders-count').textContent = summary.shipped || 0;
        document.getElementById('delivered-orders-count').textContent = summary.delivered || 0;
        document.getElementById('total-revenue').textContent = '$' + (summary.total_revenue || 0).toLocaleString();
        document.getElementById('average-order-value').textContent = '$' + (summary.average_order_value || 0).toLocaleString();
    }
    
    renderOrdersTable() {
        const tbody = document.getElementById('orders-tbody');
        const filteredOrders = this.getFilteredOrders();
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const pageOrders = filteredOrders.slice(startIndex, endIndex);
        
        tbody.innerHTML = pageOrders.map(order => `
            <tr data-order-id="${order.id}">
                <td><input type="checkbox" class="order-checkbox" value="${order.id}"></td>
                <td><strong>#${order.orderNumber}</strong></td>
                <td>
                    <strong>${order.customerName}</strong>
                    <br><small>${order.customerEmail}</small>
                </td>
                <td><span class="order-status ${order.status}">${this.getStatusText(order.status)}</span></td>
                <td><span class="payment-status ${order.paymentStatus}">${this.getPaymentStatusText(order.paymentStatus)}</span></td>
                <td>${order.items.length} items</td>
                <td><strong>$${order.totalAmount.toLocaleString()}</strong></td>
                <td>${new Date(order.createdAt).toLocaleDateString()}</td>
                <td>${order.estimatedDelivery ? new Date(order.estimatedDelivery).toLocaleDateString() : '-'}</td>
                <td>
                    <div class="order-actions">
                        <button class="btn btn-sm btn-primary" onclick="orderManagementAdmin.viewOrder(${order.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="orderManagementAdmin.updateStatus(${order.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline" onclick="orderManagementAdmin.printOrder(${order.id})">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
        // Update showing info
        document.getElementById('orders-showing').textContent = 
            `Mostrando ${startIndex + 1}-${Math.min(endIndex, filteredOrders.length)} de ${filteredOrders.length} pedidos`;
    }
    
    getStatusText(status) {
        const statusMap = {
            'pending': 'Pendiente',
            'confirmed': 'Confirmado',
            'processing': 'Procesando',
            'production': 'Producción',
            'quality_check': 'Calidad',
            'packaging': 'Empaque',
            'shipped': 'Enviado',
            'delivered': 'Entregado',
            'cancelled': 'Cancelado',
            'returned': 'Devuelto'
        };
        return statusMap[status] || status;
    }
    
    getPaymentStatusText(status) {
        const statusMap = {
            'pending': 'Pendiente',
            'paid': 'Pagado',
            'failed': 'Falló',
            'refunded': 'Reembolsado'
        };
        return statusMap[status] || status;
    }
    
    getFilteredOrders() {
        let filtered = Array.from(this.orders.values());
        
        // Apply status filter
        if (this.currentFilters.status) {
            filtered = filtered.filter(order => order.status === this.currentFilters.status);
        }
        
        // Apply payment filter
        if (this.currentFilters.payment) {
            filtered = filtered.filter(order => order.paymentStatus === this.currentFilters.payment);
        }
        
        // Apply search filter
        if (this.currentFilters.search) {
            const search = this.currentFilters.search.toLowerCase();
            filtered = filtered.filter(order => 
                order.orderNumber.toLowerCase().includes(search) ||
                order.customerName.toLowerCase().includes(search) ||
                order.customerEmail.toLowerCase().includes(search)
            );
        }
        
        return filtered;
    }
    
    applyFilters() {
        this.currentFilters = {
            status: document.getElementById('order-status-filter').value,
            payment: document.getElementById('payment-status-filter').value,
            search: document.getElementById('search-orders').value
        };
        
        this.currentPage = 1;
        this.renderOrdersTable();
        this.updatePagination();
    }
    
    updatePagination() {
        const filteredCount = this.getFilteredOrders().length;
        const totalPages = Math.ceil(filteredCount / this.itemsPerPage);
        
        document.getElementById('prev-orders-page').disabled = this.currentPage <= 1;
        document.getElementById('next-orders-page').disabled = this.currentPage >= totalPages;
        
        // Update page numbers
        const pageNumbers = document.getElementById('orders-page-numbers');
        pageNumbers.innerHTML = `Página ${this.currentPage} de ${totalPages}`;
    }
    
    viewOrder(orderId) {
        const order = this.orders.get(orderId);
        if (!order) return;
        
        const details = `
            <div class="order-detail-grid">
                <div class="detail-section">
                    <h4>Información del Pedido</h4>
                    <p><strong>Número:</strong> #${order.orderNumber}</p>
                    <p><strong>Estado:</strong> ${this.getStatusText(order.status)}</p>
                    <p><strong>Pago:</strong> ${this.getPaymentStatusText(order.paymentStatus)}</p>
                    <p><strong>Total:</strong> $${order.totalAmount.toLocaleString()}</p>
                    <p><strong>Fecha:</strong> ${new Date(order.createdAt).toLocaleString()}</p>
                    <p><strong>Método de Pago:</strong> ${order.paymentMethod || 'N/A'}</p>
                    <p><strong>Método de Envío:</strong> ${order.shippingMethod || 'N/A'}</p>
                </div>
                
                <div class="detail-section">
                    <h4>Cliente</h4>
                    <p><strong>Nombre:</strong> ${order.customerName}</p>
                    <p><strong>Email:</strong> ${order.customerEmail}</p>
                    ${order.shippingAddress ? `
                        <p><strong>Dirección de Envío:</strong><br>
                        ${order.shippingAddress.address1}<br>
                        ${order.shippingAddress.city}, ${order.shippingAddress.state}<br>
                        ${order.shippingAddress.zip}</p>
                    ` : ''}
                </div>
                
                <div class="detail-section">
                    <h4>Items del Pedido</h4>
                    ${order.items.map(item => `
                        <div class="item-detail">
                            <p><strong>${item.name}</strong></p>
                            <p>Cantidad: ${item.quantity}</p>
                            <p>Precio: $${item.price.toLocaleString()}</p>
                            <p>Subtotal: $${(item.quantity * item.price).toLocaleString()}</p>
                            ${item.isCustom ? '<span class="badge badge-warning">Personalizado</span>' : ''}
                        </div>
                    `).join('')}
                </div>
                
                <div class="detail-section">
                    <h4>Historial de Estados</h4>
                    ${order.statusHistory ? order.statusHistory.map(status => `
                        <div class="status-history-item">
                            <p><strong>${this.getStatusText(status.status)}</strong></p>
                            <p>${new Date(status.timestamp).toLocaleString()}</p>
                            ${status.notes ? `<p><em>${status.notes}</em></p>` : ''}
                        </div>
                    `).join('') : '<p>No hay historial disponible</p>'}
                </div>
            </div>
        `;
        
        document.getElementById('order-details').innerHTML = details;
        document.getElementById('order-modal-title').textContent = `Pedido #${order.orderNumber}`;
        document.getElementById('order-modal').style.display = 'block';
    }
    
    updateStatus(orderId) {
        const order = this.orders.get(orderId);
        if (!order) return;
        
        document.getElementById('status-order-number').textContent = `Pedido #${order.orderNumber}`;
        document.getElementById('status-current-state').textContent = this.getStatusText(order.status);
        document.getElementById('status-form').dataset.orderId = orderId;
        document.getElementById('status-form').reset();
        
        document.getElementById('status-modal').style.display = 'block';
    }
    
    async submitStatusUpdate() {
        const form = document.getElementById('status-form');
        const orderId = form.dataset.orderId;
        const newStatus = document.getElementById('new-status').value;
        const notes = document.getElementById('status-notes').value;
        const trackingNumber = document.getElementById('tracking-number-input').value;
        
        try {
            if (window.orderManagement) {
                await window.orderManagement.updateOrderStatus(orderId, newStatus, notes);
                
                if (trackingNumber && newStatus === 'shipped') {
                    // Update tracking number
                    const order = window.orderManagement.getOrder(orderId);
                    if (order) {
                        order.trackingNumber = trackingNumber;
                    }
                }
                
                document.getElementById('status-modal').style.display = 'none';
                this.loadOrdersData();
                this.showNotification('Estado actualizado exitosamente', 'success');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            this.showNotification('Error al actualizar estado', 'error');
        }
    }
    
    populateTimelineSelect() {
        const select = document.getElementById('timeline-order-select');
        select.innerHTML = '<option value="">Seleccionar pedido...</option>';
        
        this.orders.forEach(order => {
            const option = new Option(`#${order.orderNumber} - ${order.customerName}`, order.id);
            select.appendChild(option);
        });
        
        select.addEventListener('change', (e) => {
            if (e.target.value) {
                this.showTimeline(e.target.value);
            }
        });
    }
    
    showTimeline(orderId) {
        const order = this.orders.get(parseInt(orderId));
        if (!order) return;
        
        const container = document.getElementById('timeline-view');
        const statusHistory = order.statusHistory || [];
        
        container.innerHTML = statusHistory.map(status => `
            <div class="timeline-item completed">
                <div class="timeline-content">
                    <h5>${this.getStatusText(status.status)}</h5>
                    <p>${status.notes || 'Estado actualizado'}</p>
                </div>
                <div class="timeline-date">
                    ${new Date(status.timestamp).toLocaleString()}
                </div>
            </div>
        `).join('');
    }
    
    trackOrder() {
        const trackingNumber = document.getElementById('tracking-number').value;
        if (!trackingNumber) return;
        
        // Find order by tracking number
        const order = Array.from(this.orders.values()).find(o => o.trackingNumber === trackingNumber);
        
        const resultsContainer = document.getElementById('tracking-results');
        
        if (order) {
            resultsContainer.innerHTML = `
                <div class="tracking-result">
                    <h4>Pedido #${order.orderNumber}</h4>
                    <p><strong>Estado:</strong> ${this.getStatusText(order.status)}</p>
                    <p><strong>Cliente:</strong> ${order.customerName}</p>
                    <p><strong>Fecha de Envío:</strong> ${order.shippedAt ? new Date(order.shippedAt).toLocaleDateString() : 'No enviado'}</p>
                    <p><strong>Entrega Estimada:</strong> ${order.estimatedDelivery ? new Date(order.estimatedDelivery).toLocaleDateString() : 'No disponible'}</p>
                </div>
            `;
        } else {
            resultsContainer.innerHTML = `
                <div class="tracking-result">
                    <p>No se encontró ningún pedido con el número de tracking: <strong>${trackingNumber}</strong></p>
                </div>
            `;
        }
    }
    
    setupModals() {
        // Close buttons
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.onclick = () => {
                closeBtn.closest('.modal').style.display = 'none';
            };
        });
        
        // Status form submission
        document.getElementById('status-form').onsubmit = (e) => {
            e.preventDefault();
            this.submitStatusUpdate();
        };
        
        document.getElementById('cancel-status').onclick = () => {
            document.getElementById('status-modal').style.display = 'none';
        };
        
        // Click outside to close
        window.onclick = (event) => {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    }
    
    setupCharts() {
        // Setup Chart.js charts for analytics
        if (typeof Chart !== 'undefined') {
            this.setupOrdersChart();
            this.setupRevenueChart();
            this.setupStatusChart();
            this.setupCustomerChart();
        }
    }
    
    setupOrdersChart() {
        const ctx = document.getElementById('orders-chart');
        if (!ctx) return;
        
        this.ordersChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Pedidos',
                    data: [65, 75, 80, 85, 90, 95],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Pedidos por Mes'
                    }
                }
            }
        });
    }
    
    setupRevenueChart() {
        const ctx = document.getElementById('revenue-chart');
        if (!ctx) return;
        
        this.revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Ingresos ($)',
                    data: [12000, 15000, 18000, 22000, 25000, 28000],
                    backgroundColor: '#28a745'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Ingresos por Mes'
                    }
                }
            }
        });
    }
    
    setupStatusChart() {
        const ctx = document.getElementById('status-chart');
        if (!ctx) return;
        
        this.statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Entregados', 'Enviados', 'Procesando', 'Pendientes'],
                datasets: [{
                    data: [60, 20, 15, 5],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribución por Estado'
                    }
                }
            }
        });
    }
    
    setupCustomerChart() {
        const ctx = document.getElementById('customer-chart');
        if (!ctx) return;
        
        this.customerChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Nuevos', 'Recurrentes'],
                datasets: [{
                    data: [30, 70],
                    backgroundColor: ['#ff6384', '#36a2eb']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Tipos de Cliente'
                    }
                }
            }
        });
    }
    
    setupOrderManagementIntegration() {
        // Listen for order events
        window.orderManagement.on('orderCreated', (data) => {
            this.showNotification(`Nuevo pedido creado: ${data.order.orderNumber}`, 'info');
            this.loadOrdersData();
        });
        
        window.orderManagement.on('statusChanged', (data) => {
            this.showNotification(`Estado actualizado: ${data.order.orderNumber}`, 'success');
            this.loadOrdersData();
        });
        
        window.orderManagement.on('orderShipped', (data) => {
            this.showNotification(`Pedido enviado: ${data.order.orderNumber}`, 'success');
            this.loadOrdersData();
        });
        
        window.orderManagement.on('orderDelivered', (data) => {
            this.showNotification(`Pedido entregado: ${data.order.orderNumber}`, 'success');
            this.loadOrdersData();
        });
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
            if (checked) {
                this.selectedOrders.add(checkbox.value);
            } else {
                this.selectedOrders.delete(checkbox.value);
            }
        });
    }
    
    printOrder(orderId) {
        // Implementation for printing order
        window.open(`/admin/print-order.php?id=${orderId}`, '_blank');
    }
    
    updateCharts() {
        // Update chart data
        if (this.ordersChart) {
            this.ordersChart.update();
        }
        if (this.revenueChart) {
            this.revenueChart.update();
        }
        if (this.statusChart) {
            this.statusChart.update();
        }
        if (this.customerChart) {
            this.customerChart.update();
        }
    }
    
    async loadTimeline() {
        // Load timeline data
        console.log('Loading timeline...');
    }
    
    async loadTracking() {
        // Load tracking data
        console.log('Loading tracking...');
    }
}

// Initialize order management admin
let orderManagementAdmin;
document.addEventListener('DOMContentLoaded', () => {
    orderManagementAdmin = new OrderManagementAdmin();
});
</script>

<?php include 'admin-master-footer.php'; ?>