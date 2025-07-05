<?php
$pageTitle = '🏭 Flujo de Producción - Admin Panel';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-cogs"></i> Flujo de Producción</h1>
    <p>Sistema automatizado de gestión de flujo de producción</p>
    
    <div class="page-actions">
        <button id="start-workflow-btn" class="btn btn-primary">
            <i class="fas fa-play"></i> Iniciar Workflow
        </button>
        <button id="view-stations-btn" class="btn btn-secondary">
            <i class="fas fa-industry"></i> Estaciones
        </button>
        <button id="quality-dashboard-btn" class="btn btn-outline">
            <i class="fas fa-chart-line"></i> Dashboard Calidad
        </button>
    </div>
</div>

<!-- Resumen de Producción -->
<div class="content-card">
    <h3><i class="fas fa-chart-pie"></i> Métricas de Producción</h3>
    <div class="production-summary">
        <div class="summary-card active-workflows">
            <div class="card-icon">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="card-content">
                <h3 id="active-workflows-count">-</h3>
                <p>Workflows Activos</p>
                <span class="trend" id="workflows-trend"></span>
            </div>
        </div>

        <div class="summary-card completed-today">
            <div class="card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="card-content">
                <h3 id="completed-today-count">-</h3>
                <p>Completados Hoy</p>
                <span class="trend positive" id="completed-trend">+15% vs ayer</span>
            </div>
        </div>

        <div class="summary-card average-time">
            <div class="card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-content">
                <h3 id="average-completion-time">-</h3>
                <p>Tiempo Promedio</p>
                <span class="trend" id="time-trend"></span>
            </div>
        </div>

        <div class="summary-card quality-rate">
            <div class="card-icon">
                <i class="fas fa-award"></i>
            </div>
            <div class="card-content">
                <h3 id="quality-pass-rate">-</h3>
                <p>Tasa de Calidad</p>
                <span class="trend positive" id="quality-trend">98.5%</span>
            </div>
        </div>

        <div class="summary-card station-utilization">
            <div class="card-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="card-content">
                <h3 id="station-utilization-rate">-</h3>
                <p>Utilización Estaciones</p>
                <span class="trend" id="utilization-trend"></span>
            </div>
        </div>

        <div class="summary-card on-time-delivery">
            <div class="card-icon">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <div class="card-content">
                <h3 id="on-time-delivery-rate">-</h3>
                <p>Entrega a Tiempo</p>
                <span class="trend positive" id="delivery-trend">95.2%</span>
            </div>
        </div>
    </div>
</div>

<!-- Controles de Producción -->
<div class="content-card">
    <h3><i class="fas fa-filter"></i> Filtros y Controles</h3>
    <div class="production-controls">
        <div class="control-group">
            <label for="workflow-status-filter">Estado:</label>
            <select id="workflow-status-filter">
                <option value="">Todos los estados</option>
                <option value="active">Activo</option>
                <option value="completed">Completado</option>
                <option value="error">Error</option>
                <option value="cancelled">Cancelado</option>
            </select>
        </div>

        <div class="control-group">
            <label for="priority-filter">Prioridad:</label>
            <select id="priority-filter">
                <option value="">Todas las prioridades</option>
                <option value="high">Alta</option>
                <option value="medium">Media</option>
                <option value="low">Baja</option>
            </select>
        </div>

        <div class="control-group">
            <label for="date-range-filter">Rango de Fechas:</label>
            <input type="date" id="date-from-filter">
            <input type="date" id="date-to-filter">
        </div>

        <div class="control-group">
            <label for="search-workflows">Buscar:</label>
            <input type="text" id="search-workflows" placeholder="Número de orden, cliente...">
        </div>

        <div class="control-group">
            <button id="refresh-workflows" class="btn btn-outline">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>
</div>

<!-- Pestañas de Producción -->
<div class="content-card">
    <h3><i class="fas fa-tasks"></i> Gestión de Producción</h3>
    <div class="production-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="workflows">Workflows</button>
            <button class="tab-btn" data-tab="stations">Estaciones</button>
            <button class="tab-btn" data-tab="quality">Control Calidad</button>
            <button class="tab-btn" data-tab="analytics">Analytics</button>
        </div>

        <!-- Workflows Tab -->
        <div id="workflows-tab" class="tab-content active">
            <div class="workflows-container">
                <div class="workflows-list" id="workflows-list">
                    <!-- Workflows will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Stations Tab -->
        <div id="stations-tab" class="tab-content">
            <div class="stations-grid" id="stations-grid">
                <!-- Production stations will be loaded here -->
            </div>
        </div>

        <!-- Quality Tab -->
        <div id="quality-tab" class="tab-content">
            <div class="quality-controls">
                <div class="control-group">
                    <button id="quality-check-btn" class="btn btn-primary">
                        <i class="fas fa-search"></i> Realizar Control
                    </button>
                </div>
            </div>

            <div class="quality-results" id="quality-results">
                <!-- Quality check results will be loaded here -->
            </div>
        </div>

        <!-- Analytics Tab -->
        <div id="analytics-tab" class="tab-content">
            <div class="analytics-dashboard">
                <div class="chart-container">
                    <canvas id="production-chart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="quality-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Workflow Detail Modal -->
<div id="workflow-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="workflow-modal-title">Detalles del Workflow</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div id="workflow-details">
                <!-- Workflow details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Quality Check Modal -->
<div id="quality-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Control de Calidad</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="quality-form">
                <div class="quality-form-grid">
                    <div class="form-group">
                        <label for="workflow-select">Workflow:</label>
                        <select id="workflow-select" required>
                            <option value="">Seleccionar workflow...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="inspector-id">Inspector:</label>
                        <select id="inspector-id" required>
                            <option value="1">Inspector Principal</option>
                            <option value="2">Inspector Secundario</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="print-quality">Calidad de Impresión (1-10):</label>
                        <input type="range" id="print-quality" min="1" max="10" value="8">
                        <output for="print-quality">8</output>
                    </div>
                    
                    <div class="form-group">
                        <label for="color-accuracy">Precisión de Color (1-10):</label>
                        <input type="range" id="color-accuracy" min="1" max="10" value="8">
                        <output for="color-accuracy">8</output>
                    </div>
                    
                    <div class="form-group">
                        <label for="material-quality">Calidad de Material (1-10):</label>
                        <input type="range" id="material-quality" min="1" max="10" value="8">
                        <output for="material-quality">8</output>
                    </div>
                    
                    <div class="form-group span-2">
                        <label for="quality-notes">Notas de Calidad:</label>
                        <textarea id="quality-notes" rows="3" placeholder="Observaciones del control de calidad..."></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-quality">Cancelar</button>
                    <button type="submit" class="btn btn-success">Aprobar Calidad</button>
                    <button type="button" class="btn btn-danger" id="reject-quality">Rechazar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para production-workflow */
.production-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.summary-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.active-workflows .card-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.completed-today .card-icon { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.average-time .card-icon { background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%); }
.quality-rate .card-icon { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
.station-utilization .card-icon { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
.on-time-delivery .card-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

.card-content h3 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    color: #2c3e50;
}

.card-content p {
    margin: 0;
    color: #6c757d;
    font-size: 12px;
}

.trend {
    font-size: 11px;
    color: #6c757d;
}

.trend.positive {
    color: #28a745;
}

.production-controls {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.control-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.control-group label {
    font-weight: 600;
    color: #495057;
    font-size: 12px;
}

.control-group input,
.control-group select {
    padding: 6px 10px;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    font-size: 12px;
}

.production-tabs {
    margin-top: 15px;
}

.tab-buttons {
    display: flex;
    gap: 0;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 20px;
}

.tab-btn {
    padding: 10px 20px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #6c757d;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
}

.tab-btn.active {
    color: #007bff;
    border-bottom-color: #007bff;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.workflows-list {
    display: grid;
    gap: 15px;
}

.workflow-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    transition: transform 0.2s ease;
}

.workflow-card:hover {
    transform: translateY(-2px);
}

.workflow-card.high-priority {
    border-left-color: #dc3545;
}

.workflow-card.completed {
    border-left-color: #28a745;
}

.workflow-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.workflow-header h4 {
    margin: 0;
    font-size: 16px;
    color: #2c3e50;
}

.workflow-steps {
    display: flex;
    gap: 5px;
    margin-top: 10px;
}

.workflow-step {
    flex: 1;
    padding: 4px 6px;
    border-radius: 4px;
    text-align: center;
    font-size: 10px;
    font-weight: 600;
}

.workflow-step.completed {
    background: #d4edda;
    color: #155724;
}

.workflow-step.active {
    background: #d1ecf1;
    color: #0c5460;
}

.workflow-step.pending {
    background: #f8f9fa;
    color: #6c757d;
}

.stations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.station-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.station-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.station-utilization {
    width: 100%;
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 8px;
}

.utilization-bar {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #ffc107, #dc3545);
    transition: width 0.3s ease;
}

.quality-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group.span-2 {
    grid-column: span 2;
}

.form-group input[type="range"] {
    width: 100%;
}

.form-group output {
    display: inline-block;
    margin-left: 8px;
    font-weight: bold;
    color: #007bff;
}

.chart-container {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    margin-bottom: 15px;
}

.analytics-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .production-summary {
        grid-template-columns: 1fr;
    }
    
    .production-controls {
        flex-direction: column;
    }
    
    .quality-form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-group.span-2 {
        grid-column: span 1;
    }
    
    .analytics-dashboard {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="../assets/js/production-workflow.js?v=<?php echo time(); ?>"></script>
<script>
class ProductionWorkflowAdmin {
    constructor() {
        this.currentTab = 'workflows';
        this.workflows = new Map();
        this.stations = new Map();
        this.qualityChecks = new Map();
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadProductionData();
        this.setupModals();
        this.setupCharts();
        
        // Initialize production workflow if available
        if (window.productionWorkflow) {
            this.setupProductionWorkflowIntegration();
        }
    }
    
    setupEventListeners() {
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });
        
        // Filters
        document.getElementById('workflow-status-filter').addEventListener('change', () => this.applyFilters());
        document.getElementById('priority-filter').addEventListener('change', () => this.applyFilters());
        document.getElementById('search-workflows').addEventListener('input', () => this.applyFilters());
        
        // Refresh button
        document.getElementById('refresh-workflows').addEventListener('click', () => {
            this.loadProductionData();
        });
        
        // Quality check button
        document.getElementById('quality-check-btn').addEventListener('click', () => {
            this.showQualityModal();
        });
        
        // Range input updates
        document.querySelectorAll('input[type="range"]').forEach(range => {
            range.addEventListener('input', (e) => {
                const output = e.target.nextElementSibling;
                if (output && output.tagName === 'OUTPUT') {
                    output.textContent = e.target.value;
                }
            });
        });
    }
    
    switchTab(tabName) {
        // Update button states
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        
        // Update content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');
        
        this.currentTab = tabName;
        
        // Load tab-specific data
        switch(tabName) {
            case 'stations':
                this.loadStations();
                break;
            case 'quality':
                this.loadQualityChecks();
                break;
            case 'analytics':
                this.updateCharts();
                break;
        }
    }
    
    async loadProductionData() {
        // Simulated data for demo
        const mockData = {
            success: true,
            summary: {
                active_workflows: 12,
                completed_today: 8,
                average_completion_time: 4.5,
                quality_pass_rate: 95.2,
                station_utilization: 78.5,
                on_time_delivery: 92.1
            },
            workflows: [
                {
                    id: 1,
                    orderNumber: 'ORD-001',
                    customerName: 'Juan Pérez',
                    status: 'active',
                    priority: 'high',
                    currentStep: 'printing_sublimation',
                    completedSteps: [
                        {step: 'design_review'},
                        {step: 'design_preparation'},
                        {step: 'production_planning'}
                    ],
                    estimatedCompletion: '2024-12-20',
                    createdAt: '2024-12-15T10:30:00',
                    items: [
                        {name: 'Remera Personalizada', quantity: 2, isCustom: true}
                    ],
                    assignedStations: {
                        'printing_sublimation': 'Estación 1'
                    }
                }
            ]
        };
        
        this.workflows = new Map(mockData.workflows.map(w => [w.id, w]));
        this.updateSummaryCards(mockData.summary);
        this.renderWorkflows();
    }
    
    updateSummaryCards(summary) {
        document.getElementById('active-workflows-count').textContent = summary.active_workflows || 0;
        document.getElementById('completed-today-count').textContent = summary.completed_today || 0;
        document.getElementById('average-completion-time').textContent = (summary.average_completion_time || 0) + 'h';
        document.getElementById('quality-pass-rate').textContent = (summary.quality_pass_rate || 0) + '%';
        document.getElementById('station-utilization-rate').textContent = (summary.station_utilization || 0) + '%';
        document.getElementById('on-time-delivery-rate').textContent = (summary.on_time_delivery || 0) + '%';
    }
    
    renderWorkflows() {
        const container = document.getElementById('workflows-list');
        const workflowsArray = Array.from(this.workflows.values());
        
        if (workflowsArray.length === 0) {
            container.innerHTML = '<div class="empty-state"><p>No hay workflows activos</p></div>';
            return;
        }
        
        container.innerHTML = workflowsArray.map(workflow => `
            <div class="workflow-card ${workflow.priority} ${workflow.status}" data-workflow-id="${workflow.id}">
                <div class="workflow-header">
                    <div>
                        <h4>Orden #${workflow.orderNumber}</h4>
                        <p><strong>Cliente:</strong> ${workflow.customerName || 'N/A'}</p>
                        <span class="badge badge-${workflow.priority}">${this.getPriorityText(workflow.priority)}</span>
                        <span class="badge badge-${workflow.status}">${this.getStatusText(workflow.status)}</span>
                    </div>
                    <div class="workflow-actions">
                        <button class="btn btn-sm btn-primary" onclick="productionWorkflowAdmin.viewWorkflow(${workflow.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="workflow-info">
                    <p><strong>Paso Actual:</strong> ${this.getStepText(workflow.currentStep)}</p>
                    <p><strong>Estimado:</strong> ${new Date(workflow.estimatedCompletion).toLocaleDateString()}</p>
                    <p><strong>Items:</strong> ${workflow.items.length}</p>
                </div>
                
                <div class="workflow-steps">
                    ${this.renderWorkflowSteps(workflow)}
                </div>
            </div>
        `).join('');
    }
    
    renderWorkflowSteps(workflow) {
        const steps = ['design_review', 'design_preparation', 'production_planning', 'material_preparation', 
                      'printing_sublimation', 'quality_check', 'packaging', 'shipping_preparation', 'shipped'];
        
        return steps.map(step => {
            let status = 'pending';
            if (workflow.completedSteps.some(cs => cs.step === step)) {
                status = 'completed';
            } else if (workflow.currentStep === step) {
                status = 'active';
            }
            
            return `<div class="workflow-step ${status}">${this.getStepText(step)}</div>`;
        }).join('');
    }
    
    getPriorityText(priority) {
        const priorities = {
            'high': 'Alta',
            'medium': 'Media',
            'low': 'Baja'
        };
        return priorities[priority] || priority;
    }
    
    getStatusText(status) {
        const statuses = {
            'active': 'Activo',
            'completed': 'Completado',
            'error': 'Error',
            'cancelled': 'Cancelado'
        };
        return statuses[status] || status;
    }
    
    getStepText(step) {
        const steps = {
            'design_review': 'Revisión',
            'design_preparation': 'Preparación',
            'production_planning': 'Planificación',
            'material_preparation': 'Materiales',
            'printing_sublimation': 'Impresión',
            'quality_check': 'Calidad',
            'packaging': 'Empaque',
            'shipping_preparation': 'Envío',
            'shipped': 'Enviado'
        };
        return steps[step] || step;
    }
    
    viewWorkflow(workflowId) {
        const workflow = this.workflows.get(workflowId);
        if (!workflow) return;
        
        const details = `
            <div class="workflow-detail-grid">
                <div class="detail-section">
                    <h4>Información General</h4>
                    <p><strong>Orden:</strong> #${workflow.orderNumber}</p>
                    <p><strong>Cliente:</strong> ${workflow.customerName || 'N/A'}</p>
                    <p><strong>Estado:</strong> ${this.getStatusText(workflow.status)}</p>
                    <p><strong>Prioridad:</strong> ${this.getPriorityText(workflow.priority)}</p>
                    <p><strong>Creado:</strong> ${new Date(workflow.createdAt).toLocaleString()}</p>
                </div>
                
                <div class="detail-section">
                    <h4>Progreso</h4>
                    <p><strong>Paso Actual:</strong> ${this.getStepText(workflow.currentStep)}</p>
                    <p><strong>Completado:</strong> ${workflow.completedSteps.length}/9 pasos</p>
                    <p><strong>Estimado:</strong> ${new Date(workflow.estimatedCompletion).toLocaleDateString()}</p>
                </div>
                
                <div class="detail-section">
                    <h4>Items</h4>
                    ${workflow.items.map(item => `
                        <div class="item-detail">
                            <p><strong>${item.name}</strong></p>
                            <p>Cantidad: ${item.quantity}</p>
                            ${item.isCustom ? '<span class="badge badge-warning">Personalizado</span>' : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        document.getElementById('workflow-details').innerHTML = details;
        document.getElementById('workflow-modal').style.display = 'block';
    }
    
    showQualityModal() {
        // Populate workflow select
        const workflowSelect = document.getElementById('workflow-select');
        workflowSelect.innerHTML = '<option value="">Seleccionar workflow...</option>';
        
        this.workflows.forEach(workflow => {
            if (workflow.currentStep === 'quality_check') {
                const option = new Option(`Orden #${workflow.orderNumber}`, workflow.id);
                workflowSelect.appendChild(option);
            }
        });
        
        document.getElementById('quality-modal').style.display = 'block';
    }
    
    setupModals() {
        // Close buttons
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.onclick = () => {
                closeBtn.closest('.modal').style.display = 'none';
            };
        });
        
        // Quality form
        document.getElementById('quality-form').onsubmit = (e) => {
            e.preventDefault();
            this.submitQualityCheck();
        };
        
        document.getElementById('reject-quality').onclick = () => {
            this.rejectQuality();
        };
        
        document.getElementById('cancel-quality').onclick = () => {
            document.getElementById('quality-modal').style.display = 'none';
        };
        
        // Click outside to close
        window.onclick = (event) => {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    }
    
    async submitQualityCheck() {
        const workflowId = document.getElementById('workflow-select').value;
        const qualityData = {
            inspectorId: document.getElementById('inspector-id').value,
            printQuality: parseInt(document.getElementById('print-quality').value),
            colorAccuracy: parseInt(document.getElementById('color-accuracy').value),
            materialQuality: parseInt(document.getElementById('material-quality').value),
            notes: document.getElementById('quality-notes').value
        };
        
        console.log('Quality check submitted:', qualityData);
        document.getElementById('quality-modal').style.display = 'none';
        this.showNotification('Control de calidad completado exitosamente', 'success');
    }
    
    async rejectQuality() {
        const workflowId = document.getElementById('workflow-select').value;
        const notes = document.getElementById('quality-notes').value || 'Rechazado en control de calidad';
        
        console.log('Quality rejected for workflow:', workflowId);
        document.getElementById('quality-modal').style.display = 'none';
        this.showNotification('Producto enviado a reelaboración', 'warning');
    }
    
    setupCharts() {
        // Setup Chart.js charts for analytics
        if (typeof Chart !== 'undefined') {
            this.setupProductionChart();
            this.setupQualityChart();
        }
    }
    
    setupProductionChart() {
        const ctx = document.getElementById('production-chart');
        if (!ctx) return;
        
        this.productionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Workflows Completados',
                    data: [12, 19, 8, 15, 22, 18, 14],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Producción Semanal'
                    }
                }
            }
        });
    }
    
    setupQualityChart() {
        const ctx = document.getElementById('quality-chart');
        if (!ctx) return;
        
        this.qualityChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Aprobado', 'Rechazado', 'Reelaboración'],
                datasets: [{
                    data: [85, 5, 10],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Resultados de Calidad'
                    }
                }
            }
        });
    }
    
    showNotification(message, type = 'info') {
        if (window.AdminUtils && AdminUtils.showNotification) {
            AdminUtils.showNotification(message, type);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
    
    applyFilters() {
        // Filter implementation
        this.loadProductionData();
    }
    
    updateCharts() {
        // Update chart data
        if (this.productionChart) {
            this.productionChart.update();
        }
        if (this.qualityChart) {
            this.qualityChart.update();
        }
    }
    
    async loadStations() {
        // Mock station data
        const stationUtilization = {
            'station1': {
                name: 'Estación de Impresión 1',
                capacity: 20,
                currentLoad: 15,
                utilization: 75
            },
            'station2': {
                name: 'Estación de Impresión 2', 
                capacity: 20,
                currentLoad: 18,
                utilization: 90
            }
        };
        
        this.renderStations(stationUtilization);
    }
    
    renderStations(stationUtilization) {
        const container = document.getElementById('stations-grid');
        
        container.innerHTML = Object.entries(stationUtilization).map(([id, station]) => `
            <div class="station-card">
                <div class="station-header">
                    <h4>${station.name}</h4>
                    <span class="station-status ${station.utilization > 80 ? 'high' : station.utilization > 50 ? 'medium' : 'low'}">
                        ${station.utilization.toFixed(1)}%
                    </span>
                </div>
                
                <div class="station-info">
                    <p><strong>Capacidad:</strong> ${station.capacity}</p>
                    <p><strong>Carga Actual:</strong> ${station.currentLoad}</p>
                    <p><strong>Disponible:</strong> ${station.capacity - station.currentLoad}</p>
                </div>
                
                <div class="station-utilization">
                    <div class="utilization-bar" style="width: ${station.utilization}%"></div>
                </div>
            </div>
        `).join('');
    }
    
    async loadQualityChecks() {
        // Load quality check results
        console.log('Loading quality checks...');
    }
}

// Initialize production workflow admin
let productionWorkflowAdmin;
document.addEventListener('DOMContentLoaded', () => {
    productionWorkflowAdmin = new ProductionWorkflowAdmin();
});
</script>

<?php include 'admin-master-footer.php'; ?>