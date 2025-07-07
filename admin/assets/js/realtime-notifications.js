/**
 * Sistema de Notificaciones en Tiempo Real para Admin Panel
 * Notifica sobre eventos importantes del sistema
 */

class RealtimeNotifications {
    constructor() {
        this.notifications = [];
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.pollInterval = 30000; // 30 segundos
        this.lastCheck = Date.now();
        
        this.init();
    }

    /**
     * Inicializar sistema de notificaciones
     */
    init() {
        this.createNotificationContainer();
        this.setupNotificationTypes();
        this.startPolling();
        this.setupServiceWorker();
        this.bindEvents();
    }

    /**
     * Crear contenedor de notificaciones
     */
    createNotificationContainer() {
        if (document.querySelector('.notifications-container')) return;

        const container = document.createElement('div');
        container.className = 'notifications-container';
        container.innerHTML = `
            <div class="notifications-header">
                <button class="notifications-toggle" onclick="realtimeNotifications.toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">0</span>
                </button>
            </div>
            <div class="notifications-panel" style="display: none;">
                <div class="notifications-panel-header">
                    <h3><i class="fas fa-bell"></i> Notificaciones</h3>
                    <div class="notifications-actions">
                        <button onclick="realtimeNotifications.markAllAsRead()" class="mark-all-read">
                            <i class="fas fa-check-double"></i> Marcar todas como leídas
                        </button>
                        <button onclick="realtimeNotifications.clearAll()" class="clear-all">
                            <i class="fas fa-trash"></i> Limpiar todas
                        </button>
                    </div>
                </div>
                <div class="notifications-list">
                    <div class="no-notifications">
                        <i class="fas fa-bell-slash"></i>
                        <p>No hay notificaciones nuevas</p>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(container);
    }

    /**
     * Configurar tipos de notificaciones
     */
    setupNotificationTypes() {
        this.notificationTypes = {
            'new_order': {
                icon: 'fas fa-shopping-cart',
                color: '#10b981',
                title: 'Nuevo Pedido',
                importance: 'high',
                sound: true
            },
            'low_stock': {
                icon: 'fas fa-exclamation-triangle',
                color: '#f59e0b',
                title: 'Stock Bajo',
                importance: 'medium',
                sound: true
            },
            'new_user': {
                icon: 'fas fa-user-plus',
                color: '#3b82f6',
                title: 'Nuevo Usuario',
                importance: 'low',
                sound: false
            },
            'system_error': {
                icon: 'fas fa-exclamation-circle',
                color: '#ef4444',
                title: 'Error del Sistema',
                importance: 'critical',
                sound: true
            },
            'payment_received': {
                icon: 'fas fa-credit-card',
                color: '#10b981',
                title: 'Pago Recibido',
                importance: 'high',
                sound: true
            },
            'review_pending': {
                icon: 'fas fa-star',
                color: '#8b5cf6',
                title: 'Reseña Pendiente',
                importance: 'low',
                sound: false
            },
            'backup_completed': {
                icon: 'fas fa-database',
                color: '#06b6d4',
                title: 'Backup Completado',
                importance: 'low',
                sound: false
            },
            'security_alert': {
                icon: 'fas fa-shield-alt',
                color: '#dc2626',
                title: 'Alerta de Seguridad',
                importance: 'critical',
                sound: true
            }
        };
    }

    /**
     * Iniciar polling para obtener notificaciones
     */
    startPolling() {
        this.pollForNotifications();
        
        // Configurar intervalo regular
        setInterval(() => {
            this.pollForNotifications();
        }, this.pollInterval);
        
        // Polling más frecuente cuando la pestaña está activa
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.pollForNotifications();
            }
        });
    }

    /**
     * Obtener notificaciones del servidor
     */
    async pollForNotifications() {
        try {
            const response = await fetch(`admin_notifications.php?since=${this.lastCheck}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const data = await response.json();
            
            if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    this.addNotification(notification);
                });
            }
            
            this.lastCheck = Date.now();
            this.isConnected = true;
            this.reconnectAttempts = 0;
            
        } catch (error) {
            console.error('Error fetching notifications:', error);
            this.handleConnectionError();
        }
    }

    /**
     * Manejar errores de conexión
     */
    handleConnectionError() {
        this.isConnected = false;
        this.reconnectAttempts++;
        
        if (this.reconnectAttempts <= this.maxReconnectAttempts) {
            // Intentar reconectar con backoff exponencial
            const delay = Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000);
            
            setTimeout(() => {
                this.pollForNotifications();
            }, delay);
        } else {
            // Mostrar notificación de error de conexión
            this.addNotification({
                type: 'system_error',
                title: 'Error de Conexión',
                message: 'No se pueden obtener notificaciones en tiempo real',
                timestamp: Date.now()
            });
        }
    }

    /**
     * Agregar nueva notificación
     */
    addNotification(notification) {
        // Validar que no sea duplicada
        if (this.notifications.find(n => n.id === notification.id)) {
            return;
        }

        // Enriquecer notificación con metadatos
        const enrichedNotification = {
            ...notification,
            id: notification.id || `notif_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            timestamp: notification.timestamp || Date.now(),
            read: false,
            ...this.notificationTypes[notification.type]
        };

        // Agregar a la lista
        this.notifications.unshift(enrichedNotification);
        
        // Limitar número de notificaciones en memoria
        if (this.notifications.length > 50) {
            this.notifications = this.notifications.slice(0, 50);
        }

        // Actualizar UI
        this.updateNotificationBadge();
        this.renderNotifications();
        
        // Mostrar notificación visual
        this.showNotificationPopup(enrichedNotification);
        
        // Reproducir sonido si es necesario
        if (enrichedNotification.sound && this.shouldPlaySound()) {
            this.playNotificationSound(enrichedNotification.importance);
        }

        // Notificación del navegador si está permitido
        this.showBrowserNotification(enrichedNotification);
    }

    /**
     * Actualizar badge de notificaciones
     */
    updateNotificationBadge() {
        const badge = document.querySelector('.notification-badge');
        const unreadCount = this.notifications.filter(n => !n.read).length;
        
        if (badge) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            badge.style.display = unreadCount > 0 ? 'inline' : 'none';
        }
    }

    /**
     * Renderizar lista de notificaciones
     */
    renderNotifications() {
        const list = document.querySelector('.notifications-list');
        const noNotifications = list.querySelector('.no-notifications');
        
        if (this.notifications.length === 0) {
            noNotifications.style.display = 'block';
            return;
        }
        
        noNotifications.style.display = 'none';
        
        // Renderizar notificaciones
        const notificationsHTML = this.notifications.map(notification => `
            <div class="notification-item ${notification.read ? 'read' : 'unread'}" data-id="${notification.id}">
                <div class="notification-icon" style="color: ${notification.color}">
                    <i class="${notification.icon}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <span class="notification-title">${notification.title}</span>
                        <span class="notification-time">${this.formatTime(notification.timestamp)}</span>
                    </div>
                    <div class="notification-message">${notification.message}</div>
                    ${notification.action_url ? `<a href="${notification.action_url}" class="notification-action">Ver detalles</a>` : ''}
                </div>
                <div class="notification-actions">
                    <button onclick="realtimeNotifications.markAsRead('${notification.id}')" class="mark-read-btn" title="Marcar como leída">
                        <i class="fas fa-check"></i>
                    </button>
                    <button onclick="realtimeNotifications.deleteNotification('${notification.id}')" class="delete-btn" title="Eliminar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `).join('');
        
        list.innerHTML = notificationsHTML + '<div class="no-notifications" style="display: none;"><i class="fas fa-bell-slash"></i><p>No hay notificaciones nuevas</p></div>';
    }

    /**
     * Mostrar popup de notificación
     */
    showNotificationPopup(notification) {
        const popup = document.createElement('div');
        popup.className = `notification-popup ${notification.importance}`;
        popup.innerHTML = `
            <div class="popup-content">
                <div class="popup-icon" style="color: ${notification.color}">
                    <i class="${notification.icon}"></i>
                </div>
                <div class="popup-text">
                    <div class="popup-title">${notification.title}</div>
                    <div class="popup-message">${notification.message}</div>
                </div>
                <button class="popup-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(popup);
        
        // Auto-remover después de un tiempo
        const timeout = notification.importance === 'critical' ? 10000 : 5000;
        setTimeout(() => {
            if (popup.parentElement) {
                popup.remove();
            }
        }, timeout);
    }

    /**
     * Reproducir sonido de notificación
     */
    playNotificationSound(importance) {
        if (!this.isAudioEnabled()) return;
        
        const context = new (window.AudioContext || window.webkitAudioContext)();
        
        // Diferentes tonos según importancia
        const frequencies = {
            'low': [440, 550],
            'medium': [550, 660],
            'high': [660, 880],
            'critical': [880, 1100, 660]
        };
        
        const freqs = frequencies[importance] || frequencies['medium'];
        
        freqs.forEach((freq, index) => {
            setTimeout(() => {
                const oscillator = context.createOscillator();
                const gainNode = context.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(context.destination);
                
                oscillator.frequency.setValueAtTime(freq, context.currentTime);
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.1, context.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, context.currentTime + 0.3);
                
                oscillator.start(context.currentTime);
                oscillator.stop(context.currentTime + 0.3);
            }, index * 200);
        });
    }

    /**
     * Notificación del navegador
     */
    async showBrowserNotification(notification) {
        if (!('Notification' in window)) return;
        
        if (Notification.permission === 'default') {
            await Notification.requestPermission();
        }
        
        if (Notification.permission === 'granted' && document.hidden) {
            new Notification(notification.title, {
                body: notification.message,
                icon: '/admin/assets/images/icon-192.png',
                badge: '/admin/assets/images/badge-72.png',
                tag: notification.type,
                requireInteraction: notification.importance === 'critical'
            });
        }
    }

    /**
     * Configurar Service Worker para notificaciones push
     */
    async setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/admin/sw-notifications.js');
                console.log('Service Worker registered for notifications');
                
                // Suscribirse a push notifications si es compatible
                if ('PushManager' in window) {
                    this.setupPushNotifications(registration);
                }
            } catch (error) {
                console.log('Service Worker registration failed:', error);
            }
        }
    }

    /**
     * Configurar eventos y bindings
     */
    bindEvents() {
        // Cerrar panel al hacer click fuera
        document.addEventListener('click', (e) => {
            const panel = document.querySelector('.notifications-panel');
            const toggle = document.querySelector('.notifications-toggle');
            
            if (panel && !panel.contains(e.target) && !toggle.contains(e.target)) {
                panel.style.display = 'none';
            }
        });
        
        // Atajos de teclado
        document.addEventListener('keydown', (e) => {
            if (e.key === 'n' && (e.ctrlKey || e.metaKey) && e.shiftKey) {
                e.preventDefault();
                this.toggleNotifications();
            }
        });
    }

    /**
     * Métodos de interacción
     */
    toggleNotifications() {
        const panel = document.querySelector('.notifications-panel');
        const isVisible = panel.style.display !== 'none';
        panel.style.display = isVisible ? 'none' : 'block';
        
        if (!isVisible) {
            // Marcar notificaciones como vistas (no leídas)
            this.markNotificationsAsSeen();
        }
    }

    markAsRead(notificationId) {
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification) {
            notification.read = true;
            this.updateNotificationBadge();
            this.renderNotifications();
            
            // Persistir en localStorage
            this.saveNotificationsState();
        }
    }

    markAllAsRead() {
        this.notifications.forEach(n => n.read = true);
        this.updateNotificationBadge();
        this.renderNotifications();
        this.saveNotificationsState();
    }

    deleteNotification(notificationId) {
        this.notifications = this.notifications.filter(n => n.id !== notificationId);
        this.updateNotificationBadge();
        this.renderNotifications();
        this.saveNotificationsState();
    }

    clearAll() {
        this.notifications = [];
        this.updateNotificationBadge();
        this.renderNotifications();
        this.saveNotificationsState();
    }

    markNotificationsAsSeen() {
        // Marcar como vistas pero no necesariamente leídas
        this.notifications.forEach(n => {
            if (!n.seen) {
                n.seen = true;
            }
        });
    }

    /**
     * Utilidades
     */
    formatTime(timestamp) {
        const now = Date.now();
        const diff = now - timestamp;
        
        if (diff < 60000) return 'Ahora';
        if (diff < 3600000) return `${Math.floor(diff / 60000)}m`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)}h`;
        
        return new Date(timestamp).toLocaleDateString();
    }

    shouldPlaySound() {
        return localStorage.getItem('admin_notifications_sound') !== 'false';
    }

    isAudioEnabled() {
        return localStorage.getItem('admin_notifications_audio') !== 'false';
    }

    saveNotificationsState() {
        try {
            const state = {
                notifications: this.notifications.slice(0, 10), // Solo guardar las 10 más recientes
                lastCheck: this.lastCheck
            };
            localStorage.setItem('admin_notifications_state', JSON.stringify(state));
        } catch (error) {
            console.error('Error saving notifications state:', error);
        }
    }

    loadNotificationsState() {
        try {
            const state = JSON.parse(localStorage.getItem('admin_notifications_state') || '{}');
            if (state.notifications) {
                this.notifications = state.notifications;
                this.updateNotificationBadge();
                this.renderNotifications();
            }
            if (state.lastCheck) {
                this.lastCheck = state.lastCheck;
            }
        } catch (error) {
            console.error('Error loading notifications state:', error);
        }
    }

    /**
     * API pública para agregar notificaciones manualmente
     */
    notify(type, message, options = {}) {
        this.addNotification({
            type: type,
            message: message,
            timestamp: Date.now(),
            ...options
        });
    }
}

// CSS para notificaciones
const notificationStyles = `
<style>
/* Contenedor principal */
.notifications-container {
    position: fixed;
    top: 2rem;
    right: 2rem;
    z-index: 9999;
}

.notifications-toggle {
    position: relative;
    background: var(--admin-accent-blue);
    color: white;
    border: none;
    border-radius: 50%;
    width: 3rem;
    height: 3rem;
    cursor: pointer;
    box-shadow: var(--admin-shadow-lg);
    transition: all 0.3s ease;
}

.notifications-toggle:hover {
    background: var(--admin-accent-blue-hover);
    transform: scale(1.1);
}

.notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    border-radius: 10px;
    padding: 0.125rem 0.375rem;
    font-size: 0.625rem;
    font-weight: 600;
    min-width: 18px;
    text-align: center;
}

/* Panel de notificaciones */
.notifications-panel {
    position: absolute;
    top: 4rem;
    right: 0;
    width: 400px;
    max-height: 600px;
    background: var(--admin-bg-primary);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-lg);
    box-shadow: var(--admin-shadow-xl);
    overflow: hidden;
}

.notifications-panel-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--admin-border-light);
    background: var(--admin-bg-secondary);
}

.notifications-panel-header h3 {
    margin: 0 0 0.5rem 0;
    color: var(--admin-text-primary);
    font-size: 1.1rem;
}

.notifications-actions {
    display: flex;
    gap: 0.5rem;
}

.mark-all-read, .clear-all {
    background: transparent;
    border: 1px solid var(--admin-border-light);
    color: var(--admin-text-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: var(--admin-radius-sm);
    cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.2s ease;
}

.mark-all-read:hover, .clear-all:hover {
    background: var(--admin-bg-tertiary);
    color: var(--admin-text-primary);
}

/* Lista de notificaciones */
.notifications-list {
    max-height: 500px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--admin-border-light);
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background: var(--admin-bg-secondary);
}

.notification-item.unread {
    background: rgba(9, 105, 218, 0.05);
    border-left: 3px solid var(--admin-accent-blue);
}

.notification-icon {
    margin-right: 1rem;
    font-size: 1.25rem;
    margin-top: 0.25rem;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem;
}

.notification-title {
    font-weight: 600;
    color: var(--admin-text-primary);
    font-size: 0.875rem;
}

.notification-time {
    color: var(--admin-text-muted);
    font-size: 0.75rem;
}

.notification-message {
    color: var(--admin-text-secondary);
    font-size: 0.875rem;
    line-height: 1.4;
    margin-bottom: 0.5rem;
}

.notification-action {
    color: var(--admin-accent-blue);
    text-decoration: none;
    font-size: 0.75rem;
    font-weight: 500;
}

.notification-actions {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-left: 0.5rem;
}

.mark-read-btn, .delete-btn {
    background: transparent;
    border: 1px solid var(--admin-border-light);
    color: var(--admin-text-muted);
    padding: 0.25rem;
    border-radius: 3px;
    cursor: pointer;
    font-size: 0.75rem;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mark-read-btn:hover {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.delete-btn:hover {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

/* No hay notificaciones */
.no-notifications {
    text-align: center;
    padding: 3rem 1.5rem;
    color: var(--admin-text-muted);
}

.no-notifications i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Popup de notificación */
.notification-popup {
    position: fixed;
    top: 2rem;
    right: 2rem;
    width: 350px;
    background: var(--admin-bg-primary);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-lg);
    box-shadow: var(--admin-shadow-xl);
    z-index: 10000;
    animation: slideInRight 0.3s ease;
}

.notification-popup.critical {
    border-left: 4px solid #ef4444;
}

.notification-popup.high {
    border-left: 4px solid #f59e0b;
}

.popup-content {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    gap: 1rem;
}

.popup-icon {
    font-size: 1.5rem;
    margin-top: 0.25rem;
}

.popup-text {
    flex: 1;
}

.popup-title {
    font-weight: 600;
    color: var(--admin-text-primary);
    margin-bottom: 0.25rem;
}

.popup-message {
    color: var(--admin-text-secondary);
    font-size: 0.875rem;
    line-height: 1.4;
}

.popup-close {
    background: transparent;
    border: none;
    color: var(--admin-text-muted);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 3px;
}

.popup-close:hover {
    background: var(--admin-bg-tertiary);
    color: var(--admin-text-primary);
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Responsive */
@media (max-width: 768px) {
    .notifications-container {
        top: 1rem;
        right: 1rem;
    }
    
    .notifications-panel {
        width: 320px;
        max-width: calc(100vw - 2rem);
    }
    
    .notification-popup {
        width: 300px;
        max-width: calc(100vw - 2rem);
        top: 1rem;
        right: 1rem;
    }
}
</style>
`;

// Inyectar estilos
document.head.insertAdjacentHTML('beforeend', notificationStyles);

// Inicializar sistema de notificaciones
const realtimeNotifications = new RealtimeNotifications();

// Cargar estado previo
realtimeNotifications.loadNotificationsState();

// Guardar estado periódicamente
setInterval(() => {
    realtimeNotifications.saveNotificationsState();
}, 60000); // Cada minuto

// Exportar para uso global
window.realtimeNotifications = realtimeNotifications;