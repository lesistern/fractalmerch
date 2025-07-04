<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '📢 Marketing - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-bullhorn"></i> Marketing</h1>
                <p class="header-subtitle">Centro integral de marketing y promociones</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-primary" onclick="createCampaign()">
                    <i class="fas fa-plus"></i>
                    Nueva Campaña
                </button>
            </div>
        </div>

        <!-- Estadísticas de Marketing -->
        <section class="marketing-stats-section">
            <h2><i class="fas fa-chart-bar"></i> Resumen de Marketing</h2>
            <div class="marketing-stats-grid">
                <div class="marketing-stat-card primary">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-content">
                        <h3>8.5K</h3>
                        <p>Emails enviados</p>
                        <span class="stat-change positive">+12%</span>
                    </div>
                </div>
                <div class="marketing-stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <div class="stat-content">
                        <h3>23.5%</h3>
                        <p>Tasa de apertura</p>
                        <span class="stat-change positive">+5.2%</span>
                    </div>
                </div>
                <div class="marketing-stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3>4.8%</h3>
                        <p>Conversión</p>
                        <span class="stat-change positive">+1.3%</span>
                    </div>
                </div>
                <div class="marketing-stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$45.2K</h3>
                        <p>ROI Marketing</p>
                        <span class="stat-change positive">+8.7%</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Herramientas de Marketing Principal -->
        <section class="marketing-tools-section">
            <h2><i class="fas fa-tools"></i> Herramientas de Marketing</h2>
            
            <!-- Cupones y Promociones -->
            <div class="marketing-category">
                <h3><i class="fas fa-tags"></i> Cupones y Promociones</h3>
                <div class="marketing-tools-grid">
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-percentage"></i>
                            <h4>Cupones de Descuento</h4>
                        </div>
                        <p>Crea cupones con descuentos porcentuales o fijos</p>
                        <div class="tool-stats">
                            <span class="tool-stat"><strong>3</strong> activos</span>
                            <span class="tool-stat"><strong>234</strong> usos</span>
                        </div>
                        <button class="tool-btn primary" onclick="manageCoupons()">Gestionar Cupones</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-gift"></i>
                            <h4>Lleva X, Paga Y</h4>
                        </div>
                        <p>Promociones de cantidad: Lleva 2, paga 1</p>
                        <div class="tool-stats">
                            <span class="tool-stat"><strong>1</strong> activa</span>
                            <span class="tool-stat"><strong>89</strong> conversiones</span>
                        </div>
                        <button class="tool-btn primary" onclick="manageQuantityPromos()">Configurar</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-credit-card"></i>
                            <h4>Gift Cards</h4>
                        </div>
                        <p>Vende tarjetas de regalo para tu tienda</p>
                        <div class="tool-stats">
                            <span class="tool-stat"><strong>$2.5K</strong> vendidas</span>
                            <span class="tool-stat"><strong>45</strong> activas</span>
                        </div>
                        <button class="tool-btn primary" onclick="manageGiftCards()">Ver Gift Cards</button>
                    </div>
                </div>
            </div>

            <!-- Email Marketing -->
            <div class="marketing-category">
                <h3><i class="fas fa-envelope-open"></i> Email Marketing</h3>
                <div class="marketing-tools-grid">
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fab fa-mailchimp"></i>
                            <h4>Mailchimp Integration</h4>
                        </div>
                        <p>Conecta tu tienda con Mailchimp para campañas avanzadas</p>
                        <div class="tool-status connected">
                            <i class="fas fa-check-circle"></i> Conectado
                        </div>
                        <button class="tool-btn secondary" onclick="configMailchimp()">Configurar</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-rocket"></i>
                            <h4>Marketing Nube</h4>
                        </div>
                        <p>Herramienta nativa de marketing y automatización</p>
                        <div class="tool-stats">
                            <span class="tool-stat"><strong>5</strong> automatizaciones</span>
                            <span class="tool-stat"><strong>1.2K</strong> contactos</span>
                        </div>
                        <button class="tool-btn primary" onclick="openMarketingNube()">Abrir Marketing Nube</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-users"></i>
                            <h4>Listas de Contactos</h4>
                        </div>
                        <p>Gestiona y segmenta tu base de clientes</p>
                        <div class="tool-stats">
                            <span class="tool-stat"><strong>1,234</strong> contactos</span>
                            <span class="tool-stat"><strong>8</strong> listas</span>
                        </div>
                        <button class="tool-btn primary" onclick="manageContacts()">Gestionar Listas</button>
                    </div>
                </div>
            </div>

            <!-- Redes Sociales -->
            <div class="marketing-category">
                <h3><i class="fas fa-share-alt"></i> Redes Sociales</h3>
                <div class="marketing-tools-grid">
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fab fa-facebook"></i>
                            <h4>Facebook Business</h4>
                        </div>
                        <p>Conecta tu tienda con Facebook e Instagram</p>
                        <div class="tool-status connected">
                            <i class="fas fa-check-circle"></i> Conectado
                        </div>
                        <button class="tool-btn secondary" onclick="manageFacebook()">Gestionar</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fab fa-instagram"></i>
                            <h4>Instagram Shopping</h4>
                        </div>
                        <p>Vende directamente desde Instagram</p>
                        <div class="tool-stats">
                            <span class="tool-stat"><strong>156</strong> productos</span>
                            <span class="tool-stat"><strong>2.3K</strong> seguidores</span>
                        </div>
                        <button class="tool-btn primary" onclick="manageInstagram()">Configurar</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fab fa-pinterest"></i>
                            <h4>Pinterest</h4>
                        </div>
                        <p>Añade iconos de Pinterest a tu tienda</p>
                        <div class="tool-status disconnected">
                            <i class="fas fa-times-circle"></i> Desconectado
                        </div>
                        <button class="tool-btn primary" onclick="connectPinterest()">Conectar</button>
                    </div>
                </div>
            </div>

            <!-- Google Ads y Shopping -->
            <div class="marketing-category">
                <h3><i class="fab fa-google"></i> Google Marketing</h3>
                <div class="marketing-tools-grid">
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fab fa-google"></i>
                            <h4>Google Shopping</h4>
                        </div>
                        <p>Sincroniza tu catálogo con Google Shopping</p>
                        <div class="tool-status connected">
                            <i class="fas fa-check-circle"></i> Sincronizado
                        </div>
                        <button class="tool-btn secondary" onclick="manageGoogleShopping()">Ver Catálogo</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-bullseye"></i>
                            <h4>Google Ads</h4>
                        </div>
                        <p>Crea y monitorea campañas de Google Ads</p>
                        <div class="tool-stats">
                            <span class="tool-stat"><strong>2</strong> campañas</span>
                            <span class="tool-stat"><strong>$234</strong> invertidos</span>
                        </div>
                        <button class="tool-btn primary" onclick="manageGoogleAds()">Gestionar Campañas</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-chart-line"></i>
                            <h4>Google Analytics</h4>
                        </div>
                        <p>Análisis avanzado de tráfico y conversiones</p>
                        <div class="tool-status connected">
                            <i class="fas fa-check-circle"></i> Conectado
                        </div>
                        <button class="tool-btn secondary" onclick="openAnalytics()">Ver Reportes</button>
                    </div>
                </div>
            </div>

            <!-- Facebook Ads -->
            <div class="marketing-category">
                <h3><i class="fab fa-facebook-square"></i> Facebook Ads</h3>
                <div class="marketing-tools-grid">
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-crosshairs"></i>
                            <h4>Facebook Pixel</h4>
                        </div>
                        <p>Trackea conversiones y optimiza anuncios</p>
                        <div class="tool-status connected">
                            <i class="fas fa-check-circle"></i> Instalado
                        </div>
                        <button class="tool-btn secondary" onclick="manageFacebookPixel()">Configurar</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-ad"></i>
                            <h4>Campañas Facebook</h4>
                        </div>
                        <p>Administra tus campañas de Facebook Ads</p>
                        <div class="tool-stats">
                            <span class="tool-stat"><strong>3</strong> campañas</span>
                            <span class="tool-stat"><strong>4.2%</strong> CTR</span>
                        </div>
                        <button class="tool-btn primary" onclick="manageFacebookAds()">Ver Campañas</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-users-cog"></i>
                            <h4>Audiencias Custom</h4>
                        </div>
                        <p>Crea audiencias personalizadas para tus anuncios</p>
                        <div class="tool-stats">
                            <span class="tool-stat"><strong>5</strong> audiencias</span>
                            <span class="tool-stat"><strong>12K</strong> usuarios</span>
                        </div>
                        <button class="tool-btn primary" onclick="manageAudiences()">Gestionar</button>
                    </div>
                </div>
            </div>

            <!-- Fechas Especiales -->
            <div class="marketing-category">
                <h3><i class="fas fa-calendar-star"></i> Fechas Especiales</h3>
                <div class="marketing-tools-grid">
                    <div class="marketing-tool-card special">
                        <div class="tool-header">
                            <i class="fas fa-fire"></i>
                            <h4>Hot Sale 2025</h4>
                        </div>
                        <p>Prepara tu tienda para el Hot Sale</p>
                        <div class="countdown-timer">
                            <span id="hotsale-countdown">45 días restantes</span>
                        </div>
                        <button class="tool-btn primary" onclick="prepareHotSale()">Preparar Hot Sale</button>
                    </div>
                    
                    <div class="marketing-tool-card special">
                        <div class="tool-header">
                            <i class="fas fa-laptop"></i>
                            <h4>Cyber Monday</h4>
                        </div>
                        <p>Estrategias para Cyber Monday</p>
                        <div class="tool-stats">
                            <span class="tool-stat">Próximo: <strong>Nov 2025</strong></span>
                        </div>
                        <button class="tool-btn secondary" onclick="prepareCyberMonday()">Planificar</button>
                    </div>
                    
                    <div class="marketing-tool-card special">
                        <div class="tool-header">
                            <i class="fas fa-heart"></i>
                            <h4>San Valentín</h4>
                        </div>
                        <p>Promociones especiales para San Valentín</p>
                        <div class="tool-stats">
                            <span class="tool-stat">Próximo: <strong>Feb 2026</strong></span>
                        </div>
                        <button class="tool-btn secondary" onclick="prepareValentines()">Configurar</button>
                    </div>
                </div>
            </div>

            <!-- Aplicaciones de Marketing -->
            <div class="marketing-category">
                <h3><i class="fas fa-puzzle-piece"></i> Apps de Marketing</h3>
                <div class="marketing-tools-grid">
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-envelope-square"></i>
                            <h4>Doppler</h4>
                        </div>
                        <p>Email marketing y automatización avanzada</p>
                        <div class="app-rating">
                            <span class="stars">★★★★★</span>
                            <span class="rating-text">4.8 (234)</span>
                        </div>
                        <button class="tool-btn primary" onclick="installDoppler()">Instalar</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-bell"></i>
                            <h4>TitanPush</h4>
                        </div>
                        <p>Notificaciones push y promociones</p>
                        <div class="tool-status connected">
                            <i class="fas fa-check-circle"></i> Instalado
                        </div>
                        <button class="tool-btn secondary" onclick="manageTitanPush()">Configurar</button>
                    </div>
                    
                    <div class="marketing-tool-card">
                        <div class="tool-header">
                            <i class="fas fa-lightbulb"></i>
                            <h4>SmartHint</h4>
                        </div>
                        <p>Inteligencia artificial para recomendaciones</p>
                        <div class="app-rating">
                            <span class="stars">★★★★☆</span>
                            <span class="rating-text">4.5 (156)</span>
                        </div>
                        <button class="tool-btn primary" onclick="installSmartHint()">Instalar</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Campañas Recientes -->
        <section class="recent-campaigns-section">
            <h2><i class="fas fa-history"></i> Campañas Recientes</h2>
            <div class="campaigns-table-container">
                <table class="campaigns-table">
                    <thead>
                        <tr>
                            <th>Campaña</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Alcance</th>
                            <th>Conversión</th>
                            <th>ROI</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="campaign-info">
                                    <strong>Descuento Verano 2025</strong>
                                    <span class="campaign-date">Iniciada el 15 Jun</span>
                                </div>
                            </td>
                            <td><span class="campaign-type email">Email</span></td>
                            <td><span class="campaign-status active">Activa</span></td>
                            <td>2,345</td>
                            <td>6.8%</td>
                            <td class="positive">+245%</td>
                            <td>
                                <button class="action-btn edit" onclick="editCampaign(1)"><i class="fas fa-edit"></i></button>
                                <button class="action-btn pause" onclick="pauseCampaign(1)"><i class="fas fa-pause"></i></button>
                                <button class="action-btn stats" onclick="viewStats(1)"><i class="fas fa-chart-bar"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="campaign-info">
                                    <strong>Retargeting Facebook</strong>
                                    <span class="campaign-date">Iniciada el 10 Jun</span>
                                </div>
                            </td>
                            <td><span class="campaign-type social">Social</span></td>
                            <td><span class="campaign-status active">Activa</span></td>
                            <td>8,921</td>
                            <td>3.2%</td>
                            <td class="positive">+156%</td>
                            <td>
                                <button class="action-btn edit" onclick="editCampaign(2)"><i class="fas fa-edit"></i></button>
                                <button class="action-btn pause" onclick="pauseCampaign(2)"><i class="fas fa-pause"></i></button>
                                <button class="action-btn stats" onclick="viewStats(2)"><i class="fas fa-chart-bar"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="campaign-info">
                                    <strong>Google Shopping Boost</strong>
                                    <span class="campaign-date">Finalizada el 5 Jun</span>
                                </div>
                            </td>
                            <td><span class="campaign-type ads">Google Ads</span></td>
                            <td><span class="campaign-status completed">Finalizada</span></td>
                            <td>12,567</td>
                            <td>4.5%</td>
                            <td class="positive">+312%</td>
                            <td>
                                <button class="action-btn duplicate" onclick="duplicateCampaign(3)"><i class="fas fa-copy"></i></button>
                                <button class="action-btn stats" onclick="viewStats(3)"><i class="fas fa-chart-bar"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
// Funciones principales de marketing
function createCampaign() {
    alert('Crear nueva campaña - Redirigiendo al asistente de campañas');
}

// Cupones y Promociones
function manageCoupons() {
    window.location.href = 'coupons.php';
}

function manageQuantityPromos() {
    alert('Gestionar promociones por cantidad - En desarrollo');
}

function manageGiftCards() {
    alert('Gestionar Gift Cards - En desarrollo');
}

// Email Marketing
function configMailchimp() {
    window.location.href = 'email-marketing.php';
}

function openMarketingNube() {
    window.location.href = 'email-marketing.php';
}

function manageContacts() {
    window.location.href = 'email-marketing.php#contacts';
}

// Redes Sociales
function manageFacebook() {
    window.location.href = 'social-media.php';
}

function manageInstagram() {
    window.location.href = 'social-media.php#instagram';
}

function connectPinterest() {
    window.location.href = 'social-media.php#pinterest';
}

// Google Marketing
function manageGoogleShopping() {
    window.location.href = 'google-shopping.php';
}

function manageGoogleAds() {
    window.location.href = 'google-ads.php';
}

function openAnalytics() {
    window.open('https://analytics.google.com', '_blank');
}

// Facebook Ads
function manageFacebookPixel() {
    window.location.href = 'facebook-meta.php';
}

function manageFacebookAds() {
    alert('Gestionar campañas de Facebook Ads - En desarrollo');
}

function manageAudiences() {
    alert('Gestionar audiencias personalizadas - En desarrollo');
}

// Fechas Especiales
function prepareHotSale() {
    alert('Preparar tienda para Hot Sale - Guía paso a paso');
}

function prepareCyberMonday() {
    alert('Planificar estrategia para Cyber Monday - En desarrollo');
}

function prepareValentines() {
    alert('Configurar promociones de San Valentín - En desarrollo');
}

// Apps de Marketing
function installDoppler() {
    alert('Instalar Doppler - Redirigiendo a la app store');
}

function manageTitanPush() {
    alert('Configurar TitanPush - En desarrollo');
}

function installSmartHint() {
    alert('Instalar SmartHint - Redirigiendo a la app store');
}

// Gestión de Campañas
function editCampaign(id) {
    alert('Editar campaña ID: ' + id);
}

function pauseCampaign(id) {
    if (confirm('¿Pausar campaña ID: ' + id + '?')) {
        alert('Campaña pausada');
    }
}

function viewStats(id) {
    alert('Ver estadísticas de campaña ID: ' + id);
}

function duplicateCampaign(id) {
    alert('Duplicar campaña ID: ' + id);
}

// Countdown timer para Hot Sale
function updateCountdown() {
    const hotsaleDate = new Date('2025-05-19'); // Fecha estimada Hot Sale 2025
    const now = new Date();
    const timeDiff = hotsaleDate - now;
    const daysLeft = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
    
    const countdownElement = document.getElementById('hotsale-countdown');
    if (countdownElement) {
        if (daysLeft > 0) {
            countdownElement.textContent = daysLeft + ' días restantes';
        } else {
            countdownElement.textContent = '¡Hot Sale activo!';
        }
    }
}

// Inicializar countdown
document.addEventListener('DOMContentLoaded', function() {
    updateCountdown();
});
</script>

<style>
/* Estilos para la página de Marketing */
.marketing-stats-section {
    margin-bottom: 2rem;
}

.marketing-stats-section h2 {
    margin-bottom: 1.5rem;
    color: #333;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.marketing-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.marketing-stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    border-left: 4px solid #007bff;
}

.marketing-stat-card.primary { border-left-color: #007bff; }
.marketing-stat-card.success { border-left-color: #28a745; }
.marketing-stat-card.warning { border-left-color: #ffc107; }
.marketing-stat-card.info { border-left-color: #17a2b8; }

.marketing-stat-card .stat-icon {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 50%;
    font-size: 1.5rem;
    color: #007bff;
}

.marketing-stat-card.success .stat-icon { color: #28a745; }
.marketing-stat-card.warning .stat-icon { color: #ffc107; }
.marketing-stat-card.info .stat-icon { color: #17a2b8; }

.marketing-stat-card .stat-content h3 {
    font-size: 2rem;
    font-weight: bold;
    margin: 0;
    color: #333;
}

.marketing-stat-card .stat-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.stat-change {
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
}

.stat-change.positive {
    background: #d4edda;
    color: #155724;
}

.marketing-tools-section {
    margin-bottom: 2rem;
}

.marketing-tools-section h2 {
    margin-bottom: 2rem;
    color: #333;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.marketing-category {
    margin-bottom: 3rem;
}

.marketing-category h3 {
    margin-bottom: 1.5rem;
    color: #444;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.marketing-tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.marketing-tool-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.marketing-tool-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.marketing-tool-card.special {
    background: linear-gradient(135deg, #ff6b6b, #feca57);
    color: white;
}

.marketing-tool-card.special .tool-header h4,
.marketing-tool-card.special p {
    color: white;
}

.tool-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.tool-header i {
    font-size: 1.5rem;
    color: #007bff;
}

.marketing-tool-card.special .tool-header i {
    color: white;
}

.tool-header h4 {
    margin: 0;
    color: #333;
    font-size: 1.1rem;
}

.marketing-tool-card p {
    color: #666;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    line-height: 1.5;
}

.tool-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.tool-stat {
    font-size: 0.8rem;
    color: #666;
    background: #f8f9fa;
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
}

.tool-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    font-weight: 600;
}

.tool-status.connected {
    color: #28a745;
}

.tool-status.disconnected {
    color: #dc3545;
}

.app-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.stars {
    color: #ffc107;
    font-size: 0.9rem;
}

.rating-text {
    font-size: 0.8rem;
    color: #666;
}

.tool-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 600;
    width: 100%;
    transition: all 0.3s ease;
}

.tool-btn:hover {
    background: #0056b3;
    transform: translateY(-1px);
}

.tool-btn.secondary {
    background: #6c757d;
}

.tool-btn.secondary:hover {
    background: #545b62;
}

.countdown-timer {
    background: rgba(255,255,255,0.2);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    text-align: center;
    margin-bottom: 1rem;
    font-weight: bold;
    font-size: 0.9rem;
}

/* Tabla de Campañas */
.recent-campaigns-section {
    margin-top: 3rem;
}

.recent-campaigns-section h2 {
    margin-bottom: 1.5rem;
    color: #333;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.campaigns-table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.campaigns-table {
    width: 100%;
    border-collapse: collapse;
}

.campaigns-table th,
.campaigns-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.campaigns-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.campaigns-table tr:hover {
    background: #f8f9fa;
}

.campaign-info strong {
    display: block;
    color: #333;
    margin-bottom: 0.2rem;
}

.campaign-date {
    font-size: 0.8rem;
    color: #666;
}

.campaign-type {
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.campaign-type.email {
    background: #e3f2fd;
    color: #1976d2;
}

.campaign-type.social {
    background: #f3e5f5;
    color: #7b1fa2;
}

.campaign-type.ads {
    background: #e8f5e8;
    color: #388e3c;
}

.campaign-status {
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.campaign-status.active {
    background: #d4edda;
    color: #155724;
}

.campaign-status.completed {
    background: #d1ecf1;
    color: #0c5460;
}

.positive {
    color: #28a745;
    font-weight: 600;
}

.action-btn {
    background: none;
    border: 1px solid #ddd;
    padding: 0.4rem;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 0.3rem;
    color: #666;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: #f8f9fa;
    color: #333;
}

.action-btn.edit:hover { color: #007bff; }
.action-btn.pause:hover { color: #ffc107; }
.action-btn.stats:hover { color: #28a745; }
.action-btn.duplicate:hover { color: #6c757d; }

/* Optimización compacta para marketing.php */
.modern-admin-main { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.marketing-stats-section { margin-bottom: 1.5rem !important; }
.marketing-stats-section h2 { margin-bottom: 1rem !important; font-size: 1.2rem !important; }
.marketing-stats-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important; gap: 1rem !important; margin-bottom: 1.5rem !important; }
.marketing-stat-card { padding: 1rem !important; gap: 0.75rem !important; }
.marketing-stat-card .stat-icon { padding: 0.75rem !important; font-size: 1.2rem !important; }
.marketing-stat-card .stat-content h3 { font-size: 1.5rem !important; }
.marketing-stat-card .stat-content p { font-size: 0.8rem !important; }
.marketing-tools-section { margin-bottom: 1.5rem !important; }
.marketing-tools-section h2 { margin-bottom: 1.5rem !important; font-size: 1.2rem !important; }
.marketing-category { margin-bottom: 2rem !important; }
.marketing-category h3 { margin-bottom: 1rem !important; font-size: 1.1rem !important; }
.marketing-tools-grid { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important; gap: 1rem !important; }
.marketing-tool-card { padding: 1rem !important; }
.tool-header { margin-bottom: 0.75rem !important; gap: 0.5rem !important; }
.tool-header i { font-size: 1.2rem !important; }
.tool-header h4 { font-size: 1rem !important; }
.marketing-tool-card p { margin-bottom: 0.75rem !important; font-size: 0.8rem !important; }
.tool-stats { gap: 0.75rem !important; margin-bottom: 0.75rem !important; }
.tool-stat { font-size: 0.75rem !important; padding: 0.2rem 0.5rem !important; }
.tool-status { margin-bottom: 0.75rem !important; font-size: 0.8rem !important; }
.app-rating { margin-bottom: 0.75rem !important; }
.stars { font-size: 0.8rem !important; }
.rating-text { font-size: 0.75rem !important; }
.tool-btn { padding: 0.5rem 1rem !important; font-size: 0.8rem !important; }
.countdown-timer { padding: 0.4rem 0.8rem !important; font-size: 0.8rem !important; margin-bottom: 0.75rem !important; }
.recent-campaigns-section { margin-top: 2rem !important; }
.recent-campaigns-section h2 { margin-bottom: 1rem !important; font-size: 1.2rem !important; }
.campaigns-table th, .campaigns-table td { padding: 0.75rem !important; font-size: 0.8rem !important; }
.campaign-info strong { font-size: 0.85rem !important; }
.campaign-date { font-size: 0.75rem !important; }
.campaign-type, .campaign-status { padding: 0.2rem 0.5rem !important; font-size: 0.75rem !important; }
.action-btn { padding: 0.3rem !important; margin-right: 0.2rem !important; }
.tn-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>