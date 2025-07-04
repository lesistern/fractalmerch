<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '💬 Mensajes de Clientes - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <!-- Main Content -->
    <div class="modern-admin-main">
        <!-- Header -->
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-comments"></i> Mensajes de Clientes</h1>
                <p class="header-subtitle">Centro de atención al cliente y comunicación</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-primary" onclick="openComposeMessage()">
                    <i class="fas fa-plus"></i>
                    Nuevo Mensaje
                </button>
                <button class="tn-btn tn-btn-secondary" onclick="markAllAsRead()">
                    <i class="fas fa-check-double"></i>
                    Marcar Leídos
                </button>
            </div>
        </div>

        <!-- Stats y Filtros -->
        <div class="messages-overview">
            <div class="messages-stats">
                <div class="stat-item">
                    <div class="stat-number">23</div>
                    <div class="stat-label">Sin leer</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">156</div>
                    <div class="stat-label">Total mensajes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">2.5h</div>
                    <div class="stat-label">Tiempo respuesta</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">94%</div>
                    <div class="stat-label">Satisfacción</div>
                </div>
            </div>
            
            <div class="messages-filters">
                <select class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="unread">No leído</option>
                    <option value="read">Leído</option>
                    <option value="replied">Respondido</option>
                    <option value="closed">Cerrado</option>
                </select>
                <select class="filter-select">
                    <option value="">Todas las categorías</option>
                    <option value="support">Soporte</option>
                    <option value="sales">Ventas</option>
                    <option value="complaint">Reclamo</option>
                    <option value="suggestion">Sugerencia</option>
                </select>
                <input type="text" class="filter-search" placeholder="Buscar mensajes...">
            </div>
        </div>

        <!-- Lista de Mensajes -->
        <div class="messages-container">
            <div class="messages-list">
                <div class="message-item unread" onclick="openMessage('msg-001')">
                    <div class="message-avatar">
                        <img src="https://ui-avatars.com/api/?name=Ana+Rodriguez&background=007bff&color=fff" alt="Ana">
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="sender-name">Ana Rodríguez</span>
                            <span class="message-category support">Soporte</span>
                            <span class="message-time">Hace 2 horas</span>
                        </div>
                        <div class="message-subject">Problema con mi pedido #ORD-001</div>
                        <div class="message-preview">Hola, tengo un problema con mi pedido realizado ayer. No he recibido el email de confirmación y...</div>
                    </div>
                    <div class="message-status">
                        <div class="priority-indicator high"></div>
                        <div class="unread-dot"></div>
                    </div>
                </div>

                <div class="message-item read" onclick="openMessage('msg-002')">
                    <div class="message-avatar">
                        <img src="https://ui-avatars.com/api/?name=Carlos+Lopez&background=28a745&color=fff" alt="Carlos">
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="sender-name">Carlos López</span>
                            <span class="message-category sales">Ventas</span>
                            <span class="message-time">Ayer</span>
                        </div>
                        <div class="message-subject">Consulta sobre productos personalizados</div>
                        <div class="message-preview">Buenos días, me gustaría saber si pueden hacer remeras con diseños personalizados para mi empresa...</div>
                    </div>
                    <div class="message-status">
                        <div class="priority-indicator medium"></div>
                        <i class="fas fa-reply replied-icon" title="Respondido"></i>
                    </div>
                </div>

                <div class="message-item unread" onclick="openMessage('msg-003')">
                    <div class="message-avatar">
                        <img src="https://ui-avatars.com/api/?name=Maria+Garcia&background=dc3545&color=fff" alt="Maria">
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="sender-name">María García</span>
                            <span class="message-category complaint">Reclamo</span>
                            <span class="message-time">Hace 5 horas</span>
                        </div>
                        <div class="message-subject">Producto defectuoso recibido</div>
                        <div class="message-preview">Recibí mi pedido hoy pero la taza llegó con una grieta. Necesito que me repongan el producto...</div>
                    </div>
                    <div class="message-status">
                        <div class="priority-indicator high"></div>
                        <div class="unread-dot"></div>
                    </div>
                </div>

                <div class="message-item read" onclick="openMessage('msg-004')">
                    <div class="message-avatar">
                        <img src="https://ui-avatars.com/api/?name=Luis+Martinez&background=ffc107&color=000" alt="Luis">
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="sender-name">Luis Martínez</span>
                            <span class="message-category suggestion">Sugerencia</span>
                            <span class="message-time">Hace 2 días</span>
                        </div>
                        <div class="message-subject">Sugerencia para mejorar el checkout</div>
                        <div class="message-preview">Hola, quería sugerir que agreguen la opción de pago en cuotas sin interés. Sería muy útil...</div>
                    </div>
                    <div class="message-status">
                        <div class="priority-indicator low"></div>
                        <i class="fas fa-check closed-icon" title="Cerrado"></i>
                    </div>
                </div>
            </div>

            <!-- Panel de Mensaje -->
            <div class="message-panel" id="messagePanel" style="display: none;">
                <div class="panel-header">
                    <div class="panel-title">
                        <h3 id="panelSubject">Problema con mi pedido #ORD-001</h3>
                        <div class="panel-meta">
                            <span class="panel-sender">Ana Rodríguez</span>
                            <span class="panel-email">ana@email.com</span>
                            <span class="panel-time">15/12/2024 16:30</span>
                        </div>
                    </div>
                    <div class="panel-actions">
                        <button class="panel-btn" onclick="markAsResolved()">
                            <i class="fas fa-check"></i>
                            Marcar Resuelto
                        </button>
                        <button class="panel-btn" onclick="closeMessage()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="panel-content">
                    <div class="message-thread">
                        <div class="thread-message customer">
                            <div class="thread-avatar">
                                <img src="https://ui-avatars.com/api/?name=Ana+Rodriguez&background=007bff&color=fff" alt="Ana">
                            </div>
                            <div class="thread-content">
                                <div class="thread-header">
                                    <span class="thread-sender">Ana Rodríguez</span>
                                    <span class="thread-time">Hace 2 horas</span>
                                </div>
                                <div class="thread-text">
                                    Hola, tengo un problema con mi pedido realizado ayer. No he recibido el email de confirmación y cuando intento rastrear el pedido con el número que me dieron, no aparece nada. ¿Podrían ayudarme a verificar el estado de mi pedido #ORD-001?
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="reply-section">
                        <div class="reply-tabs">
                            <button class="reply-tab active" onclick="switchReplyTab('compose')">
                                <i class="fas fa-reply"></i>
                                Responder
                            </button>
                            <button class="reply-tab" onclick="switchReplyTab('templates')">
                                <i class="fas fa-file-alt"></i>
                                Plantillas
                            </button>
                        </div>

                        <div class="reply-content">
                            <div class="reply-compose" id="composeTab">
                                <textarea class="reply-textarea" placeholder="Escribe tu respuesta aquí..."></textarea>
                                <div class="reply-actions">
                                    <button class="reply-btn send" onclick="sendReply()">
                                        <i class="fas fa-paper-plane"></i>
                                        Enviar Respuesta
                                    </button>
                                    <button class="reply-btn save" onclick="saveDraft()">
                                        <i class="fas fa-save"></i>
                                        Guardar Borrador
                                    </button>
                                </div>
                            </div>

                            <div class="reply-templates" id="templatesTab" style="display: none;">
                                <div class="template-list">
                                    <div class="template-item" onclick="useTemplate('order-status')">
                                        <div class="template-title">Consulta sobre estado de pedido</div>
                                        <div class="template-preview">Gracias por contactarnos. Hemos verificado tu pedido...</div>
                                    </div>
                                    <div class="template-item" onclick="useTemplate('product-issue')">
                                        <div class="template-title">Problema con producto</div>
                                        <div class="template-preview">Lamentamos los inconvenientes con tu producto...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../assets/js/modern-admin.js?v=<?php echo time(); ?>"></script>
<script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>

<script>
function openMessage(messageId) {
    const panel = document.getElementById('messagePanel');
    panel.style.display = 'block';
    
    // Marcar como leído
    const messageItem = document.querySelector(`[onclick="openMessage('${messageId}')"]`);
    messageItem.classList.remove('unread');
    messageItem.classList.add('read');
    
    // Remover punto de no leído
    const unreadDot = messageItem.querySelector('.unread-dot');
    if (unreadDot) {
        unreadDot.remove();
    }
}

function closeMessage() {
    document.getElementById('messagePanel').style.display = 'none';
}

function switchReplyTab(tab) {
    document.querySelectorAll('.reply-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`[onclick="switchReplyTab('${tab}')"]`).classList.add('active');
    
    document.getElementById('composeTab').style.display = tab === 'compose' ? 'block' : 'none';
    document.getElementById('templatesTab').style.display = tab === 'templates' ? 'block' : 'none';
}

function sendReply() {
    const textarea = document.querySelector('.reply-textarea');
    if (textarea.value.trim()) {
        alert('Respuesta enviada');
        textarea.value = '';
        closeMessage();
    }
}

function saveDraft() {
    alert('Borrador guardado');
}

function useTemplate(templateId) {
    const templates = {
        'order-status': 'Gracias por contactarnos. Hemos verificado tu pedido y te confirmamos que está siendo procesado. Recibirás un email con el código de seguimiento una vez que sea despachado.',
        'product-issue': 'Lamentamos los inconvenientes con tu producto. Vamos a proceder con el reemplazo inmediatamente. Te contactaremos en las próximas 24 horas para coordinar la entrega.'
    };
    
    document.querySelector('.reply-textarea').value = templates[templateId];
    switchReplyTab('compose');
}

function markAsResolved() {
    alert('Mensaje marcado como resuelto');
    closeMessage();
}

function openComposeMessage() {
    alert('Abrir compositor de nuevo mensaje');
}

function markAllAsRead() {
    document.querySelectorAll('.message-item.unread').forEach(item => {
        item.classList.remove('unread');
        item.classList.add('read');
        const unreadDot = item.querySelector('.unread-dot');
        if (unreadDot) unreadDot.remove();
    });
    alert('Todos los mensajes marcados como leídos');
}
</script>

<style>
.messages-overview {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.messages-stats {
    display: flex;
    gap: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #007bff;
}

.stat-label {
    font-size: 0.8rem;
    color: #666;
    margin-top: 0.25rem;
}

.messages-filters {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.messages-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    height: calc(100vh - 300px);
}

.messages-list {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow-y: auto;
}

.message-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background 0.2s;
}

.message-item:hover {
    background: #f8f9fa;
}

.message-item.unread {
    background: #f0f8ff;
    border-left: 3px solid #007bff;
}

.message-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.message-content {
    flex: 1;
    min-width: 0;
}

.message-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
    flex-wrap: wrap;
}

.sender-name {
    font-weight: 600;
    color: #333;
}

.message-category {
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}

.message-category.support {
    background: #e3f2fd;
    color: #1976d2;
}

.message-category.sales {
    background: #e8f5e8;
    color: #2e7d32;
}

.message-category.complaint {
    background: #ffebee;
    color: #c62828;
}

.message-category.suggestion {
    background: #fff3e0;
    color: #f57c00;
}

.message-time {
    font-size: 0.8rem;
    color: #666;
    margin-left: auto;
}

.message-subject {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.message-preview {
    font-size: 0.9rem;
    color: #666;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.message-status {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.priority-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.priority-indicator.high {
    background: #dc3545;
}

.priority-indicator.medium {
    background: #ffc107;
}

.priority-indicator.low {
    background: #28a745;
}

.unread-dot {
    width: 8px;
    height: 8px;
    background: #007bff;
    border-radius: 50%;
}

.replied-icon,
.closed-icon {
    color: #28a745;
    font-size: 0.8rem;
}

.message-panel {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
}

.panel-title h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.panel-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.panel-actions {
    display: flex;
    gap: 0.5rem;
}

.panel-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.panel-btn:hover {
    background: #f8f9fa;
}

.panel-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.message-thread {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
}

.thread-message {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.thread-avatar img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
}

.thread-content {
    flex: 1;
}

.thread-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.thread-sender {
    font-weight: 600;
    color: #333;
}

.thread-time {
    font-size: 0.8rem;
    color: #666;
}

.thread-text {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    line-height: 1.5;
}

.reply-section {
    border-top: 1px solid #eee;
}

.reply-tabs {
    display: flex;
    border-bottom: 1px solid #eee;
}

.reply-tab {
    padding: 1rem 1.5rem;
    border: none;
    background: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
}

.reply-tab.active {
    color: #007bff;
    border-bottom: 2px solid #007bff;
}

.reply-content {
    padding: 1rem;
}

.reply-textarea {
    width: 100%;
    min-height: 100px;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
    font-family: inherit;
}

.reply-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.reply-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.reply-btn.send {
    background: #007bff;
    color: white;
}

.reply-btn.save {
    background: #6c757d;
    color: white;
}

.template-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.template-item {
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}

.template-item:hover {
    background: #f8f9fa;
}

.template-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.template-preview {
    font-size: 0.9rem;
    color: #666;
}

@media (max-width: 768px) {
    .messages-container {
        grid-template-columns: 1fr;
    }
    
    .messages-overview {
        flex-direction: column;
        align-items: stretch;
    }
    
    .messages-stats {
        justify-content: space-around;
    }
    
    .panel-header {
        flex-direction: column;
        gap: 1rem;
    }
}

/* Optimización compacta para client-messages.php */
.modern-admin-main { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.messages-overview { padding: 1rem 1.5rem !important; margin-bottom: 1.5rem !important; }
.messages-stats { gap: 1rem !important; }
.stat-item { margin: 0 !important; }
.stat-number { font-size: 1.3rem !important; }
.stat-label { font-size: 0.75rem !important; }
.messages-filters { gap: 0.75rem !important; }
.filter-select, .filter-search { padding: 0.5rem !important; font-size: 0.85rem !important; }
.messages-container { gap: 1.5rem !important; height: calc(100vh - 280px) !important; }
.message-item { padding: 0.75rem !important; gap: 0.75rem !important; }
.message-avatar img { width: 35px !important; height: 35px !important; }
.message-header { margin-bottom: 0.2rem !important; }
.sender-name { font-size: 0.9rem !important; }
.message-category { padding: 0.1rem 0.4rem !important; font-size: 0.65rem !important; }
.message-time { font-size: 0.75rem !important; }
.message-subject { font-size: 0.9rem !important; margin-bottom: 0.2rem !important; }
.message-preview { font-size: 0.8rem !important; max-height: 50px !important; overflow: hidden !important; }
.panel-header { padding: 1rem 1.5rem !important; }
.panel-title h3 { font-size: 1.1rem !important; }
.panel-meta { font-size: 0.8rem !important; }
.panel-btn { padding: 0.4rem 0.8rem !important; font-size: 0.8rem !important; }
.thread-message { margin-bottom: 0.75rem !important; }
.thread-avatar img { width: 28px !important; height: 28px !important; }
.reply-tabs { border-bottom: 1px solid #eee !important; }
.reply-tab { padding: 0.75rem 1rem !important; font-size: 0.85rem !important; }
.reply-content { padding: 0.75rem !important; }
.reply-textarea { min-height: 80px !important; padding: 0.6rem !important; }
.reply-btn { padding: 0.4rem 0.8rem !important; font-size: 0.8rem !important; }
.tn-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>