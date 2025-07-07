/**
 * Sistema de Análisis de Flujo de Usuarios Admin
 * Rastrea, analiza y optimiza las rutas críticas del panel administrativo
 */

class AdminUserJourney {
    constructor() {
        this.currentPage = this.getCurrentPage();
        this.sessionId = this.generateSessionId();
        this.journeyData = [];
        this.criticalPaths = [];
        this.analytics = {
            pageViews: {},
            timeSpent: {},
            clickPaths: [],
            errors: [],
            completedTasks: []
        };
        
        this.init();
    }

    /**
     * Inicializar sistema de análisis
     */
    init() {
        this.startJourneyTracking();
        this.setupCriticalPathAnalysis();
        this.trackPagePerformance();
        this.setupHeatmapTracking();
        this.analyzeWorkflowEfficiency();
        this.setupFrictionDetection();
        this.createJourneyVisualization();
    }

    /**
     * Iniciar tracking del journey del usuario
     */
    startJourneyTracking() {
        // Registrar inicio de sesión
        this.trackEvent('session_start', {
            page: this.currentPage,
            timestamp: Date.now(),
            userAgent: navigator.userAgent,
            screenResolution: `${screen.width}x${screen.height}`,
            viewport: `${window.innerWidth}x${window.innerHeight}`
        });

        // Track navegación entre páginas
        this.trackPageNavigation();
        
        // Track interacciones de usuario
        this.trackUserInteractions();
        
        // Track tiempo en página
        this.trackTimeOnPage();
        
        // Track eventos de formularios
        this.trackFormInteractions();
    }

    /**
     * Rastrear navegación entre páginas
     */
    trackPageNavigation() {
        // Track clicks en navegación
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href) {
                this.trackEvent('navigation_click', {
                    from: this.currentPage,
                    to: link.href,
                    linkText: link.textContent.trim(),
                    timestamp: Date.now()
                });
            }
        });

        // Track navegación de sidebar
        const sidebarLinks = document.querySelectorAll('.admin-sidebar a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                this.trackEvent('sidebar_navigation', {
                    page: link.getAttribute('href'),
                    section: link.textContent.trim(),
                    timestamp: Date.now()
                });
            });
        });

        // Track breadcrumb navigation
        document.addEventListener('click', (e) => {
            if (e.target.closest('.breadcrumb-item')) {
                this.trackEvent('breadcrumb_navigation', {
                    level: e.target.closest('.breadcrumb-item').textContent.trim(),
                    timestamp: Date.now()
                });
            }
        });
    }

    /**
     * Configurar análisis de rutas críticas
     */
    setupCriticalPathAnalysis() {
        this.criticalPaths = [
            {
                name: 'Gestión de Productos',
                path: ['dashboard.php', 'manage-products.php', 'product-edit'],
                expectedTime: 180000, // 3 minutos
                priority: 'high'
            },
            {
                name: 'Análisis de Ventas',
                path: ['dashboard.php', 'statistics.php', 'stats-products.php'],
                expectedTime: 120000, // 2 minutos
                priority: 'high'
            },
            {
                name: 'Gestión de Usuarios',
                path: ['dashboard.php', 'manage-users.php', 'user-action'],
                expectedTime: 90000, // 1.5 minutos
                priority: 'medium'
            },
            {
                name: 'Configuración Sistema',
                path: ['dashboard.php', 'settings.php', 'config-save'],
                expectedTime: 240000, // 4 minutos
                priority: 'medium'
            }
        ];

        // Analizar cada ruta crítica
        this.criticalPaths.forEach(path => {
            this.analyzeCriticalPath(path);
        });
    }

    /**
     * Analizar ruta crítica específica
     */
    analyzeCriticalPath(pathConfig) {
        const pathAnalysis = {
            name: pathConfig.name,
            completionRate: this.calculateCompletionRate(pathConfig.path),
            averageTime: this.calculateAverageTime(pathConfig.path),
            dropoffPoints: this.identifyDropoffPoints(pathConfig.path),
            bottlenecks: this.identifyBottlenecks(pathConfig.path),
            efficiency: 0
        };

        // Calcular eficiencia
        pathAnalysis.efficiency = (pathAnalysis.completionRate / 100) * 
                                 (pathConfig.expectedTime / pathAnalysis.averageTime);

        this.analytics.criticalPaths = this.analytics.criticalPaths || [];
        this.analytics.criticalPaths.push(pathAnalysis);

        // Alertar sobre rutas problemáticas
        if (pathAnalysis.efficiency < 0.7) {
            this.flagInefficient path(pathConfig, pathAnalysis);
        }
    }

    /**
     * Rastrear interacciones del usuario
     */
    trackUserInteractions() {
        // Track clicks en botones principales
        document.addEventListener('click', (e) => {
            const button = e.target.closest('button, .btn');
            if (button) {
                this.trackEvent('button_click', {
                    buttonText: button.textContent.trim(),
                    buttonClass: button.className,
                    page: this.currentPage,
                    timestamp: Date.now()
                });
            }
        });

        // Track uso de filtros y búsquedas
        const searchInputs = document.querySelectorAll('input[type="search"], input[name*="search"]');
        searchInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                this.trackEvent('search_usage', {
                    query: e.target.value,
                    page: this.currentPage,
                    timestamp: Date.now()
                });
            });
        });

        // Track uso de shortcuts de teclado
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                this.trackEvent('keyboard_shortcut', {
                    key: e.key,
                    combo: this.getKeyCombo(e),
                    page: this.currentPage,
                    timestamp: Date.now()
                });
            }
        });

        // Track uso de modales
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    if (mutation.attributeName === 'style' || mutation.attributeName === 'class') {
                        const isVisible = modal.style.display !== 'none' && !modal.classList.contains('hidden');
                        this.trackEvent('modal_interaction', {
                            modalId: modal.id,
                            action: isVisible ? 'open' : 'close',
                            page: this.currentPage,
                            timestamp: Date.now()
                        });
                    }
                });
            });
            observer.observe(modal, { attributes: true });
        });
    }

    /**
     * Rastrear rendimiento de páginas
     */
    trackPagePerformance() {
        // Timing de carga de página
        window.addEventListener('load', () => {
            const perfData = performance.getEntriesByType('navigation')[0];
            this.trackEvent('page_performance', {
                page: this.currentPage,
                loadTime: perfData.loadEventEnd - perfData.fetchStart,
                domContentLoaded: perfData.domContentLoadedEventEnd - perfData.fetchStart,
                firstPaint: this.getFirstPaint(),
                timestamp: Date.now()
            });
        });

        // Tracking de errores JavaScript
        window.addEventListener('error', (e) => {
            this.trackEvent('javascript_error', {
                error: e.error?.message || e.message,
                filename: e.filename,
                lineno: e.lineno,
                colno: e.colno,
                page: this.currentPage,
                timestamp: Date.now()
            });
        });

        // Tracking de errores de recursos
        window.addEventListener('error', (e) => {
            if (e.target !== window) {
                this.trackEvent('resource_error', {
                    resource: e.target.src || e.target.href,
                    type: e.target.tagName,
                    page: this.currentPage,
                    timestamp: Date.now()
                });
            }
        }, true);
    }

    /**
     * Configurar tracking de heatmap
     */
    setupHeatmapTracking() {
        let clickHeatmap = [];
        let scrollHeatmap = [];

        // Track clicks para heatmap
        document.addEventListener('click', (e) => {
            clickHeatmap.push({
                x: e.clientX,
                y: e.clientY,
                element: e.target.tagName,
                class: e.target.className,
                timestamp: Date.now()
            });
        });

        // Track scroll behavior
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                scrollHeatmap.push({
                    scrollY: window.scrollY,
                    viewportHeight: window.innerHeight,
                    documentHeight: document.documentElement.scrollHeight,
                    timestamp: Date.now()
                });
            }, 100);
        });

        // Guardar datos de heatmap cada 30 segundos
        setInterval(() => {
            if (clickHeatmap.length > 0 || scrollHeatmap.length > 0) {
                this.trackEvent('heatmap_data', {
                    page: this.currentPage,
                    clicks: clickHeatmap,
                    scrolls: scrollHeatmap,
                    timestamp: Date.now()
                });
                clickHeatmap = [];
                scrollHeatmap = [];
            }
        }, 30000);
    }

    /**
     * Analizar eficiencia de workflows
     */
    analyzeWorkflowEfficiency() {
        const workflows = {
            'crear_producto': {
                steps: ['click_nuevo_producto', 'fill_form', 'upload_image', 'save_product'],
                expectedTime: 300000, // 5 minutos
                currentEfficiency: 0
            },
            'gestionar_usuarios': {
                steps: ['navigate_users', 'search_user', 'edit_user', 'save_changes'],
                expectedTime: 180000, // 3 minutos
                currentEfficiency: 0
            },
            'analizar_ventas': {
                steps: ['open_stats', 'select_period', 'analyze_data', 'export_report'],
                expectedTime: 240000, // 4 minutos
                currentEfficiency: 0
            }
        };

        // Analizar cada workflow
        Object.keys(workflows).forEach(workflowName => {
            const workflow = workflows[workflowName];
            const analysis = this.analyzeWorkflow(workflow);
            
            this.trackEvent('workflow_analysis', {
                workflow: workflowName,
                efficiency: analysis.efficiency,
                bottlenecks: analysis.bottlenecks,
                suggestions: analysis.suggestions,
                timestamp: Date.now()
            });
        });
    }

    /**
     * Analizar workflow específico
     */
    analyzeWorkflow(workflow) {
        const journeyData = this.getJourneyData();
        const workflowSessions = journeyData.filter(session => 
            this.containsWorkflowSteps(session, workflow.steps)
        );

        const analysis = {
            efficiency: 0,
            bottlenecks: [],
            suggestions: [],
            averageTime: 0,
            completionRate: 0
        };

        if (workflowSessions.length > 0) {
            // Calcular tiempo promedio
            const totalTime = workflowSessions.reduce((sum, session) => 
                sum + this.calculateSessionTime(session), 0
            );
            analysis.averageTime = totalTime / workflowSessions.length;

            // Calcular eficiencia
            analysis.efficiency = workflow.expectedTime / analysis.averageTime;

            // Identificar bottlenecks
            analysis.bottlenecks = this.identifyWorkflowBottlenecks(workflowSessions, workflow);

            // Generar sugerencias
            analysis.suggestions = this.generateWorkflowSuggestions(analysis);
        }

        return analysis;
    }

    /**
     * Configurar detección de fricción
     */
    setupFrictionDetection() {
        const frictionIndicators = {
            // Múltiples clicks en el mismo elemento
            rapidClicks: { threshold: 3, timeWindow: 2000 },
            
            // Tiempo excesivo en formularios
            formStuggle: { threshold: 300000 }, // 5 minutos
            
            // Navegación errática
            backAndForth: { threshold: 3 },
            
            // Abandono de tareas
            taskAbandonment: { threshold: 180000 } // 3 minutos sin actividad
        };

        this.detectRapidClicks(frictionIndicators.rapidClicks);
        this.detectFormStruggle(frictionIndicators.formStuggle);
        this.detectErraticNavigation(frictionIndicators.backAndForth);
        this.detectTaskAbandonment(frictionIndicators.taskAbandonment);
    }

    /**
     * Detectar clicks rápidos repetitivos
     */
    detectRapidClicks(config) {
        let clickHistory = [];

        document.addEventListener('click', (e) => {
            const now = Date.now();
            const element = e.target;
            
            // Limpiar historial antiguo
            clickHistory = clickHistory.filter(click => 
                now - click.timestamp < config.timeWindow
            );

            // Agregar click actual
            clickHistory.push({
                element: element,
                timestamp: now
            });

            // Detectar clicks repetitivos en el mismo elemento
            const sameElementClicks = clickHistory.filter(click => 
                click.element === element
            );

            if (sameElementClicks.length >= config.threshold) {
                this.trackFriction('rapid_clicks', {
                    element: element.tagName + (element.className ? '.' + element.className : ''),
                    clickCount: sameElementClicks.length,
                    timespan: config.timeWindow,
                    page: this.currentPage,
                    timestamp: now
                });
            }
        });
    }

    /**
     * Detectar dificultades con formularios
     */
    detectFormStruggle(config) {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            let formStartTime = null;
            let fieldFocusTime = {};

            form.addEventListener('focusin', (e) => {
                if (!formStartTime) formStartTime = Date.now();
                fieldFocusTime[e.target.name] = Date.now();
            });

            form.addEventListener('focusout', (e) => {
                if (fieldFocusTime[e.target.name]) {
                    const timeSpent = Date.now() - fieldFocusTime[e.target.name];
                    if (timeSpent > config.threshold) {
                        this.trackFriction('form_struggle', {
                            form: form.className,
                            field: e.target.name,
                            timeSpent: timeSpent,
                            page: this.currentPage,
                            timestamp: Date.now()
                        });
                    }
                }
            });

            form.addEventListener('submit', () => {
                if (formStartTime) {
                    const totalTime = Date.now() - formStartTime;
                    this.trackEvent('form_completion', {
                        form: form.className,
                        totalTime: totalTime,
                        page: this.currentPage,
                        timestamp: Date.now()
                    });
                }
            });
        });
    }

    /**
     * Crear visualización del journey
     */
    createJourneyVisualization() {
        const visualizationContainer = this.createVisualizationContainer();
        this.renderJourneyFlow(visualizationContainer);
        this.renderMetricsDashboard(visualizationContainer);
        this.renderFrictionHeatmap(visualizationContainer);
    }

    /**
     * Crear contenedor de visualización
     */
    createVisualizationContainer() {
        let container = document.querySelector('.admin-journey-analytics');
        
        if (!container) {
            container = document.createElement('div');
            container.className = 'admin-journey-analytics';
            container.innerHTML = `
                <div class="journey-analytics-header">
                    <h3><i class="fas fa-route"></i> Analytics del Journey Admin</h3>
                    <div class="analytics-controls">
                        <button onclick="adminJourney.exportAnalytics()" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button onclick="adminJourney.resetAnalytics()" class="btn btn-sm btn-outline">
                            <i class="fas fa-refresh"></i> Reset
                        </button>
                        <button onclick="adminJourney.toggleVisualization()" class="analytics-toggle">
                            <i class="fas fa-chart-line"></i>
                        </button>
                    </div>
                </div>
                <div class="journey-analytics-content" style="display: none;">
                    <div class="analytics-tabs">
                        <button class="tab-btn active" data-tab="journey">Journey Flow</button>
                        <button class="tab-btn" data-tab="metrics">Métricas</button>
                        <button class="tab-btn" data-tab="friction">Puntos de Fricción</button>
                        <button class="tab-btn" data-tab="heatmap">Heatmap</button>
                    </div>
                    <div class="analytics-panels">
                        <div class="analytics-panel active" id="journey-panel"></div>
                        <div class="analytics-panel" id="metrics-panel"></div>
                        <div class="analytics-panel" id="friction-panel"></div>
                        <div class="analytics-panel" id="heatmap-panel"></div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(container);
        }
        
        return container;
    }

    /**
     * Renderizar flujo del journey
     */
    renderJourneyFlow(container) {
        const journeyPanel = container.querySelector('#journey-panel');
        const journeyData = this.getJourneyData();
        
        const journeyHTML = `
            <div class="journey-flow">
                <h4>Flujo de Navegación Actual</h4>
                <div class="journey-timeline">
                    ${journeyData.map(event => `
                        <div class="journey-event ${event.type}">
                            <div class="event-time">${this.formatTime(event.timestamp)}</div>
                            <div class="event-description">${this.formatEventDescription(event)}</div>
                            <div class="event-duration">${event.duration || ''}</div>
                        </div>
                    `).join('')}
                </div>
                
                <div class="critical-paths">
                    <h4>Análisis de Rutas Críticas</h4>
                    ${(this.analytics.criticalPaths || []).map(path => `
                        <div class="critical-path ${path.efficiency < 0.7 ? 'inefficient' : 'efficient'}">
                            <div class="path-header">
                                <span class="path-name">${path.name}</span>
                                <span class="path-efficiency">${Math.round(path.efficiency * 100)}%</span>
                            </div>
                            <div class="path-details">
                                <span>Tiempo promedio: ${Math.round(path.averageTime / 1000)}s</span>
                                <span>Tasa completación: ${path.completionRate}%</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        journeyPanel.innerHTML = journeyHTML;
    }

    /**
     * Utilidades
     */
    trackEvent(eventType, data) {
        const event = {
            type: eventType,
            sessionId: this.sessionId,
            ...data
        };
        
        this.journeyData.push(event);
        
        // Mantener solo los últimos 100 eventos
        if (this.journeyData.length > 100) {
            this.journeyData = this.journeyData.slice(-100);
        }
        
        // Enviar a analytics si está configurado
        this.sendToAnalytics(event);
    }

    trackFriction(frictionType, data) {
        this.trackEvent('friction_detected', {
            frictionType: frictionType,
            ...data
        });
        
        // Mostrar alerta en tiempo real
        this.showFrictionAlert(frictionType, data);
    }

    generateSessionId() {
        return 'admin_session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    getCurrentPage() {
        return window.location.pathname.split('/').pop().replace('.php', '') || 'dashboard';
    }

    getJourneyData() {
        return this.journeyData;
    }

    formatTime(timestamp) {
        return new Date(timestamp).toLocaleTimeString();
    }

    formatEventDescription(event) {
        switch(event.type) {
            case 'navigation_click':
                return `Navegó a ${event.to}`;
            case 'button_click':
                return `Clickeó "${event.buttonText}"`;
            case 'search_usage':
                return `Buscó "${event.query}"`;
            default:
                return event.type.replace('_', ' ');
        }
    }

    toggleVisualization() {
        const content = document.querySelector('.journey-analytics-content');
        const isVisible = content.style.display !== 'none';
        content.style.display = isVisible ? 'none' : 'block';
    }

    exportAnalytics() {
        const data = {
            journey: this.journeyData,
            analytics: this.analytics,
            session: this.sessionId,
            exported: new Date().toISOString()
        };
        
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `admin_journey_${this.sessionId}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    resetAnalytics() {
        this.journeyData = [];
        this.analytics = {
            pageViews: {},
            timeSpent: {},
            clickPaths: [],
            errors: [],
            completedTasks: []
        };
        
        // Reinicializar visualización
        this.createJourneyVisualization();
    }
}

// CSS para el análisis de journey
const journeyStyles = `
<style>
.admin-journey-analytics {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 400px;
    background: var(--admin-bg-primary);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-lg);
    box-shadow: var(--admin-shadow-xl);
    z-index: 9998;
    font-size: 14px;
}

.journey-analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid var(--admin-border-light);
    background: var(--admin-bg-secondary);
}

.journey-analytics-header h3 {
    margin: 0;
    font-size: 16px;
    color: var(--admin-text-primary);
}

.analytics-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}

.analytics-toggle {
    background: var(--admin-accent-blue);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 6px 10px;
    cursor: pointer;
}

.journey-analytics-content {
    max-height: 600px;
    overflow-y: auto;
}

.analytics-tabs {
    display: flex;
    border-bottom: 1px solid var(--admin-border-light);
}

.tab-btn {
    flex: 1;
    padding: 10px;
    border: none;
    background: var(--admin-bg-tertiary);
    color: var(--admin-text-secondary);
    cursor: pointer;
    font-size: 12px;
}

.tab-btn.active {
    background: var(--admin-bg-primary);
    color: var(--admin-text-primary);
    border-bottom: 2px solid var(--admin-accent-blue);
}

.analytics-panels {
    padding: 15px;
}

.analytics-panel {
    display: none;
}

.analytics-panel.active {
    display: block;
}

.journey-timeline {
    max-height: 300px;
    overflow-y: auto;
}

.journey-event {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid var(--admin-border-light);
    font-size: 12px;
}

.event-time {
    color: var(--admin-text-muted);
    min-width: 60px;
}

.event-description {
    flex: 1;
    color: var(--admin-text-primary);
}

.critical-path {
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 6px;
    border-left: 4px solid var(--admin-accent-blue);
}

.critical-path.inefficient {
    border-left-color: #dc3545;
    background: rgba(220, 53, 69, 0.1);
}

.critical-path.efficient {
    border-left-color: #28a745;
    background: rgba(40, 167, 69, 0.1);
}

.path-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
}

.path-details {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: var(--admin-text-secondary);
    margin-top: 5px;
}

@media (max-width: 768px) {
    .admin-journey-analytics {
        width: calc(100vw - 40px);
        right: 20px;
    }
}
</style>
`;

// Inyectar estilos
document.head.insertAdjacentHTML('beforeend', journeyStyles);

// Inicializar análisis de journey
const adminJourney = new AdminUserJourney();

// Exportar para uso global
window.adminJourney = adminJourney;