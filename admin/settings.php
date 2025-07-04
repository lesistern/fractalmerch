<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '⚙️ Configuración - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <!-- Main Content -->
    <div class="modern-admin-main">
        <!-- Header Tiendanube Style -->
        <div class="tiendanube-header">
            <div class="header-left">
                <h1>Configuración</h1>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="exportSettings()">
                    <i class="fas fa-download"></i>
                    Exportar
                </button>
                <button class="tn-btn tn-btn-primary" onclick="saveSettings()">
                    <i class="fas fa-save"></i>
                    Guardar cambios
                </button>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="settings-dashboard">
            <div class="settings-navigation">
                <div class="settings-tabs">
                    <button class="settings-tab active" data-tab="general">
                        <i class="fas fa-cog"></i>
                        General
                    </button>
                    <button class="settings-tab" data-tab="store">
                        <i class="fas fa-store"></i>
                        Tienda
                    </button>
                    <button class="settings-tab" data-tab="payments">
                        <i class="fas fa-credit-card"></i>
                        Pagos
                    </button>
                    <button class="settings-tab" data-tab="shipping">
                        <i class="fas fa-truck"></i>
                        Envíos
                    </button>
                    <button class="settings-tab" data-tab="notifications">
                        <i class="fas fa-bell"></i>
                        Notificaciones
                    </button>
                    <button class="settings-tab" data-tab="security">
                        <i class="fas fa-shield-alt"></i>
                        Seguridad
                    </button>
                </div>
            </div>
            
            <div class="settings-content">
                <!-- General Tab -->
                <div class="settings-panel active" id="general-panel">
                    <div class="settings-section">
                        <h3>Información General</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nombre de la tienda</label>
                                <input type="text" class="form-input" value="Mi Tienda Sublime" placeholder="Nombre de tu tienda">
                            </div>
                            <div class="form-group">
                                <label>Email de contacto</label>
                                <input type="email" class="form-input" value="contacto@mitienda.com" placeholder="Email de contacto">
                            </div>
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="tel" class="form-input" value="+54 376 123-4567" placeholder="Teléfono de contacto">
                            </div>
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" class="form-input" value="Calle Sargento Acosta 3947, Posadas, Misiones" placeholder="Dirección física">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Store Tab -->
                <div class="settings-panel" id="store-panel">
                    <div class="settings-section">
                        <h3>Configuración de la Tienda</h3>
                        <div class="alert-card info">
                            <i class="fas fa-info-circle"></i>
                            <div class="alert-content">
                                <h4>Configuración de tienda en desarrollo</h4>
                                <p>Las opciones de personalización de tienda estarán disponibles próximamente</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Other panels -->
                <div class="settings-panel" id="payments-panel">
                    <div class="settings-section">
                        <h3>Métodos de Pago</h3>
                        <div class="alert-card info">
                            <i class="fas fa-info-circle"></i>
                            <div class="alert-content">
                                <h4>Configuración de pagos en desarrollo</h4>
                                <p>Integración con MercadoPago, Stripe y otros procesadores próximamente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-dashboard {
    display: grid;
    grid-template-columns: 250px 1fr;
    height: calc(100vh - 140px);
}

.settings-navigation {
    background: var(--admin-bg-secondary);
    border-right: 1px solid var(--admin-border-light);
    padding: 1.5rem 0;
}

.settings-tabs {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.settings-tab {
    background: none;
    border: none;
    padding: 0.75rem 1.5rem;
    text-align: left;
    color: var(--admin-text-secondary);
    font-size: 0.875rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.settings-tab:hover {
    background: var(--admin-bg-tertiary);
    color: var(--admin-text-primary);
}

.settings-tab.active {
    background: var(--admin-bg-tertiary);
    color: var(--admin-accent-blue);
    border-left-color: var(--admin-accent-blue);
    font-weight: 600;
}

.settings-content {
    padding: 1.5rem;
    overflow-y: auto;
}

.settings-panel {
    display: none;
}

.settings-panel.active {
    display: block;
}

.settings-section {
    background: var(--admin-bg-secondary);
    border: 1px solid var(--admin-border-light);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.settings-section h3 {
    color: var(--admin-text-primary);
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--admin-border-light);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    color: var(--admin-text-primary);
    font-weight: 500;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.form-input {
    padding: 0.75rem;
    border: 1px solid var(--admin-border-light);
    border-radius: 6px;
    background: var(--admin-bg-primary);
    color: var(--admin-text-primary);
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: var(--admin-accent-blue);
    box-shadow: 0 0 0 3px rgba(9, 105, 218, 0.1);
}

html.dark-mode .settings-navigation {
    background: var(--admin-bg-tertiary);
    border-color: var(--admin-border-light);
}

html.dark-mode .settings-section {
    background: var(--admin-bg-tertiary);
    border-color: var(--admin-border-light);
}
</style>

<!-- Toast Notifications Container -->
<div id="toastContainer" class="toast-container"></div>

<!-- Scripts -->
<script src="../assets/js/modern-admin.js?v=<?php echo time(); ?>"></script>
<script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>
<script>
// Settings tabs functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Settings page loaded');
    
    // Initialize settings tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs and panels
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding panel
            this.classList.add('active');
            const tabName = this.getAttribute('data-tab');
            document.getElementById(tabName + '-panel').classList.add('active');
        });
    });
});
</script>

</body>
</html>