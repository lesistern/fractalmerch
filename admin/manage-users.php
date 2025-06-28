<?php
require_once '../includes/functions.php';

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
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    
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
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, first_name, last_name, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        if ($stmt->execute([$username, $email, $hashed_password, $role, $first_name, $last_name])) {
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

$page_title = '👥 Gestionar Usuarios - Panel Admin';
include 'admin-dashboard-header.php';
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
            <li><a href="generate-images.php">🎨 Generar Imágenes</a></li>
            <li><a href="../index.php">Volver al Sitio</a></li>
        </ul>
    </div>
    
    <div class="admin-main">
        <h2>Gestionar Usuarios</h2>
        
        <!-- Botón para mostrar/ocultar formulario de crear usuario -->
        <div class="admin-actions" style="margin-bottom: 2rem;">
            <button onclick="toggleCreateUserForm()" class="btn btn-success" id="toggleCreateBtn">
                <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
            </button>
        </div>
        
        <!-- Formulario de crear usuario (oculto por defecto) -->
        <div id="createUserForm" class="create-user-form" style="display: none;">
            <div class="form-card">
                <h3><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h3>
                
                <form method="POST" action="" class="user-creation-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Nombre:</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Apellido:</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Nombre de Usuario:</label>
                            <input type="text" id="username" name="username" required minlength="3">
                            <small>Mínimo 3 caracteres</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Contraseña:</label>
                            <input type="password" id="password" name="password" required minlength="6">
                            <small>Mínimo 6 caracteres</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirmar Contraseña:</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Rol del Usuario:</label>
                        <select id="role" name="role" required>
                            <option value="user">👤 Usuario Regular</option>
                            <option value="moderator">🛡️ Moderador</option>
                            <option value="admin">⚡ Administrador</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="create_user" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Crear Usuario
                        </button>
                        <button type="button" onclick="toggleCreateUserForm()" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
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

<footer class="admin-footer">
    <div class="container">
        <p>&copy; 2025 <?php echo SITE_NAME; ?> - Panel de Administración</p>
    </div>
</footer>

<style>
/* Estilos específicos para el formulario de crear usuario */
.create-user-form {
    margin-bottom: 2rem;
}

.form-card {
    background: rgba(255,255,255,0.95);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    margin-bottom: 2rem;
}

.form-card h3 {
    color: #1f2937;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.user-creation-form {
    display: grid;
    gap: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #374151;
    font-size: 0.95rem;
}

.form-group input,
.form-group select {
    padding: 0.875rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-group small {
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
    margin-top: 1rem;
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    padding: 0.875rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
    border: none;
    padding: 0.875rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}

/* Animación de mostrar/ocultar formulario */
.create-user-form {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
function toggleCreateUserForm() {
    const form = document.getElementById('createUserForm');
    const btn = document.getElementById('toggleCreateBtn');
    
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-times"></i> Cancelar';
        btn.className = 'btn btn-secondary';
        
        // Focus en el primer campo
        setTimeout(() => {
            document.getElementById('first_name').focus();
        }, 100);
    } else {
        form.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-user-plus"></i> Crear Nuevo Usuario';
        btn.className = 'btn btn-success';
        
        // Limpiar formulario
        document.querySelector('.user-creation-form').reset();
    }
}

// Validación en tiempo real de contraseñas
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePasswords() {
        if (password.value && confirmPassword.value) {
            if (password.value === confirmPassword.value) {
                confirmPassword.style.borderColor = '#10b981';
                confirmPassword.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
            } else {
                confirmPassword.style.borderColor = '#ef4444';
                confirmPassword.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
            }
        }
    }
    
    password.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
    
    // Validación de username en tiempo real
    const username = document.getElementById('username');
    username.addEventListener('input', function() {
        if (this.value.length >= 3) {
            this.style.borderColor = '#10b981';
        } else if (this.value.length > 0) {
            this.style.borderColor = '#ef4444';
        }
    });
});
</script>

</body>
</html>