/**
 * ADMIN PROFESSIONAL INTERACTIONS
 * Enterprise-grade JavaScript for admin panel
 */

class AdminProfessional {
    constructor() {
        this.init();
        this.bindEvents();
        this.startRealTimeUpdates();
    }

    init() {
        this.setupTheme();
        this.setupMobileResponsive();
        this.setupKeyboardShortcuts();
        this.animateOnLoad();
    }

    bindEvents() {
        // Sidebar toggle for mobile
        this.bindSidebarToggle();
        
        // Search functionality
        this.bindSearchFeatures();
        
        // User menu interactions
        this.bindUserMenu();
        
        // Notification system
        this.bindNotifications();
        
        // Form enhancements
        this.bindFormEnhancements();
    }

    bindSidebarToggle() {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'admin-sidebar-toggle';
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
        toggleBtn.style.cssText = `
            display: none;
            position: fixed;
            top: 12px;
            left: 12px;
            z-index: 1060;
            background: var(--admin-primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        `;

        document.body.appendChild(toggleBtn);

        toggleBtn.addEventListener('click', () => {
            const sidebar = document.querySelector('.admin-sidebar');
            const main = document.querySelector('.admin-main');
            
            if (sidebar && main) {
                sidebar.style.transform = sidebar.style.transform === 'translateX(0px)' 
                    ? 'translateX(-100%)' 
                    : 'translateX(0px)';
            }
        });

        // Show toggle button on mobile
        const checkMobile = () => {
            if (window.innerWidth <= 1024) {
                toggleBtn.style.display = 'flex';
                toggleBtn.style.alignItems = 'center';
                toggleBtn.style.justifyContent = 'center';
            } else {
                toggleBtn.style.display = 'none';
            }
        };

        window.addEventListener('resize', checkMobile);
        checkMobile();
    }

    bindSearchFeatures() {
        const searchInput = document.querySelector('.admin-header .admin-search input');
        if (!searchInput) return;

        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.performSearch(e.target.value);
            }, 300);
        });

        // Search results dropdown
        const resultsDropdown = document.createElement('div');
        resultsDropdown.className = 'admin-search-results';
        resultsDropdown.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--admin-bg-primary);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-md);
            box-shadow: var(--admin-shadow-lg);
            max-height: 300px;
            overflow-y: auto;
            z-index: var(--admin-z-dropdown);
            margin-top: 4px;
            display: none;
        `;

        searchInput.parentElement.appendChild(resultsDropdown);
        searchInput.parentElement.style.position = 'relative';
    }

    performSearch(query) {
        if (query.length < 2) {
            this.hideSearchResults();
            return;
        }

        // Simulated search results
        const mockResults = [
            { type: 'page', title: 'Gestión de Productos', url: 'manage-products.php', icon: 'box' },
            { type: 'page', title: 'Órdenes Pendientes', url: 'order-management.php', icon: 'shopping-cart' },
            { type: 'page', title: 'Analytics Dashboard', url: 'statistics.php', icon: 'chart-bar' },
            { type: 'user', title: 'Usuario: admin@proyecto.com', url: 'manage-users.php', icon: 'user' },
            { type: 'product', title: 'Producto: Remera Básica', url: 'manage-products.php?search=remera', icon: 'tshirt' }
        ].filter(item => 
            item.title.toLowerCase().includes(query.toLowerCase())
        );

        this.showSearchResults(mockResults);
    }

    showSearchResults(results) {
        const dropdown = document.querySelector('.admin-search-results');
        if (!dropdown) return;

        if (results.length === 0) {
            dropdown.innerHTML = `
                <div style="padding: 16px; text-align: center; color: var(--admin-text-muted);">
                    <i class="fas fa-search" style="font-size: 24px; margin-bottom: 8px; opacity: 0.5;"></i>
                    <div>No se encontraron resultados</div>
                </div>
            `;
        } else {
            dropdown.innerHTML = results.map(result => `
                <a href="${result.url}" style="
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 12px 16px;
                    text-decoration: none;
                    color: var(--admin-text-primary);
                    border-bottom: 1px solid var(--admin-border-light);
                    transition: background-color 0.2s ease;
                " onmouseover="this.style.background='var(--admin-bg-secondary)'" 
                   onmouseout="this.style.background='transparent'">
                    <i class="fas fa-${result.icon}" style="color: var(--admin-primary); width: 20px;"></i>
                    <div>
                        <div style="font-weight: 500;">${result.title}</div>
                        <div style="font-size: 12px; color: var(--admin-text-muted); text-transform: capitalize;">${result.type}</div>
                    </div>
                </a>
            `).join('');
        }

        dropdown.style.display = 'block';
    }

    hideSearchResults() {
        const dropdown = document.querySelector('.admin-search-results');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }

    bindUserMenu() {
        const userBtn = document.querySelector('.admin-user-menu button');
        if (!userBtn) return;

        // Create user dropdown
        const dropdown = document.createElement('div');
        dropdown.className = 'admin-user-dropdown';
        dropdown.style.cssText = `
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--admin-bg-primary);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-md);
            box-shadow: var(--admin-shadow-lg);
            min-width: 200px;
            z-index: var(--admin-z-dropdown);
            margin-top: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all var(--admin-transition-base);
        `;

        dropdown.innerHTML = `
            <div style="padding: 16px; border-bottom: 1px solid var(--admin-border-light);">
                <div style="font-weight: 600; color: var(--admin-text-primary);">${document.querySelector('.admin-user-menu span').textContent}</div>
                <div style="font-size: 12px; color: var(--admin-text-muted);">Administrador</div>
            </div>
            <a href="../profile.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; text-decoration: none; color: var(--admin-text-primary); transition: background-color 0.2s ease;" onmouseover="this.style.background='var(--admin-bg-secondary)'" onmouseout="this.style.background='transparent'">
                <i class="fas fa-user"></i>
                <span>Mi Perfil</span>
            </a>
            <a href="settings.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; text-decoration: none; color: var(--admin-text-primary); transition: background-color 0.2s ease;" onmouseover="this.style.background='var(--admin-bg-secondary)'" onmouseout="this.style.background='transparent'">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a>
            <div style="border-top: 1px solid var(--admin-border-light); margin: 8px 0;"></div>
            <a href="../logout.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; text-decoration: none; color: var(--admin-danger); transition: background-color 0.2s ease;" onmouseover="this.style.background='var(--admin-danger-light)'" onmouseout="this.style.background='transparent'">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        `;

        userBtn.parentElement.appendChild(dropdown);
        userBtn.parentElement.style.position = 'relative';

        let isMenuOpen = false;
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            isMenuOpen = !isMenuOpen;
            
            if (isMenuOpen) {
                dropdown.style.opacity = '1';
                dropdown.style.visibility = 'visible';
                dropdown.style.transform = 'translateY(0)';
            } else {
                dropdown.style.opacity = '0';
                dropdown.style.visibility = 'hidden';
                dropdown.style.transform = 'translateY(-10px)';
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', () => {
            if (isMenuOpen) {
                isMenuOpen = false;
                dropdown.style.opacity = '0';
                dropdown.style.visibility = 'hidden';
                dropdown.style.transform = 'translateY(-10px)';
            }
        });
    }

    bindNotifications() {
        const notificationBtn = document.querySelector('.admin-header-actions button[onclick*="toggleNotifications"]');
        if (!notificationBtn) return;

        notificationBtn.onclick = (e) => {
            e.preventDefault();
            this.showNotificationPanel();
        };
    }

    showNotificationPanel() {
        // Create notification panel if it doesn't exist
        let panel = document.querySelector('.admin-notification-panel');
        if (!panel) {
            panel = document.createElement('div');
            panel.className = 'admin-notification-panel';
            panel.style.cssText = `
                position: fixed;
                top: 70px;
                right: 20px;
                width: 360px;
                max-height: 500px;
                background: var(--admin-bg-primary);
                border: 1px solid var(--admin-border);
                border-radius: var(--admin-radius-lg);
                box-shadow: var(--admin-shadow-xl);
                z-index: var(--admin-z-modal);
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
                transition: all var(--admin-transition-base);
                overflow: hidden;
            `;

            panel.innerHTML = `
                <div style="padding: 20px; border-bottom: 1px solid var(--admin-border-light); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Notificaciones</h3>
                    <button onclick="this.closest('.admin-notification-panel').remove()" style="background: none; border: none; cursor: pointer; color: var(--admin-text-muted); font-size: 18px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    ${this.generateNotifications()}
                </div>
                <div style="padding: 16px; border-top: 1px solid var(--admin-border-light); text-align: center;">
                    <a href="#" style="color: var(--admin-primary); text-decoration: none; font-size: 14px; font-weight: 500;">Ver todas las notificaciones</a>
                </div>
            `;

            document.body.appendChild(panel);
        }

        // Animate panel appearance
        setTimeout(() => {
            panel.style.opacity = '1';
            panel.style.transform = 'translateY(0) scale(1)';
        }, 10);

        // Auto-close after 10 seconds
        setTimeout(() => {
            if (panel && panel.parentElement) {
                panel.style.opacity = '0';
                panel.style.transform = 'translateY(-20px) scale(0.95)';
                setTimeout(() => panel.remove(), 300);
            }
        }, 10000);
    }

    generateNotifications() {
        const notifications = [
            { type: 'success', icon: 'check-circle', message: 'Nueva orden procesada exitosamente', time: '2 min', color: 'success' },
            { type: 'warning', icon: 'exclamation-triangle', message: 'Stock bajo en 3 productos', time: '15 min', color: 'warning' },
            { type: 'info', icon: 'info-circle', message: 'Actualización del sistema disponible', time: '1h', color: 'info' },
            { type: 'danger', icon: 'times-circle', message: 'Error en sincronización de inventario', time: '2h', color: 'danger' }
        ];

        return notifications.map(notif => `
            <div style="padding: 16px; border-bottom: 1px solid var(--admin-border-light); display: flex; gap: 12px; align-items: flex-start; transition: background-color 0.2s ease;" onmouseover="this.style.background='var(--admin-bg-secondary)'" onmouseout="this.style.background='transparent'">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--admin-${notif.color}-light); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-${notif.icon}" style="color: var(--admin-${notif.color}); font-size: 14px;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 14px; color: var(--admin-text-primary); margin-bottom: 4px; line-height: 1.4;">
                        ${notif.message}
                    </div>
                    <div style="font-size: 12px; color: var(--admin-text-muted);">
                        hace ${notif.time}
                    </div>
                </div>
                <button style="background: none; border: none; cursor: pointer; color: var(--admin-text-muted); opacity: 0; transition: opacity 0.2s ease;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('.admin-search input');
                if (searchInput) {
                    searchInput.focus();
                }
            }

            // Escape to close modals/dropdowns
            if (e.key === 'Escape') {
                this.hideSearchResults();
                document.querySelectorAll('.admin-notification-panel').forEach(panel => panel.remove());
            }
        });
    }

    setupTheme() {
        // Theme toggle functionality
        const themeToggle = document.createElement('button');
        themeToggle.className = 'admin-theme-toggle';
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        themeToggle.style.cssText = `
            background: none;
            border: none;
            cursor: pointer;
            color: var(--admin-text-secondary);
            font-size: 18px;
            padding: 8px;
            border-radius: 8px;
            transition: all var(--admin-transition-fast);
        `;

        const headerActions = document.querySelector('.admin-header-actions');
        if (headerActions) {
            headerActions.insertBefore(themeToggle, headerActions.firstChild);
        }

        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('admin-theme', newTheme);
            
            themeToggle.innerHTML = newTheme === 'dark' 
                ? '<i class="fas fa-sun"></i>' 
                : '<i class="fas fa-moon"></i>';
        });

        // Load saved theme
        const savedTheme = localStorage.getItem('admin-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        themeToggle.innerHTML = savedTheme === 'dark' 
            ? '<i class="fas fa-sun"></i>' 
            : '<i class="fas fa-moon"></i>';
    }

    setupMobileResponsive() {
        // Add mobile-specific behaviors
        const handleResize = () => {
            const sidebar = document.querySelector('.admin-sidebar');
            const main = document.querySelector('.admin-main');
            
            if (window.innerWidth <= 1024) {
                if (sidebar) sidebar.style.transform = 'translateX(-100%)';
                if (main) main.style.marginLeft = '0';
            } else {
                if (sidebar) sidebar.style.transform = 'translateX(0)';
                if (main) main.style.marginLeft = '260px';
            }
        };

        window.addEventListener('resize', handleResize);
        handleResize();
    }

    bindFormEnhancements() {
        // Enhanced form interactions
        document.querySelectorAll('.admin-form-input, .admin-form-textarea, .admin-form-select').forEach(input => {
            // Floating label effect
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', () => {
                if (!input.value) {
                    input.parentElement.classList.remove('focused');
                }
            });

            // Real-time validation visual feedback
            input.addEventListener('input', () => {
                this.validateInput(input);
            });
        });
    }

    validateInput(input) {
        const isValid = input.checkValidity();
        
        if (isValid) {
            input.style.borderColor = 'var(--admin-success)';
            input.style.boxShadow = '0 0 0 3px rgba(40, 167, 69, 0.1)';
        } else if (input.value) {
            input.style.borderColor = 'var(--admin-danger)';
            input.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
        } else {
            input.style.borderColor = 'var(--admin-border)';
            input.style.boxShadow = 'none';
        }
    }

    animateOnLoad() {
        // Animate elements on page load
        const animateElements = document.querySelectorAll('.admin-metric-card, .admin-card');
        
        animateElements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }

    startRealTimeUpdates() {
        // Simulate real-time updates
        setInterval(() => {
            this.updateMetrics();
        }, 30000); // Update every 30 seconds
    }

    updateMetrics() {
        // Simulate metric updates with small random changes
        const metricValues = document.querySelectorAll('.admin-metric-value');
        
        metricValues.forEach(metric => {
            const currentValue = metric.textContent;
            // Add subtle animation to indicate update
            metric.style.transform = 'scale(1.05)';
            metric.style.transition = 'transform 0.2s ease';
            
            setTimeout(() => {
                metric.style.transform = 'scale(1)';
            }, 200);
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AdminProfessional();
});

// Export for use in other modules
window.AdminProfessional = AdminProfessional;