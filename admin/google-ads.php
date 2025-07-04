<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🎯 Google Ads - Panel Admin';
include 'admin-dashboard-header.php';

// Simulación de datos de Google Ads
$adsStats = [
    'total_spent' => 1234.56,
    'impressions' => 45678,
    'clicks' => 1234,
    'conversions' => 89,
    'ctr' => 2.7,
    'cpc' => 1.00,
    'conversion_rate' => 7.2
];

$campaigns = [
    ['id' => 1, 'name' => 'Remeras Personalizadas - Búsqueda', 'status' => 'Activa', 'budget' => 50, 'spent' => 45.67, 'impressions' => 12345, 'clicks' => 234, 'conversions' => 12],
    ['id' => 2, 'name' => 'Buzos - Display', 'status' => 'Pausada', 'budget' => 30, 'spent' => 0, 'impressions' => 0, 'clicks' => 0, 'conversions' => 0],
    ['id' => 3, 'name' => 'Shopping - Productos', 'status' => 'Activa', 'budget' => 75, 'spent' => 68.90, 'impressions' => 23456, 'clicks' => 456, 'conversions' => 34]
];

$keywords = [
    ['keyword' => 'remeras personalizadas', 'impressions' => 5678, 'clicks' => 89, 'ctr' => 1.6, 'cpc' => 0.95, 'quality_score' => 7],
    ['keyword' => 'diseño de remeras', 'impressions' => 3456, 'clicks' => 67, 'ctr' => 1.9, 'cpc' => 1.20, 'quality_score' => 8],
    ['keyword' => 'buzos personalizados', 'impressions' => 2345, 'clicks' => 34, 'ctr' => 1.4, 'cpc' => 1.50, 'quality_score' => 6],
    ['keyword' => 'regalos personalizados', 'impressions' => 4567, 'clicks' => 78, 'ctr' => 1.7, 'cpc' => 1.10, 'quality_score' => 7]
];
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fab fa-google"></i> Google Ads</h1>
                <p class="header-subtitle">Gestiona tus campañas de Google Ads</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="configureGoogleAds()">
                    <i class="fas fa-cog"></i>
                    Configurar Cuenta
                </button>
                <button class="tn-btn tn-btn-primary" onclick="createCampaign()">
                    <i class="fas fa-plus"></i>
                    Nueva Campaña
                </button>
            </div>
        </div>

        <!-- Google Ads Stats -->
        <section class="ads-stats-section">
            <h2><i class="fas fa-chart-line"></i> Rendimiento General</h2>
            <div class="ads-stats-grid">
                <div class="ads-stat-card spent">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($adsStats['total_spent'], 2); ?></h3>
                        <p>Gastado este mes</p>
                        <span class="stat-change positive">+15.3%</span>
                    </div>
                </div>
                <div class="ads-stat-card impressions">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($adsStats['impressions']); ?></h3>
                        <p>Impresiones</p>
                        <span class="stat-change positive">+8.7%</span>
                    </div>
                </div>
                <div class="ads-stat-card clicks">
                    <div class="stat-icon">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($adsStats['clicks']); ?></h3>
                        <p>Clics</p>
                        <span class="stat-change positive">+12.1%</span>
                    </div>
                </div>
                <div class="ads-stat-card conversions">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $adsStats['conversions']; ?></h3>
                        <p>Conversiones</p>
                        <span class="stat-change positive">+23.5%</span>
                    </div>
                </div>
                <div class="ads-stat-card ctr">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $adsStats['ctr']; ?>%</h3>
                        <p>CTR</p>
                        <span class="stat-change neutral">+0.2%</span>
                    </div>
                </div>
                <div class="ads-stat-card cpc">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($adsStats['cpc'], 2); ?></h3>
                        <p>CPC Promedio</p>
                        <span class="stat-change negative">-5.1%</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Campaign Management -->
        <section class="campaigns-section">
            <h2><i class="fas fa-bullhorn"></i> Campañas Activas</h2>
            <div class="campaigns-table-container">
                <table class="campaigns-table">
                    <thead>
                        <tr>
                            <th>Campaña</th>
                            <th>Estado</th>
                            <th>Presupuesto</th>
                            <th>Gastado</th>
                            <th>Impresiones</th>
                            <th>Clics</th>
                            <th>Conversiones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaigns as $campaign): ?>
                        <tr>
                            <td><strong><?php echo $campaign['name']; ?></strong></td>
                            <td>
                                <span class="campaign-status <?php echo strtolower($campaign['status']); ?>">
                                    <?php echo $campaign['status']; ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($campaign['budget'], 2); ?>/día</td>
                            <td>$<?php echo number_format($campaign['spent'], 2); ?></td>
                            <td><?php echo number_format($campaign['impressions']); ?></td>
                            <td><?php echo number_format($campaign['clicks']); ?></td>
                            <td><?php echo $campaign['conversions']; ?></td>
                            <td>
                                <button class="action-btn edit" onclick="editCampaign(<?php echo $campaign['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn <?php echo $campaign['status'] == 'Activa' ? 'pause' : 'play'; ?>" onclick="toggleCampaign(<?php echo $campaign['id']; ?>)">
                                    <i class="fas fa-<?php echo $campaign['status'] == 'Activa' ? 'pause' : 'play'; ?>"></i>
                                </button>
                                <button class="action-btn stats" onclick="viewCampaignStats(<?php echo $campaign['id']; ?>)">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Keywords Performance -->
        <section class="keywords-section">
            <h2><i class="fas fa-key"></i> Rendimiento de Palabras Clave</h2>
            <div class="keywords-table-container">
                <table class="keywords-table">
                    <thead>
                        <tr>
                            <th>Palabra Clave</th>
                            <th>Impresiones</th>
                            <th>Clics</th>
                            <th>CTR</th>
                            <th>CPC</th>
                            <th>Nivel de Calidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($keywords as $index => $keyword): ?>
                        <tr>
                            <td><strong><?php echo $keyword['keyword']; ?></strong></td>
                            <td><?php echo number_format($keyword['impressions']); ?></td>
                            <td><?php echo number_format($keyword['clicks']); ?></td>
                            <td><?php echo $keyword['ctr']; ?>%</td>
                            <td>$<?php echo number_format($keyword['cpc'], 2); ?></td>
                            <td>
                                <span class="quality-score score-<?php echo $keyword['quality_score']; ?>">
                                    <?php echo $keyword['quality_score']; ?>/10
                                </span>
                            </td>
                            <td>
                                <button class="action-btn edit" onclick="editKeyword(<?php echo $index; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn bid" onclick="adjustBid(<?php echo $index; ?>)">
                                    <i class="fas fa-dollar-sign"></i>
                                </button>
                                <button class="action-btn negative" onclick="addNegative(<?php echo $index; ?>)">
                                    <i class="fas fa-minus-circle"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Google Ads Tools -->
        <section class="ads-tools-section">
            <h2><i class="fas fa-tools"></i> Herramientas de Google Ads</h2>
            <div class="tools-grid">
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Planificador de Palabras Clave</h3>
                    <p>Encuentra nuevas palabras clave y obtén estimaciones de tráfico</p>
                    <button class="tool-btn" onclick="openKeywordPlanner()">Abrir Planificador</button>
                </div>

                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3>Google Shopping</h3>
                    <p>Gestiona tus campañas de Google Shopping</p>
                    <button class="tool-btn" onclick="manageGoogleShopping()">Gestionar Shopping</button>
                </div>

                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Campañas de Aplicaciones</h3>
                    <p>Promociona tu aplicación móvil</p>
                    <button class="tool-btn" onclick="createAppCampaign()">Crear Campaña</button>
                </div>

                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3>YouTube Ads</h3>
                    <p>Crea anuncios de video en YouTube</p>
                    <button class="tool-btn" onclick="createYouTubeAd()">Crear Anuncio</button>
                </div>

                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Optimizador de Pujas</h3>
                    <p>Optimiza automáticamente tus pujas</p>
                    <button class="tool-btn" onclick="configureBidding()">Configurar</button>
                </div>

                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Informes Personalizados</h3>
                    <p>Crea informes detallados de rendimiento</p>
                    <button class="tool-btn" onclick="createReport()">Crear Informe</button>
                </div>
            </div>
        </section>

        <!-- Campaign Creator -->
        <section class="campaign-creator-section">
            <h2><i class="fas fa-plus-square"></i> Asistente de Campaña</h2>
            <div class="campaign-wizard">
                <div class="wizard-step active">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Objetivo de la Campaña</h3>
                        <div class="objective-grid">
                            <div class="objective-card" onclick="selectObjective('sales')">
                                <i class="fas fa-shopping-cart"></i>
                                <h4>Ventas</h4>
                                <p>Impulsar ventas online</p>
                            </div>
                            <div class="objective-card" onclick="selectObjective('leads')">
                                <i class="fas fa-user-plus"></i>
                                <h4>Clientes Potenciales</h4>
                                <p>Generar leads calificados</p>
                            </div>
                            <div class="objective-card" onclick="selectObjective('traffic')">
                                <i class="fas fa-globe"></i>
                                <h4>Tráfico Web</h4>
                                <p>Aumentar visitas al sitio</p>
                            </div>
                            <div class="objective-card" onclick="selectObjective('awareness')">
                                <i class="fas fa-eye"></i>
                                <h4>Reconocimiento</h4>
                                <p>Aumentar visibilidad de marca</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
// Google Ads Functions
function createCampaign() {
    alert('Crear nueva campaña de Google Ads');
}

function configureGoogleAds() {
    alert('Configurar cuenta de Google Ads:\n\n1. Vincular cuenta\n2. Configurar conversiones\n3. Establecer audiencias\n4. Configurar remarketing');
}

function editCampaign(id) {
    alert('Editando campaña ID: ' + id);
}

function toggleCampaign(id) {
    if (confirm('¿Cambiar estado de la campaña ID: ' + id + '?')) {
        alert('Estado de campaña modificado');
    }
}

function viewCampaignStats(id) {
    alert('Estadísticas detalladas de campaña ID: ' + id);
}

function editKeyword(id) {
    alert('Editando palabra clave ID: ' + id);
}

function adjustBid(id) {
    const newBid = prompt('Nueva puja para esta palabra clave:');
    if (newBid) {
        alert('Puja actualizada a: $' + newBid);
    }
}

function addNegative(id) {
    if (confirm('¿Agregar esta palabra clave como negativa?')) {
        alert('Palabra clave agregada a la lista de negativas');
    }
}

function openKeywordPlanner() {
    alert('Abriendo Planificador de Palabras Clave de Google');
}

function manageGoogleShopping() {
    window.location.href = 'google-shopping.php';
}

function createAppCampaign() {
    alert('Crear campaña de aplicación móvil');
}

function createYouTubeAd() {
    alert('Crear anuncio de YouTube:\n\n1. Video promocional\n2. Audiencia objetivo\n3. Presupuesto\n4. Configurar conversiones');
}

function configureBidding() {
    alert('Configurar estrategias de puja automática');
}

function createReport() {
    alert('Crear informe personalizado de Google Ads');
}

function selectObjective(objective) {
    alert('Objetivo seleccionado: ' + objective + '\n\nContinuando con el asistente...');
}
</script>

<style>
/* Google Ads Styles */
.ads-stats-section {
    margin-bottom: 3rem;
}

.ads-stats-section h2 {
    margin-bottom: 1.5rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ads-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.ads-stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    border-left: 4px solid #007bff;
}

.ads-stat-card.spent { border-left-color: #dc3545; }
.ads-stat-card.impressions { border-left-color: #17a2b8; }
.ads-stat-card.clicks { border-left-color: #28a745; }
.ads-stat-card.conversions { border-left-color: #ffc107; }
.ads-stat-card.ctr { border-left-color: #6f42c1; }
.ads-stat-card.cpc { border-left-color: #fd7e14; }

.stat-icon {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 50%;
    font-size: 1.5rem;
    color: #007bff;
}

.ads-stat-card.spent .stat-icon { color: #dc3545; }
.ads-stat-card.impressions .stat-icon { color: #17a2b8; }
.ads-stat-card.clicks .stat-icon { color: #28a745; }
.ads-stat-card.conversions .stat-icon { color: #ffc107; }
.ads-stat-card.ctr .stat-icon { color: #6f42c1; }
.ads-stat-card.cpc .stat-icon { color: #fd7e14; }

.stat-content h3 {
    margin: 0;
    font-size: 1.8rem;
    color: #333;
}

.stat-content p {
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

.stat-change.negative {
    background: #f8d7da;
    color: #721c24;
}

.stat-change.neutral {
    background: #e2e3e5;
    color: #383d41;
}

.campaigns-section, .keywords-section, .ads-tools-section, .campaign-creator-section {
    margin-bottom: 3rem;
}

.campaigns-section h2, .keywords-section h2, .ads-tools-section h2, .campaign-creator-section h2 {
    margin-bottom: 1.5rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.campaigns-table-container, .keywords-table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.campaigns-table, .keywords-table {
    width: 100%;
    border-collapse: collapse;
}

.campaigns-table th, .campaigns-table td,
.keywords-table th, .keywords-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.campaigns-table th, .keywords-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.campaign-status {
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.campaign-status.activa {
    background: #d4edda;
    color: #155724;
}

.campaign-status.pausada {
    background: #f8d7da;
    color: #721c24;
}

.quality-score {
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.quality-score.score-8, .quality-score.score-9, .quality-score.score-10 {
    background: #d4edda;
    color: #155724;
}

.quality-score.score-6, .quality-score.score-7 {
    background: #fff3cd;
    color: #856404;
}

.quality-score.score-1, .quality-score.score-2, .quality-score.score-3, .quality-score.score-4, .quality-score.score-5 {
    background: #f8d7da;
    color: #721c24;
}

.tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.tool-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
}

.tool-card:hover {
    transform: translateY(-2px);
}

.tool-icon {
    background: #f8f9fa;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: #007bff;
}

.tool-card h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.tool-card p {
    color: #666;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.tool-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    cursor: pointer;
    width: 100%;
    font-weight: 600;
}

.campaign-wizard {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.wizard-step {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.step-number {
    background: #007bff;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-content h3 {
    margin: 0 0 1rem 0;
    color: #333;
}

.objective-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.objective-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.objective-card:hover {
    background: #e9ecef;
    border-color: #007bff;
}

.objective-card i {
    font-size: 2rem;
    color: #007bff;
    margin-bottom: 1rem;
}

.objective-card h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.objective-card p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.action-btn {
    background: none;
    border: 1px solid #ddd;
    padding: 0.4rem;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 0.3rem;
    color: #666;
}

.action-btn:hover {
    background: #f8f9fa;
}

.action-btn.edit:hover { color: #007bff; }
.action-btn.pause:hover { color: #ffc107; }
.action-btn.play:hover { color: #28a745; }
.action-btn.stats:hover { color: #17a2b8; }
.action-btn.bid:hover { color: #fd7e14; }
.action-btn.negative:hover { color: #dc3545; }

/* Optimización compacta */
.modern-admin-main { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.ads-stats-grid { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important; gap: 0.75rem !important; }
.ads-stat-card { padding: 1rem !important; gap: 0.75rem !important; }
.stat-icon { padding: 0.75rem !important; font-size: 1.2rem !important; }
.stat-content h3 { font-size: 1.5rem !important; }
.campaigns-table th, .campaigns-table td, .keywords-table th, .keywords-table td { padding: 0.75rem !important; font-size: 0.85rem !important; }
.tools-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important; gap: 1rem !important; }
.tool-card { padding: 1rem !important; }
.tool-icon { width: 50px !important; height: 50px !important; font-size: 1.2rem !important; }
.tool-card h3 { font-size: 1rem !important; }
.tool-card p { font-size: 0.8rem !important; }
.tool-btn { padding: 0.5rem 1rem !important; font-size: 0.8rem !important; }
.campaign-wizard { padding: 1.5rem !important; }
.objective-grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)) !important; gap: 0.75rem !important; }
.objective-card { padding: 1rem !important; }
.objective-card i { font-size: 1.5rem !important; }
.tn-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>