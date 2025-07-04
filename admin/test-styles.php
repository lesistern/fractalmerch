<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🎨 Test de Estilos - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-palette"></i> Test de Estilos Tiendanube</h1>
                <p class="header-subtitle">Verificación de estilos CSS</p>
            </div>
            <div class="header-actions">
                <button class="tn-btn tn-btn-primary">
                    <i class="fas fa-plus"></i> Botón Primario
                </button>
                <button class="tn-btn tn-btn-secondary">
                    <i class="fas fa-download"></i> Botón Secundario
                </button>
            </div>
        </div>

        <!-- Test Cards -->
        <section class="tn-card">
            <div class="tn-card-header">
                <div class="header-left">
                    <h2>Tarjeta de Prueba</h2>
                    <span class="tn-badge tn-badge-neutral">Test</span>
                </div>
                <button class="tn-btn-ghost">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="tn-form">
                <div class="tn-form-group">
                    <label class="tn-label">Campo de Prueba</label>
                    <input type="text" class="tn-input" placeholder="Escribe algo aquí...">
                </div>
                
                <div class="tn-form-group">
                    <label class="tn-label">Área de Texto</label>
                    <textarea class="tn-textarea" placeholder="Escribe un mensaje..."></textarea>
                </div>
                
                <div class="tn-form-actions">
                    <button class="tn-btn tn-btn-primary">Guardar</button>
                    <button class="tn-btn tn-btn-secondary">Cancelar</button>
                </div>
            </div>
        </section>

        <!-- Test Table -->
        <section class="tn-card">
            <div class="tn-card-header">
                <div class="header-left">
                    <h2>Tabla de Prueba</h2>
                    <span class="tn-badge tn-badge-info">5 elementos</span>
                </div>
                <div class="tn-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar...">
                </div>
            </div>

            <div class="tn-table-container">
                <table class="tn-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="tn-table-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="tn-table-row">
                            <td>
                                <div class="tn-table-cell-content">
                                    <strong>Elemento 1</strong>
                                </div>
                            </td>
                            <td>
                                <span class="tn-badge tn-badge-success">Activo</span>
                            </td>
                            <td>
                                <span class="tn-date">04 Jul 2025</span>
                            </td>
                            <td>
                                <div class="tn-action-group">
                                    <button class="tn-btn-action">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="tn-btn-action tn-btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="tn-table-row">
                            <td>
                                <div class="tn-table-cell-content">
                                    <strong>Elemento 2</strong>
                                </div>
                            </td>
                            <td>
                                <span class="tn-badge tn-badge-warning">Pendiente</span>
                            </td>
                            <td>
                                <span class="tn-date">03 Jul 2025</span>
                            </td>
                            <td>
                                <div class="tn-action-group">
                                    <button class="tn-btn-action">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="tn-btn-action tn-btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Test Badges -->
        <section class="tn-card">
            <div class="tn-card-header">
                <h2>Badges de Prueba</h2>
            </div>
            
            <div style="padding: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <span class="tn-badge tn-badge-primary">Primary</span>
                <span class="tn-badge tn-badge-success">Success</span>
                <span class="tn-badge tn-badge-warning">Warning</span>
                <span class="tn-badge tn-badge-danger">Danger</span>
                <span class="tn-badge tn-badge-info">Info</span>
                <span class="tn-badge tn-badge-neutral">Neutral</span>
            </div>
        </section>

        <!-- Test Empty State -->
        <section class="tn-card">
            <div class="tn-empty-state">
                <div class="empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3>Estado Vacío</h3>
                <p>Esto es un ejemplo de estado vacío</p>
                <button class="tn-btn tn-btn-primary">
                    <i class="fas fa-plus"></i> Agregar Elemento
                </button>
            </div>
        </section>
    </div>
</div>

<style>
/* Debug styles */
.debug-info {
    position: fixed;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 1rem;
    border-radius: 8px;
    font-size: 0.8rem;
    z-index: 1000;
}
</style>

<div class="debug-info">
    Variables CSS activas:<br>
    --tn-primary: <span style="color: var(--tn-primary);">■</span><br>
    --tn-bg-secondary: <span style="background: var(--tn-bg-secondary); padding: 2px 8px;">test</span><br>
    --tn-border: <span style="border: 2px solid var(--tn-border); padding: 2px;">border</span>
</div>

</body>
</html>