/**
 * Optimizador Mobile para Admin Panel
 * Transforma la experiencia de escritorio en una interfaz móvil nativa
 */

class MobileAdminOptimizer {
    constructor() {
        this.isMobile = this.detectMobileDevice();
        this.isTablet = this.detectTabletDevice();
        this.orientation = this.getOrientation();
        this.touchSupport = 'ontouchstart' in window;
        
        this.mobileBreakpoint = 768;
        this.tabletBreakpoint = 1024;
        this.currentLayout = 'desktop';
        
        this.swipeGestures = new Map();
        this.touchStartX = 0;
        this.touchStartY = 0;
        
        this.init();
    }

    /**
     * Inicializar optimizaciones móviles
     */
    init() {
        this.detectDeviceCapabilities();
        this.setupResponsiveLayout();
        this.optimizeNavigation();
        this.enhanceTouchInteractions();
        this.optimizeTableResponsiveness();
        this.setupMobileGestures();
        this.optimizeFormInputs();
        this.enableOfflineCapabilities();
        this.setupMobileMenus();
        this.optimizePerformanceForMobile();
        this.createMobileToolbar();
    }

    /**
     * Detectar capacidades del dispositivo
     */
    detectDeviceCapabilities() {
        this.deviceInfo = {
            isMobile: this.isMobile,
            isTablet: this.isTablet,
            touchSupport: this.touchSupport,
            screenSize: `${screen.width}x${screen.height}`,
            pixelRatio: window.devicePixelRatio || 1,
            orientation: this.orientation,
            connection: navigator.connection?.effectiveType || 'unknown',
            battery: navigator.getBattery ? true : false,
            vibration: 'vibrate' in navigator,
            geolocation: 'geolocation' in navigator
        };

        console.log('📱 Device capabilities:', this.deviceInfo);
    }

    /**
     * Configurar layout responsivo
     */
    setupResponsiveLayout() {
        // Crear breakpoint observer
        this.setupBreakpointObserver();
        
        // Aplicar layout inicial
        this.applyMobileLayout();
        
        // Listener para cambios de orientación
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.handleOrientationChange();
            }, 100);
        });

        // Listener para resize
        window.addEventListener('resize', this.debounce(() => {
            this.handleViewportChange();
        }, 250));
    }

    /**
     * Configurar observer de breakpoints
     */
    setupBreakpointObserver() {
        if ('matchMedia' in window) {
            this.mobileQuery = window.matchMedia(`(max-width: ${this.mobileBreakpoint}px)`);
            this.tabletQuery = window.matchMedia(`(max-width: ${this.tabletBreakpoint}px)`);
            
            this.mobileQuery.addListener((e) => {
                if (e.matches) {
                    this.switchToMobileLayout();
                } else {
                    this.switchToDesktopLayout();
                }
            });

            this.tabletQuery.addListener((e) => {
                if (e.matches && !this.mobileQuery.matches) {
                    this.switchToTabletLayout();
                }
            });
        }
    }

    /**
     * Aplicar layout móvil
     */
    applyMobileLayout() {
        if (this.isMobile || window.innerWidth <= this.mobileBreakpoint) {
            this.switchToMobileLayout();
        } else if (this.isTablet || window.innerWidth <= this.tabletBreakpoint) {
            this.switchToTabletLayout();
        }
    }

    /**
     * Cambiar a layout móvil
     */
    switchToMobileLayout() {
        this.currentLayout = 'mobile';
        document.body.classList.add('mobile-layout');
        document.body.classList.remove('tablet-layout', 'desktop-layout');
        
        this.optimizeSidebarForMobile();
        this.optimizeHeaderForMobile();
        this.optimizeContentForMobile();
        this.enableMobileGestures();
        
        console.log('📱 Switched to mobile layout');
    }

    /**
     * Cambiar a layout tablet
     */
    switchToTabletLayout() {
        this.currentLayout = 'tablet';
        document.body.classList.add('tablet-layout');
        document.body.classList.remove('mobile-layout', 'desktop-layout');
        
        this.optimizeSidebarForTablet();
        this.optimizeHeaderForTablet();
        this.optimizeContentForTablet();
        
        console.log('📱 Switched to tablet layout');
    }

    /**
     * Cambiar a layout desktop
     */
    switchToDesktopLayout() {
        this.currentLayout = 'desktop';
        document.body.classList.add('desktop-layout');
        document.body.classList.remove('mobile-layout', 'tablet-layout');
        
        this.restoreDesktopLayout();
        
        console.log('🖥️ Switched to desktop layout');
    }

    /**
     * Optimizar navegación para móvil
     */
    optimizeNavigation() {
        const sidebar = document.querySelector('.admin-sidebar');
        if (!sidebar) return;

        // Crear botón hamburguesa
        this.createMobileMenuButton();
        
        // Convertir sidebar en drawer móvil
        this.convertSidebarToDrawer();
        
        // Optimizar elementos de navegación
        this.optimizeNavigationItems();
    }

    /**
     * Crear botón de menú móvil
     */
    createMobileMenuButton() {
        let menuButton = document.querySelector('.mobile-menu-button');
        
        if (!menuButton) {
            menuButton = document.createElement('button');
            menuButton.className = 'mobile-menu-button';
            menuButton.innerHTML = `
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            `;
            
            menuButton.addEventListener('click', () => {
                this.toggleMobileMenu();
            });
            
            // Insertar en header
            const header = document.querySelector('.admin-header') || document.body;
            header.appendChild(menuButton);
        }
    }

    /**
     * Convertir sidebar en drawer
     */
    convertSidebarToDrawer() {
        const sidebar = document.querySelector('.admin-sidebar');
        if (!sidebar) return;

        sidebar.classList.add('mobile-drawer');
        
        // Crear overlay
        let overlay = document.querySelector('.mobile-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'mobile-overlay';
            overlay.addEventListener('click', () => {
                this.closeMobileMenu();
            });
            document.body.appendChild(overlay);
        }

        // Agregar gesto de swipe para cerrar
        this.addSwipeToCloseGesture(sidebar);
    }

    /**
     * Agregar gesto swipe para cerrar menú
     */
    addSwipeToCloseGesture(element) {
        let startX = 0;
        let currentX = 0;
        let isDragging = false;

        element.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
        }, { passive: true });

        element.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            
            currentX = e.touches[0].clientX;
            const diffX = currentX - startX;
            
            // Solo permitir swipe hacia la izquierda (cerrar)
            if (diffX < 0) {
                const transform = Math.max(diffX, -element.offsetWidth);
                element.style.transform = `translateX(${transform}px)`;
            }
        }, { passive: true });

        element.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;
            
            const diffX = currentX - startX;
            
            if (diffX < -100) { // Threshold para cerrar
                this.closeMobileMenu();
            } else {
                element.style.transform = 'translateX(0)';
            }
        }, { passive: true });
    }

    /**
     * Mejorar interacciones táctiles
     */
    enhanceTouchInteractions() {
        // Agregar ripple effect a botones
        this.addRippleEffect();
        
        // Mejorar área de toque para elementos pequeños
        this.improveTouchTargets();
        
        // Agregar feedback háptico
        this.addHapticFeedback();
        
        // Optimizar scroll para móvil
        this.optimizeMobileScrolling();
    }

    /**
     * Agregar efecto ripple a botones
     */
    addRippleEffect() {
        const buttons = document.querySelectorAll('button, .btn, .clickable');
        
        buttons.forEach(button => {
            if (button.classList.contains('ripple-enabled')) return;
            
            button.classList.add('ripple-enabled');
            button.addEventListener('click', (e) => {
                this.createRipple(e, button);
            });
        });
    }

    /**
     * Crear efecto ripple
     */
    createRipple(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.className = 'ripple-effect';
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        
        element.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    /**
     * Mejorar área de toque
     */
    improveTouchTargets() {
        const smallElements = document.querySelectorAll('a, button, input[type="checkbox"], input[type="radio"]');
        
        smallElements.forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.width < 44 || rect.height < 44) {
                element.classList.add('touch-target-enhanced');
            }
        });
    }

    /**
     * Agregar feedback háptico
     */
    addHapticFeedback() {
        if (!this.deviceInfo.vibration) return;

        const hapticElements = document.querySelectorAll('button, .btn, .clickable');
        
        hapticElements.forEach(element => {
            element.addEventListener('click', () => {
                navigator.vibrate(10); // Vibración corta
            });
        });

        // Vibración para errores
        document.addEventListener('error', () => {
            navigator.vibrate([100, 50, 100]); // Patrón de error
        });
    }

    /**
     * Optimizar tablas para móvil
     */
    optimizeTableResponsiveness() {
        const tables = document.querySelectorAll('table');
        
        tables.forEach(table => {
            this.makeTableResponsive(table);
        });
    }

    /**
     * Hacer tabla responsiva
     */
    makeTableResponsive(table) {
        if (table.classList.contains('mobile-optimized')) return;
        
        table.classList.add('mobile-optimized');
        
        // Crear wrapper scrollable
        const wrapper = document.createElement('div');
        wrapper.className = 'table-mobile-wrapper';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);

        // Agregar indicadores de scroll
        this.addScrollIndicators(wrapper);

        // Convertir a cards en móvil si es necesario
        if (this.currentLayout === 'mobile') {
            this.convertTableToCards(table);
        }
    }

    /**
     * Convertir tabla a cards
     */
    convertTableToCards(table) {
        const headers = Array.from(table.querySelectorAll('th')).map(th => th.textContent);
        const rows = table.querySelectorAll('tbody tr');
        
        const cardsContainer = document.createElement('div');
        cardsContainer.className = 'table-cards-container';
        
        rows.forEach(row => {
            const card = document.createElement('div');
            card.className = 'table-card';
            
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (headers[index]) {
                    const field = document.createElement('div');
                    field.className = 'card-field';
                    field.innerHTML = `
                        <div class="field-label">${headers[index]}</div>
                        <div class="field-value">${cell.innerHTML}</div>
                    `;
                    card.appendChild(field);
                }
            });
            
            cardsContainer.appendChild(card);
        });
        
        // Mostrar cards en móvil, tabla en desktop
        const mediaQuery = window.matchMedia(`(max-width: ${this.mobileBreakpoint}px)`);
        
        const toggleDisplay = (e) => {
            if (e.matches) {
                table.style.display = 'none';
                cardsContainer.style.display = 'block';
            } else {
                table.style.display = 'table';
                cardsContainer.style.display = 'none';
            }
        };
        
        mediaQuery.addListener(toggleDisplay);
        toggleDisplay(mediaQuery);
        
        table.parentNode.appendChild(cardsContainer);
    }

    /**
     * Configurar gestos móviles
     */
    setupMobileGestures() {
        if (!this.touchSupport) return;

        this.gestures = {
            swipeLeft: new Set(),
            swipeRight: new Set(),
            swipeUp: new Set(),
            swipeDown: new Set(),
            pinch: new Set(),
            longPress: new Set()
        };

        document.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: false });
        document.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
        document.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: false });
    }

    /**
     * Manejar inicio de toque
     */
    handleTouchStart(e) {
        this.touchStartX = e.touches[0].clientX;
        this.touchStartY = e.touches[0].clientY;
        this.touchStartTime = Date.now();
        
        // Detectar long press
        this.longPressTimer = setTimeout(() => {
            this.handleLongPress(e);
        }, 500);
    }

    /**
     * Manejar movimiento de toque
     */
    handleTouchMove(e) {
        if (this.longPressTimer) {
            clearTimeout(this.longPressTimer);
            this.longPressTimer = null;
        }
    }

    /**
     * Manejar fin de toque
     */
    handleTouchEnd(e) {
        if (this.longPressTimer) {
            clearTimeout(this.longPressTimer);
            this.longPressTimer = null;
        }

        const touchEndX = e.changedTouches[0].clientX;
        const touchEndY = e.changedTouches[0].clientY;
        const deltaX = touchEndX - this.touchStartX;
        const deltaY = touchEndY - this.touchStartY;
        const absDeltaX = Math.abs(deltaX);
        const absDeltaY = Math.abs(deltaY);
        const minSwipeDistance = 50;

        // Detectar swipe horizontal
        if (absDeltaX > minSwipeDistance && absDeltaX > absDeltaY) {
            if (deltaX > 0) {
                this.handleSwipeRight(e);
            } else {
                this.handleSwipeLeft(e);
            }
        }
        
        // Detectar swipe vertical
        if (absDeltaY > minSwipeDistance && absDeltaY > absDeltaX) {
            if (deltaY > 0) {
                this.handleSwipeDown(e);
            } else {
                this.handleSwipeUp(e);
            }
        }
    }

    /**
     * Manejar swipe a la derecha
     */
    handleSwipeRight(e) {
        // Abrir menú si se hace swipe desde el borde izquierdo
        if (this.touchStartX < 20 && this.currentLayout === 'mobile') {
            this.openMobileMenu();
        }
    }

    /**
     * Manejar swipe a la izquierda
     */
    handleSwipeLeft(e) {
        // Cerrar menú si está abierto
        if (document.body.classList.contains('mobile-menu-open')) {
            this.closeMobileMenu();
        }
    }

    /**
     * Optimizar formularios para móvil
     */
    optimizeFormInputs() {
        const inputs = document.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            this.optimizeInput(input);
        });
    }

    /**
     * Optimizar input individual
     */
    optimizeInput(input) {
        // Agregar tipo de teclado apropiado
        this.setMobileInputType(input);
        
        // Mejorar UX de focus
        this.enhanceInputFocus(input);
        
        // Agregar validación visual
        this.addInputValidation(input);
    }

    /**
     * Configurar tipo de teclado móvil
     */
    setMobileInputType(input) {
        const name = input.name || '';
        const type = input.type || '';
        
        if (name.includes('email') || type === 'email') {
            input.setAttribute('inputmode', 'email');
        } else if (name.includes('phone') || name.includes('tel')) {
            input.setAttribute('inputmode', 'tel');
        } else if (name.includes('number') || type === 'number') {
            input.setAttribute('inputmode', 'numeric');
        } else if (name.includes('url')) {
            input.setAttribute('inputmode', 'url');
        }
    }

    /**
     * Mejorar focus de inputs
     */
    enhanceInputFocus(input) {
        input.addEventListener('focus', () => {
            input.classList.add('mobile-focused');
            
            // Scroll hacia el input en móvil
            if (this.currentLayout === 'mobile') {
                setTimeout(() => {
                    input.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }, 300);
            }
        });

        input.addEventListener('blur', () => {
            input.classList.remove('mobile-focused');
        });
    }

    /**
     * Crear toolbar móvil flotante
     */
    createMobileToolbar() {
        if (this.currentLayout !== 'mobile') return;

        let toolbar = document.querySelector('.mobile-floating-toolbar');
        
        if (!toolbar) {
            toolbar = document.createElement('div');
            toolbar.className = 'mobile-floating-toolbar';
            toolbar.innerHTML = `
                <div class="toolbar-actions">
                    <button class="toolbar-btn" onclick="mobileOptimizer.quickAction('add')" title="Agregar">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="toolbar-btn" onclick="mobileOptimizer.quickAction('search')" title="Buscar">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="toolbar-btn" onclick="mobileOptimizer.quickAction('filter')" title="Filtrar">
                        <i class="fas fa-filter"></i>
                    </button>
                    <button class="toolbar-btn" onclick="mobileOptimizer.quickAction('menu')" title="Menú">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(toolbar);
        }
    }

    /**
     * Métodos públicos
     */
    toggleMobileMenu() {
        const isOpen = document.body.classList.contains('mobile-menu-open');
        
        if (isOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    }

    openMobileMenu() {
        document.body.classList.add('mobile-menu-open');
        const overlay = document.querySelector('.mobile-overlay');
        if (overlay) overlay.style.display = 'block';
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
    }

    closeMobileMenu() {
        document.body.classList.remove('mobile-menu-open');
        const overlay = document.querySelector('.mobile-overlay');
        if (overlay) overlay.style.display = 'none';
        
        // Restaurar scroll del body
        document.body.style.overflow = '';
        
        // Restaurar transform del sidebar
        const sidebar = document.querySelector('.admin-sidebar');
        if (sidebar) sidebar.style.transform = '';
    }

    quickAction(action) {
        switch(action) {
            case 'add':
                const addButton = document.querySelector('[onclick*="toggleProductForm"], .btn-primary');
                if (addButton) addButton.click();
                break;
            case 'search':
                const searchInput = document.querySelector('input[type="search"], input[name*="search"]');
                if (searchInput) searchInput.focus();
                break;
            case 'filter':
                const filterButton = document.querySelector('[onclick*="filter"], .filter-toggle');
                if (filterButton) filterButton.click();
                break;
            case 'menu':
                this.toggleMobileMenu();
                break;
        }
    }

    // Utilidades
    detectMobileDevice() {
        return /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    detectTabletDevice() {
        return /iPad|Android/i.test(navigator.userAgent) && window.innerWidth >= 768;
    }

    getOrientation() {
        return window.innerHeight > window.innerWidth ? 'portrait' : 'landscape';
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    handleOrientationChange() {
        this.orientation = this.getOrientation();
        console.log('📱 Orientation changed to:', this.orientation);
        
        // Reajustar layout
        setTimeout(() => {
            this.applyMobileLayout();
        }, 100);
    }

    handleViewportChange() {
        console.log('📱 Viewport changed:', `${window.innerWidth}x${window.innerHeight}`);
        this.applyMobileLayout();
    }
}

// CSS para optimización móvil
const mobileStyles = `
<style>
/* Layout móvil base */
.mobile-layout {
    font-size: 16px; /* Prevenir zoom en iOS */
}

.mobile-layout .admin-sidebar {
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100vh;
    background: var(--admin-bg-primary);
    z-index: 9999;
    transition: transform 0.3s ease;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.mobile-menu-open .admin-sidebar {
    transform: translateX(280px);
}

.mobile-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9998;
    display: none;
}

.mobile-menu-button {
    display: none;
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 10000;
    background: var(--admin-accent-blue);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px;
    cursor: pointer;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 44px;
    height: 44px;
}

.mobile-layout .mobile-menu-button {
    display: flex;
}

.hamburger-line {
    display: block;
    width: 20px;
    height: 2px;
    background: white;
    margin: 2px 0;
    transition: 0.3s;
}

.mobile-menu-open .hamburger-line:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}

.mobile-menu-open .hamburger-line:nth-child(2) {
    opacity: 0;
}

.mobile-menu-open .hamburger-line:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -6px);
}

/* Optimizaciones de toque */
.ripple-enabled {
    position: relative;
    overflow: hidden;
}

.ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.4);
    pointer-events: none;
    transform: scale(0);
    animation: ripple 0.6s ease-out;
}

@keyframes ripple {
    to {
        transform: scale(2);
        opacity: 0;
    }
}

.touch-target-enhanced {
    min-width: 44px !important;
    min-height: 44px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Tablas responsivas */
.table-mobile-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    position: relative;
}

.table-cards-container {
    display: none;
}

.mobile-layout .table-cards-container {
    display: block;
}

.table-card {
    background: var(--admin-bg-secondary);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid var(--admin-border-light);
}

.card-field {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--admin-border-light);
}

.card-field:last-child {
    border-bottom: none;
}

.field-label {
    font-weight: 600;
    color: var(--admin-text-secondary);
    font-size: 12px;
    text-transform: uppercase;
}

.field-value {
    color: var(--admin-text-primary);
    text-align: right;
}

/* Inputs móviles */
.mobile-layout input,
.mobile-layout textarea,
.mobile-layout select {
    font-size: 16px; /* Prevenir zoom */
    padding: 12px;
    border-radius: 8px;
}

.mobile-focused {
    border-color: var(--admin-accent-blue) !important;
    box-shadow: 0 0 0 3px rgba(9, 105, 218, 0.1) !important;
}

/* Toolbar flotante */
.mobile-floating-toolbar {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: var(--admin-accent-blue);
    border-radius: 25px;
    padding: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    z-index: 9997;
    display: none;
}

.mobile-layout .mobile-floating-toolbar {
    display: block;
}

.toolbar-actions {
    display: flex;
    gap: 5px;
}

.toolbar-btn {
    background: transparent;
    color: white;
    border: none;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.toolbar-btn:hover {
    background: rgba(255,255,255,0.1);
}

/* Ajustes de contenido móvil */
.mobile-layout .admin-content {
    margin-left: 0;
    padding: 60px 15px 15px;
}

.mobile-layout .page-header {
    padding: 15px;
    margin: -45px -15px 20px;
}

.mobile-layout .content-card {
    margin-bottom: 15px;
    border-radius: 12px;
}

/* Layout tablet */
.tablet-layout .admin-sidebar {
    width: 200px;
}

.tablet-layout .admin-content {
    margin-left: 200px;
    padding: 20px;
}

/* Mejoras de scroll */
.mobile-layout {
    scroll-behavior: smooth;
}

.mobile-layout * {
    -webkit-overflow-scrolling: touch;
}

/* Responsive media queries */
@media (max-width: 768px) {
    .mobile-layout .stats-grid,
    .mobile-layout .metrics-grid {
        grid-template-columns: 1fr !important;
        gap: 10px;
    }
    
    .mobile-layout .filters-grid {
        grid-template-columns: 1fr !important;
    }
    
    .mobile-layout .form-grid {
        grid-template-columns: 1fr !important;
    }
    
    .mobile-layout .action-buttons {
        flex-direction: column;
        gap: 5px;
    }
    
    .mobile-layout .btn {
        width: 100%;
        justify-content: center;
    }
}

/* Ajustes específicos para iOS */
@supports (-webkit-touch-callout: none) {
    .mobile-layout input {
        border-radius: 0;
    }
    
    .mobile-layout .mobile-menu-button {
        -webkit-tap-highlight-color: transparent;
    }
}

/* Ajustes para Android */
@media screen and (-webkit-device-pixel-ratio: 2) and (orientation: portrait) {
    .mobile-layout {
        zoom: 1;
    }
}
</style>
`;

// Inyectar estilos
document.head.insertAdjacentHTML('beforeend', mobileStyles);

// Inicializar optimizador móvil
const mobileOptimizer = new MobileAdminOptimizer();

// Exportar para uso global
window.mobileOptimizer = mobileOptimizer;