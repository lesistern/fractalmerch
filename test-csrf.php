<?php
require_once 'includes/functions.php';
session_start();

// Test básico de las funciones CSRF
echo "<h1>Test de Funciones CSRF</h1>";
echo "<h2>1. Generar Token CSRF</h2>";

// Generar token
$token = generate_csrf_token();
echo "Token generado: " . htmlspecialchars($token) . "<br>";
echo "Tiempo de generación: " . $_SESSION['csrf_token_time'] . "<br>";

echo "<h2>2. Validar Token CSRF</h2>";

// Validar token válido
$valid = validate_csrf_token($token);
echo "Token válido: " . ($valid ? "SÍ" : "NO") . "<br>";

// Validar token inválido
$invalid = validate_csrf_token("token_falso");
echo "Token inválido: " . ($invalid ? "SÍ" : "NO") . "<br>";

echo "<h2>3. Campo CSRF para formularios</h2>";
echo "Campo HTML: " . csrf_field() . "<br>";

echo "<h2>4. Test de Sanitización</h2>";
$dirty_data = "<script>alert('XSS')</script>test@example.com";
echo "Datos sucios: " . $dirty_data . "<br>";
echo "Sanitizado como string: " . sanitize_input($dirty_data, 'string') . "<br>";
echo "Sanitizado como email: " . sanitize_input('test@example.com', 'email') . "<br>";

echo "<h2>5. Test de Roles</h2>";
$_SESSION['role'] = 'admin';
echo "Rol actual: " . $_SESSION['role'] . "<br>";
echo "¿Es admin?: " . (has_role('admin') ? "SÍ" : "NO") . "<br>";
echo "¿Es moderador?: " . (has_role('moderator') ? "SÍ" : "NO") . "<br>";

echo "<h2>6. Formulario de Test</h2>";
?>
<form method="post" action="test-csrf.php">
    <?php echo csrf_field(); ?>
    <input type="text" name="test_input" placeholder="Ingresa algo">
    <button type="submit">Enviar</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>7. Resultado del Test POST</h2>";
    
    if (isset($_POST['csrf_token'])) {
        echo "Token recibido: " . htmlspecialchars($_POST['csrf_token']) . "<br>";
        
        if (validate_csrf_token($_POST['csrf_token'])) {
            echo "✅ Token CSRF válido<br>";
            echo "Dato recibido: " . sanitize_input($_POST['test_input']) . "<br>";
            
            // Invalidar token después de uso
            invalidate_csrf_token();
            echo "Token invalidado correctamente<br>";
        } else {
            echo "❌ Token CSRF inválido<br>";
        }
    } else {
        echo "❌ No se recibió token CSRF<br>";
    }
}
?>