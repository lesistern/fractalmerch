<?php
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_POST) {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = 'Por favor completa todos los campos';
    }
    
    if (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = 'El nombre de usuario debe tener entre 3 y 20 caracteres';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Por favor ingresa un email válido';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Las contraseñas no coinciden';
    }
    
    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $errors[] = 'El nombre de usuario o email ya están en uso';
    }
    
    if (empty($errors)) {
        if (create_user($username, $email, $password)) {
            flash_message('success', 'Cuenta creada exitosamente. Puedes iniciar sesión ahora.');
            redirect('login.php');
        } else {
            flash_message('error', 'Error al crear la cuenta. Inténtalo de nuevo.');
        }
    } else {
        foreach ($errors as $error) {
            flash_message('error', $error);
        }
    }
}

$page_title = 'Registrarse';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-form">
        <h2>Registrarse</h2>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Nombre de Usuario:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Registrarse</button>
        </form>
        
        <p class="auth-link">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>