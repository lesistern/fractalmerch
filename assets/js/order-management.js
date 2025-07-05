/**
 * FractalMerch Advanced Order Management System
 * Complete order lifecycle management with real-time tracking and automation
 */

class OrderManagement {
    constructor(config = {}) {
        this.config = {
            apiBaseUrl: config.apiBaseUrl || '/api/orders',
            realTimeUpdates: config.realTimeUpdates !== false,
            updateInterval: config.updateInterval || 30000, // 30 seconds
            enableNotifications: config.enableNotifications !== false,
            autoStatusUpdates: config.autoStatusUpdates !== false,
            trackingIntegration: config.trackingIntegration || false,
            ...config
        };
        
        this.orders = new Map();
        this.orderStatuses = new Map();
        this.suppliers = new Map();
        this.shippingCarriers = new Map();
        this.notifications = [];
        this.updateTimer = null;
        
        this.orderStates = {
            'pending': { name: 'Pendiente', color: '#ffc107', icon: 'clock' },
            'confirmed': { name: 'Confirmado', color: '#007bff', icon: 'check-circle' },
            'processing': { name: 'Procesando', color: '#fd7e14', icon: 'cogs' },
            'production': { name: 'En Producción', color: '#6f42c1', icon: 'industry' },
            'quality_check': { name: 'Control de Calidad', color: '#20c997', icon: 'search' },
            'packaging': { name: 'Empaquetado', color: '#17a2b8', icon: 'box' },
            'shipped': { name: 'Enviado', color: '#28a745', icon: 'truck' },
            'delivered': { name: 'Entregado', color: '#198754', icon: 'check-double' },
            'cancelled': { name: 'Cancelado', color: '#dc3545', icon: 'times-circle' },
            'returned': { name: 'Devuelto', color: '#6c757d', icon: 'undo' }
        };
        
        this.eventHandlers = {
            orderCreated: [],
            orderUpdated: [],
            statusChanged: [],
            paymentReceived: [],
            productionStarted: [],
            orderShipped: [],
            orderDelivered: [],
            orderCancelled: []
        };
        
        this.init();
    }
    
    init() {
        console.log('Initializing Order Management System...');
        
        this.loadOrders();
        this.loadOrderStatuses();
        this.loadSuppliers();
        this.loadShippingCarriers();
        this.setupRealTimeUpdates();
        this.setupEventListeners();
        this.setupNotifications();
        
        console.log('Order Management System initialized successfully');
    }
    
    /**
     * Load all orders from server
     */
    async loadOrders() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/get-orders.php`);
            const data = await response.json();
            
            if (data.success) {
                data.orders.forEach(order => {
                    this.orders.set(order.id, this.processOrder(order));
                });
                
                console.log(`Loaded ${this.orders.size} orders`);
                this.triggerEvent('ordersLoaded', { count: this.orders.size });
            }
        } catch (error) {
            console.error('Error loading orders:', error);
        }
    }
    
    /**
     * Process raw order data
     */
    processOrder(orderData) {
        return {
            id: orderData.id,
            orderNumber: orderData.order_number,
            customerId: orderData.customer_id,
            customerName: orderData.customer_name,
            customerEmail: orderData.customer_email,
            status: orderData.status,
            totalAmount: parseFloat(orderData.total_amount),
            currency: orderData.currency || 'ARS',
            items: JSON.parse(orderData.items || '[]'),
            shippingAddress: JSON.parse(orderData.shipping_address || '{}'),
            billingAddress: JSON.parse(orderData.billing_address || '{}'),
            paymentMethod: orderData.payment_method,
            paymentStatus: orderData.payment_status,
            shippingMethod: orderData.shipping_method,
            trackingNumber: orderData.tracking_number,
            estimatedDelivery: orderData.estimated_delivery ? new Date(orderData.estimated_delivery) : null,
            actualDelivery: orderData.actual_delivery ? new Date(orderData.actual_delivery) : null,
            notes: orderData.notes || '',
            createdAt: new Date(orderData.created_at),
            updatedAt: new Date(orderData.updated_at),
            statusHistory: JSON.parse(orderData.status_history || '[]'),
            customData: JSON.parse(orderData.custom_data || '{}')
        };
    }
    
    /**
     * Create new order
     */
    async createOrder(orderData) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/create-order.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                const newOrder = this.processOrder(data.order);
                this.orders.set(newOrder.id, newOrder);
                
                // Trigger events
                this.triggerEvent('orderCreated', { order: newOrder });
                
                // Reserve inventory
                await this.reserveInventory(newOrder);
                
                // Send confirmation email
                if (this.config.autoEmailNotifications) {
                    await this.sendOrderConfirmation(newOrder);
                }
                
                console.log(`Order created: ${newOrder.orderNumber}`);
                return newOrder;
                
            } else {
                throw new Error(data.error || 'Failed to create order');
            }
        } catch (error) {
            console.error('Error creating order:', error);
            throw error;
        }
    }
    
    /**
     * Update order status
     */
    async updateOrderStatus(orderId, newStatus, notes = '') {
        const order = this.orders.get(orderId);
        if (!order) {
            throw new Error('Order not found');
        }
        
        const oldStatus = order.status;
        
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/update-status.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    orderId: orderId,
                    status: newStatus,
                    notes: notes,
                    timestamp: new Date().toISOString()
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update local order
                order.status = newStatus;
                order.updatedAt = new Date();
                order.statusHistory.push({
                    status: newStatus,
                    timestamp: new Date(),
                    notes: notes,
                    user: 'system' // This would be current user
                });
                
                // Trigger events
                this.triggerEvent('statusChanged', {
                    order: order,
                    oldStatus: oldStatus,
                    newStatus: newStatus
                });
                
                // Handle status-specific actions
                await this.handleStatusChange(order, oldStatus, newStatus);
                
                console.log(`Order ${order.orderNumber} status updated: ${oldStatus} → ${newStatus}`);
                return true;
                
            } else {
                throw new Error(data.error || 'Failed to update status');
            }
        } catch (error) {
            console.error('Error updating order status:', error);
            throw error;
        }
    }
    
    /**
     * Handle status change actions
     */
    async handleStatusChange(order, oldStatus, newStatus) {
        switch (newStatus) {
            case 'confirmed':
                await this.handleOrderConfirmation(order);
                break;
                
            case 'processing':
                await this.handleOrderProcessing(order);
                break;
                
            case 'production':
                await this.handleProductionStart(order);
                break;
                
            case 'shipped':
                await this.handleOrderShipped(order);
                break;
                
            case 'delivered':
                await this.handleOrderDelivered(order);
                break;
                
            case 'cancelled':
                await this.handleOrderCancellation(order);
                break;
        }
    }
    
    /**
     * Handle order confirmation
     */
    async handleOrderConfirmation(order) {
        // Convert reserved inventory to committed
        await this.commitInventory(order);
        
        // Calculate production timeline
        const timeline = this.calculateProductionTimeline(order);
        order.customData.productionTimeline = timeline;
        
        // Send confirmation to customer
        if (this.config.autoEmailNotifications) {
            await this.sendStatusUpdateEmail(order, 'confirmed');
        }
        
        // Automatically move to processing if configured
        if (this.config.autoStatusUpdates) {
            setTimeout(() => {
                this.updateOrderStatus(order.id, 'processing', 'Automatically moved to processing');
            }, 5000);
        }
        
        this.triggerEvent('orderConfirmed', { order });
    }
    
    /**
     * Handle order processing
     */
    async handleOrderProcessing(order) {
        // Create production tasks
        await this.createProductionTasks(order);
        
        // Update estimated delivery
        const estimatedDelivery = this.calculateEstimatedDelivery(order);
        order.estimatedDelivery = estimatedDelivery;
        
        // Send processing notification
        if (this.config.autoEmailNotifications) {
            await this.sendStatusUpdateEmail(order, 'processing');
        }
        
        this.triggerEvent('orderProcessing', { order });
    }
    
    /**
     * Handle production start
     */
    async handleProductionStart(order) {
        // Create production workflow
        await this.initializeProductionWorkflow(order);
        
        // Update supplier orders if needed
        await this.updateSupplierOrders(order);
        
        // Send production started notification
        if (this.config.autoEmailNotifications) {
            await this.sendStatusUpdateEmail(order, 'production');
        }
        
        this.triggerEvent('productionStarted', { order });
    }
    
    /**
     * Handle order shipped
     */
    async handleOrderShipped(order) {
        // Generate tracking information
        if (!order.trackingNumber) {
            order.trackingNumber = await this.generateTrackingNumber(order);
        }
        
        // Send shipping notification with tracking
        if (this.config.autoEmailNotifications) {
            await this.sendShippingNotification(order);
        }
        
        // Start delivery tracking
        if (this.config.trackingIntegration) {
            await this.startDeliveryTracking(order);
        }
        
        this.triggerEvent('orderShipped', { order });
    }
    
    /**
     * Handle order delivered
     */
    async handleOrderDelivered(order) {
        // Record actual delivery time
        order.actualDelivery = new Date();
        
        // Release any remaining inventory reservations
        await this.releaseInventoryReservations(order);
        
        // Send delivery confirmation
        if (this.config.autoEmailNotifications) {
            await this.sendDeliveryConfirmation(order);
        }
        
        // Schedule follow-up feedback request
        if (this.config.autoFeedbackRequests) {
            setTimeout(() => {
                this.requestCustomerFeedback(order);
            }, 24 * 60 * 60 * 1000); // 24 hours after delivery
        }
        
        this.triggerEvent('orderDelivered', { order });
    }
    
    /**
     * Handle order cancellation
     */
    async handleOrderCancellation(order) {
        // Release inventory
        await this.releaseInventory(order);
        
        // Process refund if payment was received
        if (order.paymentStatus === 'paid') {
            await this.processRefund(order);
        }
        
        // Cancel production tasks
        await this.cancelProductionTasks(order);
        
        // Send cancellation notification
        if (this.config.autoEmailNotifications) {
            await this.sendCancellationNotification(order);
        }
        
        this.triggerEvent('orderCancelled', { order });
    }
    
    /**
     * Reserve inventory for order
     */
    async reserveInventory(order) {
        const reservations = order.items.map(item => ({
            productId: item.productId,
            quantity: item.quantity,
            orderId: order.id,
            sessionId: `order_${order.id}`
        }));
        
        try {
            const response = await fetch('/api/inventory/update-reservations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reservations })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error('Failed to reserve inventory');
            }
        } catch (error) {
            console.error('Error reserving inventory:', error);
            throw error;
        }
    }
    
    /**
     * Commit inventory (convert reservations to actual stock reduction)
     */
    async commitInventory(order) {
        const updates = order.items.map(item => ({
            productId: item.productId,
            type: 'sale',
            quantity: item.quantity,
            orderId: order.id,
            unitCost: item.unitCost || 0,
            reason: `Order ${order.orderNumber}`
        }));
        
        try {
            const response = await fetch('/api/inventory/update-stock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ updates })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error('Failed to commit inventory');
            }
        } catch (error) {
            console.error('Error committing inventory:', error);
            throw error;
        }
    }
    
    /**
     * Calculate production timeline
     */
    calculateProductionTimeline(order) {
        const baseProductionTime = 2; // days
        const customizationTime = 1; // day per custom item
        const qualityCheckTime = 1; // day
        const packagingTime = 0.5; // half day
        
        let totalDays = baseProductionTime + qualityCheckTime + packagingTime;
        
        // Add time for custom items
        const customItems = order.items.filter(item => item.isCustom);
        totalDays += customItems.length * customizationTime;
        
        // Add buffer for weekends
        totalDays = Math.ceil(totalDays * 1.4);
        
        const startDate = new Date();
        const endDate = new Date(startDate.getTime() + totalDays * 24 * 60 * 60 * 1000);
        
        return {
            startDate,
            endDate,
            totalDays,
            phases: [
                { name: 'Design Review', duration: 0.5, status: 'pending' },
                { name: 'Production', duration: baseProductionTime, status: 'pending' },
                { name: 'Customization', duration: customItems.length * customizationTime, status: 'pending' },
                { name: 'Quality Check', duration: qualityCheckTime, status: 'pending' },
                { name: 'Packaging', duration: packagingTime, status: 'pending' }
            ]
        };
    }
    
    /**
     * Calculate estimated delivery date
     */
    calculateEstimatedDelivery(order) {
        const timeline = order.customData.productionTimeline;
        const shippingDays = this.getShippingDays(order.shippingMethod, order.shippingAddress);
        
        const deliveryDate = new Date(timeline.endDate.getTime() + shippingDays * 24 * 60 * 60 * 1000);
        return deliveryDate;
    }
    
    /**
     * Get shipping days based on method and address
     */
    getShippingDays(shippingMethod, address) {
        const shippingTimes = {
            'standard': 3,
            'express': 1,
            'overnight': 1,
            'pickup': 0
        };
        
        let baseDays = shippingTimes[shippingMethod] || 3;
        
        // Add extra days for remote areas
        if (address.province && ['Tierra del Fuego', 'Neuquén', 'Río Negro'].includes(address.province)) {
            baseDays += 2;
        }
        
        return baseDays;
    }
    
    /**
     * Create production tasks
     */
    async createProductionTasks(order) {
        const tasks = [];
        
        order.items.forEach(item => {
            if (item.isCustom) {
                tasks.push({
                    orderId: order.id,
                    itemId: item.id,
                    taskType: 'design_preparation',
                    title: `Prepare design for ${item.name}`,
                    description: `Prepare and review custom design for order ${order.orderNumber}`,
                    assignedTo: 'design_team',
                    estimatedDuration: 2, // hours
                    status: 'pending'
                });
                
                tasks.push({
                    orderId: order.id,
                    itemId: item.id,
                    taskType: 'customization',
                    title: `Customize ${item.name}`,
                    description: `Apply custom design and print for order ${order.orderNumber}`,
                    assignedTo: 'production_team',
                    estimatedDuration: 4, // hours
                    status: 'pending',
                    dependsOn: 'design_preparation'
                });
            } else {
                tasks.push({
                    orderId: order.id,
                    itemId: item.id,
                    taskType: 'production',
                    title: `Produce ${item.name}`,
                    description: `Standard production for order ${order.orderNumber}`,
                    assignedTo: 'production_team',
                    estimatedDuration: 2, // hours
                    status: 'pending'
                });
            }
        });
        
        // Add quality check task
        tasks.push({
            orderId: order.id,
            taskType: 'quality_check',
            title: `Quality check for order ${order.orderNumber}`,
            description: 'Inspect all items before packaging',
            assignedTo: 'quality_team',
            estimatedDuration: 1, // hour
            status: 'pending',
            dependsOn: 'production'
        });
        
        // Add packaging task
        tasks.push({
            orderId: order.id,
            taskType: 'packaging',
            title: `Package order ${order.orderNumber}`,
            description: 'Package items for shipping',
            assignedTo: 'packaging_team',
            estimatedDuration: 0.5, // hour
            status: 'pending',
            dependsOn: 'quality_check'
        });
        
        // Save tasks to database
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/create-tasks.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ tasks })
            });
            
            const data = await response.json();
            
            if (data.success) {
                order.customData.productionTasks = data.tasks;
                console.log(`Created ${tasks.length} production tasks for order ${order.orderNumber}`);
            }
        } catch (error) {
            console.error('Error creating production tasks:', error);
        }
    }
    
    /**
     * Initialize production workflow
     */
    async initializeProductionWorkflow(order) {
        // Start the first production tasks
        const tasks = order.customData.productionTasks || [];
        const firstTasks = tasks.filter(task => !task.dependsOn);
        
        for (const task of firstTasks) {
            await this.startProductionTask(task);
        }
    }
    
    /**
     * Start production task
     */
    async startProductionTask(task) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/start-task.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    taskId: task.id,
                    startedAt: new Date().toISOString()
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                task.status = 'in_progress';
                task.startedAt = new Date();
                console.log(`Started production task: ${task.title}`);
            }
        } catch (error) {
            console.error('Error starting production task:', error);
        }
    }
    
    /**
     * Generate tracking number
     */
    async generateTrackingNumber(order) {
        // Generate a tracking number based on carrier
        const carrier = order.shippingMethod;
        const timestamp = Date.now().toString().slice(-8);
        const orderNumber = order.orderNumber.slice(-4);
        
        return `FM${carrier.toUpperCase()}${timestamp}${orderNumber}`;
    }
    
    /**
     * Send order confirmation email
     */
    async sendOrderConfirmation(order) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/send-email.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'order_confirmation',
                    order: order,
                    to: order.customerEmail
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log(`Order confirmation sent to ${order.customerEmail}`);
            }
        } catch (error) {
            console.error('Error sending order confirmation:', error);
        }
    }
    
    /**
     * Send status update email
     */
    async sendStatusUpdateEmail(order, status) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/send-email.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'status_update',
                    order: order,
                    status: status,
                    to: order.customerEmail
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log(`Status update email sent to ${order.customerEmail}: ${status}`);
            }
        } catch (error) {
            console.error('Error sending status update email:', error);
        }
    }
    
    /**
     * Setup real-time updates
     */
    setupRealTimeUpdates() {
        if (!this.config.realTimeUpdates) return;
        
        this.updateTimer = setInterval(() => {
            this.syncOrderUpdates();
        }, this.config.updateInterval);
    }
    
    /**
     * Sync order updates from server
     */
    async syncOrderUpdates() {
        try {
            const lastSync = localStorage.getItem('lastOrderSync') || '0';
            const response = await fetch(`${this.config.apiBaseUrl}/sync-updates.php?since=${lastSync}`);
            const data = await response.json();
            
            if (data.success && data.updates.length > 0) {
                data.updates.forEach(update => {
                    this.applyOrderUpdate(update);
                });
                
                localStorage.setItem('lastOrderSync', Date.now().toString());
                console.log(`Applied ${data.updates.length} order updates`);
            }
        } catch (error) {
            console.error('Error syncing order updates:', error);
        }
    }
    
    /**
     * Apply order update
     */
    applyOrderUpdate(update) {
        const order = this.orders.get(update.order_id);
        if (!order) return;
        
        switch (update.type) {
            case 'status_change':
                order.status = update.new_status;
                order.updatedAt = new Date(update.timestamp);
                this.triggerEvent('orderUpdated', { order, update });
                break;
                
            case 'payment_received':
                order.paymentStatus = 'paid';
                this.triggerEvent('paymentReceived', { order, update });
                break;
                
            case 'tracking_updated':
                order.trackingNumber = update.tracking_number;
                this.triggerEvent('trackingUpdated', { order, update });
                break;
        }
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for cart checkout completion
        document.addEventListener('checkoutCompleted', (event) => {
            this.handleCheckoutCompletion(event.detail);
        });
        
        // Listen for payment confirmations
        document.addEventListener('paymentConfirmed', (event) => {
            this.handlePaymentConfirmation(event.detail);
        });
    }
    
    /**
     * Handle checkout completion
     */
    async handleCheckoutCompletion(checkoutData) {
        try {
            const order = await this.createOrder({
                customerId: checkoutData.customerId,
                customerName: checkoutData.customerName,
                customerEmail: checkoutData.customerEmail,
                items: checkoutData.items,
                totalAmount: checkoutData.totalAmount,
                shippingAddress: checkoutData.shippingAddress,
                billingAddress: checkoutData.billingAddress,
                paymentMethod: checkoutData.paymentMethod,
                shippingMethod: checkoutData.shippingMethod,
                notes: checkoutData.notes || ''
            });
            
            // Dispatch order created event
            document.dispatchEvent(new CustomEvent('orderCreated', {
                detail: { order }
            }));
            
        } catch (error) {
            console.error('Error handling checkout completion:', error);
        }
    }
    
    /**
     * Handle payment confirmation
     */
    async handlePaymentConfirmation(paymentData) {
        const order = this.orders.get(paymentData.orderId);
        if (!order) return;
        
        order.paymentStatus = 'paid';
        order.updatedAt = new Date();
        
        // Automatically confirm order if payment is successful
        if (this.config.autoStatusUpdates && order.status === 'pending') {
            await this.updateOrderStatus(order.id, 'confirmed', 'Payment confirmed');
        }
        
        this.triggerEvent('paymentReceived', { order, paymentData });
    }
    
    /**
     * Setup notifications
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
                tag: `order-${type}-${Date.now()}`,
                requireInteraction: type === 'urgent'
            });
            
            setTimeout(() => {
                notification.close();
            }, 5000);
        }
    }
    
    /**
     * Get order summary
     */
    getOrderSummary() {
        const summary = {
            totalOrders: this.orders.size,
            pendingOrders: 0,
            processingOrders: 0,
            shippedOrders: 0,
            deliveredOrders: 0,
            cancelledOrders: 0,
            totalRevenue: 0,
            averageOrderValue: 0
        };
        
        this.orders.forEach(order => {
            summary.totalRevenue += order.totalAmount;
            
            switch (order.status) {
                case 'pending':
                    summary.pendingOrders++;
                    break;
                case 'processing':
                case 'production':
                case 'quality_check':
                case 'packaging':
                    summary.processingOrders++;
                    break;
                case 'shipped':
                    summary.shippedOrders++;
                    break;
                case 'delivered':
                    summary.deliveredOrders++;
                    break;
                case 'cancelled':
                    summary.cancelledOrders++;
                    break;
            }
        });
        
        summary.averageOrderValue = summary.totalOrders > 0 ? 
            summary.totalRevenue / summary.totalOrders : 0;
        
        return summary;
    }
    
    /**
     * Get order by ID
     */
    getOrder(orderId) {
        return this.orders.get(orderId);
    }
    
    /**
     * Get orders by status
     */
    getOrdersByStatus(status) {
        return Array.from(this.orders.values()).filter(order => order.status === status);
    }
    
    /**
     * Get orders by customer
     */
    getOrdersByCustomer(customerId) {
        return Array.from(this.orders.values()).filter(order => order.customerId === customerId);
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
        document.dispatchEvent(new CustomEvent(`order${eventName.charAt(0).toUpperCase() + eventName.slice(1)}`, {
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
     * Cleanup resources
     */
    destroy() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
        }
        
        this.orders.clear();
        this.orderStatuses.clear();
        this.suppliers.clear();
        this.shippingCarriers.clear();
        this.notifications = [];
        
        console.log('Order Management System destroyed');
    }
    
    // Placeholder methods for future implementation
    async loadOrderStatuses() { /* Implementation */ }
    async loadSuppliers() { /* Implementation */ }
    async loadShippingCarriers() { /* Implementation */ }
    async releaseInventory(order) { /* Implementation */ }
    async releaseInventoryReservations(order) { /* Implementation */ }
    async processRefund(order) { /* Implementation */ }
    async cancelProductionTasks(order) { /* Implementation */ }
    async sendCancellationNotification(order) { /* Implementation */ }
    async sendShippingNotification(order) { /* Implementation */ }
    async sendDeliveryConfirmation(order) { /* Implementation */ }
    async startDeliveryTracking(order) { /* Implementation */ }
    async requestCustomerFeedback(order) { /* Implementation */ }
    async updateSupplierOrders(order) { /* Implementation */ }
}

// Initialize order management
document.addEventListener('DOMContentLoaded', () => {
    const config = {
        apiBaseUrl: '/api/orders',
        realTimeUpdates: true,
        updateInterval: 30000,
        enableNotifications: true,
        autoStatusUpdates: true,
        autoEmailNotifications: true,
        autoFeedbackRequests: true,
        trackingIntegration: false
    };
    
    window.orderManagement = new OrderManagement(config);
    
    // Setup global event handlers
    window.orderManagement.on('orderCreated', (data) => {
        console.log('New order created:', data.order.orderNumber);
    });
    
    window.orderManagement.on('statusChanged', (data) => {
        console.log(`Order ${data.order.orderNumber} status changed: ${data.oldStatus} → ${data.newStatus}`);
    });
    
    window.orderManagement.on('orderShipped', (data) => {
        console.log(`Order ${data.order.orderNumber} shipped with tracking: ${data.order.trackingNumber}`);
    });
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = OrderManagement;
}