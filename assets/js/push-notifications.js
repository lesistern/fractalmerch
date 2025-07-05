/**
 * FractalMerch Push Notifications System
 * Advanced web push notifications with personalization and automation
 */

class PushNotificationSystem {
    constructor() {
        this.config = {
            enablePushNotifications: true,
            enableAutomation: true,
            enablePersonalization: true,
            vapidPublicKey: 'YOUR_VAPID_PUBLIC_KEY', // Would be configured
            apiEndpoint: '/api/push-notifications/',
            swPath: '/sw.js',
            retryAttempts: 3,
            rateLimitPerHour: 24
        };
        
        this.notificationTypes = {
            welcome: {
                title: '¡Bienvenido a FractalMerch! 🎨',
                body: 'Empezá a crear tus diseños únicos',
                icon: '/assets/images/icon-192.png',
                badge: '/assets/images/badge-72.png',
                actions: [
                    { action: 'create', title: 'Crear Diseño', icon: '/assets/images/create-icon.png' },
                    { action: 'browse', title: 'Ver Productos', icon: '/assets/images/browse-icon.png' }
                ]
            },
            
            cart_reminder: {
                title: 'Tu carrito te está esperando 🛒',
                body: 'Finalizá tu pedido y obtené 10% OFF',
                icon: '/assets/images/cart-icon.png',
                badge: '/assets/images/badge-72.png',
                actions: [
                    { action: 'checkout', title: 'Finalizar Compra', icon: '/assets/images/checkout-icon.png' },
                    { action: 'view_cart', title: 'Ver Carrito', icon: '/assets/images/cart-icon.png' }
                ]
            },
            
            order_update: {
                title: 'Actualización de tu pedido 📦',
                body: 'Tu remera está {status}',
                icon: '/assets/images/order-icon.png',
                badge: '/assets/images/badge-72.png',
                actions: [
                    { action: 'track', title: 'Seguir Pedido', icon: '/assets/images/track-icon.png' }
                ]
            },
            
            new_product: {
                title: 'Nuevo producto disponible ✨',
                body: 'Descubrí {product_name} - Perfecto para vos',
                icon: '/assets/images/product-icon.png',
                badge: '/assets/images/badge-72.png',
                actions: [
                    { action: 'view_product', title: 'Ver Producto', icon: '/assets/images/view-icon.png' },
                    { action: 'customize', title: 'Personalizar', icon: '/assets/images/customize-icon.png' }
                ]
            },
            
            special_offer: {
                title: 'Oferta especial para vos 🎁',
                body: '{discount}% OFF en todos los productos - Solo por 24hs',
                icon: '/assets/images/offer-icon.png',
                badge: '/assets/images/badge-72.png',
                actions: [
                    { action: 'shop_now', title: 'Comprar Ahora', icon: '/assets/images/shop-icon.png' },
                    { action: 'view_offer', title: 'Ver Oferta', icon: '/assets/images/offer-icon.png' }
                ]
            },
            
            back_in_stock: {
                title: 'Producto disponible otra vez 📦',
                body: '{product_name} volvió al stock - ¡No te lo pierdas!',
                icon: '/assets/images/stock-icon.png',
                badge: '/assets/images/badge-72.png',
                actions: [
                    { action: 'buy_now', title: 'Comprar Ya', icon: '/assets/images/buy-icon.png' },
                    { action: 'view_product', title: 'Ver Producto', icon: '/assets/images/view-icon.png' }
                ]
            },
            
            design_inspiration: {
                title: 'Inspiración para tu próximo diseño 💡',
                body: 'Nuevas tendencias en personalización',
                icon: '/assets/images/inspiration-icon.png',
                badge: '/assets/images/badge-72.png',
                actions: [
                    { action: 'get_inspired', title: 'Ver Ideas', icon: '/assets/images/ideas-icon.png' },
                    { action: 'start_designing', title: 'Empezar a Diseñar', icon: '/assets/images/design-icon.png' }
                ]
            }
        };
        
        this.automationRules = {
            welcome_sequence: {
                triggers: ['user_registered'],
                notifications: [
                    { type: 'welcome', delay: 0 },
                    { type: 'design_inspiration', delay: 24 * 60 * 60 * 1000 }, // 24 hours
                    { type: 'special_offer', delay: 72 * 60 * 60 * 1000 } // 72 hours
                ]
            },
            
            cart_abandonment: {
                triggers: ['cart_abandoned'],
                notifications: [
                    { type: 'cart_reminder', delay: 60 * 60 * 1000 }, // 1 hour
                    { type: 'special_offer', delay: 24 * 60 * 60 * 1000 } // 24 hours
                ]
            },
            
            order_tracking: {
                triggers: ['order_placed', 'order_processing', 'order_shipped', 'order_delivered'],
                notifications: [
                    { type: 'order_update', delay: 0 }
                ]
            },
            
            re_engagement: {
                triggers: ['user_inactive_7_days'],
                notifications: [
                    { type: 'design_inspiration', delay: 0 },
                    { type: 'special_offer', delay: 48 * 60 * 60 * 1000 } // 48 hours
                ]
            },
            
            promotional: {
                triggers: ['seasonal_campaign', 'flash_sale', 'new_product_launch'],
                notifications: [
                    { type: 'special_offer', delay: 0 }
                ]
            }
        };
        
        this.segmentation = {
            new_users: {
                conditions: { days_since_signup: { '<': 7 } },
                notification_frequency: 'high'
            },
            active_users: {
                conditions: { last_activity: { '>': 7 * 24 * 60 * 60 * 1000 } },
                notification_frequency: 'medium'
            },
            vip_customers: {
                conditions: { purchase_count: { '>': 5 } },
                notification_frequency: 'low',
                special_offers: true
            },
            design_enthusiasts: {
                conditions: { time_in_editor: { '>': 300000 } }, // 5+ minutes
                notification_frequency: 'medium',
                design_content: true
            }
        };
        
        this.personalization = {
            user_name: '{user_name}',
            product_name: '{product_name}',
            discount: '{discount}',
            status: '{status}',
            favorite_category: '{favorite_category}'
        };
        
        this.analytics = {
            sent: 0,
            delivered: 0,
            clicked: 0,
            dismissed: 0,
            subscription_requests: 0,
            subscriptions_granted: 0,
            subscriptions_denied: 0
        };
        
        this.isSupported = 'serviceWorker' in navigator && 'PushManager' in window;
        this.subscription = null;
        this.swRegistration = null;
        
        this.init();
    }
    
    async init() {
        if (!this.isSupported) {
            console.log('Push notifications not supported');
            return;
        }
        
        console.log('🔔 Push Notification System initializing...');
        
        try {
            await this.registerServiceWorker();
            await this.checkSubscription();
            this.initEventListeners();
            this.startAutomationProcessor();
            
            console.log('✅ Push Notification System active');
        } catch (error) {
            console.error('Failed to initialize push notifications:', error);
        }
    }
    
    /**
     * SERVICE WORKER REGISTRATION
     */
    async registerServiceWorker() {
        try {
            this.swRegistration = await navigator.serviceWorker.register(this.config.swPath);
            console.log('Service Worker registered:', this.swRegistration);
            
            // Listen for service worker updates
            this.swRegistration.addEventListener('updatefound', () => {
                console.log('Service Worker update found');
            });
            
        } catch (error) {
            console.error('Service Worker registration failed:', error);
            throw error;
        }
    }
    
    /**
     * SUBSCRIPTION MANAGEMENT
     */
    async checkSubscription() {
        try {
            this.subscription = await this.swRegistration.pushManager.getSubscription();
            
            if (this.subscription) {
                console.log('User is subscribed to push notifications');
                this.sendSubscriptionToServer(this.subscription);
            } else {
                console.log('User is not subscribed to push notifications');
            }
        } catch (error) {
            console.error('Error checking subscription:', error);
        }
    }
    
    async requestPermission() {
        if (!this.isSupported) {
            throw new Error('Push notifications not supported');
        }
        
        this.analytics.subscription_requests++;
        
        const permission = await Notification.requestPermission();
        
        if (permission === 'granted') {
            console.log('Notification permission granted');
            this.analytics.subscriptions_granted++;
            await this.subscribe();
            return true;
        } else {
            console.log('Notification permission denied');
            this.analytics.subscriptions_denied++;
            return false;
        }
    }
    
    async subscribe() {
        try {
            const applicationServerKey = this.urlBase64ToUint8Array(this.config.vapidPublicKey);
            
            this.subscription = await this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            });
            
            console.log('User subscribed to push notifications');
            
            // Send subscription to server
            await this.sendSubscriptionToServer(this.subscription);
            
            // Trigger welcome notification
            this.triggerAutomation('user_subscribed', { subscription: this.subscription });
            
            return this.subscription;
            
        } catch (error) {
            console.error('Failed to subscribe user:', error);
            throw error;
        }
    }
    
    async unsubscribe() {
        if (!this.subscription) {
            console.log('User is not subscribed');
            return;
        }
        
        try {
            await this.subscription.unsubscribe();
            this.subscription = null;
            console.log('User unsubscribed from push notifications');
            
            // Notify server
            await this.removeSubscriptionFromServer();
            
        } catch (error) {
            console.error('Failed to unsubscribe user:', error);
            throw error;
        }
    }
    
    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch(this.config.apiEndpoint + 'subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    subscription: subscription,
                    userId: this.getUserId(),
                    userAgent: navigator.userAgent,
                    timestamp: Date.now()
                })
            });
            
            if (!response.ok) {
                throw new Error('Failed to send subscription to server');
            }
            
        } catch (error) {
            console.error('Error sending subscription to server:', error);
        }
    }
    
    async removeSubscriptionFromServer() {
        try {
            await fetch(this.config.apiEndpoint + 'unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    userId: this.getUserId()
                })
            });
        } catch (error) {
            console.error('Error removing subscription from server:', error);
        }
    }
    
    /**
     * NOTIFICATION SENDING
     */
    async sendNotification(type, customData = {}) {
        if (!this.subscription) {
            console.log('User not subscribed, cannot send notification');
            return;
        }
        
        const notificationConfig = this.notificationTypes[type];
        if (!notificationConfig) {
            console.error(`Notification type "${type}" not found`);
            return;
        }
        
        // Personalize notification
        const personalizedNotification = this.personalizeNotification(notificationConfig, customData);
        
        try {
            // Send via service worker
            const response = await fetch(this.config.apiEndpoint + 'send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    subscription: this.subscription,
                    notification: personalizedNotification,
                    userId: this.getUserId(),
                    type: type,
                    customData: customData
                })
            });
            
            if (response.ok) {
                this.analytics.sent++;
                console.log(`Notification "${type}" sent successfully`);
            } else {
                throw new Error('Failed to send notification');
            }
            
        } catch (error) {
            console.error('Error sending notification:', error);
        }
    }
    
    personalizeNotification(config, customData) {
        let title = config.title;
        let body = config.body;
        
        // Replace personalization placeholders
        Object.entries(this.personalization).forEach(([key, placeholder]) => {
            const value = customData[key] || this.getPersonalizationValue(key);
            title = title.replace(new RegExp(placeholder.replace(/[{}]/g, '\\$&'), 'g'), value);
            body = body.replace(new RegExp(placeholder.replace(/[{}]/g, '\\$&'), 'g'), value);
        });
        
        return {
            ...config,
            title,
            body,
            data: {
                ...customData,
                timestamp: Date.now(),
                userId: this.getUserId()
            }
        };
    }
    
    getPersonalizationValue(key) {
        const userData = this.getUserData();
        
        const values = {
            user_name: userData.name || userData.email || 'Amigo',
            product_name: 'Remera Personalizada',
            discount: '15',
            status: 'en producción',
            favorite_category: userData.favorite_category || 'Remeras'
        };
        
        return values[key] || '';
    }
    
    /**
     * AUTOMATION SYSTEM
     */
    initEventListeners() {
        // User events that trigger notifications
        document.addEventListener('user_registered', (e) => {
            this.triggerAutomation('user_registered', e.detail);
        });
        
        document.addEventListener('cart_abandoned', (e) => {
            this.triggerAutomation('cart_abandoned', e.detail);
        });
        
        document.addEventListener('order_placed', (e) => {
            this.triggerAutomation('order_placed', e.detail);
        });
        
        document.addEventListener('user_inactive', (e) => {
            this.triggerAutomation('user_inactive_7_days', e.detail);
        });
        
        // Notification interaction events
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data.type === 'notification_clicked') {
                this.handleNotificationClick(event.data.notification);
            } else if (event.data.type === 'notification_dismissed') {
                this.handleNotificationDismiss(event.data.notification);
            }
        });
    }
    
    triggerAutomation(trigger, userData = {}) {
        if (!this.config.enableAutomation) return;
        
        // Find automation rules for this trigger
        Object.entries(this.automationRules).forEach(([ruleName, rule]) => {
            if (rule.triggers.includes(trigger)) {
                console.log(`🤖 Triggering automation: ${ruleName} for trigger: ${trigger}`);
                
                // Schedule notifications
                rule.notifications.forEach(notification => {
                    this.scheduleNotification({
                        ...notification,
                        userData,
                        scheduledFor: Date.now() + notification.delay,
                        automationRule: ruleName
                    });
                });
            }
        });
    }
    
    scheduleNotification(notificationData) {
        const scheduledNotifications = this.getScheduledNotifications();
        const notificationId = this.generateNotificationId();
        
        const scheduledNotification = {
            id: notificationId,
            ...notificationData,
            status: 'scheduled',
            createdAt: Date.now()
        };
        
        scheduledNotifications.push(scheduledNotification);
        this.saveScheduledNotifications(scheduledNotifications);
        
        console.log(`📅 Notification scheduled for ${new Date(notificationData.scheduledFor).toLocaleString()}`);
    }
    
    startAutomationProcessor() {
        // Process scheduled notifications every minute
        setInterval(() => {
            this.processScheduledNotifications();
        }, 60000);
    }
    
    async processScheduledNotifications() {
        const scheduledNotifications = this.getScheduledNotifications();
        const now = Date.now();
        
        const readyNotifications = scheduledNotifications.filter(notification => 
            notification.status === 'scheduled' && 
            notification.scheduledFor <= now
        );
        
        for (const notification of readyNotifications) {
            try {
                await this.sendNotification(notification.type, notification.userData);
                this.markNotificationAsSent(notification.id);
            } catch (error) {
                console.error('Failed to send scheduled notification:', error);
                this.markNotificationAsFailed(notification.id, error.message);
            }
        }
    }
    
    /**
     * NOTIFICATION INTERACTION HANDLERS
     */
    handleNotificationClick(notificationData) {
        this.analytics.clicked++;
        
        const action = notificationData.action;
        const type = notificationData.type;
        
        // Route based on action
        const routes = {
            create: '/customize-shirt.php',
            browse: '/particulares.php',
            checkout: '/checkout.php',
            view_cart: '/cart.php',
            track: '/track-order.php',
            view_product: '/product-detail.php',
            customize: '/customize-shirt.php',
            shop_now: '/particulares.php',
            view_offer: '/offers.php',
            buy_now: '/checkout.php',
            get_inspired: '/inspiration.php',
            start_designing: '/customize-shirt.php'
        };
        
        const url = routes[action] || '/';
        
        // Open the appropriate page
        if ('clients' in self && 'openWindow' in clients) {
            clients.openWindow(url);
        } else {
            window.open(url, '_blank');
        }
        
        console.log(`Notification clicked: ${type}, action: ${action}`);
    }
    
    handleNotificationDismiss(notificationData) {
        this.analytics.dismissed++;
        console.log(`Notification dismissed: ${notificationData.type}`);
    }
    
    /**
     * SMART PERMISSION REQUEST
     */
    createSmartPermissionPrompt() {
        if (Notification.permission === 'granted') {
            return; // Already granted
        }
        
        if (Notification.permission === 'denied') {
            return; // Don't show if denied
        }
        
        // Check if we should show the prompt based on user behavior
        const userEngagement = this.calculateUserEngagement();
        
        if (userEngagement > 0.5) { // High engagement
            this.showPermissionPrompt();
        }
    }
    
    calculateUserEngagement() {
        const pageViews = parseInt(localStorage.getItem('page_views') || '0');
        const timeOnSite = parseInt(localStorage.getItem('time_on_site') || '0');
        const cartInteractions = parseInt(localStorage.getItem('cart_interactions') || '0');
        
        // Simple engagement score
        return Math.min(
            (pageViews / 5) * 0.3 + 
            (timeOnSite / 300000) * 0.4 + // 5 minutes
            (cartInteractions / 3) * 0.3,
            1
        );
    }
    
    showPermissionPrompt() {
        // Create a friendly permission request UI
        const promptHTML = `
        <div id="push-permission-prompt" class="push-prompt-overlay">
            <div class="push-prompt-content">
                <div class="push-prompt-icon">🔔</div>
                <h3>¡Mantente al día!</h3>
                <p>Recibí notificaciones sobre tus pedidos, ofertas especiales y nuevos productos.</p>
                <div class="push-prompt-benefits">
                    <div class="benefit">📦 Actualizaciones de pedidos</div>
                    <div class="benefit">🎁 Ofertas exclusivas</div>
                    <div class="benefit">✨ Nuevos productos</div>
                </div>
                <div class="push-prompt-actions">
                    <button id="push-allow-btn" class="btn-primary">Permitir Notificaciones</button>
                    <button id="push-maybe-later-btn" class="btn-secondary">Tal vez más tarde</button>
                </div>
            </div>
        </div>
        `;
        
        // Add to page
        document.body.insertAdjacentHTML('beforeend', promptHTML);
        
        // Add event listeners
        document.getElementById('push-allow-btn').addEventListener('click', async () => {
            document.getElementById('push-permission-prompt').remove();
            await this.requestPermission();
        });
        
        document.getElementById('push-maybe-later-btn').addEventListener('click', () => {
            document.getElementById('push-permission-prompt').remove();
            // Maybe show again later
            localStorage.setItem('push_prompt_dismissed', Date.now().toString());
        });
    }
    
    /**
     * UTILITY FUNCTIONS
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');
        
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
    
    generateNotificationId() {
        return 'notif_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    getUserId() {
        return localStorage.getItem('user_id') || 'anonymous_' + Date.now();
    }
    
    getUserData() {
        return JSON.parse(localStorage.getItem('user_data') || '{}');
    }
    
    getScheduledNotifications() {
        return JSON.parse(localStorage.getItem('scheduled_notifications') || '[]');
    }
    
    saveScheduledNotifications(notifications) {
        localStorage.setItem('scheduled_notifications', JSON.stringify(notifications));
    }
    
    markNotificationAsSent(notificationId) {
        const notifications = this.getScheduledNotifications();
        const index = notifications.findIndex(n => n.id === notificationId);
        if (index !== -1) {
            notifications[index].status = 'sent';
            notifications[index].sentAt = Date.now();
            this.saveScheduledNotifications(notifications);
        }
    }
    
    markNotificationAsFailed(notificationId, error) {
        const notifications = this.getScheduledNotifications();
        const index = notifications.findIndex(n => n.id === notificationId);
        if (index !== -1) {
            notifications[index].status = 'failed';
            notifications[index].error = error;
            this.saveScheduledNotifications(notifications);
        }
    }
    
    /**
     * PUBLIC API
     */
    getAnalytics() {
        return this.analytics;
    }
    
    isSubscribed() {
        return !!this.subscription;
    }
    
    getSubscription() {
        return this.subscription;
    }
    
    async testNotification(type = 'welcome') {
        if (!this.subscription) {
            console.log('User not subscribed, cannot test notification');
            return;
        }
        
        await this.sendNotification(type, {
            user_name: 'Usuario de Prueba',
            product_name: 'Remera de Prueba'
        });
    }
}

// Auto-initialize
window.addEventListener('DOMContentLoaded', () => {
    if (window.pushNotifications) return;
    
    window.pushNotifications = new PushNotificationSystem();
    
    // Expose API
    window.PushNotifications = {
        request: () => window.pushNotifications.requestPermission(),
        subscribe: () => window.pushNotifications.subscribe(),
        unsubscribe: () => window.pushNotifications.unsubscribe(),
        send: (type, data) => window.pushNotifications.sendNotification(type, data),
        test: (type) => window.pushNotifications.testNotification(type),
        analytics: () => window.pushNotifications.getAnalytics(),
        isSubscribed: () => window.pushNotifications.isSubscribed()
    };
    
    // Show smart permission prompt after user engagement
    setTimeout(() => {
        window.pushNotifications.createSmartPermissionPrompt();
    }, 30000); // After 30 seconds
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PushNotificationSystem;
}