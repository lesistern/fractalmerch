<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Checkout - Finalizar Compra';
include 'includes/header.php';
?>

<div class="checkout-container">
    <!-- Checkout Principal -->
    <section class="checkout-main">
        <div class="container">
            <div class="checkout-layout">
                <!-- Columna Izquierda - Formulario -->
                <div class="checkout-left">
                    <div class="checkout-steps">
                        <div class="step active" data-step="1">
                            <span class="step-number">1</span>
                            <span class="step-title">Datos de contacto</span>
                        </div>
                        <div class="step" data-step="2">
                            <span class="step-number">2</span>
                            <span class="step-title">Envío</span>
                        </div>
                        <div class="step" data-step="3">
                            <span class="step-number">3</span>
                            <span class="step-title">Pago</span>
                        </div>
                    </div>

                    <form id="checkout-form" class="checkout-form">
                        <!-- Paso 1: Datos de contacto -->
                        <div class="checkout-step" id="step-1">
                            <h2>Datos de contacto</h2>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">Nombre</label>
                                    <input type="text" id="firstName" name="firstName" required>
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Apellido</label>
                                    <input type="text" id="lastName" name="lastName" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="phone">Teléfono</label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                        </div>

                        <!-- Paso 2: Datos de envío -->
                        <div class="checkout-step" id="step-2" style="display: none;">
                            <h2>Información de envío</h2>
                            <div class="form-group">
                                <label for="address">Dirección</label>
                                <input type="text" id="address" name="address" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">Ciudad</label>
                                    <input type="text" id="city" name="city" required>
                                </div>
                                <div class="form-group">
                                    <label for="province">Provincia</label>
                                    <select id="province" name="province" required>
                                        <option value="">Selecciona una provincia</option>
                                        <option value="buenos-aires">Buenos Aires</option>
                                        <option value="cordoba">Córdoba</option>
                                        <option value="santa-fe">Santa Fe</option>
                                        <option value="mendoza">Mendoza</option>
                                        <option value="tucuman">Tucumán</option>
                                        <option value="entre-rios">Entre Ríos</option>
                                        <option value="salta">Salta</option>
                                        <option value="misiones">Misiones</option>
                                        <option value="chaco">Chaco</option>
                                        <option value="corrientes">Corrientes</option>
                                        <option value="santiago-del-estero">Santiago del Estero</option>
                                        <option value="san-juan">San Juan</option>
                                        <option value="jujuy">Jujuy</option>
                                        <option value="rio-negro">Río Negro</option>
                                        <option value="formosa">Formosa</option>
                                        <option value="neuquen">Neuquén</option>
                                        <option value="chubut">Chubut</option>
                                        <option value="san-luis">San Luis</option>
                                        <option value="catamarca">Catamarca</option>
                                        <option value="la-rioja">La Rioja</option>
                                        <option value="la-pampa">La Pampa</option>
                                        <option value="santa-cruz">Santa Cruz</option>
                                        <option value="tierra-del-fuego">Tierra del Fuego</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="postalCode">Código Postal</label>
                                <input type="text" id="postalCode" name="postalCode" required>
                            </div>
                            
                            <div class="shipping-options">
                                <h3>Método de envío</h3>
                                <div id="shipping-loader" class="shipping-loader" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span>Calculando opciones de envío...</span>
                                </div>
                                <div id="shipping-options-grid" class="shipping-options-grid">
                                    <!-- Las opciones se cargarán dinámicamente -->
                                </div>
                                <div id="shipping-error" class="shipping-error" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>Error al calcular envío. Intenta de nuevo.</span>
                                    <button type="button" id="retry-shipping">Reintentar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 3: Pago -->
                        <div class="checkout-step" id="step-3" style="display: none;">
                            <h2>Información de pago</h2>
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <input type="radio" id="credit-card" name="payment" value="credit-card" checked>
                                    <label for="credit-card">
                                        <i class="fas fa-credit-card"></i>
                                        Tarjeta de crédito/débito
                                    </label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" id="mercado-pago" name="payment" value="mercado-pago">
                                    <label for="mercado-pago">
                                        <i class="fab fa-cc-mastercard"></i>
                                        Mercado Pago
                                    </label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" id="bank-transfer" name="payment" value="bank-transfer">
                                    <label for="bank-transfer">
                                        <i class="fas fa-university"></i>
                                        Transferencia bancaria
                                    </label>
                                </div>
                            </div>

                            <div id="credit-card-form" class="payment-form">
                                <div class="form-group">
                                    <label for="cardNumber">Número de tarjeta</label>
                                    <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                                <div class="form-triple">
                                    <div class="form-group">
                                        <label for="cardName">Nombre en la tarjeta</label>
                                        <input type="text" id="cardName" name="cardName" placeholder="Como aparece en la tarjeta">
                                    </div>
                                    <div class="form-group">
                                        <label for="expiryDate">Vencimiento</label>
                                        <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/AA" maxlength="5">
                                    </div>
                                    <div class="form-group">
                                        <label for="cvv">CVV</label>
                                        <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="4">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de navegación -->
                        <div class="checkout-navigation">
                            <button type="button" id="prev-step" class="btn-secondary" style="display: none;">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </button>
                            <button type="button" id="next-step" class="btn-primary">
                                Siguiente <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" id="place-order" class="btn-success" style="display: none;">
                                <i class="fas fa-check"></i> Finalizar Compra
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Columna Derecha - Resumen -->
                <div class="checkout-right">
                    <div class="order-summary">
                        <h3>Resumen del pedido</h3>
                        <div id="cart-items-summary">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </div>
                        
                        <div class="order-totals">
                            <div class="total-line">
                                <span>Subtotal:</span>
                                <span id="subtotal">$0</span>
                            </div>
                            <div class="total-line">
                                <span>Envío:</span>
                                <span id="shipping-cost">Gratis</span>
                            </div>
                            <div class="total-line">
                                <span>IVA (21%):</span>
                                <span id="tax-amount">$0</span>
                            </div>
                            <div class="total-line total-final">
                                <span>Total:</span>
                                <span id="final-total">$0</span>
                            </div>
                        </div>

                        <div class="coupon-section">
                            <input type="text" id="coupon-code" placeholder="Código de descuento">
                            <button type="button" id="apply-coupon">Aplicar</button>
                        </div>
                    </div>

                    <div class="security-badges">
                        <div class="security-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Compra 100% segura</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-lock"></i>
                            <span>Datos protegidos SSL</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-undo"></i>
                            <span>30 días de garantía</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
class CheckoutManager {
    constructor() {
        this.currentStep = 1;
        this.maxSteps = 3;
        this.shippingCalculationTimeout = null;
        this.shippingQuotes = [];
        this.selectedShipping = null;
        
        // Usar el carrito global inicializado en header.php
        this.cart = window.cart || new EnhancedCart();
        
        this.initializeCheckout();
        this.bindEvents();
        this.loadCartSummary();
    }

    initializeCheckout() {
        // Verificar si hay productos en el carrito
        if (this.cart.items.length === 0) {
            this.showEmptyCartMessage();
            return;
        }
    }

    bindEvents() {
        document.getElementById('next-step').addEventListener('click', () => this.nextStep());
        document.getElementById('prev-step').addEventListener('click', () => this.prevStep());
        document.getElementById('checkout-form').addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Eventos para métodos de pago
        document.querySelectorAll('input[name="payment"]').forEach(radio => {
            radio.addEventListener('change', () => this.handlePaymentMethodChange());
        });

        // Eventos para campos de dirección (para recalcular envío)
        document.getElementById('postalCode').addEventListener('input', () => {
            clearTimeout(this.shippingCalculationTimeout);
            this.shippingCalculationTimeout = setTimeout(() => {
                this.calculateShippingOptions();
            }, 1000);
        });

        document.getElementById('city').addEventListener('input', () => {
            clearTimeout(this.shippingCalculationTimeout);
            this.shippingCalculationTimeout = setTimeout(() => {
                this.calculateShippingOptions();
            }, 1000);
        });

        document.getElementById('province').addEventListener('change', () => {
            this.calculateShippingOptions();
        });

        // Evento para reintentar cálculo de envío
        document.getElementById('retry-shipping').addEventListener('click', () => {
            this.calculateShippingOptions();
        });

        // Eventos para cupones
        document.getElementById('apply-coupon').addEventListener('click', () => this.applyCoupon());

        // Formateo de campos
        this.setupFieldFormatting();
    }

    nextStep() {
        if (this.validateCurrentStep()) {
            if (this.currentStep < this.maxSteps) {
                this.hideStep(this.currentStep);
                this.currentStep++;
                this.showStep(this.currentStep);
                this.updateStepIndicators();
                this.updateNavigationButtons();
            }
        }
    }

    prevStep() {
        if (this.currentStep > 1) {
            this.hideStep(this.currentStep);
            this.currentStep--;
            this.showStep(this.currentStep);
            this.updateStepIndicators();
            this.updateNavigationButtons();
        }
    }

    showStep(step) {
        document.getElementById(`step-${step}`).style.display = 'block';
    }

    hideStep(step) {
        document.getElementById(`step-${step}`).style.display = 'none';
    }

    updateStepIndicators() {
        document.querySelectorAll('.step').forEach((step, index) => {
            const stepNumber = index + 1;
            if (stepNumber <= this.currentStep) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
    }

    updateNavigationButtons() {
        const prevBtn = document.getElementById('prev-step');
        const nextBtn = document.getElementById('next-step');
        const submitBtn = document.getElementById('place-order');

        prevBtn.style.display = this.currentStep > 1 ? 'inline-block' : 'none';
        
        if (this.currentStep === this.maxSteps) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'inline-block';
        } else {
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
        }
    }

    validateCurrentStep() {
        const currentStepDiv = document.getElementById(`step-${this.currentStep}`);
        const requiredFields = currentStepDiv.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        });

        if (!isValid) {
            this.showMessage('Por favor completa todos los campos requeridos', 'error');
        }

        return isValid;
    }

    loadCartSummary() {
        const summaryContainer = document.getElementById('cart-items-summary');
        let html = '';

        this.cart.items.forEach(item => {
            html += `
                <div class="cart-item-summary">
                    <div class="item-image">
                        <img src="${item.image}" alt="${item.name}" onerror="this.src='assets/images/products/default.svg'">
                        <span class="item-quantity">${item.quantity}</span>
                    </div>
                    <div class="item-details">
                        <h4>${item.name}</h4>
                        <p>${item.size} • ${item.color}</p>
                        <span class="item-price">$${(item.price * item.quantity).toLocaleString()}</span>
                    </div>
                </div>
            `;
        });

        summaryContainer.innerHTML = html;
        this.updateTotals();
    }

    updateTotals() {
        const subtotal = this.cart.getSubtotal();
        const shippingCost = this.getShippingCost();
        const taxAmount = subtotal * 0.21;
        const total = subtotal + shippingCost + taxAmount;

        document.getElementById('subtotal').textContent = `$${subtotal.toLocaleString()}`;
        document.getElementById('shipping-cost').textContent = shippingCost > 0 ? `$${shippingCost.toLocaleString()}` : 'Gratis';
        document.getElementById('tax-amount').textContent = `$${Math.round(taxAmount).toLocaleString()}`;
        document.getElementById('final-total').textContent = `$${Math.round(total).toLocaleString()}`;
    }

    getShippingCost() {
        if (this.selectedShipping) {
            return this.selectedShipping.price || 0;
        }
        return 0;
    }

    calculateShippingOptions() {
        const address = this.getShippingAddress();
        
        if (!this.isAddressComplete(address)) {
            return; // No calcular si la dirección está incompleta
        }

        this.showShippingLoader();

        const requestData = {
            address: address,
            items: this.cart.items.map(item => ({
                name: item.name,
                price: item.price,
                quantity: item.quantity
            }))
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
                this.shippingQuotes = data.quotes;
                this.displayShippingOptions(data.quotes);
            } else {
                this.showShippingError(data.error || 'Error desconocido');
            }
        })
        .catch(error => {
            console.error('Error calculating shipping:', error);
            this.showShippingError('Error de conexión');
        })
        .finally(() => {
            this.hideShippingLoader();
        });
    }

    getShippingAddress() {
        return {
            street: document.getElementById('address').value.trim(),
            city: document.getElementById('city').value.trim(),
            state: document.getElementById('province').value,
            postal_code: document.getElementById('postalCode').value.trim()
        };
    }

    isAddressComplete(address) {
        return address.street && address.city && address.state && address.postal_code;
    }

    showShippingLoader() {
        document.getElementById('shipping-loader').style.display = 'flex';
        document.getElementById('shipping-options-grid').style.display = 'none';
        document.getElementById('shipping-error').style.display = 'none';
    }

    hideShippingLoader() {
        document.getElementById('shipping-loader').style.display = 'none';
    }

    displayShippingOptions(quotes) {
        const container = document.getElementById('shipping-options-grid');
        container.innerHTML = '';
        container.style.display = 'block';

        quotes.forEach((quote, index) => {
            const option = document.createElement('div');
            option.className = 'shipping-option';
            
            const isChecked = index === 0 ? 'checked' : '';
            
            option.innerHTML = `
                <input type="radio" id="shipping-${quote.provider}" name="shipping" value="${quote.provider}" ${isChecked} data-quote-index="${index}">
                <label for="shipping-${quote.provider}">
                    <div class="shipping-info">
                        <span class="shipping-name">${quote.name}</span>
                        <span class="shipping-time">${quote.description}</span>
                        ${quote.free_shipping ? '<span class="free-shipping-badge">Envío gratis</span>' : ''}
                    </div>
                    <span class="shipping-price">
                        ${quote.price > 0 ? '$' + Math.round(quote.price).toLocaleString() : 'Gratis'}
                    </span>
                </label>
            `;

            container.appendChild(option);
        });

        // Seleccionar la primera opción por defecto
        if (quotes.length > 0) {
            this.selectedShipping = quotes[0];
            this.updateTotals();
        }

        // Agregar eventos a las nuevas opciones
        document.querySelectorAll('input[name="shipping"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const quoteIndex = parseInt(e.target.dataset.quoteIndex);
                this.selectedShipping = this.shippingQuotes[quoteIndex];
                this.updateTotals();
            });
        });
    }

    showShippingError(message) {
        const errorDiv = document.getElementById('shipping-error');
        errorDiv.querySelector('span').textContent = message;
        errorDiv.style.display = 'flex';
        document.getElementById('shipping-options-grid').style.display = 'none';
    }

    handlePaymentMethodChange() {
        const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
        const creditCardForm = document.getElementById('credit-card-form');
        
        if (paymentMethod === 'credit-card') {
            creditCardForm.style.display = 'block';
        } else {
            creditCardForm.style.display = 'none';
        }
    }

    applyCoupon() {
        const couponCode = document.getElementById('coupon-code').value.trim().toUpperCase();
        
        if (this.cart.applyCoupon(couponCode)) {
            this.showMessage(`Cupón ${couponCode} aplicado correctamente`, 'success');
            this.updateTotals();
        } else {
            this.showMessage('Cupón inválido o no cumple los requisitos', 'error');
        }
    }

    setupFieldFormatting() {
        // Formateo de número de tarjeta
        const cardNumberInput = document.getElementById('cardNumber');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || '';
                e.target.value = formattedValue;
            });
        }

        // Formateo de fecha de vencimiento
        const expiryInput = document.getElementById('expiryDate');
        if (expiryInput) {
            expiryInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            });
        }
    }

    handleSubmit(e) {
        e.preventDefault();
        
        if (this.validateCurrentStep()) {
            this.processOrder();
        }
    }

    processOrder() {
        // Simulación de procesamiento de pedido
        this.showMessage('Procesando tu pedido...', 'info');
        
        setTimeout(() => {
            // Limpiar carrito
            this.cart.clearCart();
            
            // Mostrar mensaje de éxito
            this.showSuccessMessage();
        }, 2000);
    }

    showSuccessMessage() {
        const successModal = document.createElement('div');
        successModal.className = 'success-modal';
        successModal.innerHTML = `
            <div class="success-modal-content">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>¡Pedido realizado con éxito!</h2>
                <p>Te enviaremos un email con los detalles de tu compra y el seguimiento del envío.</p>
                <button onclick="window.location.href='index.php'" class="btn-primary">
                    Continuar comprando
                </button>
            </div>
        `;
        document.body.appendChild(successModal);
    }

    showEmptyCartMessage() {
        document.querySelector('.checkout-left').innerHTML = `
            <div class="empty-cart-message">
                <i class="fas fa-shopping-cart"></i>
                <h2>Tu carrito está vacío</h2>
                <p>Agrega algunos productos para continuar con el checkout.</p>
                <a href="particulares.php" class="btn-primary">Ver productos</a>
            </div>
        `;
    }

    showMessage(message, type) {
        const messageEl = document.createElement('div');
        messageEl.className = `checkout-message ${type}`;
        messageEl.innerHTML = `
            <div class="message-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        document.body.appendChild(messageEl);
        
        setTimeout(() => {
            messageEl.remove();
        }, 5000);
    }
}

// Inicializar checkout cuando la página cargue
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco para asegurar que el carrito global esté disponible
    setTimeout(function() {
        new CheckoutManager();
    }, 200);
});
</script>

<?php include 'includes/footer.php'; ?>