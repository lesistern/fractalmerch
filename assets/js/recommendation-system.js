/**
 * Recommendation System - Frontend Implementation
 * Sistema de recomendaciones dinámico para e-commerce
 */

class RecommendationSystem {
    constructor() {
        this.apiBase = 'api/recommendations/';
        this.trackingEnabled = true;
        this.sessionId = this.getSessionId();
        this.userId = this.getUserId();
        this.viewTimer = null;
        this.viewStartTime = null;
        
        this.init();
    }
    
    init() {
        this.trackPageView();
        this.setupViewTracking();
        this.setupAddToCartTracking();
        this.loadRecommendations();
        this.setupLazyLoading();
    }
    
    // Obtener session ID
    getSessionId() {
        let sessionId = localStorage.getItem('session_id');
        if (!sessionId) {
            sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('session_id', sessionId);
        }
        return sessionId;
    }
    
    // Obtener user ID si está logueado
    getUserId() {
        // Puede venir de una variable global o meta tag
        return window.currentUserId || null;
    }
    
    // Trackear vista de página
    trackPageView() {
        const page = window.location.pathname;
        const productId = this.extractProductId();
        
        if (productId) {
            this.startViewTimer(productId);
        }
    }
    
    // Extraer product ID de la URL
    extractProductId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id') || urlParams.get('product_id');
    }
    
    // Iniciar timer de vista de producto
    startViewTimer(productId) {
        this.viewStartTime = Date.now();
        
        // Track vista inmediata
        this.trackEvent('product_view', {
            product_id: productId,
            source_page: this.getSourcePage()
        });
        
        // Timer para trackear duración
        this.viewTimer = setTimeout(() => {
            const duration = Math.floor((Date.now() - this.viewStartTime) / 1000);
            this.trackEvent('product_view_duration', {
                product_id: productId,
                duration: duration
            });
        }, 10000); // Track después de 10 segundos
    }
    
    // Obtener página de origen
    getSourcePage() {
        const page = window.location.pathname;
        if (page.includes('particulares')) return 'shop';
        if (page.includes('product-detail')) return 'product_detail';
        if (page.includes('customize')) return 'customizer';
        if (page === '/' || page.includes('index')) return 'home';
        return 'other';
    }
    
    // Setup tracking de vistas
    setupViewTracking() {
        // Track clicks en productos
        document.addEventListener('click', (e) => {
            const productLink = e.target.closest('[data-product-id]');
            if (productLink) {
                const productId = productLink.getAttribute('data-product-id');
                this.trackEvent('product_click', {
                    product_id: productId,
                    source: 'product_grid'
                });
            }
        });
        
        // Track scroll y tiempo en página
        let scrollDepth = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = Math.floor((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
            if (currentScroll > scrollDepth && currentScroll % 25 === 0) {
                scrollDepth = currentScroll;
                this.trackEvent('scroll_depth', { depth: scrollDepth });
            }
        });
    }
    
    // Setup tracking de carrito
    setupAddToCartTracking() {
        // Override función global addToCartWithVariants
        const originalAddToCart = window.addToCartWithVariants;
        window.addToCartWithVariants = (productId, productName, price, size, color) => {
            // Track agregado al carrito
            this.trackEvent('add_to_cart', {
                product_id: productId,
                product_name: productName,
                price: price,
                variant_details: { size, color }
            });
            
            // Llamar función original
            if (originalAddToCart) {
                originalAddToCart(productId, productName, price, size, color);
            }
        };
    }
    
    // Trackear evento
    async trackEvent(eventType, data) {
        if (!this.trackingEnabled) return;
        
        try {
            const payload = {
                event_type: eventType,
                session_id: this.sessionId,
                user_id: this.userId,
                timestamp: new Date().toISOString(),
                data: data
            };
            
            // Usar sendBeacon para mejor performance
            if (navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('tracking_data', JSON.stringify(payload));
                navigator.sendBeacon(this.apiBase + 'track.php', formData);
            } else {
                // Fallback a fetch
                fetch(this.apiBase + 'track.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
            }
        } catch (error) {
            console.warn('Tracking error:', error);
        }
    }
    
    // Cargar recomendaciones dinámicamente
    async loadRecommendations() {
        const containers = document.querySelectorAll('[data-recommendations]');
        
        for (const container of containers) {
            const type = container.getAttribute('data-recommendations');
            const productId = container.getAttribute('data-product-id');
            const limit = container.getAttribute('data-limit') || 4;
            
            try {
                const recommendations = await this.fetchRecommendations(type, {
                    product_id: productId,
                    limit: limit
                });
                
                this.renderRecommendations(container, recommendations, type);
            } catch (error) {
                console.warn(`Error loading ${type} recommendations:`, error);
            }
        }
    }
    
    // Fetch recomendaciones del backend
    async fetchRecommendations(type, params = {}) {
        const url = new URL(this.apiBase + 'get-recommendations.php', window.location.origin);
        url.searchParams.append('type', type);
        url.searchParams.append('session_id', this.sessionId);
        
        if (this.userId) {
            url.searchParams.append('user_id', this.userId);
        }
        
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.append(key, params[key]);
            }
        });
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch recommendations');
        
        return await response.json();
    }
    
    // Renderizar recomendaciones en el DOM
    renderRecommendations(container, recommendations, type) {
        if (!recommendations || recommendations.length === 0) {
            container.style.display = 'none';
            return;
        }
        
        const title = this.getRecommendationTitle(type);
        const productsHtml = recommendations.map(product => this.renderProductCard(product, type)).join('');
        
        container.innerHTML = `
            <div class="recommendations-section">
                <div class="recommendations-header">
                    <h3 class="recommendations-title">${title}</h3>
                    <div class="recommendations-subtitle">
                        ${this.getRecommendationSubtitle(type, recommendations.length)}
                    </div>
                </div>
                <div class="recommendations-grid" data-recommendation-type="${type}">
                    ${productsHtml}
                </div>
            </div>
        `;
        
        // Setup tracking para recomendaciones
        this.setupRecommendationTracking(container, type);
    }
    
    // Renderizar card de producto
    renderProductCard(product, recommendationType) {
        const rating = product.avg_rating ? parseFloat(product.avg_rating) : 0;
        const reviewCount = product.review_count || 0;
        const stars = this.renderStars(rating);
        const reason = product.reason || '';
        const price = parseFloat(product.price);
        
        return `
            <div class="recommendation-card" data-product-id="${product.id}" data-rec-type="${recommendationType}">
                <div class="rec-product-image">
                    <a href="product-detail.php?id=${product.id}">
                        <img src="${product.main_image_url || 'assets/images/products/default.svg'}" 
                             alt="${product.name}" 
                             loading="lazy"
                             onerror="this.src='assets/images/products/default.svg'">
                    </a>
                    ${product.confidence_score ? `<div class="confidence-badge">${Math.round(product.confidence_score * 100)}% match</div>` : ''}
                </div>
                
                <div class="rec-product-info">
                    <h4 class="rec-product-title">
                        <a href="product-detail.php?id=${product.id}">${product.name}</a>
                    </h4>
                    
                    <div class="rec-product-rating">
                        <div class="stars">${stars}</div>
                        <span class="rating-text">${rating.toFixed(1)} (${reviewCount})</span>
                    </div>
                    
                    <div class="rec-product-price">
                        <span class="price-current">$${price.toLocaleString('es-AR')}</span>
                    </div>
                    
                    ${reason ? `<div class="rec-reason">${reason}</div>` : ''}
                    
                    <div class="rec-product-actions">
                        <button class="btn-add-to-cart-rec" 
                                onclick="this.addToCartFromRecommendation(${product.id}, '${product.name}', ${price}, '${recommendationType}')">
                            <i class="fas fa-cart-plus"></i> Agregar
                        </button>
                        <a href="product-detail.php?id=${product.id}" class="btn-view-rec">
                            Ver más
                        </a>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Renderizar estrellas
    renderStars(rating) {
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
    
    // Obtener título de recomendación
    getRecommendationTitle(type) {
        const titles = {
            'frequently_bought_together': '🛒 Frecuentemente comprados juntos',
            'similar_products': '🔍 Productos similares',
            'personalized': '✨ Recomendado para ti',
            'trending': '🔥 Trending ahora',
            'price_based': '💰 En tu rango de precio',
            'seasonal': '🌟 Perfecto para esta temporada'
        };
        return titles[type] || 'Recomendaciones';
    }
    
    // Obtener subtítulo de recomendación
    getRecommendationSubtitle(type, count) {
        const subtitles = {
            'frequently_bought_together': `${count} productos que otros compraron con este`,
            'similar_products': `${count} productos que te pueden interesar`,
            'personalized': `Seleccionados especialmente para ti`,
            'trending': `Los ${count} más populares esta semana`,
            'price_based': `${count} alternativas en tu presupuesto`,
            'seasonal': `${count} productos perfectos para ahora`
        };
        return subtitles[type] || `${count} recomendaciones`;
    }
    
    // Setup tracking para recomendaciones
    setupRecommendationTracking(container, type) {
        // Track clicks en recomendaciones
        container.addEventListener('click', (e) => {
            const card = e.target.closest('.recommendation-card');
            if (card) {
                const productId = card.getAttribute('data-product-id');
                const recType = card.getAttribute('data-rec-type');
                
                this.trackEvent('recommendation_click', {
                    product_id: productId,
                    recommendation_type: recType,
                    position: Array.from(card.parentNode.children).indexOf(card)
                });
            }
        });
        
        // Track impresiones (cuando aparecen en viewport)
        this.setupImpressionTracking(container);
    }
    
    // Setup tracking de impresiones
    setupImpressionTracking(container) {
        const cards = container.querySelectorAll('.recommendation-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const card = entry.target;
                    const productId = card.getAttribute('data-product-id');
                    const recType = card.getAttribute('data-rec-type');
                    
                    this.trackEvent('recommendation_impression', {
                        product_id: productId,
                        recommendation_type: recType,
                        position: Array.from(card.parentNode.children).indexOf(card)
                    });
                    
                    observer.unobserve(card);
                }
            });
        }, { threshold: 0.5 });
        
        cards.forEach(card => observer.observe(card));
    }
    
    // Setup lazy loading de recomendaciones
    setupLazyLoading() {
        const lazyContainers = document.querySelectorAll('[data-recommendations-lazy]');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const container = entry.target;
                    const type = container.getAttribute('data-recommendations-lazy');
                    
                    // Convertir a carga normal
                    container.setAttribute('data-recommendations', type);
                    container.removeAttribute('data-recommendations-lazy');
                    
                    // Cargar recomendaciones
                    this.loadRecommendationForContainer(container);
                    
                    observer.unobserve(container);
                }
            });
        }, { rootMargin: '100px' });
        
        lazyContainers.forEach(container => observer.observe(container));
    }
    
    // Cargar recomendación para container específico
    async loadRecommendationForContainer(container) {
        const type = container.getAttribute('data-recommendations');
        const productId = container.getAttribute('data-product-id');
        const limit = container.getAttribute('data-limit') || 4;
        
        // Mostrar loading
        container.innerHTML = '<div class="recommendations-loading">Cargando recomendaciones...</div>';
        
        try {
            const recommendations = await this.fetchRecommendations(type, {
                product_id: productId,
                limit: limit
            });
            
            this.renderRecommendations(container, recommendations, type);
        } catch (error) {
            container.innerHTML = '<div class="recommendations-error">Error cargando recomendaciones</div>';
        }
    }
    
    // Función global para agregar desde recomendaciones
    addToCartFromRecommendation(productId, productName, price, recommendationType) {
        // Track conversión
        this.trackEvent('recommendation_conversion', {
            product_id: productId,
            recommendation_type: recommendationType,
            price: price
        });
        
        // Llamar función de carrito (sin variantes por defecto)
        if (window.cart && window.cart.addProduct) {
            window.cart.addProduct(productId, productName, price, 'M', 'Blanco');
        }
    }
}

// Inicializar sistema cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.recommendationSystem = new RecommendationSystem();
});

// Agregar función global para usar en onclick
window.addToCartFromRecommendation = function(productId, productName, price, recommendationType) {
    if (window.recommendationSystem) {
        window.recommendationSystem.addToCartFromRecommendation(productId, productName, price, recommendationType);
    }
};