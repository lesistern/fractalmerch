/**
 * Sistema de Navegación Dinámico para Admin Panel
 * Mejora la productividad con navegación inteligente y shortcuts
 */

class DynamicNavigation {
    constructor() {
        this.currentPage = this.getCurrentPage();
        this.navigationHistory = this.loadNavigationHistory();
        this.shortcuts = {};
        this.breadcrumbs = [];
        this.quickActions = [];
        
        this.init();
    }

    /**
     * Inicializar el sistema de navegación
     */
    init() {
        this.setupKeyboardShortcuts();
        this.setupBreadcrumbs();
        this.setupQuickActions();
        this.setupSidebarEnhancements();
        this.trackPageVisit();
        this.setupSearchNavigation();
    }

    /**
     * Obtener página actual
     */
    getCurrentPage() {
        const path = window.location.pathname;
        const page = path.split('/').pop().replace('.php', '');
        return page || 'dashboard';
    }

    /**
     * Configurar atajos de teclado globales
     */
    setupKeyboardShortcuts() {
        this.shortcuts = {
            // Navegación rápida
            'ctrl+shift+d': () => this.navigateTo('dashboard.php'),
            'ctrl+shift+p': () => this.navigateTo('manage-products.php'),
            'ctrl+shift+u': () => this.navigateTo('manage-users.php'),
            'ctrl+shift+s': () => this.navigateTo('statistics.php'),
            'ctrl+shift+o': () => this.navigateTo('orders.php'),
            
            // Acciones rápidas
            'ctrl+shift+n': () => this.quickAction('new'),
            'ctrl+shift+e': () => this.quickAction('export'),
            'ctrl+shift+r': () => this.refreshPage(),
            'ctrl+shift+f': () => this.focusSearch(),
            'ctrl+shift+h': () => this.showHelp(),
            
            // Navegación por pestañas
            'ctrl+tab': () => this.nextTab(),
            'ctrl+shift+tab': () => this.prevTab(),
            
            // Escape para cerrar modales
            'escape': () => this.closeModals()
        };

        // Registrar event listeners
        document.addEventListener('keydown', (e) => {
            const combo = this.getKeyCombo(e);
            if (this.shortcuts[combo]) {
                e.preventDefault();
                this.shortcuts[combo]();
            }
        });

        // Mostrar shortcuts disponibles
        this.createShortcutHelp();
    }

    /**
     * Obtener combinación de teclas
     */
    getKeyCombo(event) {
        const parts = [];
        
        if (event.ctrlKey) parts.push('ctrl');
        if (event.shiftKey) parts.push('shift');
        if (event.altKey) parts.push('alt');
        if (event.metaKey) parts.push('meta');
        
        if (event.key !== 'Control' && event.key !== 'Shift' && 
            event.key !== 'Alt' && event.key !== 'Meta') {
            parts.push(event.key.toLowerCase());
        }
        
        return parts.join('+');
    }

    /**
     * Configurar breadcrumbs dinámicos
     */
    setupBreadcrumbs() {
        const breadcrumbsContainer = document.querySelector('.breadcrumbs') || 
            this.createBreadcrumbsContainer();
        
        this.breadcrumbs = this.generateBreadcrumbs();
        this.renderBreadcrumbs(breadcrumbsContainer);
    }

    /**
     * Generar breadcrumbs basado en la página actual
     */
    generateBreadcrumbs() {
        const pageMap = {
            'dashboard': ['🏠 Dashboard'],
            'manage-products': ['🏠 Dashboard', '📦 Productos'],
            'manage-users': ['🏠 Dashboard', '👥 Usuarios'],
            'statistics': ['🏠 Dashboard', '📊 Estadísticas'],
            'orders': ['🏠 Dashboard', '🛒 Pedidos'],
            'settings': ['🏠 Dashboard', '⚙️ Configuración']
        };

        return pageMap[this.currentPage] || ['🏠 Dashboard'];
    }

    /**
     * Crear contenedor de breadcrumbs
     */
    createBreadcrumbsContainer() {
        const container = document.createElement('nav');
        container.className = 'breadcrumbs';
        container.setAttribute('aria-label', 'Navegación');
        
        const pageHeader = document.querySelector('.page-header');
        if (pageHeader) {
            pageHeader.insertBefore(container, pageHeader.firstChild);
        }
        
        return container;
    }

    /**
     * Renderizar breadcrumbs
     */
    renderBreadcrumbs(container) {
        const html = this.breadcrumbs.map((crumb, index) => {
            const isLast = index === this.breadcrumbs.length - 1;
            return `
                <span class="breadcrumb-item ${isLast ? 'active' : ''}">
                    ${isLast ? crumb : `<a href="#" onclick="dynamicNav.navigateBack(${index})">${crumb}</a>`}
                </span>
                ${!isLast ? '<i class="fas fa-chevron-right breadcrumb-separator"></i>' : ''}
            `;
        }).join('');
        
        container.innerHTML = html;
    }

    /**
     * Configurar acciones rápidas
     */
    setupQuickActions() {
        this.quickActions = this.getPageQuickActions();
        this.createQuickActionsToolbar();
    }

    /**
     * Obtener acciones rápidas por página
     */
    getPageQuickActions() {
        const actions = {
            'dashboard': [
                { icon: 'fas fa-plus', label: 'Nuevo Producto', action: () => this.navigateTo('manage-products.php') },
                { icon: 'fas fa-chart-line', label: 'Ver Estadísticas', action: () => this.navigateTo('statistics.php') },
                { icon: 'fas fa-download', label: 'Exportar Datos', action: () => this.exportData() }
            ],
            'manage-products': [
                { icon: 'fas fa-plus', label: 'Nuevo Producto', action: () => this.quickAction('new_product') },
                { icon: 'fas fa-upload', label: 'Import Bulk', action: () => this.quickAction('bulk_import') },
                { icon: 'fas fa-tags', label: 'Gestionar Categorías', action: () => this.quickAction('categories') }
            ],
            'manage-users': [
                { icon: 'fas fa-user-plus', label: 'Nuevo Usuario', action: () => this.quickAction('new_user') },
                { icon: 'fas fa-shield-alt', label: 'Permisos', action: () => this.quickAction('permissions') },
                { icon: 'fas fa-download', label: 'Exportar Usuarios', action: () => this.quickAction('export_users') }
            ]
        };

        return actions[this.currentPage] || [];
    }

    /**
     * Crear toolbar de acciones rápidas
     */
    createQuickActionsToolbar() {
        if (this.quickActions.length === 0) return;

        let toolbar = document.querySelector('.quick-actions-toolbar');
        if (!toolbar) {
            toolbar = document.createElement('div');
            toolbar.className = 'quick-actions-toolbar';
            
            const pageHeader = document.querySelector('.page-header');
            if (pageHeader) {
                pageHeader.appendChild(toolbar);
            }
        }

        const html = this.quickActions.map(action => `
            <button class="quick-action-btn" onclick="dynamicNav.executeQuickAction('${action.label}')" title="${action.label}">
                <i class="${action.icon}"></i>
                <span>${action.label}</span>
            </button>
        `).join('');

        toolbar.innerHTML = html;
    }

    /**
     * Mejorar sidebar con funcionalidad avanzada
     */
    setupSidebarEnhancements() {
        const sidebar = document.querySelector('.admin-sidebar');
        if (!sidebar) return;

        // Agregar indicadores de actividad
        this.addActivityIndicators();
        
        // Agregar navegación por historial
        this.addNavigationHistory();
        
        // Agregar collapse/expand functionality
        this.setupSidebarCollapse();
        
        // Agregar tooltips informativos
        this.addSidebarTooltips();
    }

    /**
     * Agregar indicadores de actividad a items del sidebar
     */
    addActivityIndicators() {
        const menuItems = document.querySelectorAll('.admin-sidebar a');
        
        menuItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href) {
                const pageKey = href.replace('.php', '');
                const visitCount = this.getPageVisitCount(pageKey);
                
                if (visitCount > 0) {
                    const indicator = document.createElement('span');
                    indicator.className = 'activity-indicator';
                    indicator.textContent = visitCount > 99 ? '99+' : visitCount;
                    item.appendChild(indicator);
                }
            }
        });
    }

    /**
     * Configurar búsqueda en navegación
     */
    setupSearchNavigation() {
        const searchInput = this.createNavigationSearch();
        
        searchInput.addEventListener('input', (e) => {
            this.filterNavigation(e.target.value);
        });

        // Atajo para enfocar búsqueda
        this.shortcuts['ctrl+k'] = () => {
            searchInput.focus();
            searchInput.select();
        };
    }

    /**
     * Crear input de búsqueda en navegación
     */
    createNavigationSearch() {
        let searchContainer = document.querySelector('.nav-search');
        
        if (!searchContainer) {
            searchContainer = document.createElement('div');
            searchContainer.className = 'nav-search';
            searchContainer.innerHTML = `
                <div class="search-input-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="nav-search-input" placeholder="Buscar en navegación... (Ctrl+K)">
                    <span class="search-shortcut">Ctrl+K</span>
                </div>
            `;
            
            const sidebar = document.querySelector('.admin-sidebar');
            if (sidebar) {
                sidebar.insertBefore(searchContainer, sidebar.firstChild);
            }
        }
        
        return searchContainer.querySelector('.nav-search-input');
    }

    /**
     * Filtrar navegación basado en búsqueda
     */
    filterNavigation(query) {
        const menuItems = document.querySelectorAll('.admin-sidebar .sidebar-item');
        
        menuItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            const matches = text.includes(query.toLowerCase());
            
            item.style.display = matches || query === '' ? 'block' : 'none';
            
            if (matches && query !== '') {
                item.classList.add('search-highlight');
            } else {
                item.classList.remove('search-highlight');
            }
        });
    }

    /**
     * Crear ayuda de shortcuts
     */
    createShortcutHelp() {
        const helpButton = document.createElement('button');
        helpButton.className = 'shortcuts-help-btn';
        helpButton.innerHTML = '<i class="fas fa-keyboard"></i>';
        helpButton.title = 'Ver atajos de teclado (Ctrl+Shift+H)';
        helpButton.onclick = () => this.showHelp();
        
        document.body.appendChild(helpButton);
    }

    /**
     * Mostrar ayuda de shortcuts
     */
    showHelp() {
        const modal = document.createElement('div');
        modal.className = 'shortcuts-modal';
        modal.innerHTML = `
            <div class="shortcuts-content">
                <div class="shortcuts-header">
                    <h3><i class="fas fa-keyboard"></i> Atajos de Teclado</h3>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="shortcuts-body">
                    <div class="shortcut-group">
                        <h4>Navegación Rápida</h4>
                        <div class="shortcut-item">
                            <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>D</kbd>
                            <span>Dashboard</span>
                        </div>
                        <div class="shortcut-item">
                            <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>P</kbd>
                            <span>Productos</span>
                        </div>
                        <div class="shortcut-item">
                            <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>U</kbd>
                            <span>Usuarios</span>
                        </div>
                        <div class="shortcut-item">
                            <kbd>Ctrl</kbd> + <kbd>K</kbd>
                            <span>Buscar navegación</span>
                        </div>
                    </div>
                    <div class="shortcut-group">
                        <h4>Acciones Rápidas</h4>
                        <div class="shortcut-item">
                            <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>N</kbd>
                            <span>Nuevo elemento</span>
                        </div>
                        <div class="shortcut-item">
                            <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>E</kbd>
                            <span>Exportar datos</span>
                        </div>
                        <div class="shortcut-item">
                            <kbd>Escape</kbd>
                            <span>Cerrar modales</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    /**
     * Navegación entre páginas
     */
    navigateTo(page) {
        this.saveNavigationHistory();
        window.location.href = page;
    }

    /**
     * Ejecutar acción rápida
     */
    executeQuickAction(actionLabel) {
        const action = this.quickActions.find(a => a.label === actionLabel);
        if (action) {
            action.action();
        }
    }

    /**
     * Acción rápida genérica
     */
    quickAction(type) {
        switch(type) {
            case 'new':
                if (this.currentPage === 'manage-products') {
                    document.getElementById('toggleProductForm')?.click();
                } else if (this.currentPage === 'manage-users') {
                    document.querySelector('.btn-primary')?.click();
                }
                break;
            case 'export':
                document.querySelector('[onclick*="export"]')?.click();
                break;
            default:
                console.log(`Quick action: ${type}`);
        }
    }

    /**
     * Gestión del historial de navegación
     */
    saveNavigationHistory() {
        const history = this.loadNavigationHistory();
        history.push({
            page: this.currentPage,
            timestamp: Date.now(),
            url: window.location.href
        });
        
        // Mantener solo los últimos 10 elementos
        if (history.length > 10) {
            history.shift();
        }
        
        localStorage.setItem('admin_nav_history', JSON.stringify(history));
    }

    loadNavigationHistory() {
        try {
            return JSON.parse(localStorage.getItem('admin_nav_history') || '[]');
        } catch {
            return [];
        }
    }

    /**
     * Tracking de visitas a páginas
     */
    trackPageVisit() {
        const visits = JSON.parse(localStorage.getItem('admin_page_visits') || '{}');
        visits[this.currentPage] = (visits[this.currentPage] || 0) + 1;
        localStorage.setItem('admin_page_visits', JSON.stringify(visits));
    }

    getPageVisitCount(page) {
        const visits = JSON.parse(localStorage.getItem('admin_page_visits') || '{}');
        return visits[page] || 0;
    }

    /**
     * Utilidades
     */
    refreshPage() {
        window.location.reload();
    }

    focusSearch() {
        const searchInput = document.querySelector('.nav-search-input');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    closeModals() {
        // Cerrar cualquier modal abierto
        document.querySelectorAll('.modal, .overlay, .product-form-panel').forEach(modal => {
            if (modal.style.display !== 'none') {
                modal.style.display = 'none';
            }
        });
    }
}

// CSS para el sistema de navegación
const navigationStyles = `
<style>
/* Breadcrumbs */
.breadcrumbs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.breadcrumb-item a {
    color: var(--admin-accent-blue);
    text-decoration: none;
}

.breadcrumb-item a:hover {
    text-decoration: underline;
}

.breadcrumb-separator {
    font-size: 0.75rem;
    opacity: 0.6;
}

/* Quick Actions Toolbar */
.quick-actions-toolbar {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--admin-bg-secondary);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-md);
    color: var(--admin-text-primary);
    cursor: pointer;
    transition: all 0.2s ease;
}

.quick-action-btn:hover {
    background: var(--admin-accent-blue);
    color: white;
    transform: translateY(-1px);
}

/* Navigation Search */
.nav-search {
    padding: 1rem;
    border-bottom: 1px solid var(--admin-border-light);
}

.search-input-container {
    position: relative;
    display: flex;
    align-items: center;
}

.nav-search-input {
    width: 100%;
    padding: 0.5rem 2rem 0.5rem 2.5rem;
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-md);
    background: var(--admin-bg-primary);
    font-size: 0.875rem;
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    color: var(--admin-text-secondary);
    z-index: 1;
}

.search-shortcut {
    position: absolute;
    right: 0.75rem;
    font-size: 0.75rem;
    color: var(--admin-text-muted);
    background: var(--admin-bg-tertiary);
    padding: 0.125rem 0.25rem;
    border-radius: 3px;
    border: 1px solid var(--admin-border-light);
}

/* Activity Indicators */
.activity-indicator {
    background: var(--admin-accent-blue);
    color: white;
    font-size: 0.625rem;
    padding: 0.125rem 0.375rem;
    border-radius: 10px;
    margin-left: auto;
}

/* Search Highlighting */
.search-highlight {
    background: rgba(9, 105, 218, 0.1) !important;
    border-left: 3px solid var(--admin-accent-blue) !important;
}

/* Shortcuts Help */
.shortcuts-help-btn {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 3rem;
    height: 3rem;
    background: var(--admin-accent-blue);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: var(--admin-shadow-lg);
    z-index: 1000;
    transition: all 0.3s ease;
}

.shortcuts-help-btn:hover {
    transform: scale(1.1);
    box-shadow: var(--admin-shadow-xl);
}

/* Shortcuts Modal */
.shortcuts-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.shortcuts-content {
    background: var(--admin-bg-primary);
    border-radius: var(--admin-radius-lg);
    max-width: 600px;
    width: 90vw;
    max-height: 80vh;
    overflow-y: auto;
}

.shortcuts-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--admin-border-light);
}

.shortcuts-body {
    padding: 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
}

.shortcut-group h4 {
    margin-bottom: 1rem;
    color: var(--admin-text-primary);
}

.shortcut-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--admin-border-light);
}

kbd {
    background: var(--admin-bg-tertiary);
    border: 1px solid var(--admin-border-medium);
    border-radius: 3px;
    padding: 0.125rem 0.375rem;
    font-size: 0.75rem;
    font-family: monospace;
}
</style>
`;

// Inyectar estilos
document.head.insertAdjacentHTML('beforeend', navigationStyles);

// Inicializar navegación dinámica
const dynamicNav = new DynamicNavigation();

// Exportar para uso global
window.dynamicNav = dynamicNav;