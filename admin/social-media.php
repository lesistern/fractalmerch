<?php
$pageTitle = '📱 Redes Sociales';
include 'admin-master-header.php';

// Simulación de datos de redes sociales
$socialStats = [
    'facebook' => ['followers' => 2456, 'posts' => 124, 'engagement' => 4.2, 'connected' => true],
    'instagram' => ['followers' => 3789, 'posts' => 89, 'engagement' => 6.8, 'connected' => true],
    'pinterest' => ['followers' => 567, 'posts' => 45, 'engagement' => 2.1, 'connected' => false],
    'youtube' => ['followers' => 890, 'posts' => 12, 'engagement' => 3.5, 'connected' => false]
];

$recentPosts = [
    ['platform' => 'Instagram', 'content' => 'Nueva colección de remeras personalizadas', 'likes' => 89, 'comments' => 12, 'shares' => 5, 'date' => '2025-07-02'],
    ['platform' => 'Facebook', 'content' => 'Descuento especial del 20% en buzos', 'likes' => 156, 'comments' => 23, 'shares' => 18, 'date' => '2025-07-01'],
    ['platform' => 'Instagram', 'content' => 'Tutorial: Cómo diseñar tu remera perfecta', 'likes' => 234, 'comments' => 45, 'shares' => 67, 'date' => '2025-06-30']
];
?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-share-alt"></i> Redes Sociales</h1>
        <p class="page-subtitle">Gestiona tu presencia en redes sociales</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-outline-primary" onclick="schedulePost()">
            <i class="fas fa-calendar-alt"></i>
            Programar Post
        </button>
        <button class="btn btn-primary" onclick="createPost()">
            <i class="fas fa-plus"></i>
            Nuevo Post
        </button>
    </div>
</div>

<!-- Social Media Overview -->
<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-chart-bar"></i> Resumen General</h3>
    </div>
    <div class="card-body">
        <div class="social-stats-grid">
            <div class="social-stat-card facebook <?php echo $socialStats['facebook']['connected'] ? 'connected' : 'disconnected'; ?>">
                <div class="platform-header">
                    <i class="fab fa-facebook"></i>
                    <h3>Facebook</h3>
                    <span class="connection-status">
                        <?php echo $socialStats['facebook']['connected'] ? 'Conectado' : 'Desconectado'; ?>
                    </span>
                </div>
                <div class="platform-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($socialStats['facebook']['followers']); ?></span>
                        <span class="stat-label">Seguidores</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $socialStats['facebook']['posts']; ?></span>
                        <span class="stat-label">Posts</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $socialStats['facebook']['engagement']; ?>%</span>
                        <span class="stat-label">Engagement</span>
                    </div>
                </div>
                <button class="platform-btn" onclick="manageFacebook()">
                    <?php echo $socialStats['facebook']['connected'] ? 'Gestionar' : 'Conectar'; ?>
                </button>
            </div>

            <div class="social-stat-card instagram <?php echo $socialStats['instagram']['connected'] ? 'connected' : 'disconnected'; ?>">
                <div class="platform-header">
                    <i class="fab fa-instagram"></i>
                    <h3>Instagram</h3>
                    <span class="connection-status">
                        <?php echo $socialStats['instagram']['connected'] ? 'Conectado' : 'Desconectado'; ?>
                    </span>
                </div>
                <div class="platform-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($socialStats['instagram']['followers']); ?></span>
                        <span class="stat-label">Seguidores</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $socialStats['instagram']['posts']; ?></span>
                        <span class="stat-label">Posts</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $socialStats['instagram']['engagement']; ?>%</span>
                        <span class="stat-label">Engagement</span>
                    </div>
                </div>
                <button class="platform-btn" onclick="manageInstagram()">
                    <?php echo $socialStats['instagram']['connected'] ? 'Gestionar' : 'Conectar'; ?>
                </button>
            </div>

            <div class="social-stat-card pinterest <?php echo $socialStats['pinterest']['connected'] ? 'connected' : 'disconnected'; ?>">
                <div class="platform-header">
                    <i class="fab fa-pinterest"></i>
                    <h3>Pinterest</h3>
                    <span class="connection-status">
                        <?php echo $socialStats['pinterest']['connected'] ? 'Conectado' : 'Desconectado'; ?>
                    </span>
                </div>
                <div class="platform-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($socialStats['pinterest']['followers']); ?></span>
                        <span class="stat-label">Seguidores</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $socialStats['pinterest']['posts']; ?></span>
                        <span class="stat-label">Pins</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $socialStats['pinterest']['engagement']; ?>%</span>
                        <span class="stat-label">Engagement</span>
                    </div>
                </div>
                <button class="platform-btn" onclick="connectPinterest()">
                    <?php echo $socialStats['pinterest']['connected'] ? 'Gestionar' : 'Conectar'; ?>
                </button>
            </div>

            <div class="social-stat-card youtube <?php echo $socialStats['youtube']['connected'] ? 'connected' : 'disconnected'; ?>">
                <div class="platform-header">
                    <i class="fab fa-youtube"></i>
                    <h3>YouTube</h3>
                    <span class="connection-status">
                        <?php echo $socialStats['youtube']['connected'] ? 'Conectado' : 'Desconectado'; ?>
                    </span>
                </div>
                <div class="platform-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($socialStats['youtube']['followers']); ?></span>
                        <span class="stat-label">Suscriptores</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $socialStats['youtube']['posts']; ?></span>
                        <span class="stat-label">Videos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $socialStats['youtube']['engagement']; ?>%</span>
                        <span class="stat-label">Engagement</span>
                    </div>
                </div>
                <button class="platform-btn" onclick="connectYoutube()">
                    <?php echo $socialStats['youtube']['connected'] ? 'Gestionar' : 'Conectar'; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Posts -->
<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Posts Recientes</h3>
    </div>
    <div class="card-body">
        <div class="posts-table-container">
            <table class="posts-table">
                <thead>
                    <tr>
                        <th>Plataforma</th>
                        <th>Contenido</th>
                        <th>Likes</th>
                        <th>Comentarios</th>
                        <th>Compartidos</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPosts as $index => $post): ?>
                    <tr>
                        <td>
                            <span class="platform-badge <?php echo strtolower($post['platform']); ?>">
                                <i class="fab fa-<?php echo strtolower($post['platform']); ?>"></i>
                                <?php echo $post['platform']; ?>
                            </span>
                        </td>
                        <td><?php echo $post['content']; ?></td>
                        <td><?php echo $post['likes']; ?></td>
                        <td><?php echo $post['comments']; ?></td>
                        <td><?php echo $post['shares']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($post['date'])); ?></td>
                        <td>
                            <button class="action-btn edit" onclick="editPost(<?php echo $index; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn share" onclick="resharePost(<?php echo $index; ?>)">
                                <i class="fas fa-share"></i>
                            </button>
                            <button class="action-btn stats" onclick="viewPostStats(<?php echo $index; ?>)">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Social Media Tools -->
<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-tools"></i> Herramientas Sociales</h3>
    </div>
    <div class="card-body">
        <div class="tools-grid">
            <div class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Programador de Posts</h3>
                <p>Programa tus publicaciones para múltiples plataformas</p>
                <button class="tool-btn" onclick="openScheduler()">Abrir Programador</button>
            </div>

            <div class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-hashtag"></i>
                </div>
                <h3>Generador de Hashtags</h3>
                <p>Encuentra los mejores hashtags para tus posts</p>
                <button class="tool-btn" onclick="openHashtagGenerator()">Generar Hashtags</button>
            </div>

            <div class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Analytics Social</h3>
                <p>Analiza el rendimiento de tus redes sociales</p>
                <button class="tool-btn" onclick="openSocialAnalytics()">Ver Analytics</button>
            </div>

            <div class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Gestión de Comunidad</h3>
                <p>Responde mensajes y comentarios desde un lugar</p>
                <button class="tool-btn" onclick="openCommunityManager()">Gestionar</button>
            </div>

            <div class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3>Social Commerce</h3>
                <p>Configura tu catálogo de productos sociales</p>
                <button class="tool-btn" onclick="configureSocialCommerce()">Configurar</button>
            </div>

            <div class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h3>Promociones Sociales</h3>
                <p>Crea concursos y promociones especiales</p>
                <button class="tool-btn" onclick="createSocialPromo()">Crear Promoción</button>
            </div>
        </div>
    </div>
</div>

<!-- Content Calendar -->
<div class="content-card">
    <div class="card-header">
        <h3><i class="fas fa-calendar"></i> Calendario de Contenido</h3>
    </div>
    <div class="card-body">
        <div class="calendar-container">
            <div class="calendar-header">
                <button class="calendar-nav" onclick="previousMonth()">‹</button>
                <h3 id="calendar-month">Julio 2025</h3>
                <button class="calendar-nav" onclick="nextMonth()">›</button>
            </div>
            <div class="calendar-grid">
                <div class="calendar-day header">Lun</div>
                <div class="calendar-day header">Mar</div>
                <div class="calendar-day header">Mié</div>
                <div class="calendar-day header">Jue</div>
                <div class="calendar-day header">Vie</div>
                <div class="calendar-day header">Sáb</div>
                <div class="calendar-day header">Dom</div>
                
                <!-- Sample calendar days -->
                <div class="calendar-day">1</div>
                <div class="calendar-day">2</div>
                <div class="calendar-day has-post" onclick="viewDayPosts('2025-07-03')">
                    3
                    <div class="post-indicator instagram"></div>
                </div>
                <div class="calendar-day">4</div>
                <div class="calendar-day has-post" onclick="viewDayPosts('2025-07-05')">
                    5
                    <div class="post-indicator facebook"></div>
                    <div class="post-indicator instagram"></div>
                </div>
                <div class="calendar-day">6</div>
                <div class="calendar-day">7</div>
            </div>
        </div>
    </div>
</div>

<script>
// Social Media Functions
function createPost() {
    alert('Crear nuevo post multi-plataforma');
}

function schedulePost() {
    alert('Programar publicación para múltiples redes');
}

function manageFacebook() {
    window.location.href = 'facebook-meta.php';
}

function manageInstagram() {
    alert('Gestionar Instagram:\n\n1. Instagram Shopping\n2. Stories programadas\n3. IGTV\n4. Reels');
}

function connectPinterest() {
    alert('Conectar Pinterest:\n\n1. Verificar dominio\n2. Crear Rich Pins\n3. Configurar catálogo');
}

function connectYoutube() {
    alert('Conectar YouTube:\n\n1. Canal empresarial\n2. Configurar shorts\n3. Monetización');
}

function editPost(id) {
    alert('Editando post ID: ' + id);
}

function resharePost(id) {
    if (confirm('¿Compartir nuevamente este post?')) {
        alert('Post compartido exitosamente');
    }
}

function viewPostStats(id) {
    alert('Estadísticas del post ID: ' + id);
}

function openScheduler() {
    alert('Abriendo programador de posts multi-plataforma');
}

function openHashtagGenerator() {
    alert('Generador de hashtags:\n\n#personalizado #remeras #diseño #argentina #fashion');
}

function openSocialAnalytics() {
    alert('Analytics social consolidado por plataforma');
}

function openCommunityManager() {
    alert('Inbox unificado para gestión de comunidad');
}

function configureSocialCommerce() {
    alert('Configurar tienda social:\n\n1. Catálogo Facebook\n2. Instagram Shopping\n3. Pinterest Shopping');
}

function createSocialPromo() {
    alert('Crear promoción social:\n\n1. Concursos\n2. Giveaways\n3. User Generated Content');
}

function previousMonth() {
    alert('Mes anterior - Funcionalidad del calendario');
}

function nextMonth() {
    alert('Siguiente mes - Funcionalidad del calendario');
}

function viewDayPosts(date) {
    alert('Ver posts programados para: ' + date);
}
</script>

<style>
/* Social Media Styles */
.social-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.social-stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border-left: 4px solid #ddd;
    border: 1px solid #e9ecef;
}

.social-stat-card.connected {
    border-left-color: #28a745;
}

.social-stat-card.facebook.connected { border-left-color: #1877f2; }
.social-stat-card.instagram.connected { border-left-color: #e4405f; }
.social-stat-card.pinterest.connected { border-left-color: #bd081c; }
.social-stat-card.youtube.connected { border-left-color: #ff0000; }

.platform-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.platform-header i {
    font-size: 1.5rem;
}

.social-stat-card.facebook i { color: #1877f2; }
.social-stat-card.instagram i { color: #e4405f; }
.social-stat-card.pinterest i { color: #bd081c; }
.social-stat-card.youtube i { color: #ff0000; }

.platform-header h3 {
    flex: 1;
    margin: 0;
    color: #333;
}

.connection-status {
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #f8f9fa;
    color: #666;
}

.social-stat-card.connected .connection-status {
    background: #d4edda;
    color: #155724;
}

.platform-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.stat-label {
    font-size: 0.8rem;
    color: #666;
}

.platform-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    cursor: pointer;
    width: 100%;
    font-weight: 600;
}

.posts-table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.posts-table {
    width: 100%;
    border-collapse: collapse;
}

.posts-table th,
.posts-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.posts-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.platform-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.platform-badge.instagram {
    background: #fdf2f8;
    color: #e4405f;
}

.platform-badge.facebook {
    background: #eff6ff;
    color: #1877f2;
}

.tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.tool-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
    border: 1px solid #e9ecef;
}

.tool-card:hover {
    transform: translateY(-2px);
}

.tool-icon {
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

.tool-card h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.tool-card p {
    color: #666;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.tool-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    cursor: pointer;
    width: 100%;
    font-weight: 600;
}

.calendar-container {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.calendar-nav {
    background: none;
    border: 1px solid #ddd;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1.2rem;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: #e9ecef;
}

.calendar-day {
    background: white;
    padding: 1rem;
    text-align: center;
    min-height: 60px;
    position: relative;
    cursor: pointer;
}

.calendar-day.header {
    background: #f8f9fa;
    font-weight: 600;
    color: #666;
    cursor: default;
}

.calendar-day.has-post {
    background: #f8f9fa;
}

.post-indicator {
    position: absolute;
    bottom: 4px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin: 0 1px;
}

.post-indicator.facebook { background: #1877f2; left: 4px; }
.post-indicator.instagram { background: #e4405f; left: 16px; }

.action-btn {
    background: none;
    border: 1px solid #ddd;
    padding: 0.4rem;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 0.3rem;
    color: #666;
}

.action-btn:hover {
    background: #f8f9fa;
}
</style>

<?php include 'admin-master-footer.php'; ?>