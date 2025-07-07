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
            const registration = await navigator.serviceWorker.register(this.config.swPath);
            this.swRegistration = registration;
            
            console.log('Service Worker registered successfully');
            
            // Handle updates
            registration.addEventListener('updatefound', () => {
                this.handleUpdateFound(registration);
            });
            
            // Handle controller change
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                console.log('New service worker activated');
                this.isUpdateAvailable = false;
            });
            
        } catch (error) {
            console.error('Service Worker registration failed:', error);
            throw error;
        }
    }
    
    handleUpdateFound(registration) {
        const installingWorker = registration.installing;
        
        installingWorker.addEventListener('statechange', () => {
            if (installingWorker.state === 'installed') {
                if (navigator.serviceWorker.controller) {
                    // Update available
                    this.isUpdateAvailable = true;
                    this.showUpdateNotification();
                } else {
                    // First install
                    console.log('Service Worker installed for the first time');
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
            e.preventDefault();
            this.deferredPrompt = e;
            console.log('Install prompt ready');
        });
        
        // App installed event
        window.addEventListener('appinstalled', (e) => {
            console.log('PWA was installed');
            this.isInstalled = true;
            this.showInstalledConfirmation();
            this.trackInstallation('completed');
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
            if (!document.hidden && this.swRegistration) {
                this.checkForUpdates();
            }
        });
    }
    
    /**
     * INSTALLATION MANAGEMENT
     */
    checkInstallStatus() {
        // Check if running as PWA
        if (window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true) {
            this.isInstalled = true;
            console.log('App is running as PWA');
        }
        
        // Check installation criteria
        this.checkInstallCriteria();
    }
    
    checkInstallCriteria() {
        const pageViews = parseInt(localStorage.getItem('page_views') || '0');
        const timeOnSite = parseInt(localStorage.getItem('time_on_site') || '0');
        const engagement = parseInt(localStorage.getItem('user_engagement') || '0');
        
        // Show install prompt if user is engaged
        if (pageViews >= 3 && timeOnSite > 180000 && engagement > 0.5) {
            if (!this.installPromptShown && !this.installDismissed && this.deferredPrompt) {
                setTimeout(() => {
                    this.showInstallPrompt();
                }, 5000);
            }
        }
    }
    
    createInstallPrompt() {
        // Create install button if not shown
        if (!this.installPromptShown && !this.installDismissed) {
            const installButton = document.createElement('button');
            installButton.id = 'pwa-install-button';
            installButton.innerHTML = '<i class="fas fa-download"></i> Instalar App';
            installButton.className = 'pwa-install-btn';
            installButton.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #007bff;
                color: white;
                border: none;
                padding: 12px 20px;
                border-radius: 25px;
                cursor: pointer;
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                z-index: 1000;
                display: none;
            `;
            
            installButton.addEventListener('click', () => {
                this.showInstallPrompt();
            });
            
            document.body.appendChild(installButton);
            
            // Show button when install prompt is available
            if (this.deferredPrompt) {
                installButton.style.display = 'block';
            }
        }
    }
    
    async showInstallPrompt() {
        if (!this.deferredPrompt || this.isInstalled || this.installDismissed) {
            return;
        }
        
        try {
            const { outcome } = await this.deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('User accepted the install prompt');
                this.trackInstallation('accepted');
            } else {
                console.log('User dismissed the install prompt');
                this.trackInstallation('dismissed');
            }
            
            this.deferredPrompt = null;
            this.installPromptShown = true;
            localStorage.setItem('pwa_install_prompt_shown', 'true');
            
            // Hide install button
            const installButton = document.getElementById('pwa-install-button');
            if (installButton) {
                installButton.style.display = 'none';
            }
            
        } catch (error) {
            console.error('Installation failed:', error);
        }
    }
    
    /**
     * UPDATE MANAGEMENT
     */
    setupUpdateChecking() {
        if (!this.config.enableUpdateNotifications) return;
        
        setInterval(() => {
            this.checkForUpdates();
        }, this.config.updateCheckInterval);
    }
    
    async checkForUpdates() {
        if (!this.swRegistration) return;
        
        try {
            await this.swRegistration.update();
        } catch (error) {
            console.warn('Update check failed:', error);
        }
    }
    
    showUpdateNotification() {
        if (document.getElementById('pwa-update-notification')) {
            return;
        }
        
        const notification = document.createElement('div');
        notification.id = 'pwa-update-notification';
        notification.innerHTML = `
            <div class="pwa-update-content">
                <div class="pwa-update-icon">
                    <i class="fas fa-download"></i>
                </div>
                <div class="pwa-update-text">
                    <h4>Nueva versión disponible</h4>
                    <p>Actualización con mejoras y nuevas funcionalidades</p>
                </div>
                <div class="pwa-update-actions">
                    <button id="pwa-update-btn" class="btn-primary btn-sm">Actualizar</button>
                    <button id="pwa-update-dismiss-btn" class="btn-secondary btn-sm">Después</button>
                </div>
            </div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1001;
            max-width: 300px;
        `;
        
        document.body.appendChild(notification);
        
        // Event listeners
        document.getElementById('pwa-update-btn').addEventListener('click', () => {
            this.applyUpdate();
        });
        
        document.getElementById('pwa-update-dismiss-btn').addEventListener('click', () => {
            this.dismissUpdateNotification();
        });
        
        // Auto-dismiss after 10 seconds
        setTimeout(() => {
            this.dismissUpdateNotification();
        }, 10000);
    }
    
    async applyUpdate() {
        if (!this.swRegistration || !this.swRegistration.waiting) {
            console.log('No update available');
            return;
        }
        
        try {
            this.swRegistration.waiting.postMessage({ type: 'SKIP_WAITING' });
            window.location.reload();
        } catch (error) {
            console.error('Update failed:', error);
        }
    }
    
    dismissUpdateNotification() {
        const notification = document.getElementById('pwa-update-notification');
        if (notification) {
            notification.remove();
        }
    }
    
    /**
     * OFFLINE HANDLING
     */
    setupOfflineHandling() {
        if (!this.config.enableOfflineMode) return;
        
        this.createOfflineIndicator();
    }
    
    createOfflineIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'pwa-offline-indicator';
        indicator.innerHTML = `
            <div class="pwa-offline-content">
                <i class="fas fa-wifi-slash"></i>
                <span>Sin conexión - Usando modo offline</span>
            </div>
        `;
        
        indicator.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #ff6b6b;
            color: white;
            padding: 10px;
            text-align: center;
            z-index: 1002;
            display: ${this.isOnline ? 'none' : 'block'};
        `;
        
        document.body.appendChild(indicator);
    }
    
    handleOnlineStatusChange() {
        const indicator = document.getElementById('pwa-offline-indicator');
        
        if (this.isOnline) {
            if (indicator) indicator.style.display = 'none';
            console.log('App is online');
        } else {
            if (indicator) indicator.style.display = 'block';
            console.log('App is offline');
        }
        
        document.dispatchEvent(new CustomEvent('pwa-online-status-change', {
            detail: { isOnline: this.isOnline }
        }));
    }
    
    /**
     * UTILITIES
     */
    isPWASupported() {
        return 'serviceWorker' in navigator && 
               'PushManager' in window && 
               'Notification' in window;
    }
    
    showInstalledConfirmation() {
        const confirmation = document.createElement('div');
        confirmation.id = 'pwa-installed-confirmation';
        confirmation.innerHTML = `
            <div class="pwa-success-content">
                <div class="pwa-success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="pwa-success-text">
                    <h4>¡App instalada exitosamente!</h4>
                    <p>Ya podés acceder desde tu escritorio</p>
                </div>
            </div>
        `;
        
        confirmation.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            z-index: 1003;
            text-align: center;
        `;
        
        document.body.appendChild(confirmation);
        
        setTimeout(() => {
            confirmation.remove();
        }, 5000);
    }
    
    trackInstallation(outcome = 'completed') {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'pwa_install', {
                'outcome': outcome,
                'timestamp': Date.now()
            });
        }
        
        fetch('/api/analytics/pwa-install', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                outcome: outcome,
                timestamp: Date.now(),
                userAgent: navigator.userAgent
            })
        }).catch(error => {
            console.warn('Failed to track installation:', error);
        });
    }
    
    /**
     * PUBLIC API
     */
    getInstallationStatus() {
        return {
            isInstalled: this.isInstalled,
            isInstallable: !!this.deferredPrompt,
            isUpdateAvailable: this.isUpdateAvailable,
            isOnline: this.isOnline
        };
    }
    
    async triggerInstallPrompt() {
        if (this.deferredPrompt && !this.isInstalled) {
            await this.showInstallPrompt();
        } else {
            console.log('Install prompt not available');
        }
    }
    
    async forceUpdate() {
        await this.applyUpdate();
    }
    
    clearCache() {
        if (this.swRegistration) {
            this.swRegistration.active?.postMessage({ type: 'CLEAR_CACHE' });
        }
    }
    
    getNetworkStatus() {
        return {
            isOnline: this.isOnline,
            connectionType: navigator.connection?.effectiveType || 'unknown',
            downlink: navigator.connection?.downlink || null
        };
    }
}

// Auto-initialize
window.addEventListener('DOMContentLoaded', () => {
    if (window.pwaManager) return;
    
    window.pwaManager = new PWAManager();
    
    // Expose API
    window.PWA = {
        install: () => window.pwaManager.triggerInstallPrompt(),
        update: () => window.pwaManager.forceUpdate(),
        status: () => window.pwaManager.getInstallationStatus(),
        network: () => window.pwaManager.getNetworkStatus(),
        clearCache: () => window.pwaManager.clearCache()
    };
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PWAManager;
}