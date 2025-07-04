<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '💳 Estadísticas de Pagos - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <!-- Main Content -->
    <div class="modern-admin-main">
        <!-- Header -->
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-credit-card"></i> Estadísticas de Pagos</h1>
                <p class="header-subtitle">Análisis detallado de métodos de pago y transacciones</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="exportPaymentStats()">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="time-filters">
            <div class="filter-group">
                <label>Período:</label>
                <div class="time-buttons">
                    <button class="time-btn" data-period="today">Hoy</button>
                    <button class="time-btn active" data-period="week">Esta semana</button>
                    <button class="time-btn" data-period="month">Este mes</button>
                    <button class="time-btn" data-period="year">Año</button>
                </div>
            </div>
        </div>

        <!-- Payment Stats Content -->
        <div class="stats-dashboard">
            <!-- Métricas Principales -->
            <section class="metrics-section">
                <h2>Resumen de Pagos</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon billing">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3>$247,890</h3>
                            <p>Total procesado</p>
                            <span class="stat-trend positive">+8.5%</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon sales">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="stat-content">
                            <h3>152</h3>
                            <p>Transacciones exitosas</p>
                            <span class="stat-trend positive">+12%</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon conversion">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-content">
                            <h3>96.2%</h3>
                            <p>Tasa de éxito</p>
                            <span class="stat-trend positive">+0.8%</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon ticket">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="stat-content">
                            <h3>$1,631</h3>
                            <p>Ticket promedio</p>
                            <span class="stat-trend negative">-3.2%</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Métodos de Pago -->
            <section class="payment-methods-section">
                <div class="metrics-row">
                    <div class="metric-card">
                        <h3>Métodos de Pago - Volumen</h3>
                        <div class="payment-methods">
                            <div class="payment-item">
                                <div class="payment-info">
                                    <i class="fas fa-credit-card"></i>
                                    <span class="payment-label">Tarjeta de Crédito</span>
                                </div>
                                <div class="payment-bar">
                                    <div class="payment-fill" style="width: 65%;"></div>
                                </div>
                                <div class="payment-stats">
                                    <span class="payment-percent">65%</span>
                                    <span class="payment-amount">$161,129</span>
                                </div>
                            </div>
                            <div class="payment-item">
                                <div class="payment-info">
                                    <i class="fas fa-university"></i>
                                    <span class="payment-label">Transferencia</span>
                                </div>
                                <div class="payment-bar">
                                    <div class="payment-fill" style="width: 25%;"></div>
                                </div>
                                <div class="payment-stats">
                                    <span class="payment-percent">25%</span>
                                    <span class="payment-amount">$61,973</span>
                                </div>
                            </div>
                            <div class="payment-item">
                                <div class="payment-info">
                                    <i class="fab fa-cc-mastercard"></i>
                                    <span class="payment-label">MercadoPago</span>
                                </div>
                                <div class="payment-bar">
                                    <div class="payment-fill" style="width: 10%;"></div>
                                </div>
                                <div class="payment-stats">
                                    <span class="payment-percent">10%</span>
                                    <span class="payment-amount">$24,789</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <h3>Cuotas - Distribución</h3>
                        <div class="installments-chart">
                            <canvas id="installmentsChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Transacciones Recientes -->
            <section class="recent-transactions">
                <div class="metric-card">
                    <h3>Transacciones Recientes</h3>
                    <div class="transactions-table">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Método</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#TXN-001</td>
                                    <td>Juan Pérez</td>
                                    <td><i class="fas fa-credit-card"></i> Tarjeta</td>
                                    <td>$2,450</td>
                                    <td><span class="status-badge success">Exitosa</span></td>
                                    <td>Hace 5 min</td>
                                </tr>
                                <tr>
                                    <td>#TXN-002</td>
                                    <td>María García</td>
                                    <td><i class="fas fa-university"></i> Transferencia</td>
                                    <td>$1,890</td>
                                    <td><span class="status-badge success">Exitosa</span></td>
                                    <td>Hace 12 min</td>
                                </tr>
                                <tr>
                                    <td>#TXN-003</td>
                                    <td>Carlos López</td>
                                    <td><i class="fab fa-cc-mastercard"></i> MercadoPago</td>
                                    <td>$890</td>
                                    <td><span class="status-badge pending">Pendiente</span></td>
                                    <td>Hace 18 min</td>
                                </tr>
                                <tr>
                                    <td>#TXN-004</td>
                                    <td>Ana Rodríguez</td>
                                    <td><i class="fas fa-credit-card"></i> Tarjeta</td>
                                    <td>$3,200</td>
                                    <td><span class="status-badge error">Rechazada</span></td>
                                    <td>Hace 25 min</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/js/modern-admin.js?v=<?php echo time(); ?>"></script>
<script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializePaymentCharts();
});

function initializePaymentCharts() {
    // Gráfico de cuotas
    const installmentsCtx = document.getElementById('installmentsChart').getContext('2d');
    new Chart(installmentsCtx, {
        type: 'doughnut',
        data: {
            labels: ['1 cuota', '3 cuotas', '6 cuotas', '12 cuotas', '18 cuotas'],
            datasets: [{
                data: [45, 25, 15, 10, 5],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6f42c1'
                ]
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

function exportPaymentStats() {
    alert('Exportando estadísticas de pagos...');
}
</script>

<style>
/* Optimización compacta para stats-payments */
.stats-dashboard { padding: 1.5rem; }
.metrics-section h2 { margin-bottom: 1rem; font-size: 1.3rem; }
.stats-grid { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { padding: 1rem; gap: 0.75rem; }
.stat-icon { width: 45px; height: 45px; font-size: 1.2rem; }
.stat-content h3 { font-size: 1.5rem; }
.stat-content p { font-size: 0.85rem; }
.stat-trend { font-size: 0.75rem; }
.payment-methods-section { margin: 1.5rem 0; }
.metrics-row { gap: 1.5rem; margin-bottom: 1.5rem; }
.metric-card { padding: 1rem; }
.metric-card h3 { margin-bottom: 1rem; font-size: 1.1rem; }
.payment-item { margin-bottom: 0.75rem; gap: 0.75rem; }
.payment-info { display: flex; align-items: center; gap: 0.5rem; min-width: 130px; }
.payment-info i { width: 20px; color: #007bff; }
.payment-label { font-size: 0.85rem; color: #666; }
.payment-bar { flex: 1; height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden; }
.payment-fill { height: 100%; background: #007bff; }
.payment-stats { display: flex; gap: 0.5rem; align-items: center; }
.payment-percent { font-weight: 600; font-size: 0.85rem; color: #333; min-width: 40px; }
.payment-amount { font-size: 0.8rem; color: #666; min-width: 60px; }
.installments-chart { height: 200px; }
.recent-transactions { margin-top: 1.5rem; }
.transactions-table { max-height: 300px; overflow-y: auto; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 0.5rem; border-bottom: 1px solid #eee; text-align: left; }
.admin-table th { background: #f8f9fa; font-weight: 600; font-size: 0.9rem; }
.admin-table td { font-size: 0.85rem; }
.status-badge { padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.7rem; font-weight: 600; }
.status-badge.success { background: #d4edda; color: #155724; }
.status-badge.error { background: #f8d7da; color: #721c24; }
.status-badge.pending { background: #fff3cd; color: #856404; }
</style>

</body>
</html>