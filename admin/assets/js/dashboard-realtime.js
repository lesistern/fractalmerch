/**
 * ENTERPRISE DASHBOARD REAL-TIME CLIENT
 * Professional JavaScript client for real-time dashboard updates
 * 
 * Features:
 * - Real-time metrics updates via AJAX polling
 * - WebSocket support for instant updates
 * - Automatic error handling and retry logic
 * - Performance monitoring and optimization
 * - Professional UI animations and notifications
 * - Caching and rate limiting
 * - Service Worker integration for offline support
 * 
 * @author Claude Assistant
 * @version 1.0.0 Enterprise
 * @since 2025-01-07
 */

class DashboardRealTime {
    constructor(options = {}) {
        this.config = {
            apiBaseUrl: './api/dashboard_api.php',
            updateInterval: 30000, // 30 seconds
            retryInterval: 5000,    // 5 seconds
            maxRetries: 3,
            enableWebSocket: true,
            enableNotifications: true,
            enableCache: true,
            debug: false,
            ...options
        };
        
        this.state = {
            isRunning: false,
            retryCount: 0,
            lastUpdate: null,
            connectionStatus: 'disconnected',
            cache: new Map(),
            updateQueue: []
        };
        
        this.eventListeners = new Map();
        this.updateTimer = null;
        this.websocket = null;
        this.performanceMonitor = new PerformanceMonitor();
        
        this.init();
    }
    
    /**
     * Initialize the real-time dashboard system
     */
    init() {
        this.log('Initializing Dashboard Real-Time System');
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Initialize WebSocket if enabled
        if (this.config.enableWebSocket) {
            this.initWebSocket();
        }
        
        // Setup visibility change handling
        this.setupVisibilityHandling();
        
        // Setup performance monitoring
        this.setupPerformanceMonitoring();
        
        // Setup notification system
        if (this.config.enableNotifications) {
            this.initNotificationSystem();
        }
        
        // Start real-time updates
        this.start();
        
        // Setup service worker for offline support
        this.setupServiceWorker();
    }
    
    /**
     * Start real-time updates
     */
    start() {
        if (this.state.isRunning) return;
        
        this.log('Starting real-time updates');
        this.state.isRunning = true;
        this.state.connectionStatus = 'connecting';
        
        // Initial load
        this.fetchDashboardStats();
        
        // Start polling
        this.startPolling();
        
        // Update connection status indicator
        this.updateConnectionStatus('connected');
    }
    
    /**
     * Stop real-time updates
     */
    stop() {
        this.log('Stopping real-time updates');
        this.state.isRunning = false;
        this.state.connectionStatus = 'disconnected';
        
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
            this.updateTimer = null;
        }
        
        if (this.websocket) {
            this.websocket.close();
        }
        
        this.updateConnectionStatus('disconnected');
    }
    
    /**
     * Start polling for updates
     */
    startPolling() {
        this.updateTimer = setInterval(() => {
            if (this.state.isRunning) {
                this.fetchRealTimeData();
            }
        }, this.config.updateInterval);
    }
    
    /**
     * Fetch dashboard statistics
     */
    async fetchDashboardStats(forceRefresh = false) {
        const startTime = performance.now();
        
        try {
            const url = forceRefresh ? 
                `${this.config.apiBaseUrl}?endpoint=stats/refresh` :
                `${this.config.apiBaseUrl}?endpoint=stats`;
            
            const response = await this.fetchWithRetry(url);
            
            if (response.success) {
                this.updateDashboardStats(response.data);
                this.state.lastUpdate = new Date();
                this.state.retryCount = 0;
                
                // Update performance metrics
                const duration = performance.now() - startTime;
                this.performanceMonitor.recordApiCall('stats', duration);
                
                // Cache the data
                if (this.config.enableCache) {
                    this.cache.set('dashboard_stats', {
                        data: response.data,
                        timestamp: Date.now(),
                        ttl: 300000 // 5 minutes
                    });
                }
                
                this.emit('statsUpdated', response.data);
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            this.handleError('Failed to fetch dashboard stats', error);
        }
    }
    
    /**
     * Fetch real-time data
     */
    async fetchRealTimeData() {
        const startTime = performance.now();
        
        try {
            const response = await this.fetchWithRetry(
                `${this.config.apiBaseUrl}?endpoint=real-time`
            );
            
            if (response.success) {
                this.updateRealTimeMetrics(response.data);
                
                const duration = performance.now() - startTime;
                this.performanceMonitor.recordApiCall('real-time', duration);
                
                this.emit('realTimeUpdated', response.data);
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            this.handleError('Failed to fetch real-time data', error);
        }
    }
    
    /**
     * Fetch with retry logic
     */
    async fetchWithRetry(url, options = {}) {
        const maxRetries = options.maxRetries || this.config.maxRetries;
        
        for (let attempt = 0; attempt <= maxRetries; attempt++) {
            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...options.headers
                    },
                    credentials: 'same-origin',
                    ...options
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                return data;
                
            } catch (error) {
                if (attempt === maxRetries) {
                    throw error;
                }
                
                const delay = this.config.retryInterval * Math.pow(2, attempt); // Exponential backoff
                await this.sleep(delay);
            }
        }
    }
    
    /**
     * Update dashboard statistics in the UI
     */
    updateDashboardStats(stats) {
        // Update metric cards
        this.updateMetricCard('total-revenue', stats.total_revenue, 'currency');
        this.updateMetricCard('total-orders', stats.total_orders, 'number');
        this.updateMetricCard('total-products', stats.total_products, 'number');
        this.updateMetricCard('total-users', stats.total_users, 'number');
        this.updateMetricCard('active-sessions', stats.active_sessions, 'number');
        this.updateMetricCard('conversion-rate', stats.conversion_rate, 'percentage');
        
        // Update trend indicators
        this.updateTrendIndicator('revenue-trend', stats.revenue_growth_rate);
        this.updateTrendIndicator('order-trend', stats.order_growth_rate);
        this.updateTrendIndicator('user-trend', stats.user_growth_rate);
        
        // Update charts
        if (stats.sales_data) {
            this.updateSalesChart(stats.sales_data);
        }
        
        if (stats.top_products) {
            this.updateTopProductsChart(stats.top_products);
        }
        
        // Update inventory alerts
        if (stats.inventory_alerts) {
            this.updateInventoryAlerts(stats.inventory_alerts);
        }
        
        // Update recent activity
        if (stats.recent_orders) {
            this.updateRecentActivity(stats.recent_orders);
        }
    }
    
    /**
     * Update real-time metrics
     */
    updateRealTimeMetrics(data) {
        // Update real-time counters
        this.updateRealTimeCounter('active-users-realtime', data.active_users);
        this.updateRealTimeCounter('current-cart-value', data.current_cart_value, 'currency');
        this.updateRealTimeCounter('pending-notifications', data.pending_notifications);
        
        // Update system health
        if (data.system_health) {
            this.updateSystemHealth(data.system_health);
        }
        
        // Update server metrics
        if (data.server_load) {
            this.updateServerLoad(data.server_load);
        }
    }
    
    /**
     * Update metric card with animation
     */
    updateMetricCard(cardId, value, format = 'number') {
        const card = document.querySelector(`[data-metric="${cardId}"]`);
        if (!card) return;
        
        const valueElement = card.querySelector('.metric-value') || card.querySelector('h3');
        if (!valueElement) return;
        
        const currentValue = this.parseValue(valueElement.textContent);
        const newValue = parseFloat(value) || 0;
        
        if (currentValue !== newValue) {
            // Animate the change
            this.animateValue(valueElement, currentValue, newValue, format);
            
            // Add pulse effect
            card.classList.add('metric-updated');
            setTimeout(() => card.classList.remove('metric-updated'), 1000);
        }
    }
    
    /**
     * Animate value change
     */
    animateValue(element, start, end, format, duration = 1000) {
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            
            const current = start + (end - start) * easeOut;
            element.textContent = this.formatValue(current, format);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }
    
    /**
     * Update trend indicator
     */
    updateTrendIndicator(indicatorId, value) {
        const indicator = document.querySelector(`[data-trend="${indicatorId}"]`);
        if (!indicator) return;
        
        const numericValue = parseFloat(value) || 0;
        const isPositive = numericValue > 0;
        const isNegative = numericValue < 0;
        
        // Update classes
        indicator.classList.remove('positive', 'negative', 'neutral');
        if (isPositive) {
            indicator.classList.add('positive');
        } else if (isNegative) {
            indicator.classList.add('negative');
        } else {
            indicator.classList.add('neutral');
        }
        
        // Update icon
        const icon = indicator.querySelector('i');
        if (icon) {
            icon.className = isPositive ? 'fas fa-arrow-up' : 
                           isNegative ? 'fas fa-arrow-down' : 'fas fa-minus';
        }
        
        // Update text
        const text = indicator.querySelector('.trend-text');
        if (text) {
            text.textContent = `${Math.abs(numericValue).toFixed(1)}%`;
        }
    }
    
    /**
     * Update real-time counter with pulse effect
     */
    updateRealTimeCounter(counterId, value, format = 'number') {
        const counter = document.querySelector(`[data-realtime="${counterId}"]`);
        if (!counter) return;
        
        const currentValue = this.parseValue(counter.textContent);
        const newValue = parseFloat(value) || 0;
        
        if (currentValue !== newValue) {
            // Update value with animation
            this.animateValue(counter, currentValue, newValue, format, 500);
            
            // Add pulse effect
            counter.classList.add('realtime-pulse');
            setTimeout(() => counter.classList.remove('realtime-pulse'), 500);
        }
    }
    
    /**
     * Update system health indicators
     */
    updateSystemHealth(health) {
        const healthIndicator = document.querySelector('.system-health-indicator');
        if (!healthIndicator) return;
        
        // Update overall status
        const statusElement = healthIndicator.querySelector('.health-status');
        if (statusElement) {
            statusElement.className = `health-status ${health.status}`;
            statusElement.textContent = health.status.toUpperCase();
        }
        
        // Update individual components
        Object.entries(health).forEach(([component, data]) => {
            if (component === 'overall' || component === 'status') return;
            
            const componentElement = healthIndicator.querySelector(`[data-component="${component}"]`);
            if (componentElement && typeof data === 'object' && data.score !== undefined) {
                const scoreElement = componentElement.querySelector('.component-score');
                const statusElement = componentElement.querySelector('.component-status');
                
                if (scoreElement) {
                    scoreElement.textContent = `${data.score}%`;
                }
                
                if (statusElement) {
                    statusElement.className = `component-status ${data.status || 'unknown'}`;
                }
            }
        });
    }
    
    /**
     * Update server load meters
     */
    updateServerLoad(loadData) {
        ['1_min', '5_min', '15_min'].forEach(period => {
            const meter = document.querySelector(`[data-load="${period}"]`);
            if (meter && loadData[period] !== undefined) {
                const value = parseFloat(loadData[period]);
                const percentage = Math.min(value * 25, 100); // Assuming 4-core system
                
                const bar = meter.querySelector('.load-bar');
                if (bar) {
                    bar.style.width = `${percentage}%`;
                    bar.className = `load-bar ${percentage > 80 ? 'high' : percentage > 60 ? 'medium' : 'low'}`;
                }
                
                const valueElement = meter.querySelector('.load-value');
                if (valueElement) {
                    valueElement.textContent = value.toFixed(2);
                }
            }
        });
    }
    
    /**
     * Setup WebSocket connection
     */
    initWebSocket() {
        if (!window.WebSocket) {
            this.log('WebSocket not supported');
            return;
        }
        
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsUrl = `${protocol}//${window.location.host}/ws/dashboard`;
        
        this.websocket = new WebSocket(wsUrl);
        
        this.websocket.onopen = () => {
            this.log('WebSocket connected');
            this.updateConnectionStatus('websocket');
        };
        
        this.websocket.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                this.handleWebSocketMessage(data);
            } catch (error) {
                this.log('Invalid WebSocket message', error);
            }
        };
        
        this.websocket.onclose = () => {
            this.log('WebSocket disconnected');
            this.updateConnectionStatus('polling');
            
            // Attempt to reconnect after delay
            setTimeout(() => {
                if (this.state.isRunning) {
                    this.initWebSocket();
                }
            }, 5000);
        };
        
        this.websocket.onerror = (error) => {
            this.log('WebSocket error', error);
        };
    }
    
    /**
     * Handle WebSocket messages
     */
    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'stats_update':
                this.updateDashboardStats(data.payload);
                break;
            case 'realtime_update':
                this.updateRealTimeMetrics(data.payload);
                break;
            case 'notification':
                this.showNotification(data.payload);
                break;
            case 'alert':
                this.showAlert(data.payload);
                break;
            default:
                this.log('Unknown WebSocket message type', data.type);
        }
    }
    
    /**
     * Setup performance monitoring
     */
    setupPerformanceMonitoring() {
        // Monitor page performance
        if (window.PerformanceObserver) {
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    this.performanceMonitor.recordPerformanceEntry(entry);
                }
            });
            
            observer.observe({ entryTypes: ['measure', 'navigation', 'resource'] });
        }
        
        // Monitor memory usage
        if (window.performance && window.performance.memory) {
            setInterval(() => {
                this.performanceMonitor.recordMemoryUsage(window.performance.memory);
            }, 60000); // Every minute
        }
    }
    
    /**
     * Setup visibility change handling
     */
    setupVisibilityHandling() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Reduce update frequency when page is hidden
                this.config.updateInterval = 60000; // 1 minute
                this.log('Page hidden, reducing update frequency');
            } else {
                // Resume normal frequency when page is visible
                this.config.updateInterval = 30000; // 30 seconds
                this.log('Page visible, resuming normal update frequency');
                
                // Fetch fresh data immediately
                this.fetchRealTimeData();
            }
            
            // Restart polling with new interval
            if (this.updateTimer) {
                clearInterval(this.updateTimer);
                this.startPolling();
            }
        });
    }
    
    /**
     * Setup notification system
     */
    initNotificationSystem() {
        if ('Notification' in window) {
            Notification.requestPermission().then(permission => {
                this.log('Notification permission:', permission);
            });
        }
    }
    
    /**
     * Show browser notification
     */
    showNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.message,
                icon: '/assets/images/icon-192.png',
                badge: '/assets/images/icon-192.png',
                tag: notification.tag || 'dashboard',
                requireInteraction: notification.important || false
            });
        }
        
        // Also show in-app notification
        this.showInAppNotification(notification);
    }
    
    /**
     * Show in-app notification
     */
    showInAppNotification(notification) {
        const container = document.querySelector('.notifications-container') || 
                         this.createNotificationContainer();
        
        const notificationEl = document.createElement('div');
        notificationEl.className = `notification ${notification.type || 'info'}`;
        notificationEl.innerHTML = `
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
            </div>
            <button class="notification-close">&times;</button>
        `;
        
        // Add close functionality
        notificationEl.querySelector('.notification-close').addEventListener('click', () => {
            notificationEl.remove();
        });
        
        // Auto-remove after delay
        setTimeout(() => {
            if (notificationEl.parentNode) {
                notificationEl.remove();
            }
        }, notification.duration || 5000);
        
        container.appendChild(notificationEl);
        
        // Animate in
        requestAnimationFrame(() => {
            notificationEl.classList.add('show');
        });
    }
    
    /**
     * Create notification container
     */
    createNotificationContainer() {
        const container = document.createElement('div');
        container.className = 'notifications-container';
        document.body.appendChild(container);
        return container;
    }
    
    /**
     * Setup service worker
     */
    setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    this.log('Service Worker registered:', registration);
                })
                .catch(error => {
                    this.log('Service Worker registration failed:', error);
                });
        }
    }
    
    /**
     * Update connection status indicator
     */
    updateConnectionStatus(status) {
        const indicator = document.querySelector('.connection-status');
        if (indicator) {
            indicator.className = `connection-status ${status}`;
            
            const statusText = {
                'connected': 'Connected',
                'connecting': 'Connecting...',
                'disconnected': 'Disconnected',
                'websocket': 'Real-time',
                'polling': 'Polling'
            };
            
            indicator.textContent = statusText[status] || status;
        }
    }
    
    /**
     * Handle errors with professional error handling
     */
    handleError(message, error) {
        this.log('Error:', message, error);
        
        this.state.retryCount++;
        
        if (this.state.retryCount >= this.config.maxRetries) {
            this.updateConnectionStatus('disconnected');
            this.showAlert({
                type: 'error',
                title: 'Connection Lost',
                message: 'Unable to connect to the server. Please check your connection.'
            });
        }
        
        // Emit error event
        this.emit('error', { message, error });
    }
    
    /**
     * Format value based on type
     */
    formatValue(value, format) {
        switch (format) {
            case 'currency':
                return new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS'
                }).format(value);
            case 'percentage':
                return `${value.toFixed(1)}%`;
            case 'number':
                return new Intl.NumberFormat('es-AR').format(Math.round(value));
            default:
                return value.toString();
        }
    }
    
    /**
     * Parse value from text
     */
    parseValue(text) {
        return parseFloat(text.replace(/[^\d.-]/g, '')) || 0;
    }
    
    /**
     * Event system
     */
    on(event, callback) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        this.eventListeners.get(event).push(callback);
    }
    
    emit(event, data) {
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    this.log('Error in event callback:', error);
                }
            });
        }
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Refresh button
        const refreshBtn = document.getElementById('refresh-data-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.fetchDashboardStats(true);
            });
        }
        
        // Export button
        const exportBtn = document.getElementById('export-report-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportReport();
            });
        }
        
        // Time period selector
        const timePeriodSelect = document.getElementById('time-period');
        if (timePeriodSelect) {
            timePeriodSelect.addEventListener('change', (e) => {
                this.updateTimePeriod(e.target.value);
            });
        }
    }
    
    /**
     * Export report
     */
    async exportReport() {
        try {
            const format = 'csv'; // Could be made configurable
            const response = await this.fetchWithRetry(
                `${this.config.apiBaseUrl}?endpoint=metrics/export&format=${format}`
            );
            
            if (response.success) {
                // Create download link
                const blob = new Blob([response.data], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `dashboard_report_${new Date().toISOString().split('T')[0]}.csv`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                this.showNotification({
                    type: 'success',
                    title: 'Export Complete',
                    message: 'Report has been downloaded successfully.'
                });
            }
        } catch (error) {
            this.showNotification({
                type: 'error',
                title: 'Export Failed',
                message: 'Unable to export report. Please try again.'
            });
        }
    }
    
    /**
     * Utility functions
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    log(...args) {
        if (this.config.debug) {
            console.log('[Dashboard Real-Time]', ...args);
        }
    }
}

/**
 * Performance Monitor Class
 */
class PerformanceMonitor {
    constructor() {
        this.metrics = {
            apiCalls: [],
            pageLoad: null,
            memoryUsage: [],
            errors: []
        };
    }
    
    recordApiCall(endpoint, duration) {
        this.metrics.apiCalls.push({
            endpoint,
            duration,
            timestamp: Date.now()
        });
        
        // Keep only last 100 calls
        if (this.metrics.apiCalls.length > 100) {
            this.metrics.apiCalls.shift();
        }
    }
    
    recordPerformanceEntry(entry) {
        if (entry.entryType === 'navigation') {
            this.metrics.pageLoad = {
                loadTime: entry.loadEventEnd - entry.loadEventStart,
                domContentLoaded: entry.domContentLoadedEventEnd - entry.domContentLoadedEventStart,
                timestamp: Date.now()
            };
        }
    }
    
    recordMemoryUsage(memory) {
        this.metrics.memoryUsage.push({
            used: memory.usedJSHeapSize,
            total: memory.totalJSHeapSize,
            limit: memory.jsHeapSizeLimit,
            timestamp: Date.now()
        });
        
        // Keep only last 60 measurements (1 hour with 1-minute intervals)
        if (this.metrics.memoryUsage.length > 60) {
            this.metrics.memoryUsage.shift();
        }
    }
    
    getStats() {
        return {
            ...this.metrics,
            avgApiResponseTime: this.getAverageApiResponseTime(),
            memoryTrend: this.getMemoryTrend()
        };
    }
    
    getAverageApiResponseTime() {
        if (this.metrics.apiCalls.length === 0) return 0;
        
        const total = this.metrics.apiCalls.reduce((sum, call) => sum + call.duration, 0);
        return total / this.metrics.apiCalls.length;
    }
    
    getMemoryTrend() {
        if (this.metrics.memoryUsage.length < 2) return 0;
        
        const latest = this.metrics.memoryUsage[this.metrics.memoryUsage.length - 1];
        const previous = this.metrics.memoryUsage[this.metrics.memoryUsage.length - 2];
        
        return latest.used - previous.used;
    }
}

// Initialize the dashboard real-time system when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardRealTime = new DashboardRealTime({
        debug: true // Enable debug logging in development
    });
    
    // Add CSS for real-time effects
    const style = document.createElement('style');
    style.textContent = `
        .metric-updated {
            animation: metricPulse 1s ease-out;
        }
        
        @keyframes metricPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .realtime-pulse {
            animation: realtimePulse 0.5s ease-out;
        }
        
        @keyframes realtimePulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; transform: scale(1.1); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        .connection-status {
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1001;
            transition: all 0.3s ease;
        }
        
        .connection-status.connected {
            background: #28a745;
            color: white;
        }
        
        .connection-status.websocket {
            background: #007bff;
            color: white;
        }
        
        .connection-status.polling {
            background: #ffc107;
            color: #212529;
        }
        
        .connection-status.disconnected {
            background: #dc3545;
            color: white;
        }
        
        .notifications-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }
        
        .notification {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-bottom: 10px;
            padding: 16px;
            display: flex;
            align-items: flex-start;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            border-left: 4px solid #007bff;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            border-left-color: #28a745;
        }
        
        .notification.error {
            border-left-color: #dc3545;
        }
        
        .notification.warning {
            border-left-color: #ffc107;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .notification-message {
            color: #6c757d;
            font-size: 14px;
        }
        
        .notification-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            color: #6c757d;
        }
        
        .system-health-indicator {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .health-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .health-status.healthy {
            background: #d4edda;
            color: #155724;
        }
        
        .health-status.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .health-status.critical {
            background: #f8d7da;
            color: #721c24;
        }
        
        .load-bar {
            height: 8px;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .load-bar.low {
            background: #28a745;
        }
        
        .load-bar.medium {
            background: #ffc107;
        }
        
        .load-bar.high {
            background: #dc3545;
        }
    `;
    document.head.appendChild(style);
});