<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '📘 Facebook & Meta - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fab fa-facebook"></i> Facebook & Meta</h1>
                <p class="header-subtitle">Integración con Facebook, Instagram y WhatsApp</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-primary" onclick="connectFacebook()">
                    <i class="fas fa-link"></i>
                    Conectar Facebook
                </button>
            </div>
        </div>

        <div class="integrations-grid">
            <div class="integration-card">
                <div class="integration-header">
                    <i class="fab fa-facebook" style="color: #1877f2;"></i>
                    <h3>Facebook Shop</h3>
                    <span class="status-badge disconnected">Desconectado</span>
                </div>
                <p>Vende directamente en Facebook con tu catálogo sincronizado</p>
                <button class="integration-btn" onclick="setupFacebookShop()">Configurar</button>
            </div>

            <div class="integration-card">
                <div class="integration-header">
                    <i class="fab fa-instagram" style="color: #e4405f;"></i>
                    <h3>Instagram Shopping</h3>
                    <span class="status-badge disconnected">Desconectado</span>
                </div>
                <p>Etiqueta productos en tus posts de Instagram</p>
                <button class="integration-btn" onclick="setupInstagramShopping()">Configurar</button>
            </div>

            <div class="integration-card">
                <div class="integration-header">
                    <i class="fab fa-whatsapp" style="color: #25d366;"></i>
                    <h3>WhatsApp Business</h3>
                    <span class="status-badge connected">Conectado</span>
                </div>
                <p>Atiende consultas y ventas por WhatsApp</p>
                <button class="integration-btn secondary" onclick="manageWhatsApp()">Gestionar</button>
            </div>

            <div class="integration-card">
                <div class="integration-header">
                    <i class="fas fa-bullseye" style="color: #1877f2;"></i>
                    <h3>Facebook Ads</h3>
                    <span class="status-badge disconnected">Desconectado</span>
                </div>
                <p>Crea campañas publicitarias automáticas</p>
                <button class="integration-btn" onclick="setupFacebookAds()">Configurar</button>
            </div>
        </div>
    </div>
</div>

<script>
function connectFacebook() {
    alert('Conectar con Facebook - En desarrollo');
}

function setupFacebookShop() {
    alert('Configurar Facebook Shop - En desarrollo');
}

function setupInstagramShopping() {
    alert('Configurar Instagram Shopping - En desarrollo');
}

function manageWhatsApp() {
    alert('Gestionar WhatsApp Business - En desarrollo');
}

function setupFacebookAds() {
    alert('Configurar Facebook Ads - En desarrollo');
}
</script>

<style>
.integrations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    padding: 2rem;
}

.integration-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.integration-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.integration-header i {
    font-size: 1.5rem;
}

.integration-header h3 {
    flex: 1;
    margin: 0;
    color: #333;
}

.status-badge.connected {
    background: #d4edda;
    color: #155724;
}

.status-badge.disconnected {
    background: #f8d7da;
    color: #721c24;
}

.integration-card p {
    color: #666;
    margin-bottom: 1.5rem;
}

.integration-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
}

.integration-btn.secondary {
    background: #6c757d;
}
</style>

<style>
/* Optimización compacta para facebook-meta */
.integrations-grid { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important; gap: 1rem !important; padding: 1.5rem !important; }
.integration-card { padding: 1rem !important; }
.integration-header { margin-bottom: 0.75rem !important; gap: 0.75rem !important; }
.integration-header i { font-size: 1.2rem !important; }
.integration-header h3 { font-size: 1.1rem !important; }
.integration-card p { margin-bottom: 1rem !important; font-size: 0.85rem !important; }
.integration-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
.status-badge { padding: 0.2rem 0.5rem !important; font-size: 0.75rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.tn-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>