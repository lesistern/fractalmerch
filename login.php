<?php
require_once 'includes/functions.php';
require_once 'includes/oauth/OAuthManager.php';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_POST) {
    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        flash_message('error', 'Token de seguridad inválido. Por favor, intenta de nuevo.');
    } else {
        $email = validate_and_sanitize_input($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        
        if (!$email || empty($password)) {
            flash_message('error', 'Por favor completa todos los campos con información válida');
        } else {
            if (authenticate_user($email, $password)) {
                // Invalidar token después de uso exitoso
                invalidate_csrf_token();
                flash_message('success', 'Bienvenido de vuelta!');
                redirect('index.php');
            } else {
                flash_message('error', 'Email o contraseña incorrectos');
            }
        }
    }
}

$page_title = 'Iniciar Sesión';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-form">
        <h2>Iniciar Sesión</h2>
        
        <form method="POST" action="">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? sanitize_output($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </form>
        
        <!-- Divisor -->
        <div class="auth-divider">
            <span>O continúa con</span>
        </div>
        
        <!-- Botones OAuth -->
        <div class="oauth-buttons">
            <a href="auth/oauth-login.php?provider=google" class="oauth-btn google-btn">
                <i class="fab fa-google"></i>
                <span>Google</span>
            </a>
            
            <a href="auth/oauth-login.php?provider=facebook" class="oauth-btn facebook-btn">
                <i class="fab fa-facebook-f"></i>
                <span>Facebook</span>
            </a>
            
            <a href="auth/oauth-login.php?provider=apple" class="oauth-btn apple-btn">
                <i class="fab fa-apple"></i>
                <span>Apple</span>
            </a>
            
            <a href="auth/oauth-login.php?provider=microsoft" class="oauth-btn microsoft-btn">
                <i class="fab fa-microsoft"></i>
                <span>Microsoft</span>
            </a>
        </div>
        
        <p class="auth-link">
            ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>