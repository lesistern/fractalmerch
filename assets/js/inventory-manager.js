/**
 * FractalMerch Real-Time Inventory Management System
 * Advanced inventory tracking with real-time updates, low stock alerts, and supplier integration
 */

class InventoryManager {
    constructor(config = {}) {
        this.config = {
            apiBaseUrl: config.apiBaseUrl || '/api/inventory',
            realTimeUpdates: config.realTimeUpdates !== false,
            updateInterval: config.updateInterval || 30000, // 30 seconds
            lowStockThreshold: config.lowStockThreshold || 10,
            criticalStockThreshold: config.criticalStockThreshold || 5,
            enableNotifications: config.enableNotifications !== false,
            autoReorder: config.autoReorder || false,
            reorderQuantity: config.reorderQuantity || 50,
            ...config
        };
        
        this.inventory = new Map();
        this.suppliers = new Map();
        this.pendingOrders = new Map();
        this.stockMovements = [];
        this.alerts = [];
        this.realTimeConnection = null;
        this.updateTimer = null;
        
        this.eventHandlers = {
            stockUpdate: [],
            lowStock: [],
            outOfStock: [],
            reorderTriggered: [],
            supplierUpdate: []
        };
        
        this.init();
    }
    
    init() {
        console.log('Initializing Inventory Manager...');
        
        this.loadInitialInventory();
        this.setupRealTimeUpdates();
        this.setupEventListeners();
        this.startPeriodicUpdates();
        this.loadSuppliers();
        this.setupNotifications();
        
        console.log('Inventory Manager initialized successfully');
    }
    
    /**
     * Load initial inventory data
     */
    async loadInitialInventory() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/get-inventory.php`);
            const data = await response.json();
            
            if (data.success) {
                data.inventory.forEach(item => {
                    this.inventory.set(item.id, {
                        id: item.id,
                        sku: item.sku,
                        name: item.name,
                        category: item.category,
                        currentStock: parseInt(item.current_stock),
                        reservedStock: parseInt(item.reserved_stock),
                        availableStock: parseInt(item.available_stock),
                        reorderPoint: parseInt(item.reorder_point),
                        reorderQuantity: parseInt(item.reorder_quantity),
                        unitCost: parseFloat(item.unit_cost),
                        supplierId: item.supplier_id,
                        location: item.location,
                        lastUpdated: new Date(item.last_updated),
                        movements: []
                    });
                });
                
                console.log(`Loaded ${this.inventory.size} inventory items`);
                this.checkStockLevels();
                this.triggerEvent('inventoryLoaded', { count: this.inventory.size });
            }
        } catch (error) {
            console.error('Error loading initial inventory:', error);
        }
    }
    
    /**
     * Setup real-time inventory updates
     */
    setupRealTimeUpdates() {
        if (!this.config.realTimeUpdates) return;
        
        // This would typically use WebSockets or Server-Sent Events
        // For now, we'll simulate with periodic polling
        console.log('Setting up real-time inventory updates...');
    }
    
    /**
     * Setup event listeners for inventory changes
     */
    setupEventListeners() {
        // Listen for cart updates
        document.addEventListener('cartUpdated', (event) => {
            this.handleCartUpdate(event.detail);
        });
        
        // Listen for order completions
        document.addEventListener('orderCompleted', (event) => {
            this.handleOrderCompletion(event.detail);
        });
        
        // Listen for product purchases
        document.addEventListener('productPurchased', (event) => {
            this.handleProductPurchase(event.detail);
        });
        
        // Listen for inventory adjustments
        document.addEventListener('inventoryAdjustment', (event) => {
            this.handleInventoryAdjustment(event.detail);
        });
    }
    
    /**
     * Start periodic inventory updates
     */
    startPeriodicUpdates() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
        }
        
        this.updateTimer = setInterval(() => {
            this.syncInventoryUpdates();
        }, this.config.updateInterval);
    }
    
    /**
     * Sync inventory updates from server
     */
    async syncInventoryUpdates() {
        try {
            const lastSync = localStorage.getItem('lastInventorySync') || '0';
            const response = await fetch(`${this.config.apiBaseUrl}/sync-updates.php?since=${lastSync}`);
            const data = await response.json();
            
            if (data.success && data.updates.length > 0) {
                data.updates.forEach(update => {
                    this.applyInventoryUpdate(update);
                });
                
                localStorage.setItem('lastInventorySync', Date.now().toString());
                console.log(`Applied ${data.updates.length} inventory updates`);
            }
        } catch (error) {
            console.error('Error syncing inventory updates:', error);
        }
    }
    
    /**
     * Apply inventory update
     */
    applyInventoryUpdate(update) {
        const item = this.inventory.get(update.product_id);
        if (!item) return;
        
        const oldStock = item.currentStock;
        
        switch (update.type) {
            case 'stock_change':
                item.currentStock = parseInt(update.new_stock);
                item.availableStock = item.currentStock - item.reservedStock;
                break;
                
            case 'reservation':
                item.reservedStock = parseInt(update.reserved_stock);
                item.availableStock = item.currentStock - item.reservedStock;
                break;
                
            case 'purchase':
                item.currentStock -= parseInt(update.quantity);
                item.reservedStock = Math.max(0, item.reservedStock - parseInt(update.quantity));
                item.availableStock = item.currentStock - item.reservedStock;
                break;
                
            case 'restock':
                item.currentStock += parseInt(update.quantity);
                item.availableStock = item.currentStock - item.reservedStock;
                break;
        }
        
        item.lastUpdated = new Date(update.timestamp);
        
        // Record movement
        this.recordStockMovement({
            productId: update.product_id,
            type: update.type,
            quantity: parseInt(update.quantity || 0),
            oldStock: oldStock,
            newStock: item.currentStock,
            reason: update.reason || '',
            timestamp: new Date(update.timestamp)
        });
        
        // Check stock levels
        this.checkProductStockLevel(item);
        
        // Trigger events
        this.triggerEvent('stockUpdate', {
            productId: item.id,
            oldStock: oldStock,
            newStock: item.currentStock,
            change: item.currentStock - oldStock
        });
        
        // Update UI elements
        this.updateStockDisplays(item);
    }
    
    /**
     * Handle cart updates (reserve/unreserve stock)
     */
    async handleCartUpdate(cartData) {
        const reservations = [];
        
        cartData.items.forEach(item => {
            const product = this.inventory.get(item.productId);
            if (product) {
                const newReservation = item.quantity;
                const oldReservation = this.getCartReservation(item.productId, cartData.sessionId);
                const reservationChange = newReservation - oldReservation;
                
                if (reservationChange !== 0) {
                    reservations.push({
                        productId: item.productId,
                        sessionId: cartData.sessionId,
                        quantity: newReservation,
                        change: reservationChange
                    });
                }
            }
        });
        
        if (reservations.length > 0) {
            await this.updateReservations(reservations);
        }
    }
    
    /**
     * Handle product purchase (remove from stock)
     */
    async handleProductPurchase(purchaseData) {
        const stockUpdates = [];
        
        purchaseData.items.forEach(item => {
            const product = this.inventory.get(item.productId);
            if (product && product.availableStock >= item.quantity) {
                stockUpdates.push({
                    productId: item.productId,
                    quantity: item.quantity,
                    orderId: purchaseData.orderId,
                    type: 'purchase'
                });
            }
        });
        
        if (stockUpdates.length > 0) {
            await this.processStockUpdates(stockUpdates);
        }
    }
    
    /**
     * Process stock updates
     */
    async processStockUpdates(updates) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/update-stock.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ updates })
            });
            
            const data = await response.json();
            
            if (data.success) {
                updates.forEach(update => {
                    this.applyInventoryUpdate({
                        product_id: update.productId,
                        type: update.type,
                        quantity: update.quantity,
                        new_stock: data.updatedStock[update.productId],
                        timestamp: new Date().toISOString(),
                        reason: `Order ${update.orderId || 'N/A'}`
                    });
                });
            }
        } catch (error) {
            console.error('Error processing stock updates:', error);
        }
    }
    
    /**
     * Update stock reservations
     */
    async updateReservations(reservations) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/update-reservations.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reservations })
            });
            
            const data = await response.json();
            
            if (data.success) {
                reservations.forEach(reservation => {
                    const product = this.inventory.get(reservation.productId);
                    if (product) {
                        product.reservedStock = data.updatedReservations[reservation.productId] || 0;
                        product.availableStock = product.currentStock - product.reservedStock;
                        this.updateStockDisplays(product);
                    }
                });
            }
        } catch (error) {
            console.error('Error updating reservations:', error);
        }
    }
    
    /**
     * Check stock levels for all products
     */
    checkStockLevels() {
        this.inventory.forEach(product => {
            this.checkProductStockLevel(product);
        });
    }
    
    /**
     * Check stock level for specific product
     */
    checkProductStockLevel(product) {
        const { availableStock, reorderPoint } = product;
        
        if (availableStock <= 0) {
            this.handleOutOfStock(product);
        } else if (availableStock <= this.config.criticalStockThreshold) {
            this.handleCriticalStock(product);
        } else if (availableStock <= this.config.lowStockThreshold || availableStock <= reorderPoint) {
            this.handleLowStock(product);
        }
    }
    
    /**
     * Handle out of stock situation
     */
    handleOutOfStock(product) {
        console.warn(`Product ${product.name} (${product.sku}) is OUT OF STOCK`);
        
        this.addAlert({
            type: 'out_of_stock',
            priority: 'critical',
            productId: product.id,
            message: `${product.name} is out of stock`,
            timestamp: new Date()
        });
        
        this.triggerEvent('outOfStock', { product });
        
        // Disable product in UI
        this.disableProductInUI(product.id);
        
        // Auto-reorder if enabled
        if (this.config.autoReorder) {
            this.triggerReorder(product);
        }
    }
    
    /**
     * Handle critical stock level
     */
    handleCriticalStock(product) {
        console.warn(`Product ${product.name} (${product.sku}) has CRITICAL stock: ${product.availableStock}`);
        
        this.addAlert({
            type: 'critical_stock',
            priority: 'high',
            productId: product.id,
            message: `${product.name} has critical stock (${product.availableStock} remaining)`,
            timestamp: new Date()
        });
        
        this.triggerEvent('criticalStock', { product });
        
        // Show warning in UI
        this.showStockWarning(product.id, 'critical');
    }
    
    /**
     * Handle low stock situation
     */
    handleLowStock(product) {
        console.warn(`Product ${product.name} (${product.sku}) has LOW stock: ${product.availableStock}`);
        
        this.addAlert({
            type: 'low_stock',
            priority: 'medium',
            productId: product.id,
            message: `${product.name} has low stock (${product.availableStock} remaining)`,
            timestamp: new Date()
        });
        
        this.triggerEvent('lowStock', { product });
        
        // Show warning in UI
        this.showStockWarning(product.id, 'low');
        
        // Consider reorder
        if (product.availableStock <= product.reorderPoint) {
            this.considerReorder(product);
        }
    }
    
    /**
     * Trigger automatic reorder
     */
    async triggerReorder(product) {
        if (this.pendingOrders.has(product.id)) {
            console.log(`Reorder already pending for ${product.name}`);
            return;
        }
        
        const supplier = this.suppliers.get(product.supplierId);
        if (!supplier) {
            console.error(`No supplier found for product ${product.name}`);
            return;
        }
        
        const reorderData = {
            productId: product.id,
            supplierId: product.supplierId,
            quantity: product.reorderQuantity || this.config.reorderQuantity,
            unitCost: product.unitCost,
            totalCost: (product.reorderQuantity || this.config.reorderQuantity) * product.unitCost,
            urgentOrder: product.availableStock <= 0,
            timestamp: new Date()
        };
        
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/create-reorder.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(reorderData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.pendingOrders.set(product.id, {
                    orderId: data.orderId,
                    ...reorderData,
                    status: 'pending'
                });
                
                console.log(`Reorder triggered for ${product.name}: ${reorderData.quantity} units`);
                
                this.triggerEvent('reorderTriggered', { product, reorderData });
                
                // Send to supplier if API available
                if (supplier.apiEndpoint) {
                    this.sendReorderToSupplier(supplier, reorderData);
                }
            }
        } catch (error) {
            console.error('Error triggering reorder:', error);
        }
    }
    
    /**
     * Send reorder to supplier API
     */
    async sendReorderToSupplier(supplier, reorderData) {
        try {
            const response = await fetch(supplier.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${supplier.apiKey}`,
                    'X-Client-ID': supplier.clientId
                },
                body: JSON.stringify({
                    action: 'create_order',
                    products: [{
                        sku: reorderData.productId,
                        quantity: reorderData.quantity,
                        unit_price: reorderData.unitCost
                    }],
                    delivery_address: supplier.deliveryAddress,
                    urgent: reorderData.urgentOrder
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log(`Reorder sent to supplier ${supplier.name}: Order ${data.supplierOrderId}`);
                
                // Update pending order with supplier order ID
                const pendingOrder = this.pendingOrders.get(reorderData.productId);
                if (pendingOrder) {
                    pendingOrder.supplierOrderId = data.supplierOrderId;
                    pendingOrder.estimatedDelivery = data.estimatedDelivery;
                }
            }
        } catch (error) {
            console.error('Error sending reorder to supplier:', error);
        }
    }
    
    /**
     * Load suppliers configuration
     */
    async loadSuppliers() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/get-suppliers.php`);
            const data = await response.json();
            
            if (data.success) {
                data.suppliers.forEach(supplier => {
                    this.suppliers.set(supplier.id, {
                        id: supplier.id,
                        name: supplier.name,
                        apiEndpoint: supplier.api_endpoint,
                        apiKey: supplier.api_key,
                        clientId: supplier.client_id,
                        deliveryAddress: supplier.delivery_address,
                        leadTime: parseInt(supplier.lead_time_days),
                        minimumOrder: parseFloat(supplier.minimum_order),
                        isActive: supplier.is_active === '1'
                    });
                });
                
                console.log(`Loaded ${this.suppliers.size} suppliers`);
            }
        } catch (error) {
            console.error('Error loading suppliers:', error);
        }
    }
    
    /**
     * Setup notifications system
     */
    setupNotifications() {
        if (!this.config.enableNotifications) return;
        
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
    
    /**
     * Send notification
     */
    sendNotification(title, message, type = 'info') {
        if (!this.config.enableNotifications || !('Notification' in window)) return;
        
        if (Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: message,
                icon: '/assets/images/icon.png',
                tag: `inventory-${type}-${Date.now()}`,
                requireInteraction: type === 'critical'
            });
            
            setTimeout(() => {
                notification.close();
            }, 5000);
        }
    }
    
    /**
     * Add alert to system
     */
    addAlert(alert) {
        this.alerts.unshift(alert);
        
        // Keep only last 100 alerts
        if (this.alerts.length > 100) {
            this.alerts = this.alerts.slice(0, 100);
        }
        
        // Send notification for critical alerts
        if (alert.priority === 'critical') {
            this.sendNotification('Inventory Alert', alert.message, 'critical');
        }
        
        // Update alerts display
        this.updateAlertsDisplay();
    }
    
    /**
     * Record stock movement
     */
    recordStockMovement(movement) {
        this.stockMovements.unshift(movement);
        
        // Keep only last 1000 movements in memory
        if (this.stockMovements.length > 1000) {
            this.stockMovements = this.stockMovements.slice(0, 1000);
        }
        
        // Update movements display if visible
        this.updateMovementsDisplay();
    }
    
    /**
     * Update stock displays in UI
     */
    updateStockDisplays(product) {
        // Update product card stock display
        const stockElements = document.querySelectorAll(`[data-product-id="${product.id}"] .stock-display`);
        stockElements.forEach(element => {
            element.textContent = `${product.availableStock} disponibles`;
            
            // Update stock status classes
            element.classList.remove('stock-ok', 'stock-low', 'stock-critical', 'stock-out');
            
            if (product.availableStock <= 0) {
                element.classList.add('stock-out');
                element.textContent = 'Sin stock';
            } else if (product.availableStock <= this.config.criticalStockThreshold) {
                element.classList.add('stock-critical');
            } else if (product.availableStock <= this.config.lowStockThreshold) {
                element.classList.add('stock-low');
            } else {
                element.classList.add('stock-ok');
            }
        });
        
        // Update add to cart buttons
        const addToCartButtons = document.querySelectorAll(`[data-product-id="${product.id}"] .add-to-cart-btn`);
        addToCartButtons.forEach(button => {
            if (product.availableStock <= 0) {
                button.disabled = true;
                button.textContent = 'Sin stock';
                button.classList.add('disabled');
            } else {
                button.disabled = false;
                button.textContent = 'Agregar al carrito';
                button.classList.remove('disabled');
            }
        });
        
        // Update quantity selectors
        const quantitySelectors = document.querySelectorAll(`[data-product-id="${product.id}"] .quantity-selector`);
        quantitySelectors.forEach(selector => {
            const input = selector.querySelector('input');
            if (input) {
                input.max = product.availableStock;
                if (parseInt(input.value) > product.availableStock) {
                    input.value = Math.max(1, product.availableStock);
                }
            }
        });
    }
    
    /**
     * Show stock warning in UI
     */
    showStockWarning(productId, level) {
        const productCards = document.querySelectorAll(`[data-product-id="${productId}"]`);
        productCards.forEach(card => {
            let warningElement = card.querySelector('.stock-warning');
            
            if (!warningElement) {
                warningElement = document.createElement('div');
                warningElement.className = 'stock-warning';
                card.appendChild(warningElement);
            }
            
            warningElement.className = `stock-warning ${level}`;
            
            switch (level) {
                case 'critical':
                    warningElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Stock crítico';
                    break;
                case 'low':
                    warningElement.innerHTML = '<i class="fas fa-info-circle"></i> Stock bajo';
                    break;
            }
        });
    }
    
    /**
     * Disable product in UI
     */
    disableProductInUI(productId) {
        const productCards = document.querySelectorAll(`[data-product-id="${productId}"]`);
        productCards.forEach(card => {
            card.classList.add('out-of-stock');
            
            // Add out of stock overlay
            let overlay = card.querySelector('.out-of-stock-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'out-of-stock-overlay';
                overlay.innerHTML = '<div class="out-of-stock-message">Sin stock</div>';
                card.appendChild(overlay);
            }
        });
    }
    
    /**
     * Update alerts display
     */
    updateAlertsDisplay() {
        const alertsContainer = document.getElementById('inventory-alerts');
        if (!alertsContainer) return;
        
        const recentAlerts = this.alerts.slice(0, 10);
        
        alertsContainer.innerHTML = recentAlerts.map(alert => `
            <div class="alert alert-${alert.priority}">
                <div class="alert-icon">
                    <i class="fas fa-${this.getAlertIcon(alert.type)}"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-message">${alert.message}</div>
                    <div class="alert-time">${this.formatTime(alert.timestamp)}</div>
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Update movements display
     */
    updateMovementsDisplay() {
        const movementsContainer = document.getElementById('stock-movements');
        if (!movementsContainer) return;
        
        const recentMovements = this.stockMovements.slice(0, 20);
        
        movementsContainer.innerHTML = recentMovements.map(movement => `
            <div class="movement-item">
                <div class="movement-type ${movement.type}">
                    <i class="fas fa-${this.getMovementIcon(movement.type)}"></i>
                </div>
                <div class="movement-details">
                    <div class="movement-product">${this.getProductName(movement.productId)}</div>
                    <div class="movement-change">
                        ${movement.oldStock} → ${movement.newStock} 
                        (${movement.quantity > 0 ? '+' : ''}${movement.quantity})
                    </div>
                    <div class="movement-reason">${movement.reason}</div>
                    <div class="movement-time">${this.formatTime(movement.timestamp)}</div>
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Get alert icon
     */
    getAlertIcon(type) {
        const icons = {
            out_of_stock: 'times-circle',
            critical_stock: 'exclamation-triangle',
            low_stock: 'info-circle',
            reorder_triggered: 'shopping-cart'
        };
        return icons[type] || 'bell';
    }
    
    /**
     * Get movement icon
     */
    getMovementIcon(type) {
        const icons = {
            purchase: 'minus',
            restock: 'plus',
            adjustment: 'edit',
            reservation: 'lock',
            cancellation: 'undo'
        };
        return icons[type] || 'exchange-alt';
    }
    
    /**
     * Get product name by ID
     */
    getProductName(productId) {
        const product = this.inventory.get(productId);
        return product ? product.name : `Product ${productId}`;
    }
    
    /**
     * Format time for display
     */
    formatTime(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diff = now - time;
        
        if (diff < 60000) return 'Hace un momento';
        if (diff < 3600000) return `Hace ${Math.floor(diff / 60000)} min`;
        if (diff < 86400000) return `Hace ${Math.floor(diff / 3600000)} h`;
        
        return time.toLocaleDateString();
    }
    
    /**
     * Get cart reservation for product and session
     */
    getCartReservation(productId, sessionId) {
        // This would typically be stored in database
        // For now, return 0 as placeholder
        return 0;
    }
    
    /**
     * Consider reorder for product
     */
    considerReorder(product) {
        if (!this.config.autoReorder) {
            console.log(`Manual reorder recommended for ${product.name}`);
            return;
        }
        
        this.triggerReorder(product);
    }
    
    /**
     * Trigger event
     */
    triggerEvent(eventName, data) {
        if (this.eventHandlers[eventName]) {
            this.eventHandlers[eventName].forEach(handler => {
                try {
                    handler(data);
                } catch (error) {
                    console.error(`Error in ${eventName} event handler:`, error);
                }
            });
        }
        
        // Also dispatch DOM event
        document.dispatchEvent(new CustomEvent(`inventory${eventName.charAt(0).toUpperCase() + eventName.slice(1)}`, {
            detail: data
        }));
    }
    
    /**
     * Add event listener
     */
    on(eventName, handler) {
        if (!this.eventHandlers[eventName]) {
            this.eventHandlers[eventName] = [];
        }
        this.eventHandlers[eventName].push(handler);
    }
    
    /**
     * Remove event listener
     */
    off(eventName, handler) {
        if (this.eventHandlers[eventName]) {
            const index = this.eventHandlers[eventName].indexOf(handler);
            if (index > -1) {
                this.eventHandlers[eventName].splice(index, 1);
            }
        }
    }
    
    /**
     * Get inventory summary
     */
    getInventorySummary() {
        const summary = {
            totalProducts: this.inventory.size,
            totalStock: 0,
            totalValue: 0,
            lowStockItems: 0,
            outOfStockItems: 0,
            pendingReorders: this.pendingOrders.size,
            recentAlerts: this.alerts.length
        };
        
        this.inventory.forEach(product => {
            summary.totalStock += product.currentStock;
            summary.totalValue += product.currentStock * product.unitCost;
            
            if (product.availableStock <= 0) {
                summary.outOfStockItems++;
            } else if (product.availableStock <= this.config.lowStockThreshold) {
                summary.lowStockItems++;
            }
        });
        
        return summary;
    }
    
    /**
     * Get product stock info
     */
    getProductStock(productId) {
        return this.inventory.get(productId);
    }
    
    /**
     * Check if product is available
     */
    isProductAvailable(productId, quantity = 1) {
        const product = this.inventory.get(productId);
        return product && product.availableStock >= quantity;
    }
    
    /**
     * Cleanup resources
     */
    destroy() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
        }
        
        if (this.realTimeConnection) {
            this.realTimeConnection.close();
        }
        
        this.inventory.clear();
        this.suppliers.clear();
        this.pendingOrders.clear();
        this.stockMovements = [];
        this.alerts = [];
        
        console.log('Inventory Manager destroyed');
    }
}

// Initialize inventory manager
document.addEventListener('DOMContentLoaded', () => {
    const config = {
        apiBaseUrl: '/api/inventory',
        realTimeUpdates: true,
        updateInterval: 30000,
        lowStockThreshold: 10,
        criticalStockThreshold: 5,
        enableNotifications: true,
        autoReorder: false, // Set to true to enable auto-reordering
        reorderQuantity: 50
    };
    
    window.inventoryManager = new InventoryManager(config);
    
    // Setup global event handlers
    window.inventoryManager.on('outOfStock', (data) => {
        console.log('Product out of stock:', data.product.name);
        // Could integrate with email notifications, Slack, etc.
    });
    
    window.inventoryManager.on('lowStock', (data) => {
        console.log('Product low stock:', data.product.name);
        // Could show admin notification
    });
    
    window.inventoryManager.on('reorderTriggered', (data) => {
        console.log('Reorder triggered:', data.product.name);
        // Could send email to purchasing team
    });
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = InventoryManager;
}