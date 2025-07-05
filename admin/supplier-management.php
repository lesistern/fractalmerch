<?php
$pageTitle = '🏢 Gestión de Proveedores - Admin Panel';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-truck"></i> Gestión de Proveedores</h1>
    <p>Administra proveedores, contactos y acuerdos comerciales</p>
    
    <div class="page-actions">
        <button class="btn btn-primary" onclick="AdminUtils.modal.show('add-supplier-modal')">
            <i class="fas fa-plus"></i> Agregar Proveedor
        </button>
        <button class="btn btn-secondary" onclick="exportSuppliers()">
            <i class="fas fa-download"></i> Exportar
        </button>
    </div>
</div>

<!-- Estadísticas de Proveedores -->
<div class="content-card">
    <h3><i class="fas fa-chart-bar"></i> Resumen de Proveedores</h3>
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-icon total">
                <i class="fas fa-truck"></i>
            </div>
            <div class="stat-content">
                <h4>15</h4>
                <p>Total Proveedores</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon active">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h4>12</h4>
                <p>Activos</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h4>3</h4>
                <p>Pendientes</p>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Proveedores -->
<div class="content-card">
    <h3><i class="fas fa-list"></i> Lista de Proveedores</h3>
    
    <div class="empty-state">
        <i class="fas fa-truck"></i>
        <p>Funcionalidad en desarrollo</p>
        <p>La gestión completa de proveedores estará disponible próximamente</p>
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

.stat-icon.total { background: #007bff; }
.stat-icon.active { background: #28a745; }
.stat-icon.pending { background: #ffc107; }

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

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}
</style>

<script>
function exportSuppliers() {
    AdminUtils.showNotification('Funcionalidad en desarrollo', 'info');
}
</script>

<?php include 'admin-master-footer.php'; ?>