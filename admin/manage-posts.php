<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_moderator()) {
    flash_message('error', 'No tienes permisos para gestionar posts');
    redirect('../index.php');
}

// Procesar acciones
if (isset($_GET['action'])) {
    $post_id = (int)$_GET['post_id'];
    $action = $_GET['action'];
    
    switch ($action) {
        case 'publish':
            $stmt = $pdo->prepare("UPDATE posts SET status = 'published' WHERE id = ?");
            if ($stmt->execute([$post_id])) {
                flash_message('success', 'Post publicado');
            } else {
                flash_message('error', 'Error al publicar post');
            }
            break;
            
        case 'draft':
            $stmt = $pdo->prepare("UPDATE posts SET status = 'draft' WHERE id = ?");
            if ($stmt->execute([$post_id])) {
                flash_message('success', 'Post marcado como borrador');
            } else {
                flash_message('error', 'Error al cambiar estado del post');
            }
            break;
            
        case 'archive':
            $stmt = $pdo->prepare("UPDATE posts SET status = 'archived' WHERE id = ?");
            if ($stmt->execute([$post_id])) {
                flash_message('success', 'Post archivado');
            } else {
                flash_message('error', 'Error al archivar post');
            }
            break;
            
        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            if ($stmt->execute([$post_id])) {
                flash_message('success', 'Post eliminado');
            } else {
                flash_message('error', 'Error al eliminar post');
            }
            break;
    }
    
    redirect('manage-posts.php');
}

// Filtros
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$author_filter = isset($_GET['author']) ? sanitize_input($_GET['author']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

$sql = "SELECT p.*, u.username, c.name as category_name 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";
$params = [];

if ($status_filter) {
    $sql .= " AND p.status = ?";
    $params[] = $status_filter;
}

if ($category_filter) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

if ($author_filter) {
    $sql .= " AND u.username LIKE ?";
    $params[] = "%$author_filter%";
}

if ($search) {
    $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$categories = get_categories();

$page_title = '📝 Gestionar Posts - Panel Admin';
include 'admin-dashboard-header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Panel Admin</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage-users.php">Gestionar Usuarios</a></li>
            <li><a href="manage-posts.php" class="active">Gestionar Posts</a></li>
            <li><a href="manage-comments.php">Gestionar Comentarios</a></li>
            <li><a href="manage-categories.php">Categorías</a></li>
            <li><a href="generate-images.php">🎨 Generar Imágenes</a></li>
            <li><a href="../index.php">Volver al Sitio</a></li>
        </ul>
    </div>
    
    <div class="admin-main">
        <h2>Gestionar Posts</h2>
        
        <div class="admin-filters">
            <form method="GET" action="" class="filter-form">
                <input type="text" name="search" placeholder="Buscar posts..." 
                       value="<?php echo $search; ?>">
                
                <select name="status">
                    <option value="">Todos los estados</option>
                    <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>Publicado</option>
                    <option value="archived" <?php echo $status_filter == 'archived' ? 'selected' : ''; ?>>Archivado</option>
                </select>
                
                <select name="category">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo $category['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="author" placeholder="Filtrar por autor..." 
                       value="<?php echo $author_filter; ?>">
                
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
        
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Vistas</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?php echo $post['id']; ?></td>
                            <td>
                                <a href="../post.php?id=<?php echo $post['id']; ?>" target="_blank">
                                    <?php echo substr($post['title'], 0, 50) . (strlen($post['title']) > 50 ? '...' : ''); ?>
                                </a>
                            </td>
                            <td><?php echo $post['username']; ?></td>
                            <td><?php echo $post['category_name'] ?: 'Sin categoría'; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $post['status']; ?>">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $post['views']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="../edit-post.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-small">Editar</a>
                                    
                                    <?php if ($post['status'] == 'draft'): ?>
                                        <a href="?action=publish&post_id=<?php echo $post['id']; ?>" 
                                           class="btn btn-small btn-success">Publicar</a>
                                    <?php elseif ($post['status'] == 'published'): ?>
                                        <a href="?action=draft&post_id=<?php echo $post['id']; ?>" 
                                           class="btn btn-small btn-warning">Borrador</a>
                                        <a href="?action=archive&post_id=<?php echo $post['id']; ?>" 
                                           class="btn btn-small">Archivar</a>
                                    <?php elseif ($post['status'] == 'archived'): ?>
                                        <a href="?action=publish&post_id=<?php echo $post['id']; ?>" 
                                           class="btn btn-small btn-success">Publicar</a>
                                    <?php endif; ?>
                                    
                                    <a href="?action=delete&post_id=<?php echo $post['id']; ?>" 
                                       class="btn btn-small btn-danger"
                                       onclick="return confirm('¿Estás seguro de eliminar este post?')">
                                        Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="admin-stats">
            <p>Mostrando <?php echo count($posts); ?> posts</p>
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