<?php
$pageTitle = '🔥 Heatmap Analytics - Admin Panel';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-fire"></i> Heatmap Analytics</h1>
    <p>Análisis avanzado de comportamiento de usuarios con mapas de calor y grabaciones de sesión</p>
    
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportAnalytics()">
            <i class="fas fa-download"></i> Exportar
        </button>
    </div>
</div>

    <!-- Analytics Controls -->
    <div class="analytics-controls">
        <div class="control-group">
            <label for="timeframe-select">Período de tiempo:</label>
            <select id="timeframe-select">
                <option value="1d">Últimas 24 horas</option>
                <option value="7d" selected>Últimos 7 días</option>
                <option value="30d">Últimos 30 días</option>
                <option value="90d">Últimos 90 días</option>
            </select>
        </div>

        <div class="control-group">
            <label for="page-filter">Filtrar por página:</label>
            <select id="page-filter">
                <option value="">Todas las páginas</option>
                <option value="/">Página principal</option>
                <option value="/particulares.php">Tienda</option>
                <option value="/product-detail.php">Detalle producto</option>
                <option value="/checkout.php">Checkout</option>
                <option value="/customize-shirt.php">Editor remeras</option>
            </select>
        </div>

        <div class="control-group">
            <button id="refresh-data" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Actualizar datos
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="card-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-content">
                <h3 id="total-sessions">-</h3>
                <p>Sesiones totales</p>
                <span class="trend" id="sessions-trend"></span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">
                <i class="fas fa-mouse-pointer"></i>
            </div>
            <div class="card-content">
                <h3 id="total-clicks">-</h3>
                <p>Clics registrados</p>
                <span class="trend" id="clicks-trend"></span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-content">
                <h3 id="avg-duration">-</h3>
                <p>Duración promedio</p>
                <span class="trend" id="duration-trend"></span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">
                <i class="fas fa-video"></i>
            </div>
            <div class="card-content">
                <h3 id="total-recordings">-</h3>
                <p>Grabaciones disponibles</p>
                <span class="trend" id="recordings-trend"></span>
            </div>
        </div>
    </div>

    <!-- Analytics Tabs -->
    <div class="analytics-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="overview">Resumen</button>
            <button class="tab-btn" data-tab="heatmap">Mapa de calor</button>
            <button class="tab-btn" data-tab="scroll">Análisis scroll</button>
            <button class="tab-btn" data-tab="flow">Flujo de usuarios</button>
            <button class="tab-btn" data-tab="recordings">Grabaciones</button>
        </div>

        <!-- Overview Tab -->
        <div id="overview-tab" class="tab-content active">
            <div class="overview-grid">
                <div class="chart-container">
                    <h3>Páginas más visitadas</h3>
                    <canvas id="top-pages-chart"></canvas>
                </div>

                <div class="chart-container">
                    <h3>Elementos más clickeados</h3>
                    <div id="top-clicks-list" class="clicks-list"></div>
                </div>

                <div class="chart-container">
                    <h3>Distribución de dispositivos</h3>
                    <canvas id="device-distribution-chart"></canvas>
                </div>

                <div class="chart-container">
                    <h3>Actividad por hora</h3>
                    <canvas id="hourly-activity-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Heatmap Tab -->
        <div id="heatmap-tab" class="tab-content">
            <div class="heatmap-controls">
                <div class="heatmap-options">
                    <label>
                        <input type="radio" name="heatmap-type" value="clicks" checked>
                        Clics
                    </label>
                    <label>
                        <input type="radio" name="heatmap-type" value="moves">
                        Movimientos
                    </label>
                    <label>
                        <input type="radio" name="heatmap-type" value="scroll">
                        Scroll
                    </label>
                </div>

                <div class="heatmap-intensity">
                    <label>Intensidad:</label>
                    <input type="range" id="intensity-slider" min="1" max="10" value="5">
                    <span id="intensity-value">5</span>
                </div>
            </div>

            <div class="heatmap-container">
                <div id="heatmap-visualization">
                    <!-- Heatmap will be rendered here -->
                    <div class="heatmap-placeholder">
                        <i class="fas fa-fire"></i>
                        <p>Selecciona una página para ver el mapa de calor</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Analysis Tab -->
        <div id="scroll-tab" class="tab-content">
            <div class="scroll-analysis">
                <div class="scroll-chart-container">
                    <h3>Profundidad de scroll por página</h3>
                    <canvas id="scroll-depth-chart"></canvas>
                </div>

                <div class="scroll-stats">
                    <h3>Estadísticas de scroll</h3>
                    <div id="scroll-statistics"></div>
                </div>
            </div>
        </div>

        <!-- User Flow Tab -->
        <div id="flow-tab" class="tab-content">
            <div class="user-flow">
                <h3>Flujo de navegación de usuarios</h3>
                <div id="user-flow-diagram">
                    <!-- Flow diagram will be rendered here -->
                </div>

                <div class="flow-table">
                    <h4>Transiciones más comunes</h4>
                    <table id="flow-transitions-table">
                        <thead>
                            <tr>
                                <th>Desde</th>
                                <th>Hacia</th>
                                <th>Usuarios</th>
                                <th>Tasa</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recordings Tab -->
        <div id="recordings-tab" class="tab-content">
            <div class="recordings-section">
                <div class="recordings-controls">
                    <div class="search-controls">
                        <input type="text" id="recording-search" placeholder="Buscar por ID de sesión...">
                        <select id="recording-filter">
                            <option value="">Todas las grabaciones</option>
                            <option value="long">Sesiones largas (>5 min)</option>
                            <option value="active">Sesiones activas (>50 eventos)</option>
                            <option value="recent">Más recientes</option>
                        </select>
                    </div>
                </div>

                <div class="recordings-list">
                    <table id="recordings-table">
                        <thead>
                            <tr>
                                <th>ID Sesión</th>
                                <th>Duración</th>
                                <th>Eventos</th>
                                <th>Páginas</th>
                                <th>Inicio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="recordings-pagination">
                    <button id="prev-recordings" disabled>← Anterior</button>
                    <span id="recordings-page-info">Página 1 de 1</span>
                    <button id="next-recordings" disabled>Siguiente →</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Recording Modal -->
    <div id="recording-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="recording-title">Grabación de sesión</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="recording-info">
                    <div class="recording-meta">
                        <span><strong>ID:</strong> <span id="recording-id"></span></span>
                        <span><strong>Usuario:</strong> <span id="recording-user"></span></span>
                        <span><strong>Duración:</strong> <span id="recording-duration"></span></span>
                        <span><strong>Eventos:</strong> <span id="recording-events"></span></span>
                    </div>
                </div>

                <div class="recording-timeline">
                    <div class="timeline-controls">
                        <button id="play-recording" class="btn btn-primary">
                            <i class="fas fa-play"></i> Reproducir
                        </button>
                        <button id="pause-recording" class="btn btn-secondary" disabled>
                            <i class="fas fa-pause"></i> Pausar
                        </button>
                        <input type="range" id="recording-progress" min="0" max="100" value="0">
                        <span id="recording-time">00:00 / 00:00</span>
                    </div>
                </div>

                <div class="recording-viewer">
                    <div id="recording-events-list">
                        <!-- Events will be listed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.analytics-controls {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.control-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.control-group label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.control-group select,
.control-group input {
    padding: 8px 12px;
    border: 2px solid #e0e6ed;
    border-radius: 6px;
    font-size: 14px;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-content h3 {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 5px 0;
}

.card-content p {
    color: #7f8c8d;
    margin: 0 0 10px 0;
    font-size: 14px;
}

.trend {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
}

.trend.positive {
    background: #d4edda;
    color: #155724;
}

.trend.negative {
    background: #f8d7da;
    color: #721c24;
}

.analytics-tabs {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.tab-buttons {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.tab-btn {
    flex: 1;
    padding: 15px 20px;
    border: none;
    background: transparent;
    color: #6c757d;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.tab-btn.active {
    background: white;
    color: #007bff;
    border-bottom: 3px solid #007bff;
}

.tab-content {
    display: none;
    padding: 30px;
}

.tab-content.active {
    display: block;
}

.overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
}

.chart-container {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.chart-container h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 18px;
    font-weight: 600;
}

.clicks-list {
    max-height: 300px;
    overflow-y: auto;
}

.click-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.click-item:last-child {
    border-bottom: none;
}

.click-element {
    font-weight: 600;
    color: #2c3e50;
}

.click-text {
    color: #6c757d;
    font-size: 14px;
    margin-top: 4px;
}

.click-count {
    background: #007bff;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.heatmap-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.heatmap-options {
    display: flex;
    gap: 20px;
}

.heatmap-options label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.heatmap-container {
    background: #f8f9fa;
    border-radius: 8px;
    min-height: 400px;
    position: relative;
}

.heatmap-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 400px;
    color: #6c757d;
}

.heatmap-placeholder i {
    font-size: 48px;
    margin-bottom: 15px;
}

.recordings-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.recordings-table th,
.recordings-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.recordings-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.recordings-table tr:hover {
    background: #f8f9fa;
}

.recording-actions {
    display: flex;
    gap: 10px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 4px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 1000px;
    max-height: 80vh;
    overflow: hidden;
}

.modal-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}

.timeline-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 20px 0;
}

.recording-meta {
    display: flex;
    gap: 30px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

#recording-progress {
    flex: 1;
}

@media (max-width: 768px) {
    .analytics-controls {
        flex-direction: column;
    }
    
    .tab-buttons {
        overflow-x: auto;
    }
    
    .overview-grid {
        grid-template-columns: 1fr;
    }
    
    .summary-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
class HeatmapAnalyticsDashboard {
    constructor() {
        this.currentTimeframe = '7d';
        this.currentPage = '';
        this.charts = {};
        this.currentRecordingPage = 1;
        this.recordingsPerPage = 10;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadAnalyticsData();
        this.setupTabs();
    }
    
    setupEventListeners() {
        // Timeframe and page filter changes
        document.getElementById('timeframe-select').addEventListener('change', (e) => {
            this.currentTimeframe = e.target.value;
            this.loadAnalyticsData();
        });
        
        document.getElementById('page-filter').addEventListener('change', (e) => {
            this.currentPage = e.target.value;
            this.loadAnalyticsData();
        });
        
        // Refresh button
        document.getElementById('refresh-data').addEventListener('click', () => {
            this.loadAnalyticsData();
        });
        
        // Heatmap controls
        document.querySelectorAll('input[name="heatmap-type"]').forEach(radio => {
            radio.addEventListener('change', () => {
                this.loadHeatmapData();
            });
        });
        
        document.getElementById('intensity-slider').addEventListener('input', (e) => {
            document.getElementById('intensity-value').textContent = e.target.value;
            this.updateHeatmapIntensity(e.target.value);
        });
        
        // Recording modal
        this.setupRecordingModal();
    }
    
    setupTabs() {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tabName = e.target.dataset.tab;
                this.switchTab(tabName);
            });
        });
    }
    
    switchTab(tabName) {
        // Update button states
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        
        // Update content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');
        
        // Load tab-specific data
        switch(tabName) {
            case 'heatmap':
                this.loadHeatmapData();
                break;
            case 'scroll':
                this.loadScrollAnalysis();
                break;
            case 'flow':
                this.loadUserFlow();
                break;
            case 'recordings':
                this.loadSessionRecordings();
                break;
        }
    }
    
    async loadAnalyticsData() {
        try {
            const response = await fetch(`/api/analytics/heatmap-event.php?type=summary&timeframe=${this.currentTimeframe}&page=${this.currentPage}`);
            const data = await response.json();
            
            if (data.success) {
                this.updateSummaryCards(data.data);
                this.updateOverviewCharts(data.data);
            }
        } catch (error) {
            console.error('Error loading analytics data:', error);
        }
    }
    
    updateSummaryCards(data) {
        document.getElementById('total-sessions').textContent = data.totals.total_sessions.toLocaleString();
        document.getElementById('total-clicks').textContent = data.totals.total_events.toLocaleString();
        document.getElementById('avg-duration').textContent = this.formatDuration(data.avg_session_duration);
        document.getElementById('total-recordings').textContent = data.session_count.toLocaleString();
        
        // Update trends (placeholder - would calculate from historical data)
        this.updateTrend('sessions-trend', 12.5, true);
        this.updateTrend('clicks-trend', -3.2, false);
        this.updateTrend('duration-trend', 8.7, true);
        this.updateTrend('recordings-trend', 15.1, true);
    }
    
    updateTrend(elementId, value, isPositive) {
        const element = document.getElementById(elementId);
        element.textContent = `${isPositive ? '+' : ''}${value}%`;
        element.className = `trend ${isPositive ? 'positive' : 'negative'}`;
    }
    
    updateOverviewCharts(data) {
        this.createTopPagesChart(data.top_pages);
        this.createTopClicksList(data.top_clicks);
        this.createDeviceDistributionChart();
        this.createHourlyActivityChart();
    }
    
    createTopPagesChart(pages) {
        const ctx = document.getElementById('top-pages-chart').getContext('2d');
        
        if (this.charts.topPages) {
            this.charts.topPages.destroy();
        }
        
        this.charts.topPages = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: pages.map(page => page.page_url.substring(0, 30) + '...'),
                datasets: [{
                    label: 'Sesiones',
                    data: pages.map(page => page.session_count),
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    createTopClicksList(clicks) {
        const container = document.getElementById('top-clicks-list');
        container.innerHTML = '';
        
        clicks.forEach(click => {
            const item = document.createElement('div');
            item.className = 'click-item';
            item.innerHTML = `
                <div>
                    <div class="click-element">${click.target_selector || 'Unknown element'}</div>
                    <div class="click-text">${click.target_text || 'No text'}</div>
                </div>
                <div class="click-count">${click.click_count}</div>
            `;
            container.appendChild(item);
        });
    }
    
    createDeviceDistributionChart() {
        const ctx = document.getElementById('device-distribution-chart').getContext('2d');
        
        if (this.charts.deviceDistribution) {
            this.charts.deviceDistribution.destroy();
        }
        
        this.charts.deviceDistribution = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Desktop', 'Mobile', 'Tablet'],
                datasets: [{
                    data: [45, 40, 15],
                    backgroundColor: ['#007bff', '#28a745', '#ffc107'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    createHourlyActivityChart() {
        const ctx = document.getElementById('hourly-activity-chart').getContext('2d');
        
        if (this.charts.hourlyActivity) {
            this.charts.hourlyActivity.destroy();
        }
        
        // Sample data - would come from actual analytics
        const hours = Array.from({length: 24}, (_, i) => i);
        const sampleData = hours.map(() => Math.floor(Math.random() * 100));
        
        this.charts.hourlyActivity = new Chart(ctx, {
            type: 'line',
            data: {
                labels: hours.map(h => `${h}:00`),
                datasets: [{
                    label: 'Actividad',
                    data: sampleData,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    async loadHeatmapData() {
        const type = document.querySelector('input[name="heatmap-type"]:checked').value;
        
        try {
            const response = await fetch(`/api/analytics/heatmap-event.php?type=clicks&page=${this.currentPage}&timeframe=${this.currentTimeframe}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderHeatmap(data.data, type);
            }
        } catch (error) {
            console.error('Error loading heatmap data:', error);
        }
    }
    
    renderHeatmap(data, type) {
        const container = document.getElementById('heatmap-visualization');
        
        if (data.length === 0) {
            container.innerHTML = `
                <div class="heatmap-placeholder">
                    <i class="fas fa-chart-area"></i>
                    <p>No hay datos de ${type} para mostrar en el período seleccionado</p>
                </div>
            `;
            return;
        }
        
        // This would integrate with a heatmap library like h337.js
        container.innerHTML = `
            <div class="heatmap-info">
                <h4>Mapa de calor - ${type}</h4>
                <p>Mostrando ${data.length} puntos de datos</p>
                <div class="heatmap-legend">
                    <span>Menos actividad</span>
                    <div class="legend-gradient"></div>
                    <span>Más actividad</span>
                </div>
            </div>
        `;
    }
    
    async loadScrollAnalysis() {
        try {
            const response = await fetch(`/api/analytics/heatmap-event.php?type=scrolls&page=${this.currentPage}&timeframe=${this.currentTimeframe}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderScrollChart(data.data);
            }
        } catch (error) {
            console.error('Error loading scroll analysis:', error);
        }
    }
    
    renderScrollChart(data) {
        const ctx = document.getElementById('scroll-depth-chart').getContext('2d');
        
        if (this.charts.scrollDepth) {
            this.charts.scrollDepth.destroy();
        }
        
        this.charts.scrollDepth = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => `${d.scroll_depth_percent}%`),
                datasets: [{
                    label: 'Usuarios',
                    data: data.map(d => d.user_count),
                    backgroundColor: '#28a745',
                    borderColor: '#1e7e34',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Update statistics
        this.updateScrollStatistics(data);
    }
    
    updateScrollStatistics(data) {
        const container = document.getElementById('scroll-statistics');
        const totalUsers = data.reduce((sum, d) => sum + d.user_count, 0);
        const avgDepth = data.reduce((sum, d) => sum + (d.scroll_depth_percent * d.user_count), 0) / totalUsers;
        
        container.innerHTML = `
            <div class="stat-item">
                <h4>${totalUsers.toLocaleString()}</h4>
                <p>Total de usuarios</p>
            </div>
            <div class="stat-item">
                <h4>${Math.round(avgDepth)}%</h4>
                <p>Profundidad promedio</p>
            </div>
            <div class="stat-item">
                <h4>${data.filter(d => d.scroll_depth_percent >= 75).reduce((s, d) => s + d.user_count, 0)}</h4>
                <p>Llegaron al 75%</p>
            </div>
        `;
    }
    
    async loadUserFlow() {
        try {
            const response = await fetch(`/api/analytics/heatmap-event.php?type=user_flow&timeframe=${this.currentTimeframe}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderUserFlow(data.data);
            }
        } catch (error) {
            console.error('Error loading user flow:', error);
        }
    }
    
    renderUserFlow(data) {
        const table = document.getElementById('flow-transitions-table').getElementsByTagName('tbody')[0];
        table.innerHTML = '';
        
        data.forEach(transition => {
            const row = table.insertRow();
            const totalTransitions = data.reduce((sum, t) => sum + t.transition_count, 0);
            const rate = ((transition.transition_count / totalTransitions) * 100).toFixed(1);
            
            row.innerHTML = `
                <td>${transition.from_page}</td>
                <td>${transition.to_page}</td>
                <td>${transition.transition_count.toLocaleString()}</td>
                <td>${rate}%</td>
            `;
        });
    }
    
    async loadSessionRecordings() {
        try {
            const response = await fetch(`/api/analytics/heatmap-event.php?type=session_recordings&timeframe=${this.currentTimeframe}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderSessionRecordings(data.data);
            }
        } catch (error) {
            console.error('Error loading session recordings:', error);
        }
    }
    
    renderSessionRecordings(recordings) {
        const table = document.getElementById('recordings-table').getElementsByTagName('tbody')[0];
        table.innerHTML = '';
        
        recordings.forEach(recording => {
            const row = table.insertRow();
            row.innerHTML = `
                <td><code>${recording.session_id.substring(0, 12)}...</code></td>
                <td>${recording.duration_formatted}</td>
                <td>${recording.event_count}</td>
                <td>${recording.page_count}</td>
                <td>${new Date(recording.session_start * 1000).toLocaleString()}</td>
                <td>
                    <div class="recording-actions">
                        <button class="btn btn-sm btn-primary" onclick="dashboard.viewRecording('${recording.session_id}')">
                            <i class="fas fa-play"></i> Ver
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="dashboard.downloadRecording('${recording.session_id}')">
                            <i class="fas fa-download"></i> Descargar
                        </button>
                    </div>
                </td>
            `;
        });
    }
    
    setupRecordingModal() {
        const modal = document.getElementById('recording-modal');
        const closeBtn = modal.querySelector('.close');
        
        closeBtn.onclick = () => {
            modal.style.display = 'none';
        };
        
        window.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    }
    
    viewRecording(sessionId) {
        // Load and display recording
        const modal = document.getElementById('recording-modal');
        document.getElementById('recording-id').textContent = sessionId;
        modal.style.display = 'block';
        
        // Load recording data
        this.loadRecordingData(sessionId);
    }
    
    async loadRecordingData(sessionId) {
        // This would load the actual recording events
        // For now, we'll show placeholder data
        document.getElementById('recording-user').textContent = 'user_' + sessionId.substring(0, 8);
        document.getElementById('recording-duration').textContent = '5:23';
        document.getElementById('recording-events').textContent = '127';
        
        // Show events list
        const eventsList = document.getElementById('recording-events-list');
        eventsList.innerHTML = `
            <div class="event-item">
                <span class="event-time">00:00</span>
                <span class="event-type">Page Load</span>
                <span class="event-details">/</span>
            </div>
            <div class="event-item">
                <span class="event-time">00:03</span>
                <span class="event-type">Click</span>
                <span class="event-details">Button: "Ver productos"</span>
            </div>
            <div class="event-item">
                <span class="event-time">00:15</span>
                <span class="event-type">Scroll</span>
                <span class="event-details">Depth: 25%</span>
            </div>
        `;
    }
    
    downloadRecording(sessionId) {
        // Generate and download recording data
        console.log('Downloading recording:', sessionId);
        alert('Funcionalidad de descarga próximamente disponible');
    }
    
    formatDuration(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        
        if (minutes > 0) {
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        } else {
            return `0:${remainingSeconds.toString().padStart(2, '0')}`;
        }
    }
}

// Initialize dashboard
let dashboard;
document.addEventListener('DOMContentLoaded', () => {
    dashboard = new HeatmapAnalyticsDashboard();
});
</script>

<?php include 'admin-master-footer.php'; ?>