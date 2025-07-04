<?php
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$edit_product = null;

// AJAX endpoint to get product data
if (isset($_GET['action']) && $_GET['action'] === 'get_product' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    try {
        $product = get_product_by_id($product_id);
        
        header('Content-Type: application/json');
        if ($product) {
            echo json_encode([
                'success' => true,
                'product' => $product
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Producto no encontrado'
            ]);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error interno del servidor: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Manejo de acciones (añadir, editar, eliminar)
if ($_POST) {
    error_log("POST data received: " . json_encode(array_keys($_POST)));
    error_log("FILES data received: " . json_encode(array_keys($_FILES)));
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    $price = (float)$_POST['price'];
    $cost = (float)$_POST['cost'];
    $sku = sanitize_input($_POST['sku']);
    $main_image_url = sanitize_input($_POST['main_image_url']);
    $category_id = empty($_POST['category_id']) ? null : (int)$_POST['category_id'];
    $variants = $_POST['variants'] ?? [];
    
    // Manejo de carga de imagen principal
    if (isset($_FILES['mainImageFile']) && $_FILES['mainImageFile']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['mainImageFile']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid('product_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['mainImageFile']['tmp_name'], $upload_path)) {
                $main_image_url = 'assets/images/products/' . $new_filename;
                error_log("Image uploaded successfully: $main_image_url");
            } else {
                error_log("Failed to move uploaded file");
                flash_message('warning', 'La imagen no se pudo cargar, pero el producto se guardó');
            }
        } else {
            error_log("Invalid file extension: $file_extension");
            flash_message('warning', 'Formato de imagen no válido. Use JPG, PNG, WEBP o SVG');
        }
    }

    // Limpiar variantes vacías
    $variants = array_filter($variants, function($variant) {
        return !empty($variant['size']) || !empty($variant['color']) || !empty($variant['measure']) || (isset($variant['stock']) && $variant['stock'] !== '');
    });

    if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
        // Editar producto
        $product_id = (int)$_POST['product_id'];
        error_log("Updating product ID: $product_id with data: " . json_encode([
            'name' => $name,
            'description' => substr($description, 0, 50) . '...',
            'price' => $price,
            'main_image_url' => $main_image_url,
            'variants_count' => count($variants),
            'files_received' => isset($_FILES['mainImageFile']) ? $_FILES['mainImageFile']['error'] : 'none'
        ]));
        
        if (update_product($product_id, $name, $description, $price, $cost, $sku, $main_image_url, $category_id, $variants)) {
            flash_message('success', 'Producto actualizado exitosamente');
            error_log("Product $product_id updated successfully with image: $main_image_url");
        } else {
            flash_message('error', 'Error al actualizar el producto');
            error_log("Failed to update product $product_id");
        }
    } else {
        // Añadir producto
        error_log("Adding new product with data: " . json_encode([
            'name' => $name,
            'description' => substr($description, 0, 50) . '...',
            'price' => $price,
            'main_image_url' => $main_image_url,
            'variants_count' => count($variants),
            'files_received' => isset($_FILES['mainImageFile']) ? $_FILES['mainImageFile']['error'] : 'none'
        ]));
        
        if (add_product($name, $description, $price, $cost, $sku, $main_image_url, $category_id, $variants)) {
            flash_message('success', 'Producto añadido exitosamente');
            error_log("New product added successfully with image: $main_image_url");
        } else {
            flash_message('error', 'Error al añadir el producto');
            error_log("Failed to add new product");
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

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <!-- Main Content -->
    <div class="modern-admin-main">
        <!-- Header Tiendanube Style -->
        <div class="tiendanube-header">
            <div class="header-left">
                <h1>Productos</h1>
            </div>
            <div class="header-right">
                <button class="tn-btn tn-btn-secondary" onclick="organizeProducts()">
                    <i class="fas fa-sort"></i>
                    Organizar
                </button>
                <button class="tn-btn tn-btn-secondary" onclick="exportProducts()">
                    <i class="fas fa-download"></i>
                    Exportar e importar
                </button>
                <button id="toggleProductForm" class="tn-btn tn-btn-primary">
                    <i class="fas fa-plus"></i>
                    Agregar producto
                </button>
            </div>
        </div>

        <!-- Product Form Modal/Panel -->
        <div id="productFormOverlay" class="product-form-overlay"></div>
        <div id="productFormPanel" class="product-form-panel" style="display: none;">
            <div class="form-panel-header">
                <h2 id="formTitle">
                    <i class="fas fa-plus-circle"></i>
                    <?php echo $edit_product ? 'Editar Producto' : 'Nuevo Producto'; ?>
                </h2>
                <button id="closeFormPanel" class="btn-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="" method="POST" class="modern-product-form" enctype="multipart/form-data">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                <?php endif; ?>

                <!-- Form Tabs -->
                <div class="form-tabs">
                    <button type="button" class="tab-btn active" data-tab="basic">
                        <i class="fas fa-info-circle"></i>
                        Información Básica
                    </button>
                    <button type="button" class="tab-btn" data-tab="images">
                        <i class="fas fa-images"></i>
                        Imágenes
                    </button>
                    <button type="button" class="tab-btn" data-tab="variants">
                        <i class="fas fa-layer-group"></i>
                        Variantes
                    </button>
                    <button type="button" class="tab-btn" data-tab="pricing">
                        <i class="fas fa-dollar-sign"></i>
                        Precios & Stock
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Basic Information Tab -->
                    <div class="tab-panel active" id="basic-tab">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="name" class="form-label">
                                    <i class="fas fa-tag"></i>
                                    Nombre del Producto
                                </label>
                                <input type="text" id="name" name="name" 
                                       value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" 
                                       class="form-input" 
                                       placeholder="Ej: Remera Personalizada Premium" 
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="sku" class="form-label">
                                    <i class="fas fa-barcode"></i>
                                    SKU/Código
                                </label>
                                <input type="text" id="sku" name="sku" 
                                       value="<?php echo $edit_product ? htmlspecialchars($edit_product['sku']) : ''; ?>" 
                                       class="form-input" 
                                       placeholder="Ej: REM-001" 
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-folder"></i>
                                    Categoría
                                </label>
                                <select id="category_id" name="category_id" class="form-select">
                                    <option value="">Seleccionar Categoría</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left"></i>
                                    Descripción
                                </label>
                                <textarea id="description" name="description" 
                                          class="form-textarea" 
                                          rows="4" 
                                          placeholder="Describe tu producto de manera detallada..."><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Images Tab -->
                    <div class="tab-panel" id="images-tab">
                        <div class="image-upload-section">
                            <div class="main-image-upload">
                                <label class="image-upload-label">
                                    <i class="fas fa-camera"></i>
                                    <span>Imagen Principal</span>
                                    <div class="image-preview" id="mainImagePreview">
                                        <?php if ($edit_product && $edit_product['main_image_url']): ?>
                                            <img src="<?php echo htmlspecialchars($edit_product['main_image_url']); ?>" alt="Preview">
                                        <?php else: ?>
                                            <div class="upload-placeholder">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <p>Arrastra o haz clic para subir</p>
                                                <small>JPG, PNG o SVG</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" id="mainImageFile" name="mainImageFile" accept="image/*" style="display: none;">
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label for="main_image_url" class="form-label">
                                    <i class="fas fa-link"></i>
                                    URL de Imagen (Alternativo)
                                </label>
                                <input type="text" id="main_image_url" name="main_image_url" 
                                       value="<?php echo $edit_product ? htmlspecialchars($edit_product['main_image_url']) : ''; ?>" 
                                       class="form-input" 
                                       placeholder="https://ejemplo.com/imagen.jpg">
                            </div>

                            <div class="additional-images">
                                <h4><i class="fas fa-images"></i> Galería Adicional</h4>
                                <div class="image-gallery" id="imageGallery">
                                    <div class="add-image-btn" onclick="addImageSlot()">
                                        <i class="fas fa-plus"></i>
                                        <span>Agregar Imagen</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Variants Tab -->
                    <div class="tab-panel" id="variants-tab">
                        <div class="variants-section">
                            <div class="variants-header">
                                <h3><i class="fas fa-layer-group"></i> Variantes del Producto</h3>
                                <button type="button" class="btn-secondary" id="addVariantBtn">
                                    <i class="fas fa-plus"></i>
                                    Agregar Variante
                                </button>
                            </div>

                            <div id="variantsContainer" class="variants-container">
                                <?php if ($edit_product && !empty($edit_product['variants'])): ?>
                                    <?php foreach ($edit_product['variants'] as $index => $variant): ?>
                                        <div class="variant-card" data-index="<?php echo $index; ?>">
                                            <div class="variant-header">
                                                <h4>Variante #<?php echo $index + 1; ?></h4>
                                                <button type="button" class="btn-remove" onclick="removeVariant(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <div class="variant-form">
                                                <div class="form-row">
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fas fa-ruler"></i>
                                                            Talle/Tamaño
                                                        </label>
                                                        <input type="text" 
                                                               name="variants[<?php echo $index; ?>][size]" 
                                                               value="<?php echo htmlspecialchars($variant['size'] ?? ''); ?>"
                                                               placeholder="S, M, L, XL...">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fas fa-palette"></i>
                                                            Color
                                                        </label>
                                                        <input type="text" 
                                                               name="variants[<?php echo $index; ?>][color]" 
                                                               value="<?php echo htmlspecialchars($variant['color'] ?? ''); ?>"
                                                               placeholder="Rojo, Azul, Verde...">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fas fa-weight"></i>
                                                            Medida
                                                        </label>
                                                        <input type="text" 
                                                               name="variants[<?php echo $index; ?>][measure]" 
                                                               value="<?php echo htmlspecialchars($variant['measure'] ?? ''); ?>"
                                                               placeholder="330ml, 500g...">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fas fa-boxes"></i>
                                                            Stock
                                                        </label>
                                                        <input type="number" 
                                                               name="variants[<?php echo $index; ?>][stock]" 
                                                               value="<?php echo htmlspecialchars($variant['stock'] ?? 0); ?>"
                                                               min="0" required>
                                                    </div>
                                                </div>
                                                <div class="variant-image-upload">
                                                    <label>
                                                        <i class="fas fa-image"></i>
                                                        Imagen de Variante
                                                    </label>
                                                    <div class="mini-image-preview">
                                                        <input type="file" accept="image/*" style="display: none;">
                                                        <div class="mini-upload-placeholder">
                                                            <i class="fas fa-camera"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="variant-card" data-index="0">
                                        <div class="variant-header">
                                            <h4>Variante #1</h4>
                                            <button type="button" class="btn-remove" onclick="removeVariant(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="variant-form">
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label>
                                                        <i class="fas fa-ruler"></i>
                                                        Talle/Tamaño
                                                    </label>
                                                    <input type="text" name="variants[0][size]" placeholder="S, M, L, XL...">
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <i class="fas fa-palette"></i>
                                                        Color
                                                    </label>
                                                    <input type="text" name="variants[0][color]" placeholder="Rojo, Azul, Verde...">
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <i class="fas fa-weight"></i>
                                                        Medida
                                                    </label>
                                                    <input type="text" name="variants[0][measure]" placeholder="330ml, 500g...">
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <i class="fas fa-boxes"></i>
                                                        Stock
                                                    </label>
                                                    <input type="number" name="variants[0][stock]" value="0" min="0" required>
                                                </div>
                                            </div>
                                            <div class="variant-image-upload">
                                                <label>
                                                    <i class="fas fa-image"></i>
                                                    Imagen de Variante
                                                </label>
                                                <div class="mini-image-preview">
                                                    <input type="file" accept="image/*" style="display: none;">
                                                    <div class="mini-upload-placeholder">
                                                        <i class="fas fa-camera"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Tab -->
                    <div class="tab-panel" id="pricing-tab">
                        <div class="pricing-grid">
                            <div class="form-group">
                                <label for="price" class="form-label">
                                    <i class="fas fa-dollar-sign"></i>
                                    Precio de Venta
                                </label>
                                <div class="input-group">
                                    <span class="input-prefix">$</span>
                                    <input type="number" id="price" name="price" 
                                           step="0.01" 
                                           value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" 
                                           class="form-input" 
                                           placeholder="0.00" 
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="cost" class="form-label">
                                    <i class="fas fa-coins"></i>
                                    Costo del Producto
                                </label>
                                <div class="input-group">
                                    <span class="input-prefix">$</span>
                                    <input type="number" id="cost" name="cost" 
                                           step="0.01" 
                                           value="<?php echo $edit_product ? $edit_product['cost'] : ''; ?>" 
                                           class="form-input" 
                                           placeholder="0.00">
                                </div>
                            </div>

                            <div class="profit-display">
                                <h4><i class="fas fa-chart-line"></i> Análisis de Ganancia</h4>
                                <div class="profit-info">
                                    <div class="profit-item">
                                        <span class="label">Margen Bruto:</span>
                                        <span class="value" id="profitMargin">$0.00</span>
                                    </div>
                                    <div class="profit-item">
                                        <span class="label">% Ganancia:</span>
                                        <span class="value" id="profitPercentage">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn-secondary" id="cancelForm">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i>
                        <?php echo $edit_product ? 'Actualizar Producto' : 'Guardar Producto'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Products Grid -->
        <div class="products-section">
            <div class="tiendanube-search-bar">
                <div class="tn-search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" id="productSearch" placeholder="Busca por nombre, SKU o tags">
                </div>
                <div class="tn-filters">
                    <button class="tn-filter-btn" onclick="toggleFilters()">
                        <i class="fas fa-filter"></i>
                        Filtrar
                    </button>
                    <button class="tn-filter-btn" onclick="toggleMoreOptions()">
                        <i class="fas fa-plus"></i>
                        Más nuevo
                    </button>
                </div>
            </div>
            
            <div class="tn-products-counter">
                <span id="productsCount"><?php echo count($products); ?> productos</span>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state" style="padding: 3rem; text-align: center;">
                    <i class="fas fa-box-open" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3 style="color: #111827; font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">No hay productos</h3>
                    <p style="color: #6b7280; margin-bottom: 1.5rem;">Comienza agregando tu primer producto al catálogo</p>
                    <button class="btn-primary" onclick="window.adminPanel.showProductForm()">
                        <i class="fas fa-plus"></i>
                        Crear Primer Producto
                    </button>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="tn-products-table">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
                                <th width="80"></th>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th>Promocional</th>
                                <th width="120">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr class="tn-product-row" data-id="<?php echo $product['id']; ?>">
                                    <td>
                                        <input type="checkbox" class="product-checkbox" value="<?php echo $product['id']; ?>" onchange="updateBulkActions()">
                                    </td>
                                    <td class="tn-product-image">
                                        <?php if ($product['main_image_url']): ?>
                                            <img src="../<?php echo htmlspecialchars($product['main_image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="tn-image-placeholder" style="display: none;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="tn-image-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="tn-product-info">
                                        <div class="tn-product-name">
                                            <a href="#" onclick="editProduct(<?php echo $product['id']; ?>)" class="tn-product-link">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="tn-stock-cell">
                                        <?php 
                                        $stock = $product['total_stock'];
                                        if ($stock == 0): ?>
                                            <span class="tn-stock-infinite">∞ Infinito</span>
                                        <?php else: ?>
                                            <span class="tn-stock-number"><?php echo $stock; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="tn-price-cell">
                                        <div class="tn-price-input">
                                            <span class="currency">$</span>
                                            <input type="text" value="<?php echo number_format($product['price'], 0, '', ''); ?>" 
                                                   class="tn-inline-input" 
                                                   onchange="updatePrice(<?php echo $product['id']; ?>, this.value)">
                                        </div>
                                    </td>
                                    <td class="tn-promotional-cell">
                                        <div class="tn-price-input">
                                            <span class="currency">$</span>
                                            <input type="text" placeholder="" 
                                                   class="tn-inline-input tn-promotional" 
                                                   onchange="updatePromotionalPrice(<?php echo $product['id']; ?>, this.value)">
                                        </div>
                                    </td>
                                    <td class="tn-actions-cell">
                                        <div class="tn-actions">
                                            <button class="tn-action-btn" onclick="shareProduct(<?php echo $product['id']; ?>)" title="Compartir">
                                                <i class="fas fa-share-alt"></i>
                                            </button>
                                            <button class="tn-action-btn" onclick="duplicateProduct(<?php echo $product['id']; ?>)" title="Duplicar">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button class="tn-action-btn tn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Toast Notifications Container -->
<div id="toastContainer" class="toast-container"></div>

<!-- Scripts -->
<script src="../assets/js/modern-admin.js?v=<?php echo time(); ?>"></script>
<script src="includes/admin-functions.js?v=<?php echo time(); ?>"></script>
<script>
// Initialize admin panel
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the admin panel
    window.adminPanel = new ModernAdminPanel();
    
    // Initialize all advanced features
    initializeFilters();
    initializeViewToggle();
    
    // Show welcome toast
    setTimeout(() => {
        toast.success('Panel de administración cargado', 'Sistema listo');
    }, 500);
    
    <?php if ($edit_product): ?>
        // Show form automatically and populate with existing product data
        setTimeout(function() {
            window.adminPanel.showProductForm(<?php echo json_encode($edit_product); ?>);
        }, 100);
    <?php endif; ?>
    
    // Initialize keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('productSearch')?.focus();
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            window.adminPanel?.hideProductForm();
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
        
        // Ctrl/Cmd + N for new product
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            window.adminPanel?.showProductForm();
        }
    });
});

// Additional functions for product management
async function editProduct(productId) {
    try {
        console.log('Editing product:', productId);
        
        // Fetch product data via AJAX
        const response = await fetch(`manage-products.php?action=get_product&id=${productId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.product) {
            console.log('Product data received:', data.product);
            // Show the form with the product data
            window.adminPanel.showProductForm(data.product);
        } else {
            toast.error('Error al cargar producto', data.message || 'Producto no encontrado');
        }
    } catch (error) {
        console.error('Error fetching product:', error);
        toast.error('Error', 'No se pudo cargar el producto');
    }
}

function viewProduct(productId) {
    console.log('Viewing product:', productId);
    // Redirect to product detail page
    window.open(`../product-detail.php?id=${productId}`, '_blank');
}

function duplicateProduct(productId) {
    console.log('Duplicating product:', productId);
    // This could be implemented as needed
    toast.info('Función pendiente', 'La duplicación de productos estará disponible pronto');
}

function deleteProduct(productId) {
    if (confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')) {
        console.log('Deleting product:', productId);
        window.location.href = `manage-products.php?delete=${productId}`;
    }
}

function toggleProductStatus(productId, currentStatus) {
    console.log('Toggling product status:', productId, currentStatus);
    // This functionality would need to be implemented in the backend
    toast.info('Función pendiente', 'El cambio de estado estará disponible pronto');
}

function toggleActionDropdown(productId) {
    const dropdown = document.getElementById(`dropdown-${productId}`);
    if (dropdown) {
        dropdown.classList.toggle('show');
        
        // Close other dropdowns
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            if (menu !== dropdown) {
                menu.classList.remove('show');
            }
        });
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.action-dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Bulk operations functions
function toggleSelectAll(checkbox) {
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    productCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'flex';
        selectedCount.textContent = `${checkedBoxes.length} seleccionados`;
    } else {
        bulkActions.style.display = 'none';
    }
}

function bulkEdit() {
    console.log('Bulk edit functionality');
    toast.info('Función pendiente', 'La edición masiva estará disponible pronto');
}

function bulkExport() {
    console.log('Bulk export functionality');
    toast.info('Función pendiente', 'La exportación masiva estará disponible pronto');
}

function bulkToggleStatus() {
    console.log('Bulk toggle status functionality');
    toast.info('Función pendiente', 'El cambio de estado masivo estará disponible pronto');
}

function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    if (checkedBoxes.length > 0 && confirm(`¿Estás seguro de que quieres eliminar ${checkedBoxes.length} productos? Esta acción no se puede deshacer.`)) {
        console.log('Bulk delete functionality');
        toast.info('Función pendiente', 'La eliminación masiva estará disponible pronto');
    }
}

// Search and filter functions
function initializeFilters() {
    const searchInput = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterProductsTable);
    }
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProductsTable);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterProductsTable);
    }
}

function filterProductsTable() {
    const searchTerm = document.getElementById('productSearch')?.value.toLowerCase() || '';
    const categoryFilter = document.getElementById('categoryFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    
    const tableRows = document.querySelectorAll('.products-table tbody tr');
    
    tableRows.forEach(row => {
        const productName = row.querySelector('.product-name-cell')?.textContent.toLowerCase() || '';
        const productSku = row.querySelector('.product-sku-cell')?.textContent.toLowerCase() || '';
        
        const matchesSearch = !searchTerm || 
            productName.includes(searchTerm) || 
            productSku.includes(searchTerm);
        
        // Add category and status filtering logic as needed
        const matchesCategory = !categoryFilter;
        const matchesStatus = !statusFilter;
        
        row.style.display = matchesSearch && matchesCategory && matchesStatus ? '' : 'none';
    });
}

// View toggle functions
function initializeViewToggle() {
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const view = e.currentTarget.dataset.view;
            switchView(view);
        });
    });
}

function switchView(view) {
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(btn => {
        btn.classList.remove('active');
    });
    
    const activeBtn = document.querySelector(`[data-view="${view}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    // Add view switching logic as needed
    console.log('Switched to view:', view);
}

// Simple toast system if not available
if (typeof toast === 'undefined') {
    window.toast = {
        success: (title, message) => console.log('Success:', title, message),
        error: (title, message) => console.log('Error:', title, message),
        info: (title, message) => console.log('Info:', title, message),
        warning: (title, message) => console.log('Warning:', title, message)
    };
}

// Tiendanube Style Functions
function organizeProducts() {
    console.log('Organize products functionality');
    toast.info('Función pendiente', 'La organización de productos estará disponible pronto');
}

function exportProducts() {
    console.log('Export products functionality');
    toast.info('Función pendiente', 'La exportación de productos estará disponible pronto');
}

function toggleFilters() {
    console.log('Toggle filters functionality');
    toast.info('Función pendiente', 'Los filtros avanzados estarán disponibles pronto');
}

function toggleMoreOptions() {
    console.log('Toggle more options functionality');
    toast.info('Función pendiente', 'Más opciones estarán disponibles pronto');
}

function updatePrice(productId, newPrice) {
    console.log('Updating price for product:', productId, 'New price:', newPrice);
    // Here you would make an AJAX call to update the price
    toast.info('Función pendiente', 'La actualización de precios estará disponible pronto');
}

function updatePromotionalPrice(productId, newPrice) {
    console.log('Updating promotional price for product:', productId, 'New price:', newPrice);
    // Here you would make an AJAX call to update the promotional price
    toast.info('Función pendiente', 'La actualización de precios promocionales estará disponible pronto');
}

function shareProduct(productId) {
    console.log('Sharing product:', productId);
    toast.info('Función pendiente', 'La función de compartir estará disponible pronto');
}

// Update products counter
function updateProductsCounter() {
    const visibleRows = document.querySelectorAll('.tn-product-row:not([style*="display: none"])');
    const counter = document.getElementById('productsCount');
    if (counter) {
        counter.textContent = `${visibleRows.length} productos`;
    }
}

// Enhanced search function for Tiendanube style
function enhancedProductSearch() {
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.tn-product-row');
            
            rows.forEach(row => {
                const productName = row.querySelector('.tn-product-name')?.textContent.toLowerCase() || '';
                const productLink = row.querySelector('.tn-product-link')?.textContent.toLowerCase() || '';
                
                const matches = productName.includes(searchTerm) || productLink.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
            
            updateProductsCounter();
        });
    }
}

// Initialize enhanced features
document.addEventListener('DOMContentLoaded', function() {
    enhancedProductSearch();
    updateProductsCounter();
});
</script>

</body>
</html>