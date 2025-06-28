<?php
require_once 'includes/functions.php';

$page_title = 'Inicio';
include 'includes/header.php';
?>

<section class="hero-section">
            <!-- Sección izquierda - Empresas -->
            <div class="hero-left">
                <div class="hero-slider left-slider">
                    <div class="hero-slide active">
                        <img src="assets/images/corp1.png" alt="Soluciones Empresariales" class="hero-bg-image">
                    </div>
                    <div class="hero-slide">
                        <img src="assets/images/corp2.png" alt="Soluciones Empresariales" class="hero-bg-image">
                    </div>
                </div>
                
                <div class="hero-content left-content">
                    <h2>Soluciones Empresariales</h2>
                    <p>Equipos industriales y soporte especializado para hacer crecer tu negocio</p>
                    <a href="empresas.php" class="cta-button business">
                        <i class="fas fa-building"></i>
                        Para Empresas
                    </a>
                </div>
                
                <!-- Indicadores izquierda -->
                <div class="slider-indicators left-indicators">
                    <button class="indicator active" data-slide="0" data-side="left"></button>
                    <button class="indicator" data-slide="1" data-side="left"></button>
                </div>
            </div>
            
            <!-- Sección derecha - Particulares -->
            <div class="hero-right">
                <div class="hero-slider right-slider">
                    <div class="hero-slide active">
                        <img src="assets/images/izquierda1.png" alt="Para Particulares" class="hero-bg-image">
                    </div>
                    <div class="hero-slide">
                        <img src="assets/images/izquierda2.png" alt="Para Particulares" class="hero-bg-image">
                    </div>
                    <div class="hero-slide">
                        <img src="assets/images/centro1.png" alt="Para Particulares" class="hero-bg-image">
                    </div>
                    <div class="hero-slide">
                        <img src="assets/images/centro2.png" alt="Para Particulares" class="hero-bg-image">
                    </div>
                    <div class="hero-slide">
                        <img src="assets/images/centro3.png" alt="Para Particulares" class="hero-bg-image">
                    </div>
                </div>
                
                <div class="hero-content right-content">
                    <h2>Para Creativos</h2>
                    <p>Todo lo que necesitas para dar vida a tus ideas más creativas</p>
                    <a href="particulares.php" class="cta-button personal">
                        <i class="fas fa-user"></i>
                        Para Particulares
                    </a>
                </div>
                
                <!-- Indicadores derecha -->
                <div class="slider-indicators right-indicators">
                    <button class="indicator active" data-slide="0" data-side="right"></button>
                    <button class="indicator" data-slide="1" data-side="right"></button>
                    <button class="indicator" data-slide="2" data-side="right"></button>
                    <button class="indicator" data-slide="3" data-side="right"></button>
                    <button class="indicator" data-slide="4" data-side="right"></button>
                </div>
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
    // Funcionalidad del slider dividido
    class DualHeroSlider {
        constructor() {
            this.leftSlides = document.querySelectorAll('.left-slider .hero-slide');
            this.rightSlides = document.querySelectorAll('.right-slider .hero-slide');
            this.leftIndicators = document.querySelectorAll('.left-indicators .indicator');
            this.rightIndicators = document.querySelectorAll('.right-indicators .indicator');
            
            this.currentLeftSlide = 0;
            this.currentRightSlide = 0;
            this.leftInterval = null;
            this.rightInterval = null;
            
            this.init();
        }

        init() {
            // Event listeners para indicadores izquierdos
            this.leftIndicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    this.goToSlide('left', index);
                });
            });

            // Event listeners para indicadores derechos
            this.rightIndicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    this.goToSlide('right', index);
                });
            });

            // Auto-slide para ambos lados
            this.startAutoSlide();

            // Pausar auto-slide en hover
            const leftSection = document.querySelector('.hero-left');
            const rightSection = document.querySelector('.hero-right');
            
            leftSection.addEventListener('mouseenter', () => this.stopAutoSlide('left'));
            leftSection.addEventListener('mouseleave', () => this.startAutoSlide('left'));
            
            rightSection.addEventListener('mouseenter', () => this.stopAutoSlide('right'));
            rightSection.addEventListener('mouseleave', () => this.startAutoSlide('right'));
        }

        goToSlide(side, index) {
            if (side === 'left') {
                // Remover clase active del slide e indicador actual izquierdo
                this.leftSlides[this.currentLeftSlide].classList.remove('active');
                this.leftIndicators[this.currentLeftSlide].classList.remove('active');

                // Agregar clase active al nuevo slide e indicador izquierdo
                this.currentLeftSlide = index;
                this.leftSlides[this.currentLeftSlide].classList.add('active');
                this.leftIndicators[this.currentLeftSlide].classList.add('active');
                
                // Reiniciar auto-slide izquierdo
                this.stopAutoSlide('left');
                this.startAutoSlide('left');
            } else {
                // Remover clase active del slide e indicador actual derecho
                this.rightSlides[this.currentRightSlide].classList.remove('active');
                this.rightIndicators[this.currentRightSlide].classList.remove('active');

                // Agregar clase active al nuevo slide e indicador derecho
                this.currentRightSlide = index;
                this.rightSlides[this.currentRightSlide].classList.add('active');
                this.rightIndicators[this.currentRightSlide].classList.add('active');
                
                // Reiniciar auto-slide derecho
                this.stopAutoSlide('right');
                this.startAutoSlide('right');
            }
        }

        nextSlide(side) {
            if (side === 'left') {
                const nextIndex = (this.currentLeftSlide + 1) % this.leftSlides.length;
                this.goToSlide('left', nextIndex);
            } else {
                const nextIndex = (this.currentRightSlide + 1) % this.rightSlides.length;
                this.goToSlide('right', nextIndex);
            }
        }

        startAutoSlide(side = 'both') {
            if (side === 'left' || side === 'both') {
                this.leftInterval = setInterval(() => {
                    this.nextSlide('left');
                }, 5000);
            }
            
            if (side === 'right' || side === 'both') {
                this.rightInterval = setInterval(() => {
                    this.nextSlide('right');
                }, 5000);
            }
        }

        stopAutoSlide(side = 'both') {
            if (side === 'left' || side === 'both') {
                if (this.leftInterval) {
                    clearInterval(this.leftInterval);
                    this.leftInterval = null;
                }
            }
            
            if (side === 'right' || side === 'both') {
                if (this.rightInterval) {
                    clearInterval(this.rightInterval);
                    this.rightInterval = null;
                }
            }
        }
    }

    // Inicializar slider cuando la página cargue
    document.addEventListener('DOMContentLoaded', function() {
        new DualHeroSlider();
    });
</script>

<?php include 'includes/footer.php'; ?>