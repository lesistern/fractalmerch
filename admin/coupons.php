<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🎫 Cupones de Descuento - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-ticket-alt"></i> Cupones de Descuento</h1>
                <p class="header-subtitle">Gestión de cupones y códigos promocionales</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-primary" onclick="createCoupon()">
                    <i class="fas fa-plus"></i>
                    Crear Cupón
                </button>
                <button class="tn-btn tn-btn-secondary" onclick="exportCoupons()">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
            </div>
        </div>

        <div class="coupons-stats">
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-content">
                    <h3>15</h3>
                    <p>Cupones activos</p>
                    <span class="stat-trend positive">+3 este mes</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon used">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h3>248</h3>
                    <p>Usos totales</p>
                    <span class="stat-trend positive">+45 este mes</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon savings">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>$12,450</h3>
                    <p>Descuentos otorgados</p>
                    <span class="stat-trend neutral">Este mes</span>
                </div>
            </div>
        </div>

        <div class="coupons-table-container">
            <table class="coupons-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Descuento</th>
                        <th>Usos</th>
                        <th>Válido hasta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="coupon-code">
                                <span class="code-text">VERANO2024</span>
                                <button class="copy-btn" onclick="copyCouponCode('VERANO2024')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td><span class="coupon-type percentage">Porcentaje</span></td>
                        <td><span class="discount-value">20%</span></td>
                        <td>
                            <div class="usage-info">
                                <span class="usage-count">45/100</span>
                                <div class="usage-bar">
                                    <div class="usage-fill" style="width: 45%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>31/12/2024</td>
                        <td><span class="status-badge active">Activo</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn edit" onclick="editCoupon('VERANO2024')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn pause" onclick="pauseCoupon('VERANO2024')">
                                    <i class="fas fa-pause"></i>
                                </button>
                                <button class="action-btn delete" onclick="deleteCoupon('VERANO2024')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="coupon-code">
                                <span class="code-text">ENVIOGRATIS</span>
                                <button class="copy-btn" onclick="copyCouponCode('ENVIOGRATIS')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td><span class="coupon-type shipping">Envío gratis</span></td>
                        <td><span class="discount-value">100%</span></td>
                        <td>
                            <div class="usage-info">
                                <span class="usage-count">23/50</span>
                                <div class="usage-bar">
                                    <div class="usage-fill" style="width: 46%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>15/01/2025</td>
                        <td><span class="status-badge active">Activo</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn edit" onclick="editCoupon('ENVIOGRATIS')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn pause" onclick="pauseCoupon('ENVIOGRATIS')">
                                    <i class="fas fa-pause"></i>
                                </button>
                                <button class="action-btn delete" onclick="deleteCoupon('ENVIOGRATIS')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="coupon-code">
                                <span class="code-text">PRIMERACOMPRA</span>
                                <button class="copy-btn" onclick="copyCouponCode('PRIMERACOMPRA')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td><span class="coupon-type fixed">Monto fijo</span></td>
                        <td><span class="discount-value">$1,000</span></td>
                        <td>
                            <div class="usage-info">
                                <span class="usage-count">12/∞</span>
                                <div class="usage-bar">
                                    <div class="usage-fill unlimited" style="width: 100%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>∞</td>
                        <td><span class="status-badge active">Activo</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn edit" onclick="editCoupon('PRIMERACOMPRA')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn pause" onclick="pauseCoupon('PRIMERACOMPRA')">
                                    <i class="fas fa-pause"></i>
                                </button>
                                <button class="action-btn delete" onclick="deleteCoupon('PRIMERACOMPRA')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function createCoupon() {
    alert('Crear nuevo cupón - Modal en desarrollo');
}

function copyCouponCode(code) {
    navigator.clipboard.writeText(code);
    alert('Código copiado: ' + code);
}

function editCoupon(code) {
    alert('Editar cupón: ' + code);
}

function pauseCoupon(code) {
    alert('Pausar cupón: ' + code);
}

function deleteCoupon(code) {
    if (confirm('¿Eliminar cupón ' + code + '?')) {
        alert('Cupón eliminado');
    }
}

function exportCoupons() {
    alert('Exportando cupones...');
}
</script>

<style>
.coupons-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-icon.active { background: #28a745; }
.stat-icon.used { background: #007bff; }
.stat-icon.savings { background: #ffc107; }

.coupons-table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.coupons-table {
    width: 100%;
    border-collapse: collapse;
}

.coupons-table th,
.coupons-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.coupons-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.coupon-code {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.code-text {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    color: #007bff;
}

.copy-btn {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 0.25rem;
}

.copy-btn:hover {
    color: #007bff;
}

.coupon-type {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.coupon-type.percentage {
    background: #e3f2fd;
    color: #1976d2;
}

.coupon-type.shipping {
    background: #e8f5e8;
    color: #2e7d32;
}

.coupon-type.fixed {
    background: #fff3e0;
    color: #f57c00;
}

.discount-value {
    font-weight: 700;
    font-size: 1.1rem;
    color: #28a745;
}

.usage-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.usage-count {
    font-size: 0.9rem;
    color: #666;
}

.usage-bar {
    width: 60px;
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
}

.usage-fill {
    height: 100%;
    background: #007bff;
    transition: width 0.3s ease;
}

.usage-fill.unlimited {
    background: #28a745;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-btn.edit {
    background: #fff3e0;
    color: #f57c00;
}

.action-btn.pause {
    background: #e3f2fd;
    color: #1976d2;
}

.action-btn.delete {
    background: #ffebee;
    color: #c62828;
}

/* Optimización compacta para coupons.php */
.modern-admin-main { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.coupons-stats { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important; gap: 1rem !important; margin-bottom: 1.5rem !important; }
.stat-card { padding: 1rem !important; gap: 0.75rem !important; }
.stat-icon { width: 45px !important; height: 45px !important; font-size: 1.2rem !important; }
.stat-content h3 { font-size: 1.5rem !important; }
.stat-content p { font-size: 0.85rem !important; }
.stat-trend { font-size: 0.75rem !important; }
.coupons-table-container { margin-top: 1.5rem !important; }
.coupons-table th { padding: 0.75rem !important; font-size: 0.9rem !important; }
.coupons-table td { padding: 0.75rem !important; font-size: 0.85rem !important; }
.coupon-code { gap: 0.4rem !important; }
.code-text { padding: 0.2rem 0.4rem !important; font-size: 0.8rem !important; }
.copy-btn { padding: 0.2rem !important; font-size: 0.75rem !important; }
.coupon-type { padding: 0.2rem 0.6rem !important; font-size: 0.75rem !important; }
.discount-value { font-size: 1rem !important; }
.usage-info { gap: 0.2rem !important; }
.usage-count { font-size: 0.8rem !important; }
.usage-bar { width: 50px !important; height: 3px !important; }
.status-badge { padding: 0.2rem 0.6rem !important; font-size: 0.75rem !important; }
.action-btn { width: 28px !important; height: 28px !important; font-size: 0.75rem !important; }
.tn-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>