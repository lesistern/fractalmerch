/**
 * Analizador de Cuellos de Botella del Admin Panel
 * Identifica, monitorea y resuelve problemas de rendimiento críticos
 */

class PerformanceBottleneckAnalyzer {
    constructor() {
        this.performanceData = {
            pageLoadTimes: [],
            databaseQueries: [],
            memoryUsage: [],
            networkRequests: [],
            renderingMetrics: [],
            userInteractionLatency: []
        };
        
        this.bottleneckThresholds = {
            pageLoad: 3000, // 3 segundos
            databaseQuery: 1000, // 1 segundo
            memoryUsage: 100, // 100MB
            networkRequest: 2000, // 2 segundos
            firstPaint: 1500, // 1.5 segundos
            interactionLatency: 100 // 100ms
        };
        
        this.activeMitigation = new Map();
        this.performanceObserver = null;
        
        this.init();
    }

    /**
     * Inicializar análisis de rendimiento
     */
    init() {
        this.setupPerformanceObserver();
        this.monitorPageLoadPerformance();
        this.monitorDatabasePerformance();
        this.monitorMemoryUsage();
        this.monitorNetworkRequests();
        this.monitorUserInteractionLatency();
        this.setupRealTimeMonitoring();
        this.createPerformanceDashboard();
        this.enableAutomaticOptimizations();
    }

    /**
     * Configurar Performance Observer API
     */
    setupPerformanceObserver() {
        if ('PerformanceObserver' in window) {
            // Observer para métricas de navegación
            const navObserver = new PerformanceObserver((entryList) => {
                for (const entry of entryList.getEntries()) {
                    this.analyzeNavigationTiming(entry);
                }
            });

            // Observer para métricas de paint
            const paintObserver = new PerformanceObserver((entryList) => {
                for (const entry of entryList.getEntries()) {
                    this.analyzePaintTiming(entry);
                }
            });

            // Observer para métricas de recursos
            const resourceObserver = new PerformanceObserver((entryList) => {
                for (const entry of entryList.getEntries()) {
                    this.analyzeResourceTiming(entry);
                }
            });

            // Observer para Long Tasks
            const longTaskObserver = new PerformanceObserver((entryList) => {
                for (const entry of entryList.getEntries()) {
                    this.analyzeLongTask(entry);
                }
            });

            try {
                navObserver.observe({ entryTypes: ['navigation'] });
                paintObserver.observe({ entryTypes: ['paint'] });
                resourceObserver.observe({ entryTypes: ['resource'] });
                longTaskObserver.observe({ entryTypes: ['longtask'] });
                
                this.performanceObserver = {
                    nav: navObserver,
                    paint: paintObserver,
                    resource: resourceObserver,
                    longTask: longTaskObserver
                };
            } catch (error) {
                console.warn('Performance Observer not fully supported:', error);
            }
        }
    }

    /**
     * Monitorear rendimiento de carga de página
     */
    monitorPageLoadPerformance() {
        window.addEventListener('load', () => {
            // Obtener métricas de navegación
            const navEntries = performance.getEntriesByType('navigation');
            if (navEntries.length > 0) {
                const navTiming = navEntries[0];
                this.analyzeNavigationTiming(navTiming);
            }

            // Obtener métricas de paint
            const paintEntries = performance.getEntriesByType('paint');
            paintEntries.forEach(entry => this.analyzePaintTiming(entry));

            // Analizar Core Web Vitals
            this.measureCoreWebVitals();
        });
    }

    /**
     * Analizar timing de navegación
     */
    analyzeNavigationTiming(entry) {
        const metrics = {
            timestamp: Date.now(),
            page: window.location.pathname,
            dnsLookup: entry.domainLookupEnd - entry.domainLookupStart,
            tcpConnection: entry.connectEnd - entry.connectStart,
            serverResponse: entry.responseEnd - entry.requestStart,
            domParsing: entry.domContentLoadedEventEnd - entry.domContentLoadedEventStart,
            totalLoadTime: entry.loadEventEnd - entry.fetchStart,
            firstByte: entry.responseStart - entry.requestStart,
            domReady: entry.domContentLoadedEventEnd - entry.fetchStart
        };

        this.performanceData.pageLoadTimes.push(metrics);

        // Identificar bottlenecks específicos
        this.identifyLoadBottlenecks(metrics);
    }

    /**
     * Identificar cuellos de botella en la carga
     */
    identifyLoadBottlenecks(metrics) {
        const bottlenecks = [];

        if (metrics.totalLoadTime > this.bottleneckThresholds.pageLoad) {
            bottlenecks.push({
                type: 'slow_page_load',
                severity: 'high',
                value: metrics.totalLoadTime,
                threshold: this.bottleneckThresholds.pageLoad,
                impact: 'user_experience'
            });
        }

        if (metrics.serverResponse > 1000) {
            bottlenecks.push({
                type: 'slow_server_response',
                severity: 'high',
                value: metrics.serverResponse,
                threshold: 1000,
                impact: 'backend_performance'
            });
        }

        if (metrics.domParsing > 500) {
            bottlenecks.push({
                type: 'slow_dom_parsing',
                severity: 'medium',
                value: metrics.domParsing,
                threshold: 500,
                impact: 'frontend_performance'
            });
        }

        if (bottlenecks.length > 0) {
            this.reportBottlenecks('page_load', bottlenecks);
            this.triggerAutomaticMitigation(bottlenecks);
        }
    }

    /**
     * Monitorear rendimiento de base de datos (simulado)
     */
    monitorDatabasePerformance() {
        // Interceptar peticiones AJAX para analizar tiempo de respuesta
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            const startTime = performance.now();
            const url = args[0];
            
            try {
                const response = await originalFetch(...args);
                const endTime = performance.now();
                const duration = endTime - startTime;
                
                this.analyzeDatabaseQuery(url, duration, response.status);
                
                return response;
            } catch (error) {
                const endTime = performance.now();
                const duration = endTime - startTime;
                
                this.analyzeDatabaseQuery(url, duration, 'error');
                throw error;
            }
        };

        // Interceptar XMLHttpRequest también
        const originalXHROpen = XMLHttpRequest.prototype.open;
        const originalXHRSend = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function(method, url, ...args) {
            this._startTime = performance.now();
            this._url = url;
            return originalXHROpen.call(this, method, url, ...args);
        };

        XMLHttpRequest.prototype.send = function(...args) {
            this.addEventListener('loadend', () => {
                if (this._startTime && this._url) {
                    const duration = performance.now() - this._startTime;
                    this.analyzer?.analyzeDatabaseQuery(this._url, duration, this.status);
                }
            });
            
            this.analyzer = this;
            return originalXHRSend.call(this, ...args);
        };
    }

    /**
     * Analizar consulta de base de datos
     */
    analyzeDatabaseQuery(url, duration, status) {
        const queryData = {
            timestamp: Date.now(),
            url: url,
            duration: duration,
            status: status,
            type: this.inferQueryType(url)
        };

        this.performanceData.databaseQueries.push(queryData);

        // Identificar consultas lentas
        if (duration > this.bottleneckThresholds.databaseQuery) {
            this.reportBottlenecks('database_query', [{
                type: 'slow_database_query',
                severity: duration > 3000 ? 'critical' : 'high',
                value: duration,
                threshold: this.bottleneckThresholds.databaseQuery,
                url: url,
                impact: 'backend_performance'
            }]);
        }

        // Mantener solo las últimas 100 consultas
        if (this.performanceData.databaseQueries.length > 100) {
            this.performanceData.databaseQueries = this.performanceData.databaseQueries.slice(-100);
        }
    }

    /**
     * Inferir tipo de consulta desde URL
     */
    inferQueryType(url) {
        if (url.includes('manage-products')) return 'products_query';
        if (url.includes('manage-users')) return 'users_query';
        if (url.includes('statistics')) return 'analytics_query';
        if (url.includes('dashboard')) return 'dashboard_query';
        return 'general_query';
    }

    /**
     * Monitorear uso de memoria
     */
    monitorMemoryUsage() {
        if ('memory' in performance) {
            setInterval(() => {
                const memInfo = performance.memory;
                const memoryData = {
                    timestamp: Date.now(),
                    usedJSHeapSize: memInfo.usedJSHeapSize / 1024 / 1024, // MB
                    totalJSHeapSize: memInfo.totalJSHeapSize / 1024 / 1024, // MB
                    jsHeapSizeLimit: memInfo.jsHeapSizeLimit / 1024 / 1024 // MB
                };

                this.performanceData.memoryUsage.push(memoryData);

                // Verificar uso excesivo de memoria
                if (memoryData.usedJSHeapSize > this.bottleneckThresholds.memoryUsage) {
                    this.reportBottlenecks('memory_usage', [{
                        type: 'high_memory_usage',
                        severity: memoryData.usedJSHeapSize > 200 ? 'critical' : 'high',
                        value: memoryData.usedJSHeapSize,
                        threshold: this.bottleneckThresholds.memoryUsage,
                        impact: 'browser_performance'
                    }]);
                }

                // Mantener solo las últimas 50 mediciones
                if (this.performanceData.memoryUsage.length > 50) {
                    this.performanceData.memoryUsage = this.performanceData.memoryUsage.slice(-50);
                }
            }, 5000); // Cada 5 segundos
        }
    }

    /**
     * Monitorear latencia de interacciones del usuario
     */
    monitorUserInteractionLatency() {
        let interactionStart = 0;

        // Monitorear clicks
        document.addEventListener('click', () => {
            interactionStart = performance.now();
        }, true);

        // Monitorear cuando se completa la interacción
        document.addEventListener('click', () => {
            if (interactionStart > 0) {
                const latency = performance.now() - interactionStart;
                
                this.performanceData.userInteractionLatency.push({
                    timestamp: Date.now(),
                    type: 'click',
                    latency: latency
                });

                if (latency > this.bottleneckThresholds.interactionLatency) {
                    this.reportBottlenecks('interaction_latency', [{
                        type: 'slow_interaction',
                        severity: latency > 200 ? 'high' : 'medium',
                        value: latency,
                        threshold: this.bottleneckThresholds.interactionLatency,
                        impact: 'user_experience'
                    }]);
                }

                interactionStart = 0;
            }
        }, false);

        // Monitorear input lag
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', (e) => {
                const inputStart = performance.now();
                
                // Usar requestAnimationFrame para medir hasta el siguiente frame
                requestAnimationFrame(() => {
                    const inputLatency = performance.now() - inputStart;
                    
                    if (inputLatency > 50) { // 50ms threshold para inputs
                        this.reportBottlenecks('input_lag', [{
                            type: 'input_lag',
                            severity: inputLatency > 100 ? 'high' : 'medium',
                            value: inputLatency,
                            threshold: 50,
                            impact: 'user_experience'
                        }]);
                    }
                });
            });
        });
    }

    /**
     * Configurar monitoreo en tiempo real
     */
    setupRealTimeMonitoring() {
        // Monitor de FPS
        let lastFrameTime = performance.now();
        let frameCount = 0;
        let fpsData = [];

        function measureFPS() {
            const now = performance.now();
            frameCount++;
            
            if (now - lastFrameTime >= 1000) {
                const fps = Math.round((frameCount * 1000) / (now - lastFrameTime));
                fpsData.push({ timestamp: Date.now(), fps: fps });
                
                // Alertar sobre FPS bajo
                if (fps < 30) {
                    this.reportBottlenecks?.('low_fps', [{
                        type: 'low_fps',
                        severity: fps < 15 ? 'critical' : 'high',
                        value: fps,
                        threshold: 30,
                        impact: 'user_experience'
                    }]);
                }
                
                frameCount = 0;
                lastFrameTime = now;
                
                // Mantener solo los últimos 60 segundos
                if (fpsData.length > 60) {
                    fpsData = fpsData.slice(-60);
                }
            }
            
            requestAnimationFrame(measureFPS.bind(this));
        }
        
        measureFPS.call(this);

        // Monitor de CPU usage (aproximado)
        this.monitorCPUUsage();
    }

    /**
     * Monitorear uso de CPU (aproximado)
     */
    monitorCPUUsage() {
        let lastTime = performance.now();
        let lastUsage = 0;

        setInterval(() => {
            const now = performance.now();
            const timeDiff = now - lastTime;
            
            // Usar tiempo de ejecución de tareas para estimar uso de CPU
            const startMeasure = performance.now();
            for (let i = 0; i < 100000; i++) {
                Math.random();
            }
            const endMeasure = performance.now();
            const executionTime = endMeasure - startMeasure;
            
            // Calcular "CPU usage" basado en tiempo de ejecución
            const cpuUsage = Math.min(100, (executionTime / 10) * 100);
            
            if (cpuUsage > 80) {
                this.reportBottlenecks('high_cpu', [{
                    type: 'high_cpu_usage',
                    severity: cpuUsage > 90 ? 'critical' : 'high',
                    value: cpuUsage,
                    threshold: 80,
                    impact: 'system_performance'
                }]);
            }
            
            lastTime = now;
            lastUsage = cpuUsage;
        }, 2000);
    }

    /**
     * Crear dashboard de rendimiento
     */
    createPerformanceDashboard() {
        const dashboard = this.createDashboardContainer();
        this.renderPerformanceMetrics(dashboard);
        this.renderBottlenecksList(dashboard);
        this.renderOptimizationSuggestions(dashboard);
        this.setupDashboardControls(dashboard);
        
        // Actualizar dashboard cada 5 segundos
        setInterval(() => {
            this.updateDashboard(dashboard);
        }, 5000);
    }

    /**
     * Crear contenedor del dashboard
     */
    createDashboardContainer() {
        let dashboard = document.querySelector('.performance-dashboard');
        
        if (!dashboard) {
            dashboard = document.createElement('div');
            dashboard.className = 'performance-dashboard';
            dashboard.innerHTML = `
                <div class="dashboard-header">
                    <h3><i class="fas fa-tachometer-alt"></i> Performance Monitor</h3>
                    <div class="dashboard-controls">
                        <button onclick="perfAnalyzer.exportReport()" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button onclick="perfAnalyzer.runOptimizations()" class="btn btn-sm btn-success">
                            <i class="fas fa-rocket"></i> Optimizar
                        </button>
                        <button onclick="perfAnalyzer.toggleDashboard()" class="dashboard-toggle">
                            <i class="fas fa-chart-line"></i>
                        </button>
                    </div>
                </div>
                <div class="dashboard-content" style="display: none;">
                    <div class="metrics-section">
                        <h4>Métricas en Tiempo Real</h4>
                        <div class="metrics-grid" id="metrics-grid"></div>
                    </div>
                    <div class="bottlenecks-section">
                        <h4>Cuellos de Botella Detectados</h4>
                        <div class="bottlenecks-list" id="bottlenecks-list"></div>
                    </div>
                    <div class="optimizations-section">
                        <h4>Optimizaciones Sugeridas</h4>
                        <div class="optimizations-list" id="optimizations-list"></div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dashboard);
        }
        
        return dashboard;
    }

    /**
     * Reportar cuellos de botella detectados
     */
    reportBottlenecks(category, bottlenecks) {
        bottlenecks.forEach(bottleneck => {
            console.warn(`Performance Bottleneck [${category}]:`, bottleneck);
            
            // Agregar a cola de notificaciones si está disponible
            if (window.realtimeNotifications) {
                window.realtimeNotifications.notify('system_error', 
                    `Cuello de botella detectado: ${bottleneck.type}`, {
                    title: 'Performance Alert',
                    importance: bottleneck.severity
                });
            }
            
            // Registrar en analytics
            this.logBottleneckToAnalytics(category, bottleneck);
        });
    }

    /**
     * Activar mitigaciones automáticas
     */
    triggerAutomaticMitigation(bottlenecks) {
        bottlenecks.forEach(bottleneck => {
            switch(bottleneck.type) {
                case 'slow_page_load':
                    this.enablePageLoadOptimizations();
                    break;
                case 'high_memory_usage':
                    this.triggerGarbageCollection();
                    break;
                case 'slow_database_query':
                    this.enableQueryOptimizations();
                    break;
                case 'low_fps':
                    this.enableRenderingOptimizations();
                    break;
            }
        });
    }

    /**
     * Optimizaciones automáticas de carga de página
     */
    enablePageLoadOptimizations() {
        if (this.activeMitigation.has('page_load')) return;
        
        this.activeMitigation.set('page_load', true);
        
        // Lazy loading de imágenes
        this.enableLazyLoading();
        
        // Diferir scripts no críticos
        this.deferNonCriticalScripts();
        
        // Preload de recursos críticos
        this.preloadCriticalResources();
        
        console.log('✅ Page load optimizations enabled');
    }

    /**
     * Utilidades y métodos públicos
     */
    toggleDashboard() {
        const content = document.querySelector('.dashboard-content');
        const isVisible = content.style.display !== 'none';
        content.style.display = isVisible ? 'none' : 'block';
    }

    exportReport() {
        const report = {
            timestamp: new Date().toISOString(),
            performanceData: this.performanceData,
            bottleneckThresholds: this.bottleneckThresholds,
            activeMitigations: Array.from(this.activeMitigation.keys()),
            summary: this.generatePerformanceSummary()
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `performance_report_${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    runOptimizations() {
        console.log('🚀 Running performance optimizations...');
        
        this.enablePageLoadOptimizations();
        this.triggerGarbageCollection();
        this.enableQueryOptimizations();
        this.enableRenderingOptimizations();
        
        if (window.realtimeNotifications) {
            window.realtimeNotifications.notify('backup_completed', 
                'Optimizaciones de rendimiento aplicadas', {
                title: 'Performance Boost',
                importance: 'low'
            });
        }
    }

    generatePerformanceSummary() {
        const summary = {
            averagePageLoad: this.calculateAverageMetric('pageLoadTimes', 'totalLoadTime'),
            averageQueryTime: this.calculateAverageMetric('databaseQueries', 'duration'),
            currentMemoryUsage: this.getCurrentMemoryUsage(),
            criticalBottlenecks: this.getCriticalBottlenecks(),
            optimizationsActive: this.activeMitigation.size
        };
        
        return summary;
    }

    calculateAverageMetric(dataSet, field) {
        const data = this.performanceData[dataSet];
        if (data.length === 0) return 0;
        
        const sum = data.reduce((acc, item) => acc + (item[field] || 0), 0);
        return Math.round(sum / data.length);
    }

    getCurrentMemoryUsage() {
        const memData = this.performanceData.memoryUsage;
        return memData.length > 0 ? memData[memData.length - 1].usedJSHeapSize : 0;
    }

    getCriticalBottlenecks() {
        // Esta función sería implementada para retornar bottlenecks críticos actuales
        return [];
    }
}

// CSS para el dashboard de rendimiento
const performanceStyles = `
<style>
.performance-dashboard {
    position: fixed;
    bottom: 20px;
    left: 20px;
    width: 450px;
    background: var(--admin-bg-primary);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-lg);
    box-shadow: var(--admin-shadow-xl);
    z-index: 9997;
    font-size: 14px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid var(--admin-border-light);
    background: var(--admin-bg-secondary);
}

.dashboard-header h3 {
    margin: 0;
    font-size: 16px;
    color: var(--admin-text-primary);
}

.dashboard-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}

.dashboard-toggle {
    background: var(--admin-accent-blue);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 6px 10px;
    cursor: pointer;
}

.dashboard-content {
    max-height: 500px;
    overflow-y: auto;
    padding: 15px;
}

.metrics-section, .bottlenecks-section, .optimizations-section {
    margin-bottom: 20px;
}

.metrics-section h4, .bottlenecks-section h4, .optimizations-section h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: var(--admin-text-primary);
    border-bottom: 1px solid var(--admin-border-light);
    padding-bottom: 5px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.metric-card {
    background: var(--admin-bg-tertiary);
    padding: 10px;
    border-radius: 6px;
    border-left: 3px solid var(--admin-accent-blue);
}

.metric-card.warning {
    border-left-color: #ffc107;
}

.metric-card.danger {
    border-left-color: #dc3545;
}

.metric-value {
    font-size: 18px;
    font-weight: 600;
    color: var(--admin-text-primary);
}

.metric-label {
    font-size: 11px;
    color: var(--admin-text-secondary);
    text-transform: uppercase;
}

.bottleneck-item, .optimization-item {
    background: var(--admin-bg-tertiary);
    padding: 8px 12px;
    border-radius: 4px;
    margin-bottom: 5px;
    border-left: 3px solid #dc3545;
}

.optimization-item {
    border-left-color: #28a745;
}

.bottleneck-severity, .optimization-status {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    color: white;
    text-transform: uppercase;
}

.severity-critical { background: #dc3545; }
.severity-high { background: #fd7e14; }
.severity-medium { background: #ffc107; color: #000; }
.severity-low { background: #20c997; }

@media (max-width: 768px) {
    .performance-dashboard {
        width: calc(100vw - 40px);
        left: 20px;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
}
</style>
`;

// Inyectar estilos
document.head.insertAdjacentHTML('beforeend', performanceStyles);

// Inicializar analizador de rendimiento
const perfAnalyzer = new PerformanceBottleneckAnalyzer();

// Exportar para uso global
window.perfAnalyzer = perfAnalyzer;