/**
 * Enhanced Cart System
 * Sistema de carrito avanzado con variantes, cupones y checkout
 */

class EnhancedCart {
    constructor() {
        this.items = this.loadCart();
        this.coupons = this.loadCoupons();
        this.appliedCoupon = null;
        this.shippingCost = 0;
        this.freeShippingThreshold = 10000;
        this.taxRate = 0.21; // 21% IVA
        
        this.updateCartBadge();
        this.bindEvents();
    }

    // Cargar carrito desde localStorage
    loadCart() {
        try {
            return JSON.parse(localStorage.getItem('enhanced-cart')) || [];
        } catch (e) {
            return [];
        }
    }

    // Guardar carrito en localStorage
    saveCart() {
        localStorage.setItem('enhanced-cart', JSON.stringify(this.items));
        this.updateCartBadge();
    }

    // Cargar cupones disponibles
    loadCoupons() {
        return {
            'SUBLIME10': { discount: 0.10, minAmount: 5000, type: 'percentage' },
            'PRIMERA20': { discount: 0.20, minAmount: 8000, type: 'percentage' },
            'DESCUENTO500': { discount: 500, minAmount: 3000, type: 'fixed' },
            'ENVIOGRATIS': { discount: 0, minAmount: 5000, type: 'free_shipping' },
            'MEGA30': { discount: 0.30, minAmount: 15000, type: 'percentage' }
        };
    }

    // Generar ID único para producto con variantes
    generateProductId(productId, size, color) {
        return `${productId}_${size}_${color}`.replace(/\s+/g, '');
    }

    // Agregar producto al carrito
    addProduct(productId, productName, price, size, color) {
        const uniqueId = this.generateProductId(productId, size, color);
        const existingItem = this.items.find(item => item.uniqueId === uniqueId);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.items.push({
                uniqueId: uniqueId,
                productId: productId,
                name: productName,
                price: price,
                size: size,
                color: color,
                quantity: 1,
                image: this.getProductImage(productName)
            });
        }

        this.saveCart();
        this.showAddedToCartMessage(productName, size, color);
    }

    // Obtener imagen del producto
    getProductImage(productName) {
        const productType = productName.toLowerCase().split(' ')[0];
        const imageMap = {
            'remera': 'assets/images/products/remera.svg',
            'buzo': 'assets/images/products/buzo.svg',
            'taza': 'assets/images/products/taza.svg',
            'mouse': 'assets/images/products/mousepad.svg',
            'funda': 'assets/images/products/funda.svg',
            'almohada': 'assets/images/products/almohada.svg'
        };
        return imageMap[productType] || 'assets/images/products/default.svg';
    }

    // Mostrar mensaje de producto agregado
    showAddedToCartMessage(productName, size, color) {
        const message = document.createElement('div');
        message.className = 'cart-notification';
        message.innerHTML = `
            <div class="cart-notification-content">
                <i class="fas fa-check-circle"></i>
                <span>¡${productName} (${size}, ${color}) agregado al carrito!</span>
                <button onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        document.body.appendChild(message);

        setTimeout(() => {
            message.remove();
        }, 3000);
    }

    // Actualizar cantidad de producto
    updateQuantity(uniqueId, quantity) {
        const item = this.items.find(item => item.uniqueId === uniqueId);
        if (item) {
            if (quantity <= 0) {
                this.removeItem(uniqueId);
            } else {
                item.quantity = quantity;
                this.saveCart();
                this.updateCartDisplay();
            }
        }
    }

    // Eliminar producto del carrito
    removeItem(uniqueId) {
        this.items = this.items.filter(item => item.uniqueId !== uniqueId);
        this.saveCart();
        this.updateCartDisplay();
    }

    // Calcular subtotal
    calculateSubtotal() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    // Obtener subtotal (alias para compatibilidad)
    getSubtotal() {
        return this.calculateSubtotal();
    }

    // Limpiar carrito
    clearCart() {
        this.items = [];
        this.appliedCoupon = null;
        this.saveCart();
        this.updateCartBadge();
    }

    // Aplicar cupón
    applyCoupon(couponCode) {
        const coupon = this.coupons[couponCode];
        if (!coupon) {
            return false;
        }

        const subtotal = this.getSubtotal();
        if (subtotal < coupon.minAmount) {
            return false;
        }

        this.appliedCoupon = { code: couponCode, ...coupon };
        return true;
    }

    // Obtener descuento aplicado
    getDiscount() {
        if (!this.appliedCoupon) {
            return 0;
        }

        const subtotal = this.getSubtotal();
        if (this.appliedCoupon.type === 'percentage') {
            return subtotal * this.appliedCoupon.discount;
        } else if (this.appliedCoupon.type === 'fixed') {
            return this.appliedCoupon.discount;
        }
        return 0;
    }

    // Verificar si aplica envío gratis
    haseFreeShipping() {
        if (this.appliedCoupon && this.appliedCoupon.type === 'free_shipping') {
            return true;
        }
        return this.getSubtotal() >= this.freeShippingThreshold;
    }

    // Calcular descuento
    calculateDiscount() {
        if (!this.appliedCoupon) return 0;
        
        const coupon = this.coupons[this.appliedCoupon];
        const subtotal = this.calculateSubtotal();

        if (coupon.type === 'percentage') {
            return subtotal * coupon.discount;
        } else if (coupon.type === 'fixed') {
            return Math.min(coupon.discount, subtotal);
        }
        return 0;
    }

    // Calcular costo de envío
    calculateShipping() {
        if (this.appliedCoupon && this.coupons[this.appliedCoupon].type === 'free_shipping') {
            return 0;
        }
        
        const subtotal = this.calculateSubtotal();
        return subtotal >= this.freeShippingThreshold ? 0 : this.shippingCost;
    }

    // Calcular IVA contenido según RG 5.614/2024
    // Fórmula: IVA = Precio Total × [Alicuota / (1 + Alicuota)]
    calculateTax() {
        const subtotal = this.calculateSubtotal();
        const discount = this.calculateDiscount();
        const totalWithShipping = subtotal - discount + this.calculateShipping();
        
        // IVA contenido en el precio total (RG 5.614/2024)
        const ivaContenido = totalWithShipping * (this.taxRate / (1 + this.taxRate));
        return ivaContenido;
    }

    // Calcular total (IVA ya contenido en precios según RG 5.614/2024)
    calculateTotal() {
        const subtotal = this.calculateSubtotal();
        const discount = this.calculateDiscount();
        const shipping = this.calculateShipping();
        // NO sumamos el IVA porque ya está contenido en los precios
        return subtotal - discount + shipping;
    }

    // Aplicar cupón
    applyCoupon(couponCode) {
        const coupon = this.coupons[couponCode.toUpperCase()];
        if (!coupon) {
            this.showCouponMessage('Cupón inválido', 'error');
            return false;
        }

        const subtotal = this.calculateSubtotal();
        if (subtotal < coupon.minAmount) {
            this.showCouponMessage(`Compra mínima requerida: $${coupon.minAmount.toLocaleString()}`, 'error');
            return false;
        }

        this.appliedCoupon = couponCode.toUpperCase();
        this.showCouponMessage('¡Cupón aplicado exitosamente!', 'success');
        this.updateCartDisplay();
        return true;
    }

    // Remover cupón
    removeCoupon() {
        this.appliedCoupon = null;
        this.updateCartDisplay();
        this.showCouponMessage('Cupón removido', 'info');
    }

    // Mostrar mensaje de cupón
    showCouponMessage(message, type) {
        const messageEl = document.getElementById('coupon-message');
        if (messageEl) {
            messageEl.textContent = message;
            messageEl.className = `coupon-message ${type}`;
            messageEl.style.display = 'block';
            
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 3000);
        }
    }

    // Actualizar badge del carrito
    updateCartBadge() {
        const badge = document.querySelector('.cart-badge');
        
        if (badge) {
            const totalItems = this.items.reduce((sum, item) => sum + item.quantity, 0);
            badge.textContent = totalItems;
            badge.style.display = totalItems > 0 ? 'flex' : 'none';
        }
    }

    // Mostrar modal del carrito
    showCartModal() {
        const modal = document.getElementById('cartModal');
        if (modal) {
            modal.style.display = 'flex';
            this.updateCartDisplay();
        }
    }

    // Cerrar modal del carrito
    closeCartModal() {
        const modal = document.getElementById('cartModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Generar barra de progreso para envío gratis
    generateShippingProgressBar() {
        const subtotal = this.calculateSubtotal();
        const remaining = this.freeShippingThreshold - subtotal;
        const progress = Math.min((subtotal / this.freeShippingThreshold) * 100, 100);
        
        if (remaining <= 0) {
            return `
                <div class="shipping-progress-bar">
                    <div class="shipping-progress-header">
                        <i class="fas fa-check-circle" style="color: #10b981;"></i>
                        <span class="shipping-progress-text">¡Envío GRATIS activado!</span>
                    </div>
                    <div class="shipping-progress-track">
                        <div class="shipping-progress-fill" style="width: 100%;"></div>
                    </div>
                </div>
            `;
        } else {
            return `
                <div class="shipping-progress-bar">
                    <div class="shipping-progress-header">
                        <i class="fas fa-truck" style="color: #ff9500;"></i>
                        <span class="shipping-progress-text">Te faltan $${remaining.toLocaleString()} para <strong>ENVÍO GRATIS</strong></span>
                    </div>
                    <div class="shipping-progress-track">
                        <div class="shipping-progress-fill" style="width: ${progress}%;"></div>
                    </div>
                    <div class="shipping-progress-percentage">${Math.round(progress)}%</div>
                </div>
            `;
        }
    }

    // Actualizar display del carrito
    updateCartDisplay() {
        const cartBody = document.getElementById('cartModalBody');
        if (!cartBody) return;

        if (this.items.length === 0) {
            cartBody.innerHTML = this.getEmptyCartHTML();
            return;
        }

        cartBody.innerHTML = this.getCartHTML();
    }

    // HTML para carrito vacío
    getEmptyCartHTML() {
        return `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Tu carrito está vacío</h3>
                <p>¡Agrega algunos productos para comenzar!</p>
                <button class="btn-continue-shopping" onclick="closeCartModal()">
                    Continuar Comprando
                </button>
            </div>
        `;
    }

    // HTML completo del carrito
    getCartHTML() {
        const subtotal = this.calculateSubtotal();
        const discount = this.calculateDiscount();
        const shipping = this.calculateShipping();
        const tax = this.calculateTax();
        const total = this.calculateTotal();

        return `
            <div class="cart-items">
                ${this.items.map(item => this.getCartItemHTML(item)).join('')}
            </div>
            
            ${this.generateShippingProgressBar()}
            
            <div class="cart-summary">
                <div class="coupon-section">
                    <h4>Cupón de Descuento</h4>
                    <div class="coupon-input-group">
                        <input type="text" id="coupon-input" placeholder="Ingresa tu cupón" value="${this.appliedCoupon || ''}">
                        <button onclick="cart.handleCouponAction()" id="coupon-btn">
                            ${this.appliedCoupon ? 'Quitar' : 'Aplicar'}
                        </button>
                    </div>
                    <div id="coupon-message" class="coupon-message" style="display: none;"></div>
                </div>

                <div class="cart-totals">
                    <div class="total-line">
                        <span>Subtotal:</span>
                        <span>$${subtotal.toLocaleString()}</span>
                    </div>
                    
                    ${discount > 0 ? `
                        <div class="total-line discount">
                            <span>Descuento (${this.appliedCoupon}):</span>
                            <span>-$${discount.toLocaleString()}</span>
                        </div>
                    ` : ''}
                    
                    <div class="total-line">
                        <span>Envío:</span>
                        <span>${shipping === 0 ? 'GRATIS' : '$' + shipping.toLocaleString()}</span>
                    </div>
                    
                    <div class="total-line">
                        <span>IVA contenido (21%):</span>
                        <span>$${tax.toLocaleString()}</span>
                    </div>
                    
                    <div class="total-line total">
                        <span>Total:</span>
                        <span>$${total.toLocaleString()}</span>
                    </div>
                </div>

                <div class="cart-actions">
                    <button class="cart-btn-modal cart-btn-modal-secondary" onclick="closeCartModal()">
                        Continuar Comprando
                    </button>
                    <button class="cart-btn-modal cart-btn-modal-primary" onclick="cart.showCheckout()">
                        Finalizar Compra
                    </button>
                </div>
            </div>
        `;
    }

    // HTML de item individual del carrito
    getCartItemHTML(item) {
        return `
            <div class="cart-item" data-id="${item.uniqueId}">
                <div class="cart-item-image">
                    <img src="${item.image}" alt="${item.name}" onerror="this.src='assets/images/products/default.svg'">
                </div>
                <div class="cart-item-details">
                    <h4>${item.name}</h4>
                    <div class="cart-item-variants">
                        <span class="variant">Tamaño: ${item.size}</span>
                        <span class="variant">Color: ${item.color}</span>
                    </div>
                    <div class="cart-item-price">$${item.price.toLocaleString()}</div>
                </div>
                <div class="cart-item-quantity">
                    <button onclick="cart.updateQuantity('${item.uniqueId}', ${item.quantity - 1})" class="qty-btn">-</button>
                    <span class="qty-display">${item.quantity}</span>
                    <button onclick="cart.updateQuantity('${item.uniqueId}', ${item.quantity + 1})" class="qty-btn">+</button>
                </div>
                <div class="cart-item-total">
                    $${(item.price * item.quantity).toLocaleString()}
                </div>
                <button onclick="cart.removeItem('${item.uniqueId}')" class="cart-item-remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    }

    // Manejar acción de cupón (aplicar/quitar)
    handleCouponAction() {
        if (this.appliedCoupon) {
            this.removeCoupon();
        } else {
            const couponInput = document.getElementById('coupon-input');
            if (couponInput && couponInput.value.trim()) {
                this.applyCoupon(couponInput.value.trim());
            }
        }
    }

    // Mostrar checkout - redirigir a página independiente
    showCheckout() {
        // Verificar que hay productos en el carrito
        if (this.items.length === 0) {
            alert('Tu carrito está vacío. Agrega algunos productos antes de continuar.');
            return;
        }
        
        // Redirigir a la página de checkout
        window.location.href = 'checkout.php';
    }

    // Cerrar checkout - ya no se usa modal, redirige a página
    closeCheckout() {
        // Función mantenida por compatibilidad, pero ahora redirige
        window.location.href = 'checkout.php';
    }

    // Bind events
    bindEvents() {
        // Cerrar modal del carrito al hacer click fuera
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('cart-modal')) {
                this.closeCartModal();
            }
        });

        // Tecla escape para cerrar modal del carrito
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeCartModal();
            }
        });
    }
}

// Funciones globales para compatibilidad
function closeCartModal() {
    if (window.cart) {
        window.cart.closeCartModal();
    }
}

function closeCheckoutModal() {
    // Ya no se usa modal de checkout, redirige a página independiente
    window.location.href = 'checkout.php';
}

function showCartModal() {
    if (window.cart) {
        window.cart.showCartModal();
    }
}

// Exportar la clase
window.EnhancedCart = EnhancedCart;