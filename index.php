<?php
require_once 'includes/functions.php';

$page_title = 'Inicio';
include 'includes/header.php';
?>

<!-- Trust Bar Superior -->
        <section class="trust-bar-top">
            <div class="trust-container">
                <div class="trust-items">
                    <div class="trust-item">
                        <i class="fas fa-shipping-fast"></i>
                        <span>Envío a todo el país</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-shield-check"></i>
                        <span>Compra 100% segura</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-clock"></i>
                        <span>Producción rápida</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-undo"></i>
                        <span>Garantía de calidad</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hero Section Optimizado con soporte modo oscuro -->
        <section class="hero-section-optimized">
            <div class="hero-container">
                <div class="hero-content-main">
                    <h1 class="hero-headline">Remeras Personalizadas de Alta Calidad</h1>
                    <p class="hero-subline">Diseñá tu remera única con nuestro editor interactivo. Sublimación premium, diseños ilimitados y entregas en todo el país.</p>
                    
                    <!-- CTAs principales -->
                    <div class="hero-cta-group">
                        <a href="customize-shirt.php" class="cta-primary">
                            <i class="fas fa-tshirt"></i>
                            Personalizar Mi Remera
                        </a>
                        <a href="particulares.php" class="cta-secondary">
                            <i class="fas fa-shopping-bag"></i>
                            Ver Productos
                        </a>
                    </div>

                    <!-- Features destacadas -->
                    <div class="hero-features">
                        <div class="feature-item">
                            <i class="fas fa-palette"></i>
                            <span>Editor intuitivo</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-medal"></i>
                            <span>Sublimación HD</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-heart"></i>
                            <span>Diseños únicos</span>
                        </div>
                    </div>
                </div>
                
                <!-- Visual del producto -->
                <div class="hero-visual">
                    <div class="product-showcase">
                        <img src="assets/images/centro1.png" alt="Remera personalizada FractalMerch" class="hero-product-image" loading="eager" fetchpriority="high">
                        <div class="floating-badge">
                            <i class="fas fa-crown"></i>
                            <span>Premium</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección de Proceso -->
        <section class="process-section">
            <div class="container">
                <div class="section-header">
                    <h2>Cómo funciona</h2>
                    <p>Crear tu remera personalizada es súper fácil</p>
                </div>
                <div class="process-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3>Diseñá</h3>
                        <p>Usá nuestro editor para crear tu diseño único con tus propias imágenes</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3>Pedí</h3>
                        <p>Elegí tu talle, confirmá tu diseño y hacé tu pedido de forma segura</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3>Recibí</h3>
                        <p>Producimos tu remera con sublimación premium y te la enviamos</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Productos Destacados -->
        <section class="featured-products">
            <div class="container">
                <div class="section-header">
                    <h2>Productos más elegidos</h2>
                    <p>Descubrí nuestros productos más populares</p>
                </div>
                <div class="products-grid">
                    <div class="product-card-mini">
                        <div class="product-image">
                            <img src="assets/images/centro1.png" alt="Remeras personalizadas" loading="lazy" decoding="async">
                        </div>
                        <h3>Remeras</h3>
                        <p class="price">Desde $5.999</p>
                        <a href="particulares.php#remeras" class="btn-outline">Ver más</a>
                    </div>
                    <div class="product-card-mini">
                        <div class="product-image">
                            <img src="assets/images/centro2.png" alt="Buzos personalizados" loading="lazy" decoding="async">
                        </div>
                        <h3>Buzos</h3>
                        <p class="price">Desde $12.999</p>
                        <a href="particulares.php#buzos" class="btn-outline">Ver más</a>
                    </div>
                    <div class="product-card-mini">
                        <div class="product-image">
                            <img src="assets/images/centro3.png" alt="Tazas personalizadas" loading="lazy" decoding="async">
                        </div>
                        <h3>Tazas</h3>
                        <p class="price">Desde $3.499</p>
                        <a href="particulares.php#tazas" class="btn-outline">Ver más</a>
                    </div>
                </div>
                <div class="cta-center">
                    <a href="particulares.php" class="cta-primary">Ver Todos los Productos</a>
                </div>
            </div>
        </section>

        <!-- Trust Signals y Social Proof -->
        <section class="trust-signals-section">
            <div class="container">
                <!-- Indicadores de Confianza -->
                <div class="trust-indicators">
                    <div class="trust-metric">
                        <div class="trust-icon">
                            <i class="fas fa-shield-check"></i>
                        </div>
                        <div class="trust-content">
                            <h3>Garantía Total</h3>
                            <p>100% satisfacción garantizada o devolvemos tu dinero</p>
                        </div>
                    </div>
                    <div class="trust-metric">
                        <div class="trust-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="trust-content">
                            <h3>Envío Seguro</h3>
                            <p>Envíos asegurados a todo el país con seguimiento</p>
                        </div>
                    </div>
                    <div class="trust-metric">
                        <div class="trust-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="trust-content">
                            <h3>Calidad Premium</h3>
                            <p>Sublimación HD con materiales de primera calidad</p>
                        </div>
                    </div>
                    <div class="trust-metric">
                        <div class="trust-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="trust-content">
                            <h3>Entrega Rápida</h3>
                            <p>Producción en 2-3 días hábiles</p>
                        </div>
                    </div>
                </div>

                <!-- Sección de Testimonios -->
                <div class="testimonials-section">
                    <div class="section-header">
                        <h2>Lo que dicen nuestros clientes</h2>
                        <p>Testimonios reales de clientes satisfechos</p>
                    </div>
                    <div class="testimonials-container">
                        <div class="testimonial-placeholder">
                            <div class="testimonial-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <h3>¡Próximamente testimonios reales!</h3>
                            <p>Estamos empezando y queremos que seas parte de nuestras primeras reseñas.</p>
                            <div class="early-adopter-badge">
                                <i class="fas fa-crown"></i>
                                <span>Sé uno de nuestros primeros clientes</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Badges de Seguridad -->
                <div class="security-badges">
                    <div class="badge-item">
                        <i class="fas fa-lock"></i>
                        <span>Pago Seguro</span>
                    </div>
                    <div class="badge-item">
                        <i class="fas fa-certificate"></i>
                        <span>Calidad Certificada</span>
                    </div>
                    <div class="badge-item">
                        <i class="fas fa-undo"></i>
                        <span>Devolución Gratuita</span>
                    </div>
                    <div class="badge-item">
                        <i class="fas fa-headset"></i>
                        <span>Soporte 24/7</span>
                    </div>
                </div>

                <!-- Garantías Expandidas -->
                <div class="guarantees-section">
                    <div class="section-header">
                        <h2>Nuestro Compromiso Contigo</h2>
                        <p>Trabajamos para que tengas la mejor experiencia de compra</p>
                    </div>
                    <div class="guarantees-grid">
                        <div class="guarantee-card">
                            <div class="guarantee-icon">
                                <i class="fas fa-undo-alt"></i>
                            </div>
                            <h3>Devolución Sin Complicaciones</h3>
                            <p>Si no estás 100% satisfecho, devolvemos tu dinero en 30 días. Sin preguntas, sin complicaciones.</p>
                        </div>
                        <div class="guarantee-card">
                            <div class="guarantee-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h3>Reimpresión Gratuita</h3>
                            <p>¿Problema con la impresión? Te hacemos una nueva sin costo adicional hasta que quedes conforme.</p>
                        </div>
                        <div class="guarantee-card">
                            <div class="guarantee-icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <h3>Envío Asegurado</h3>
                            <p>Todos nuestros envíos incluyen seguro y seguimiento. Si se pierde, te enviamos otro.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

<script>
// Animaciones suaves para el hero optimizado y modo oscuro
document.addEventListener('DOMContentLoaded', function() {
    // Verificar preferencia del usuario y aplicar modo oscuro
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        document.documentElement.classList.add('dark-mode');
        console.log('✨ Modo oscuro activado con tema fractal');
    }
    
    // Intersection Observer para animaciones on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    // Observar elementos para animación
    document.querySelectorAll('.step, .product-card-mini, .trust-metric, .testimonial-placeholder, .guarantee-card').forEach(el => {
        observer.observe(el);
    });

    // Smooth scroll para links internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Core Web Vitals optimization
    if ('PerformanceObserver' in window) {
        // Optimize LCP by preloading largest contentful paint candidates
        const observer = new PerformanceObserver((list) => {
            const entries = list.getEntries();
            entries.forEach(entry => {
                if (entry.element && entry.element.tagName === 'IMG') {
                    // Ensure critical images have fetchpriority="high"
                    if (!entry.element.getAttribute('fetchpriority')) {
                        entry.element.setAttribute('fetchpriority', 'auto');
                    }
                }
            });
        });
        
        observer.observe({ entryTypes: ['largest-contentful-paint'] });
    }
    
    // Optimize CLS by setting dimensions for images without them
    document.querySelectorAll('img:not([width]):not([height])').forEach(img => {
        img.addEventListener('load', function() {
            // Set intrinsic dimensions to prevent layout shifts
            if (this.naturalWidth && this.naturalHeight) {
                this.style.aspectRatio = `${this.naturalWidth} / ${this.naturalHeight}`;
            }
        });
    });
    
    // Performance budgets monitoring
    if ('performance' in window) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                const perfData = performance.timing;
                const loadTime = perfData.loadEventEnd - perfData.navigationStart;
                
                // Log performance metrics for optimization
                if (loadTime > 3000) {
                    console.warn('⚠️ Page load time exceeds 3s:', loadTime + 'ms');
                }
                
                // Report to analytics if available
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'page_load_time', {
                        'custom_map': {'metric1': 'load_time'},
                        'metric1': loadTime
                    });
                }
            }, 0);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>