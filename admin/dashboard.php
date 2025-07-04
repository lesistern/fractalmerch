<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

// Obtener estadísticas
$stats = [];

// Total usuarios
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$stats['total_users'] = $stmt->fetchColumn();

// Total posts
$stmt = $pdo->query("SELECT COUNT(*) FROM posts");
$stats['total_posts'] = $stmt->fetchColumn();

// Posts publicados
$stmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'");
$stats['published_posts'] = $stmt->fetchColumn();

// Total comentarios
$stmt = $pdo->query("SELECT COUNT(*) FROM comments");
$stats['total_comments'] = $stmt->fetchColumn();

// Comentarios pendientes
$stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'");
$stats['pending_comments'] = $stmt->fetchColumn();

// Usuarios por rol
$stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$users_by_role = $stmt->fetchAll();

// Posts recientes
$stmt = $pdo->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5");
$recent_posts = $stmt->fetchAll();

// Comentarios recientes pendientes
$stmt = $pdo->query("SELECT c.*, p.title, u.username FROM comments c JOIN posts p ON c.post_id = p.id JOIN users u ON c.user_id = u.id WHERE c.status = 'pending' ORDER BY c.created_at DESC LIMIT 5");
$pending_comments = $stmt->fetchAll();

$page_title = '📊 Panel de Administración';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="modern-admin-main">
        <!-- Header -->
        <div class="admin-header">
            <div class="header-title">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p>Panel de control y estadísticas del sistema</p>
            </div>
        </div>
        
        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_users']; ?></h3>
                    <p>Total Usuarios</p>
                    <span class="stat-trend positive">+2 este mes</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_posts']; ?></h3>
                    <p>Total Posts</p>
                    <span class="stat-trend positive">+1 esta semana</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['published_posts']; ?></h3>
                    <p>Posts Publicados</p>
                    <span class="stat-trend neutral">Sin cambios</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_comments']; ?></h3>
                    <p>Total Comentarios</p>
                    <span class="stat-trend neutral">Estable</span>
                </div>
            </div>
            
            <?php if ($stats['pending_comments'] > 0): ?>
            <div class="stat-card alert">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['pending_comments']; ?></h3>
                    <p>Comentarios Pendientes</p>
                    <span class="stat-trend warning">Requiere atención</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Dashboard Sections -->
        <div class="dashboard-sections">
            <!-- Users Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3><i class="fas fa-users"></i> Usuarios por Rol</h3>
                    <div class="section-actions">
                        <a href="manage-users.php" class="btn-secondary">
                            <i class="fas fa-cog"></i> Gestionar
                        </a>
                    </div>
                </div>
                <div class="users-grid">
                    <?php foreach ($users_by_role as $role_data): ?>
                        <div class="user-role-card">
                            <div class="role-icon">
                                <i class="fas fa-<?php echo $role_data['role'] === 'admin' ? 'crown' : ($role_data['role'] === 'moderator' ? 'shield-alt' : 'user'); ?>"></i>
                            </div>
                            <div class="role-info">
                                <h4><?php echo ucfirst($role_data['role']); ?></h4>
                                <span class="role-count"><?php echo $role_data['count']; ?> usuarios</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Posts Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3><i class="fas fa-file-alt"></i> Posts Recientes</h3>
                    <div class="section-actions">
                        <a href="manage-posts.php" class="btn-secondary">
                            <i class="fas fa-cog"></i> Gestionar
                        </a>
                    </div>
                </div>
                <div class="posts-list">
                    <?php if (empty($recent_posts)): ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <p>No hay posts recientes</p>
                            <a href="../create-post.php" class="btn-primary">
                                <i class="fas fa-plus"></i> Crear Post
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_posts as $post): ?>
                            <div class="post-item">
                                <div class="post-content">
                                    <h4><a href="../post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h4>
                                    <div class="post-meta">
                                        <span class="author">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['username']); ?>
                                        </span>
                                        <span class="date">
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="post-actions">
                                    <a href="../edit-post.php?id=<?php echo $post['id']; ?>" class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../delete-post.php?id=<?php echo $post['id']; ?>" class="action-btn delete-btn" onclick="return confirm('¿Confirmar eliminación?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Comments Section -->
            <?php if (!empty($pending_comments)): ?>
            <div class="dashboard-section">
                <div class="section-header">
                    <h3><i class="fas fa-clock"></i> Comentarios Pendientes</h3>
                    <div class="section-actions">
                        <a href="manage-comments.php" class="btn-secondary">
                            <i class="fas fa-cog"></i> Gestionar
                        </a>
                    </div>
                </div>
                <div class="comments-list">
                    <?php foreach ($pending_comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-content">
                                <h4><a href="../post.php?id=<?php echo $comment['post_id']; ?>"><?php echo htmlspecialchars($comment['title']); ?></a></h4>
                                <p class="comment-text"><?php echo htmlspecialchars(substr($comment['content'], 0, 80) . '...'); ?></p>
                                <div class="comment-meta">
                                    <span class="author">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($comment['username']); ?>
                                    </span>
                                    <span class="date">
                                        <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($comment['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="comment-actions">
                                <a href="manage-comments.php?approve=<?php echo $comment['id']; ?>" class="action-btn approve-btn">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="manage-comments.php?reject=<?php echo $comment['id']; ?>" class="action-btn reject-btn">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="admin-footer">
    <div class="container">
        <p>&copy; 2025 <?php echo SITE_NAME; ?> - Panel de Administración</p>
    </div>
</footer>

<style>
.admin-footer {
    background: rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    color: rgba(255,255,255,0.8);
    text-align: center;
    padding: 1rem 0;
    margin-top: 2rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.admin-footer p {
    margin: 0;
    font-size: 0.9rem;
}
</style>

<!-- Scripts -->
<script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>

</body>
</html>