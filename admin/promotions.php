<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🎯 Promociones - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-bullhorn"></i> Promociones</h1>
                <p class="header-subtitle">Gestión de ofertas y promociones especiales</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-primary" onclick="createPromotion()">
                    <i class="fas fa-plus"></i>
                    Nueva Promoción
                </button>
            </div>
        </div>

        <div class="promotions-grid">
            <div class="promotion-card active">
                <div class="promotion-header">
                    <h3>Oferta de Verano 2024</h3>
                    <span class="promotion-status active">Activa</span>
                </div>
                <div class="promotion-details">
                    <p>20% de descuento en toda la tienda</p>
                    <div class="promotion-meta">
                        <span><i class="fas fa-calendar"></i> 01/12/2024 - 31/12/2024</span>
                        <span><i class="fas fa-shopping-cart"></i> 156 ventas</span>
                    </div>
                </div>
                <div class="promotion-actions">
                    <button class="action-btn edit" onclick="editPromotion(1)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn pause" onclick="pausePromotion(1)">
                        <i class="fas fa-pause"></i>
                    </button>
                </div>
            </div>
            
            <div class="promotion-card scheduled">
                <div class="promotion-header">
                    <h3>Black Friday 2024</h3>
                    <span class="promotion-status scheduled">Programada</span>
                </div>
                <div class="promotion-details">
                    <p>50% de descuento en productos seleccionados</p>
                    <div class="promotion-meta">
                        <span><i class="fas fa-calendar"></i> 29/11/2024 - 02/12/2024</span>
                        <span><i class="fas fa-clock"></i> Inicia en 15 días</span>
                    </div>
                </div>
                <div class="promotion-actions">
                    <button class="action-btn edit" onclick="editPromotion(2)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" onclick="deletePromotion(2)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createPromotion() {
    alert('Crear nueva promoción');
}

function editPromotion(id) {
    alert('Editar promoción ID: ' + id);
}

function pausePromotion(id) {
    alert('Pausar promoción ID: ' + id);
}

function deletePromotion(id) {
    if (confirm('¿Eliminar promoción?')) {
        alert('Promoción eliminada');
    }
}
</script>

<style>
.promotions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.promotion-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #ddd;
}

.promotion-card.active {
    border-left-color: #28a745;
}

.promotion-card.scheduled {
    border-left-color: #ffc107;
}

.promotion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.promotion-header h3 {
    margin: 0;
    color: #333;
}

.promotion-status {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.promotion-status.active {
    background: #d4edda;
    color: #155724;
}

.promotion-status.scheduled {
    background: #fff3cd;
    color: #856404;
}

.promotion-details p {
    color: #666;
    margin-bottom: 1rem;
}

.promotion-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}

.promotion-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

/* Optimización compacta para promotions.php */
.modern-admin-main { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.promotions-grid { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important; gap: 1rem !important; }
.promotion-card { padding: 1rem !important; }
.promotion-header { margin-bottom: 0.75rem !important; }
.promotion-header h3 { font-size: 1.1rem !important; }
.promotion-status { padding: 0.2rem 0.6rem !important; font-size: 0.75rem !important; }
.promotion-details p { font-size: 0.85rem !important; margin-bottom: 0.75rem !important; }
.promotion-meta { gap: 0.4rem !important; font-size: 0.8rem !important; }
.promotion-actions { margin-top: 0.75rem !important; padding-top: 0.75rem !important; gap: 0.4rem !important; }
.action-btn { width: 28px !important; height: 28px !important; font-size: 0.75rem !important; }
.tn-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>