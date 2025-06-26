<?php
require_once 'includes/functions.php';

if (!is_logged_in()) {
    flash_message('error', 'Debes iniciar sesión');
    redirect('login.php');
}

$user = get_user_by_id($_SESSION['user_id']);

if ($_POST) {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $bio = sanitize_input($_POST['bio']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    if (empty($username) || empty($email)) {
        $errors[] = 'Nombre de usuario y email son requeridos';
    }
    
    // Verificar si el username/email ya están en uso por otro usuario
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = 'El nombre de usuario o email ya están en uso';
    }
    
    // Si se quiere cambiar contraseña
    if (!empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = 'La contraseña actual es incorrecta';
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'Las contraseñas nuevas no coinciden';
        }
    }
    
    if (empty($errors)) {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, bio = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([$username, $email, $bio, $hashed_password, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, bio = ? WHERE id = ?");
            $result = $stmt->execute([$username, $email, $bio, $_SESSION['user_id']]);
        }
        
        if ($result) {
            $_SESSION['username'] = $username;
            flash_message('success', 'Perfil actualizado exitosamente');
            redirect('profile.php');
        } else {
            flash_message('error', 'Error al actualizar el perfil');
        }
    } else {
        foreach ($errors as $error) {
            flash_message('error', $error);
        }
    }
}

// Obtener posts del usuario
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_posts = $stmt->fetchAll();

$page_title = 'Mi Perfil';
include 'includes/header.php';
?>

<div class="profile-container">
    <div class="profile-sidebar">
        <div class="profile-info">
            <h2><?php echo $user['username']; ?></h2>
            <p class="user-role"><?php echo ucfirst($user['role']); ?></p>
            <p class="member-since">Miembro desde <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
            
            <?php if ($user['bio']): ?>
                <div class="user-bio">
                    <h4>Acerca de mí</h4>
                    <p><?php echo nl2br($user['bio']); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-stats">
            <h4>Estadísticas</h4>
            <ul>
                <li>Posts publicados: <?php echo count($user_posts); ?></li>
                <li>
                    Total de vistas: 
                    <?php 
                    $total_views = array_sum(array_column($user_posts, 'views'));
                    echo $total_views;
                    ?>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="profile-main">
        <div class="profile-tabs">
            <button class="tab-button active" onclick="showTab('posts')">Mis Posts</button>
            <button class="tab-button" onclick="showTab('settings')">Configuración</button>
        </div>
        
        <div id="posts-tab" class="tab-content active">
            <h3>Mis Posts</h3>
            
            <div class="user-posts">
                <?php if (empty($user_posts)): ?>
                    <p>No has publicado ningún post aún. <a href="create-post.php">¡Crea tu primer post!</a></p>
                <?php else: ?>
                    <?php foreach ($user_posts as $post): ?>
                        <div class="user-post-item">
                            <h4><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></h4>
                            <div class="post-meta">
                                <span><?php echo time_ago($post['created_at']); ?></span>
                                <span><?php echo $post['views']; ?> vistas</span>
                                <span class="status-<?php echo $post['status']; ?>"><?php echo ucfirst($post['status']); ?></span>
                            </div>
                            <div class="post-actions">
                                <a href="edit-post.php?id=<?php echo $post['id']; ?>">Editar</a>
                                <a href="delete-post.php?id=<?php echo $post['id']; ?>" 
                                   onclick="return confirm('¿Estás seguro?')">Eliminar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div id="settings-tab" class="tab-content">
            <h3>Configuración de Perfil</h3>
            
            <form method="POST" action="" class="profile-form">
                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? $_POST['username'] : $user['username']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? $_POST['email'] : $user['email']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="bio">Biografía:</label>
                    <textarea id="bio" name="bio" rows="4" placeholder="Cuéntanos sobre ti..."><?php echo isset($_POST['bio']) ? $_POST['bio'] : $user['bio']; ?></textarea>
                </div>
                
                <hr>
                <h4>Cambiar Contraseña</h4>
                
                <div class="form-group">
                    <label for="current_password">Contraseña Actual:</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña:</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                
                <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>