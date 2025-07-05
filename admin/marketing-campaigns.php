<?php
$pageTitle = '=â Campañas de Marketing - Admin Panel';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-bullhorn"></i> Campañas de Marketing</h1>
    <p>Gestión completa de campañas de marketing y promociones</p>
    
    <div class="page-actions">
        <button class="btn btn-primary" onclick="AdminUtils.modal.show('create-campaign-modal')">
            <i class="fas fa-plus"></i> Nueva Campaña
        </button>
        <button class="btn btn-secondary" onclick="exportCampaigns()">
            <i class="fas fa-download"></i> Exportar
        </button>
    </div>
</div>

<!-- Estadísticas de Campañas -->
<div class="content-card">
    <h3><i class="fas fa-chart-line"></i> Resumen de Campañas</h3>
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-icon active">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="stat-content">
                <h4>8</h4>
                <p>Campañas Activas</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon total">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-content">
                <h4>12,547</h4>
                <p>Emails Enviados</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon pending">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-content">
                <h4>23.5%</h4>
                <p>Tasa de Apertura</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon success">
                <i class="fas fa-mouse-pointer"></i>
            </div>
            <div class="stat-content">
                <h4>4.2%</h4>
                <p>Tasa de Click</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon revenue">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <h4>$45,230</h4>
                <p>Ingresos Generados</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon conversion">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <h4>2.8%</h4>
                <p>Conversión</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Campañas -->
<div class="content-card">
    <div class="campaign-filters">
        <div class="filter-group">
            <label>Estado:</label>
            <select class="filter-select">
                <option value="">Todos los estados</option>
                <option value="active">Activa</option>
                <option value="paused">Pausada</option>
                <option value="completed">Completada</option>
                <option value="draft">Borrador</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Tipo:</label>
            <select class="filter-select">
                <option value="">Todos los tipos</option>
                <option value="email">Email Marketing</option>
                <option value="social">Redes Sociales</option>
                <option value="sms">SMS</option>
                <option value="push">Push Notifications</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Fecha:</label>
            <input type="date" class="filter-input">
            <span class="filter-separator">a</span>
            <input type="date" class="filter-input">
        </div>
        
        <div class="filter-group">
            <input type="text" class="filter-search" placeholder="Buscar campañas...">
            <button class="filter-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
</div>

<!-- Lista de Campañas -->
<div class="content-card">
    <h3><i class="fas fa-list"></i> Campañas Recientes</h3>
    
    <div class="campaigns-table-container">
        <table class="campaigns-table">
            <thead>
                <tr>
                    <th>Campaña</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Audiencia</th>
                    <th>Enviados</th>
                    <th>Apertura</th>
                    <th>Clicks</th>
                    <th>ROI</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="campaign-info">
                            <strong>Promoción Remeras Verano</strong>
                            <span class="campaign-date">15 Dic 2024</span>
                        </div>
                    </td>
                    <td>
                        <span class="campaign-type email">
                            <i class="fas fa-envelope"></i> Email
                        </span>
                    </td>
                    <td>
                        <span class="status-badge active">Activa</span>
                    </td>
                    <td>2,547 usuarios</td>
                    <td>2,489</td>
                    <td>
                        <span class="metric-value">24.5%</span>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: 24.5%"></div>
                        </div>
                    </td>
                    <td>
                        <span class="metric-value">4.8%</span>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: 4.8%"></div>
                        </div>
                    </td>
                    <td>
                        <span class="roi-positive">+285%</span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn copy" title="Duplicar">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="action-btn pause" title="Pausar">
                                <i class="fas fa-pause"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <div class="campaign-info">
                            <strong>Black Friday 2024</strong>
                            <span class="campaign-date">29 Nov 2024</span>
                        </div>
                    </td>
                    <td>
                        <span class="campaign-type social">
                            <i class="fab fa-instagram"></i> Social
                        </span>
                    </td>
                    <td>
                        <span class="status-badge completed">Completada</span>
                    </td>
                    <td>5,842 usuarios</td>
                    <td>5,798</td>
                    <td>
                        <span class="metric-value">31.2%</span>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: 31.2%"></div>
                        </div>
                    </td>
                    <td>
                        <span class="metric-value">7.3%</span>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: 7.3%"></div>
                        </div>
                    </td>
                    <td>
                        <span class="roi-positive">+540%</span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn copy" title="Duplicar">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="action-btn report" title="Reporte">
                                <i class="fas fa-chart-line"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <div class="campaign-info">
                            <strong>Descuento Primera Compra</strong>
                            <span class="campaign-date">10 Dic 2024</span>
                        </div>
                    </td>
                    <td>
                        <span class="campaign-type sms">
                            <i class="fas fa-sms"></i> SMS
                        </span>
                    </td>
                    <td>
                        <span class="status-badge paused">Pausada</span>
                    </td>
                    <td>1,234 usuarios</td>
                    <td>1,180</td>
                    <td>
                        <span class="metric-value">-</span>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: 0%"></div>
                        </div>
                    </td>
                    <td>
                        <span class="metric-value">12.4%</span>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: 12.4%"></div>
                        </div>
                    </td>
                    <td>
                        <span class="roi-neutral">+120%</span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn play" title="Reanudar">
                                <i class="fas fa-play"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Actions -->
<div class="content-card">
    <h3><i class="fas fa-rocket"></i> Acciones Rápidas</h3>
    <div class="quick-actions-grid">
        <button class="quick-action-btn" onclick="createEmailCampaign()">
            <i class="fas fa-envelope"></i>
            <span>Campaña Email</span>
        </button>
        <button class="quick-action-btn" onclick="createSocialCampaign()">
            <i class="fab fa-instagram"></i>
            <span>Post Social</span>
        </button>
        <button class="quick-action-btn" onclick="createSMSCampaign()">
            <i class="fas fa-sms"></i>
            <span>Campaña SMS</span>
        </button>
        <button class="quick-action-btn" onclick="createPushNotification()">
            <i class="fas fa-bell"></i>
            <span>Push Notification</span>
        </button>
        <button class="quick-action-btn" onclick="viewAnalytics()">
            <i class="fas fa-chart-bar"></i>
            <span>Ver Analytics</span>
        </button>
        <button class="quick-action-btn" onclick="manageSegments()">
            <i class="fas fa-users"></i>
            <span>Segmentos</span>
        </button>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
    flex-shrink: 0;
}

.stat-icon.active { background: #28a745; }
.stat-icon.total { background: #007bff; }
.stat-icon.pending { background: #ffc107; }
.stat-icon.success { background: #17a2b8; }
.stat-icon.revenue { background: #6f42c1; }
.stat-icon.conversion { background: #fd7e14; }

.stat-content h4 {
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    color: #2c3e50;
}

.stat-content p {
    margin: 0;
    color: #6c757d;
    font-size: 12px;
}

.campaign-filters {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
    min-width: max-content;
}

.filter-select, .filter-input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.filter-separator {
    color: #666;
    margin: 0 0.5rem;
}

.filter-search {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px 0 0 4px;
    font-size: 0.9rem;
    min-width: 200px;
}

.filter-btn {
    padding: 0.5rem 1rem;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
}

.campaigns-table-container {
    overflow-x: auto;
}

.campaigns-table {
    width: 100%;
    border-collapse: collapse;
}

.campaigns-table th {
    background: #f8f9fa;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 1px solid #dee2e6;
    font-size: 0.9rem;
}

.campaigns-table td {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    font-size: 0.85rem;
}

.campaign-info {
    display: flex;
    flex-direction: column;
}

.campaign-info strong {
    color: #333;
    margin-bottom: 2px;
}

.campaign-date {
    font-size: 0.75rem;
    color: #666;
}

.campaign-type {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.campaign-type.email {
    background: #e3f2fd;
    color: #1976d2;
}

.campaign-type.social {
    background: #fce4ec;
    color: #c2185b;
}

.campaign-type.sms {
    background: #f3e5f5;
    color: #7b1fa2;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.completed {
    background: #cce5ff;
    color: #004085;
}

.status-badge.paused {
    background: #fff3cd;
    color: #856404;
}

.metric-value {
    font-weight: 600;
    color: #333;
    display: block;
    margin-bottom: 2px;
}

.metric-bar {
    width: 60px;
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
}

.metric-fill {
    height: 100%;
    background: #28a745;
    border-radius: 2px;
}

.roi-positive {
    color: #28a745;
    font-weight: 600;
}

.roi-neutral {
    color: #ffc107;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.action-btn {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}

.action-btn.view {
    background: #e3f2fd;
    color: #1976d2;
}

.action-btn.edit {
    background: #fff3e0;
    color: #f57c00;
}

.action-btn.copy {
    background: #f3e5f5;
    color: #7b1fa2;
}

.action-btn.pause {
    background: #fff3cd;
    color: #856404;
}

.action-btn.play {
    background: #d4edda;
    color: #155724;
}

.action-btn.report {
    background: #e8f5e8;
    color: #2e7d32;
}

.action-btn:hover {
    opacity: 0.8;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.quick-action-btn:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.quick-action-btn i {
    font-size: 24px;
    color: #007bff;
}

.quick-action-btn span {
    font-size: 0.9rem;
    font-weight: 600;
    color: #333;
}

@media (max-width: 768px) {
    .campaign-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        flex-wrap: wrap;
    }
    
    .campaigns-table {
        font-size: 0.75rem;
    }
    
    .campaigns-table th,
    .campaigns-table td {
        padding: 0.5rem;
    }
    
    .quick-actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
function exportCampaigns() {
    AdminUtils.showNotification('Exportando campañas...', 'info');
}

function createEmailCampaign() {
    AdminUtils.showNotification('Funcionalidad en desarrollo', 'info');
}

function createSocialCampaign() {
    AdminUtils.showNotification('Funcionalidad en desarrollo', 'info');
}

function createSMSCampaign() {
    AdminUtils.showNotification('Funcionalidad en desarrollo', 'info');
}

function createPushNotification() {
    AdminUtils.showNotification('Funcionalidad en desarrollo', 'info');
}

function viewAnalytics() {
    AdminUtils.showNotification('Funcionalidad en desarrollo', 'info');
}

function manageSegments() {
    AdminUtils.showNotification('Funcionalidad en desarrollo', 'info');
}
</script>

<?php include 'admin-master-footer.php'; ?>