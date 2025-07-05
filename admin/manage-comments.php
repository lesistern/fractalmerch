<?php
// Incluir dependencias necesarias
require_once '../config/database.php';
require_once '../includes/functions.php';

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

$pageTitle = '💬 Gestionar Comentarios - Panel Admin';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-comments"></i> Gestionar Comentarios</h1>
    <p>Modera y administra comentarios de los posts</p>
    
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="moderateAll()">
            <i class="fas fa-check-double"></i> Moderar Todo
        </button>
        <button class="btn btn-secondary" onclick="exportComments()">
            <i class="fas fa-download"></i> Exportar
        </button>
    </div>
</div>

<!-- Estadísticas de Comentarios -->
<div class="content-card">
    <h3><i class="fas fa-chart-bar"></i> Estadísticas de Comentarios</h3>
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $comment_counts['pending'] ?? 0; ?></h4>
                <p>Pendientes</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon approved">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $comment_counts['approved'] ?? 0; ?></h4>
                <p>Aprobados</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon rejected">
                <i class="fas fa-times"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $comment_counts['rejected'] ?? 0; ?></h4>
                <p>Rechazados</p>
            </div>
        </div>
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
                       placeholder="Contenido, autor o post...">
            </div>
            
            <div class="filter-group">
                <label>Estado:</label>
                <select name="status">
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pendientes</option>
                    <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Aprobados</option>
                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rechazados</option>
                    <option value="" <?php echo $status_filter == '' ? 'selected' : ''; ?>>Todos</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="manage-comments.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</div>
<!-- Lista de Comentarios -->
<div class="content-card">
    <h3><i class="fas fa-list"></i> Comentarios (<?php echo count($comments); ?> total)</h3>
    
    <?php if (empty($comments)): ?>
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <p>No se encontraron comentarios</p>
        </div>
    <?php else: ?>
        <div class="comments-list">
            <?php foreach ($comments as $comment): ?>
                <div class="comment-card">
                    <div class="comment-header">
                        <div class="comment-meta">
                            <div class="user-info">
                                <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                <span class="comment-date"><?php echo time_ago($comment['created_at']); ?></span>
                            </div>
                            <div class="post-link">
                                <span>en</span>
                                <a href="../post.php?id=<?php echo $comment['post_id']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($comment['post_title']); ?>
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        <div class="comment-status">
                            <span class="badge badge-<?php echo $comment['status'] === 'approved' ? 'success' : ($comment['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                <i class="fas fa-<?php echo $comment['status'] === 'approved' ? 'check' : ($comment['status'] === 'pending' ? 'clock' : 'times'); ?>"></i>
                                <?php echo ucfirst($comment['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="comment-content">
                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                    </div>
                    
                    <div class="comment-actions">
                        <?php if ($comment['status'] == 'pending'): ?>
                            <a href="?approve=<?php echo $comment['id']; ?>" 
                               class="btn btn-sm btn-success" title="Aprobar comentario">
                                <i class="fas fa-check"></i> Aprobar
                            </a>
                            <a href="?reject=<?php echo $comment['id']; ?>" 
                               class="btn btn-sm btn-warning" title="Rechazar comentario">
                                <i class="fas fa-times"></i> Rechazar
                            </a>
                        <?php elseif ($comment['status'] == 'approved'): ?>
                            <a href="?reject=<?php echo $comment['id']; ?>" 
                               class="btn btn-sm btn-warning" title="Rechazar comentario"
                               onclick="return confirm('¿Rechazar este comentario aprobado?')">
                                <i class="fas fa-times"></i> Rechazar
                            </a>
                        <?php elseif ($comment['status'] == 'rejected'): ?>
                            <a href="?approve=<?php echo $comment['id']; ?>" 
                               class="btn btn-sm btn-success" title="Aprobar comentario"
                               onclick="return confirm('¿Aprobar este comentario rechazado?')">
                                <i class="fas fa-check"></i> Aprobar
                            </a>
                        <?php endif; ?>
                        
                        <a href="?delete=<?php echo $comment['id']; ?>" 
                           class="btn btn-sm btn-danger" title="Eliminar comentario"
                           onclick="return confirm('¿Estás seguro de eliminar este comentario? Esta acción no se puede deshacer.')">
                            <i class="fas fa-trash"></i> Eliminar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Estilos específicos para gestión de comentarios */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
    flex-shrink: 0;
}

.stat-icon.pending { background: #ffc107; }
.stat-icon.approved { background: #28a745; }
.stat-icon.rejected { background: #dc3545; }

.stat-content h4 {
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    color: #2c3e50;
}

.stat-content p {
    margin: 0;
    color: #6c757d;
    font-size: 12px;
}

.comments-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.comment-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    background: #fff;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.comment-meta {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.user-info strong {
    color: #2c3e50;
    font-weight: 600;
}

.comment-date {
    color: #6c757d;
    font-size: 0.9rem;
}

.post-link {
    color: #6c757d;
    font-size: 0.9rem;
}

.post-link a {
    color: #007bff;
    text-decoration: none;
    margin-left: 5px;
}

.post-link a:hover {
    text-decoration: underline;
}

.post-link i {
    font-size: 0.8rem;
    margin-left: 5px;
}

.comment-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 3px solid #007bff;
    margin-bottom: 15px;
    line-height: 1.6;
    color: #495057;
}

.comment-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.filters-form {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr auto;
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
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
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
    
    .comment-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .comment-actions {
        justify-content: center;
    }
}
</style>

<script>
function moderateAll() {
    AdminUtils.showNotification('Moderación masiva no implementada aún', 'info');
}

function exportComments() {
    AdminUtils.showNotification('Exportando comentarios...', 'info');
    
    // Simular exportación
    setTimeout(() => {
        AdminUtils.showNotification('Comentarios exportados correctamente', 'success');
    }, 2000);
}
</script>

<?php include 'admin-master-footer.php'; ?>