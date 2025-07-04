<?php
require_once 'includes/functions.php';

if (!is_logged_in()) {
    flash_message('error', 'Debes iniciar sesión');
    redirect('login.php');
}

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$post_id) {
    redirect('index.php');
}

$post = get_post_by_id($post_id);

if (!$post) {
    flash_message('error', 'Post no encontrado');
    redirect('index.php');
}

// Verificar permisos
if ($_SESSION['user_id'] != $post['user_id'] && !is_admin()) {
    flash_message('error', 'No tienes permisos para editar este post');
    redirect('index.php');
}

$categories = get_categories();

if ($_POST) {
    $title = sanitize_input($_POST['title']);
    $content = $_POST['content'];
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $excerpt = sanitize_input($_POST['excerpt']);
    
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'El título es requerido';
    }
    
    if (empty($content)) {
        $errors[] = 'El contenido es requerido';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, excerpt = ?, category_id = ? WHERE id = ?");
        
        if ($stmt->execute([$title, $content, $excerpt, $category_id, $post_id])) {
            flash_message('success', 'Post actualizado exitosamente');
            redirect("post.php?id=$post_id");
        } else {
            flash_message('error', 'Error al actualizar el post');
        }
    } else {
        foreach ($errors as $error) {
            flash_message('error', $error);
        }
    }
}

$page_title = 'Editar Post';
include 'includes/header.php';
?>

<div class="edit-post-container">
    <h2>Editar Post</h2>
    
    <form method="POST" action="" class="post-form">
        <div class="form-group">
            <label for="title">Título:</label>
            <input type="text" id="title" name="title" required 
                   value="<?php echo isset($_POST['title']) ? $_POST['title'] : $post['title']; ?>">
        </div>
        
        <div class="form-group">
            <label for="category_id">Categoría:</label>
            <select id="category_id" name="category_id">
                <option value="">Seleccionar categoría</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"
                            <?php 
                            $selected = isset($_POST['category_id']) ? $_POST['category_id'] : $post['category_id'];
                            echo ($selected == $category['id']) ? 'selected' : ''; 
                            ?>>
                        <?php echo $category['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="excerpt">Resumen (opcional):</label>
            <textarea id="excerpt" name="excerpt" rows="3" placeholder="Breve descripción del post..."><?php echo isset($_POST['excerpt']) ? $_POST['excerpt'] : $post['excerpt']; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="content">Contenido:</label>
            <textarea id="content" name="content" rows="15" required><?php echo isset($_POST['content']) ? $_POST['content'] : $post['content']; ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Actualizar Post</button>
            <a href="post.php?id=<?php echo $post_id; ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>