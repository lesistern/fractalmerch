<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para gestionar categorías');
    redirect('../index.php');
}

// Procesar acciones
if ($_POST) {
    if (isset($_POST['create_category'])) {
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            if ($stmt->execute([$name, $description])) {
                flash_message('success', 'Categoría creada exitosamente');
            } else {
                flash_message('error', 'Error al crear la categoría');
            }
        } else {
            flash_message('error', 'El nombre de la categoría es requerido');
        }
    }
    
    if (isset($_POST['edit_category'])) {
        $id = (int)$_POST['category_id'];
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        
        if (!empty($name)) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $id])) {
                flash_message('success', 'Categoría actualizada exitosamente');
            } else {
                flash_message('error', 'Error al actualizar la categoría');
            }
        } else {
            flash_message('error', 'El nombre de la categoría es requerido');
        }
    }
    
    redirect('manage-categories.php');
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Verificar si hay posts con esta categoría
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
    $stmt->execute([$id]);
    $post_count = $stmt->fetchColumn();
    
    if ($post_count > 0) {
        flash_message('error', "No se puede eliminar la categoría porque tiene $post_count posts asociados");
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            flash_message('success', 'Categoría eliminada exitosamente');
        } else {
            flash_message('error', 'Error al eliminar la categoría');
        }
    }
    
    redirect('manage-categories.php');
}

// Obtener categorías
$categories = get_categories();

// Obtener categoría para editar
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_category = $stmt->fetch();
}

$page_title = 'Gestionar Categorías';
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Panel Admin</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage-users.php">Gestionar Usuarios</a></li>
            <li><a href="manage-posts.php">Gestionar Posts</a></li>
            <li><a href="manage-comments.php">Gestionar Comentarios</a></li>
            <li><a href="manage-categories.php" class="active">Categorías</a></li>
            <li><a href="../index.php">Volver al Sitio</a></li>
        </ul>
    </div>
    
    <div class="admin-main">
        <h2>Gestionar Categorías</h2>
        
        <div class="category-form">
            <h3><?php echo $edit_category ? 'Editar Categoría' : 'Crear Nueva Categoría'; ?></h3>
            
            <form method="POST" action="">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo $edit_category ? $edit_category['name'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Descripción:</label>
                    <textarea id="description" name="description" rows="3"><?php echo $edit_category ? $edit_category['description'] : ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="<?php echo $edit_category ? 'edit_category' : 'create_category'; ?>" 
                            class="btn btn-primary">
                        <?php echo $edit_category ? 'Actualizar' : 'Crear'; ?> Categoría
                    </button>
                    
                    <?php if ($edit_category): ?>
                        <a href="manage-categories.php" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="categories-list">
            <h3>Categorías Existentes</h3>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Posts</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        // Contar posts en esta categoría
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
                        $stmt->execute([$category['id']]);
                        $post_count = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo $category['name']; ?></td>
                            <td><?php echo $category['description']; ?></td>
                            <td><?php echo $post_count; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?php echo $category['id']; ?>" class="btn btn-small">Editar</a>
                                    
                                    <?php if ($post_count == 0): ?>
                                        <a href="?delete=<?php echo $category['id']; ?>" 
                                           class="btn btn-small btn-danger"
                                           onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                                            Eliminar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No se puede eliminar</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>