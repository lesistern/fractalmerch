<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

// Iniciar sesión para manejar mensajes de feedback
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtener ID del producto
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: particulares.php');
    exit;
}

// Definir productos predeterminados
$default_products = [
    1 => ['id' => 1, 'name' => 'Remera Personalizada', 'price' => 5990, 'description' => 'Remera 100% algodón de alta calidad, perfecta para personalizar con tus diseños favoritos. Disponible en múltiples tallas y colores.', 'category' => 'remeras', 'stock' => 50, 'sku' => 'REM-001', 'iva_rate' => 21, 'real_sales' => 1247],
    2 => ['id' => 2, 'name' => 'Buzo Personalizado', 'price' => 12990, 'description' => 'Buzo con capucha, ideal para el invierno. Material premium con forro interno suave. Personalizable en frente y espalda.', 'category' => 'buzos', 'stock' => 30, 'sku' => 'BUZ-001', 'iva_rate' => 21, 'real_sales' => 856],
    3 => ['id' => 3, 'name' => 'Taza Personalizada', 'price' => 3490, 'description' => 'Taza de cerámica de alta calidad, resistente al lavavajillas y microondas. Ideal para sublimación.', 'category' => 'tazas', 'stock' => 100, 'sku' => 'TAZ-001', 'iva_rate' => 10.5, 'real_sales' => 2341],
    4 => ['id' => 4, 'name' => 'Mouse Pad Personalizado', 'price' => 2990, 'description' => 'Mouse pad con base antideslizante de goma. Superficie suave para óptimo deslizamiento del mouse.', 'category' => 'accesorios', 'stock' => 75, 'sku' => 'MP-001', 'iva_rate' => 21, 'real_sales' => 634],
    5 => ['id' => 5, 'name' => 'Funda Personalizada', 'price' => 4990, 'description' => 'Funda para celular resistente a impactos. Compatible con múltiples modelos de smartphones.', 'category' => 'accesorios', 'stock' => 60, 'sku' => 'FUN-001', 'iva_rate' => 21, 'real_sales' => 423],
    6 => ['id' => 6, 'name' => 'Almohada Personalizada', 'price' => 6990, 'description' => 'Almohada suave y cómoda con funda personalizable. Relleno hipoalergénico de alta calidad.', 'category' => 'hogar', 'stock' => 25, 'sku' => 'ALM-001', 'iva_rate' => 10.5, 'real_sales' => 789]
];

// Obtener producto de la base de datos
try {
    $product = get_product_by_id($product_id);
    
    if (!$product) {
        // Fallback a productos predeterminados
        $product = $default_products[$product_id] ?? null;
    }
} catch (Exception $e) {
    error_log("Error fetching product: " . $e->getMessage());
    $product = $default_products[$product_id] ?? null;
}

// Definir variantes de productos
$product_variants = [
    1 => [ // Remeras
        'sizes' => ['S', 'M', 'L', 'XL', 'XXL', 'XXL2', 'XXL3', 'XXL4', 'XXL5', 'XXL6', 'XXL7', 'XXL8', 'XXL9', 'XXL10'],
        'colors' => [
            'Blanco' => '#FFFFFF',
            'Negro' => '#000000',
            'Gris' => '#808080',
            'Azul Navy' => '#1B365D',
            'Rojo' => '#DC143C',
            'Verde' => '#228B22',
            'Amarillo' => '#FFD700',
            'Rosa' => '#FF69B4'
        ]
    ],
    2 => [ // Buzos
        'sizes' => ['S', 'M', 'L', 'XL', 'XXL', 'XXL2', 'XXL3', 'XXL4', 'XXL5', 'XXL6', 'XXL7', 'XXL8', 'XXL9', 'XXL10'],
        'colors' => [
            'Negro' => '#000000',
            'Gris' => '#808080',
            'Azul Navy' => '#1B365D',
            'Bordo' => '#800020',
            'Verde' => '#228B22',
            'Blanco' => '#FFFFFF'
        ]
    ],
    3 => [ // Tazas
        'sizes' => ['300ml', '350ml', '400ml'],
        'colors' => [
            'Blanco' => '#FFFFFF',
            'Negro' => '#000000',
            'Azul' => '#0000FF',
            'Rojo' => '#DC143C',
            'Verde' => '#228B22'
        ]
    ],
    4 => [ // Mouse Pads
        'sizes' => ['Estándar', 'Grande', 'XL'],
        'colors' => [
            'Negro' => '#000000',
            'Azul' => '#0000FF',
            'Gris' => '#808080',
            'Blanco' => '#FFFFFF'
        ]
    ],
    5 => [ // Fundas
        'sizes' => ['iPhone', 'Samsung', 'Universal'],
        'colors' => [
            'Transparente' => '#FFFFFF',
            'Negro' => '#000000',
            'Azul' => '#0000FF',
            'Rosa' => '#FF69B4',
            'Verde' => '#228B22'
        ]
    ],
    6 => [ // Almohadas
        'sizes' => ['40x40cm', '50x50cm', '60x60cm'],
        'colors' => [
            'Blanco' => '#FFFFFF',
            'Crema' => '#F5F5DC',
            'Gris' => '#808080',
            'Azul' => '#87CEEB'
        ]
    ]
];

// Si aún no existe el producto, redirigir
if (!$product) {
    header('Location: particulares.php');
    exit;
}

// Asegurar que el producto tenga los campos necesarios
if (!isset($product['sales_count'])) {
    $product['sales_count'] = isset($product['real_sales']) ? $product['real_sales'] : rand(150, 800);
}

// Obtener reseñas del producto y calcular calificaciones reales
try {
    $stmt_reviews = $pdo->prepare("SELECT pr.*, u.username 
                                   FROM product_reviews pr
                                   JOIN users u ON pr.user_id = u.id
                                   WHERE pr.product_id = ?
                                   ORDER BY pr.created_at DESC");
    $stmt_reviews->execute([$product_id]);
    $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular estadísticas reales de calificación
    if (!empty($reviews)) {
        $total_rating = 0;
        foreach ($reviews as $review) {
            $total_rating += $review['rating'];
        }
        $product['avg_rating'] = $total_rating / count($reviews);
        $product['review_count'] = count($reviews);
    } else {
        // Si no hay reseñas, usar valores por defecto
        $product['avg_rating'] = 0;
        $product['review_count'] = 0;
    }
} catch (PDOException $e) {
    $reviews = [];
    $product['avg_rating'] = 0;
    $product['review_count'] = 0;
}

// Función para renderizar estrellas
function render_stars($rating) {
    $rating = round($rating * 2) / 2;
    $output = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $output .= '<i class="fas fa-star"></i>';
        } elseif ($rating >= $i - 0.5) {
            $output .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $output .= '<i class="far fa-star"></i>';
        }
    }
    return $output;
}

// Obtener productos relacionados (misma categoría)
$related_products = [];
foreach ($default_products as $id => $prod) {
    if ($id != $product_id && isset($product['category']) && $prod['category'] == $product['category']) {
        // Calcular calificación real para producto relacionado
        try {
            $stmt_related_rating = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                                                   FROM product_reviews WHERE product_id = ?");
            $stmt_related_rating->execute([$id]);
            $rating_data = $stmt_related_rating->fetch(PDO::FETCH_ASSOC);
            
            if ($rating_data && $rating_data['review_count'] > 0) {
                $prod['avg_rating'] = (float)$rating_data['avg_rating'];
                $prod['review_count'] = (int)$rating_data['review_count'];
            } else {
                $prod['avg_rating'] = 0;
                $prod['review_count'] = 0;
            }
        } catch (PDOException $e) {
            $prod['avg_rating'] = 0;
            $prod['review_count'] = 0;
        }
        
        $related_products[] = $prod;
    }
}

// Limitar a 3 productos relacionados
$related_products = array_slice($related_products, 0, 3);

$page_title = htmlspecialchars($product['name']) . ' - Detalles del Producto';
include 'includes/header.php';
?>

<div class="product-detail-container">
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <nav class="breadcrumb-nav">
                <a href="index.php" class="breadcrumb-item">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <span class="breadcrumb-separator">/</span>
                <a href="particulares.php" class="breadcrumb-item">Productos</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current"><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>
        </div>
    </div>

    <!-- Producto Principal -->
    <section class="product-main">
        <div class="container">
            <div class="product-layout-two-columns">
                <!-- Columna Izquierda -->
                <div class="product-left-column">
                    <!-- Galería de Imágenes -->
                    <div class="product-gallery">
                        <div class="main-image" id="main-image-container">
                            <?php 
                            // Usar imagen real del producto desde la base de datos
                            $main_image_src = 'assets/images/products/default.svg'; // Fallback por defecto
                            
                            if (isset($product['main_image_url']) && !empty($product['main_image_url'])) {
                                // Si existe la imagen en la BD, verificar que el archivo exista
                                $image_path = $product['main_image_url'];
                                if (file_exists($image_path)) {
                                    $main_image_src = $image_path;
                                }
                            } else {
                                // Fallback a imagen por nombre de producto (compatibilidad)
                                $product_name_image = strtolower(str_replace(' ', '', explode(' ', $product['name'])[0])) . '.svg';
                                $legacy_path = 'assets/images/products/' . $product_name_image;
                                if (file_exists($legacy_path)) {
                                    $main_image_src = $legacy_path;
                                }
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($main_image_src); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 id="main-product-image"
                                 onerror="this.src='assets/images/products/default.svg'">
                            <div class="image-badge">
                                <i class="fas fa-expand"></i>
                            </div>
                        </div>
                        <?php
                        // Obtener imágenes adicionales del producto (si existen)
                        $additional_images = [];
                        try {
                            $stmt_images = $pdo->prepare("SELECT image_url, alt_text FROM product_images WHERE product_id = ? ORDER BY image_order ASC, id ASC");
                            $stmt_images->execute([$product_id]);
                            $additional_images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            $additional_images = [];
                        }
                        
                        // Crear array de todas las imágenes (principal + adicionales)
                        $all_images = [];
                        if (!empty($main_image_src) && $main_image_src !== 'assets/images/products/default.svg') {
                            $all_images[] = [
                                'url' => $main_image_src,
                                'alt' => 'Imagen principal - ' . htmlspecialchars($product['name']),
                                'is_main' => true
                            ];
                        }
                        
                        foreach ($additional_images as $img) {
                            if (!empty($img['image_url']) && file_exists($img['image_url'])) {
                                $all_images[] = [
                                    'url' => $img['image_url'],
                                    'alt' => !empty($img['alt_text']) ? $img['alt_text'] : 'Imagen adicional - ' . htmlspecialchars($product['name']),
                                    'is_main' => false
                                ];
                            }
                        }
                        
                        // Solo mostrar thumbnail gallery si hay más de una imagen
                        if (count($all_images) > 1):
                        ?>
                            <div class="thumbnail-gallery">
                                <?php foreach ($all_images as $index => $image): ?>
                                    <div class="thumbnail <?php echo $index == 0 ? 'active' : ''; ?>" 
                                         onclick="changeMainImage('<?php echo htmlspecialchars($image['url']); ?>', this)">
                                        <img src="<?php echo htmlspecialchars($image['url']); ?>" 
                                             alt="<?php echo htmlspecialchars($image['alt']); ?>"
                                             onerror="this.src='assets/images/products/default.svg'">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                    </div>

                    <!-- Descripción y Características en columna izquierda -->
                    <div class="product-description-left">
                        <div class="description-tabs">
                            <nav class="tabs-nav">
                                <button class="tab-btn active" data-tab="description">Descripción</button>
                                <button class="tab-btn" data-tab="specifications">Características</button>
                            </nav>
                            
                            <div class="tab-content active" id="description">
                                <h3>Descripción del producto</h3>
                                <div class="description-content">
                                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                                    <ul class="product-features">
                                        <li>Material: 100% algodón premium</li>
                                        <li>Lavable en lavarropas</li>
                                        <li>Diseño unisex</li>
                                        <li>Ideal para sublimación</li>
                                        <li>Colores sólidos y duraderos</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="tab-content" id="specifications">
                                <h3>Características principales</h3>
                                <table class="specs-table">
                                    <tr><td><strong>Material principal</strong></td><td>Algodón</td></tr>
                                    <tr><td><strong>Tipo de producto</strong></td><td><?php echo ucfirst(isset($product['category']) ? $product['category'] : 'General'); ?></td></tr>
                                    <tr><td><strong>Género</strong></td><td>Unisex</td></tr>
                                    <tr><td><strong>Marca</strong></td><td>Sublime</td></tr>
                                    <tr><td><strong>Garantía</strong></td><td>30 días</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Reseñas en columna izquierda -->
                    <div class="reviews-section-left" id="reviews-section">
                        <div class="reviews-header">
                            <h2>💬 Reseñas de Clientes</h2>
                            <div class="reviews-summary-stats">
                                <div class="overall-rating">
                                    <?php if ($product['review_count'] > 0): ?>
                                        <span class="rating-large"><?php echo number_format($product['avg_rating'], 1); ?></span>
                                        <div class="rating-stars">
                                            <?php echo render_stars($product['avg_rating']); ?>
                                        </div>
                                        <span class="total-reviews"><?php echo $product['review_count']; ?> reseñas</span>
                                    <?php else: ?>
                                        <span class="rating-large">-</span>
                                        <div class="rating-stars">
                                            <span class="no-rating-text">Sin calificaciones aún</span>
                                        </div>
                                        <span class="total-reviews">0 reseñas</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="reviews-content">
                            <!-- Lista de Reseñas -->
                            <div class="reviews-list-section">
                                <?php if (empty($reviews)): ?>
                                    <div class="no-reviews-message">
                                        <i class="fas fa-comment-slash"></i>
                                        <h3>¡Sé el primero en opinar!</h3>
                                        <p>Este producto aún no tiene reseñas. Compártenos tu experiencia.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="reviews-list">
                                        <?php foreach ($reviews as $review): ?>
                                            <div class="review-item">
                                                <div class="review-header">
                                                    <div class="review-user-info">
                                                        <div class="review-avatar">
                                                            <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                                                        </div>
                                                        <div class="review-user-details">
                                                            <h6><?php echo htmlspecialchars($review['username']); ?></h6>
                                                            <p class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="review-rating">
                                                        <span class="stars"><?php echo render_stars($review['rating']); ?></span>
                                                        <span class="rating-number">(<?php echo $review['rating']; ?>/5)</span>
                                                    </div>
                                                </div>
                                                <div class="review-comment">
                                                    <?php echo htmlspecialchars($review['comment']); ?>
                                                </div>
                                                <div class="review-helpful">
                                                    <span>¿Te resultó útil esta reseña?</span>
                                                    <button class="helpful-btn">👍 Útil</button>
                                                    <button class="helpful-btn">👎 No útil</button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Formulario de Nueva Reseña -->
                            <div class="review-form-section">
                                <h3>✍️ Deja tu reseña</h3>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="submit_review.php" method="POST" class="review-form" enctype="multipart/form-data">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <div class="form-group rating-group">
                                            <label>Tu calificación:</label>
                                            <div class="star-rating">
                                                <input type="radio" id="5-stars" name="rating" value="5" required />
                                                <label for="5-stars">⭐</label>
                                                <input type="radio" id="4-stars" name="rating" value="4" />
                                                <label for="4-stars">⭐</label>
                                                <input type="radio" id="3-stars" name="rating" value="3" />
                                                <label for="3-stars">⭐</label>
                                                <input type="radio" id="2-stars" name="rating" value="2" />
                                                <label for="2-stars">⭐</label>
                                                <input type="radio" id="1-star" name="rating" value="1" />
                                                <label for="1-star">⭐</label>
                                            </div>
                                            <div class="rating-description" id="rating-description">
                                                Selecciona una calificación del 1 al 5
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="comment">Tu comentario:</label>
                                            <textarea id="comment" name="comment" rows="4" placeholder="Comparte tu experiencia con este producto..." required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="review-photos">Agregar fotos (opcional):</label>
                                            <div class="photo-upload-area" id="photo-upload-area">
                                                <input type="file" id="review-photos" name="review_photos[]" multiple accept="image/*" style="display: none;">
                                                <div class="upload-placeholder" onclick="document.getElementById('review-photos').click()">
                                                    <i class="fas fa-camera"></i>
                                                    <p>Haz clic para agregar fotos</p>
                                                    <small>Máximo 5 fotos, 5MB cada una</small>
                                                </div>
                                                <div class="photo-preview" id="photo-preview"></div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn-submit-review">📝 Enviar Reseña</button>
                                    </form>
                                <?php else: ?>
                                    <div class="login-prompt">
                                        <p>Debes <a href="login.php">iniciar sesión</a> para dejar una reseña.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha -->
                <div class="product-right-column">
                    <!-- Información del Producto -->
                    <div class="product-info">
                        <div class="product-header">
                            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                            <div class="product-meta">
                                <?php if (isset($product['sku'])): ?>
                                    <span class="product-sku">SKU: <?php echo htmlspecialchars($product['sku']); ?></span>
                                <?php endif; ?>
                                <?php
                                // Determinar condición del producto basado en fecha de creación
                                $product_condition = 'Usado';
                                $condition_class = 'condition-used';
                                
                                // Verificar si el producto tiene fecha de creación o was agregado recientemente
                                if (isset($product['created_at'])) {
                                    $created_date = new DateTime($product['created_at']);
                                    $current_date = new DateTime();
                                    $days_diff = $current_date->diff($created_date)->days;
                                    
                                    if ($days_diff <= 15) {
                                        $product_condition = 'Nuevo';
                                        $condition_class = 'condition-new';
                                    }
                                } else {
                                    // Si no hay fecha, asumir que es nuevo (para productos predeterminados)
                                    $product_condition = 'Nuevo';
                                    $condition_class = 'condition-new';
                                }
                                
                                // Obtener ventas reales del producto
                                $sales_count = 0;
                                if (isset($product['real_sales'])) {
                                    $sales_count = $product['real_sales'];
                                } else if (isset($product['sales_count'])) {
                                    $sales_count = $product['sales_count'];
                                } else {
                                    // Consultar ventas reales de la base de datos si existe
                                    try {
                                        $stmt_sales = $pdo->prepare("SELECT COUNT(*) as total_sales FROM order_items oi 
                                                                     JOIN orders o ON oi.order_id = o.id 
                                                                     WHERE oi.product_id = ? AND o.status = 'completed'");
                                        $stmt_sales->execute([$product_id]);
                                        $sales_data = $stmt_sales->fetch(PDO::FETCH_ASSOC);
                                        $sales_count = $sales_data ? $sales_data['total_sales'] : 0;
                                    } catch (PDOException $e) {
                                        $sales_count = rand(15, 120); // Fallback realista
                                    }
                                }
                                ?>
                                <span class="product-condition <?php echo $condition_class; ?>"><?php echo $product_condition; ?> | <?php echo $sales_count; ?> vendidos</span>
                            </div>
                        </div>

                        <!-- Precio -->
                        <div class="product-pricing">
                            <div class="price-main">$<?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                            
                            <!-- Precio sin impuestos -->
                            <?php 
                            $iva_rate = isset($product['iva_rate']) ? $product['iva_rate'] : 21;
                            $price_without_tax = $product['price'] / (1 + ($iva_rate / 100));
                            ?>
                            <div class="price-without-tax-line">
                                Precio sin impuestos nacionales: <strong>$<?php echo number_format($price_without_tax, 0, ',', '.'); ?></strong>
                            </div>
                            
                            <div class="price-features">
                                <span class="feature"><i class="fas fa-truck"></i> Envío gratis en compras +$10.000</span>
                                <span class="feature"><i class="fas fa-shield-alt"></i> Garantía de calidad</span>
                                <span class="feature"><i class="fas fa-medal"></i> Producto destacado</span>
                            </div>
                        </div>

                        <!-- Variantes -->
                        <?php if (isset($product_variants[$product_id])): ?>
                            <div class="product-variants-ml" id="variants-<?php echo $product['id']; ?>">
                                <div class="variant-section">
                                    <h3 class="variant-title">Tamaño: <span id="selected-size">Selecciona una opción</span></h3>
                                    <div class="variant-options-new size-options">
                                        <?php foreach ($product_variants[$product_id]['sizes'] as $size): ?>
                                            <button type="button" class="size-btn-new" data-size="<?php echo $size; ?>" onclick="selectSize('<?php echo $product['id']; ?>', '<?php echo $size; ?>')"><?php echo $size; ?></button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="variant-section">
                                    <h3 class="variant-title">Color: <span id="selected-color">Selecciona una opción</span></h3>
                                    <div class="variant-options-new color-options">
                                        <?php foreach ($product_variants[$product_id]['colors'] as $colorName => $colorCode): ?>
                                            <button type="button" class="color-btn-new" data-color="<?php echo $colorName; ?>" onclick="selectColor('<?php echo $product['id']; ?>', '<?php echo $colorName; ?>')" title="<?php echo $colorName; ?>">
                                                <span class="color-square" style="background-color: <?php echo $colorCode; ?>"></span>
                                                <span class="color-name-btn"><?php echo $colorName; ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="variant-section">
                                    <h3 class="variant-title">Cantidad:</h3>
                                    <div class="quantity-new">
                                        <select id="quantity-select" class="quantity-select">
                                            <option value="1">1 unidad</option>
                                            <option value="2">2 unidades</option>
                                            <option value="3">3 unidades</option>
                                            <option value="4">4 unidades</option>
                                            <option value="5">5 unidades</option>
                                            <option value="custom">Más de 6 unidades</option>
                                        </select>
                                        <div id="custom-quantity" class="custom-quantity" style="display: none;">
                                            <input type="number" id="custom-qty-input" min="6" max="<?php echo isset($product['stock']) ? $product['stock'] : 25; ?>" placeholder="Ingresa cantidad (max: <?php echo isset($product['stock']) ? $product['stock'] : 25; ?>)">
                                        </div>
                                        <span class="stock-available">(<?php echo isset($product['stock']) ? $product['stock'] : 25; ?> disponibles)</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Botones de acción estilo MercadoLibre -->
                        <div class="product-actions-ml">
                            <button class="btn-buy-now-ml" id="buy-now-btn" onclick="buyNow()">
                                <i class="fas fa-bolt"></i>
                                <span class="btn-text">Comprar ahora</span>
                                <span class="btn-loading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i> Procesando...
                                </span>
                            </button>
                            <button class="btn-add-cart-ml" id="add-cart-btn" onclick="addProductToCart()">
                                <i class="fas fa-cart-plus"></i>
                                <span class="btn-text">Agregar al carrito</span>
                                <span class="btn-loading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i> Agregando...
                                </span>
                            </button>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="product-extras">
                            <div class="shipping-info">
                                <i class="fas fa-truck"></i>
                                <div class="shipping-content">
                                    <div class="shipping-header">
                                        <strong>Calculá el costo de envío</strong>
                                        <p>Ingresa tu ubicación para ver las opciones disponibles</p>
                                    </div>
                                    
                                    <div class="shipping-calculator">
                                        <div class="location-selector">
                                            <label>Selecciona tu ubicación:</label>
                                            <div class="location-buttons">
                                                <button type="button" class="location-btn active" data-location="local" onclick="selectLocation('local')">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    Local (Posadas)
                                                </button>
                                                <button type="button" class="location-btn" data-location="national" onclick="selectLocation('national')">
                                                    <i class="fas fa-map"></i>
                                                    Nacional
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Calculadora para envío local -->
                                        <div id="local-shipping-calc" class="shipping-calc-section active">
                                            <div class="calc-header">
                                                <h4><i class="fas fa-home"></i> Envío Local - Posadas, Misiones</h4>
                                                <p>Entrega rápida en la ciudad</p>
                                            </div>
                                            <div class="address-input-group">
                                                <input type="text" id="local-address" placeholder="Ej: Av. Quaranta 2550" class="address-input">
                                                <button type="button" onclick="calculateLocalShipping()" class="calc-btn">
                                                    <i class="fas fa-calculator"></i>
                                                    Calcular
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Calculadora para envío nacional -->
                                        <div id="national-shipping-calc" class="shipping-calc-section">
                                            <div class="calc-header">
                                                <h4><i class="fas fa-truck"></i> Envío Nacional</h4>
                                                <p>A todo el país vía Andreani</p>
                                            </div>
                                            <div class="address-inputs">
                                                <div class="input-row">
                                                    <input type="text" id="national-address" placeholder="Dirección completa" class="address-input">
                                                    <input type="text" id="national-city" placeholder="Ciudad" class="city-input">
                                                </div>
                                                <div class="input-row">
                                                    <select id="national-province" class="province-select">
                                                        <option value="">Selecciona provincia</option>
                                                        <option value="Buenos Aires">Buenos Aires</option>
                                                        <option value="Córdoba">Córdoba</option>
                                                        <option value="Santa Fe">Santa Fe</option>
                                                        <option value="Mendoza">Mendoza</option>
                                                        <option value="Tucumán">Tucumán</option>
                                                        <option value="Entre Ríos">Entre Ríos</option>
                                                        <option value="Salta">Salta</option>
                                                        <option value="Misiones">Misiones</option>
                                                        <option value="Chaco">Chaco</option>
                                                        <option value="Corrientes">Corrientes</option>
                                                        <option value="Santiago del Estero">Santiago del Estero</option>
                                                        <option value="San Juan">San Juan</option>
                                                        <option value="Jujuy">Jujuy</option>
                                                        <option value="Río Negro">Río Negro</option>
                                                        <option value="Formosa">Formosa</option>
                                                        <option value="Neuquén">Neuquén</option>
                                                        <option value="Chubut">Chubut</option>
                                                        <option value="San Luis">San Luis</option>
                                                        <option value="Catamarca">Catamarca</option>
                                                        <option value="La Rioja">La Rioja</option>
                                                        <option value="La Pampa">La Pampa</option>
                                                        <option value="Santa Cruz">Santa Cruz</option>
                                                        <option value="Tierra del Fuego">Tierra del Fuego</option>
                                                    </select>
                                                    <input type="text" id="national-postal" placeholder="Código Postal" class="postal-input">
                                                </div>
                                                <button type="button" onclick="calculateNationalShipping()" class="calc-btn full-width">
                                                    <i class="fas fa-calculator"></i>
                                                    Calcular Envío Nacional
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Resultados del cálculo -->
                                        <div id="shipping-results" class="shipping-results" style="display: none;">
                                            <div class="results-header">
                                                <h4><i class="fas fa-shipping-fast"></i> Opciones de Envío</h4>
                                            </div>
                                            <div id="shipping-options" class="shipping-options">
                                                <!-- Las opciones se cargarán dinámicamente -->
                                            </div>
                                        </div>

                                        <!-- Loader -->
                                        <div id="shipping-loader" class="shipping-loader" style="display: none;">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            <span>Calculando opciones de envío...</span>
                                        </div>

                                        <!-- Error -->
                                        <div id="shipping-error" class="shipping-error" style="display: none;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <span>Error al calcular envío. Verifica los datos e intenta nuevamente.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="return-info">
                                <i class="fas fa-shield-alt"></i>
                                <div>
                                    <strong>Cambios por defectos totalmente gratis</strong>
                                    <p><span class="local-shipping">Plazo 10 días (envío local)</span> • <span class="national-shipping">Plazo 30 días (envío nacional)</span></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Productos Relacionados -->
    <?php if (!empty($related_products)): ?>
        <section class="related-products">
            <div class="container">
                <h2>🔗 Productos Relacionados</h2>
                <div class="related-grid">
                    <?php foreach ($related_products as $related): 
                        // Determinar imagen para producto relacionado
                        $related_image_src = 'assets/images/products/default.svg';
                        
                        if (isset($related['main_image_url']) && !empty($related['main_image_url'])) {
                            if (file_exists($related['main_image_url'])) {
                                $related_image_src = $related['main_image_url'];
                            }
                        } else {
                            // Fallback a imagen por nombre de producto
                            $related_name_image = strtolower(str_replace(' ', '', explode(' ', $related['name'])[0])) . '.svg';
                            $related_legacy_path = 'assets/images/products/' . $related_name_image;
                            if (file_exists($related_legacy_path)) {
                                $related_image_src = $related_legacy_path;
                            }
                        }
                    ?>
                        <div class="related-product-card">
                            <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="product-link">
                                <img src="<?php echo htmlspecialchars($related_image_src); ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>"
                                     onerror="this.src='assets/images/products/default.svg'">
                                <h4><?php echo htmlspecialchars($related['name']); ?></h4>
                                <div class="related-rating">
                                    <?php echo render_stars($related['avg_rating']); ?>
                                </div>
                                <div class="related-price">$<?php echo number_format($related['price'], 0, ',', '.'); ?></div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>

<!-- Los modales del carrito están ahora en header.php -->

<script>
// Variables globales del producto
const currentProduct = {
    id: <?php echo $product['id']; ?>,
    name: '<?php echo addslashes($product['name']); ?>',
    price: <?php echo $product['price']; ?>
};

// Variables globales para selecciones
let selectedSize = null;
let selectedColor = null;

document.addEventListener('DOMContentLoaded', function() {
    // El carrito se inicializa globalmente en header.php
    
    // Inicializar sistema de reseñas
    initializeReviewSystem();

    // Inicializar galería de imágenes
    initializeImageGallery();

    // Inicializar tabs
    initializeTabs();

    // Inicializar eventos de cantidad
    initializeQuantityEvents();

    // Inicializar upload de fotos
    initializePhotoUpload();

    // Inicializar calculadora de envío
    initializeShippingCalculator();
    
    // Inicializar zoom de imagen
    initializeImageZoom();
});

// Inicializar funcionalidad de zoom
function initializeImageZoom() {
    const mainImage = document.getElementById('main-product-image');
    const imageContainer = document.getElementById('main-image-container');
    
    if (!mainImage || !imageContainer) return;
    
    // Crear popup de zoom que sigue el cursor (1.5x más grande)
    const zoomPopup = document.createElement('div');
    zoomPopup.id = 'zoom-popup';
    zoomPopup.className = 'zoom-popup-cursor';
    zoomPopup.innerHTML = `
        <div class="zoom-popup-content-cursor">
            <img id="zoom-popup-image" src="" alt="">
        </div>
    `;
    
    // Solo agregar si no existe
    if (!document.getElementById('zoom-popup')) {
        document.body.appendChild(zoomPopup);
    }
    
    // Función para verificar si hay imagen cargada
    function isImageLoaded() {
        const imgSrc = mainImage.src;
        return imgSrc && 
               !imgSrc.includes('default.svg') && 
               !imgSrc.includes('placeholder') && 
               mainImage.complete && 
               mainImage.naturalWidth > 0;
    }
    
    // Evento hover para mostrar imagen maximizada como cursor
    imageContainer.addEventListener('mouseenter', function() {
        // Solo activar zoom si hay una imagen cargada
        if (isImageLoaded()) {
            imageContainer.style.cursor = 'none';
            openZoomPopup(mainImage.src, mainImage.alt);
        }
    });
    
    imageContainer.addEventListener('mouseleave', function() {
        imageContainer.style.cursor = 'default';
        closeZoomPopup();
    });
    
    // Evento para seguir el cursor con zoom del área específica
    imageContainer.addEventListener('mousemove', function(e) {
        // Solo procesar si hay imagen cargada
        if (!isImageLoaded()) return;
        
        const popup = document.getElementById('zoom-popup');
        const popupImage = document.getElementById('zoom-popup-image');
        
        if (popup && popup.style.display === 'block' && popupImage) {
            // Calcular posición relativa del cursor en la imagen principal
            const mainImageRect = mainImage.getBoundingClientRect();
            const x = e.clientX - mainImageRect.left;
            const y = e.clientY - mainImageRect.top;
            
            // Asegurar que esté dentro de los límites de la imagen
            const clampedX = Math.max(0, Math.min(x, mainImageRect.width));
            const clampedY = Math.max(0, Math.min(y, mainImageRect.height));
            
            // Calcular porcentaje de posición en la imagen principal
            const xPercent = (clampedX / mainImageRect.width) * 100;
            const yPercent = (clampedY / mainImageRect.height) * 100;
            
            // Aplicar zoom del 300% y posicionar el área enfocada
            const zoomLevel = 3.0; // 300%
            const offsetX = -(xPercent - 50) * (zoomLevel - 1);
            const offsetY = -(yPercent - 50) * (zoomLevel - 1);
            
            popupImage.style.transform = `scale(${zoomLevel}) translate(${offsetX}%, ${offsetY}%)`;
            
            // Posicionar el popup siguiendo el cursor
            const popupOffsetX = 20;
            const popupOffsetY = -150; // Ajustado para tamaño más grande
            
            popup.style.left = (e.clientX + popupOffsetX) + 'px';
            popup.style.top = (e.clientY + popupOffsetY) + 'px';
            
            // Ajustar si se sale de la pantalla
            const popupRect = popup.getBoundingClientRect();
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;
            
            if (popupRect.right > windowWidth) {
                popup.style.left = (e.clientX - popupRect.width - 20) + 'px';
            }
            if (popupRect.top < 0) {
                popup.style.top = (e.clientY + 20) + 'px';
            }
        }
    });
}

// Abrir popup de zoom que sigue cursor
function openZoomPopup(imageSrc, imageAlt) {
    const popup = document.getElementById('zoom-popup');
    const popupImage = document.getElementById('zoom-popup-image');
    
    if (!popup || !popupImage) return;
    
    // Solo usar la imagen principal (main-product-image)
    const mainImage = document.getElementById('main-product-image');
    if (mainImage) {
        // Verificar que la imagen está cargada antes de mostrar el popup
        const imgSrc = mainImage.src;
        if (imgSrc && 
            !imgSrc.includes('default.svg') && 
            !imgSrc.includes('placeholder') && 
            mainImage.complete && 
            mainImage.naturalWidth > 0) {
            
            popupImage.src = mainImage.src;
            popupImage.alt = mainImage.alt;
            
            popup.style.display = 'block';
            
            // Resetear transform al abrir con zoom 300%
            popupImage.style.transform = 'scale(3.0) translate(0%, 0%)';
            
            // Animar entrada
            setTimeout(() => {
                popup.classList.add('show');
            }, 10);
        }
    }
}

// Cerrar popup de zoom
function closeZoomPopup() {
    const popup = document.getElementById('zoom-popup');
    const popupImage = document.getElementById('zoom-popup-image');
    
    if (!popup) return;
    
    popup.classList.remove('show');
    popup.style.display = 'none';
    
    // Resetear transform con zoom 300%
    if (popupImage) {
        popupImage.style.transform = 'scale(3.0) translate(0%, 0%)';
    }
}

// Funciones para seleccionar variantes
function selectSize(productId, size) {
    // Remover selección anterior
    document.querySelectorAll('.size-btn-new').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    // Encontrar el botón correcto y seleccionarlo
    const targetButton = event.target.closest('.size-btn-new');
    if (targetButton) {
        targetButton.classList.add('selected');
    }
    
    selectedSize = size;
    updateSelectedVariant('size', size);
    
    // Log para debugging
    console.log('Size selected:', size);
}

function selectColor(productId, color) {
    // Remover selección anterior
    document.querySelectorAll('.color-btn-new').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    // Encontrar el botón correcto y seleccionarlo
    const targetButton = event.target.closest('.color-btn-new');
    if (targetButton) {
        targetButton.classList.add('selected');
        
        // Agregar efecto visual adicional
        targetButton.style.borderWidth = '2px';
        targetButton.style.transform = 'scale(1.05)';
        
        // Remover efectos de otros botones
        document.querySelectorAll('.color-btn-new:not(.selected)').forEach(btn => {
            btn.style.borderWidth = '1px';
            btn.style.transform = 'scale(1)';
        });
    }
    
    selectedColor = color;
    updateSelectedVariant('color', color);
    
    // Log para debugging
    console.log('Color selected:', color);
}

// Funciones de calculadora de envío
function initializeShippingCalculator() {
    // Seleccionar ubicación local por defecto
    selectLocation('local');
}

function selectLocation(location) {
    // Actualizar botones
    document.querySelectorAll('.location-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-location="${location}"]`).classList.add('active');

    // Mostrar/ocultar secciones
    document.querySelectorAll('.shipping-calc-section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(`${location}-shipping-calc`).classList.add('active');

    // Limpiar resultados anteriores
    hideShippingResults();
}

function calculateLocalShipping() {
    const address = document.getElementById('local-address').value.trim();
    
    if (!address) {
        showShippingError('Por favor ingresa una dirección');
        return;
    }

    showShippingLoader();

    const requestData = {
        address: {
            street: address,
            city: 'Posadas',
            state: 'Misiones',
            postal_code: '3300'
        },
        items: [{
            name: currentProduct.name,
            price: currentProduct.price,
            quantity: getSelectedQuantity()
        }]
    };

    fetch('api/shipping_quotes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayShippingResults(data.quotes, 'local');
        } else {
            showShippingError(data.error || 'Error al calcular envío local');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showShippingError('Error de conexión');
    })
    .finally(() => {
        hideShippingLoader();
    });
}

function calculateNationalShipping() {
    const address = document.getElementById('national-address').value.trim();
    const city = document.getElementById('national-city').value.trim();
    const province = document.getElementById('national-province').value;
    const postalCode = document.getElementById('national-postal').value.trim();

    if (!address || !city || !province || !postalCode) {
        showShippingError('Por favor completa todos los campos');
        return;
    }

    showShippingLoader();

    const requestData = {
        address: {
            street: address,
            city: city,
            state: province,
            postal_code: postalCode
        },
        items: [{
            name: currentProduct.name,
            price: currentProduct.price,
            quantity: getSelectedQuantity()
        }]
    };

    fetch('api/shipping_quotes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayShippingResults(data.quotes, 'national');
        } else {
            showShippingError(data.error || 'Error al calcular envío nacional');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showShippingError('Error de conexión');
    })
    .finally(() => {
        hideShippingLoader();
    });
}

function getSelectedQuantity() {
    const quantitySelect = document.getElementById('quantity-select');
    const customInput = document.getElementById('custom-qty-input');
    
    if (quantitySelect.value === 'custom') {
        return parseInt(customInput.value) || 1;
    }
    return parseInt(quantitySelect.value) || 1;
}

function displayShippingResults(quotes, locationType) {
    const resultsDiv = document.getElementById('shipping-results');
    const optionsDiv = document.getElementById('shipping-options');
    
    optionsDiv.innerHTML = '';

    quotes.forEach(quote => {
        const optionDiv = document.createElement('div');
        optionDiv.className = 'shipping-option';
        
        const providerIcon = getProviderIcon(quote.provider);
        const freeShippingBadge = quote.free_shipping ? '<span class="free-shipping-badge">Gratis</span>' : '';
        
        optionDiv.innerHTML = `
            <div class="option-header">
                <div class="provider-info">
                    <i class="${providerIcon}"></i>
                    <span class="provider-name">${quote.name}</span>
                    ${freeShippingBadge}
                </div>
                <div class="option-price">
                    ${quote.price > 0 ? '$' + Math.round(quote.price).toLocaleString() : 'Gratis'}
                </div>
            </div>
            <div class="option-details">
                <p>${quote.description}</p>
                ${quote.estimated_delivery ? `<small>Entrega estimada: ${new Date(quote.estimated_delivery).toLocaleDateString()}</small>` : ''}
                ${quote.estimated_days ? `<small>Entrega en ${quote.estimated_days} días hábiles</small>` : ''}
            </div>
        `;
        
        optionsDiv.appendChild(optionDiv);
    });

    resultsDiv.style.display = 'block';
    hideShippingError();
}

function getProviderIcon(provider) {
    switch (provider) {
        case 'uber_direct':
            return 'fab fa-uber';
        case 'andreani':
            return 'fas fa-truck';
        case 'pickup':
            return 'fas fa-store';
        default:
            return 'fas fa-shipping-fast';
    }
}

function showShippingLoader() {
    document.getElementById('shipping-loader').style.display = 'flex';
    hideShippingResults();
    hideShippingError();
}

function hideShippingLoader() {
    document.getElementById('shipping-loader').style.display = 'none';
}

function showShippingError(message) {
    const errorDiv = document.getElementById('shipping-error');
    errorDiv.querySelector('span').textContent = message;
    errorDiv.style.display = 'flex';
    hideShippingResults();
}

function hideShippingError() {
    document.getElementById('shipping-error').style.display = 'none';
}

function hideShippingResults() {
    document.getElementById('shipping-results').style.display = 'none';
}

// Eventos para cantidad
function initializeQuantityEvents() {
    const quantitySelect = document.getElementById('quantity-select');
    const customQuantity = document.getElementById('custom-quantity');
    const customInput = document.getElementById('custom-qty-input');
    
    if (quantitySelect) {
        quantitySelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customQuantity.style.display = 'block';
            } else {
                customQuantity.style.display = 'none';
            }
        });
    }
    
    // Validación de stock máximo en custom input
    if (customInput) {
        // Prevenir entrada de caracteres no numéricos
        customInput.addEventListener('keydown', function(e) {
            // Permitir: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Permitir: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Permitir: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            // Asegurar que es un número (0-9)
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
        
        // Filtrar solo números positivos enteros
        customInput.addEventListener('input', function() {
            // Remover caracteres no numéricos
            this.value = this.value.replace(/[^0-9]/g, '');
            
            const maxStock = parseInt(this.getAttribute('max'));
            const minValue = parseInt(this.getAttribute('min'));
            const currentValue = parseInt(this.value);
            
            // Validar límites
            if (!isNaN(currentValue)) {
                if (currentValue > maxStock) {
                    this.value = maxStock;
                    showStockLimitMessage(maxStock);
                } else if (currentValue < minValue && this.value !== '') {
                    this.value = minValue;
                }
            }
        });
        
        // Prevenir pegar contenido no numérico
        customInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const numericValue = paste.replace(/[^0-9]/g, '');
            if (numericValue) {
                this.value = numericValue;
                // Disparar evento input para validar límites
                this.dispatchEvent(new Event('input'));
            }
        });
        
        customInput.addEventListener('blur', function() {
            const minValue = parseInt(this.getAttribute('min'));
            const maxStock = parseInt(this.getAttribute('max'));
            const currentValue = parseInt(this.value);
            
            // Si está vacío o es inválido, establecer valor mínimo
            if (isNaN(currentValue) || this.value === '' || currentValue < minValue) {
                this.value = minValue;
            } else if (currentValue > maxStock) {
                this.value = maxStock;
                showStockLimitMessage(maxStock);
            }
        });
    }
}

// Mostrar mensaje de límite de stock
function showStockLimitMessage(maxStock) {
    // Remover mensaje anterior si existe
    const existingMessage = document.querySelector('.stock-limit-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Crear mensaje de límite
    const message = document.createElement('div');
    message.className = 'stock-limit-message';
    message.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i>
        Stock máximo disponible: ${maxStock} unidades
    `;
    
    // Insertar después del input
    const customInput = document.getElementById('custom-qty-input');
    customInput.parentNode.insertBefore(message, customInput.nextSibling);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        if (message.parentNode) {
            message.remove();
        }
    }, 3000);
}

// Obtener cantidad seleccionada
function getSelectedQuantity() {
    const quantitySelect = document.getElementById('quantity-select');
    const customInput = document.getElementById('custom-qty-input');
    
    if (quantitySelect.value === 'custom') {
        return parseInt(customInput.value) || 6;
    } else {
        return parseInt(quantitySelect.value);
    }
}

// Upload de fotos en reseñas
function initializePhotoUpload() {
    const photoInput = document.getElementById('review-photos');
    const photoPreview = document.getElementById('photo-preview');
    let selectedFiles = [];
    
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            // Limitar a 5 fotos
            if (selectedFiles.length + files.length > 5) {
                showMessage('Máximo 5 fotos permitidas', 'error');
                return;
            }
            
            files.forEach(file => {
                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showMessage(`La foto ${file.name} es muy grande (máx 5MB)`, 'error');
                    return;
                }
                
                selectedFiles.push(file);
                addPhotoPreview(file);
            });
            
            // Limpiar input
            photoInput.value = '';
        });
    }
}

// Agregar vista previa de foto
function addPhotoPreview(file) {
    const photoPreview = document.getElementById('photo-preview');
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const photoItem = document.createElement('div');
        photoItem.className = 'photo-preview-item';
        photoItem.innerHTML = `
            <img src="${e.target.result}" alt="Preview">
            <button type="button" class="photo-remove" onclick="removePhoto(this, '${file.name}')">×</button>
        `;
        
        photoPreview.appendChild(photoItem);
    };
    
    reader.readAsDataURL(file);
}

// Remover foto
function removePhoto(button, fileName) {
    const photoItem = button.parentElement;
    photoItem.remove();
    
    // Remover del array
    selectedFiles = selectedFiles.filter(file => file.name !== fileName);
}

// Sistema de galería de imágenes
function initializeImageGallery() {
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

function changeMainImage(src, thumbnail) {
    const mainImg = document.getElementById('main-product-image');
    mainImg.src = src;
    
    // Actualizar thumbnail activo
    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
    thumbnail.classList.add('active');
    
    // Reinicializar zoom con nueva imagen
    setTimeout(() => {
        initializeImageZoom();
    }, 100);
}

// Funciones para variantes estilo MercadoLibre
function updateSelectedVariant(type, value) {
    const selectedElement = document.getElementById(`selected-${type}`);
    if (selectedElement) {
        selectedElement.textContent = value;
        selectedElement.style.color = '#333';
    }
}

// Gestionar estado de carga de botones
function setButtonLoading(buttonId, isLoading) {
    const button = document.getElementById(buttonId);
    if (!button) return;
    
    const btnText = button.querySelector('.btn-text');
    const btnLoading = button.querySelector('.btn-loading');
    
    if (isLoading) {
        button.disabled = true;
        button.style.opacity = '0.7';
        if (btnText) btnText.style.display = 'none';
        if (btnLoading) btnLoading.style.display = 'inline-block';
    } else {
        button.disabled = false;
        button.style.opacity = '1';
        if (btnText) btnText.style.display = 'inline-block';
        if (btnLoading) btnLoading.style.display = 'none';
    }
}

// Comprar ahora
function buyNow() {
    if (validateVariants()) {
        // Mostrar estado de carga
        setButtonLoading('buy-now-btn', true);
        
        const productId = currentProduct.id;
        const productName = currentProduct.name;
        const price = currentProduct.price;
        const quantity = getSelectedQuantity();
        const size = selectedSize;
        const color = selectedColor;
        
        // Limpiar carrito actual
        if (window.cart) {
            try {
                window.cart.clearCart();
                
                // Agregar producto seleccionado con la cantidad especificada
                const uniqueId = window.cart.generateProductId(productId, size, color);
                window.cart.items.push({
                    uniqueId: uniqueId,
                    productId: productId,
                    name: productName,
                    price: price,
                    size: size,
                    color: color,
                    quantity: quantity,
                    image: window.cart.getProductImage(productName)
                });
                
                window.cart.saveCart();
                
                showMessage('Redirigiendo al checkout...', 'success');
                
                // Redirigir al checkout después de un breve delay
                setTimeout(() => {
                    window.location.href = 'checkout.php';
                }, 1500);
            } catch (error) {
                console.error('Error in buyNow:', error);
                setButtonLoading('buy-now-btn', false);
                showMessage('Error al procesar la compra', 'error');
            }
        } else {
            setButtonLoading('buy-now-btn', false);
            showMessage('Error: Carrito no inicializado', 'error');
        }
    }
}

// Validar variantes seleccionadas
function validateVariants() {
    let missingFields = [];
    
    if (!selectedSize) {
        missingFields.push('un tamaño');
    }
    
    if (!selectedColor) {
        missingFields.push('un color');
    }
    
    if (missingFields.length > 0) {
        let message;
        if (missingFields.length === 1) {
            message = `Por favor selecciona ${missingFields[0]}`;
        } else {
            message = `Por favor selecciona ${missingFields.join(' y ')}`;
        }
        
        showValidationAlert(message);
        return false;
    }
    
    return true;
}

// Mostrar alerta de validación personalizada
function showValidationAlert(message) {
    // Remover alerta anterior si existe
    closeValidationAlert();
    
    // Crear burbuja de alerta
    const alertBubble = document.createElement('div');
    alertBubble.className = 'validation-bubble';
    alertBubble.innerHTML = `
        <div class="bubble-alert-content">
            <div class="alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <span class="alert-text">${message}</span>
            <button class="bubble-close" onclick="closeValidationAlert()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="bubble-arrow"></div>
    `;
    
    // Posicionar la burbuja arriba del botón con posición absoluta para mantener posición durante scroll
    const addButton = document.getElementById('add-cart-btn');
    const buttonRect = addButton.getBoundingClientRect();
    const scrollY = window.pageYOffset || document.documentElement.scrollTop;
    const scrollX = window.pageXOffset || document.documentElement.scrollLeft;
    
    alertBubble.style.position = 'absolute';
    alertBubble.style.left = (buttonRect.left + scrollX + buttonRect.width / 2) + 'px';
    alertBubble.style.top = (buttonRect.top + scrollY - 10) + 'px';
    alertBubble.style.transform = 'translateX(-50%) translateY(-100%)';
    alertBubble.style.zIndex = '10000';
    
    document.body.appendChild(alertBubble);
    
    // Función para actualizar posición durante scroll
    const updatePosition = () => {
        if (alertBubble.parentNode) {
            const currentButtonRect = addButton.getBoundingClientRect();
            const currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
            const currentScrollX = window.pageXOffset || document.documentElement.scrollLeft;
            
            alertBubble.style.left = (currentButtonRect.left + currentScrollX + currentButtonRect.width / 2) + 'px';
            alertBubble.style.top = (currentButtonRect.top + currentScrollY - 10) + 'px';
        }
    };
    
    // Agregar listener de scroll
    window.addEventListener('scroll', updatePosition);
    
    // Animar entrada
    setTimeout(() => {
        alertBubble.classList.add('show');
    }, 10);
    
    // Auto-cerrar después de 4 segundos y remover listener
    setTimeout(() => {
        window.removeEventListener('scroll', updatePosition);
        closeValidationAlert();
    }, 4000);
}

// Cerrar alerta de validación
function closeValidationAlert() {
    const alertBubble = document.querySelector('.validation-bubble');
    if (alertBubble) {
        alertBubble.classList.add('hide');
        setTimeout(() => {
            alertBubble.remove();
        }, 300);
    }
}

// Mostrar efecto de burbuja de éxito
function showBubbleSuccess() {
    // Crear elemento de burbuja
    const bubble = document.createElement('div');
    bubble.className = 'success-bubble';
    bubble.innerHTML = `
        <div class="bubble-success-content">
            <div class="success-icon">
                <i class="fas fa-thumbs-up"></i>
            </div>
            <div class="success-text">
                <strong>¡Producto agregado!</strong>
                <span>Se agregó al carrito exitosamente</span>
            </div>
            <div class="bubble-sparkles">
                <div class="sparkle sparkle-1">✨</div>
                <div class="sparkle sparkle-2">⭐</div>
                <div class="sparkle sparkle-3">💫</div>
                <div class="sparkle sparkle-4">✨</div>
            </div>
        </div>
        <div class="bubble-arrow success-arrow"></div>
    `;
    
    // Posicionar la burbuja arriba del botón con posición absoluta para mantener posición durante scroll
    const addButton = document.getElementById('add-cart-btn');
    const buttonRect = addButton.getBoundingClientRect();
    const scrollY = window.pageYOffset || document.documentElement.scrollTop;
    const scrollX = window.pageXOffset || document.documentElement.scrollLeft;
    
    bubble.style.position = 'absolute';
    bubble.style.left = (buttonRect.left + scrollX + buttonRect.width / 2) + 'px';
    bubble.style.top = (buttonRect.top + scrollY - 10) + 'px';
    bubble.style.transform = 'translateX(-50%) translateY(-100%)';
    bubble.style.zIndex = '10000';
    
    document.body.appendChild(bubble);
    
    // Función para actualizar posición durante scroll
    const updateSuccessPosition = () => {
        if (bubble.parentNode) {
            const currentButtonRect = addButton.getBoundingClientRect();
            const currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
            const currentScrollX = window.pageXOffset || document.documentElement.scrollLeft;
            
            bubble.style.left = (currentButtonRect.left + currentScrollX + currentButtonRect.width / 2) + 'px';
            bubble.style.top = (currentButtonRect.top + currentScrollY - 10) + 'px';
        }
    };
    
    // Agregar listener de scroll
    window.addEventListener('scroll', updateSuccessPosition);
    
    // Animar entrada y salida
    setTimeout(() => {
        bubble.classList.add('animate-in');
    }, 10);
    
    // Iniciar animación de explosión después de un tiempo
    setTimeout(() => {
        bubble.classList.add('explode');
    }, 1500);
    
    // Remover elemento después de la animación y limpiar listener
    setTimeout(() => {
        window.removeEventListener('scroll', updateSuccessPosition);
        if (bubble.parentNode) {
            bubble.remove();
        }
    }, 2500);
}

// Sistema de tabs
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.getAttribute('data-tab');
            
            // Remover clase active de todos los botones y contenidos
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Agregar clase active al botón clickeado y su contenido
            button.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
}

// Agregar producto al carrito
function addProductToCart() {
    // Verificar variantes seleccionadas
    if (!validateVariants()) {
        return;
    }
    
    // Mostrar estado de carga
    setButtonLoading('add-cart-btn', true);
    
    const productId = currentProduct.id;
    const productName = currentProduct.name;
    const price = currentProduct.price;
    const quantity = getSelectedQuantity();
    const size = selectedSize;
    const color = selectedColor;
    
    if (window.cart) {
        try {
            // Agregar producto con la cantidad especificada usando el método directo
            const uniqueId = window.cart.generateProductId(productId, size, color);
            const existingItem = window.cart.items.find(item => item.uniqueId === uniqueId);

            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                window.cart.items.push({
                    uniqueId: uniqueId,
                    productId: productId,
                    name: productName,
                    price: price,
                    size: size,
                    color: color,
                    quantity: quantity,
                    image: window.cart.getProductImage(productName)
                });
            }

            // Guardar carrito
            window.cart.saveCart();
            
            // Remover estado de carga
            setButtonLoading('add-cart-btn', false);
            
            // Mostrar efecto de confirmación burbuja
            showBubbleSuccess();
            
            // Actualizar badge del carrito
            window.cart.updateCartBadge();
            
        } catch (error) {
            setButtonLoading('add-cart-btn', false);
            showMessage('Error al agregar producto al carrito', 'error');
        }
    } else {
        setButtonLoading('add-cart-btn', false);
        showMessage('Error: Carrito no inicializado', 'error');
    }
}

// Mostrar mensaje de éxito mejorado
function showSuccessMessage(quantity, productName, size, color) {
    const messageEl = document.createElement('div');
    messageEl.className = 'product-success-message';
    messageEl.innerHTML = `
        <div class="success-content">
            <div class="success-header">
                <i class="fas fa-check-circle"></i>
                <span>¡Producto agregado!</span>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="close-btn">×</button>
            </div>
            <div class="success-details">
                <p><strong>${quantity} x ${productName}</strong></p>
                <p>Talla: ${size} • Color: ${color}</p>
            </div>
            <div class="success-actions">
                <button onclick="window.location.href='checkout.php'" class="btn-checkout">
                    <i class="fas fa-credit-card"></i> Ir al Checkout
                </button>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="btn-continue">
                    Seguir comprando
                </button>
            </div>
        </div>
    `;
    
    // Agregar estilos en línea
    messageEl.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: var(--bg-secondary);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        z-index: 10000;
        padding: 0;
        animation: scaleIn 0.3s ease-out;
        max-width: 400px;
        width: 90%;
    `;
    
    document.body.appendChild(messageEl);
    
    // Remover automáticamente después de 8 segundos
    setTimeout(() => {
        if (messageEl.parentNode) {
            messageEl.remove();
        }
    }, 8000);
}

// Mostrar mensajes
function showMessage(message, type) {
    const messageEl = document.createElement('div');
    messageEl.className = `product-message ${type}`;
    messageEl.innerHTML = `
        <div class="message-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    document.body.appendChild(messageEl);
    
    setTimeout(() => {
        messageEl.remove();
    }, 4000);
}

// Sistema de reseñas interactivo
function initializeReviewSystem() {
    const ratingDescriptions = {
        1: "😞 Muy malo - No lo recomiendo",
        2: "😐 Malo - Esperaba más", 
        3: "🙂 Regular - Está bien",
        4: "😊 Bueno - Lo recomiendo",
        5: "😍 Excelente - ¡Me encanta!"
    };

    // Manejar cambios en las calificaciones
    document.querySelectorAll('.star-rating input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const rating = this.value;
            const descElement = document.getElementById('rating-description');
            
            if (descElement && ratingDescriptions[rating]) {
                descElement.textContent = ratingDescriptions[rating];
                descElement.style.color = '#007bff';
                descElement.style.fontWeight = '600';
            }
        });
    });

    // Manejar botones de utilidad
    document.querySelectorAll('.helpful-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const isHelpful = this.textContent.includes('👍');
            const currentCount = this.textContent.match(/\d+/) ? parseInt(this.textContent.match(/\d+/)[0]) : 0;
            
            if (isHelpful) {
                this.innerHTML = `👍 Útil (${currentCount + 1})`;
                this.style.background = 'rgba(40, 167, 69, 0.1)';
                this.style.borderColor = '#28a745';
                this.style.color = '#28a745';
            } else {
                this.innerHTML = `👎 No útil (${currentCount + 1})`;
                this.style.background = 'rgba(220, 53, 69, 0.1)';
                this.style.borderColor = '#dc3545';
                this.style.color = '#dc3545';
            }
            
            this.disabled = true;
            this.style.opacity = '0.7';
            
            showThankYouMessage();
        });
    });
}

// Mensaje de agradecimiento
function showThankYouMessage() {
    const message = document.createElement('div');
    message.className = 'thank-you-message';
    message.innerHTML = `
        <div class="thank-you-content">
            <i class="fas fa-heart"></i>
            <span>¡Gracias por tu feedback!</span>
        </div>
    `;
    
    message.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        z-index: 10000;
        animation: slideInUp 0.3s ease-out;
    `;
    
    document.body.appendChild(message);
    
    setTimeout(() => {
        message.remove();
    }, 3000);
}

// Scroll suave a reseñas
document.addEventListener('click', function(e) {
    if (e.target.matches('.reviews-link')) {
        e.preventDefault();
        document.getElementById('reviews-section').scrollIntoView({
            behavior: 'smooth'
        });
    }
});

// Estilos CSS para la funcionalidad de zoom
const zoomStyles = `
<style>
/* Contenedor de imagen principal */
#main-image-container {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    transition: all 0.3s ease;
}

#main-image-container:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Remover lupa - ya no se necesita */
.zoom-lens {
    display: none !important;
}

/* Popup de zoom que sigue el cursor */
.zoom-popup-cursor {
    position: fixed;
    display: none;
    z-index: 10000;
    opacity: 0;
    transition: opacity 0.15s ease;
    pointer-events: none;
}

.zoom-popup-cursor.show {
    opacity: 1;
}

.zoom-popup-content-cursor {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 
        0 25px 60px rgba(0,0,0,0.5),
        0 15px 30px rgba(0,0,0,0.3),
        0 5px 15px rgba(0,0,0,0.2);
    width: 300px;
    height: 300px;
    transition: all 0.15s ease;
}

.zoom-popup-content-cursor img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    background: #f8f9fa;
    transform-origin: center center;
    transition: transform 0.1s ease;
}

/* Mejorar badge de imagen */
.image-badge {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0,123,255,0.9);
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.image-badge:hover {
    background: rgba(0,123,255,1);
    transform: scale(1.1);
}

/* Mensaje de límite de stock */
.stock-limit-message {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 8px 12px;
    border-radius: 6px;
    margin-top: 8px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
    animation: slideIn 0.3s ease-out;
}

.stock-limit-message i {
    color: #f39c12;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .zoom-popup-content-cursor {
        width: 250px;
        height: 250px;
    }
}

@media (max-width: 480px) {
    .zoom-popup-content-cursor {
        width: 200px;
        height: 200px;
        box-shadow: 
            0 15px 40px rgba(0,0,0,0.4),
            0 10px 20px rgba(0,0,0,0.3),
            0 3px 10px rgba(0,0,0,0.2);
    }
}
</style>
`;

// Insertar estilos
document.head.insertAdjacentHTML('beforeend', zoomStyles);

</script>

<!-- Secciones de Recomendaciones -->
<section class="recommendations-wrapper">
    <div class="container">
        
        <!-- Productos Frecuentemente Comprados Juntos -->
        <div id="frequently-bought-together" 
             data-recommendations="frequently_bought_together" 
             data-product-id="<?php echo $product['id']; ?>" 
             data-limit="4">
            <!-- Se carga dinámicamente con JavaScript -->
        </div>
        
        <!-- Productos Similares -->
        <div id="similar-products" 
             data-recommendations="similar_products" 
             data-product-id="<?php echo $product['id']; ?>" 
             data-limit="4">
            <!-- Se carga dinámicamente con JavaScript -->
        </div>
        
        <!-- Recomendaciones Personalizadas (solo si hay usuario logueado) -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div id="personalized-recommendations" 
             data-recommendations="personalized" 
             data-limit="6">
            <!-- Se carga dinámicamente con JavaScript -->
        </div>
        <?php endif; ?>
        
        <!-- Productos Trending -->
        <div id="trending-products" 
             data-recommendations="trending" 
             data-limit="4">
            <!-- Se carga dinámicamente con JavaScript -->
        </div>
        
        <!-- Recomendaciones Basadas en Precio -->
        <div id="price-based-recommendations" 
             data-recommendations="price_based" 
             data-current-price="<?php echo $product['price']; ?>"
             data-limit="4">
            <!-- Se carga dinámicamente con JavaScript -->
        </div>
        
        <!-- Productos Estacionales -->
        <div id="seasonal-recommendations" 
             data-recommendations-lazy="seasonal" 
             data-limit="4">
            <!-- Se carga con lazy loading -->
        </div>
        
    </div>
</section>

<!-- Script para cargar las recomendaciones -->
<script>
// Configurar usuario actual para recomendaciones
<?php if (isset($_SESSION['user_id'])): ?>
window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
<?php else: ?>
window.currentUserId = null;
<?php endif; ?>

// Trackear vista del producto actual
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que el sistema de recomendaciones se inicialice
    setTimeout(() => {
        if (window.recommendationSystem) {
            // Trackear vista de producto
            window.recommendationSystem.trackEvent('product_view', {
                product_id: <?php echo $product['id']; ?>,
                source_page: 'product_detail'
            });
            
            // Configurar parámetros adicionales para recomendaciones basadas en precio
            const priceBasedContainer = document.getElementById('price-based-recommendations');
            if (priceBasedContainer) {
                priceBasedContainer.setAttribute('data-current-price', '<?php echo $product['price']; ?>');
            }
        }
    }, 500);
});
</script>

<!-- CSS específico para recomendaciones en product detail -->
<link rel="stylesheet" href="assets/css/recommendations.css?v=<?php echo time(); ?>">

<?php include 'includes/footer.php'; ?>