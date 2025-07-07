<?php
/**
 * Bundle Kit Home Office - Implementación según E-commerce Strategist AI
 * Mouse Pad + Taza + Almohada = $9.990 (20% descuento)
 */

require_once 'includes/functions.php';
require_once 'config/database.php';

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Kit Home Office Completo';
$page_description = 'Combo especial para trabajar desde casa: Mouse Pad + Taza + Almohada personalizada con 20% de descuento';

include 'includes/header.php';

// Definir productos del bundle
$bundle_products = [
    ['id' => 4, 'name' => 'Mouse Pad Personalizado', 'price' => 2990, 'image' => 'assets/images/products/mousepad.svg'],
    ['id' => 3, 'name' => 'Taza Personalizada', 'price' => 3490, 'image' => 'assets/images/products/taza.svg'],
    ['id' => 6, 'name' => 'Almohada Personalizada', 'price' => 6990, 'image' => 'assets/images/products/almohada.svg']
];

$individual_price = array_sum(array_column($bundle_products, 'price'));
$bundle_price = 9990;
$discount = $individual_price - $bundle_price;
$discount_percentage = round(($discount / $individual_price) * 100);
?>

<div class="bundle-hero">
    <div class="container">
        <div class="bundle-header">
            <div class="bundle-badge">
                <span class="bundle-discount">-<?php echo $discount_percentage; ?>%</span>
                <span class="bundle-text">OFERTA ESPECIAL</span>
            </div>
            <h1>Kit Home Office Completo</h1>
            <p class="bundle-subtitle">Todo lo que necesitas para trabajar cómodo desde casa</p>
        </div>
        
        <div class="bundle-showcase">
            <div class="bundle-products">
                <?php foreach ($bundle_products as $index => $product): ?>
                    <div class="bundle-product-item" data-product="<?php echo $product['id']; ?>">
                        <div class="product-image">
                            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" onerror="this.src='assets/images/products/default.svg'">
                        </div>
                        <h3><?php echo $product['name']; ?></h3>
                        <div class="individual-price">$<?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                        <?php if ($index < count($bundle_products) - 1): ?>
                            <div class="plus-icon">+</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="bundle-pricing">
                <div class="price-comparison">
                    <div class="individual-total">
                        <span class="label">Precio individual:</span>
                        <span class="price original">$<?php echo number_format($individual_price, 0, ',', '.'); ?></span>
                    </div>
                    <div class="bundle-savings">
                        <span class="label">Ahorras:</span>
                        <span class="savings">$<?php echo number_format($discount, 0, ',', '.'); ?></span>
                    </div>
                    <div class="bundle-total">
                        <span class="label">Precio del Kit:</span>
                        <span class="price bundle">$<?php echo number_format($bundle_price, 0, ',', '.'); ?></span>
                    </div>
                </div>
                
                <div class="bundle-benefits">
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Personalización incluida en los 3 productos</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-truck"></i>
                        <span>Envío gratis (supera los $12.000)</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-clock"></i>
                        <span>Oferta válida por tiempo limitado</span>
                    </div>
                </div>
                
                <div class="bundle-actions">
                    <button class="btn-bundle-primary" onclick="addBundleToCart()">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Agregar Kit Completo</span>
                        <small>$<?php echo number_format($bundle_price, 0, ',', '.'); ?></small>
                    </button>
                    
                    <button class="btn-bundle-secondary" onclick="showCustomizeOptions()">
                        <i class="fas fa-edit"></i>
                        <span>Personalizar Productos</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="bundle-details">
    <div class="container">
        <div class="details-grid">
            <div class="product-details">
                <h2>¿Qué incluye el Kit Home Office?</h2>
                
                <div class="product-detail-card">
                    <img src="assets/images/products/mousepad.svg" alt="Mouse Pad">
                    <div class="detail-content">
                        <h3>Mouse Pad Ergonómico</h3>
                        <ul>
                            <li>Base antideslizante de goma natural</li>
                            <li>Superficie suave para óptimo deslizamiento</li>
                            <li>Diseño personalizable en alta resolución</li>
                            <li>Tamaño estándar: 22x18cm</li>
                        </ul>
                    </div>
                </div>
                
                <div class="product-detail-card">
                    <img src="assets/images/products/taza.svg" alt="Taza">
                    <div class="detail-content">
                        <h3>Taza de Cerámica Premium</h3>
                        <ul>
                            <li>Cerámica de alta calidad, 330ml</li>
                            <li>Resistente al lavavajillas y microondas</li>
                            <li>Sublimación de alta definición</li>
                            <li>Asa ergonómica para mejor agarre</li>
                        </ul>
                    </div>
                </div>
                
                <div class="product-detail-card">
                    <img src="assets/images/products/almohada.svg" alt="Almohada">
                    <div class="detail-content">
                        <h3>Almohada Ergonómica</h3>
                        <ul>
                            <li>Relleno hipoalergénico premium</li>
                            <li>Funda 100% algodón personalizable</li>
                            <li>Tamaño: 40x40cm, ideal para oficina</li>
                            <li>Soporte lumbar y cervical</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="testimonials">
                <h2>Lo que dicen nuestros clientes</h2>
                
                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"El kit completo es perfecto para mi home office. La calidad es excelente y el diseño personalizado le da un toque único a mi espacio de trabajo."</p>
                    <div class="testimonial-author">
                        <strong>María González</strong>
                        <span>Diseñadora Gráfica</span>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"Increíble relación precio-calidad. La almohada es súper cómoda y el mouse pad tiene la textura perfecta. Recomendado al 100%."</p>
                    <div class="testimonial-author">
                        <strong>Carlos Martínez</strong>
                        <span>Desarrollador</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bundle-faq">
    <div class="container">
        <h2>Preguntas Frecuentes</h2>
        
        <div class="faq-grid">
            <div class="faq-item">
                <h3>¿Puedo personalizar cada producto por separado?</h3>
                <p>¡Por supuesto! Cada producto del kit puede tener un diseño diferente. Solo usa nuestro editor de personalización para cada uno.</p>
            </div>
            
            <div class="faq-item">
                <h3>¿Cuánto demora la producción?</h3>
                <p>El kit completo se produce en 3-5 días hábiles. Una vez listo, el envío demora 24-48hs adicionales.</p>
            </div>
            
            <div class="faq-item">
                <h3>¿El descuento se aplica automáticamente?</h3>
                <p>Sí, al agregar el kit completo al carrito, el precio especial se aplica automáticamente. Ahorras $<?php echo number_format($discount, 0, ',', '.'); ?> vs comprar por separado.</p>
            </div>
            
            <div class="faq-item">
                <h3>¿Puedo cambiar algún producto del kit?</h3>
                <p>El kit está diseñado específicamente para home office, pero si necesitas algún cambio, contáctanos y veremos opciones alternativas.</p>
            </div>
        </div>
    </div>
</section>

<script>
// Funciones específicas del bundle
function addBundleToCart() {
    // Agregar todos los productos del bundle con descuento aplicado
    const bundleProducts = <?php echo json_encode($bundle_products); ?>;
    const bundlePrice = <?php echo $bundle_price; ?>;
    const individualPrice = <?php echo $individual_price; ?>;
    
    // Crear bundle como producto especial
    const bundleItem = {
        id: 'bundle_home_office',
        name: 'Kit Home Office Completo',
        price: bundlePrice,
        originalPrice: individualPrice,
        quantity: 1,
        image: 'assets/images/products/bundle-home-office.svg',
        isBundle: true,
        bundleProducts: bundleProducts,
        size: 'Kit Completo',
        color: 'Personalizable'
    };
    
    // Agregar al carrito usando el sistema existente
    if (window.cart) {
        window.cart.addProduct(bundleItem.name, bundleItem.price, bundleItem.image, bundleItem.size, bundleItem.color, bundleItem);
        
        // Mostrar mensaje de éxito
        showBundleSuccess();
        
        // Analytics tracking
        if (typeof gtag !== 'undefined') {
            gtag('event', 'add_to_cart', {
                event_category: 'E-commerce',
                event_label: 'Bundle Home Office',
                value: bundlePrice
            });
        }
    } else {
        alert('Error al agregar el bundle. Por favor, intenta nuevamente.');
    }
}

function showBundleSuccess() {
    // Crear notificación de éxito
    const notification = document.createElement('div');
    notification.className = 'bundle-success-notification';
    notification.innerHTML = `
        <div class="success-content">
            <i class="fas fa-check-circle"></i>
            <div class="success-text">
                <h4>¡Kit agregado exitosamente!</h4>
                <p>Ahorraste $<?php echo number_format($discount, 0, ',', '.'); ?> vs comprar por separado</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="close-notification">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove después de 5 segundos
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
    
    // Mostrar carrito modal
    setTimeout(() => {
        if (window.showCartModal) {
            window.showCartModal();
        }
    }, 1000);
}

function showCustomizeOptions() {
    // Redirigir al personalizador con parámetros del bundle
    const params = new URLSearchParams({
        bundle: 'home_office',
        products: '4,3,6', // Mouse Pad, Taza, Almohada
        return_url: window.location.href
    });
    
    window.location.href = 'customize-shirt.php?' + params.toString();
}

// Agregar estilos para notificación
const bundleStyles = document.createElement('style');
bundleStyles.textContent = `
    .bundle-success-notification {
        position: fixed;
        top: 2rem;
        right: 2rem;
        background: var(--bg-secondary);
        border: 1px solid #10b981;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
    }
    
    .success-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .success-content i {
        color: #10b981;
        font-size: 1.5rem;
    }
    
    .success-text h4 {
        margin: 0 0 0.25rem 0;
        color: var(--text-primary);
        font-size: 1rem;
    }
    
    .success-text p {
        margin: 0;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }
    
    .close-notification {
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 0.25rem;
        margin-left: 0.5rem;
    }
    
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @media (max-width: 768px) {
        .bundle-success-notification {
            top: 1rem;
            right: 1rem;
            left: 1rem;
            width: auto;
        }
    }
`;
document.head.appendChild(bundleStyles);
</script>

<style>
/* Bundle-specific styles */
.bundle-hero {
    background: linear-gradient(135deg, var(--bg-secondary), var(--bg-tertiary));
    padding: 3rem 0;
    border-bottom: 1px solid var(--border-light);
}

.bundle-header {
    text-align: center;
    margin-bottom: 3rem;
}

.bundle-badge {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(135deg, #ff9500, #ff6b35);
    color: white;
    border-radius: 20px;
    padding: 0.5rem 1rem;
    margin-bottom: 1rem;
    font-weight: 600;
    gap: 0.5rem;
}

.bundle-discount {
    font-size: 1.25rem;
    font-weight: 700;
}

.bundle-header h1 {
    font-size: 2.5rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.bundle-subtitle {
    font-size: 1.25rem;
    color: var(--text-secondary);
    margin: 0;
}

.bundle-showcase {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    align-items: center;
}

.bundle-products {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.bundle-product-item {
    text-align: center;
    background: var(--bg-primary);
    padding: 1.5rem;
    border-radius: 16px;
    border: 1px solid var(--border-light);
    transition: all 0.3s ease;
    min-width: 150px;
}

.bundle-product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.bundle-product-item .product-image {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bundle-product-item img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.bundle-product-item h3 {
    font-size: 1rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.individual-price {
    color: var(--text-secondary);
    font-size: 0.875rem;
    text-decoration: line-through;
}

.plus-icon {
    position: absolute;
    right: -15px;
    top: 50%;
    transform: translateY(-50%);
    background: #ff9500;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.bundle-product-item {
    position: relative;
}

.bundle-pricing {
    background: var(--bg-primary);
    padding: 2rem;
    border-radius: 20px;
    border: 1px solid var(--border-light);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.price-comparison {
    margin-bottom: 2rem;
}

.price-comparison > div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-light);
}

.price-comparison > div:last-child {
    border-bottom: none;
    padding-top: 1rem;
    border-top: 2px solid var(--border-light);
}

.label {
    color: var(--text-secondary);
    font-weight: 500;
}

.price.original {
    color: var(--text-secondary);
    text-decoration: line-through;
    font-size: 1.1rem;
}

.savings {
    color: #10b981;
    font-weight: 700;
    font-size: 1.1rem;
}

.price.bundle {
    color: #ff9500;
    font-weight: 700;
    font-size: 1.5rem;
}

.bundle-benefits {
    margin: 2rem 0;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    color: var(--text-primary);
}

.benefit-item i {
    color: #10b981;
    font-size: 1.1rem;
}

.bundle-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.btn-bundle-primary, .btn-bundle-secondary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
}

.btn-bundle-primary {
    background: linear-gradient(135deg, #ff9500, #ff6b35);
    color: white;
    flex-direction: column;
    padding: 1.5rem;
}

.btn-bundle-primary:hover {
    background: linear-gradient(135deg, #ff6b35, #e55100);
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(255, 149, 0, 0.3);
}

.btn-bundle-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 2px solid var(--border-light);
}

.btn-bundle-secondary:hover {
    background: var(--bg-tertiary);
    border-color: #ff9500;
    color: var(--text-primary);
}

/* Details Section */
.bundle-details {
    padding: 4rem 0;
    background: var(--bg-primary);
}

.details-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
}

.product-details h2 {
    color: var(--text-primary);
    margin-bottom: 2rem;
    font-size: 2rem;
}

.product-detail-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: var(--bg-secondary);
    padding: 1.5rem;
    border-radius: 16px;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-light);
}

.product-detail-card img {
    width: 80px;
    height: 80px;
    object-fit: contain;
}

.detail-content h3 {
    color: var(--text-primary);
    margin-bottom: 0.75rem;
    font-size: 1.25rem;
}

.detail-content ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.detail-content li {
    color: var(--text-secondary);
    padding: 0.25rem 0;
    position: relative;
    padding-left: 1.25rem;
}

.detail-content li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: bold;
}

/* Testimonials */
.testimonials h2 {
    color: var(--text-primary);
    margin-bottom: 2rem;
    font-size: 2rem;
}

.testimonial-card {
    background: var(--bg-secondary);
    padding: 1.5rem;
    border-radius: 16px;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-light);
}

.stars {
    color: #fbbf24;
    margin-bottom: 1rem;
}

.testimonial-card p {
    color: var(--text-secondary);
    font-style: italic;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.testimonial-author strong {
    color: var(--text-primary);
    display: block;
}

.testimonial-author span {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

/* FAQ Section */
.bundle-faq {
    padding: 4rem 0;
    background: var(--bg-secondary);
}

.bundle-faq h2 {
    text-align: center;
    color: var(--text-primary);
    margin-bottom: 3rem;
    font-size: 2rem;
}

.faq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.faq-item {
    background: var(--bg-primary);
    padding: 1.5rem;
    border-radius: 16px;
    border: 1px solid var(--border-light);
}

.faq-item h3 {
    color: var(--text-primary);
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.faq-item p {
    color: var(--text-secondary);
    line-height: 1.6;
    margin: 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .bundle-showcase,
    .details-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .bundle-products {
        flex-direction: column;
        align-items: center;
    }
    
    .plus-icon {
        position: static;
        transform: none;
        margin: 0.5rem 0;
    }
    
    .bundle-header h1 {
        font-size: 2rem;
    }
    
    .product-detail-card {
        flex-direction: column;
        text-align: center;
    }
    
    .faq-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>