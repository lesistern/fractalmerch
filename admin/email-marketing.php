<?php
$pageTitle = '📧 Email Marketing';
include 'admin-master-header.php';

// Simulación de datos de email marketing
$emailStats = [
    'total_subscribers' => 1234,
    'active_campaigns' => 5,
    'open_rate' => 23.5,
    'click_rate' => 4.8,
    'monthly_sent' => 8567
];

$campaigns = [
    ['id' => 1, 'name' => 'Descuento Verano 2025', 'status' => 'Enviada', 'sent' => 1234, 'opens' => 289, 'clicks' => 67, 'date' => '2025-07-01'],
    ['id' => 2, 'name' => 'Nuevos Productos', 'status' => 'Activa', 'sent' => 2345, 'opens' => 512, 'clicks' => 89, 'date' => '2025-06-28'],
    ['id' => 3, 'name' => 'Carrito Abandonado', 'status' => 'Programada', 'sent' => 0, 'opens' => 0, 'clicks' => 0, 'date' => '2025-07-05']
];

$templates = [
    ['id' => 1, 'name' => 'Promoción de Descuento', 'type' => 'Promocional', 'usage' => 12],
    ['id' => 2, 'name' => 'Bienvenida Nuevos Clientes', 'type' => 'Bienvenida', 'usage' => 45],
    ['id' => 3, 'name' => 'Recuperación de Carrito', 'type' => 'Automatización', 'usage' => 89],
    ['id' => 4, 'name' => 'Newsletter Semanal', 'type' => 'Newsletter', 'usage' => 23]
];
?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-envelope"></i> Email Marketing</h1>
        <p class="page-subtitle">Gestiona tus campañas de email marketing</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-outline-primary" onclick="configureMailchimp()">
            <i class="fab fa-mailchimp"></i>
            Configurar Mailchimp
        </button>
        <button class="btn btn-primary" onclick="createCampaign()">
            <i class="fas fa-plus"></i>
            Nueva Campaña
        </button>
    </div>
</div>

<!-- Email Stats -->
<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-chart-bar"></i> Estadísticas de Email</h3>
    </div>
    <div class="card-body">
        <div class="email-stats-grid">
            <div class="email-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($emailStats['total_subscribers']); ?></h3>
                    <p>Suscriptores</p>
                </div>
            </div>
            <div class="email-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $emailStats['active_campaigns']; ?></h3>
                    <p>Campañas Activas</p>
                </div>
            </div>
            <div class="email-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $emailStats['open_rate']; ?>%</h3>
                    <p>Tasa de Apertura</p>
                </div>
            </div>
            <div class="email-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $emailStats['click_rate']; ?>%</h3>
                    <p>Tasa de Clics</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Integration Status -->
<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-plug"></i> Integraciones</h3>
    </div>
    <div class="card-body">
        <div class="integration-grid">
            <div class="integration-card connected">
                <div class="integration-header">
                    <i class="fab fa-mailchimp"></i>
                    <h3>Mailchimp</h3>
                    <span class="status-badge connected">Conectado</span>
                </div>
                <p>Integración activa con Mailchimp para campañas avanzadas</p>
                <div class="integration-stats">
                    <span>API Key: ****-us1</span>
                    <span>Última sincronización: Hace 2 horas</span>
                </div>
                <button class="integration-btn" onclick="configureMailchimp()">Configurar</button>
            </div>

            <div class="integration-card">
                <div class="integration-header">
                    <i class="fas fa-rocket"></i>
                    <h3>Marketing Nube</h3>
                    <span class="status-badge active">Activo</span>
                </div>
                <p>Herramienta nativa de marketing y automatización</p>
                <div class="integration-stats">
                    <span>5 automatizaciones activas</span>
                    <span>1,234 contactos sincronizados</span>
                </div>
                <button class="integration-btn" onclick="openMarketingNube()">Abrir Marketing Nube</button>
            </div>
        </div>
    </div>
</div>

<!-- Campaign Management -->
<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-bullhorn"></i> Gestión de Campañas</h3>
    </div>
    <div class="card-body">
        <div class="campaigns-table-container">
            <table class="campaigns-table">
                <thead>
                    <tr>
                        <th>Campaña</th>
                        <th>Estado</th>
                        <th>Enviados</th>
                        <th>Aperturas</th>
                        <th>Clics</th>
                        <th>Fecha</th>
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
                        <td><?php echo number_format($campaign['sent']); ?></td>
                        <td>
                            <?php if ($campaign['sent'] > 0): ?>
                                <?php echo number_format($campaign['opens']); ?> (<?php echo round(($campaign['opens']/$campaign['sent'])*100, 1); ?>%)
                            <?php else: ?>-<?php endif; ?>
                        </td>
                        <td>
                            <?php if ($campaign['sent'] > 0): ?>
                                <?php echo number_format($campaign['clicks']); ?> (<?php echo round(($campaign['clicks']/$campaign['sent'])*100, 1); ?>%)
                            <?php else: ?>-<?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($campaign['date'])); ?></td>
                        <td>
                            <button class="action-btn edit" onclick="editCampaign(<?php echo $campaign['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn duplicate" onclick="duplicateCampaign(<?php echo $campaign['id']; ?>)">
                                <i class="fas fa-copy"></i>
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
    </div>
</div>

<!-- Email Templates -->
<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-file-alt"></i> Plantillas de Email</h3>
    </div>
    <div class="card-body">
        <div class="templates-grid">
            <?php foreach ($templates as $template): ?>
            <div class="template-card">
                <div class="template-preview">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="template-info">
                    <h4><?php echo $template['name']; ?></h4>
                    <p>Tipo: <?php echo $template['type']; ?></p>
                    <span class="usage-count">Usado <?php echo $template['usage']; ?> veces</span>
                </div>
                <div class="template-actions">
                    <button class="template-btn use" onclick="useTemplate(<?php echo $template['id']; ?>)">Usar</button>
                    <button class="template-btn edit" onclick="editTemplate(<?php echo $template['id']; ?>)">Editar</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Contact Lists -->
<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> Listas de Contactos</h3>
    </div>
    <div class="card-body">
        <div class="lists-grid">
            <div class="list-card">
                <h4>Lista Principal</h4>
                <p>1,234 contactos</p>
                <span class="list-type">Principal</span>
                <button class="list-btn" onclick="manageList(1)">Gestionar</button>
            </div>
            <div class="list-card">
                <h4>Clientes VIP</h4>
                <p>89 contactos</p>
                <span class="list-type">Segmentada</span>
                <button class="list-btn" onclick="manageList(2)">Gestionar</button>
            </div>
            <div class="list-card">
                <h4>Carritos Abandonados</h4>
                <p>156 contactos</p>
                <span class="list-type">Automática</span>
                <button class="list-btn" onclick="manageList(3)">Gestionar</button>
            </div>
            <div class="list-card create-new">
                <i class="fas fa-plus"></i>
                <h4>Crear Nueva Lista</h4>
                <button class="list-btn primary" onclick="createList()">Crear</button>
            </div>
        </div>
    </div>
</div>

<script>
// Email Marketing Functions
function createCampaign() {
    window.location.href = 'email-campaign-builder.php';
}

function configureMailchimp() {
    alert('Configurar Mailchimp:\n\n1. API Key\n2. Lista por defecto\n3. Sincronización automática\n\nRedirigiendo a configuración...');
}

function openMarketingNube() {
    alert('Abriendo Marketing Nube - Herramienta nativa');
}

function editCampaign(id) {
    alert('Editando campaña ID: ' + id);
}

function duplicateCampaign(id) {
    if (confirm('¿Duplicar campaña ID: ' + id + '?')) {
        alert('Campaña duplicada exitosamente');
    }
}

function viewCampaignStats(id) {
    alert('Mostrando estadísticas de campaña ID: ' + id);
}

function useTemplate(id) {
    alert('Usando plantilla ID: ' + id + ' para nueva campaña');
}

function editTemplate(id) {
    alert('Editando plantilla ID: ' + id);
}

function manageList(id) {
    alert('Gestionando lista ID: ' + id);
}

function createList() {
    const listName = prompt('Nombre de la nueva lista:');
    if (listName) {
        alert('Lista "' + listName + '" creada exitosamente');
    }
}
</script>

<style>
/* Email Marketing Styles */
.email-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.email-stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid #e9ecef;
}

.stat-icon {
    background: #e3f2fd;
    padding: 1rem;
    border-radius: 50%;
    color: #1976d2;
    font-size: 1.5rem;
}

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

.integration-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.integration-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #ddd;
    border: 1px solid #e9ecef;
}

.integration-card.connected {
    border-left-color: #28a745;
}

.integration-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.integration-header i {
    font-size: 1.5rem;
    color: #007bff;
}

.integration-header h3 {
    flex: 1;
    margin: 0;
    color: #333;
}

.status-badge {
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.connected {
    background: #d4edda;
    color: #155724;
}

.status-badge.active {
    background: #d1ecf1;
    color: #0c5460;
}

.integration-stats {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.integration-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
}

.campaigns-table-container {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
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
}

.campaign-status {
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.campaign-status.enviada {
    background: #d4edda;
    color: #155724;
}

.campaign-status.activa {
    background: #d1ecf1;
    color: #0c5460;
}

.campaign-status.programada {
    background: #fff3cd;
    color: #856404;
}

.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.template-card {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
    border: 1px solid #e9ecef;
}

.template-preview {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.template-preview i {
    font-size: 2rem;
    color: #666;
}

.template-info h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.template-info p {
    margin: 0 0 0.5rem 0;
    color: #666;
    font-size: 0.9rem;
}

.usage-count {
    font-size: 0.8rem;
    color: #999;
}

.template-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.template-btn {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
}

.template-btn.use {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.lists-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.list-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
    border: 1px solid #e9ecef;
}

.list-card.create-new {
    border: 2px dashed #ddd;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.list-card.create-new i {
    font-size: 2rem;
    color: #999;
    margin-bottom: 1rem;
}

.list-card h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.list-card p {
    margin: 0 0 0.5rem 0;
    color: #666;
}

.list-type {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    background: #e9ecef;
    border-radius: 12px;
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 1rem;
}

.list-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
}

.list-btn.primary {
    background: #007bff;
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
</style>

<?php include 'admin-master-footer.php'; ?>