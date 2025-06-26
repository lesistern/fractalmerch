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

$page_title = 'Panel de Administración';
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Panel Admin</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="manage-users.php">Gestionar Usuarios</a></li>
            <li><a href="manage-posts.php">Gestionar Posts</a></li>
            <li><a href="manage-comments.php">Gestionar Comentarios</a></li>
            <li><a href="manage-categories.php">Categorías</a></li>
            <li><a href="../index.php">Volver al Sitio</a></li>
        </ul>
    </div>
    
    <div class="admin-main">
        <h2>Dashboard</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total_users']; ?></h3>
                <p>Total Usuarios</p>
            </div>
            
            <div class="stat-card">
                <h3><?php echo $stats['total_posts']; ?></h3>
                <p>Total Posts</p>
            </div>
            
            <div class="stat-card">
                <h3><?php echo $stats['published_posts']; ?></h3>
                <p>Posts Publicados</p>
            </div>
            
            <div class="stat-card">
                <h3><?php echo $stats['total_comments']; ?></h3>
                <p>Total Comentarios</p>
            </div>
            
            <div class="stat-card alert">
                <h3><?php echo $stats['pending_comments']; ?></h3>
                <p>Comentarios Pendientes</p>
            </div>
        </div>
        
        <div class="admin-sections">
            <div class="admin-section">
                <h3>Usuarios por Rol</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users_by_role as $role_data): ?>
                            <tr>
                                <td><?php echo ucfirst($role_data['role']); ?></td>
                                <td><?php echo $role_data['count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="admin-section">
                <h3>Posts Recientes</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_posts as $post): ?>
                            <tr>
                                <td><a href="../post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></td>
                                <td><?php echo $post['username']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <a href="../edit-post.php?id=<?php echo $post['id']; ?>">Editar</a>
                                    <a href="../delete-post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('¿Confirmar eliminación?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($pending_comments)): ?>
            <div class="admin-section">
                <h3>Comentarios Pendientes de Aprobación</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th>Usuario</th>
                            <th>Comentario</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_comments as $comment): ?>
                            <tr>
                                <td><a href="../post.php?id=<?php echo $comment['post_id']; ?>"><?php echo $comment['title']; ?></a></td>
                                <td><?php echo $comment['username']; ?></td>
                                <td><?php echo substr($comment['content'], 0, 50) . '...'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($comment['created_at'])); ?></td>
                                <td>
                                    <a href="manage-comments.php?approve=<?php echo $comment['id']; ?>">Aprobar</a>
                                    <a href="manage-comments.php?reject=<?php echo $comment['id']; ?>">Rechazar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>