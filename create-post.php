<?php
require_once 'includes/functions.php';

if (!is_logged_in()) {
    flash_message('error', 'Debes iniciar sesión para crear un post');
    redirect('login.php');
}

$categories = get_categories();

if ($_POST) {
    $title = validate_and_sanitize_input($_POST['title'] ?? '', 'string');
    $content = validate_and_sanitize_input($_POST['content'] ?? '', 'string');
    $category_id = validate_and_sanitize_input($_POST['category_id'] ?? '', 'int');
    $excerpt = validate_and_sanitize_input($_POST['excerpt'] ?? '', 'string');
    
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'El título es requerido';
    }
    
    if (empty($content)) {
        $errors[] = 'El contenido es requerido';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, excerpt, user_id, category_id, status) VALUES (?, ?, ?, ?, ?, 'published')");
        
        if ($stmt->execute([$title, $content, $excerpt, $_SESSION['user_id'], $category_id])) {
            $post_id = $pdo->lastInsertId();
            flash_message('success', 'Post creado exitosamente');
            redirect("post.php?id=$post_id");
        } else {
            flash_message('error', 'Error al crear el post');
        }
    } else {
        foreach ($errors as $error) {
            flash_message('error', $error);
        }
    }
}

$page_title = 'Crear Post';
include 'includes/header.php';
?>

<div class="create-post-container">
    <h2>Crear Nuevo Post</h2>
    
    <form method="POST" action="" class="post-form">
        <div class="form-group">
            <label for="title">Título:</label>
            <input type="text" id="title" name="title" required 
                   value="<?php echo isset($_POST['title']) ? sanitize_output($_POST['title']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="category_id">Categoría:</label>
            <select id="category_id" name="category_id">
                <option value="">Seleccionar categoría</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"
                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo $category['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="excerpt">Resumen (opcional):</label>
            <textarea id="excerpt" name="excerpt" rows="3" placeholder="Breve descripción del post..."><?php echo isset($_POST['excerpt']) ? sanitize_output($_POST['excerpt']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="content">Contenido:</label>
            <textarea id="content" name="content" rows="15" required><?php echo isset($_POST['content']) ? sanitize_output($_POST['content']) : ''; ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Publicar Post</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>