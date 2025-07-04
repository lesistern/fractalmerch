<?php
require_once 'includes/functions.php';

$page_title = 'Inicio';
include 'includes/header.php';
?>

<section class="hero-section">
            <!-- Slider de imágenes de fondo -->
            <div class="hero-slider">
                <div class="hero-slide active">
                    <img src="assets/images/izquierda1.png" alt="Persona con remera" class="hero-bg-image" data-position="left">
                </div>
                <div class="hero-slide">
                    <img src="assets/images/izquierda2.png" alt="Persona con remera" class="hero-bg-image" data-position="left">
                </div>
                <div class="hero-slide">
                    <img src="assets/images/centro1.png" alt="Persona con remera" class="hero-bg-image" data-position="center">
                </div>
                <div class="hero-slide">
                    <img src="assets/images/centro2.png" alt="Persona con remera" class="hero-bg-image" data-position="center">
                </div>
                <div class="hero-slide">
                    <img src="assets/images/centro3.png" alt="Persona con remera" class="hero-bg-image" data-position="center">
                </div>
            </div>
            
            <!-- Contenido principal -->
            <div class="hero-content">
                <h2>Bienvenido a nuestro sitio</h2>
                <p>Descubre todo lo que tenemos para ofrecerte</p>
                <div class="hero-buttons">
                    <button class="cta-button">Explorar productos</button>
                    <a href="customize-shirt.php" class="shirt-editor-btn">
                        <i class="fas fa-tshirt"></i>
                        Personalizar Remera
                    </a>
                </div>
            </div>
            
            <!-- Indicadores del slider -->
            <div class="slider-indicators">
                <button class="indicator active" data-slide="0"></button>
                <button class="indicator" data-slide="1"></button>
                <button class="indicator" data-slide="2"></button>
                <button class="indicator" data-slide="3"></button>
                <button class="indicator" data-slide="4"></button>
            </div>
        </section>

        <section class="features-section">
            <div class="container">
                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fas fa-shipping-fast"></i>
                        <h3>Envío rápido</h3>
                        <p>Recibe tus productos en tiempo récord</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Compra segura</h3>
                        <p>Tus datos están protegidos</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-headset"></i>
                        <h3>Soporte 24/7</h3>
                        <p>Estamos aquí para ayudarte</p>
                    </div>
                </div>
            </div>
        </section>

<script>
    // Funcionalidad del slider de hero específica para index
    class HeroSlider {
        constructor() {
            this.slides = document.querySelectorAll('.hero-slide');
            this.indicators = document.querySelectorAll('.indicator');
            this.currentSlide = 0;
            this.autoSlideInterval = null;
            this.init();
        }

        init() {
            // Event listeners para indicadores
            this.indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    this.goToSlide(index);
                });
            });

            // Auto-slide cada 5 segundos
            this.startAutoSlide();

            // Pausar auto-slide en hover
            const heroSection = document.querySelector('.hero-section');
            heroSection.addEventListener('mouseenter', () => this.stopAutoSlide());
            heroSection.addEventListener('mouseleave', () => this.startAutoSlide());
        }

        goToSlide(index) {
            // Remover clase active de slide e indicador actual
            this.slides[this.currentSlide].classList.remove('active');
            this.indicators[this.currentSlide].classList.remove('active');

            // Agregar clase active al nuevo slide e indicador
            this.currentSlide = index;
            this.slides[this.currentSlide].classList.add('active');
            this.indicators[this.currentSlide].classList.add('active');
            
            // Reiniciar auto-slide
            this.stopAutoSlide();
            this.startAutoSlide();
        }

        nextSlide() {
            const nextIndex = (this.currentSlide + 1) % this.slides.length;
            this.goToSlide(nextIndex);
        }

        startAutoSlide() {
            this.autoSlideInterval = setInterval(() => {
                this.nextSlide();
            }, 5000);
        }

        stopAutoSlide() {
            if (this.autoSlideInterval) {
                clearInterval(this.autoSlideInterval);
                this.autoSlideInterval = null;
            }
        }
    }

    // Inicializar slider cuando la página cargue
    document.addEventListener('DOMContentLoaded', function() {
        new HeroSlider();
    });
</script>

<?php include 'includes/footer.php'; ?>