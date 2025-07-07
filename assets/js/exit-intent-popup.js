/**
 * Exit Intent Popup - Sistema de descuento para primera compra
 * Optimizado para conversión según Business Analyst AI
 */

class ExitIntentPopup {
    constructor() {
        this.shown = false;
        this.exitThreshold = 10; // pixels del top para trigger
        this.delay = 3000; // 3 segundos mínimo en la página
        this.startTime = Date.now();
        this.isFirstTime = !localStorage.getItem('exit_popup_shown');
        
        this.init();
    }

    init() {
        // Solo mostrar para primera vez y usuarios que no compraron
        if (!this.isFirstTime || this.hasCompletedPurchase()) {
            return;
        }

        // Event listeners
        document.addEventListener('mouseleave', (e) => this.handleMouseLeave(e));
        document.addEventListener('beforeunload', (e) => this.handleBeforeUnload(e));
        
        // Para móvil - detectar scroll up rápido
        this.setupMobileExitIntent();
    }

    handleMouseLeave(e) {
        // Verificar que está saliendo por arriba
        if (e.clientY <= this.exitThreshold && this.canShow()) {
            this.showPopup();
        }
    }

    handleBeforeUnload(e) {
        if (this.canShow()) {
            this.showPopup();
        }
    }

    setupMobileExitIntent() {
        let lastScrollY = window.scrollY;
        let scrollSpeed = 0;
        
        window.addEventListener('scroll', () => {
            const currentScrollY = window.scrollY;
            scrollSpeed = Math.abs(currentScrollY - lastScrollY);
            
            // Detectar scroll up rápido en móvil
            if (currentScrollY < lastScrollY && scrollSpeed > 50 && currentScrollY < 100) {
                if (this.canShow()) {
                    setTimeout(() => this.showPopup(), 200);
                }
            }
            
            lastScrollY = currentScrollY;
        });
    }

    canShow() {
        const timeSpent = Date.now() - this.startTime;
        return !this.shown && 
               this.isFirstTime && 
               timeSpent > this.delay &&
               !this.hasCompletedPurchase();
    }

    hasCompletedPurchase() {
        return localStorage.getItem('has_purchased') === 'true';
    }

    showPopup() {
        if (this.shown) return;
        
        this.shown = true;
        localStorage.setItem('exit_popup_shown', 'true');
        
        this.createPopupHTML();
        this.bindEvents();
        
        // Analytics tracking
        this.trackEvent('exit_intent_shown');
    }

    createPopupHTML() {
        const popupHTML = `
            <div id="exitIntentPopup" class="exit-intent-overlay">
                <div class="exit-intent-popup">
                    <button class="exit-intent-close" onclick="exitIntentPopup.closePopup()">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="exit-intent-content">
                        <div class="exit-intent-header">
                            <h2>¡Espera! 🎁</h2>
                            <p class="exit-intent-subtitle">No te vayas sin tu descuento especial</p>
                        </div>
                        
                        <div class="exit-intent-offer">
                            <div class="discount-badge">
                                <span class="discount-percent">10%</span>
                                <span class="discount-text">OFF</span>
                            </div>
                            
                            <div class="offer-details">
                                <h3>Descuento de Bienvenida</h3>
                                <p>En tu primera compra + <strong>Envío gratis</strong> en pedidos superiores a $12.000</p>
                                
                                <div class="benefits-list">
                                    <div class="benefit-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Personalización incluida</span>
                                    </div>
                                    <div class="benefit-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Calidad garantizada</span>
                                    </div>
                                    <div class="benefit-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Soporte 24/7</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="exit-intent-form">
                            <h4>Obtén tu cupón ahora</h4>
                            <div class="email-capture">
                                <input type="email" id="exitEmailInput" placeholder="tu@email.com" required>
                                <button class="claim-button" onclick="exitIntentPopup.claimDiscount()">
                                    <span>Obtener Descuento</span>
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                            <p class="privacy-note">No spam. Solo ofertas especiales 💌</p>
                        </div>
                        
                        <div class="urgency-timer">
                            <p>⏰ Esta oferta expira en: <span id="urgencyCountdown">05:00</span></p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', popupHTML);
        
        // Iniciar countdown
        this.startUrgencyTimer();
        
        // Animación de entrada
        setTimeout(() => {
            document.getElementById('exitIntentPopup').classList.add('show');
        }, 100);
    }

    startUrgencyTimer() {
        let timeLeft = 300; // 5 minutos
        const countdown = document.getElementById('urgencyCountdown');
        
        const timer = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            countdown.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            timeLeft--;
            
            if (timeLeft < 0) {
                clearInterval(timer);
                this.closePopup();
            }
        }, 1000);
    }

    bindEvents() {
        // Cerrar con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closePopup();
            }
        });
        
        // Cerrar al hacer click fuera
        document.getElementById('exitIntentPopup').addEventListener('click', (e) => {
            if (e.target.classList.contains('exit-intent-overlay')) {
                this.closePopup();
            }
        });
    }

    claimDiscount() {
        const emailInput = document.getElementById('exitEmailInput');
        const email = emailInput.value.trim();
        
        if (!this.validateEmail(email)) {
            this.showError('Por favor ingresa un email válido');
            return;
        }
        
        // Guardar email
        localStorage.setItem('user_email', email);
        
        // Generar cupón único
        const couponCode = this.generateCouponCode();
        localStorage.setItem('welcome_coupon', couponCode);
        
        // Mostrar éxito
        this.showSuccess(couponCode);
        
        // Analytics
        this.trackEvent('discount_claimed', { email, coupon: couponCode });
        
        // Cerrar popup después de 3 segundos
        setTimeout(() => {
            this.closePopup();
            // Redirigir a tienda
            window.location.href = 'particulares.php?coupon=' + couponCode;
        }, 3000);
    }

    generateCouponCode() {
        const prefix = 'WELCOME';
        const random = Math.random().toString(36).substring(2, 8).toUpperCase();
        return `${prefix}${random}`;
    }

    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    showError(message) {
        const emailInput = document.getElementById('exitEmailInput');
        emailInput.style.borderColor = '#ef4444';
        
        // Mostrar mensaje de error
        let errorMsg = document.querySelector('.email-error');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'email-error';
            emailInput.parentNode.insertBefore(errorMsg, emailInput.nextSibling);
        }
        
        errorMsg.textContent = message;
        errorMsg.style.color = '#ef4444';
        errorMsg.style.fontSize = '0.875rem';
        errorMsg.style.marginTop = '0.5rem';
        
        setTimeout(() => {
            emailInput.style.borderColor = '';
            if (errorMsg) errorMsg.remove();
        }, 3000);
    }

    showSuccess(couponCode) {
        const popup = document.querySelector('.exit-intent-popup');
        popup.innerHTML = `
            <div class="success-content">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>¡Perfecto! 🎉</h2>
                <p>Tu cupón de descuento está listo:</p>
                
                <div class="coupon-code-display">
                    <code>${couponCode}</code>
                    <button onclick="navigator.clipboard.writeText('${couponCode}')" class="copy-coupon">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                
                <div class="success-benefits">
                    <p>✅ 10% de descuento en tu primera compra</p>
                    <p>✅ Envío gratis en compras +$12.000</p>
                    <p>✅ Válido por 7 días</p>
                </div>
                
                <p class="redirect-message">Te redirigimos a la tienda en 3 segundos...</p>
            </div>
        `;
    }

    closePopup() {
        const popup = document.getElementById('exitIntentPopup');
        if (popup) {
            popup.classList.add('closing');
            setTimeout(() => {
                popup.remove();
            }, 300);
        }
        
        this.trackEvent('exit_intent_closed');
    }

    trackEvent(event, data = {}) {
        // Google Analytics 4 tracking
        if (typeof gtag !== 'undefined') {
            gtag('event', event, {
                event_category: 'Exit Intent',
                ...data
            });
        }
        
        // Console log para debugging
        console.log('Exit Intent Event:', event, data);
    }
}

// Auto-inicializar
document.addEventListener('DOMContentLoaded', () => {
    window.exitIntentPopup = new ExitIntentPopup();
});

// Exportar para uso global
window.ExitIntentPopup = ExitIntentPopup;