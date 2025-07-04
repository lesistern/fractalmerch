<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

// Iniciar sesión para manejar mensajes de feedback
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Tienda - Productos Personalizables';
include 'includes/header.php';

// --- Lógica para obtener productos y reseñas ---
try {
    // Obtener todos los productos
    $stmt_products = $pdo->query(
        "SELECT p.*, AVG(pr.rating) as avg_rating, COUNT(pr.id) as review_count
         FROM products p
         LEFT JOIN product_reviews pr ON p.id = pr.product_id
         GROUP BY p.id"
    );
    $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todas las reseñas y agruparlas por product_id
    $stmt_reviews = $pdo->query(
        "SELECT pr.*, u.username 
         FROM product_reviews pr
         JOIN users u ON pr.user_id = u.id
         ORDER BY pr.created_at DESC"
    );
    $all_reviews_raw = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);
    $all_reviews = [];
    foreach ($all_reviews_raw as $review) {
        $all_reviews[$review['product_id']][] = $review;
    }

} catch (PDOException $e) {
    // Fallback to predefined products if database is not available
    error_log("Database error in particulares.php: " . $e->getMessage());
    $products = [
        ['id' => 1, 'name' => 'Remera Personalizada', 'price' => 5999, 'description' => 'Remera 100% algodón de alta calidad', 'avg_rating' => 4.5, 'review_count' => 12, 'main_image_url' => 'assets/images/remera-default.jpg'],
        ['id' => 2, 'name' => 'Buzo Personalizado', 'price' => 12999, 'description' => 'Buzo con capucha, ideal para el invierno', 'avg_rating' => 4.8, 'review_count' => 8, 'main_image_url' => 'assets/images/buzo-default.jpg'],
        ['id' => 3, 'name' => 'Taza Personalizada', 'price' => 3499, 'description' => 'Taza de cerámica de alta calidad', 'avg_rating' => 4.3, 'review_count' => 15, 'main_image_url' => 'assets/images/taza-default.jpg'],
        ['id' => 4, 'name' => 'Mouse Pad Personalizado', 'price' => 2999, 'description' => 'Mouse pad con base antideslizante', 'avg_rating' => 4.6, 'review_count' => 6, 'main_image_url' => 'assets/images/mousepad-default.jpg'],
        ['id' => 5, 'name' => 'Funda Personalizada', 'price' => 4999, 'description' => 'Funda para celular resistente', 'avg_rating' => 4.4, 'review_count' => 10, 'main_image_url' => 'assets/images/funda-default.jpg'],
        ['id' => 6, 'name' => 'Almohada Personalizada', 'price' => 6999, 'description' => 'Almohada suave y cómoda', 'avg_rating' => 4.7, 'review_count' => 5, 'main_image_url' => 'assets/images/almohada-default.jpg']
    ];
    $all_reviews = [];
}

// Función para renderizar estrellas de calificación
function render_stars($rating) {
    $rating = round($rating * 2) / 2; // Redondear a 0.5
    $output = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $output .= '<i class="fas fa-star"></i>'; // Estrella completa
        } elseif ($rating >= $i - 0.5) {
            $output .= '<i class="fas fa-star-half-alt"></i>'; // Media estrella
        } else {
            $output .= '<i class="far fa-star"></i>'; // Estrella vacía
        }
    }
    return $output;
}

// Definir variantes de productos
$product_variants = [
    1 => [ // Remeras
        'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
        'colors' => ['Blanco', 'Negro', 'Gris', 'Azul Navy', 'Rojo']
    ],
    2 => [ // Buzos
        'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
        'colors' => ['Negro', 'Gris', 'Azul Navy', 'Bordo']
    ],
    3 => [ // Tazas
        'sizes' => ['300ml', '350ml', '400ml'],
        'colors' => ['Blanco', 'Negro', 'Azul', 'Rojo']
    ],
    4 => [ // Mouse Pads
        'sizes' => ['Estándar', 'Grande', 'XL'],
        'colors' => ['Negro', 'Azul', 'Gris']
    ],
    5 => [ // Fundas
        'sizes' => ['iPhone', 'Samsung', 'Universal'],
        'colors' => ['Transparente', 'Negro', 'Azul', 'Rosa']
    ],
    6 => [ // Almohadas
        'sizes' => ['40x40cm', '50x50cm', '60x60cm'],
        'colors' => ['Blanco', 'Crema', 'Gris']
    ]
];
?>

<section class="shop-hero-compact">
    <div class="container">
        <div class="shop-header-jovial">
            <h1>🛍️ ¡Tienda Genial!</h1>
            <p>✨ Productos únicos que amarás ✨</p>
        </div>
    </div>
</section>

<section class="shop-products">
    <div class="container">
        <h2>🌟 ¡Los Más Cool!</h2>
        <p class="section-subtitle">💯 Diseños que marcan tendencia</p>
        
        <!-- Mensajes de feedback (éxito o error) -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="products-grid">
            <?php if (empty($products)): ?>
                <p>No hay productos disponibles en este momento.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image-compact">
                            <div class="image-badge-fun">🔥</div>
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                <img src="assets/images/products/<?php echo strtolower(str_replace(' ', '', explode(' ', $product['name'])[0])); ?>.svg" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='assets/images/products/default.svg'">
                            </a>
                        </div>
                        <h3 class="product-title-compact">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-title-link">
                                <?php echo htmlspecialchars($product['name']); ?> 🎨
                            </a>
                        </h3>
                        
                        <!-- Calificación con estrellas -->
                        <div class="product-rating">
                            <?php echo render_stars($product['avg_rating']); ?>
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>#reviews-section" class="reviews-count">
                                (<?php echo $product['review_count']; ?> reseñas)
                            </a>
                        </div>

                        <div class="product-price-compact">
                            <span class="price-current">💸 $<?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                        </div>
                        
                        <p class="product-description-compact">
                            <?php echo substr(htmlspecialchars($product['description']), 0, 60) . '...'; ?>
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="read-more-fun">👀 Ver más</a>
                        </p>

                        <!-- Variantes del producto - Compactas -->
                        <div class="product-variants-compact" id="variants-<?php echo $product['id']; ?>">
                            <?php if (isset($product_variants[$product['id']])): ?>
                                <div class="variant-group-compact">
                                    <select name="size-<?php echo $product['id']; ?>" class="variant-select-compact" required>
                                        <option value="">Talla</option>
                                        <?php foreach ($product_variants[$product['id']]['sizes'] as $size): ?>
                                            <option value="<?php echo $size; ?>"><?php echo $size; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="variant-group-compact">
                                    <select name="color-<?php echo $product['id']; ?>" class="variant-select-compact" required>
                                        <option value="">Color</option>
                                        <?php foreach ($product_variants[$product['id']]['colors'] as $color): ?>
                                            <option value="<?php echo $color; ?>"><?php echo $color; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <a href="customize-shirt.php?product_id=<?php echo $product['id']; ?>" class="btn-customize-compact">
                                🎨 Personalizar
                            </a>
                            <button class="btn-cart-small" 
                                    onclick="addToCartWithVariants(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>)"
                                    aria-label="Agregar <?php echo htmlspecialchars($product['name']); ?> al carrito">
                                🛒 Agregar
                            </button>
                        </div>

                        <!-- Rating compacto -->
                        <div class="rating-compact">
                            <div class="stars-compact">
                                <?php echo render_stars($product['avg_rating']); ?>
                            </div>
                            <span class="rating-compact-text"><?php echo number_format($product['avg_rating'], 1); ?> (<?php echo $product['review_count']; ?>)</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Modal del Carrito -->
<div id="cartModal" class="cart-modal" style="display: none;">
    <div class="cart-modal-content">
        <div class="cart-modal-header">
            <h2><i class="fas fa-shopping-cart"></i> Tu Carrito</h2>
            <button class="cart-modal-close" onclick="closeCartModal()">&times;</button>
        </div>
        <div class="cart-modal-body" id="cartModalBody">
            <!-- El contenido se llenará dinámicamente -->
        </div>
    </div>
</div>

<!-- Modal de Checkout -->
<div id="checkoutModal" class="checkout-modal" style="display: none;">
    <div class="checkout-modal-content">
        <div class="checkout-modal-header">
            <h2><i class="fas fa-credit-card"></i> Finalizar Compra</h2>
            <button class="checkout-modal-close" onclick="closeCheckoutModal()">&times;</button>
        </div>
        <div class="checkout-modal-body" id="checkoutModalBody">
            <!-- El contenido se llenará dinámicamente -->
        </div>
    </div>
</div>

<section class="shop-info">
    <div class="container">
        <div class="info-grid">
            <div class="info-card">
                <i class="fas fa-shipping-fast"></i>
                <h3>Envío Gratis</h3>
                <p>En compras superiores a $10.000</p>
            </div>
            <div class="info-card">
                <i class="fas fa-undo"></i>
                <h3>Devoluciones</h3>
                <p>30 días para cambios y devoluciones</p>
            </div>
            <div class="info-card">
                <i class="fas fa-headset"></i>
                <h3>Soporte 24/7</h3>
                <p>Atención al cliente las 24 horas</p>
            </div>
            <div class="info-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Compra Segura</h3>
                <p>Tus datos están protegidos</p>
            </div>
        </div>
    </div>
</section>

<!-- Enhanced cart ya está cargado en header.php -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para el acordeón de reseñas con accesibilidad mejorada
    document.querySelectorAll('.accordion-toggle, .accordion-toggle-compact').forEach(button => {
        button.addEventListener('click', () => {
            const accordionContent = button.nextElementSibling;
            const isExpanded = button.getAttribute('aria-expanded') === 'true';
            
            // Toggle estado
            button.setAttribute('aria-expanded', !isExpanded);
            button.classList.toggle('active');
            
            if (!isExpanded) {
                accordionContent.style.maxHeight = accordionContent.scrollHeight + "px";
                button.querySelector('i').classList.replace('fa-chevron-down', 'fa-chevron-up');
                // Anunciar cambio para lectores de pantalla
                button.setAttribute('aria-label', button.getAttribute('aria-label').replace('Ver', 'Ocultar'));
            } else {
                accordionContent.style.maxHeight = 0;
                button.querySelector('i').classList.replace('fa-chevron-up', 'fa-chevron-down');
                button.setAttribute('aria-label', button.getAttribute('aria-label').replace('Ocultar', 'Ver'));
            }
        });
        
        // Soporte para navegación por teclado
        button.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                button.click();
            }
        });
    });

    // Carrito ya inicializado globalmente en header.php

    // Mejorar interactividad de reseñas
    initializeReviewSystem();
});

// Sistema de reseñas interactivo
function initializeReviewSystem() {
    // Agregar descripciones dinámicas para calificaciones
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
            const productId = this.name.split('-')[1];
            const rating = this.value;
            const descElement = document.getElementById(`rating-desc-${productId}`);
            
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
            
            // Simular incremento
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
            
            // Deshabilitar el botón
            this.disabled = true;
            this.style.opacity = '0.7';
            
            // Mostrar mensaje de agradecimiento
            showThankYouMessage();
        });
    });
}

// Mostrar mensaje de agradecimiento por feedback
function showThankYouMessage() {
    const message = document.createElement('div');
    message.className = 'thank-you-message';
    message.innerHTML = `
        <div class="thank-you-content">
            <i class="fas fa-heart"></i>
            <span>¡Gracias por tu feedback!</span>
        </div>
    `;
    
    // Agregar estilos inline
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

// Función para agregar productos con variantes (actualizada para selects)
function addToCartWithVariants(productId, productName, price) {
    const sizeSelect = document.querySelector(`select[name="size-${productId}"]`);
    const colorSelect = document.querySelector(`select[name="color-${productId}"]`);
    
    if (!sizeSelect || !sizeSelect.value) {
        alert('Por favor selecciona un tamaño');
        return;
    }
    
    if (!colorSelect || !colorSelect.value) {
        alert('Por favor selecciona un color');
        return;
    }
    
    const size = sizeSelect.value;
    const color = colorSelect.value;
    
    if (window.cart) {
        window.cart.addProduct(productId, productName, price, size, color);
    }
}

// Función para mostrar el modal del carrito
function showCartModal() {
    if (window.cart) {
        window.cart.showCartModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>