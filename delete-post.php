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
    flash_message('error', 'No tienes permisos para eliminar este post');
    redirect('index.php');
}

// Eliminar el post
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
if ($stmt->execute([$post_id])) {
    flash_message('success', 'Post eliminado exitosamente');
} else {
    flash_message('error', 'Error al eliminar el post');
}

redirect('index.php');
?>