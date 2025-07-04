<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🏪 Marketplaces - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-store"></i> Marketplaces</h1>
                <p class="header-subtitle">Integración con marketplaces populares</p>
            </div>
        </div>

        <div class="marketplaces-grid">
            <div class="marketplace-card">
                <div class="marketplace-header">
                    <img src="https://logoeps.com/wp-content/uploads/2013/03/mercado-libre-vector-logo.png" alt="MercadoLibre" class="marketplace-logo">
                    <h3>MercadoLibre</h3>
                    <span class="status-badge disconnected">Desconectado</span>
                </div>
                <p>Vende en el marketplace más grande de Latinoamérica</p>
                <div class="marketplace-stats">
                    <div class="stat-item">
                        <span class="stat-number">-</span>
                        <span class="stat-label">Productos publicados</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">-</span>
                        <span class="stat-label">Ventas este mes</span>
                    </div>
                </div>
                <button class="marketplace-btn" onclick="connectMercadoLibre()">Conectar</button>
            </div>

            <div class="marketplace-card">
                <div class="marketplace-header">
                    <img src="https://logoeps.com/wp-content/uploads/2013/03/amazon-vector-logo.png" alt="Amazon" class="marketplace-logo">
                    <h3>Amazon</h3>
                    <span class="status-badge disconnected">Desconectado</span>
                </div>
                <p>Expande tu alcance al marketplace global</p>
                <div class="marketplace-stats">
                    <div class="stat-item">
                        <span class="stat-number">-</span>
                        <span class="stat-label">Productos publicados</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">-</span>
                        <span class="stat-label">Ventas este mes</span>
                    </div>
                </div>
                <button class="marketplace-btn" onclick="connectAmazon()">Conectar</button>
            </div>

            <div class="marketplace-card">
                <div class="marketplace-header">
                    <div class="marketplace-logo-placeholder" style="background: #ff6900; color: white;">F</div>
                    <h3>Falabella</h3>
                    <span class="status-badge connected">Conectado</span>
                </div>
                <p>Marketplace líder en retail en Argentina</p>
                <div class="marketplace-stats">
                    <div class="stat-item">
                        <span class="stat-number">45</span>
                        <span class="stat-label">Productos publicados</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">12</span>
                        <span class="stat-label">Ventas este mes</span>
                    </div>
                </div>
                <button class="marketplace-btn secondary" onclick="manageFalabella()">Gestionar</button>
            </div>

            <div class="marketplace-card">
                <div class="marketplace-header">
                    <div class="marketplace-logo-placeholder" style="background: #e60012; color: white;">G</div>
                    <h3>Garbarino</h3>
                    <span class="status-badge disconnected">Desconectado</span>
                </div>
                <p>Marketplace de tecnología y electrodomésticos</p>
                <div class="marketplace-stats">
                    <div class="stat-item">
                        <span class="stat-number">-</span>
                        <span class="stat-label">Productos publicados</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">-</span>
                        <span class="stat-label">Ventas este mes</span>
                    </div>
                </div>
                <button class="marketplace-btn" onclick="connectGarbarino()">Conectar</button>
            </div>
        </div>
    </div>
</div>

<script>
function connectMercadoLibre() {
    alert('Conectar con MercadoLibre - En desarrollo');
}

function connectAmazon() {
    alert('Conectar con Amazon - En desarrollo');
}

function manageFalabella() {
    alert('Gestionar Falabella - En desarrollo');
}

function connectGarbarino() {
    alert('Conectar con Garbarino - En desarrollo');
}
</script>

<style>
.marketplaces-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    padding: 2rem;
}

.marketplace-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.marketplace-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.marketplace-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.marketplace-logo-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.marketplace-header h3 {
    flex: 1;
    margin: 0;
    color: #333;
}

.marketplace-card p {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.marketplace-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #007bff;
}

.stat-label {
    font-size: 0.8rem;
    color: #666;
}

.marketplace-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    font-weight: 600;
}

.marketplace-btn.secondary {
    background: #6c757d;
}

.marketplace-btn:hover {
    opacity: 0.9;
}
</style>

<style>
/* Optimización compacta para marketplaces */
.marketplaces-grid { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important; gap: 1rem !important; padding: 1.5rem !important; }
.marketplace-card { padding: 1rem !important; }
.marketplace-header { margin-bottom: 0.75rem !important; gap: 0.75rem !important; }
.marketplace-logo { width: 35px !important; height: 35px !important; }
.marketplace-logo-placeholder { width: 35px !important; height: 35px !important; font-size: 1rem !important; }
.marketplace-header h3 { font-size: 1.1rem !important; }
.marketplace-card p { margin-bottom: 1rem !important; font-size: 0.85rem !important; line-height: 1.4 !important; }
.marketplace-stats { gap: 0.75rem !important; margin-bottom: 1rem !important; padding: 0.75rem !important; }
.stat-number { font-size: 1.2rem !important; }
.stat-label { font-size: 0.75rem !important; }
.marketplace-btn { padding: 0.5rem 1rem !important; font-weight: 500 !important; font-size: 0.85rem !important; }
.status-badge { padding: 0.2rem 0.5rem !important; font-size: 0.75rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
</style>

</body>
</html>