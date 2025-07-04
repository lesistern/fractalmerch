<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_moderator()) {
    flash_message('error', 'No tienes permisos para gestionar comentarios');
    redirect('../index.php');
}

// Procesar acciones
if (isset($_GET['approve'])) {
    $comment_id = (int)$_GET['approve'];
    $stmt = $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
    if ($stmt->execute([$comment_id])) {
        flash_message('success', 'Comentario aprobado');
    } else {
        flash_message('error', 'Error al aprobar comentario');
    }
    redirect('manage-comments.php');
}

if (isset($_GET['reject'])) {
    $comment_id = (int)$_GET['reject'];
    $stmt = $pdo->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?");
    if ($stmt->execute([$comment_id])) {
        flash_message('success', 'Comentario rechazado');
    } else {
        flash_message('error', 'Error al rechazar comentario');
    }
    redirect('manage-comments.php');
}

if (isset($_GET['delete'])) {
    $comment_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    if ($stmt->execute([$comment_id])) {
        flash_message('success', 'Comentario eliminado');
    } else {
        flash_message('error', 'Error al eliminar comentario');
    }
    redirect('manage-comments.php');
}

// Filtros
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : 'pending';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

$sql = "SELECT c.*, p.title as post_title, u.username 
        FROM comments c 
        JOIN posts p ON c.post_id = p.id 
        JOIN users u ON c.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($status_filter) {
    $sql .= " AND c.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $sql .= " AND (c.content LIKE ? OR u.username LIKE ? OR p.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comments = $stmt->fetchAll();

// Contar comentarios por estado
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM comments GROUP BY status");
$comment_counts = [];
while ($row = $stmt->fetch()) {
    $comment_counts[$row['status']] = $row['count'];
}

$page_title = '💬 Gestionar Comentarios - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <!-- Sidebar -->
    <div class="modern-admin-sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-cube"></i> Panel Admin</h3>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="manage-users.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>
            <a href="manage-posts.php" class="nav-item">
                <i class="fas fa-file-alt"></i>
                <span>Posts</span>
            </a>
            <a href="manage-comments.php" class="nav-item active">
                <i class="fas fa-comments"></i>
                <span>Comentarios</span>
            </a>
            <a href="manage-products.php" class="nav-item">
                <i class="fas fa-box"></i>
                <span>Productos</span>
            </a>
            <a href="manage-categories.php" class="nav-item">
                <i class="fas fa-tags"></i>
                <span>Categorías</span>
            </a>
            <div class="sidebar-divider"></div>
            <a href="../index.php" class="nav-item">
                <i class="fas fa-arrow-left"></i>
                <span>Volver al Sitio</span>
            </a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="modern-admin-main">
        <!-- Header -->
        <div class="admin-header">
            <div class="header-title">
                <h1><i class="fas fa-comments"></i> Gestionar Comentarios</h1>
                <p>Modera y administra comentarios de los posts</p>
            </div>
        </div>
        
        <!-- Comment Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $comment_counts['pending'] ?? 0; ?></h3>
                    <p>Pendientes</p>
                    <span class="stat-trend warning">Requiere acción</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $comment_counts['approved'] ?? 0; ?></h3>
                    <p>Aprobados</p>
                    <span class="stat-trend positive">Activos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-times"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $comment_counts['rejected'] ?? 0; ?></h3>
                    <p>Rechazados</p>
                    <span class="stat-trend negative">Bloqueados</span>
                </div>
            </div>
        </div>
        
        <div class="admin-filters">
            <form method="GET" action="" class="filter-form">
                <input type="text" name="search" placeholder="Buscar comentarios..." 
                       value="<?php echo $search; ?>">
                
                <select name="status">
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pendientes</option>
                    <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Aprobados</option>
                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rechazados</option>
                    <option value="" <?php echo $status_filter == '' ? 'selected' : ''; ?>>Todos</option>
                </select>
                
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
        
        <div class="comments-list">
            <?php if (empty($comments)): ?>
                <p>No hay comentarios con los filtros seleccionados.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-meta">
                                <strong><?php echo $comment['username']; ?></strong>
                                <span>en</span>
                                <a href="../post.php?id=<?php echo $comment['post_id']; ?>">
                                    <?php echo $comment['post_title']; ?>
                                </a>
                                <span class="comment-date"><?php echo time_ago($comment['created_at']); ?></span>
                                <span class="status-badge status-<?php echo $comment['status']; ?>">
                                    <?php echo ucfirst($comment['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </div>
                        
                        <div class="comment-actions">
                            <?php if ($comment['status'] == 'pending'): ?>
                                <a href="?approve=<?php echo $comment['id']; ?>" class="btn btn-small btn-success">Aprobar</a>
                                <a href="?reject=<?php echo $comment['id']; ?>" class="btn btn-small btn-warning">Rechazar</a>
                            <?php elseif ($comment['status'] == 'approved'): ?>
                                <a href="?reject=<?php echo $comment['id']; ?>" class="btn btn-small btn-warning">Rechazar</a>
                            <?php elseif ($comment['status'] == 'rejected'): ?>
                                <a href="?approve=<?php echo $comment['id']; ?>" class="btn btn-small btn-success">Aprobar</a>
                            <?php endif; ?>
                            
                            <a href="?delete=<?php echo $comment['id']; ?>" 
                               class="btn btn-small btn-danger"
                               onclick="return confirm('¿Estás seguro de eliminar este comentario?')">
                                Eliminar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="admin-stats">
            <p>Mostrando <?php echo count($comments); ?> comentarios</p>
        </div>
    </div>
</div>

<footer class="admin-footer">
    <div class="container">
        <p>&copy; 2025 <?php echo SITE_NAME; ?> - Panel de Administración</p>
    </div>
</footer>

</body>
</html>