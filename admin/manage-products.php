<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$edit_product = null;

// Manejo de acciones (añadir, editar, eliminar)
if ($_POST) {
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    $price = (float)$_POST['price'];
    $cost = (float)$_POST['cost'];
    $sku = sanitize_input($_POST['sku']);
    $main_image_url = sanitize_input($_POST['main_image_url']);
    $category_id = empty($_POST['category_id']) ? null : (int)$_POST['category_id'];
    $variants = $_POST['variants'] ?? [];

    // Limpiar variantes vacías
    $variants = array_filter($variants, function($variant) {
        return !empty($variant['size']) || !empty($variant['color']) || !empty($variant['measure']) || (isset($variant['stock']) && $variant['stock'] !== '');
    });

    if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
        // Editar producto
        $product_id = (int)$_POST['product_id'];
        if (update_product($product_id, $name, $description, $price, $cost, $sku, $main_image_url, $category_id, $variants)) {
            flash_message('success', 'Producto actualizado exitosamente');
        } else {
            flash_message('error', 'Error al actualizar el producto');
        }
    } else {
        // Añadir producto
        if (add_product($name, $description, $price, $cost, $sku, $main_image_url, $category_id, $variants)) {
            flash_message('success', 'Producto añadido exitosamente');
        } else {
            flash_message('error', 'Error al añadir el producto');
        }
    }
    redirect('manage-products.php');
}

if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    if (delete_product($product_id)) {
        flash_message('success', 'Producto eliminado exitosamente');
    } else {
        flash_message('error', 'Error al eliminar el producto');
    }
    redirect('manage-products.php');
}

if (isset($_GET['edit'])) {
    $product_id = (int)$_GET['edit'];
    $edit_product = get_product_by_id($product_id);
    if (!$edit_product) {
        flash_message('error', 'Producto no encontrado');
        redirect('manage-products.php');
    }
}

// Obtener productos para mostrar
$products = get_products();
$categories = get_categories();

$page_title = '📦 Gestionar Productos - Panel Admin';
include 'admin-dashboard-header.php';

?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Panel Admin</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage-users.php">Gestionar Usuarios</a></li>
            <li><a href="manage-posts.php">Gestionar Posts</a></li>
            <li><a href="manage-comments.php">Gestionar Comentarios</a></li>
            <li><a href="manage-products.php" class="active">📦 Gestionar Productos</a></li>
            <li><a href="manage-categories.php">Categorías</a></li>
            <li><a href="generate-images.php">🎨 Generar Imágenes</a></li>
            <li><a href="../index.php">Volver al Sitio</a></li>
        </ul>
    </div>

    <div class="admin-main">
        <h2>Gestionar Productos</h2>

        <!-- Formulario para añadir/editar producto -->
        <div class="form-card">
            <h3><?php echo $edit_product ? 'Editar Producto' : 'Añadir Nuevo Producto'; ?></h3>
            <form action="" method="POST">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="name">Nombre del Producto:</label>
                    <input type="text" id="name" name="name" value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Descripción:</label>
                    <textarea id="description" name="description"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Precio:</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="cost">Costo:</label>
                    <input type="number" id="cost" name="cost" step="0.01" value="<?php echo $edit_product ? $edit_product['cost'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="sku">SKU/Código:</label>
                    <input type="text" id="sku" name="sku" value="<?php echo $edit_product ? htmlspecialchars($edit_product['sku']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="main_image_url">URL Imagen Principal:</label>
                    <input type="text" id="main_image_url" name="main_image_url" value="<?php echo $edit_product ? htmlspecialchars($edit_product['main_image_url']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="category_id">Categoría:</label>
                    <select id="category_id" name="category_id">
                        <option value="">Seleccionar Categoría</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h4>Variantes del Producto</h4>
                <div id="variants-container">
                    <?php if ($edit_product && !empty($edit_product['variants'])): ?>
                        <?php foreach ($edit_product['variants'] as $index => $variant): ?>
                            <div class="variant-item">
                                <div class="form-group">
                                    <label>Talle:</label>
                                    <input type="text" name="variants[<?php echo $index; ?>][size]" value="<?php echo htmlspecialchars($variant['size'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Color:</label>
                                    <input type="text" name="variants[<?php echo $index; ?>][color]" value="<?php echo htmlspecialchars($variant['color'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Medida (ej. 330ml):</label>
                                    <input type="text" name="variants[<?php echo $index; ?>][measure]" value="<?php echo htmlspecialchars($variant['measure'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Stock:</label>
                                    <input type="number" name="variants[<?php echo $index; ?>][stock]" value="<?php echo htmlspecialchars($variant['stock'] ?? 0); ?>" required>
                                </div>
                                <button type="button" class="btn btn-danger remove-variant">-</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="variant-item">
                            <div class="form-group">
                                <label>Talle:</label>
                                <input type="text" name="variants[0][size]">
                            </div>
                            <div class="form-group">
                                <label>Color:</label>
                                <input type="text" name="variants[0][color]">
                            </div>
                            <div class="form-group">
                                <label>Medida (ej. 330ml):</label>
                                <input type="text" name="variants[0][measure]">
                            </div>
                            <div class="form-group">
                                <label>Stock:</label>
                                <input type="number" name="variants[0][stock]" value="0" required>
                            </div>
                            <button type="button" class="btn btn-danger remove-variant">-</button>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-secondary" id="add-variant">+ Añadir Variante</button>

                <button type="submit" name="<?php echo $edit_product ? 'edit_product' : 'add_product'; ?>" class="btn btn-primary">Guardar Producto</button>
            </form>
        </div>

        <!-- Tabla de productos existentes -->
        <div class="admin-table-container">
            <h3>Productos Existentes</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>SKU</th>
                        <th>Stock Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6">No hay productos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                <td><?php echo $product['total_stock']; ?></td>
                                <td>
                                    <a href="manage-products.php?edit=<?php echo $product['id']; ?>" class="btn btn-small btn-warning">Editar</a>
                                    <a href="manage-products.php?delete=<?php echo $product['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('¿Seguro que quieres eliminar este producto y todas sus variantes?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="admin-footer">
    <div class="container">
        <p>&copy; 2025 <?php echo SITE_NAME; ?> - Panel de Administración</p>
    </div>
</footer>

<script>
// Lógica para añadir/eliminar campos de variante dinámicamente
document.addEventListener('DOMContentLoaded', function() {
    let variantCount = <?php echo ($edit_product && !empty($edit_product['variants'])) ? count($edit_product['variants']) : 1; ?>;
    const variantsContainer = document.getElementById('variants-container');
    const addVariantBtn = document.getElementById('add-variant');

    addVariantBtn.addEventListener('click', function() {
        const newVariantHtml = `
            <div class="variant-item">
                <div class="form-group">
                    <label>Talle:</label>
                    <input type="text" name="variants[${variantCount}][size]">
                </div>
                <div class="form-group">
                    <label>Color:</label>
                    <input type="text" name="variants[${variantCount}][color]">
                </div>
                <div class="form-group">
                    <label>Medida (ej. 330ml):</label>
                    <input type="text" name="variants[${variantCount}][measure]">
                </div>
                <div class="form-group">
                    <label>Stock:</label>
                    <input type="number" name="variants[${variantCount}][stock]" value="0" required>
                </div>
                <button type="button" class="btn btn-danger remove-variant">-</button>
            </div>
        `;
        variantsContainer.insertAdjacentHTML('beforeend', newVariantHtml);
        variantCount++;
    });

    variantsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-variant')) {
            e.target.closest('.variant-item').remove();
        }
    });
});
</script>

</body>
</html>