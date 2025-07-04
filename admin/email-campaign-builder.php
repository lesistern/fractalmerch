<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '✉️ Constructor de Campañas - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-magic"></i> Constructor de Campañas</h1>
                <p class="header-subtitle">Crea campañas de email marketing profesionales</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="saveDraft()">
                    <i class="fas fa-save"></i>
                    Guardar Borrador
                </button>
                <button class="tn-btn tn-btn-primary" onclick="sendCampaign()">
                    <i class="fas fa-paper-plane"></i>
                    Enviar Campaña
                </button>
            </div>
        </div>

        <!-- Campaign Builder Steps -->
        <section class="campaign-builder-section">
            <div class="builder-progress">
                <div class="progress-step active" data-step="1">
                    <div class="step-number">1</div>
                    <span>Configuración</span>
                </div>
                <div class="progress-step" data-step="2">
                    <div class="step-number">2</div>
                    <span>Diseño</span>
                </div>
                <div class="progress-step" data-step="3">
                    <div class="step-number">3</div>
                    <span>Audiencia</span>
                </div>
                <div class="progress-step" data-step="4">
                    <div class="step-number">4</div>
                    <span>Envío</span>
                </div>
            </div>

            <!-- Step 1: Campaign Configuration -->
            <div class="builder-step active" id="step-1">
                <h2>Configuración de la Campaña</h2>
                <div class="config-form">
                    <div class="form-group">
                        <label for="campaign-name">Nombre de la Campaña</label>
                        <input type="text" id="campaign-name" placeholder="Ej: Descuento Verano 2025" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label for="campaign-subject">Asunto del Email</label>
                        <input type="text" id="campaign-subject" placeholder="Ej: ¡50% OFF en toda la tienda!" class="form-input">
                        <small>Tip: Usa emojis y palabras llamativas para aumentar la tasa de apertura</small>
                    </div>

                    <div class="form-group">
                        <label for="sender-name">Nombre del Remitente</label>
                        <input type="text" id="sender-name" value="Sublime Store" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="sender-email">Email del Remitente</label>
                        <input type="email" id="sender-email" value="noreply@sublime.com" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="campaign-type">Tipo de Campaña</label>
                        <select id="campaign-type" class="form-select">
                            <option value="promotional">Promocional</option>
                            <option value="newsletter">Newsletter</option>
                            <option value="abandoned-cart">Carrito Abandonado</option>
                            <option value="welcome">Bienvenida</option>
                            <option value="product-announcement">Anuncio de Producto</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 2: Email Design -->
            <div class="builder-step" id="step-2">
                <h2>Diseño del Email</h2>
                <div class="design-workspace">
                    <div class="template-selector">
                        <h3>Selecciona una Plantilla</h3>
                        <div class="templates-grid">
                            <div class="template-card" onclick="selectTemplate('promotional')">
                                <div class="template-preview">
                                    <div class="preview-header" style="background: #ff6b6b;"></div>
                                    <div class="preview-content">
                                        <div class="preview-text"></div>
                                        <div class="preview-button"></div>
                                    </div>
                                </div>
                                <span>Promocional</span>
                            </div>
                            
                            <div class="template-card" onclick="selectTemplate('newsletter')">
                                <div class="template-preview">
                                    <div class="preview-header" style="background: #4ecdc4;"></div>
                                    <div class="preview-content">
                                        <div class="preview-text"></div>
                                        <div class="preview-text"></div>
                                    </div>
                                </div>
                                <span>Newsletter</span>
                            </div>
                            
                            <div class="template-card" onclick="selectTemplate('product')">
                                <div class="template-preview">
                                    <div class="preview-header" style="background: #45b7d1;"></div>
                                    <div class="preview-content">
                                        <div class="preview-product"></div>
                                        <div class="preview-button"></div>
                                    </div>
                                </div>
                                <span>Producto</span>
                            </div>
                        </div>
                    </div>

                    <div class="email-editor">
                        <h3>Editor de Contenido</h3>
                        <div class="editor-toolbar">
                            <button type="button" onclick="insertElement('text')"><i class="fas fa-font"></i> Texto</button>
                            <button type="button" onclick="insertElement('image')"><i class="fas fa-image"></i> Imagen</button>
                            <button type="button" onclick="insertElement('button')"><i class="fas fa-link"></i> Botón</button>
                            <button type="button" onclick="insertElement('product')"><i class="fas fa-shopping-bag"></i> Producto</button>
                        </div>
                        
                        <div class="email-preview" id="email-preview">
                            <div class="email-header">
                                <img src="../assets/images/logo.png" alt="Logo" style="height: 40px;">
                                <h1 id="preview-subject">Tu asunto aparecerá aquí</h1>
                            </div>
                            <div class="email-content" id="email-content">
                                <p>Haz clic en "Agregar Elemento" para comenzar a diseñar tu email.</p>
                            </div>
                            <div class="email-footer">
                                <p>© 2025 Sublime Store. Todos los derechos reservados.</p>
                                <p><a href="#">Darse de baja</a> | <a href="#">Ver en navegador</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Audience Selection -->
            <div class="builder-step" id="step-3">
                <h2>Seleccionar Audiencia</h2>
                <div class="audience-selector">
                    <div class="audience-options">
                        <div class="audience-card" onclick="selectAudience('all')">
                            <div class="audience-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>Todos los Suscriptores</h3>
                            <p>1,234 contactos</p>
                            <span class="audience-type">Lista completa</span>
                        </div>

                        <div class="audience-card" onclick="selectAudience('vip')">
                            <div class="audience-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h3>Clientes VIP</h3>
                            <p>89 contactos</p>
                            <span class="audience-type">Segmento premium</span>
                        </div>

                        <div class="audience-card" onclick="selectAudience('new')">
                            <div class="audience-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h3>Nuevos Suscriptores</h3>
                            <p>156 contactos</p>
                            <span class="audience-type">Últimos 30 días</span>
                        </div>

                        <div class="audience-card" onclick="selectAudience('abandoned')">
                            <div class="audience-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h3>Carritos Abandonados</h3>
                            <p>67 contactos</p>
                            <span class="audience-type">Sin comprar</span>
                        </div>

                        <div class="audience-card" onclick="selectAudience('custom')">
                            <div class="audience-icon">
                                <i class="fas fa-filter"></i>
                            </div>
                            <h3>Audiencia Personalizada</h3>
                            <p>Crear filtros</p>
                            <span class="audience-type">Configurar</span>
                        </div>
                    </div>

                    <div class="audience-preview">
                        <h3>Vista Previa de la Audiencia</h3>
                        <div class="audience-stats">
                            <div class="stat-item">
                                <span class="stat-number" id="audience-count">1,234</span>
                                <span class="stat-label">Contactos</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">23.5%</span>
                                <span class="stat-label">Tasa apertura promedio</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">4.8%</span>
                                <span class="stat-label">Tasa de clics promedio</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Send Configuration -->
            <div class="builder-step" id="step-4">
                <h2>Configuración de Envío</h2>
                <div class="send-config">
                    <div class="send-options">
                        <div class="send-option">
                            <input type="radio" id="send-now" name="send-type" value="now" checked>
                            <label for="send-now">
                                <i class="fas fa-bolt"></i>
                                Enviar Ahora
                                <span>La campaña se enviará inmediatamente</span>
                            </label>
                        </div>

                        <div class="send-option">
                            <input type="radio" id="send-schedule" name="send-type" value="schedule">
                            <label for="send-schedule">
                                <i class="fas fa-calendar-alt"></i>
                                Programar Envío
                                <span>Selecciona fecha y hora específica</span>
                            </label>
                            <div class="schedule-inputs">
                                <input type="date" id="send-date" class="form-input">
                                <input type="time" id="send-time" class="form-input">
                            </div>
                        </div>

                        <div class="send-option">
                            <input type="radio" id="send-test" name="send-type" value="test">
                            <label for="send-test">
                                <i class="fas fa-flask"></i>
                                Envío de Prueba
                                <span>Envía a emails específicos para probar</span>
                            </label>
                            <div class="test-inputs">
                                <input type="email" id="test-emails" placeholder="email1@test.com, email2@test.com" class="form-input">
                            </div>
                        </div>
                    </div>

                    <div class="campaign-summary">
                        <h3>Resumen de la Campaña</h3>
                        <div class="summary-item">
                            <span class="label">Nombre:</span>
                            <span class="value" id="summary-name">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Asunto:</span>
                            <span class="value" id="summary-subject">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Audiencia:</span>
                            <span class="value" id="summary-audience">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Plantilla:</span>
                            <span class="value" id="summary-template">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Estimado de apertura:</span>
                            <span class="value" id="summary-opens">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="builder-navigation">
                <button type="button" class="nav-btn prev" onclick="previousStep()" disabled>
                    <i class="fas fa-chevron-left"></i> Anterior
                </button>
                <button type="button" class="nav-btn next" onclick="nextStep()">
                    Siguiente <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </section>
    </div>
</div>

<script>
let currentStep = 1;
let campaignData = {
    name: '',
    subject: '',
    template: '',
    audience: '',
    audienceCount: 0
};

// Navigation Functions
function nextStep() {
    if (currentStep < 4) {
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        document.getElementById(`step-${currentStep}`).classList.remove('active');
        
        currentStep++;
        
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        document.getElementById(`step-${currentStep}`).classList.add('active');
        
        updateNavigation();
        updateSummary();
    }
}

function previousStep() {
    if (currentStep > 1) {
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        document.getElementById(`step-${currentStep}`).classList.remove('active');
        
        currentStep--;
        
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        document.getElementById(`step-${currentStep}`).classList.add('active');
        
        updateNavigation();
    }
}

function updateNavigation() {
    const prevBtn = document.querySelector('.nav-btn.prev');
    const nextBtn = document.querySelector('.nav-btn.next');
    
    prevBtn.disabled = currentStep === 1;
    
    if (currentStep === 4) {
        nextBtn.innerHTML = 'Finalizar <i class="fas fa-check"></i>';
        nextBtn.onclick = finalizeCampaign;
    } else {
        nextBtn.innerHTML = 'Siguiente <i class="fas fa-chevron-right"></i>';
        nextBtn.onclick = nextStep;
    }
}

// Template Functions
function selectTemplate(templateType) {
    campaignData.template = templateType;
    document.querySelectorAll('.template-card').forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    
    // Update email preview based on template
    updateEmailPreview(templateType);
}

function updateEmailPreview(templateType) {
    const content = document.getElementById('email-content');
    
    switch(templateType) {
        case 'promotional':
            content.innerHTML = `
                <div style="text-align: center; padding: 2rem; background: linear-gradient(135deg, #ff6b6b, #feca57);">
                    <h2 style="color: white; margin: 0;">¡OFERTA ESPECIAL!</h2>
                    <p style="color: white; font-size: 1.2rem;">50% OFF en toda la tienda</p>
                    <button style="background: white; color: #ff6b6b; padding: 1rem 2rem; border: none; border-radius: 25px; font-weight: bold; cursor: pointer;">
                        COMPRAR AHORA
                    </button>
                </div>
            `;
            break;
        case 'newsletter':
            content.innerHTML = `
                <h2>Novedades de la semana</h2>
                <p>Descubre nuestros nuevos productos y las últimas tendencias en personalización.</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 2rem 0;">
                    <div style="padding: 1rem; border: 1px solid #ddd;">
                        <h3>Nuevas Remeras</h3>
                        <p>Diseños únicos para esta temporada</p>
                    </div>
                    <div style="padding: 1rem; border: 1px solid #ddd;">
                        <h3>Buzos de Invierno</h3>
                        <p>Comodidad y estilo para el frío</p>
                    </div>
                </div>
            `;
            break;
        case 'product':
            content.innerHTML = `
                <div style="text-align: center;">
                    <img src="../assets/images/remera-placeholder.jpg" alt="Producto" style="width: 200px; height: 200px; object-fit: cover; border-radius: 8px;">
                    <h2>Nueva Remera Personalizada</h2>
                    <p style="font-size: 1.5rem; color: #ff6b6b; font-weight: bold;">$5.999</p>
                    <p>Diseña tu remera perfecta con nuestro editor online</p>
                    <button style="background: #007bff; color: white; padding: 1rem 2rem; border: none; border-radius: 6px; cursor: pointer;">
                        PERSONALIZAR AHORA
                    </button>
                </div>
            `;
            break;
    }
}

// Audience Functions
function selectAudience(audienceType) {
    const audienceCounts = {
        'all': 1234,
        'vip': 89,
        'new': 156,
        'abandoned': 67,
        'custom': 0
    };
    
    campaignData.audience = audienceType;
    campaignData.audienceCount = audienceCounts[audienceType];
    
    document.querySelectorAll('.audience-card').forEach(card => card.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    
    document.getElementById('audience-count').textContent = audienceCounts[audienceType];
}

// Form Updates
function updateSummary() {
    document.getElementById('summary-name').textContent = document.getElementById('campaign-name').value || '-';
    document.getElementById('summary-subject').textContent = document.getElementById('campaign-subject').value || '-';
    document.getElementById('summary-audience').textContent = campaignData.audienceCount ? `${campaignData.audienceCount} contactos` : '-';
    document.getElementById('summary-template').textContent = campaignData.template || '-';
    
    const estimatedOpens = Math.round(campaignData.audienceCount * 0.235);
    document.getElementById('summary-opens').textContent = estimatedOpens ? `${estimatedOpens} aperturas (23.5%)` : '-';
}

// Live preview updates
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('campaign-subject').addEventListener('input', function() {
        document.getElementById('preview-subject').textContent = this.value || 'Tu asunto aparecerá aquí';
        updateSummary();
    });
    
    document.getElementById('campaign-name').addEventListener('input', updateSummary);
});

// Editor Functions
function insertElement(elementType) {
    const content = document.getElementById('email-content');
    
    switch(elementType) {
        case 'text':
            content.innerHTML += '<p contenteditable="true">Escribe tu texto aquí...</p>';
            break;
        case 'image':
            content.innerHTML += '<div style="text-align: center; margin: 1rem 0;"><img src="../assets/images/placeholder.jpg" alt="Imagen" style="max-width: 100%; height: 200px; object-fit: cover; border-radius: 8px;"><br><small>Haz clic para cambiar imagen</small></div>';
            break;
        case 'button':
            content.innerHTML += '<div style="text-align: center; margin: 2rem 0;"><button style="background: #007bff; color: white; padding: 1rem 2rem; border: none; border-radius: 6px; cursor: pointer;">Botón de Acción</button></div>';
            break;
        case 'product':
            content.innerHTML += '<div style="border: 1px solid #ddd; padding: 1rem; margin: 1rem 0; border-radius: 8px;"><div style="display: flex; gap: 1rem;"><img src="../assets/images/remera-placeholder.jpg" style="width: 100px; height: 100px; object-fit: cover;"><div><h3>Nombre del Producto</h3><p style="color: #666;">Descripción del producto</p><p style="font-weight: bold; color: #007bff;">$5.999</p></div></div></div>';
            break;
    }
}

// Campaign Actions
function saveDraft() {
    alert('Borrador guardado exitosamente');
}

function sendCampaign() {
    if (currentStep === 4) {
        finalizeCampaign();
    } else {
        alert('Complete todos los pasos antes de enviar la campaña');
    }
}

function finalizeCampaign() {
    const sendType = document.querySelector('input[name="send-type"]:checked').value;
    
    switch(sendType) {
        case 'now':
            if (confirm('¿Enviar la campaña ahora a ' + campaignData.audienceCount + ' contactos?')) {
                alert('Campaña enviada exitosamente!');
                window.location.href = 'email-marketing.php';
            }
            break;
        case 'schedule':
            const date = document.getElementById('send-date').value;
            const time = document.getElementById('send-time').value;
            if (date && time) {
                alert(`Campaña programada para ${date} a las ${time}`);
                window.location.href = 'email-marketing.php';
            } else {
                alert('Selecciona fecha y hora para programar el envío');
            }
            break;
        case 'test':
            const testEmails = document.getElementById('test-emails').value;
            if (testEmails) {
                alert('Enviando prueba a: ' + testEmails);
            } else {
                alert('Ingresa emails para el envío de prueba');
            }
            break;
    }
}
</script>

<style>
/* Campaign Builder Styles */
.campaign-builder-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.builder-progress {
    display: flex;
    justify-content: space-between;
    margin-bottom: 3rem;
    position: relative;
}

.builder-progress::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    z-index: 2;
    background: white;
    padding: 0 1rem;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    transition: all 0.3s ease;
}

.progress-step.active .step-number {
    background: #007bff;
    color: white;
}

.progress-step span {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
}

.progress-step.active span {
    color: #007bff;
}

.builder-step {
    display: none;
    margin-bottom: 2rem;
}

.builder-step.active {
    display: block;
}

.builder-step h2 {
    margin-bottom: 2rem;
    color: #333;
}

/* Configuration Form */
.config-form {
    display: grid;
    gap: 1.5rem;
    max-width: 600px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: #333;
}

.form-input, .form-select {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.form-group small {
    color: #666;
    font-size: 0.85rem;
}

/* Design Workspace */
.design-workspace {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
}

.template-selector h3 {
    margin-bottom: 1rem;
    color: #333;
}

.templates-grid {
    display: grid;
    gap: 1rem;
}

.template-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.template-card:hover {
    border-color: #007bff;
}

.template-card.selected {
    border-color: #007bff;
    background: #f8f9fa;
}

.template-preview {
    width: 80px;
    height: 100px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin: 0 auto 0.5rem;
    overflow: hidden;
}

.preview-header {
    height: 20px;
    background: #007bff;
}

.preview-content {
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.preview-text {
    height: 4px;
    background: #ddd;
    border-radius: 2px;
}

.preview-button {
    height: 8px;
    background: #007bff;
    border-radius: 4px;
    margin-top: 0.25rem;
}

.preview-product {
    height: 20px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 2px;
}

.email-editor h3 {
    margin-bottom: 1rem;
    color: #333;
}

.editor-toolbar {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.editor-toolbar button {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.editor-toolbar button:hover {
    background: #e9ecef;
}

.email-preview {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    max-height: 600px;
    overflow-y: auto;
}

.email-header {
    background: #f8f9fa;
    padding: 1rem;
    border-bottom: 1px solid #ddd;
    text-align: center;
}

.email-header h1 {
    margin: 0.5rem 0 0 0;
    font-size: 1.2rem;
    color: #333;
}

.email-content {
    padding: 2rem;
    min-height: 300px;
}

.email-footer {
    background: #f8f9fa;
    padding: 1rem;
    border-top: 1px solid #ddd;
    text-align: center;
    font-size: 0.8rem;
    color: #666;
}

.email-footer a {
    color: #007bff;
    text-decoration: none;
}

/* Audience Selector */
.audience-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.audience-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.audience-card:hover {
    border-color: #007bff;
}

.audience-card.selected {
    border-color: #007bff;
    background: #f8f9fa;
}

.audience-icon {
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

.audience-card h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.audience-card p {
    margin: 0 0 0.5rem 0;
    font-size: 1.2rem;
    font-weight: bold;
    color: #007bff;
}

.audience-type {
    font-size: 0.8rem;
    color: #666;
    background: #e9ecef;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
}

.audience-preview {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
}

.audience-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    text-align: center;
}

.stat-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.stat-label {
    font-size: 0.8rem;
    color: #666;
}

/* Send Configuration */
.send-options {
    margin-bottom: 2rem;
}

.send-option {
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 1rem;
    overflow: hidden;
}

.send-option input[type="radio"] {
    display: none;
}

.send-option label {
    display: block;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.send-option label i {
    color: #007bff;
    margin-right: 0.75rem;
    font-size: 1.2rem;
}

.send-option label span {
    display: block;
    font-size: 0.9rem;
    color: #666;
    margin-top: 0.25rem;
}

.send-option input[type="radio"]:checked + label {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
}

.schedule-inputs, .test-inputs {
    display: none;
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #ddd;
}

.send-option input[type="radio"]:checked ~ .schedule-inputs,
.send-option input[type="radio"]:checked ~ .test-inputs {
    display: flex;
    gap: 1rem;
}

.campaign-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
}

.campaign-summary h3 {
    margin-bottom: 1rem;
    color: #333;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item .label {
    font-weight: 600;
    color: #333;
}

.summary-item .value {
    color: #666;
}

/* Navigation */
.builder-navigation {
    display: flex;
    justify-content: space-between;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

.nav-btn {
    padding: 0.75rem 1.5rem;
    border: 1px solid #ddd;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.nav-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.nav-btn.next {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.nav-btn:hover:not(:disabled) {
    background: #e9ecef;
}

.nav-btn.next:hover {
    background: #0056b3;
}

/* Optimización compacta */
.modern-admin-main { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.campaign-builder-section { padding: 1.5rem !important; }
.builder-progress { margin-bottom: 2rem !important; }
.step-number { width: 35px !important; height: 35px !important; font-size: 0.9rem !important; }
.progress-step span { font-size: 0.8rem !important; }
.builder-step h2 { margin-bottom: 1.5rem !important; font-size: 1.3rem !important; }
.config-form { gap: 1rem !important; }
.form-input, .form-select { padding: 0.6rem !important; font-size: 0.9rem !important; }
.design-workspace { grid-template-columns: 250px 1fr !important; gap: 1.5rem !important; }
.template-preview { width: 60px !important; height: 80px !important; }
.editor-toolbar { padding: 0.75rem !important; }
.editor-toolbar button { padding: 0.4rem 0.8rem !important; font-size: 0.8rem !important; }
.email-preview { max-height: 500px !important; }
.email-header, .email-footer { padding: 0.75rem !important; }
.email-content { padding: 1.5rem !important; min-height: 250px !important; }
.audience-options { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) !important; gap: 0.75rem !important; }
.audience-card { padding: 1rem !important; }
.audience-icon { width: 50px !important; height: 50px !important; font-size: 1.2rem !important; }
.send-option label { padding: 1rem !important; }
.campaign-summary { padding: 1rem !important; }
.builder-navigation { padding-top: 1.5rem !important; }
.nav-btn { padding: 0.6rem 1.2rem !important; font-size: 0.9rem !important; }
.tn-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>