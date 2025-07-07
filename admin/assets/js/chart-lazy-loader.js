/**
 * Chart.js Lazy Loader - Performance Optimization
 * Carga Chart.js solo cuando es necesario para mejorar tiempo de carga inicial
 */

class ChartLazyLoader {
    constructor() {
        this.chartJsLoaded = false;
        this.chartInstances = new Map();
        this.pendingCharts = [];
        this.observers = new Map();
    }

    /**
     * Inicializa el sistema de lazy loading para charts
     */
    init() {
        // Observar charts cuando entran al viewport
        this.observeChartContainers();
        
        // Precargar Chart.js en páginas de estadísticas
        if (this.isStatsPage()) {
            this.preloadChartJS();
        }
    }

    /**
     * Detecta si estamos en una página que necesita charts
     */
    isStatsPage() {
        const statsPages = ['statistics.php', 'dashboard.php', 'analytics.php'];
        return statsPages.some(page => window.location.href.includes(page));
    }

    /**
     * Observa contenedores de charts para lazy loading
     */
    observeChartContainers() {
        const chartContainers = document.querySelectorAll('[data-chart-type]');
        
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadChart(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '50px' // Cargar 50px antes de que sea visible
            });

            chartContainers.forEach(container => {
                observer.observe(container);
                this.observers.set(container, observer);
            });
        } else {
            // Fallback para navegadores sin IntersectionObserver
            chartContainers.forEach(container => {
                this.loadChart(container);
            });
        }
    }

    /**
     * Precarga Chart.js en páginas de estadísticas
     */
    async preloadChartJS() {
        if (!this.chartJsLoaded) {
            await this.loadChartLibrary();
        }
    }

    /**
     * Carga la librería Chart.js de forma asíncrona
     */
    async loadChartLibrary() {
        if (this.chartJsLoaded) return Promise.resolve();

        return new Promise((resolve, reject) => {
            // Mostrar loading indicator
            this.showChartLoading();

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.async = true;
            
            script.onload = () => {
                this.chartJsLoaded = true;
                this.hideChartLoading();
                
                // Procesar charts pendientes
                this.processPendingCharts();
                
                console.log('Chart.js loaded successfully');
                resolve();
            };
            
            script.onerror = () => {
                console.error('Failed to load Chart.js');
                this.hideChartLoading();
                reject(new Error('Failed to load Chart.js'));
            };
            
            document.head.appendChild(script);
        });
    }

    /**
     * Carga un chart específico
     */
    async loadChart(container) {
        try {
            // Asegurar que Chart.js esté cargado
            if (!this.chartJsLoaded) {
                this.pendingCharts.push(container);
                await this.loadChartLibrary();
                return;
            }

            const chartType = container.dataset.chartType;
            const chartData = this.getChartData(container);
            
            if (!chartData) {
                console.warn('No chart data found for container:', container);
                return;
            }

            // Crear canvas si no existe
            let canvas = container.querySelector('canvas');
            if (!canvas) {
                canvas = document.createElement('canvas');
                canvas.id = container.id + '_canvas';
                container.appendChild(canvas);
            }

            // Configuración del chart
            const config = this.getChartConfig(chartType, chartData);
            
            // Crear instancia del chart
            const chart = new Chart(canvas.getContext('2d'), config);
            
            // Guardar referencia
            this.chartInstances.set(container.id, chart);
            
            // Marcar como cargado
            container.classList.add('chart-loaded');
            
            console.log(`Chart ${chartType} loaded for container:`, container.id);

        } catch (error) {
            console.error('Error loading chart:', error);
            this.showChartError(container, error.message);
        }
    }

    /**
     * Obtiene datos del chart desde el contenedor
     */
    getChartData(container) {
        try {
            const dataScript = container.querySelector('script[type="application/json"]');
            if (dataScript) {
                return JSON.parse(dataScript.textContent);
            }
            
            // Datos desde data attributes
            if (container.dataset.chartData) {
                return JSON.parse(container.dataset.chartData);
            }
            
            return null;
        } catch (error) {
            console.error('Error parsing chart data:', error);
            return null;
        }
    }

    /**
     * Configuración base para diferentes tipos de charts
     */
    getChartConfig(type, data) {
        const baseConfig = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        };

        const configs = {
            'line': {
                type: 'line',
                data: data,
                options: {
                    ...baseConfig,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            },
            'bar': {
                type: 'bar',
                data: data,
                options: {
                    ...baseConfig,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            },
            'doughnut': {
                type: 'doughnut',
                data: data,
                options: {
                    ...baseConfig,
                    cutout: '60%'
                }
            },
            'pie': {
                type: 'pie',
                data: data,
                options: baseConfig
            }
        };

        return configs[type] || configs['line'];
    }

    /**
     * Procesa charts que estaban pendientes de carga
     */
    processPendingCharts() {
        while (this.pendingCharts.length > 0) {
            const container = this.pendingCharts.shift();
            this.loadChart(container);
        }
    }

    /**
     * Muestra indicator de carga
     */
    showChartLoading() {
        const containers = document.querySelectorAll('[data-chart-type]:not(.chart-loaded)');
        containers.forEach(container => {
            if (!container.querySelector('.chart-loading')) {
                const loading = document.createElement('div');
                loading.className = 'chart-loading';
                loading.innerHTML = `
                    <div class="loading-spinner"></div>
                    <span>Cargando gráfico...</span>
                `;
                container.appendChild(loading);
            }
        });
    }

    /**
     * Oculta indicator de carga
     */
    hideChartLoading() {
        const loadingElements = document.querySelectorAll('.chart-loading');
        loadingElements.forEach(el => el.remove());
    }

    /**
     * Muestra error en el chart
     */
    showChartError(container, message) {
        const error = document.createElement('div');
        error.className = 'chart-error';
        error.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <span>Error al cargar el gráfico: ${message}</span>
        `;
        container.appendChild(error);
    }

    /**
     * Destruye un chart específico
     */
    destroyChart(containerId) {
        const chart = this.chartInstances.get(containerId);
        if (chart) {
            chart.destroy();
            this.chartInstances.delete(containerId);
        }
    }

    /**
     * Actualiza datos de un chart existente
     */
    updateChart(containerId, newData) {
        const chart = this.chartInstances.get(containerId);
        if (chart) {
            chart.data = newData;
            chart.update();
        }
    }

    /**
     * Limpieza al cambiar de página
     */
    cleanup() {
        // Destruir todos los charts
        this.chartInstances.forEach((chart, id) => {
            chart.destroy();
        });
        this.chartInstances.clear();

        // Limpiar observers
        this.observers.forEach(observer => {
            observer.disconnect();
        });
        this.observers.clear();

        // Limpiar pending charts
        this.pendingCharts = [];
    }
}

// CSS para loading y error states
const chartStyles = `
<style>
.chart-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: var(--text-secondary);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid var(--border-light);
    border-top: 3px solid var(--admin-accent-blue);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 10px;
}

.chart-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: var(--admin-accent-red);
    text-align: center;
    padding: 20px;
}

.chart-error i {
    font-size: 2rem;
    margin-bottom: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Chart container optimization */
[data-chart-type] {
    position: relative;
    min-height: 200px;
}

[data-chart-type].chart-loaded {
    min-height: auto;
}
</style>
`;

// Inyectar estilos
document.head.insertAdjacentHTML('beforeend', chartStyles);

// Inicializar el lazy loader
const chartLazyLoader = new ChartLazyLoader();

// Auto-inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        chartLazyLoader.init();
    });
} else {
    chartLazyLoader.init();
}

// Limpiar al cambiar de página
window.addEventListener('beforeunload', () => {
    chartLazyLoader.cleanup();
});

// Exportar para uso global
window.chartLazyLoader = chartLazyLoader;