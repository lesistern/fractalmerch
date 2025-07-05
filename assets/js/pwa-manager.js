/**
 * FractalMerch Progressive Web App Manager
 * Handles PWA installation, updates, and offline functionality
 */

class PWAManager {
    constructor() {
        this.config = {
            enablePWA: true,
            enableInstallPrompt: true,
            enableUpdateNotifications: true,
            enableOfflineMode: true,
            swPath: '/sw.js',
            manifestPath: '/manifest.json',
            updateCheckInterval: 60000, // 1 minute
            offlinePagePath: '/offline.html'
        };
        
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.isUpdateAvailable = false;
        this.swRegistration = null;
        this.isOnline = navigator.onLine;
        
        this.installPromptShown = localStorage.getItem('pwa_install_prompt_shown') === 'true';
        this.installDismissed = localStorage.getItem('pwa_install_dismissed') === 'true';
        
        this.init();
    }
    
    async init() {
        if (!this.isPWASupported()) {
            console.log('PWA not supported in this browser');
            return;
        }
        
        console.log('🚀 PWA Manager initializing...');
        
        try {
            await this.registerServiceWorker();
            this.initEventListeners();
            this.checkInstallStatus();
            this.setupUpdateChecking();
            this.setupOfflineHandling();
            this.createInstallPrompt();
            
            console.log('✅ PWA Manager initialized successfully');
        } catch (error) {
            console.error('Failed to initialize PWA Manager:', error);
        }
    }
    
    /**
     * SERVICE WORKER REGISTRATION
     */
    async registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            throw new Error('Service Worker not supported');
        }
        
        try {
            this.swRegistration = await navigator.serviceWorker.register(this.config.swPath);
            console.log('Service Worker registered successfully:', this.swRegistration);
            
            // Check for updates
            this.swRegistration.addEventListener('updatefound', () => {
                this.handleServiceWorkerUpdate();
            });
            
            return this.swRegistration;
        } catch (error) {
            console.error('Service Worker registration failed:', error);
            throw error;
        }
    }
    
    handleServiceWorkerUpdate() {
        const newWorker = this.swRegistration.installing;
        
        newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed') {
                if (navigator.serviceWorker.controller) {
                    // New update available
                    this.isUpdateAvailable = true;
                    this.showUpdateNotification();
                } else {
                    // First time installation
                    console.log('PWA is ready for offline use');
                }
            }
        });
    }
    
    /**
     * EVENT LISTENERS
     */
    initEventListeners() {
        // Install prompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('PWA install prompt triggered');
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallPrompt();
        });
        
        // App installed event
        window.addEventListener('appinstalled', () => {
            console.log('PWA was installed');
            this.isInstalled = true;
            this.hideInstallPrompt();
            this.trackInstallation();
            this.showInstalledConfirmation();
        });
        
        // Online/offline events
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.handleOnlineStatusChange();
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.handleOnlineStatusChange();
        });
        
        // Visibility change (for update checking when app becomes visible)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.swRegistration) {\n                this.checkForUpdates();\n            }\n        });\n    }\n    \n    /**\n     * INSTALLATION MANAGEMENT\n     */\n    checkInstallStatus() {\n        // Check if running as PWA\n        if (window.matchMedia('(display-mode: standalone)').matches ||\n            window.navigator.standalone === true) {\n            this.isInstalled = true;\n            console.log('App is running as PWA');\n        }\n        \n        // Check installation criteria\n        this.checkInstallCriteria();\n    }\n    \n    checkInstallCriteria() {\n        const pageViews = parseInt(localStorage.getItem('page_views') || '0');\n        const timeOnSite = parseInt(localStorage.getItem('time_on_site') || '0');\n        const engagement = parseInt(localStorage.getItem('user_engagement') || '0');\n        \n        // Show install prompt if user is engaged\n        if (pageViews >= 3 && timeOnSite > 180000 && engagement > 0.5) { // 3+ pages, 3+ minutes, high engagement\n            if (!this.installPromptShown && !this.installDismissed && this.deferredPrompt) {\n                setTimeout(() => {\n                    this.showInstallPrompt();\n                }, 5000); // Show after 5 seconds\n            }\n        }\n    }\n    \n    async showInstallPrompt() {\n        if (!this.deferredPrompt || this.isInstalled || this.installDismissed) {\n            return;\n        }\n        \n        this.createInstallPromptUI();\n    }\n    \n    createInstallPromptUI() {\n        // Don't show if already shown\n        if (document.getElementById('pwa-install-prompt')) {\n            return;\n        }\n        \n        const promptHTML = `\n        <div id=\"pwa-install-prompt\" class=\"pwa-install-overlay\">\n            <div class=\"pwa-install-content\">\n                <div class=\"pwa-install-header\">\n                    <img src=\"/assets/images/icon-192.png\" alt=\"FractalMerch\" class=\"pwa-install-icon\">\n                    <h3>¡Instalá FractalMerch!</h3>\n                </div>\n                <div class=\"pwa-install-body\">\n                    <p>Acceso rápido desde tu escritorio</p>\n                    <ul class=\"pwa-benefits\">\n                        <li>📱 Acceso instantáneo</li>\n                        <li>🚀 Carga súper rápida</li>\n                        <li>🔄 Funciona sin internet</li>\n                        <li>🔔 Notificaciones push</li>\n                    </ul>\n                </div>\n                <div class=\"pwa-install-actions\">\n                    <button id=\"pwa-install-btn\" class=\"btn-primary\">\n                        <i class=\"fas fa-download\"></i>\n                        Instalar App\n                    </button>\n                    <button id=\"pwa-dismiss-btn\" class=\"btn-secondary\">Tal vez después</button>\n                </div>\n                <button id=\"pwa-close-btn\" class=\"pwa-close-btn\">\n                    <i class=\"fas fa-times\"></i>\n                </button>\n            </div>\n        </div>\n        `;\n        \n        document.body.insertAdjacentHTML('beforeend', promptHTML);\n        \n        // Add event listeners\n        document.getElementById('pwa-install-btn').addEventListener('click', () => {\n            this.installPWA();\n        });\n        \n        document.getElementById('pwa-dismiss-btn').addEventListener('click', () => {\n            this.dismissInstallPrompt(false);\n        });\n        \n        document.getElementById('pwa-close-btn').addEventListener('click', () => {\n            this.dismissInstallPrompt(true);\n        });\n        \n        this.installPromptShown = true;\n        localStorage.setItem('pwa_install_prompt_shown', 'true');\n    }\n    \n    async installPWA() {\n        if (!this.deferredPrompt) {\n            console.log('Install prompt not available');\n            return;\n        }\n        \n        try {\n            // Show the install prompt\n            this.deferredPrompt.prompt();\n            \n            // Wait for the user response\n            const { outcome } = await this.deferredPrompt.userChoice;\n            \n            console.log('Install prompt outcome:', outcome);\n            \n            if (outcome === 'accepted') {\n                console.log('User accepted the install prompt');\n                this.trackInstallation('accepted');\n            } else {\n                console.log('User dismissed the install prompt');\n                this.trackInstallation('dismissed');\n            }\n            \n            this.deferredPrompt = null;\n            this.hideInstallPrompt();\n            \n        } catch (error) {\n            console.error('Installation failed:', error);\n        }\n    }\n    \n    dismissInstallPrompt(permanent = false) {\n        this.hideInstallPrompt();\n        \n        if (permanent) {\n            this.installDismissed = true;\n            localStorage.setItem('pwa_install_dismissed', 'true');\n        }\n    }\n    \n    hideInstallPrompt() {\n        const prompt = document.getElementById('pwa-install-prompt');\n        if (prompt) {\n            prompt.remove();\n        }\n    }\n    \n    /**\n     * UPDATE MANAGEMENT\n     */\n    setupUpdateChecking() {\n        if (!this.config.enableUpdateNotifications) return;\n        \n        // Check for updates periodically\n        setInterval(() => {\n            this.checkForUpdates();\n        }, this.config.updateCheckInterval);\n    }\n    \n    async checkForUpdates() {\n        if (!this.swRegistration) return;\n        \n        try {\n            await this.swRegistration.update();\n        } catch (error) {\n            console.warn('Update check failed:', error);\n        }\n    }\n    \n    showUpdateNotification() {\n        // Don't show multiple update notifications\n        if (document.getElementById('pwa-update-notification')) {\n            return;\n        }\n        \n        const updateHTML = `\n        <div id=\"pwa-update-notification\" class=\"pwa-update-notification\">\n            <div class=\"pwa-update-content\">\n                <div class=\"pwa-update-icon\">\n                    <i class=\"fas fa-download\"></i>\n                </div>\n                <div class=\"pwa-update-text\">\n                    <h4>Nueva versión disponible</h4>\n                    <p>Actualización con mejoras y nuevas funcionalidades</p>\n                </div>\n                <div class=\"pwa-update-actions\">\n                    <button id=\"pwa-update-btn\" class=\"btn-primary btn-sm\">Actualizar</button>\n                    <button id=\"pwa-update-dismiss-btn\" class=\"btn-secondary btn-sm\">Después</button>\n                </div>\n            </div>\n        </div>\n        `;\n        \n        document.body.insertAdjacentHTML('beforeend', updateHTML);\n        \n        // Add event listeners\n        document.getElementById('pwa-update-btn').addEventListener('click', () => {\n            this.applyUpdate();\n        });\n        \n        document.getElementById('pwa-update-dismiss-btn').addEventListener('click', () => {\n            this.dismissUpdateNotification();\n        });\n        \n        // Auto-dismiss after 10 seconds\n        setTimeout(() => {\n            this.dismissUpdateNotification();\n        }, 10000);\n    }\n    \n    async applyUpdate() {\n        if (!this.swRegistration || !this.swRegistration.waiting) {\n            console.log('No update available');\n            return;\n        }\n        \n        try {\n            // Tell the waiting service worker to skip waiting\n            this.swRegistration.waiting.postMessage({ type: 'SKIP_WAITING' });\n            \n            // Reload the page to apply the update\n            window.location.reload();\n            \n        } catch (error) {\n            console.error('Update failed:', error);\n        }\n    }\n    \n    dismissUpdateNotification() {\n        const notification = document.getElementById('pwa-update-notification');\n        if (notification) {\n            notification.remove();\n        }\n    }\n    \n    /**\n     * OFFLINE HANDLING\n     */\n    setupOfflineHandling() {\n        if (!this.config.enableOfflineMode) return;\n        \n        this.createOfflineIndicator();\n        this.setupOfflineSync();\n    }\n    \n    createOfflineIndicator() {\n        const indicatorHTML = `\n        <div id=\"pwa-offline-indicator\" class=\"pwa-offline-indicator ${this.isOnline ? 'hidden' : ''}\">\n            <div class=\"pwa-offline-content\">\n                <i class=\"fas fa-wifi-slash\"></i>\n                <span>Sin conexión - Usando modo offline</span>\n            </div>\n        </div>\n        `;\n        \n        document.body.insertAdjacentHTML('beforeend', indicatorHTML);\n    }\n    \n    handleOnlineStatusChange() {\n        const indicator = document.getElementById('pwa-offline-indicator');\n        \n        if (this.isOnline) {\n            indicator?.classList.add('hidden');\n            this.syncOfflineData();\n            console.log('App is online - syncing data');\n        } else {\n            indicator?.classList.remove('hidden');\n            console.log('App is offline - using cached data');\n        }\n        \n        // Trigger custom event\n        document.dispatchEvent(new CustomEvent('pwa-online-status-change', {\n            detail: { isOnline: this.isOnline }\n        }));\n    }\n    \n    setupOfflineSync() {\n        // Register for background sync if supported\n        if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {\n            navigator.serviceWorker.ready.then(registration => {\n                // Register background sync events\n                registration.sync.register('background-sync-cart');\n                registration.sync.register('background-sync-analytics');\n            });\n        }\n    }\n    \n    async syncOfflineData() {\n        // Sync pending data when coming back online\n        try {\n            // Sync cart data\n            const pendingCartData = JSON.parse(localStorage.getItem('pending_cart_sync') || '[]');\n            if (pendingCartData.length > 0) {\n                await this.syncCartData(pendingCartData);\n                localStorage.removeItem('pending_cart_sync');\n            }\n            \n            // Sync analytics data\n            const pendingAnalytics = JSON.parse(localStorage.getItem('pending_analytics_sync') || '[]');\n            if (pendingAnalytics.length > 0) {\n                await this.syncAnalyticsData(pendingAnalytics);\n                localStorage.removeItem('pending_analytics_sync');\n            }\n            \n            console.log('Offline data synced successfully');\n        } catch (error) {\n            console.error('Failed to sync offline data:', error);\n        }\n    }\n    \n    async syncCartData(data) {\n        for (const item of data) {\n            await fetch('/api/cart/sync', {\n                method: 'POST',\n                headers: { 'Content-Type': 'application/json' },\n                body: JSON.stringify(item)\n            });\n        }\n    }\n    \n    async syncAnalyticsData(data) {\n        await fetch('/api/analytics/bulk', {\n            method: 'POST',\n            headers: { 'Content-Type': 'application/json' },\n            body: JSON.stringify(data)\n        });\n    }\n    \n    /**\n     * PWA UTILITIES\n     */\n    isPWASupported() {\n        return 'serviceWorker' in navigator && \n               'PushManager' in window && \n               'Notification' in window;\n    }\n    \n    showInstalledConfirmation() {\n        const confirmationHTML = `\n        <div id=\"pwa-installed-confirmation\" class=\"pwa-success-notification\">\n            <div class=\"pwa-success-content\">\n                <div class=\"pwa-success-icon\">\n                    <i class=\"fas fa-check-circle\"></i>\n                </div>\n                <div class=\"pwa-success-text\">\n                    <h4>¡App instalada exitosamente!</h4>\n                    <p>Ya podés acceder desde tu escritorio</p>\n                </div>\n            </div>\n        </div>\n        `;\n        \n        document.body.insertAdjacentHTML('beforeend', confirmationHTML);\n        \n        // Auto-remove after 5 seconds\n        setTimeout(() => {\n            const confirmation = document.getElementById('pwa-installed-confirmation');\n            confirmation?.remove();\n        }, 5000);\n    }\n    \n    trackInstallation(outcome = 'completed') {\n        // Send installation analytics\n        if (typeof gtag !== 'undefined') {\n            gtag('event', 'pwa_install', {\n                'outcome': outcome,\n                'timestamp': Date.now()\n            });\n        }\n        \n        // Send to our analytics\n        fetch('/api/analytics/pwa-install', {\n            method: 'POST',\n            headers: { 'Content-Type': 'application/json' },\n            body: JSON.stringify({\n                outcome: outcome,\n                timestamp: Date.now(),\n                userAgent: navigator.userAgent\n            })\n        }).catch(error => {\n            console.warn('Failed to track installation:', error);\n        });\n    }\n    \n    /**\n     * PUBLIC API\n     */\n    getInstallationStatus() {\n        return {\n            isInstalled: this.isInstalled,\n            isInstallable: !!this.deferredPrompt,\n            isUpdateAvailable: this.isUpdateAvailable,\n            isOnline: this.isOnline\n        };\n    }\n    \n    async triggerInstallPrompt() {\n        if (this.deferredPrompt && !this.isInstalled) {\n            await this.installPWA();\n        } else {\n            console.log('Install prompt not available');\n        }\n    }\n    \n    async forceUpdate() {\n        await this.applyUpdate();\n    }\n    \n    clearCache() {\n        if (this.swRegistration) {\n            this.swRegistration.active?.postMessage({ type: 'CLEAR_CACHE' });\n        }\n    }\n    \n    getNetworkStatus() {\n        return {\n            isOnline: this.isOnline,\n            connectionType: navigator.connection?.effectiveType || 'unknown',\n            downlink: navigator.connection?.downlink || null\n        };\n    }\n}\n\n// Auto-initialize\nwindow.addEventListener('DOMContentLoaded', () => {\n    if (window.pwaManager) return;\n    \n    window.pwaManager = new PWAManager();\n    \n    // Expose API\n    window.PWA = {\n        install: () => window.pwaManager.triggerInstallPrompt(),\n        update: () => window.pwaManager.forceUpdate(),\n        status: () => window.pwaManager.getInstallationStatus(),\n        network: () => window.pwaManager.getNetworkStatus(),\n        clearCache: () => window.pwaManager.clearCache()\n    };\n});\n\n// Export for module systems\nif (typeof module !== 'undefined' && module.exports) {\n    module.exports = PWAManager;\n}