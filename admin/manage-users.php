<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

// Procesar acciones
if (isset($_GET['action'])) {
    $user_id = (int)$_GET['user_id'];
    $action = $_GET['action'];
    
    if ($user_id == $_SESSION['user_id']) {
        flash_message('error', 'No puedes realizar acciones sobre tu propia cuenta');
    } else {
        switch ($action) {
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    flash_message('success', 'Usuario eliminado exitosamente');
                } else {
                    flash_message('error', 'Error al eliminar usuario');
                }
                break;
                
            case 'promote':
                $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    flash_message('success', 'Usuario promovido a administrador');
                } else {
                    flash_message('error', 'Error al promover usuario');
                }
                break;
                
            case 'demote':
                $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    flash_message('success', 'Usuario degradado a usuario regular');
                } else {
                    flash_message('error', 'Error al degradar usuario');
                }
                break;
                
            case 'make_moderator':
                $stmt = $pdo->prepare("UPDATE users SET role = 'moderator' WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    flash_message('success', 'Usuario promovido a moderador');
                } else {
                    flash_message('error', 'Error al promover usuario');
                }
                break;
        }
    }
    
    redirect('manage-users.php');
}

// Obtener usuarios
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? sanitize_input($_GET['role']) : '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$page_title = 'Gestionar Usuarios';
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Panel Admin</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage-users.php" class="active">Gestionar Usuarios</a></li>
            <li><a href="manage-posts.php">Gestionar Posts</a></li>
            <li><a href="manage-comments.php">Gestionar Comentarios</a></li>
            <li><a href="manage-categories.php">Categorías</a></li>
            <li><a href="../index.php">Volver al Sitio</a></li>
        </ul>
    </div>
    
    <div class="admin-main">
        <h2>Gestionar Usuarios</h2>
        
        <div class="admin-filters">
            <form method="GET" action="" class="filter-form">
                <input type="text" name="search" placeholder="Buscar usuarios..." 
                       value="<?php echo $search; ?>">
                
                <select name="role">
                    <option value="">Todos los roles</option>
                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                    <option value="moderator" <?php echo $role_filter == 'moderator' ? 'selected' : ''; ?>>Moderador</option>
                    <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>Usuario</option>
                </select>
                
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
        
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Fecha Registro</th>
                        <th>Posts</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php
                        // Contar posts del usuario
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
                        $stmt->execute([$user['id']]);
                        $post_count = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td><?php echo $post_count; ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <div class="action-buttons">
                                        <?php if ($user['role'] == 'user'): ?>
                                            <a href="?action=make_moderator&user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-small">Hacer Moderador</a>
                                            <a href="?action=promote&user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-small">Hacer Admin</a>
                                        <?php elseif ($user['role'] == 'moderator'): ?>
                                            <a href="?action=promote&user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-small">Hacer Admin</a>
                                            <a href="?action=demote&user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-small">Degradar</a>
                                        <?php elseif ($user['role'] == 'admin'): ?>
                                            <a href="?action=demote&user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-small">Degradar</a>
                                        <?php endif; ?>
                                        
                                        <a href="?action=delete&user_id=<?php echo $user['id']; ?>" 
                                           class="btn btn-small btn-danger"
                                           onclick="return confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')">
                                            Eliminar
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="current-user">Tu cuenta</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="admin-stats">
            <p>Total de usuarios: <?php echo count($users); ?></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>