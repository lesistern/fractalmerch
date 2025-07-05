/**
 * FractalMerch Stock Alert System
 * Intelligent stock monitoring with real-time alerts and automated actions
 */

class StockAlerts {
    constructor(config = {}) {
        this.config = {
            apiBaseUrl: config.apiBaseUrl || '/api/inventory',
            checkInterval: config.checkInterval || 60000, // 1 minute
            enableNotifications: config.enableNotifications !== false,
            enableEmailAlerts: config.enableEmailAlerts !== false,
            enableSlackIntegration: config.enableSlackIntegration || false,
            slackWebhook: config.slackWebhook || '',
            autoReorder: config.autoReorder || false,
            alertThresholds: {
                critical: config.criticalThreshold || 5,
                low: config.lowThreshold || 10,
                outOfStock: 0,
                ...config.alertThresholds
            },
            ...config
        };
        
        this.alerts = new Map();
        this.alertHistory = [];
        this.subscribedUsers = new Set();
        this.mutedAlerts = new Set();
        this.checkTimer = null;
        this.lastCheck = 0;
        
        this.alertTypes = {
            'out_of_stock': {
                name: 'Sin Stock',
                priority: 'critical',
                color: '#dc3545',
                icon: 'times-circle',
                sound: 'critical.mp3',
                actions: ['reorder', 'notify_sales', 'disable_product']
            },
            'critical_stock': {
                name: 'Stock Crítico',
                priority: 'high',
                color: '#fd7e14',
                icon: 'exclamation-triangle',
                sound: 'warning.mp3',
                actions: ['reorder', 'notify_purchasing']
            },
            'low_stock': {
                name: 'Stock Bajo',
                priority: 'medium',
                color: '#ffc107',
                icon: 'info-circle',
                sound: 'info.mp3',
                actions: ['consider_reorder', 'track_usage']
            },
            'reorder_triggered': {
                name: 'Reorden Activada',
                priority: 'info',
                color: '#17a2b8',
                icon: 'shopping-cart',
                sound: 'success.mp3',
                actions: ['track_order', 'update_eta']
            },
            'supplier_delayed': {
                name: 'Proveedor Retrasado',
                priority: 'high',
                color: '#6f42c1',
                icon: 'clock',
                sound: 'warning.mp3',
                actions: ['contact_supplier', 'find_alternative']
            },
            'unusual_consumption': {
                name: 'Consumo Inusual',
                priority: 'medium',
                color: '#20c997',
                icon: 'chart-line',
                sound: 'info.mp3',
                actions: ['analyze_pattern', 'adjust_forecasting']
            }
        };
        
        this.eventHandlers = {
            alertCreated: [],
            alertResolved: [],
            alertEscalated: [],
            batchAlertsProcessed: []
        };
        
        this.init();
    }
    
    init() {
        console.log('Initializing Stock Alert System...');
        
        this.loadExistingAlerts();
        this.loadUserSubscriptions();
        this.setupEventListeners();
        this.startMonitoring();
        this.setupNotificationSystem();
        this.loadAlertHistory();
        
        console.log('Stock Alert System initialized successfully');
    }
    
    /**
     * Load existing alerts from server
     */
    async loadExistingAlerts() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/get-alerts.php`);
            const data = await response.json();
            
            if (data.success) {
                data.alerts.forEach(alert => {
                    this.alerts.set(alert.id, this.processAlert(alert));
                });
                
                console.log(`Loaded ${this.alerts.size} existing alerts`);
                this.updateAlertDisplay();
            }
        } catch (error) {
            console.error('Error loading existing alerts:', error);
        }
    }
    
    /**
     * Process raw alert data
     */
    processAlert(alertData) {
        return {
            id: alertData.id,
            inventoryItemId: alertData.inventory_item_id,
            alertType: alertData.alert_type,
            message: alertData.message,
            priority: alertData.priority,
            isAcknowledged: alertData.is_acknowledged === '1',
            acknowledgedBy: alertData.acknowledged_by,
            acknowledgedAt: alertData.acknowledged_at ? new Date(alertData.acknowledged_at) : null,
            createdAt: new Date(alertData.created_at),
            itemName: alertData.item_name,
            itemSku: alertData.item_sku,
            currentStock: parseInt(alertData.current_stock || 0),
            availableStock: parseInt(alertData.available_stock || 0),
            reorderPoint: parseInt(alertData.reorder_point || 0),
            supplierName: alertData.supplier_name,
            actionsTaken: JSON.parse(alertData.actions_taken || '[]'),
            metadata: JSON.parse(alertData.metadata || '{}')
        };
    }
    
    /**
     * Start monitoring stock levels
     */
    startMonitoring() {
        this.checkTimer = setInterval(() => {
            this.performStockCheck();
        }, this.config.checkInterval);
        
        // Perform initial check
        this.performStockCheck();
    }
    
    /**
     * Perform stock level check
     */
    async performStockCheck() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/check-stock-levels.php`);
            const data = await response.json();
            
            if (data.success) {
                this.processStockLevels(data.inventory);
                this.lastCheck = Date.now();
            }
        } catch (error) {
            console.error('Error performing stock check:', error);
        }
    }
    
    /**
     * Process stock levels and create alerts
     */
    processStockLevels(inventory) {
        const newAlerts = [];
        const resolvedAlerts = [];
        
        inventory.forEach(item => {
            const availableStock = item.current_stock - (item.reserved_stock || 0);
            const alertKey = `${item.id}_stock_level`;
            
            // Check for stock issues
            if (availableStock <= 0) {
                this.createAlert({
                    key: alertKey,
                    type: 'out_of_stock',
                    inventoryItemId: item.id,
                    message: `${item.name} está sin stock`,
                    currentStock: item.current_stock,
                    availableStock: availableStock,
                    item: item
                });
            } else if (availableStock <= this.config.alertThresholds.critical) {
                this.createAlert({
                    key: alertKey,
                    type: 'critical_stock',
                    inventoryItemId: item.id,
                    message: `${item.name} tiene stock crítico (${availableStock} disponibles)`,
                    currentStock: item.current_stock,
                    availableStock: availableStock,
                    item: item
                });
            } else if (availableStock <= item.reorder_point || availableStock <= this.config.alertThresholds.low) {
                this.createAlert({
                    key: alertKey,
                    type: 'low_stock',
                    inventoryItemId: item.id,
                    message: `${item.name} tiene stock bajo (${availableStock} disponibles)`,
                    currentStock: item.current_stock,
                    availableStock: availableStock,
                    item: item
                });
            } else {
                // Stock is OK, resolve any existing alerts
                this.resolveAlertByKey(alertKey);
            }
            
            // Check for unusual consumption patterns
            this.checkConsumptionPattern(item);
        });
        
        // Check supplier delivery delays
        this.checkSupplierDelays();
    }
    
    /**
     * Create or update alert
     */
    async createAlert(alertData) {
        const existingAlert = Array.from(this.alerts.values())
            .find(alert => alert.inventoryItemId === alertData.inventoryItemId && 
                          alert.alertType === alertData.type && 
                          !alert.isAcknowledged);
        
        if (existingAlert) {
            // Update existing alert
            existingAlert.message = alertData.message;
            existingAlert.currentStock = alertData.currentStock;
            existingAlert.availableStock = alertData.availableStock;
            existingAlert.metadata = { ...existingAlert.metadata, ...alertData.item };
            return existingAlert;
        }
        
        // Check if alert is muted
        const muteKey = `${alertData.inventoryItemId}_${alertData.type}`;
        if (this.mutedAlerts.has(muteKey)) {
            return;
        }
        
        // Create new alert
        const alert = {
            id: Date.now() + Math.random(),
            inventoryItemId: alertData.inventoryItemId,
            alertType: alertData.type,
            message: alertData.message,
            priority: this.alertTypes[alertData.type].priority,
            isAcknowledged: false,
            acknowledgedBy: null,
            acknowledgedAt: null,
            createdAt: new Date(),
            itemName: alertData.item.name,
            itemSku: alertData.item.sku,
            currentStock: alertData.currentStock,
            availableStock: alertData.availableStock,
            reorderPoint: alertData.item.reorder_point,
            supplierName: alertData.item.supplier_name,
            actionsTaken: [],
            metadata: { ...alertData.item }
        };
        
        this.alerts.set(alert.id, alert);
        
        // Save to server
        await this.saveAlert(alert);
        
        // Trigger notifications
        this.sendNotification(alert);
        
        // Trigger automatic actions
        await this.triggerAutomaticActions(alert);
        
        // Update display
        this.updateAlertDisplay();
        
        // Trigger events
        this.triggerEvent('alertCreated', { alert });
        
        console.log(`Created ${alertData.type} alert for ${alertData.item.name}`);
        
        return alert;
    }
    
    /**
     * Save alert to server
     */
    async saveAlert(alert) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/save-alert.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(alert)
            });
            
            const data = await response.json();
            
            if (data.success && data.alertId) {
                alert.id = data.alertId;
            }
        } catch (error) {
            console.error('Error saving alert:', error);
        }
    }
    
    /**
     * Resolve alert
     */
    async resolveAlert(alertId, resolvedBy = null, notes = '') {
        const alert = this.alerts.get(alertId);
        if (!alert || alert.isAcknowledged) return;
        
        alert.isAcknowledged = true;
        alert.acknowledgedBy = resolvedBy;
        alert.acknowledgedAt = new Date();
        alert.metadata.resolutionNotes = notes;
        
        // Update on server
        try {
            await fetch(`${this.config.apiBaseUrl}/resolve-alert.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    alertId: alertId,
                    resolvedBy: resolvedBy,
                    notes: notes,
                    resolvedAt: alert.acknowledgedAt.toISOString()
                })
            });
        } catch (error) {
            console.error('Error resolving alert:', error);
        }
        
        this.updateAlertDisplay();
        this.triggerEvent('alertResolved', { alert, resolvedBy, notes });
        
        console.log(`Resolved alert: ${alert.message}`);
    }
    
    /**
     * Resolve alert by key
     */
    resolveAlertByKey(key) {
        const alert = Array.from(this.alerts.values())
            .find(a => a.metadata.key === key && !a.isAcknowledged);
        
        if (alert) {
            this.resolveAlert(alert.id, 'system', 'Stock level returned to normal');
        }
    }
    
    /**
     * Send notification for alert
     */
    sendNotification(alert) {
        const alertType = this.alertTypes[alert.alertType];
        
        // Browser notification
        if (this.config.enableNotifications && 'Notification' in window && Notification.permission === 'granted') {
            const notification = new Notification(`${alertType.name}: ${alert.itemName}`, {
                body: alert.message,
                icon: '/assets/images/icon.png',
                tag: `stock-alert-${alert.id}`,
                requireInteraction: alertType.priority === 'critical'
            });
            
            setTimeout(() => {
                notification.close();
            }, 10000);
        }
        
        // Play sound
        if (alertType.sound) {
            this.playAlertSound(alertType.sound);
        }
        
        // Email notification
        if (this.config.enableEmailAlerts && alertType.priority !== 'info') {
            this.sendEmailAlert(alert);
        }
        
        // Slack notification
        if (this.config.enableSlackIntegration && this.config.slackWebhook) {
            this.sendSlackAlert(alert);
        }
        
        // Add to notification history
        this.alertHistory.unshift({
            alert: alert,
            timestamp: new Date(),
            notificationsSent: ['browser', 'email', 'slack'].filter(type => {
                switch (type) {
                    case 'email': return this.config.enableEmailAlerts;
                    case 'slack': return this.config.enableSlackIntegration;
                    default: return true;
                }
            })
        });
        
        // Keep only last 100 notifications
        if (this.alertHistory.length > 100) {
            this.alertHistory = this.alertHistory.slice(0, 100);
        }
    }
    
    /**
     * Play alert sound
     */
    playAlertSound(soundFile) {
        try {
            const audio = new Audio(`/assets/sounds/${soundFile}`);
            audio.volume = 0.5;
            audio.play().catch(error => {
                console.warn('Could not play alert sound:', error);
            });
        } catch (error) {
            console.warn('Error playing alert sound:', error);
        }
    }
    
    /**
     * Send email alert
     */
    async sendEmailAlert(alert) {
        try {
            await fetch(`${this.config.apiBaseUrl}/send-alert-email.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    alert: alert,
                    recipients: Array.from(this.subscribedUsers)
                })
            });
        } catch (error) {
            console.error('Error sending email alert:', error);
        }
    }
    
    /**
     * Send Slack alert
     */
    async sendSlackAlert(alert) {
        const alertType = this.alertTypes[alert.alertType];
        const color = alertType.color;
        
        const slackMessage = {
            text: `🚨 Stock Alert: ${alert.itemName}`,
            attachments: [{
                color: color,
                fields: [
                    {
                        title: 'Product',
                        value: `${alert.itemName} (${alert.itemSku})`,
                        short: true
                    },
                    {
                        title: 'Alert Type',
                        value: alertType.name,
                        short: true
                    },
                    {
                        title: 'Available Stock',
                        value: alert.availableStock.toString(),
                        short: true
                    },
                    {
                        title: 'Reorder Point',
                        value: alert.reorderPoint.toString(),
                        short: true
                    }
                ],
                footer: 'FractalMerch Inventory System',
                ts: Math.floor(alert.createdAt.getTime() / 1000)
            }]
        };
        
        try {
            await fetch(this.config.slackWebhook, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(slackMessage)
            });
        } catch (error) {
            console.error('Error sending Slack alert:', error);
        }
    }
    
    /**
     * Trigger automatic actions
     */
    async triggerAutomaticActions(alert) {
        const alertType = this.alertTypes[alert.alertType];
        const actions = alertType.actions || [];
        
        for (const action of actions) {
            try {
                await this.executeAction(alert, action);
            } catch (error) {
                console.error(`Error executing action ${action} for alert ${alert.id}:`, error);
            }
        }
    }
    
    /**
     * Execute automatic action
     */
    async executeAction(alert, action) {
        switch (action) {
            case 'reorder':
                if (this.config.autoReorder) {
                    await this.triggerReorder(alert);
                }
                break;
                
            case 'notify_sales':
                await this.notifySalesTeam(alert);
                break;
                
            case 'disable_product':
                await this.disableProduct(alert);
                break;
                
            case 'notify_purchasing':
                await this.notifyPurchasingTeam(alert);
                break;
                
            case 'consider_reorder':
                await this.createReorderRecommendation(alert);
                break;
                
            case 'track_usage':
                await this.startUsageTracking(alert);
                break;
                
            case 'contact_supplier':
                await this.contactSupplier(alert);
                break;
                
            case 'find_alternative':
                await this.findAlternativeSupplier(alert);
                break;
                
            case 'analyze_pattern':
                await this.analyzeConsumptionPattern(alert);
                break;
        }
        
        // Record action taken
        alert.actionsTaken.push({
            action: action,
            timestamp: new Date(),
            status: 'completed'
        });
    }
    
    /**
     * Trigger reorder
     */
    async triggerReorder(alert) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/trigger-reorder.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    inventoryItemId: alert.inventoryItemId,
                    reason: `Automatic reorder triggered by ${alert.alertType} alert`,
                    urgent: alert.alertType === 'out_of_stock'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Create reorder notification
                this.createAlert({
                    key: `reorder_${alert.inventoryItemId}`,
                    type: 'reorder_triggered',
                    inventoryItemId: alert.inventoryItemId,
                    message: `Reorder automático iniciado para ${alert.itemName}`,
                    item: alert.metadata
                });
                
                console.log(`Automatic reorder triggered for ${alert.itemName}`);
            }
        } catch (error) {
            console.error('Error triggering reorder:', error);
        }
    }
    
    /**
     * Check consumption pattern
     */
    checkConsumptionPattern(item) {
        // This would analyze historical usage patterns
        // For now, we'll simulate unusual consumption detection
        
        const normalConsumption = item.reorder_quantity / 30; // Daily normal usage
        const recentUsage = this.calculateRecentUsage(item.id);
        
        if (recentUsage > normalConsumption * 3) {
            this.createAlert({
                key: `unusual_consumption_${item.id}`,
                type: 'unusual_consumption',
                inventoryItemId: item.id,
                message: `${item.name} muestra un consumo inusualmente alto`,
                item: item
            });
        }
    }
    
    /**
     * Calculate recent usage (placeholder)
     */
    calculateRecentUsage(itemId) {
        // This would query actual usage data
        // For now, return random value for demonstration
        return Math.random() * 20;
    }
    
    /**
     * Check supplier delays
     */
    async checkSupplierDelays() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/check-supplier-delays.php`);
            const data = await response.json();
            
            if (data.success && data.delays.length > 0) {
                data.delays.forEach(delay => {
                    this.createAlert({
                        key: `supplier_delay_${delay.supplier_id}_${delay.order_id}`,
                        type: 'supplier_delayed',
                        inventoryItemId: delay.inventory_item_id,
                        message: `Proveedor ${delay.supplier_name} tiene retraso en orden ${delay.order_number}`,
                        item: delay
                    });
                });
            }
        } catch (error) {
            console.error('Error checking supplier delays:', error);
        }
    }
    
    /**
     * Update alert display
     */
    updateAlertDisplay() {
        // Update alerts counter in header
        const alertsCounter = document.getElementById('alerts-counter');
        if (alertsCounter) {
            const unacknowledgedCount = Array.from(this.alerts.values())
                .filter(alert => !alert.isAcknowledged).length;
            alertsCounter.textContent = unacknowledgedCount;
            alertsCounter.style.display = unacknowledgedCount > 0 ? 'block' : 'none';
        }
        
        // Update alerts dropdown
        this.updateAlertsDropdown();
        
        // Update alerts dashboard if visible
        this.updateAlertsDashboard();
    }
    
    /**
     * Update alerts dropdown
     */
    updateAlertsDropdown() {
        const alertsDropdown = document.getElementById('alerts-dropdown');
        if (!alertsDropdown) return;
        
        const unacknowledgedAlerts = Array.from(this.alerts.values())
            .filter(alert => !alert.isAcknowledged)
            .sort((a, b) => this.getPriorityWeight(b.priority) - this.getPriorityWeight(a.priority))
            .slice(0, 10);
        
        if (unacknowledgedAlerts.length === 0) {
            alertsDropdown.innerHTML = '<div class="alert-item no-alerts">No hay alertas activas</div>';
            return;
        }
        
        alertsDropdown.innerHTML = unacknowledgedAlerts.map(alert => {
            const alertType = this.alertTypes[alert.alertType];
            return `
                <div class="alert-item ${alert.priority}" data-alert-id="${alert.id}">
                    <div class="alert-icon" style="color: ${alertType.color}">
                        <i class="fas fa-${alertType.icon}"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">${alertType.name}</div>
                        <div class="alert-message">${alert.message}</div>
                        <div class="alert-time">${this.formatTimeAgo(alert.createdAt)}</div>
                    </div>
                    <div class="alert-actions">
                        <button class="btn btn-sm" onclick="stockAlerts.resolveAlert(${alert.id}, 'user')">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    /**
     * Update alerts dashboard
     */
    updateAlertsDashboard() {
        const alertsDashboard = document.getElementById('alerts-dashboard');
        if (!alertsDashboard) return;
        
        // This would update a comprehensive alerts dashboard
        // Implementation would depend on specific dashboard design
    }
    
    /**
     * Get priority weight for sorting
     */
    getPriorityWeight(priority) {
        const weights = { 'critical': 4, 'high': 3, 'medium': 2, 'low': 1, 'info': 0 };
        return weights[priority] || 0;
    }
    
    /**
     * Format time ago
     */
    formatTimeAgo(date) {
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);
        
        if (diffMins < 1) return 'Ahora';
        if (diffMins < 60) return `Hace ${diffMins} min`;
        if (diffHours < 24) return `Hace ${diffHours} h`;
        return `Hace ${diffDays} días`;
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for inventory updates
        document.addEventListener('inventoryUpdated', (event) => {
            this.handleInventoryUpdate(event.detail);
        });
        
        // Listen for stock updates
        document.addEventListener('stockUpdate', (event) => {
            this.handleStockUpdate(event.detail);
        });
        
        // Setup notification permission request
        if (this.config.enableNotifications && 'Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
    
    /**
     * Handle inventory update
     */
    handleInventoryUpdate(updateData) {
        // Re-check stock levels for updated items
        if (updateData.productId) {
            this.checkSingleProduct(updateData.productId);
        }
    }
    
    /**
     * Handle stock update
     */
    handleStockUpdate(updateData) {
        // Immediate check for the updated product
        if (updateData.productId) {
            this.checkSingleProduct(updateData.productId);
        }
    }
    
    /**
     * Check single product stock level
     */
    async checkSingleProduct(productId) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/get-inventory.php?product_id=${productId}`);
            const data = await response.json();
            
            if (data.success && data.inventory.length > 0) {
                this.processStockLevels(data.inventory);
            }
        } catch (error) {
            console.error('Error checking single product:', error);
        }
    }
    
    /**
     * Setup notification system
     */
    setupNotificationSystem() {
        // Request notification permission if needed
        if (this.config.enableNotifications && 'Notification' in window) {
            if (Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        console.log('Notification permission granted');
                    }
                });
            }
        }
    }
    
    /**
     * Load user subscriptions
     */
    async loadUserSubscriptions() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/get-subscriptions.php`);
            const data = await response.json();
            
            if (data.success) {
                this.subscribedUsers = new Set(data.subscriptions);
            }
        } catch (error) {
            console.error('Error loading user subscriptions:', error);
        }
    }
    
    /**
     * Load alert history
     */
    async loadAlertHistory() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/get-alert-history.php?limit=50`);
            const data = await response.json();
            
            if (data.success) {
                this.alertHistory = data.history.map(item => ({
                    alert: this.processAlert(item.alert),
                    timestamp: new Date(item.timestamp),
                    notificationsSent: item.notifications_sent || []
                }));
            }
        } catch (error) {
            console.error('Error loading alert history:', error);
        }
    }
    
    /**
     * Mute alert type for specific item
     */
    muteAlert(inventoryItemId, alertType, duration = 3600000) { // 1 hour default
        const muteKey = `${inventoryItemId}_${alertType}`;
        this.mutedAlerts.add(muteKey);
        
        // Automatically unmute after duration
        setTimeout(() => {
            this.mutedAlerts.delete(muteKey);
        }, duration);
        
        console.log(`Muted ${alertType} alerts for item ${inventoryItemId} for ${duration / 1000} seconds`);
    }
    
    /**
     * Get alert statistics
     */
    getAlertStatistics() {
        const stats = {
            total: this.alerts.size,
            unacknowledged: 0,
            critical: 0,
            high: 0,
            medium: 0,
            low: 0,
            byType: {}
        };
        
        this.alerts.forEach(alert => {
            if (!alert.isAcknowledged) {
                stats.unacknowledged++;
                stats[alert.priority]++;
            }
            
            if (!stats.byType[alert.alertType]) {
                stats.byType[alert.alertType] = 0;
            }
            stats.byType[alert.alertType]++;
        });
        
        return stats;
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
        document.dispatchEvent(new CustomEvent(`stockAlert${eventName.charAt(0).toUpperCase() + eventName.slice(1)}`, {
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
     * Cleanup resources
     */
    destroy() {
        if (this.checkTimer) {
            clearInterval(this.checkTimer);
        }
        
        this.alerts.clear();
        this.alertHistory = [];
        this.subscribedUsers.clear();
        this.mutedAlerts.clear();
        
        console.log('Stock Alert System destroyed');
    }
    
    // Placeholder methods for future implementation
    async notifySalesTeam(alert) { console.log('Notifying sales team:', alert.message); }
    async disableProduct(alert) { console.log('Disabling product:', alert.itemName); }
    async notifyPurchasingTeam(alert) { console.log('Notifying purchasing team:', alert.message); }
    async createReorderRecommendation(alert) { console.log('Creating reorder recommendation:', alert.itemName); }
    async startUsageTracking(alert) { console.log('Starting usage tracking:', alert.itemName); }
    async contactSupplier(alert) { console.log('Contacting supplier for:', alert.itemName); }
    async findAlternativeSupplier(alert) { console.log('Finding alternative supplier for:', alert.itemName); }
    async analyzeConsumptionPattern(alert) { console.log('Analyzing consumption pattern for:', alert.itemName); }
}

// Initialize stock alerts system
document.addEventListener('DOMContentLoaded', () => {
    const config = {
        apiBaseUrl: '/api/inventory',
        checkInterval: 60000, // 1 minute
        enableNotifications: true,
        enableEmailAlerts: true,
        enableSlackIntegration: false,
        autoReorder: false, // Set to true to enable automatic reordering
        alertThresholds: {
            critical: 5,
            low: 10
        }
    };
    
    window.stockAlerts = new StockAlerts(config);
    
    // Setup global event handlers
    window.stockAlerts.on('alertCreated', (data) => {
        console.log('New stock alert created:', data.alert.message);
    });
    
    window.stockAlerts.on('alertResolved', (data) => {
        console.log('Stock alert resolved:', data.alert.message);
    });
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StockAlerts;
}