/**
 * ╔══════════════════════════════════════════════════════════════════════════════════════╗
 * ║                          DASHBOARD PRO - ENTERPRISE JS                              ║
 * ║                           Professional Admin Dashboard                               ║
 * ║                                                                                      ║
 * ║ Description: Enhanced JavaScript for professional admin dashboard                    ║
 * ║ Features: Animations, interactions, real-time updates, accessibility                ║
 * ║ Author: Professional Frontend Development Team                                      ║
 * ║ Version: 1.0.0                                                                      ║
 * ╚══════════════════════════════════════════════════════════════════════════════════════╝
 */

class DashboardPro {
    constructor() {
        this.init();
        this.bindEvents();
        this.initializeAnimations();
        this.startRealTimeUpdates();
    }

    init() {
        // Initialize dashboard components
        this.initializeCharts();
        this.initializeTooltips();
        this.initializeModals();
        this.initializeThemeToggle();
        this.initializeKeyboardShortcuts();
        
        // Add fade-in animation to main sections
        this.animateOnLoad();
    }

    bindEvents() {
        // Refresh button
        const refreshBtn = document.getElementById('refresh-data-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', this.refreshData.bind(this));
        }

        // Export button
        const exportBtn = document.getElementById('export-report-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', this.exportReport.bind(this));
        }

        // Time period selector
        const timePeriod = document.getElementById('time-period');
        if (timePeriod) {
            timePeriod.addEventListener('change', this.updateDashboard.bind(this));
        }

        // Metric cards hover effects
        this.bindMetricCardEvents();
        
        // Action cards events
        this.bindActionCardEvents();
        
        // Activity items events
        this.bindActivityEvents();
    }

    initializeCharts() {
        // Chart.js default configuration
        Chart.defaults.font.family = 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#6b7280';
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;
        
        // Initialize charts with enhanced styling
        this.initializeSalesChart();
        this.initializeProductsChart();
        this.initializeMetricsChart();
    }

    initializeSalesChart() {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        // Create gradient
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.1)');

        this.salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.labels,
                datasets: [{
                    label: 'Órdenes',
                    data: salesData.orders,
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                return `${context[0].label}`;
                            },
                            label: function(context) {
                                return `${context.parsed.y} órdenes`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 11
                            },
                            padding: 10
                        }
                    }
                },
                elements: {
                    point: {
                        hoverBackgroundColor: '#3b82f6',
                        hoverBorderColor: '#ffffff'
                    }
                }
            }
        });
    }

    initializeProductsChart() {
        const ctx = document.getElementById('productsChart');
        if (!ctx) return;

        this.productsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: productsData.labels,
                datasets: [{
                    data: productsData.sales,
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 8,
                    hoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 12,
                                family: 'Inter'
                            },
                            color: '#6b7280'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    initializeMetricsChart() {
        // Add sparkline charts to metric cards
        const metricCards = document.querySelectorAll('.metric-card');
        metricCards.forEach((card, index) => {
            this.addSparklineToCard(card, index);
        });
    }

    addSparklineToCard(card, index) {
        const sparklineData = [
            [12, 19, 3, 5, 2, 3, 9, 15, 10, 8, 12, 18],
            [8, 12, 18, 15, 10, 12, 8, 14, 16, 12, 15, 20],
            [5, 9, 5, 6, 4, 12, 18, 14, 10, 15, 12, 16],
            [3, 5, 4, 8, 12, 15, 18, 20, 16, 12, 14, 18],
            [15, 12, 8, 10, 14, 16, 12, 18, 20, 22, 18, 25],
            [8, 10, 12, 14, 16, 18, 20, 22, 18, 16, 20, 24]
        ];

        const canvas = document.createElement('canvas');
        canvas.width = 100;
        canvas.height = 30;
        canvas.style.cssText = 'position: absolute; top: 10px; right: 10px; opacity: 0.6;';
        
        const ctx = canvas.getContext('2d');
        const data = sparklineData[index] || sparklineData[0];
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i),
                datasets: [{
                    data: data,
                    borderColor: '#3b82f6',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0.4
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                },
                elements: {
                    point: { radius: 0 }
                }
            }
        });
        
        card.style.position = 'relative';
        card.appendChild(canvas);
    }

    updateSalesChart(type) {
        if (!this.salesChart) return;

        const data = type === 'revenue' ? salesData.revenue : salesData.orders;
        const label = type === 'revenue' ? 'Ingresos ($)' : 'Órdenes';
        
        this.salesChart.data.datasets[0].data = data;
        this.salesChart.data.datasets[0].label = label;
        this.salesChart.update('active');
        
        // Update button states
        document.querySelectorAll('.chart-header .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById(type + '-btn')?.classList.add('active');
    }

    bindMetricCardEvents() {
        const metricCards = document.querySelectorAll('.metric-card');
        metricCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-4px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
            });
        });
    }

    bindActionCardEvents() {
        const actionCards = document.querySelectorAll('.action-card');
        actionCards.forEach(card => {
            card.addEventListener('click', (e) => {
                // Add ripple effect
                this.createRippleEffect(e, card);
            });
        });
    }

    bindActivityEvents() {
        const activityItems = document.querySelectorAll('.activity-item');
        activityItems.forEach(item => {
            item.addEventListener('click', () => {
                item.style.backgroundColor = '#f3f4f6';
                setTimeout(() => {
                    item.style.backgroundColor = '';
                }, 200);
            });
        });
    }

    createRippleEffect(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(59, 130, 246, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    initializeTooltips() {
        // Add tooltips to action cards
        const actionCards = document.querySelectorAll('.action-card');
        actionCards.forEach(card => {
            const title = card.querySelector('h4')?.textContent;
            const description = card.querySelector('p')?.textContent;
            
            if (title && description) {
                card.setAttribute('title', `${title}: ${description}`);
            }
        });
    }

    initializeModals() {
        // Initialize modal functionality for detailed views
        this.createModal();
    }

    createModal() {
        const modal = document.createElement('div');
        modal.className = 'dashboard-modal';
        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Detalles</h2>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Cargando...</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Add modal styles
        const style = document.createElement('style');
        style.textContent = `
            .dashboard-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1050;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .dashboard-modal.active {
                display: flex;
            }
            
            .modal-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(5px);
            }
            
            .modal-content {
                background: white;
                border-radius: 16px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                max-width: 600px;
                width: 100%;
                max-height: 80vh;
                overflow-y: auto;
                position: relative;
                z-index: 1051;
                transform: scale(0.9);
                transition: transform 0.3s ease;
            }
            
            .dashboard-modal.active .modal-content {
                transform: scale(1);
            }
            
            .modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 24px;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .modal-title {
                margin: 0;
                font-size: 1.5rem;
                font-weight: 600;
                color: #1f2937;
            }
            
            .modal-close {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #6b7280;
                padding: 0;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.2s ease;
            }
            
            .modal-close:hover {
                background: #f3f4f6;
                color: #1f2937;
            }
            
            .modal-body {
                padding: 24px;
            }
        `;
        
        document.head.appendChild(style);
        
        // Bind modal events
        const closeBtn = modal.querySelector('.modal-close');
        const backdrop = modal.querySelector('.modal-backdrop');
        
        closeBtn.addEventListener('click', () => this.hideModal());
        backdrop.addEventListener('click', () => this.hideModal());
        
        this.modal = modal;
    }

    showModal(title, content) {
        if (!this.modal) return;
        
        this.modal.querySelector('.modal-title').textContent = title;
        this.modal.querySelector('.modal-body').innerHTML = content;
        this.modal.classList.add('active');
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    hideModal() {
        if (!this.modal) return;
        
        this.modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    initializeThemeToggle() {
        // Add theme toggle functionality
        const themeToggle = document.createElement('button');
        themeToggle.className = 'theme-toggle';
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        themeToggle.title = 'Cambiar tema';
        
        // Add to header controls
        const headerControls = document.querySelector('.header-controls');
        if (headerControls) {
            headerControls.appendChild(themeToggle);
        }
        
        // Add theme toggle styles
        const style = document.createElement('style');
        style.textContent = `
            .theme-toggle {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                border: 1px solid #e5e7eb;
                background: white;
                cursor: pointer;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #6b7280;
            }
            
            .theme-toggle:hover {
                background: #f3f4f6;
                border-color: #d1d5db;
                transform: scale(1.05);
            }
            
            [data-theme="dark"] .theme-toggle {
                background: #374151;
                border-color: #4b5563;
                color: #f3f4f6;
            }
            
            [data-theme="dark"] .theme-toggle:hover {
                background: #4b5563;
                border-color: #6b7280;
            }
        `;
        
        document.head.appendChild(style);
        
        // Bind theme toggle event
        themeToggle.addEventListener('click', this.toggleTheme.bind(this));
    }

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('dashboard-theme', newTheme);
        
        // Update icon
        const icon = document.querySelector('.theme-toggle i');
        if (icon) {
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
        
        // Update charts colors
        this.updateChartsTheme(newTheme);
    }

    updateChartsTheme(theme) {
        const textColor = theme === 'dark' ? '#f3f4f6' : '#6b7280';
        const gridColor = theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';
        
        if (this.salesChart) {
            this.salesChart.options.scales.x.ticks.color = textColor;
            this.salesChart.options.scales.y.ticks.color = textColor;
            this.salesChart.options.scales.y.grid.color = gridColor;
            this.salesChart.update();
        }
        
        if (this.productsChart) {
            this.productsChart.options.plugins.legend.labels.color = textColor;
            this.productsChart.update();
        }
    }

    initializeKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'r':
                        e.preventDefault();
                        this.refreshData();
                        break;
                    case 'e':
                        e.preventDefault();
                        this.exportReport();
                        break;
                    case 't':
                        e.preventDefault();
                        this.toggleTheme();
                        break;
                }
            }
            
            if (e.key === 'Escape') {
                this.hideModal();
            }
        });
    }

    animateOnLoad() {
        // Add staggered animation to metric cards
        const metricCards = document.querySelectorAll('.metric-card');
        metricCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Animate charts section
        const chartsSection = document.querySelector('.charts-section');
        if (chartsSection) {
            chartsSection.style.opacity = '0';
            chartsSection.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                chartsSection.style.transition = 'all 0.8s ease';
                chartsSection.style.opacity = '1';
                chartsSection.style.transform = 'translateY(0)';
            }, 500);
        }
        
        // Animate quick actions
        const quickActions = document.querySelector('.quick-actions');
        if (quickActions) {
            quickActions.style.opacity = '0';
            quickActions.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                quickActions.style.transition = 'all 0.8s ease';
                quickActions.style.opacity = '1';
                quickActions.style.transform = 'translateY(0)';
            }, 700);
        }
    }

    startRealTimeUpdates() {
        // Simulate real-time updates
        setInterval(() => {
            this.updateMetrics();
        }, 30000); // Update every 30 seconds
        
        setInterval(() => {
            this.updateActivity();
        }, 15000); // Update activity every 15 seconds
    }

    updateMetrics() {
        // Simulate metric updates
        const metricValues = document.querySelectorAll('.metric-content h3');
        metricValues.forEach(value => {
            const currentValue = parseInt(value.textContent.replace(/[^0-9]/g, ''));
            const newValue = currentValue + Math.floor(Math.random() * 5);
            
            // Animate number change
            this.animateNumber(value, currentValue, newValue);
        });
    }

    animateNumber(element, from, to) {
        const duration = 1000;
        const startTime = performance.now();
        const originalText = element.textContent;
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = Math.floor(from + (to - from) * progress);
            element.textContent = originalText.replace(/\d+/, currentValue);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    updateActivity() {
        // Add new activity item
        const activityList = document.querySelector('.activity-list');
        if (!activityList) return;
        
        const newActivities = [
            { message: 'Nueva orden procesada', icon: 'shopping-cart', color: 'success' },
            { message: 'Usuario registrado', icon: 'user-plus', color: 'info' },
            { message: 'Producto actualizado', icon: 'edit', color: 'warning' },
            { message: 'Pago confirmado', icon: 'credit-card', color: 'success' }
        ];
        
        const activity = newActivities[Math.floor(Math.random() * newActivities.length)];
        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item slide-in-from-right';
        activityItem.innerHTML = `
            <div class="activity-icon ${activity.color}">
                <i class="fas fa-${activity.icon}"></i>
            </div>
            <div class="activity-content">
                <p>${activity.message}</p>
                <span class="activity-time">Ahora</span>
            </div>
        `;
        
        activityList.insertBefore(activityItem, activityList.firstChild);
        
        // Remove old items if more than 10
        while (activityList.children.length > 10) {
            activityList.removeChild(activityList.lastChild);
        }
    }

    refreshData() {
        const btn = document.getElementById('refresh-data-btn');
        if (!btn) return;
        
        // Show loading state
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<div class="loading-spinner"></div> Actualizando...';
        btn.disabled = true;
        
        // Simulate data refresh
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            // Update charts
            if (this.salesChart) this.salesChart.update();
            if (this.productsChart) this.productsChart.update();
            
            // Show success notification
            this.showNotification('Datos actualizados correctamente', 'success');
        }, 2000);
    }

    exportReport() {
        const btn = document.getElementById('export-report-btn');
        if (!btn) return;
        
        // Show loading state
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<div class="loading-spinner"></div> Exportando...';
        btn.disabled = true;
        
        // Simulate export
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            // Create download link
            const link = document.createElement('a');
            link.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent('Dashboard Report - ' + new Date().toISOString());
            link.download = 'dashboard-report.txt';
            link.click();
            
            this.showNotification('Reporte exportado correctamente', 'success');
        }, 2000);
    }

    updateDashboard() {
        const period = document.getElementById('time-period')?.value;
        if (!period) return;
        
        this.showNotification(`Actualizando datos para: ${period}`, 'info');
        
        // Simulate data update based on period
        setTimeout(() => {
            if (this.salesChart) {
                // Update chart data based on period
                this.salesChart.update();
            }
            
            this.showNotification('Dashboard actualizado', 'success');
        }, 1000);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">&times;</button>
        `;
        
        // Add notification styles
        if (!document.querySelector('.notification-styles')) {
            const style = document.createElement('style');
            style.className = 'notification-styles';
            style.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                    border-left: 4px solid;
                    padding: 16px;
                    z-index: 1060;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    min-width: 300px;
                    animation: slide-in-from-right 0.3s ease-out;
                }
                
                .notification-success { border-left-color: #10b981; }
                .notification-error { border-left-color: #ef4444; }
                .notification-info { border-left-color: #3b82f6; }
                .notification-warning { border-left-color: #f59e0b; }
                
                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    flex: 1;
                }
                
                .notification-success i { color: #10b981; }
                .notification-error i { color: #ef4444; }
                .notification-info i { color: #3b82f6; }
                .notification-warning i { color: #f59e0b; }
                
                .notification-close {
                    background: none;
                    border: none;
                    font-size: 18px;
                    cursor: pointer;
                    color: #6b7280;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                    transition: all 0.2s ease;
                }
                
                .notification-close:hover {
                    background: #f3f4f6;
                    color: #1f2937;
                }
            `;
            
            document.head.appendChild(style);
        }
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
        
        // Manual close
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Load saved theme
    const savedTheme = localStorage.getItem('dashboard-theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    }
    
    // Initialize dashboard
    window.dashboardPro = new DashboardPro();
});

// Add ripple animation CSS
const rippleCSS = document.createElement('style');
rippleCSS.textContent = `
    @keyframes ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
`;
document.head.appendChild(rippleCSS);

// Export for global access
window.DashboardPro = DashboardPro;