<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🏪 Punto de Venta - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-cash-register"></i> Punto de Venta (POS)</h1>
                <p class="header-subtitle">Sistema de ventas presenciales</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-primary" onclick="openPOS()">
                    <i class="fas fa-play"></i>
                    Abrir POS
                </button>
            </div>
        </div>

        <div class="pos-dashboard">
            <div class="pos-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$45,620</h3>
                        <p>Ventas de hoy</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3>38</h3>
                        <p>Transacciones</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$1,200</h3>
                        <p>Ticket promedio</p>
                    </div>
                </div>
            </div>

            <div class="pos-features">
                <div class="feature-card">
                    <i class="fas fa-barcode"></i>
                    <h3>Escáner de Códigos</h3>
                    <p>Escanea productos rápidamente</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-print"></i>
                    <h3>Impresión de Tickets</h3>
                    <p>Imprime recibos automáticamente</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-credit-card"></i>
                    <h3>Múltiples Pagos</h3>
                    <p>Acepta efectivo, tarjetas y más</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-sync"></i>
                    <h3>Sincronización</h3>
                    <p>Sincroniza con inventario online</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openPOS() {
    alert('Abriendo sistema POS - En desarrollo');
}
</script>

<style>
.pos-dashboard {
    padding: 2rem;
}

.pos-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.pos-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.feature-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.feature-card i {
    font-size: 2rem;
    color: #007bff;
    margin-bottom: 1rem;
}

.feature-card h3 {
    margin-bottom: 0.5rem;
    color: #333;
}

.feature-card p {
    color: #666;
    margin: 0;
}
</style>

<style>
/* Optimización compacta para pos */
.pos-dashboard { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.pos-stats { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important; gap: 1rem !important; margin-bottom: 1.5rem !important; }
.stat-card { padding: 1rem !important; gap: 0.75rem !important; }
.stat-icon { width: 45px !important; height: 45px !important; font-size: 1.2rem !important; }
.stat-content h3 { font-size: 1.5rem !important; }
.stat-content p { font-size: 0.85rem !important; }
.pos-features { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important; gap: 1rem !important; }
.feature-card { padding: 1.5rem !important; }
.feature-card i { font-size: 1.5rem !important; margin-bottom: 0.75rem !important; }
.feature-card h3 { font-size: 1.1rem !important; margin-bottom: 0.4rem !important; }
.feature-card p { font-size: 0.85rem !important; }
.tn-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>