<?php
// Incluir dependencias necesarias
require_once '../config/database.php';
require_once '../includes/functions.php';

// Procesar creación de usuario
if ($_POST && isset($_POST['create_user'])) {
    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        flash_message('error', 'Token de seguridad inválido');
        redirect('manage-users.php');
    }
    
    $username = sanitize_input($_POST['username'], 'string');
    $email = sanitize_input($_POST['email'], 'email');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize_input($_POST['role'], 'string');
    
    $errors = [];
    
    // Validaciones
    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'El nombre de usuario debe tener al menos 3 caracteres';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email es inválido';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Las contraseñas no coinciden';
    }
    
    if (!in_array($role, ['user', 'moderator', 'admin'])) {
        $errors[] = 'Rol inválido';
    }
    
    // Verificar que el username y email no existan
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'El nombre de usuario o email ya existe';
        }
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        
        if ($stmt->execute([$username, $email, $hashed_password, $role])) {
            // Invalidar token después de uso exitoso
            invalidate_csrf_token();
            flash_message('success', "Usuario $username creado exitosamente como $role");
        } else {
            flash_message('error', 'Error al crear el usuario');
        }
    } else {
        foreach ($errors as $error) {
            flash_message('error', $error);
        }
    }
    
    redirect('manage-users.php');
}

// Procesar acciones (POST con CSRF)
if ($_POST && isset($_POST['action'])) {
    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        flash_message('error', 'Token de seguridad inválido');
        redirect('manage-users.php');
    }
    
    $user_id = (int)$_POST['user_id'];
    $action = sanitize_input($_POST['action'], 'string');
    
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
        }
    }
    
    redirect('manage-users.php');
}

// Obtener estadísticas de usuarios
$stats = [];
$stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$stats_by_role = $stmt->fetchAll();

$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$stats['total_users'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stats['new_users_month'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['active_users_week'] = $stmt->fetchColumn();

// Obtener lista de usuarios
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(username LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Contar total de usuarios para paginación
$count_query = "SELECT COUNT(*) FROM users $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_users = $stmt->fetchColumn();
$total_pages = ceil($total_users / $per_page);

// Obtener usuarios de la página actual
$query = "SELECT id, username, email, role, created_at, last_login 
          FROM users $where_clause 
          ORDER BY created_at DESC 
          LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = '👥 Gestión de Usuarios - Admin Panel';
include 'admin-master-header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-users-cog"></i> Gestión de Usuarios</h1>
    <p>Administra cuentas de usuario, roles y permisos del sistema</p>
    
    <div class="page-actions">
        <button class="btn btn-primary" onclick="AdminUtils.modal.show('create-user-modal')">
            <i class="fas fa-user-plus"></i> Crear Usuario
        </button>
        <button class="btn btn-secondary" onclick="exportUsers()">
            <i class="fas fa-download"></i> Exportar
        </button>
    </div>
</div>

<!-- Estadísticas de Usuarios -->
<div class="content-card">
    <h3><i class="fas fa-chart-bar"></i> Estadísticas de Usuarios</h3>
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-icon total">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $stats['total_users']; ?></h4>
                <p>Total Usuarios</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon new">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $stats['new_users_month']; ?></h4>
                <p>Nuevos (30 días)</p>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon active">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $stats['active_users_week']; ?></h4>
                <p>Activos (7 días)</p>
            </div>
        </div>
        
        <?php foreach ($stats_by_role as $role_stat): ?>
        <div class="stat-item">
            <div class="stat-icon role-<?php echo $role_stat['role']; ?>">
                <i class="fas fa-<?php echo $role_stat['role'] === 'admin' ? 'crown' : ($role_stat['role'] === 'moderator' ? 'shield-alt' : 'user'); ?>"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo $role_stat['count']; ?></h4>
                <p><?php echo ucfirst($role_stat['role']); ?>s</p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Filtros y Búsqueda -->
<div class="content-card">
    <h3><i class="fas fa-filter"></i> Filtros y Búsqueda</h3>
    <form method="GET" class="filters-form">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Buscar:</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nombre de usuario o email...">
            </div>
            
            <div class="filter-group">
                <label>Rol:</label>
                <select name="role">
                    <option value="">Todos los roles</option>
                    <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Administradores</option>
                    <option value="moderator" <?php echo $role_filter === 'moderator' ? 'selected' : ''; ?>>Moderadores</option>
                    <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>Usuarios</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="manage-users.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Lista de Usuarios -->
<div class="content-card">
    <h3><i class="fas fa-list"></i> Usuarios (<?php echo $total_users; ?> total)</h3>
    
    <?php if (empty($users)): ?>
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <p>No se encontraron usuarios</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Creado</th>
                        <th>Último acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="user-details">
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <small>ID: <?php echo $user['id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'moderator' ? 'warning' : 'info'); ?>">
                                    <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'crown' : ($user['role'] === 'moderator' ? 'shield-alt' : 'user'); ?>"></i>
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <time title="<?php echo $user['created_at']; ?>">
                                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                </time>
                            </td>
                            <td>
                                <?php if ($user['last_login']): ?>
                                    <time title="<?php echo $user['last_login']; ?>">
                                        <?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?>
                                    </time>
                                <?php else: ?>
                                    <span class="text-muted">Nunca</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <form method="POST" style="display: inline;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="promote">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success"
                                                        onclick="return confirm('¿Promover a administrador?')">
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['role'] !== 'user'): ?>
                                            <form method="POST" style="display: inline;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="demote">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-warning"
                                                        onclick="return confirm('¿Degradar a usuario regular?')">
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('¿Eliminar usuario? Esta acción no se puede deshacer.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge badge-info">Tu cuenta</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
        <div class="table-pagination">
            <div class="pagination-info">
                Mostrando <?php echo $offset + 1; ?> - <?php echo min($offset + $per_page, $total_users); ?> de <?php echo $total_users; ?> usuarios
            </div>
            <div class="pagination-controls">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>" 
                       class="btn btn-sm btn-outline">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>
                
                <span class="page-info">Página <?php echo $page; ?> de <?php echo $total_pages; ?></span>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>" 
                       class="btn btn-sm btn-outline">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal Crear Usuario -->
<div id="create-user-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h3>
            <span class="close" onclick="AdminUtils.modal.hide('create-user-modal')">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" class="user-form">
                <?php echo csrf_field(); ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nombre de usuario</label>
                        <input type="text" name="username" required minlength="3" 
                               placeholder="Ej: juan_perez">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" required 
                               placeholder="Ej: juan@ejemplo.com">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" name="password" required minlength="6" 
                               placeholder="Mínimo 6 caracteres">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Confirmar contraseña</label>
                        <input type="password" name="confirm_password" required minlength="6" 
                               placeholder="Repetir contraseña">
                    </div>
                    
                    <div class="form-group span-2">
                        <label><i class="fas fa-user-tag"></i> Rol</label>
                        <select name="role" required>
                            <option value="user">Usuario</option>
                            <option value="moderator">Moderador</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="AdminUtils.modal.hide('create-user-modal')">
                        Cancelar
                    </button>
                    <button type="submit" name="create_user" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para gestión de usuarios */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

.stat-icon.total { background: #007bff; }
.stat-icon.new { background: #28a745; }
.stat-icon.active { background: #17a2b8; }
.stat-icon.role-admin { background: #dc3545; }
.stat-icon.role-moderator { background: #ffc107; }
.stat-icon.role-user { background: #6c757d; }

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

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.user-details strong {
    display: block;
    color: #2c3e50;
}

.user-details small {
    color: #6c757d;
    font-size: 11px;
}

.action-buttons {
    display: flex;
    gap: 5px;
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

.text-muted {
    color: #6c757d;
    font-style: italic;
}

.user-form .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.user-form .form-group.span-2 {
    grid-column: span 2;
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
    
    .user-form .form-grid {
        grid-template-columns: 1fr;
    }
    
    .user-form .form-group.span-2 {
        grid-column: span 1;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
function exportUsers() {
    AdminUtils.showNotification('Exportando usuarios...', 'info');
    
    // Simular exportación
    setTimeout(() => {
        AdminUtils.showNotification('Usuarios exportados correctamente', 'success');
    }, 2000);
}

// Validación del formulario de crear usuario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.user-form');
    const passwordField = form.querySelector('input[name="password"]');
    const confirmField = form.querySelector('input[name="confirm_password"]');
    
    function validatePasswords() {
        if (passwordField.value !== confirmField.value) {
            confirmField.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmField.setCustomValidity('');
        }
    }
    
    passwordField.addEventListener('input', validatePasswords);
    confirmField.addEventListener('input', validatePasswords);
});
</script>

<?php include 'admin-master-footer.php'; ?>