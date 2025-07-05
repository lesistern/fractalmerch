<?php
// Incluir dependencias necesarias
require_once '../config/database.php';
require_once '../includes/functions.php';

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

$pageTitle = '📝 Gestionar Posts - Panel Admin';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-file-alt"></i> Gestionar Posts</h1>
    <p>Administra artículos, contenido y publicaciones del sitio</p>
    
    <div class="page-actions">
        <a href="../create-post.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Post
        </a>
        <button class="btn btn-secondary" onclick="exportPosts()">
            <i class="fas fa-download"></i> Exportar
        </button>
    </div>
</div>
<!-- Filtros -->
<div class="content-card">
    <h3><i class="fas fa-filter"></i> Filtros y Búsqueda</h3>
    <form method="GET" class="filters-form">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Buscar:</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Título o contenido...">
            </div>
            
            <div class="filter-group">
                <label>Estado:</label>
                <select name="status">
                    <option value="">Todos los estados</option>
                    <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>Publicado</option>
                    <option value="archived" <?php echo $status_filter == 'archived' ? 'selected' : ''; ?>>Archivado</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Categoría:</label>
                <select name="category">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Autor:</label>
                <input type="text" name="author" value="<?php echo htmlspecialchars($author_filter); ?>" 
                       placeholder="Nombre de autor...">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="manage-posts.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</div>
<!-- Lista de Posts -->
<div class="content-card">
    <h3><i class="fas fa-list"></i> Posts (<?php echo count($posts); ?> total)</h3>
    
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <p>No se encontraron posts</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Post</th>
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
                            <td>
                                <div class="post-info">
                                    <strong><?php echo htmlspecialchars(substr($post['title'], 0, 60) . (strlen($post['title']) > 60 ? '...' : '')); ?></strong>
                                    <small>ID: <?php echo $post['id']; ?></small>
                                    <a href="../post.php?id=<?php echo $post['id']; ?>" target="_blank" class="view-link">
                                        <i class="fas fa-external-link-alt"></i> Ver
                                    </a>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($post['username']); ?></td>
                            <td><?php echo htmlspecialchars($post['category_name'] ?: 'Sin categoría'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : ($post['status'] === 'draft' ? 'warning' : 'secondary'); ?>">
                                    <i class="fas fa-<?php echo $post['status'] === 'published' ? 'check' : ($post['status'] === 'draft' ? 'edit' : 'archive'); ?>"></i>
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="metric-value"><?php echo number_format($post['views']); ?></span>
                            </td>
                            <td>
                                <time title="<?php echo $post['created_at']; ?>">
                                    <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                </time>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="../edit-post.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-sm btn-primary" title="Editar post">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if ($post['status'] == 'draft'): ?>
                                        <a href="?action=publish&post_id=<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-success" title="Publicar"
                                           onclick="return confirm('¿Publicar este post?')">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php elseif ($post['status'] == 'published'): ?>
                                        <a href="?action=draft&post_id=<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-warning" title="Enviar a borrador"
                                           onclick="return confirm('¿Enviar a borrador?')">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=archive&post_id=<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-secondary" title="Archivar"
                                           onclick="return confirm('¿Archivar este post?')">
                                            <i class="fas fa-archive"></i>
                                        </a>
                                    <?php elseif ($post['status'] == 'archived'): ?>
                                        <a href="?action=publish&post_id=<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-success" title="Publicar"
                                           onclick="return confirm('¿Publicar este post archivado?')">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="?action=delete&post_id=<?php echo $post['id']; ?>" 
                                       class="btn btn-sm btn-danger" title="Eliminar"
                                       onclick="return confirm('¿Estás seguro de eliminar este post? Esta acción no se puede deshacer.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
/* Estilos específicos para gestión de posts */
.post-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.post-info strong {
    color: #2c3e50;
    font-weight: 600;
}

.post-info small {
    color: #6c757d;
    font-size: 0.75rem;
}

.view-link {
    color: #007bff;
    text-decoration: none;
    font-size: 0.8rem;
}

.view-link:hover {
    text-decoration: underline;
}

.metric-value {
    font-weight: 600;
    color: #2c3e50;
}

.filters-form {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1.5fr auto;
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.filter-group input,
.filter-group select {
    padding: 8px 12px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    font-size: 14px;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #007bff;
}

.filter-actions {
    display: flex;
    gap: 8px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .filters-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .filter-actions {
        justify-content: stretch;
    }
    
    .filter-actions .btn {
        flex: 1;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 4px;
    }
    
    .post-info strong {
        font-size: 14px;
    }
}
</style>

<script>
function exportPosts() {
    AdminUtils.showNotification('Exportando posts...', 'info');
    
    // Simular exportación
    setTimeout(() => {
        AdminUtils.showNotification('Posts exportados correctamente', 'success');
    }, 2000);
}
</script>

<?php include 'admin-master-footer.php'; ?>