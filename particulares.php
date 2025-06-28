<?php
require_once 'includes/functions.php';

$page_title = 'Tienda - Productos Personalizables';
include 'includes/header.php';
?>

<section class="shop-hero">
    <div class="container">
        <div class="shop-header">
            <h1>Tienda de Productos Personalizables</h1>
            <p>Elige tu producto favorito y personalizalo a tu gusto</p>
            <a href="customize-shirt.php" class="btn btn-primary btn-large">
                <i class="fas fa-magic"></i>
                Ir al Editor de Remeras
            </a>
        </div>
    </div>
</section>

<section class="shop-products">
    <div class="container">
        <h2>Productos Personalizables</h2>
        <p class="section-subtitle">Elige tu producto y personalízalo con nuestro editor</p>
        
        <div class="products-grid">
            <!-- Remeras -->
            <div class="product-card">
                <div class="product-image">
                    <img src="assets/images/remera-frente.png" alt="Remera Personalizable">
                </div>
                <h3>Remera Personalizable</h3>
                <div class="product-price">
                    <span class="price-current">$5.999</span>
                </div>
                <p class="product-description">Remera 100% algodón, ideal para sublimación. Disponible en múltiples colores.</p>
                <div class="product-actions">
                    <div class="add-to-cart-group">
                        <div class="quantity-wrapper">
                            <div class="product-quantity">
                                <button class="quantity-btn minus-btn" data-product="remera">-</button>
                                <input type="number" id="quantity-remera" class="quantity-input" value="1" min="1" max="99">
                                <button class="quantity-btn plus-btn" data-product="remera">+</button>
                            </div>
                            <div class="quantity-tooltip" id="tooltip-remera">Máximo 99 unidades</div>
                        </div>
                        <button class="btn btn-primary" onclick="addToCart('remera', 5999, 'quantity-remera')">
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </button>
                    </div>
                    <a href="customize-shirt.php" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Personalizar
                    </a>
                </div>
            </div>

            <!-- Buzos -->
            <div class="product-card">
                <div class="product-image">
                    <i class="fas fa-tshirt" style="font-size: 4rem; color: #007bff;"></i>
                </div>
                <h3>Buzo con Capucha</h3>
                <div class="product-price">
                    <span class="price-current">$12.999</span>
                </div>
                <p class="product-description">Buzo canguro con capucha, excelente para diseños grandes y llamativos.</p>
                <div class="product-actions">
                    <div class="add-to-cart-group">
                        <div class="quantity-wrapper">
                            <div class="product-quantity">
                                <button class="quantity-btn minus-btn" data-product="buzo">-</button>
                                <input type="number" id="quantity-buzo" class="quantity-input" value="1" min="1" max="99">
                                <button class="quantity-btn plus-btn" data-product="buzo">+</button>
                            </div>
                            <div class="quantity-tooltip" id="tooltip-buzo">Máximo 99 unidades</div>
                        </div>
                        <button class="btn btn-primary" onclick="addToCart('buzo', 12999, 'quantity-buzo')">
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </button>
                    </div>
                    <button class="btn btn-secondary" onclick="alert('Próximamente: Editor de Buzos')">
                        <i class="fas fa-edit"></i> Personalizar
                    </button>
                </div>
            </div>

            <!-- Tazas -->
            <div class="product-card">
                <div class="product-image">
                    <i class="fas fa-mug-hot" style="font-size: 4rem; color: #007bff;"></i>
                </div>
                <h3>Taza Sublimable</h3>
                <div class="product-price">
                    <span class="price-current">$3.499</span>
                </div>
                <p class="product-description">Taza cerámica blanca de 330ml, perfecta para diseños personalizados.</p>
                <div class="product-actions">
                    <div class="add-to-cart-group">
                        <div class="quantity-wrapper">
                            <div class="product-quantity">
                                <button class="quantity-btn minus-btn" data-product="taza">-</button>
                                <input type="number" id="quantity-taza" class="quantity-input" value="1" min="1" max="99">
                                <button class="quantity-btn plus-btn" data-product="taza">+</button>
                            </div>
                            <div class="quantity-tooltip" id="tooltip-taza">Máximo 99 unidades</div>
                        </div>
                        <button class="btn btn-primary" onclick="addToCart('taza', 3499, 'quantity-taza')">
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </button>
                    </div>
                    <button class="btn btn-secondary" onclick="alert('Próximamente: Editor de Tazas')">
                        <i class="fas fa-edit"></i> Personalizar
                    </button>
                </div>
            </div>

            <!-- Mouse Pad -->
            <div class="product-card">
                <div class="product-image">
                    <i class="fas fa-mouse" style="font-size: 4rem; color: #007bff;"></i>
                </div>
                <h3>Mouse Pad</h3>
                <div class="product-price">
                    <span class="price-current">$2.999</span>
                </div>
                <p class="product-description">Mouse pad rectangular 25x19cm con base antideslizante.</p>
                <div class="product-actions">
                    <div class="add-to-cart-group">
                        <div class="quantity-wrapper">
                            <div class="product-quantity">
                                <button class="quantity-btn minus-btn" data-product="mousepad">-</button>
                                <input type="number" id="quantity-mousepad" class="quantity-input" value="1" min="1" max="99">
                                <button class="quantity-btn plus-btn" data-product="mousepad">+</button>
                            </div>
                            <div class="quantity-tooltip" id="tooltip-mousepad">Máximo 99 unidades</div>
                        </div>
                        <button class="btn btn-primary" onclick="addToCart('mousepad', 2999, 'quantity-mousepad')">
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </button>
                    </div>
                    <button class="btn btn-secondary" onclick="alert('Próximamente: Editor de Mouse Pads')">
                        <i class="fas fa-edit"></i> Personalizar
                    </button>
                </div>
            </div>

            <!-- Fundas de Celular -->
            <div class="product-card">
                <div class="product-image">
                    <i class="fas fa-mobile-alt" style="font-size: 4rem; color: #007bff;"></i>
                </div>
                <h3>Funda de Celular</h3>
                <div class="product-price">
                    <span class="price-current">$4.999</span>
                </div>
                <p class="product-description">Funda rígida sublimable compatible con múltiples modelos de teléfonos.</p>
                <div class="product-actions">
                    <div class="add-to-cart-group">
                        <div class="quantity-wrapper">
                            <div class="product-quantity">
                                <button class="quantity-btn minus-btn" data-product="funda">-</button>
                                <input type="number" id="quantity-funda" class="quantity-input" value="1" min="1" max="99">
                                <button class="quantity-btn plus-btn" data-product="funda">+</button>
                            </div>
                            <div class="quantity-tooltip" id="tooltip-funda">Máximo 99 unidades</div>
                        </div>
                        <button class="btn btn-primary" onclick="addToCart('funda', 4999, 'quantity-funda')">
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </button>
                    </div>
                    <button class="btn btn-secondary" onclick="alert('Próximamente: Editor de Fundas')">
                        <i class="fas fa-edit"></i> Personalizar
                    </button>
                </div>
            </div>

            <!-- Almohada -->
            <div class="product-card">
                <div class="product-image">
                    <i class="fas fa-bed" style="font-size: 4rem; color: #007bff;"></i>
                </div>
                <h3>Funda de Almohada</h3>
                <div class="product-price">
                    <span class="price-current">$6.999</span>
                </div>
                <p class="product-description">Funda de almohada 40x40cm en material sublimable, incluye relleno.</p>
                <div class="product-actions">
                    <div class="add-to-cart-group">
                        <div class="quantity-wrapper">
                            <div class="product-quantity">
                                <button class="quantity-btn minus-btn" data-product="almohada">-</button>
                                <input type="number" id="quantity-almohada" class="quantity-input" value="1" min="1" max="99">
                                <button class="quantity-btn plus-btn" data-product="almohada">+</button>
                            </div>
                            <div class="quantity-tooltip" id="tooltip-almohada">Máximo 99 unidades</div>
                        </div>
                        <button class="btn btn-primary" onclick="addToCart('almohada', 6999, 'quantity-almohada')">
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </button>
                    </div>
                    <button class="btn btn-secondary" onclick="alert('Próximamente: Editor de Fundas')">
                        <i class="fas fa-edit"></i> Personalizar
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

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
                <p>30 días para devolver tu producto</p>
            </div>
            <div class="info-card">
                <i class="fas fa-headset"></i>
                <h3>Soporte 24/7</h3>
                <p>Estamos aquí para ayudarte</p>
            </div>
        </div>
    </div>
</section>

<script>
// Funciones del carrito de compras
let cart = JSON.parse(localStorage.getItem('cart')) || [];

function addToCart(productName, price, quantityInputId) {
    const quantityInput = document.getElementById(quantityInputId);
    const quantity = parseInt(quantityInput.value);

    if (quantity > 0) {
        const existingProductIndex = cart.findIndex(item => item.name === productName);

        if (existingProductIndex > -1) {
            cart[existingProductIndex].quantity += quantity;
        } else {
            const product = {
                id: productName + '_' + Date.now(),
                name: productName,
                price: price,
                quantity: quantity
            };
            cart.push(product);
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartBadge();
        
        alert(`${quantity} x ${productName} agregado(s) al carrito!`);
    } else {
        alert('Por favor, introduce una cantidad válida.');
    }
}

function updateCartBadge() {
    const badge = document.querySelector('.cart-badge');
    if (badge) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        badge.textContent = totalItems;
    }
}

function setupQuantityButtons() {
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.product;
            const input = document.getElementById(`quantity-${productId}`);
            let currentValue = parseInt(input.value);

            if (this.classList.contains('plus-btn')) {
                if (currentValue < 99) {
                    input.value = currentValue + 1;
                } else {
                    showQuantityTooltip(productId);
                }
            } else if (this.classList.contains('minus-btn')) {
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                }
            }
        });
    });

    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function() {
            const productId = this.id.split('-')[1];
            let value = parseInt(this.value);
            if (isNaN(value) || value < 1) {
                this.value = 1;
            } else if (value > 99) {
                this.value = 99;
                showQuantityTooltip(productId);
            }
        });
    });
}

function showQuantityTooltip(productId) {
    const tooltip = document.getElementById(`tooltip-${productId}`);
    const input = document.getElementById(`quantity-${productId}`);
    
    tooltip.classList.add('show');
    input.classList.add('shake-animation');

    setTimeout(() => {
        tooltip.classList.remove('show');
        input.classList.remove('shake-animation');
    }, 2000);
}

function showQuantityTooltip(productId) {
    const tooltip = document.getElementById(`tooltip-${productId}`);
    const input = document.getElementById(`quantity-${productId}`);
    
    tooltip.classList.add('show');
    input.classList.add('shake-animation');

    setTimeout(() => {
        tooltip.classList.remove('show');
        input.classList.remove('shake-animation');
    }, 2000);
}

// Actualizar badge y configurar botones al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge();
    setupQuantityButtons();
});
</script>

<?php include 'includes/footer.php'; ?>