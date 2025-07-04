<?php
require_once 'includes/functions.php';

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$post_id) {
    redirect('index.php');
}

$post = get_post_by_id($post_id);

if (!$post) {
    flash_message('error', 'Post no encontrado');
    redirect('index.php');
}

// Incrementar contador de vistas
$stmt = $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
$stmt->execute([$post_id]);

// Obtener comentarios
$comments = get_comments_by_post($post_id);

// Procesar nuevo comentario
if ($_POST && is_logged_in()) {
    $content = sanitize_input($_POST['content']);
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    
    if (!empty($content)) {
        if (create_comment($post_id, $_SESSION['user_id'], $content, $parent_id)) {
            flash_message('success', 'Comentario enviado. Será visible una vez aprobado.');
            redirect("post.php?id=$post_id");
        } else {
            flash_message('error', 'Error al enviar el comentario');
        }
    } else {
        flash_message('error', 'El comentario no puede estar vacío');
    }
}

$page_title = $post['title'];
include 'includes/header.php';
?>

<div class="post-container">
    <article class="post-full">
        <header class="post-header">
            <h1><?php echo $post['title']; ?></h1>
            <div class="post-meta">
                <span>Por <?php echo $post['username']; ?></span>
                <span><?php echo time_ago($post['created_at']); ?></span>
                <span><?php echo $post['views']; ?> vistas</span>
                <?php if ($post['category_name']): ?>
                    <span class="category-tag"><?php echo $post['category_name']; ?></span>
                <?php endif; ?>
            </div>
            
            <?php if (is_logged_in() && ($_SESSION['user_id'] == $post['user_id'] || is_admin())): ?>
                <div class="post-actions">
                    <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-small">Editar</a>
                    <a href="delete-post.php?id=<?php echo $post['id']; ?>" class="btn btn-small btn-danger" 
                       onclick="return confirm('¿Estás seguro de eliminar este post?')">Eliminar</a>
                </div>
            <?php endif; ?>
        </header>
        
        <div class="post-content">
            <?php echo nl2br($post['content']); ?>
        </div>
    </article>
    
    <section class="comments-section">
        <h3>Comentarios (<?php echo count($comments); ?>)</h3>
        
        <?php if (is_logged_in()): ?>
            <form method="POST" action="" class="comment-form">
                <div class="form-group">
                    <textarea name="content" placeholder="Escribe tu comentario..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar Comentario</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Inicia sesión</a> para comentar.</p>
        <?php endif; ?>
        
        <div class="comments-list">
            <?php if (empty($comments)): ?>
                <p>No hay comentarios aún.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment" id="comment-<?php echo $comment['id']; ?>">
                        <div class="comment-header">
                            <strong><?php echo $comment['username']; ?></strong>
                            <span class="comment-date"><?php echo time_ago($comment['created_at']); ?></span>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br($comment['content']); ?>
                        </div>
                        
                        <?php if (is_logged_in()): ?>
                            <div class="comment-actions">
                                <a href="#" class="reply-link" data-comment-id="<?php echo $comment['id']; ?>">Responder</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>