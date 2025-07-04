<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🔌 Aplicaciones - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-puzzle-piece"></i> Aplicaciones</h1>
                <p class="header-subtitle">Tienda de aplicaciones y extensiones</p>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="browseApps()">
                    <i class="fas fa-search"></i>
                    Explorar Apps
                </button>
            </div>
        </div>

        <div class="apps-sections">
            <!-- Apps Instaladas -->
            <section class="apps-section">
                <h2>Aplicaciones Instaladas</h2>
                <div class="apps-grid">
                    <div class="app-card installed">
                        <div class="app-icon">
                            <i class="fas fa-envelope" style="color: #28a745;"></i>
                        </div>
                        <div class="app-info">
                            <h3>Mailchimp</h3>
                            <p>Email marketing automation</p>
                            <span class="app-status installed">Instalada</span>
                        </div>
                        <div class="app-actions">
                            <button class="app-btn secondary" onclick="configureApp('mailchimp')">Configurar</button>
                            <button class="app-btn danger" onclick="uninstallApp('mailchimp')">Desinstalar</button>
                        </div>
                    </div>

                    <div class="app-card installed">
                        <div class="app-icon">
                            <i class="fab fa-whatsapp" style="color: #25d366;"></i>
                        </div>
                        <div class="app-info">
                            <h3>WhatsApp Business</h3>
                            <p>Atención al cliente por WhatsApp</p>
                            <span class="app-status installed">Instalada</span>
                        </div>
                        <div class="app-actions">
                            <button class="app-btn secondary" onclick="configureApp('whatsapp')">Configurar</button>
                            <button class="app-btn danger" onclick="uninstallApp('whatsapp')">Desinstalar</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Apps Recomendadas -->
            <section class="apps-section">
                <h2>Aplicaciones Recomendadas</h2>
                <div class="apps-grid">
                    <div class="app-card">
                        <div class="app-icon">
                            <i class="fas fa-chart-line" style="color: #007bff;"></i>
                        </div>
                        <div class="app-info">
                            <h3>Google Analytics</h3>
                            <p>Análisis web avanzado</p>
                            <div class="app-rating">
                                <span class="stars">★★★★★</span>
                                <span class="rating-text">4.8 (1,234)</span>
                            </div>
                            <span class="app-price">Gratis</span>
                        </div>
                        <div class="app-actions">
                            <button class="app-btn primary" onclick="installApp('google-analytics')">Instalar</button>
                        </div>
                    </div>

                    <div class="app-card">
                        <div class="app-icon">
                            <i class="fas fa-shipping-fast" style="color: #ffc107;"></i>
                        </div>
                        <div class="app-info">
                            <h3>Andreani</h3>
                            <p>Gestión de envíos automática</p>
                            <div class="app-rating">
                                <span class="stars">★★★★☆</span>
                                <span class="rating-text">4.5 (892)</span>
                            </div>
                            <span class="app-price">Gratis</span>
                        </div>
                        <div class="app-actions">
                            <button class="app-btn primary" onclick="installApp('andreani')">Instalar</button>
                        </div>
                    </div>

                    <div class="app-card">
                        <div class="app-icon">
                            <i class="fas fa-star" style="color: #dc3545;"></i>
                        </div>
                        <div class="app-info">
                            <h3>Reviews & Ratings</h3>
                            <p>Sistema de reseñas avanzado</p>
                            <div class="app-rating">
                                <span class="stars">★★★★★</span>
                                <span class="rating-text">4.9 (567)</span>
                            </div>
                            <span class="app-price">$29/mes</span>
                        </div>
                        <div class="app-actions">
                            <button class="app-btn primary" onclick="installApp('reviews')">Instalar</button>
                        </div>
                    </div>

                    <div class="app-card">
                        <div class="app-icon">
                            <i class="fas fa-shield-alt" style="color: #6f42c1;"></i>
                        </div>
                        <div class="app-info">
                            <h3>Fraud Protection</h3>
                            <p>Protección contra fraudes</p>
                            <div class="app-rating">
                                <span class="stars">★★★★☆</span>
                                <span class="rating-text">4.6 (321)</span>
                            </div>
                            <span class="app-price">$49/mes</span>
                        </div>
                        <div class="app-actions">
                            <button class="app-btn primary" onclick="installApp('fraud-protection')">Instalar</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Categorías -->
            <section class="apps-section">
                <h2>Explorar por Categoría</h2>
                <div class="categories-grid">
                    <div class="category-card" onclick="browseCategory('marketing')">
                        <i class="fas fa-bullhorn"></i>
                        <h3>Marketing</h3>
                        <span>15 aplicaciones</span>
                    </div>
                    <div class="category-card" onclick="browseCategory('shipping')">
                        <i class="fas fa-truck"></i>
                        <h3>Envíos</h3>
                        <span>8 aplicaciones</span>
                    </div>
                    <div class="category-card" onclick="browseCategory('analytics')">
                        <i class="fas fa-chart-bar"></i>
                        <h3>Análisis</h3>
                        <span>12 aplicaciones</span>
                    </div>
                    <div class="category-card" onclick="browseCategory('customer-service')">
                        <i class="fas fa-headset"></i>
                        <h3>Atención al Cliente</h3>
                        <span>6 aplicaciones</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
function browseApps() {
    alert('Explorar tienda de aplicaciones - En desarrollo');
}

function configureApp(appId) {
    alert('Configurar aplicación: ' + appId);
}

function uninstallApp(appId) {
    if (confirm('¿Desinstalar aplicación ' + appId + '?')) {
        alert('Aplicación desinstalada');
    }
}

function installApp(appId) {
    alert('Instalando aplicación: ' + appId);
}

function browseCategory(category) {
    alert('Explorar categoría: ' + category);
}
</script>

<style>
.apps-sections {
    padding: 2rem;
}

.apps-section {
    margin-bottom: 3rem;
}

.apps-section h2 {
    margin-bottom: 1.5rem;
    color: #333;
    font-size: 1.3rem;
}

.apps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.app-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.app-card.installed {
    border-left: 4px solid #28a745;
}

.app-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 1.5rem;
}

.app-info h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.app-info p {
    color: #666;
    margin: 0 0 0.5rem 0;
    font-size: 0.9rem;
}

.app-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.stars {
    color: #ffc107;
}

.rating-text {
    font-size: 0.8rem;
    color: #666;
}

.app-status {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
}

.app-status.installed {
    background: #d4edda;
    color: #155724;
}

.app-price {
    font-weight: 600;
    color: #007bff;
}

.app-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: auto;
}

.app-btn {
    flex: 1;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 600;
}

.app-btn.primary {
    background: #007bff;
    color: white;
}

.app-btn.secondary {
    background: #6c757d;
    color: white;
}

.app-btn.danger {
    background: #dc3545;
    color: white;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.category-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
    cursor: pointer;
    transition: transform 0.2s;
}

.category-card:hover {
    transform: translateY(-2px);
}

.category-card i {
    font-size: 2rem;
    color: #007bff;
    margin-bottom: 1rem;
}

.category-card h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.category-card span {
    font-size: 0.9rem;
    color: #666;
}
</style>

<style>
/* Optimización compacta para applications */
.apps-sections { padding: 1.5rem !important; }
.apps-section { margin-bottom: 2rem !important; }
.apps-section h2 { margin-bottom: 1rem !important; font-size: 1.2rem !important; }
.apps-grid { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important; gap: 1rem !important; }
.app-card { padding: 1rem !important; gap: 0.75rem !important; }
.app-icon { width: 40px !important; height: 40px !important; font-size: 1.2rem !important; }
.app-info h3 { font-size: 1rem !important; margin: 0 0 0.4rem 0 !important; }
.app-info p { font-size: 0.8rem !important; margin: 0 0 0.4rem 0 !important; }
.app-rating { margin-bottom: 0.4rem !important; gap: 0.4rem !important; }
.rating-text { font-size: 0.75rem !important; }
.app-status { padding: 0.2rem 0.6rem !important; font-size: 0.75rem !important; }
.app-price { font-size: 0.85rem !important; }
.app-btn { padding: 0.4rem 0.8rem !important; font-size: 0.8rem !important; }
.categories-grid { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) !important; gap: 0.75rem !important; }
.category-card { padding: 1rem !important; }
.category-card i { font-size: 1.5rem !important; margin-bottom: 0.75rem !important; }
.category-card h3 { font-size: 1rem !important; margin: 0 0 0.4rem 0 !important; }
.category-card span { font-size: 0.8rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.tn-btn { padding: 0.5rem 1rem !important; font-size: 0.85rem !important; }
</style>

</body>
</html>