<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar autenticación
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit;
}

$page_title = "Analytics Dashboard - ROI Tracking";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-dashboard {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }

        .dashboard-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin: 5px 0 0 0;
        }

        .refresh-btn {
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-left: 5px solid #667eea;
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .metric-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .metric-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            color: white;
        }

        .metric-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .metric-label {
            color: #666;
            font-size: 0.9rem;
        }

        .metric-trend {
            display: flex;
            align-items: center;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        .trend-up {
            color: #10b981;
        }

        .trend-down {
            color: #ef4444;
        }

        .trend-neutral {
            color: #6b7280;
        }

        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .chart-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .details-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .details-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .details-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 10px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-label {
            font-weight: 500;
            color: #555;
        }

        .detail-value {
            font-weight: 600;
            color: #667eea;
        }

        .export-section {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .export-btn {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .export-btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .loading {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Dark mode support */
        body.dark-mode .metric-card,
        body.dark-mode .chart-card,
        body.dark-mode .details-card {
            background: #374151;
            color: #f9fafb;
        }

        body.dark-mode .metric-title,
        body.dark-mode .chart-title,
        body.dark-mode .details-title {
            color: #f9fafb;
        }

        body.dark-mode .detail-label {
            color: #d1d5db;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .charts-section,
            .details-section {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body class="admin-page">
    <?php include '../includes/header.php'; ?>

    <div class="analytics-dashboard">
        <div class="dashboard-header">
            <div>
                <h1 class="dashboard-title">
                    <i class="fas fa-chart-line"></i> Analytics Dashboard
                </h1>
                <p class="dashboard-subtitle">Monitoreo de ROI y Conversiones en Tiempo Real</p>
            </div>
            <button class="refresh-btn" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Actualizar Datos
            </button>
        </div>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Cargando métricas de analytics...</p>
        </div>

        <div id="dashboard-content" style="display: none;">
            <!-- Métricas Principales -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #ff6b6b, #ff5252);">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <h3 class="metric-title">Exit Intent Popup</h3>
                    </div>
                    <div class="metric-value" id="exit-intent-rate">0%</div>
                    <div class="metric-label">Tasa de Conversión</div>
                    <div class="metric-trend" id="exit-intent-trend">
                        <i class="fas fa-arrow-up trend-up"></i>
                        <span>+5.2% vs período anterior</span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #4ecdc4, #44a08d);">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3 class="metric-title">Bundle Kit Attach Rate</h3>
                    </div>
                    <div class="metric-value" id="bundle-attach-rate">0%</div>
                    <div class="metric-label">% de Órdenes con Bundle</div>
                    <div class="metric-trend" id="bundle-trend">
                        <i class="fas fa-arrow-up trend-up"></i>
                        <span>+12.8% vs período anterior</span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #45b7d1, #096dd9);">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3 class="metric-title">Shipping Progress Impact</h3>
                    </div>
                    <div class="metric-value" id="shipping-impact">0</div>
                    <div class="metric-label">Productos Promedio por Orden</div>
                    <div class="metric-trend" id="shipping-trend">
                        <i class="fas fa-arrow-up trend-up"></i>
                        <span>+8.3% vs período anterior</span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="metric-title">Mobile vs Desktop</h3>
                    </div>
                    <div class="metric-value" id="mobile-conversion">0%</div>
                    <div class="metric-label">Conversión Mobile</div>
                    <div class="metric-trend" id="mobile-trend">
                        <i class="fas fa-arrow-down trend-down"></i>
                        <span>-2.1% vs Desktop</span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #a8edea, #fed6e3);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="metric-title">Time to Free Shipping</h3>
                    </div>
                    <div class="metric-value" id="free-shipping-time">0min</div>
                    <div class="metric-label">Tiempo Promedio</div>
                    <div class="metric-trend" id="free-shipping-trend">
                        <i class="fas fa-arrow-down trend-up"></i>
                        <span>-15% más rápido</span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #ffecd2, #fcb69f);">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3 class="metric-title">ROI Total</h3>
                    </div>
                    <div class="metric-value" id="total-roi">0%</div>
                    <div class="metric-label">Return on Investment</div>
                    <div class="metric-trend" id="roi-trend">
                        <i class="fas fa-arrow-up trend-up"></i>
                        <span>+24.7% vs mes anterior</span>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="charts-section">
                <div class="chart-card">
                    <h3 class="chart-title">Conversiones por Dispositivo</h3>
                    <div class="chart-container">
                        <canvas id="deviceChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">Tendencia de Exit Intent</h3>
                    <div class="chart-container">
                        <canvas id="exitIntentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detalles -->
            <div class="details-section">
                <div class="details-card">
                    <h3 class="details-title">Detalles de Bundle Kit</h3>
                    <div id="bundle-details">
                        <!-- Contenido dinámico -->
                    </div>
                </div>

                <div class="details-card">
                    <h3 class="details-title">Performance por Horario</h3>
                    <div id="hourly-performance">
                        <!-- Contenido dinámico -->
                    </div>
                </div>
            </div>

            <!-- Sección de Exportación -->
            <div class="export-section">
                <h3>Exportar Reportes</h3>
                <button class="export-btn" onclick="exportToJSON()">
                    <i class="fas fa-download"></i> JSON
                </button>
                <button class="export-btn" onclick="exportToCSV()">
                    <i class="fas fa-file-csv"></i> CSV
                </button>
                <button class="export-btn" onclick="generatePDFReport()">
                    <i class="fas fa-file-pdf"></i> PDF Report
                </button>
            </div>
        </div>
    </div>

    <script src="../assets/js/analytics-tracker.js"></script>
    <script>
        class AnalyticsDashboard {
            constructor() {
                this.charts = {};
                this.refreshInterval = null;
                this.init();
            }

            async init() {
                await this.loadDashboard();
                this.startAutoRefresh();
            }

            async loadDashboard() {
                try {
                    // Simular carga
                    await new Promise(resolve => setTimeout(resolve, 1500));
                    
                    if (window.analyticsTracker) {
                        const metrics = window.analyticsTracker.getMetrics();
                        const report = window.analyticsTracker.generateReport();
                        
                        this.updateMetricCards(report);
                        this.initializeCharts(metrics);
                        this.updateDetailsSections(metrics);
                    } else {
                        // Datos de ejemplo si no hay tracker
                        this.loadSampleData();
                    }
                    
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('dashboard-content').style.display = 'block';
                    
                } catch (error) {
                    console.error('Error loading dashboard:', error);
                    this.showError();
                }
            }

            updateMetricCards(report) {
                // Exit Intent
                const exitIntentRate = report.summary.exitIntentROI.conversionRate || 0;
                document.getElementById('exit-intent-rate').textContent = exitIntentRate.toFixed(1) + '%';

                // Bundle Attach Rate
                const bundleRate = report.summary.bundleKitROI.attachRate || 0;
                document.getElementById('bundle-attach-rate').textContent = bundleRate.toFixed(1) + '%';

                // Shipping Impact
                const shippingImpact = report.summary.shippingProgressROI.averageCartIncrease || 0;
                document.getElementById('shipping-impact').textContent = (shippingImpact / 1000).toFixed(1);

                // Mobile Conversion
                const mobileConversion = report.summary.devicePerformance.mobile.conversionRate || 0;
                document.getElementById('mobile-conversion').textContent = mobileConversion.toFixed(1) + '%';

                // Free Shipping Time
                const freeShippingTime = report.summary.freeShippingEffectiveness.averageTimeToReach || 0;
                document.getElementById('free-shipping-time').textContent = Math.round(freeShippingTime / 60000) + 'min';

                // Total ROI (calculado)
                const totalROI = this.calculateTotalROI(report);
                document.getElementById('total-roi').textContent = totalROI.toFixed(1) + '%';
            }

            calculateTotalROI(report) {
                // Fórmula simplificada de ROI basada en las mejoras
                const exitROI = report.summary.exitIntentROI.conversionRate * 0.3;
                const bundleROI = report.summary.bundleKitROI.attachRate * 0.4;
                const shippingROI = (report.summary.shippingProgressROI.averageCartIncrease / 1000) * 0.2;
                const deviceROI = report.summary.devicePerformance.mobile.conversionRate * 0.1;
                
                return exitROI + bundleROI + shippingROI + deviceROI;
            }

            initializeCharts(metrics) {
                this.createDeviceChart(metrics.deviceConversion);
                this.createExitIntentChart(metrics.exitIntent);
            }

            createDeviceChart(deviceData) {
                const ctx = document.getElementById('deviceChart').getContext('2d');
                
                const data = {
                    labels: ['Mobile', 'Desktop', 'Tablet'],
                    datasets: [{
                        label: 'Conversiones',
                        data: [
                            deviceData.mobile.conversions || 0,
                            deviceData.desktop.conversions || 0,
                            deviceData.tablet.conversions || 0
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 205, 86, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 205, 86, 1)'
                        ],
                        borderWidth: 2
                    }]
                };

                this.charts.deviceChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            createExitIntentChart(exitIntentData) {
                const ctx = document.getElementById('exitIntentChart').getContext('2d');
                
                // Generar datos de tendencia (últimos 7 días)
                const dates = [];
                const conversions = [];
                
                for (let i = 6; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    dates.push(date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' }));
                    
                    // Simular datos de conversión con variación
                    const baseRate = exitIntentData.conversionRate || 15;
                    const variation = (Math.random() - 0.5) * 10;
                    conversions.push(Math.max(0, baseRate + variation));
                }

                this.charts.exitIntentChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Conversión Exit Intent (%)',
                            data: conversions,
                            borderColor: 'rgba(102, 126, 234, 1)',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 30
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            updateDetailsSections(metrics) {
                // Bundle Details
                const bundleDetails = document.getElementById('bundle-details');
                bundleDetails.innerHTML = `
                    <div class="detail-item">
                        <span class="detail-label">Total Views de Productos</span>
                        <span class="detail-value">${metrics.bundleKit.productViews.toLocaleString()}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Bundle Views</span>
                        <span class="detail-value">${metrics.bundleKit.bundleViews.toLocaleString()}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Bundle Adds</span>
                        <span class="detail-value">${metrics.bundleKit.bundleAdds.toLocaleString()}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Revenue del Bundle</span>
                        <span class="detail-value">$${metrics.bundleKit.bundleRevenue.toLocaleString()}</span>
                    </div>
                `;

                // Hourly Performance (simulado)
                const hourlyPerformance = document.getElementById('hourly-performance');
                const currentHour = new Date().getHours();
                let hourlyHTML = '';
                
                for (let i = 0; i < 24; i += 4) {
                    const hour = i.toString().padStart(2, '0') + ':00';
                    const performance = Math.random() * 100;
                    const isCurrentPeriod = i <= currentHour && currentHour < i + 4;
                    
                    hourlyHTML += `
                        <div class="detail-item ${isCurrentPeriod ? 'current-period' : ''}">
                            <span class="detail-label">${hour} - ${(i+3).toString().padStart(2, '0')}:59</span>
                            <span class="detail-value">${performance.toFixed(1)}%</span>
                        </div>
                    `;
                }
                
                hourlyPerformance.innerHTML = hourlyHTML;
            }

            loadSampleData() {
                // Datos de ejemplo para demostración
                document.getElementById('exit-intent-rate').textContent = '18.5%';
                document.getElementById('bundle-attach-rate').textContent = '24.2%';
                document.getElementById('shipping-impact').textContent = '3.4';
                document.getElementById('mobile-conversion').textContent = '12.8%';
                document.getElementById('free-shipping-time').textContent = '8min';
                document.getElementById('total-roi').textContent = '31.7%';

                // Crear gráficos con datos de ejemplo
                this.createSampleCharts();
                this.createSampleDetails();
            }

            createSampleCharts() {
                // Device Chart
                const deviceCtx = document.getElementById('deviceChart').getContext('2d');
                this.charts.deviceChart = new Chart(deviceCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Mobile', 'Desktop', 'Tablet'],
                        datasets: [{
                            data: [45, 35, 20],
                            backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                // Exit Intent Chart
                const exitCtx = document.getElementById('exitIntentChart').getContext('2d');
                this.charts.exitIntentChart = new Chart(exitCtx, {
                    type: 'line',
                    data: {
                        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                        datasets: [{
                            label: 'Conversión %',
                            data: [15, 18, 22, 19, 25, 20, 18],
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 30
                            }
                        }
                    }
                });
            }

            createSampleDetails() {
                document.getElementById('bundle-details').innerHTML = `
                    <div class="detail-item">
                        <span class="detail-label">Total Views de Productos</span>
                        <span class="detail-value">2,847</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Bundle Views</span>
                        <span class="detail-value">687</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Bundle Adds</span>
                        <span class="detail-value">142</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Revenue del Bundle</span>
                        <span class="detail-value">$75,430</span>
                    </div>
                `;

                document.getElementById('hourly-performance').innerHTML = `
                    <div class="detail-item">
                        <span class="detail-label">00:00 - 03:59</span>
                        <span class="detail-value">15.2%</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">04:00 - 07:59</span>
                        <span class="detail-value">22.8%</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">08:00 - 11:59</span>
                        <span class="detail-value">45.6%</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">12:00 - 15:59</span>
                        <span class="detail-value">67.3%</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">16:00 - 19:59</span>
                        <span class="detail-value">78.9%</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">20:00 - 23:59</span>
                        <span class="detail-value">52.1%</span>
                    </div>
                `;
            }

            startAutoRefresh() {
                this.refreshInterval = setInterval(() => {
                    this.refreshDashboard();
                }, 60000); // Refresh cada minuto
            }

            async refreshDashboard() {
                console.log('🔄 Refreshing dashboard...');
                
                if (window.analyticsTracker) {
                    const metrics = window.analyticsTracker.getMetrics();
                    const report = window.analyticsTracker.generateReport();
                    
                    this.updateMetricCards(report);
                    this.updateDetailsSections(metrics);
                    
                    // Actualizar gráficos si es necesario
                    this.updateCharts(metrics);
                }
            }

            updateCharts(metrics) {
                // Actualizar datos de device chart
                if (this.charts.deviceChart) {
                    this.charts.deviceChart.data.datasets[0].data = [
                        metrics.deviceConversion.mobile.conversions || 0,
                        metrics.deviceConversion.desktop.conversions || 0,
                        metrics.deviceConversion.tablet.conversions || 0
                    ];
                    this.charts.deviceChart.update();
                }
            }

            showError() {
                document.getElementById('loading').innerHTML = `
                    <div style="color: #ef4444;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px;"></i>
                        <p>Error al cargar los datos de analytics</p>
                        <button onclick="location.reload()" style="margin-top: 15px; padding: 10px 20px; background: #ef4444; color: white; border: none; border-radius: 5px;">
                            Reintentar
                        </button>
                    </div>
                `;
            }
        }

        // Funciones globales
        function refreshDashboard() {
            if (window.dashboard) {
                window.dashboard.refreshDashboard();
            }
        }

        function exportToJSON() {
            if (window.analyticsTracker) {
                const report = window.analyticsTracker.generateReport();
                const dataStr = JSON.stringify(report, null, 2);
                const dataBlob = new Blob([dataStr], {type: 'application/json'});
                
                const link = document.createElement('a');
                link.href = URL.createObjectURL(dataBlob);
                link.download = `analytics-report-${new Date().toISOString().split('T')[0]}.json`;
                link.click();
            }
        }

        function exportToCSV() {
            if (window.analyticsTracker) {
                const events = window.analyticsTracker.getEvents(1000);
                let csv = 'Timestamp,Event,Session ID,User ID,Details\n';
                
                events.forEach(event => {
                    const details = JSON.stringify(event.data).replace(/"/g, '""');
                    csv += `${new Date(event.timestamp).toISOString()},${event.name},${event.sessionId},${event.userId},"${details}"\n`;
                });
                
                const dataBlob = new Blob([csv], {type: 'text/csv'});
                const link = document.createElement('a');
                link.href = URL.createObjectURL(dataBlob);
                link.download = `analytics-events-${new Date().toISOString().split('T')[0]}.csv`;
                link.click();
            }
        }

        function generatePDFReport() {
            // Simulación de generación PDF
            alert('Función de PDF en desarrollo. Por ahora use JSON o CSV para exportar los datos.');
        }

        // Inicializar dashboard cuando se carga la página
        document.addEventListener('DOMContentLoaded', () => {
            window.dashboard = new AnalyticsDashboard();
        });

        // Cleanup al salir
        window.addEventListener('beforeunload', () => {
            if (window.dashboard && window.dashboard.refreshInterval) {
                clearInterval(window.dashboard.refreshInterval);
            }
        });
    </script>
</body>
</html>