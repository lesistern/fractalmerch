<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '💬 Chat - Panel Admin';
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
                <h1>Chat</h1>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="configureChatSettings()">
                    <i class="fas fa-cog"></i>
                    Configurar
                </button>
                <button class="tn-btn tn-btn-primary" onclick="toggleChatStatus()">
                    <i class="fas fa-toggle-on"></i>
                    Activar Chat
                </button>
            </div>
        </div>

        <!-- Chat Content -->
        <div class="chat-dashboard">
            <div class="alert-card info">
                <i class="fas fa-info-circle"></i>
                <div class="alert-content">
                    <h4>Función de Chat en Desarrollo</h4>
                    <p>El sistema de chat en tiempo real estará disponible próximamente. Permitirá comunicación directa con clientes.</p>
                </div>
                <button class="alert-action" onclick="learnMoreChat()">Más info</button>
            </div>
            
            <div class="chat-preview">
                <div class="chat-features">
                    <div class="feature-card">
                        <i class="fas fa-comments"></i>
                        <h3>Chat en Tiempo Real</h3>
                        <p>Responde a tus clientes instantáneamente</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-robot"></i>
                        <h3>Respuestas Automáticas</h3>
                        <p>Configura mensajes automáticos para consultas frecuentes</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-bell"></i>
                        <h3>Notificaciones</h3>
                        <p>Recibe alertas cuando lleguen nuevos mensajes</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-history"></i>
                        <h3>Historial</h3>
                        <p>Guarda todas las conversaciones con tus clientes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-dashboard {
    padding: 1.5rem;
}

.chat-preview {
    margin-top: 2rem;
}

.chat-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.feature-card {
    background: var(--admin-bg-secondary);
    border: 1px solid var(--admin-border-light);
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.2s ease;
}

.feature-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--admin-shadow-md);
}

.feature-card i {
    font-size: 2rem;
    color: var(--admin-accent-blue);
    margin-bottom: 1rem;
}

.feature-card h3 {
    color: var(--admin-text-primary);
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.feature-card p {
    color: var(--admin-text-secondary);
    font-size: 0.875rem;
    line-height: 1.5;
}

html.dark-mode .feature-card {
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
// Page-specific initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Chat page loaded');
});
</script>

</body>
</html>