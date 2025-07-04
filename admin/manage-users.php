<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

// Procesar creación de usuario
if ($_POST && isset($_POST['create_user'])) {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize_input($_POST['role']);
    
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

// Obtener usuarios con conteo de posts
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? sanitize_input($_GET['role']) : '';

$sql = "SELECT u.*, 
               (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count
        FROM users u WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (u.username LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter) {
    $sql .= " AND u.role = ?";
    $params[] = $role_filter;
}

$sql .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Estadísticas de usuarios
$stats = [
    'total' => count($users),
    'admins' => count(array_filter($users, fn($u) => $u['role'] === 'admin')),
    'moderators' => count(array_filter($users, fn($u) => $u['role'] === 'moderator')),
    'users' => count(array_filter($users, fn($u) => $u['role'] === 'user'))
];

$page_title = '👥 Gestionar Usuarios - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-users"></i> Usuarios</h1>
                <p class="header-subtitle">Administra usuarios, roles y permisos del sistema</p>
            </div>
            <div class="header-actions">
                <button onclick="toggleUserForm()" class="tn-btn tn-btn-primary">
                    <i class="fas fa-user-plus"></i> Nuevo usuario
                </button>
                <button onclick="exportUsers()" class="tn-btn tn-btn-secondary">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>
        </div>

        <!-- Estadísticas de usuarios -->
        <section class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $stats['total']; ?></span>
                    <span class="stat-label">Total usuarios</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon admin">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $stats['admins']; ?></span>
                    <span class="stat-label">Administradores</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon moderator">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $stats['moderators']; ?></span>
                    <span class="stat-label">Moderadores</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon user">
                    <i class="fas fa-user"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $stats['users']; ?></span>
                    <span class="stat-label">Usuarios</span>
                </div>
            </div>
        </section>

        <!-- Formulario de usuario (inicialmente oculto) -->
        <section class="tn-card user-form-section" id="userForm" style="display: none;">
            <div class="tn-card-header">
                <h2>Nuevo usuario</h2>
                <button onclick="closeUserForm()" class="tn-btn-ghost">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="" class="tn-form">
                <div class="tn-form-grid">
                    <div class="tn-form-group">
                        <label for="username" class="tn-label">Nombre de usuario *</label>
                        <input type="text" id="username" name="username" class="tn-input" required minlength="3"
                               placeholder="Mínimo 3 caracteres">
                    </div>
                    
                    <div class="tn-form-group">
                        <label for="email" class="tn-label">Email *</label>
                        <input type="email" id="email" name="email" class="tn-input" required
                               placeholder="usuario@ejemplo.com">
                    </div>
                    
                    <div class="tn-form-group">
                        <label for="password" class="tn-label">Contraseña *</label>
                        <input type="password" id="password" name="password" class="tn-input" required minlength="6"
                               placeholder="Mínimo 6 caracteres">
                    </div>
                    
                    <div class="tn-form-group">
                        <label for="confirm_password" class="tn-label">Confirmar contraseña *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="tn-input" required
                               placeholder="Repetir contraseña">
                    </div>
                    
                    <div class="tn-form-group full-width">
                        <label for="role" class="tn-label">Rol del usuario *</label>
                        <select id="role" name="role" class="tn-select" required>
                            <option value="">Seleccionar rol...</option>
                            <option value="user">👤 Usuario Regular</option>
                            <option value="moderator">🛡️ Moderador</option>
                            <option value="admin">⚡ Administrador</option>
                        </select>
                    </div>
                </div>
                
                <div class="tn-form-actions">
                    <button type="submit" name="create_user" class="tn-btn tn-btn-primary">
                        <i class="fas fa-user-plus"></i> Crear usuario
                    </button>
                    <button type="button" onclick="closeUserForm()" class="tn-btn tn-btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </section>

        <!-- Lista de usuarios -->
        <section class="tn-card">
            <div class="tn-card-header">
                <div class="header-left">
                    <h2>Lista de usuarios</h2>
                    <span class="tn-badge tn-badge-neutral"><?php echo count($users); ?> usuarios</span>
                </div>
                <div class="tn-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar usuarios..." id="userSearch" value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>

            <!-- Filtros -->
            <div class="tn-filters">
                <form method="GET" action="" class="filter-form">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    
                    <div class="filter-group">
                        <label for="role_filter">Filtrar por rol:</label>
                        <select name="role" id="role_filter" onchange="this.form.submit()">
                            <option value="">Todos los roles</option>
                            <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Administradores</option>
                            <option value="moderator" <?php echo $role_filter == 'moderator' ? 'selected' : ''; ?>>Moderadores</option>
                            <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>Usuarios</option>
                        </select>
                    </div>
                    
                    <?php if ($role_filter || $search): ?>
                        <a href="manage-users.php" class="tn-btn tn-btn-ghost">
                            <i class="fas fa-times"></i> Limpiar filtros
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($users)): ?>
                <div class="tn-empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>No hay usuarios</h3>
                    <p>No se encontraron usuarios con los filtros aplicados</p>
                    <a href="manage-users.php" class="tn-btn tn-btn-primary">
                        <i class="fas fa-refresh"></i> Ver todos
                    </a>
                </div>
            <?php else: ?>
                <div class="tn-table-container">
                    <table class="tn-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Posts</th>
                                <th>Registrado</th>
                                <th class="tn-table-actions">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php foreach ($users as $user): ?>
                                <tr class="tn-table-row">
                                    <td>
                                        <div class="tn-table-cell-content">
                                            <div class="user-info">
                                                <strong class="user-name"><?php echo htmlspecialchars($user['username']); ?></strong>
                                                <span class="user-id">#<?php echo $user['id']; ?></span>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                    <span class="tn-badge tn-badge-primary">Tú</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </td>
                                    <td>
                                        <span class="tn-badge tn-badge-<?php echo $user['role']; ?>">
                                            <?php 
                                            $role_icons = ['admin' => '⚡', 'moderator' => '🛡️', 'user' => '👤'];
                                            echo $role_icons[$user['role']] . ' ' . ucfirst($user['role']); 
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="tn-metric">
                                            <span class="metric-value"><?php echo $user['post_count']; ?></span>
                                            <span class="metric-label">posts</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="tn-date">
                                            <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <div class="tn-action-group">
                                                <div class="dropdown">
                                                    <button class="tn-btn-action" onclick="toggleUserDropdown(<?php echo $user['id']; ?>)">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu" id="dropdown-<?php echo $user['id']; ?>">
                                                        <?php if ($user['role'] == 'user'): ?>
                                                            <a href="?action=make_moderator&user_id=<?php echo $user['id']; ?>" class="dropdown-item">
                                                                <i class="fas fa-arrow-up"></i> Hacer Moderador
                                                            </a>
                                                            <a href="?action=promote&user_id=<?php echo $user['id']; ?>" class="dropdown-item">
                                                                <i class="fas fa-crown"></i> Hacer Admin
                                                            </a>
                                                        <?php elseif ($user['role'] == 'moderator'): ?>
                                                            <a href="?action=promote&user_id=<?php echo $user['id']; ?>" class="dropdown-item">
                                                                <i class="fas fa-crown"></i> Hacer Admin
                                                            </a>
                                                            <a href="?action=demote&user_id=<?php echo $user['id']; ?>" class="dropdown-item">
                                                                <i class="fas fa-arrow-down"></i> Degradar a Usuario
                                                            </a>
                                                        <?php elseif ($user['role'] == 'admin'): ?>
                                                            <a href="?action=demote&user_id=<?php echo $user['id']; ?>" class="dropdown-item">
                                                                <i class="fas fa-arrow-down"></i> Degradar a Usuario
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <div class="dropdown-divider"></div>
                                                        <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')"
                                                                class="dropdown-item danger">
                                                            <i class="fas fa-trash"></i> Eliminar Usuario
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="tn-badge tn-badge-info">Tu cuenta</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<script>
// Funciones de la página
function toggleUserForm() {
    const form = document.getElementById('userForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
        document.getElementById('username').focus();
    } else {
        form.style.display = 'none';
    }
}

function closeUserForm() {
    document.getElementById('userForm').style.display = 'none';
    // Clear form
    document.querySelector('.tn-form').reset();
    // Reset password validation styles
    document.getElementById('password').style.borderColor = '';
    document.getElementById('confirm_password').style.borderColor = '';
}

function deleteUser(id, username) {
    if (confirm(`¿Estás seguro de que quieres eliminar al usuario "${username}"?\n\nEsta acción eliminará también todos sus posts y comentarios.\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `?action=delete&user_id=${id}`;
    }
}

function exportUsers() {
    console.log('Export users functionality');
    toast.info('Función pendiente', 'La exportación estará disponible pronto');
}

function toggleUserDropdown(userId) {
    const dropdown = document.getElementById(`dropdown-${userId}`);
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu !== dropdown) {
            menu.classList.remove('show');
        }
    });
    dropdown.classList.toggle('show');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Búsqueda de usuarios
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#usersTableBody .tn-table-row');
                
                rows.forEach(row => {
                    const userName = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
                    const userEmail = row.querySelector('.user-email')?.textContent.toLowerCase() || '';
                    
                    const matches = userName.includes(searchTerm) || userEmail.includes(searchTerm);
                    row.style.display = matches ? '' : 'none';
                });
            }, 300);
        });
    }
    
    // Validación de contraseñas en tiempo real
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePasswords() {
        if (password.value && confirmPassword.value) {
            if (password.value === confirmPassword.value) {
                confirmPassword.style.borderColor = 'var(--tn-success)';
                confirmPassword.style.boxShadow = '0 0 0 3px rgba(34, 197, 94, 0.1)';
            } else {
                confirmPassword.style.borderColor = 'var(--tn-danger)';
                confirmPassword.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
            }
        } else {
            confirmPassword.style.borderColor = '';
            confirmPassword.style.boxShadow = '';
        }
    }
    
    if (password && confirmPassword) {
        password.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);
    }
});
</script>

<style>
/* Estilos específicos para gestión de usuarios */
.user-form-section {
    margin-bottom: 2rem;
}

.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid var(--tn-border);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: var(--tn-primary);
    color: white;
}

.stat-icon.admin {
    background: var(--tn-danger);
}

.stat-icon.moderator {
    background: var(--tn-warning);
}

.stat-icon.user {
    background: var(--tn-info);
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--tn-text-primary);
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--tn-text-muted);
    font-weight: 500;
}

.tn-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.tn-form-group.full-width {
    grid-column: 1 / -1;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.user-name {
    font-weight: 600;
    color: var(--tn-text-primary);
}

.user-id {
    font-size: 0.75rem;
    color: var(--tn-text-muted);
    font-weight: 400;
}

.user-email {
    color: var(--tn-text-secondary);
    font-size: 0.9rem;
}

.tn-badge-admin {
    background: var(--tn-danger);
    color: white;
}

.tn-badge-moderator {
    background: var(--tn-warning);
    color: white;
}

.tn-badge-user {
    background: var(--tn-info);
    color: white;
}

.tn-filters {
    padding: 1rem 0;
    border-bottom: 1px solid var(--tn-border);
    margin-bottom: 1rem;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-group label {
    font-size: 0.9rem;
    color: var(--tn-text-secondary);
    white-space: nowrap;
}

.filter-group select {
    padding: 0.5rem;
    border: 1px solid var(--tn-border);
    border-radius: 6px;
    background: white;
    font-size: 0.9rem;
}

.dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid var(--tn-border);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 180px;
    z-index: 1000;
    display: none;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: var(--tn-text-primary);
    font-size: 0.9rem;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    transition: background-color 0.2s;
}

.dropdown-item:hover {
    background: var(--tn-bg-secondary);
}

.dropdown-item.danger {
    color: var(--tn-danger);
}

.dropdown-item.danger:hover {
    background: rgba(239, 68, 68, 0.1);
}

.dropdown-divider {
    height: 1px;
    background: var(--tn-border);
    margin: 0.25rem 0;
}

/* Responsive */
@media (max-width: 768px) {
    .tn-form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stats-overview {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .tiendanube-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .header-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .filter-form {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
}

/* Optimización compacta */
.modern-admin-main { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.tn-card { padding: 1.5rem !important; }
.tn-form-actions { margin-top: 1.5rem !important; }
.stats-overview { margin-bottom: 1.5rem !important; }
</style>

</body>
</html>