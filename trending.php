<?php
/**
 * Página de Productos Trending
 * Muestra productos populares y recomendaciones dinámicas
 */

require_once 'includes/functions.php';
require_once 'config/database.php';

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Productos Trending - Lo Más Popular';
$page_description = 'Descubre los productos más populares y tendencias actuales. Productos personalizables que están siendo los más comprados.';
include 'includes/header.php';
?>

<section class="trending-hero">
    <div class="container">
        <div class="trending-header">
            <h1><span class="trending-icon">🔥</span> Trending Ahora</h1>
            <p class="trending-subtitle">Los productos más populares y demandados de la semana</p>
        </div>
        
        <div class="trending-stats">
            <div class="stat-item">
                <div class="stat-number">2.547</div>
                <div class="stat-label">Productos vendidos esta semana</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">89%</div>
                <div class="stat-label">Satisfacción del cliente</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24h</div>
                <div class="stat-label">Actualización de tendencias</div>
            </div>
        </div>
    </div>
</section>

<section class="trending-content">
    <div class="container">
        
        <!-- Productos Más Populares -->
        <div class="trending-section">
            <h2><i class="fas fa-fire"></i> Lo Más Popular Esta Semana</h2>
            <div id="trending-products-main" 
                 data-recommendations="trending" 
                 data-limit="6">
                <div class="trending-loading">
                    <div class="loading-spinner"></div>
                    <p>Cargando productos trending...</p>
                </div>
            </div>
        </div>
        
        <!-- Recomendaciones por Temporada -->
        <div class="trending-section">
            <h2><i class="fas fa-calendar-alt"></i> Perfecto para Esta Temporada</h2>
            <div id="seasonal-trending" 
                 data-recommendations="seasonal" 
                 data-limit="4">
                <div class="trending-loading">
                    <div class="loading-spinner"></div>
                    <p>Cargando productos estacionales...</p>
                </div>
            </div>
        </div>
        
        <!-- Recomendaciones Personalizadas -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="trending-section">
            <h2><i class="fas fa-user-star"></i> Especialmente para Ti</h2>
            <div id="personalized-trending" 
                 data-recommendations="personalized" 
                 data-limit="6">
                <div class="trending-loading">
                    <div class="loading-spinner"></div>
                    <p>Cargando recomendaciones personalizadas...</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="trending-section">
            <div class="login-prompt">
                <div class="login-content">
                    <i class="fas fa-user-circle"></i>
                    <h3>¿Quieres recomendaciones personalizadas?</h3>
                    <p>Inicia sesión para obtener productos recomendados basados en tus gustos</p>
                    <a href="login.php" class="btn-login-trending">Iniciar Sesión</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Productos por Rango de Precio -->
        <div class="trending-section">
            <h2><i class="fas fa-tag"></i> Por Rango de Precio</h2>
            
            <div class="price-filters">
                <button class="price-filter-btn active" data-price-range="all">Todos</button>
                <button class="price-filter-btn" data-price-range="budget">Económicos ($0-$4.999)</button>
                <button class="price-filter-btn" data-price-range="mid">Medio ($5.000-$8.999)</button>
                <button class="price-filter-btn" data-price-range="premium">Premium ($9.000+)</button>
            </div>
            
            <div id="price-filtered-products" class="recommendations-grid">
                <!-- Se carga dinámicamente -->
            </div>
        </div>
        
        <!-- Call to Action -->
        <div class="trending-cta">
            <div class="cta-content">
                <h2>¿No encuentras lo que buscas?</h2>
                <p>Explora toda nuestra colección o crea tu diseño personalizado</p>
                <div class="cta-buttons">
                    <a href="particulares.php" class="btn-cta primary">
                        <i class="fas fa-th-large"></i> Ver Toda la Tienda
                    </a>
                    <a href="customize-shirt.php" class="btn-cta secondary">
                        <i class="fas fa-palette"></i> Crear Diseño
                    </a>
                </div>
            </div>
        </div>
        
    </div>
</section>

<!-- CSS específico para trending -->
<style>
.trending-hero {
    background: linear-gradient(135deg, #ff9500 0%, #ff6b00 50%, #e55d00 100%);
    color: white;
    padding: 4rem 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.trending-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="fire" patternUnits="userSpaceOnUse" width="20" height="20"><path d="M10 0L20 10L10 20L0 10Z" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23fire)"/></svg>');
    animation: firePattern 20s linear infinite;
}

@keyframes firePattern {
    0% { transform: translateX(0) translateY(0); }
    100% { transform: translateX(-20px) translateY(-20px); }
}

.trending-header {
    position: relative;
    z-index: 2;
    margin-bottom: 3rem;
}

.trending-header h1 {
    font-size: 3rem;
    margin: 0 0 1rem 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.trending-icon {
    display: inline-block;
    animation: fireFlicker 1.5s ease-in-out infinite;
}

.trending-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.trending-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    max-width: 800px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

.stat-item {
    text-align: center;
    padding: 1.5rem;
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #fff;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.trending-content {
    padding: 4rem 0;
}

.trending-section {
    margin-bottom: 4rem;
}

.trending-section h2 {
    font-size: 2rem;
    margin-bottom: 2rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.trending-section h2 i {
    color: var(--ecommerce-primary);
}

.trending-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
    color: var(--text-secondary);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--ecommerce-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.login-prompt {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 3rem;
    text-align: center;
    border: 2px dashed var(--border-color);
}

.login-content i {
    font-size: 3rem;
    color: var(--ecommerce-primary);
    margin-bottom: 1rem;
}

.login-content h3 {
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.login-content p {
    margin-bottom: 2rem;
    color: var(--text-secondary);
}

.btn-login-trending {
    background: var(--ecommerce-primary);
    color: white;
    padding: 1rem 2rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-login-trending:hover {
    background: #ff7b00;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255,149,0,0.4);
}

.price-filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.price-filter-btn {
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    color: var(--text-primary);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.price-filter-btn:hover,
.price-filter-btn.active {
    background: var(--ecommerce-primary);
    color: white;
    border-color: var(--ecommerce-primary);
}

.trending-cta {
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
    border-radius: 16px;
    padding: 4rem 2rem;
    text-align: center;
    margin-top: 4rem;
}

.cta-content h2 {
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.cta-content p {
    margin-bottom: 2rem;
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-cta {
    padding: 1rem 2rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-cta.primary {
    background: var(--ecommerce-primary);
    color: white;
}

.btn-cta.primary:hover {
    background: #ff7b00;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255,149,0,0.4);
}

.btn-cta.secondary {
    background: transparent;
    color: var(--text-primary);
    border: 2px solid var(--border-color);
}

.btn-cta.secondary:hover {
    background: var(--text-primary);
    color: var(--bg-primary);
    border-color: var(--text-primary);
}

/* Responsive */
@media (max-width: 768px) {
    .trending-hero {
        padding: 3rem 0;
    }
    
    .trending-header h1 {
        font-size: 2rem;
    }
    
    .trending-stats {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stat-item {
        padding: 1rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .trending-section h2 {
        font-size: 1.5rem;
    }
    
    .price-filters {
        flex-direction: column;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
}

/* Modo oscuro */
[data-theme="dark"] .trending-cta {
    background: linear-gradient(135deg, var(--bg-secondary-dark) 0%, var(--bg-tertiary-dark) 100%);
}

[data-theme="dark"] .login-prompt {
    background: linear-gradient(135deg, var(--bg-secondary-dark) 0%, var(--bg-tertiary-dark) 100%);
    border-color: var(--border-color-dark);
}

[data-theme="dark"] .price-filter-btn {
    background: var(--bg-secondary-dark);
    border-color: var(--border-color-dark);
    color: var(--text-primary-dark);
}

[data-theme="dark"] .btn-cta.secondary {
    color: var(--text-primary-dark);
    border-color: var(--border-color-dark);
}

[data-theme="dark"] .btn-cta.secondary:hover {
    background: var(--text-primary-dark);
    color: var(--bg-primary-dark);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar filtros de precio
    setupPriceFilters();
    
    // Trackear vista de página trending
    setTimeout(() => {
        if (window.recommendationSystem) {
            window.recommendationSystem.trackEvent('page_view', {
                page: 'trending',
                source: 'navigation'
            });
        }
    }, 500);
});

function setupPriceFilters() {
    const filterButtons = document.querySelectorAll('.price-filter-btn');
    const productsContainer = document.getElementById('price-filtered-products');
    
    // Cargar todos los productos inicialmente
    loadProductsByPriceRange('all');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Actualizar botones activos
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Cargar productos filtrados
            const priceRange = this.getAttribute('data-price-range');
            loadProductsByPriceRange(priceRange);
        });
    });
}

async function loadProductsByPriceRange(range) {
    const container = document.getElementById('price-filtered-products');
    container.innerHTML = '<div class="trending-loading"><div class="loading-spinner"></div><p>Cargando productos...</p></div>';
    
    try {
        let apiUrl = 'api/recommendations/get-recommendations.php?type=trending&session_id=' + 
                    (window.recommendationSystem ? window.recommendationSystem.sessionId : 'temp') + 
                    '&limit=6';
        
        const response = await fetch(apiUrl);
        const data = await response.json();
        
        if (data.success && data.recommendations) {
            let filteredProducts = data.recommendations;
            
            // Filtrar por precio si no es "all"
            if (range !== 'all') {
                filteredProducts = data.recommendations.filter(product => {
                    const price = parseFloat(product.price);
                    switch (range) {
                        case 'budget':
                            return price < 5000;
                        case 'mid':
                            return price >= 5000 && price < 9000;
                        case 'premium':
                            return price >= 9000;
                        default:
                            return true;
                    }
                });
            }
            
            if (filteredProducts.length > 0) {
                renderFilteredProducts(container, filteredProducts);
            } else {
                container.innerHTML = `
                    <div class="no-products">
                        <i class="fas fa-search"></i>
                        <h3>No hay productos en este rango</h3>
                        <p>Prueba con otro filtro de precio</p>
                    </div>
                `;
            }
        } else {
            throw new Error('No se pudieron cargar los productos');
        }
    } catch (error) {
        console.error('Error loading products by price range:', error);
        container.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Error al cargar productos. Intenta nuevamente.</p>
            </div>
        `;
    }
}

function renderFilteredProducts(container, products) {
    const productsHtml = products.map(product => {
        const rating = parseFloat(product.avg_rating || 0);
        const stars = renderStars(rating);
        
        return `
            <div class="recommendation-card" data-product-id="${product.id}">
                <div class="rec-product-image">
                    <a href="product-detail.php?id=${product.id}">
                        <img src="${product.main_image_url || 'assets/images/products/default.svg'}" 
                             alt="${product.name}" 
                             loading="lazy"
                             onerror="this.src='assets/images/products/default.svg'">
                    </a>
                </div>
                <div class="rec-product-info">
                    <h4 class="rec-product-title">
                        <a href="product-detail.php?id=${product.id}">${product.name}</a>
                    </h4>
                    <div class="rec-product-rating">
                        <div class="stars">${stars}</div>
                        <span class="rating-text">${rating.toFixed(1)} (${product.review_count || 0})</span>
                    </div>
                    <div class="rec-product-price">
                        <span class="price-current">$${parseFloat(product.price).toLocaleString('es-AR')}</span>
                    </div>
                    <div class="rec-product-actions">
                        <button class="btn-add-to-cart-rec" 
                                onclick="addToCartFromTrending(${product.id}, '${product.name}', ${product.price})">
                            <i class="fas fa-cart-plus"></i> Agregar
                        </button>
                        <a href="product-detail.php?id=${product.id}" class="btn-view-rec">
                            Ver más
                        </a>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = productsHtml;
}

function renderStars(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    
    let html = '';
    for (let i = 0; i < fullStars; i++) {
        html += '<i class="fas fa-star"></i>';
    }
    if (hasHalfStar) {
        html += '<i class="fas fa-star-half-alt"></i>';
    }
    for (let i = 0; i < emptyStars; i++) {
        html += '<i class="far fa-star"></i>';
    }
    
    return html;
}

function addToCartFromTrending(productId, productName, price) {
    if (window.cart && window.cart.addProduct) {
        window.cart.addProduct(productId, productName, price, 'M', 'Blanco');
        
        // Trackear conversión desde trending
        if (window.recommendationSystem) {
            window.recommendationSystem.trackEvent('trending_conversion', {
                product_id: productId,
                page: 'trending',
                price: price
            });
        }
    }
}
</script>

<!-- CSS específico para recomendaciones en trending -->
<link rel="stylesheet" href="assets/css/recommendations.css?v=<?php echo time(); ?>">

<?php include 'includes/footer.php'; ?>