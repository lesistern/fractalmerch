/**
 * ANALYTICS TRACKER - Sistema de Tracking Completo para ROI
 * Mide conversiones, comportamiento de usuario y efectividad de optimizaciones
 * Author: Claude Data Scientist AI
 * Version: 1.0
 */

class AnalyticsTracker {
    constructor() {
        this.sessionId = this.generateSessionId();
        this.userId = this.getUserId();
        this.startTime = Date.now();
        this.events = [];
        this.metrics = this.initializeMetrics();
        
        // Configuración de tracking
        this.config = {
            trackingEnabled: true,
            sessionTimeout: 30 * 60 * 1000, // 30 minutos
            batchSize: 10,
            sendInterval: 5000, // 5 segundos
            enableConsoleLogging: true
        };

        this.initializeTracking();
        this.startPeriodicSave();
        
        // Bind events
        this.bindExitIntentTracking();
        this.bindBundleTracking();
        this.bindShippingProgressTracking();
        this.bindDeviceTracking();
        this.bindTimeToThresholdTracking();
        
        console.log('🔥 Analytics Tracker iniciado - Session:', this.sessionId);
    }

    // =====================================
    // INICIALIZACIÓN Y CONFIGURACIÓN
    // =====================================
    
    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    getUserId() {
        let userId = localStorage.getItem('analytics_user_id');
        if (!userId) {
            userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('analytics_user_id', userId);
        }
        return userId;
    }

    initializeMetrics() {
        const defaultMetrics = {
            // Exit Intent Popup Metrics
            exitIntent: {
                popupShows: 0,
                emailCaptures: 0,
                conversionRate: 0,
                dismissals: 0,
                timeToShow: []
            },
            
            // Bundle Kit Home Office Metrics
            bundleKit: {
                productViews: 0,
                bundleViews: 0,
                bundleAdds: 0,
                ordersWithBundle: 0,
                ordersWithoutBundle: 0,
                attachRate: 0,
                bundleRevenue: 0
            },
            
            // Shipping Progress Bar Metrics
            shippingProgress: {
                cartValues: [],
                productsPerOrder: [],
                progressInteractions: 0,
                thresholdReached: 0,
                averageCartIncrease: 0
            },
            
            // Mobile vs Desktop Conversion
            deviceConversion: {
                mobile: {
                    sessions: 0,
                    conversions: 0,
                    conversionRate: 0,
                    revenue: 0
                },
                desktop: {
                    sessions: 0,
                    conversions: 0,
                    conversionRate: 0,
                    revenue: 0
                },
                tablet: {
                    sessions: 0,
                    conversions: 0,
                    conversionRate: 0,
                    revenue: 0
                }
            },
            
            // Time to Free Shipping Threshold
            freeShippingThreshold: {
                attempts: [],
                successfulReaches: 0,
                averageTime: 0,
                averageProducts: 0,
                abandonedAttempts: 0
            },
            
            // General Session Metrics
            session: {
                pageViews: 0,
                timeOnSite: 0,
                bounceRate: 0,
                device: this.detectDevice(),
                browser: this.detectBrowser(),
                referrer: document.referrer || 'direct',
                firstVisit: !localStorage.getItem('analytics_returning_user')
            }
        };

        // Cargar métricas existentes
        const existingMetrics = localStorage.getItem('analytics_metrics');
        if (existingMetrics) {
            try {
                const parsed = JSON.parse(existingMetrics);
                return { ...defaultMetrics, ...parsed };
            } catch (e) {
                console.error('Error parsing existing metrics:', e);
            }
        }

        return defaultMetrics;
    }

    initializeTracking() {
        // Marcar como usuario recurrente
        localStorage.setItem('analytics_returning_user', 'true');
        
        // Tracking inicial de sesión
        this.trackEvent('session_start', {
            sessionId: this.sessionId,
            userId: this.userId,
            device: this.metrics.session.device,
            browser: this.metrics.session.browser,
            referrer: this.metrics.session.referrer,
            timestamp: Date.now(),
            url: window.location.href
        });

        // Increment device session count
        this.metrics.deviceConversion[this.metrics.session.device].sessions++;
        
        // Track page view
        this.trackPageView();
        
        // Bind general events
        this.bindGeneralEvents();
    }

    // =====================================
    // TRACKING DE EXIT INTENT POPUP
    // =====================================
    
    bindExitIntentTracking() {
        let exitIntentTriggered = false;
        let mouseLeaveTime = 0;

        // Detectar movimiento de salida del mouse
        document.addEventListener('mouseleave', (e) => {
            if (e.clientY <= 0 && !exitIntentTriggered) {
                mouseLeaveTime = Date.now();
                this.trackExitIntentTrigger();
                exitIntentTriggered = true;
            }
        });

        // Tracking de popup shows (cuando se muestra realmente)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1 && (
                        node.classList?.contains('exit-intent-popup') ||
                        node.querySelector?.('.exit-intent-popup')
                    )) {
                        this.trackExitIntentPopupShow(mouseLeaveTime);
                    }
                });
            });
        });
        
        observer.observe(document.body, { childList: true, subtree: true });

        // Tracking de email capture
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.classList.contains('exit-intent-form') || 
                form.closest('.exit-intent-popup')) {
                this.trackExitIntentEmailCapture(form);
            }
        });

        // Tracking de dismissals
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('exit-intent-close') ||
                e.target.closest('.exit-intent-close')) {
                this.trackExitIntentDismissal();
            }
        });
    }

    trackExitIntentTrigger() {
        this.trackEvent('exit_intent_trigger', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            timeOnPage: Date.now() - this.startTime,
            url: window.location.href
        });
    }

    trackExitIntentPopupShow(triggerTime) {
        const showTime = Date.now();
        const timeToShow = triggerTime ? showTime - triggerTime : 0;
        
        this.metrics.exitIntent.popupShows++;
        this.metrics.exitIntent.timeToShow.push(timeToShow);
        
        this.trackEvent('exit_intent_popup_show', {
            sessionId: this.sessionId,
            timestamp: showTime,
            timeToShow: timeToShow,
            url: window.location.href
        });

        this.saveMetrics();
    }

    trackExitIntentEmailCapture(form) {
        const email = form.querySelector('input[type="email"]')?.value;
        
        this.metrics.exitIntent.emailCaptures++;
        this.updateExitIntentConversionRate();
        
        this.trackEvent('exit_intent_email_capture', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            email: email ? this.hashEmail(email) : null,
            url: window.location.href
        });

        this.saveMetrics();
    }

    trackExitIntentDismissal() {
        this.metrics.exitIntent.dismissals++;
        
        this.trackEvent('exit_intent_dismissal', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            url: window.location.href
        });

        this.saveMetrics();
    }

    updateExitIntentConversionRate() {
        if (this.metrics.exitIntent.popupShows > 0) {
            this.metrics.exitIntent.conversionRate = 
                (this.metrics.exitIntent.emailCaptures / this.metrics.exitIntent.popupShows) * 100;
        }
    }

    // =====================================
    // TRACKING DE BUNDLE KIT HOME OFFICE
    // =====================================
    
    bindBundleTracking() {
        // Track product views
        if (window.location.pathname.includes('product-detail.php') ||
            window.location.pathname.includes('particulares.php')) {
            this.trackProductView();
        }

        // Track bundle views (cuando se muestra la sección de bundle)
        const bundleElements = document.querySelectorAll('[data-bundle], .bundle-kit, .home-office-bundle');
        bundleElements.forEach(element => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.trackBundleView();
                        observer.unobserve(entry.target);
                    }
                });
            });
            observer.observe(element);
        });

        // Track bundle adds to cart
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-bundle-add]') || 
                e.target.closest('.add-bundle-to-cart')) {
                this.trackBundleAdd();
            }
        });

        // Track orders (listening for cart checkout)
        this.bindOrderTracking();
    }

    trackProductView() {
        this.metrics.bundleKit.productViews++;
        
        this.trackEvent('product_view', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            url: window.location.href,
            productId: this.getProductIdFromUrl()
        });

        this.saveMetrics();
    }

    trackBundleView() {
        this.metrics.bundleKit.bundleViews++;
        
        this.trackEvent('bundle_view', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            url: window.location.href
        });

        this.saveMetrics();
    }

    trackBundleAdd() {
        this.metrics.bundleKit.bundleAdds++;
        
        this.trackEvent('bundle_add_to_cart', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            url: window.location.href
        });

        this.saveMetrics();
    }

    bindOrderTracking() {
        // Listen for checkout completion
        document.addEventListener('checkout_completed', (e) => {
            this.trackOrder(e.detail);
        });

        // Alternative: listen for successful purchase page
        if (window.location.pathname.includes('order-success') ||
            window.location.search.includes('purchase=success')) {
            this.trackOrderFromUrl();
        }
    }

    trackOrder(orderData) {
        const hasBundle = orderData?.items?.some(item => 
            item.isBundle || item.category === 'bundle' || item.name.includes('Bundle')
        );

        if (hasBundle) {
            this.metrics.bundleKit.ordersWithBundle++;
            this.metrics.bundleKit.bundleRevenue += (orderData.total || 0);
        } else {
            this.metrics.bundleKit.ordersWithoutBundle++;
        }

        this.updateBundleAttachRate();
        
        this.trackEvent('order_completed', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            hasBundle: hasBundle,
            orderValue: orderData.total || 0,
            itemCount: orderData.items?.length || 0
        });

        // Track device conversion
        this.metrics.deviceConversion[this.metrics.session.device].conversions++;
        this.metrics.deviceConversion[this.metrics.session.device].revenue += (orderData.total || 0);
        this.updateDeviceConversionRates();

        this.saveMetrics();
    }

    updateBundleAttachRate() {
        const totalOrders = this.metrics.bundleKit.ordersWithBundle + this.metrics.bundleKit.ordersWithoutBundle;
        if (totalOrders > 0) {
            this.metrics.bundleKit.attachRate = 
                (this.metrics.bundleKit.ordersWithBundle / totalOrders) * 100;
        }
    }

    // =====================================
    // TRACKING DE SHIPPING PROGRESS BAR
    // =====================================
    
    bindShippingProgressTracking() {
        // Track cart value changes
        this.trackCartValueChanges();
        
        // Track progress bar interactions
        document.addEventListener('click', (e) => {
            if (e.target.closest('.shipping-progress-bar') ||
                e.target.closest('[data-shipping-progress]')) {
                this.trackShippingProgressInteraction();
            }
        });

        // Track threshold reached
        this.monitorShippingThreshold();
    }

    trackCartValueChanges() {
        let lastCartValue = this.getCurrentCartValue();
        
        const checkCartValue = () => {
            const currentValue = this.getCurrentCartValue();
            if (currentValue !== lastCartValue) {
                this.trackCartValueChange(lastCartValue, currentValue);
                lastCartValue = currentValue;
            }
        };

        // Check every 2 seconds
        setInterval(checkCartValue, 2000);
        
        // Also check on storage changes
        window.addEventListener('storage', checkCartValue);
    }

    trackCartValueChange(oldValue, newValue) {
        this.metrics.shippingProgress.cartValues.push({
            timestamp: Date.now(),
            oldValue: oldValue,
            newValue: newValue,
            change: newValue - oldValue
        });

        this.trackEvent('cart_value_change', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            oldValue: oldValue,
            newValue: newValue,
            change: newValue - oldValue
        });

        this.calculateAverageCartIncrease();
        this.saveMetrics();
    }

    trackShippingProgressInteraction() {
        this.metrics.shippingProgress.progressInteractions++;
        
        this.trackEvent('shipping_progress_interaction', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            cartValue: this.getCurrentCartValue()
        });

        this.saveMetrics();
    }

    monitorShippingThreshold() {
        const freeShippingThreshold = 15000; // $15,000 para envío gratis
        let thresholdStartTime = null;
        
        const checkThreshold = () => {
            const cartValue = this.getCurrentCartValue();
            
            if (cartValue > 0 && cartValue < freeShippingThreshold && !thresholdStartTime) {
                // Usuario empezó a intentar alcanzar el threshold
                thresholdStartTime = Date.now();
                this.startFreeShippingAttempt();
            } else if (cartValue >= freeShippingThreshold && thresholdStartTime) {
                // Usuario alcanzó el threshold
                this.trackShippingThresholdReached(thresholdStartTime);
                thresholdStartTime = null;
            } else if (cartValue === 0 && thresholdStartTime) {
                // Usuario abandonó
                this.trackFreeShippingAbandoned(thresholdStartTime);
                thresholdStartTime = null;
            }
        };

        setInterval(checkThreshold, 3000);
    }

    startFreeShippingAttempt() {
        this.trackEvent('free_shipping_attempt_start', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            cartValue: this.getCurrentCartValue()
        });
    }

    trackShippingThresholdReached(startTime) {
        const timeToReach = Date.now() - startTime;
        const cartItems = this.getCurrentCartItems();
        
        this.metrics.shippingProgress.thresholdReached++;
        this.metrics.freeShippingThreshold.attempts.push({
            timestamp: Date.now(),
            timeToReach: timeToReach,
            success: true,
            productCount: cartItems.length
        });
        
        this.updateFreeShippingAverages();
        
        this.trackEvent('shipping_threshold_reached', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            timeToReach: timeToReach,
            productCount: cartItems.length,
            finalValue: this.getCurrentCartValue()
        });

        this.saveMetrics();
    }

    trackFreeShippingAbandoned(startTime) {
        const timeBeforeAbandon = Date.now() - startTime;
        
        this.metrics.freeShippingThreshold.abandonedAttempts++;
        this.metrics.freeShippingThreshold.attempts.push({
            timestamp: Date.now(),
            timeToReach: timeBeforeAbandon,
            success: false,
            productCount: 0
        });
        
        this.trackEvent('free_shipping_abandoned', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            timeBeforeAbandon: timeBeforeAbandon
        });

        this.saveMetrics();
    }

    calculateAverageCartIncrease() {
        const increases = this.metrics.shippingProgress.cartValues
            .filter(change => change.change > 0)
            .map(change => change.change);
            
        if (increases.length > 0) {
            this.metrics.shippingProgress.averageCartIncrease = 
                increases.reduce((sum, val) => sum + val, 0) / increases.length;
        }
    }

    updateFreeShippingAverages() {
        const successfulAttempts = this.metrics.freeShippingThreshold.attempts.filter(a => a.success);
        
        if (successfulAttempts.length > 0) {
            this.metrics.freeShippingThreshold.averageTime = 
                successfulAttempts.reduce((sum, a) => sum + a.timeToReach, 0) / successfulAttempts.length;
                
            this.metrics.freeShippingThreshold.averageProducts = 
                successfulAttempts.reduce((sum, a) => sum + a.productCount, 0) / successfulAttempts.length;
        }
    }

    // =====================================
    // TRACKING DE DISPOSITIVOS
    // =====================================
    
    bindDeviceTracking() {
        // Ya se trackea en initializeTracking y trackOrder
        // Agregar tracking adicional de comportamiento por dispositivo
        
        this.trackDeviceBehavior();
    }

    trackDeviceBehavior() {
        // Track scroll depth
        let maxScrollDepth = 0;
        
        const trackScroll = () => {
            const scrollDepth = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
            if (scrollDepth > maxScrollDepth) {
                maxScrollDepth = scrollDepth;
            }
        };

        window.addEventListener('scroll', trackScroll);
        
        // Track on page unload
        window.addEventListener('beforeunload', () => {
            this.trackEvent('page_engagement', {
                sessionId: this.sessionId,
                timestamp: Date.now(),
                device: this.metrics.session.device,
                maxScrollDepth: maxScrollDepth,
                timeOnPage: Date.now() - this.startTime
            });
        });
    }

    updateDeviceConversionRates() {
        Object.keys(this.metrics.deviceConversion).forEach(device => {
            const data = this.metrics.deviceConversion[device];
            if (data.sessions > 0) {
                data.conversionRate = (data.conversions / data.sessions) * 100;
            }
        });
    }

    // =====================================
    // UTILIDADES Y HELPERS
    // =====================================
    
    getCurrentCartValue() {
        try {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        } catch (e) {
            return 0;
        }
    }

    getCurrentCartItems() {
        try {
            return JSON.parse(localStorage.getItem('cart') || '[]');
        } catch (e) {
            return [];
        }
    }

    getProductIdFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id') || 'unknown';
    }

    detectDevice() {
        const width = window.innerWidth;
        if (width <= 768) return 'mobile';
        if (width <= 1024) return 'tablet';
        return 'desktop';
    }

    detectBrowser() {
        const userAgent = navigator.userAgent;
        if (userAgent.includes('Chrome')) return 'chrome';
        if (userAgent.includes('Firefox')) return 'firefox';
        if (userAgent.includes('Safari')) return 'safari';
        if (userAgent.includes('Edge')) return 'edge';
        return 'other';
    }

    hashEmail(email) {
        // Simple hash for privacy
        let hash = 0;
        for (let i = 0; i < email.length; i++) {
            const char = email.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return hash.toString();
    }

    // =====================================
    // EVENTOS GENERALES
    // =====================================
    
    bindGeneralEvents() {
        // Track page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.trackEvent('page_hidden', {
                    sessionId: this.sessionId,
                    timestamp: Date.now(),
                    timeOnPage: Date.now() - this.startTime
                });
            } else {
                this.trackEvent('page_visible', {
                    sessionId: this.sessionId,
                    timestamp: Date.now()
                });
            }
        });

        // Track clicks on key elements
        document.addEventListener('click', (e) => {
            if (e.target.matches('button, .btn, a[href]')) {
                this.trackEvent('element_click', {
                    sessionId: this.sessionId,
                    timestamp: Date.now(),
                    element: e.target.tagName,
                    text: e.target.textContent?.slice(0, 50) || '',
                    href: e.target.href || '',
                    className: e.target.className
                });
            }
        });
    }

    trackPageView() {
        this.metrics.session.pageViews++;
        
        this.trackEvent('page_view', {
            sessionId: this.sessionId,
            timestamp: Date.now(),
            url: window.location.href,
            title: document.title,
            referrer: document.referrer
        });

        this.saveMetrics();
    }

    trackEvent(eventName, eventData) {
        const event = {
            id: this.generateEventId(),
            name: eventName,
            data: eventData,
            timestamp: Date.now(),
            sessionId: this.sessionId,
            userId: this.userId
        };

        this.events.push(event);
        
        if (this.config.enableConsoleLogging) {
            console.log('📊 Analytics Event:', eventName, eventData);
        }

        // Auto-save if batch is full
        if (this.events.length >= this.config.batchSize) {
            this.saveEvents();
        }
    }

    generateEventId() {
        return 'event_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // =====================================
    // PERSISTENCIA DE DATOS
    // =====================================
    
    saveMetrics() {
        try {
            localStorage.setItem('analytics_metrics', JSON.stringify(this.metrics));
        } catch (e) {
            console.error('Error saving metrics:', e);
        }
    }

    saveEvents() {
        try {
            const existingEvents = JSON.parse(localStorage.getItem('analytics_events') || '[]');
            const allEvents = [...existingEvents, ...this.events];
            
            // Keep only last 1000 events to prevent storage overflow
            const recentEvents = allEvents.slice(-1000);
            
            localStorage.setItem('analytics_events', JSON.stringify(recentEvents));
            this.events = []; // Clear current batch
        } catch (e) {
            console.error('Error saving events:', e);
        }
    }

    startPeriodicSave() {
        setInterval(() => {
            if (this.events.length > 0) {
                this.saveEvents();
            }
            this.saveMetrics();
        }, this.config.sendInterval);
    }

    // =====================================
    // API PÚBLICA
    // =====================================
    
    getMetrics() {
        return { ...this.metrics };
    }

    getEvents(limit = 100) {
        try {
            const events = JSON.parse(localStorage.getItem('analytics_events') || '[]');
            return events.slice(-limit);
        } catch (e) {
            return [];
        }
    }

    generateReport() {
        const report = {
            generated: new Date().toISOString(),
            sessionId: this.sessionId,
            userId: this.userId,
            metrics: this.getMetrics(),
            summary: {
                exitIntentROI: this.calculateExitIntentROI(),
                bundleKitROI: this.calculateBundleKitROI(),
                shippingProgressROI: this.calculateShippingProgressROI(),
                devicePerformance: this.getDevicePerformance(),
                freeShippingEffectiveness: this.getFreeShippingEffectiveness()
            }
        };

        return report;
    }

    calculateExitIntentROI() {
        const metrics = this.metrics.exitIntent;
        return {
            conversionRate: metrics.conversionRate,
            totalShows: metrics.popupShows,
            totalCaptures: metrics.emailCaptures,
            averageTimeToShow: metrics.timeToShow.length > 0 ? 
                metrics.timeToShow.reduce((a, b) => a + b, 0) / metrics.timeToShow.length : 0,
            dismissalRate: metrics.popupShows > 0 ? (metrics.dismissals / metrics.popupShows) * 100 : 0
        };
    }

    calculateBundleKitROI() {
        const metrics = this.metrics.bundleKit;
        return {
            attachRate: metrics.attachRate,
            bundleRevenue: metrics.bundleRevenue,
            totalOrders: metrics.ordersWithBundle + metrics.ordersWithoutBundle,
            bundleViewToAddRate: metrics.bundleViews > 0 ? (metrics.bundleAdds / metrics.bundleViews) * 100 : 0
        };
    }

    calculateShippingProgressROI() {
        const metrics = this.metrics.shippingProgress;
        return {
            averageCartIncrease: metrics.averageCartIncrease,
            totalInteractions: metrics.progressInteractions,
            thresholdReached: metrics.thresholdReached,
            cartOptimizations: metrics.cartValues.filter(v => v.change > 0).length
        };
    }

    getDevicePerformance() {
        return this.metrics.deviceConversion;
    }

    getFreeShippingEffectiveness() {
        const metrics = this.metrics.freeShippingThreshold;
        return {
            averageTimeToReach: metrics.averageTime,
            averageProductsToReach: metrics.averageProducts,
            successfulAttempts: metrics.successfulReaches,
            abandonedAttempts: metrics.abandonedAttempts,
            successRate: (metrics.successfulReaches + metrics.abandonedAttempts) > 0 ? 
                (metrics.successfulReaches / (metrics.successfulReaches + metrics.abandonedAttempts)) * 100 : 0
        };
    }

    // Método para limpiar datos (para testing)
    clearAllData() {
        localStorage.removeItem('analytics_metrics');
        localStorage.removeItem('analytics_events');
        localStorage.removeItem('analytics_user_id');
        this.metrics = this.initializeMetrics();
        this.events = [];
        console.log('🧹 Analytics data cleared');
    }
}

// Inicializar automáticamente
window.AnalyticsTracker = AnalyticsTracker;

// Auto-inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    if (!window.analyticsTracker) {
        window.analyticsTracker = new AnalyticsTracker();
    }
});

// Export para uso en otros archivos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AnalyticsTracker;
}