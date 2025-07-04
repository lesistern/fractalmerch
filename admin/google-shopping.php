<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🛍️ Google Shopping - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fab fa-google"></i> Google Shopping</h1>
                <p class="header-subtitle">Integración con Google Merchant Center y Google Ads</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-primary" onclick="connectGoogle()">
                    <i class="fas fa-link"></i>
                    Conectar Google
                </button>
            </div>
        </div>

        <div class="google-integrations">
            <div class="integration-card">
                <div class="integration-header">
                    <i class="fab fa-google" style="color: #4285f4;"></i>
                    <h3>Google Merchant Center</h3>
                    <span class="status-badge disconnected">Desconectado</span>
                </div>
                <p>Sincroniza tu catálogo con Google Shopping</p>
                <div class="integration-features">
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Sincronización automática de productos</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Actualización de precios en tiempo real</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Gestión de inventario</span>
                    </div>
                </div>
                <button class="integration-btn" onclick="setupMerchantCenter()">Configurar</button>
            </div>

            <div class="integration-card">
                <div class="integration-header">
                    <i class="fab fa-google" style="color: #34a853;"></i>
                    <h3>Google Ads</h3>
                    <span class="status-badge disconnected">Desconectado</span>
                </div>
                <p>Crea campañas de Shopping automáticas</p>
                <div class="integration-features">
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Campañas automáticas de Shopping</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Seguimiento de conversiones</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Optimización inteligente</span>
                    </div>
                </div>
                <button class="integration-btn" onclick="setupGoogleAds()">Configurar</button>
            </div>

            <div class="integration-card">
                <div class="integration-header">
                    <i class="fas fa-chart-line" style="color: #ea4335;"></i>
                    <h3>Google Analytics</h3>
                    <span class="status-badge connected">Conectado</span>
                </div>
                <p>Analiza el rendimiento de tus productos</p>
                <div class="integration-features">
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Seguimiento de ecommerce</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Reportes de ventas</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Análisis de audiencia</span>
                    </div>
                </div>
                <button class="integration-btn secondary" onclick="manageAnalytics()">Gestionar</button>
            </div>
        </div>
    </div>
</div>

<script>
function connectGoogle() {
    alert('Conectar con Google - En desarrollo');
}

function setupMerchantCenter() {
    alert('Configurar Google Merchant Center - En desarrollo');
}

function setupGoogleAds() {
    alert('Configurar Google Ads - En desarrollo');
}

function manageAnalytics() {
    alert('Gestionar Google Analytics - En desarrollo');
}
</script>

<style>
.google-integrations {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    padding: 2rem;
}

.integration-features {
    margin: 1rem 0;
    padding: 1rem 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}

.feature-item i {
    color: #28a745;
    font-size: 0.8rem;
}
</style>

<style>
/* Optimización compacta para google-shopping */
.google-integrations { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important; gap: 1.5rem !important; padding: 1.5rem !important; }
.integration-card { padding: 1rem !important; }
.integration-header { margin-bottom: 0.75rem !important; gap: 0.75rem !important; }
.integration-header i { font-size: 1.2rem !important; }
.integration-header h3 { font-size: 1.1rem !important; }
.integration-card p { margin-bottom: 1rem !important; font-size: 0.85rem !important; }
.integration-features { margin: 0.75rem 0 !important; padding: 0.75rem 0 !important; }
.feature-item { margin-bottom: 0.4rem !important; font-size: 0.8rem !important; gap: 0.4rem !important; }
.feature-item i { font-size: 0.7rem !important; }
.integration-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
.status-badge { padding: 0.2rem 0.5rem !important; font-size: 0.75rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.tn-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>