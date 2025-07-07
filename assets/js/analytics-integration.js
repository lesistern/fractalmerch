/**
 * ANALYTICS INTEGRATION - Integración del Tracker en páginas específicas
 * Configura tracking específico según la página actual
 * Author: Claude Data Scientist AI
 * Version: 1.0
 */

class AnalyticsIntegration {
    constructor() {
        this.currentPage = this.detectCurrentPage();
        this.tracker = null;
        this.init();
    }

    detectCurrentPage() {
        const path = window.location.pathname;
        const search = window.location.search;
        
        if (path.includes('particulares.php')) return 'products';
        if (path.includes('product-detail.php')) return 'product-detail';
        if (path.includes('checkout.php')) return 'checkout';
        if (path.includes('customize-shirt.php')) return 'shirt-designer';
        if (path.includes('empresas.php')) return 'business';
        if (path.includes('admin/')) return 'admin';
        if (path.includes('index.php') || path === '/' || path.endsWith('/proyecto/')) return 'home';
        
        return 'other';
    }

    async init() {
        // Esperar a que el tracker esté disponible
        await this.waitForTracker();
        
        // Configurar tracking específico según la página
        this.setupPageSpecificTracking();
        
        // Configurar eventos globales
        this.setupGlobalTracking();
        
        console.log(`🎯 Analytics Integration initialized for page: ${this.currentPage}`);
    }

    async waitForTracker() {
        return new Promise((resolve) => {
            const checkTracker = () => {
                if (window.analyticsTracker) {
                    this.tracker = window.analyticsTracker;
                    resolve();
                } else {
                    setTimeout(checkTracker, 100);
                }
            };
            checkTracker();
        });
    }

    setupPageSpecificTracking() {
        switch (this.currentPage) {
            case 'home':
                this.setupHomeTracking();
                break;
            case 'products':
                this.setupProductsTracking();
                break;
            case 'product-detail':
                this.setupProductDetailTracking();
                break;
            case 'checkout':
                this.setupCheckoutTracking();
                break;
            case 'shirt-designer':
                this.setupShirtDesignerTracking();
                break;
            case 'business':
                this.setupBusinessTracking();
                break;
            default:
                this.setupDefaultTracking();
        }
    }

    // =====================================
    // TRACKING ESPECÍFICO POR PÁGINA
    // =====================================

    setupHomeTracking() {
        // Track hero slider interactions
        this.trackHeroSliderInteractions();
        
        // Track CTA clicks
        this.trackCTAClicks();
        
        // Track section visibility
        this.trackSectionVisibility(['hero-particulares', 'hero-empresas']);
        
        // Exit intent para homepage
        this.setupExitIntentForHome();
    }

    setupProductsTracking() {
        // Track product card views
        this.trackProductCardViews();
        
        // Track add to cart events
        this.trackAddToCartEvents();
        
        // Track cart modal interactions
        this.trackCartModalInteractions();
        
        // Track bundle kit visibility and interactions
        this.trackBundleKitInteractions();
        
        // Track shipping progress bar
        this.trackShippingProgressBar();
    }

    setupProductDetailTracking() {
        // Track product variant selections
        this.trackVariantSelections();
        
        // Track image gallery interactions
        this.trackImageGalleryInteractions();
        
        // Track quantity changes
        this.trackQuantityChanges();
        
        // Track related products views
        this.trackRelatedProductsViews();
    }

    setupCheckoutTracking() {
        // Track checkout step completions
        this.trackCheckoutSteps();
        
        // Track payment method selections
        this.trackPaymentMethodSelections();
        
        // Track shipping method selections
        this.trackShippingMethodSelections();
        
        // Track form field interactions
        this.trackCheckoutFormInteractions();
        
        // Track checkout abandonment
        this.trackCheckoutAbandonment();
    }

    setupShirtDesignerTracking() {
        // Track design tool usage
        this.trackDesignToolUsage();
        
        // Track image uploads
        this.trackImageUploads();
        
        // Track design actions (rotate, scale, move)
        this.trackDesignActions();
        
        // Track design completion and add to cart
        this.trackDesignCompletion();
    }

    setupBusinessTracking() {
        // Track B2B form interactions
        this.trackBusinessFormInteractions();
        
        // Track service section views
        this.trackServiceSectionViews();
        
        // Track contact form submissions
        this.trackContactFormSubmissions();
    }

    setupDefaultTracking() {
        // Basic page tracking for other pages
        this.trackBasicPageMetrics();
    }

    // =====================================
    // TRACKING METHODS ESPECÍFICOS
    // =====================================

    trackHeroSliderInteractions() {
        // Track slider navigation clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.hero-nav-dot') || e.target.closest('.hero-nav-arrow')) {
                const side = e.target.closest('.hero-left') ? 'empresas' : 'particulares';
                const slideIndex = Array.from(e.target.parentNode.children).indexOf(e.target);
                
                this.tracker.trackEvent('hero_slider_navigation', {
                    side: side,
                    slideIndex: slideIndex,
                    interactionType: e.target.classList.contains('hero-nav-dot') ? 'dot' : 'arrow',
                    timestamp: Date.now()
                });
            }
        });

        // Track auto-advance completions
        let autoAdvanceCount = { empresas: 0, particulares: 0 };
        
        const observeSliderChanges = () => {
            const empresasSlider = document.querySelector('.hero-left .active');
            const particularesSlider = document.querySelector('.hero-right .active');
            
            // Simple tracking of slider position changes
            setInterval(() => {
                // Check if slides have auto-advanced
                const currentEmpresasSlide = document.querySelector('.hero-left .slide.active');
                const currentParticularesSlide = document.querySelector('.hero-right .slide.active');
                
                if (currentEmpresasSlide && currentParticularesSlide) {
                    // Track engagement with current slides
                    this.tracker.trackEvent('hero_slide_view', {
                        empresasSlide: Array.from(currentEmpresasSlide.parentNode.children).indexOf(currentEmpresasSlide),
                        particularesSlide: Array.from(currentParticularesSlide.parentNode.children).indexOf(currentParticularesSlide),
                        timestamp: Date.now()
                    });
                }
            }, 5000); // Check every 5 seconds (slider auto-advance interval)
        };

        observeSliderChanges();
    }

    trackCTAClicks() {
        document.addEventListener('click', (e) => {
            const target = e.target;
            
            // Track main CTA buttons
            if (target.matches('.hero-cta, .cta-button, .btn-primary')) {
                const ctaText = target.textContent.trim();
                const ctaLocation = target.closest('.hero-left') ? 'empresas' : 
                                 target.closest('.hero-right') ? 'particulares' : 'other';
                
                this.tracker.trackEvent('cta_click', {
                    ctaText: ctaText,
                    location: ctaLocation,
                    href: target.href || '',
                    timestamp: Date.now()
                });
            }
        });
    }

    trackProductCardViews() {
        const productCards = document.querySelectorAll('.product-card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const productName = entry.target.querySelector('.product-name')?.textContent || 'Unknown';
                    const productPrice = entry.target.querySelector('.price-main')?.textContent || 'Unknown';
                    
                    this.tracker.trackEvent('product_card_view', {
                        productName: productName,
                        productPrice: productPrice,
                        viewDuration: Date.now(),
                        timestamp: Date.now()
                    });
                    
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        productCards.forEach(card => observer.observe(card));
    }

    trackAddToCartEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.add-to-cart, .btn-add-cart')) {
                const productCard = e.target.closest('.product-card, .product-detail');
                const productName = productCard?.querySelector('.product-name, .product-title')?.textContent || 'Unknown';
                const productPrice = productCard?.querySelector('.price-main')?.textContent || 'Unknown';
                
                this.tracker.trackEvent('add_to_cart', {
                    productName: productName,
                    productPrice: productPrice,
                    source: this.currentPage,
                    timestamp: Date.now()
                });
            }
        });
    }

    trackCartModalInteractions() {
        // Track cart modal open
        document.addEventListener('click', (e) => {
            if (e.target.matches('.cart-btn, .show-cart')) {
                this.tracker.trackEvent('cart_modal_open', {
                    cartValue: this.tracker.getCurrentCartValue(),
                    itemCount: this.tracker.getCurrentCartItems().length,
                    timestamp: Date.now()
                });
            }
        });

        // Track cart modal close
        document.addEventListener('click', (e) => {
            if (e.target.matches('.cart-close, .cart-backdrop')) {
                this.tracker.trackEvent('cart_modal_close', {
                    cartValue: this.tracker.getCurrentCartValue(),
                    timeOpen: Date.now() - (window.cartOpenTime || Date.now()),
                    timestamp: Date.now()
                });
            }
        });

        // Track quantity changes in cart
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quantity-btn, .qty-increase, .qty-decrease')) {
                const action = e.target.classList.contains('qty-increase') || 
                              e.target.textContent.includes('+') ? 'increase' : 'decrease';
                
                this.tracker.trackEvent('cart_quantity_change', {
                    action: action,
                    oldCartValue: this.tracker.getCurrentCartValue(),
                    timestamp: Date.now()
                });
            }
        });
    }

    trackBundleKitInteractions() {
        // Track bundle kit section visibility
        const bundleSection = document.querySelector('.bundle-kit, .home-office-bundle, [data-bundle]');
        if (bundleSection) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.tracker.trackEvent('bundle_kit_section_view', {
                            viewTime: Date.now(),
                            timestamp: Date.now()
                        });
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.3 });

            observer.observe(bundleSection);
        }

        // Track bundle interactions
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-bundle-item], .bundle-product')) {
                const bundleItem = e.target.closest('[data-bundle-item], .bundle-product');
                const itemName = bundleItem.querySelector('.product-name, .item-name')?.textContent || 'Unknown';
                
                this.tracker.trackEvent('bundle_item_click', {
                    itemName: itemName,
                    timestamp: Date.now()
                });
            }
        });
    }

    trackShippingProgressBar() {
        const progressBar = document.querySelector('.shipping-progress-bar, [data-shipping-progress]');
        if (progressBar) {
            // Track progress bar visibility
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.tracker.trackEvent('shipping_progress_visible', {
                            cartValue: this.tracker.getCurrentCartValue(),
                            progressPercentage: this.calculateShippingProgress(),
                            timestamp: Date.now()
                        });
                    }
                });
            }, { threshold: 0.5 });

            observer.observe(progressBar);

            // Track progress updates
            this.monitorShippingProgressUpdates();
        }
    }

    monitorShippingProgressUpdates() {
        let lastProgress = this.calculateShippingProgress();
        
        const checkProgress = () => {
            const currentProgress = this.calculateShippingProgress();
            if (currentProgress !== lastProgress) {
                this.tracker.trackEvent('shipping_progress_update', {
                    oldProgress: lastProgress,
                    newProgress: currentProgress,
                    cartValue: this.tracker.getCurrentCartValue(),
                    timestamp: Date.now()
                });
                lastProgress = currentProgress;
            }
        };

        setInterval(checkProgress, 2000);
    }

    calculateShippingProgress() {
        const cartValue = this.tracker.getCurrentCartValue();
        const freeShippingThreshold = 15000; // $15,000
        return Math.min(100, (cartValue / freeShippingThreshold) * 100);
    }

    trackVariantSelections() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.variant-option, .color-option, .size-option')) {
                const variantType = e.target.classList.contains('color-option') ? 'color' : 'size';
                const variantValue = e.target.textContent || e.target.getAttribute('data-value') || 'Unknown';
                
                this.tracker.trackEvent('product_variant_selection', {
                    variantType: variantType,
                    variantValue: variantValue,
                    productId: this.getProductIdFromUrl(),
                    timestamp: Date.now()
                });
            }
        });
    }

    trackImageGalleryInteractions() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.thumbnail-img, .gallery-thumb')) {
                const imageIndex = Array.from(e.target.parentNode.children).indexOf(e.target);
                
                this.tracker.trackEvent('product_image_view', {
                    imageIndex: imageIndex,
                    productId: this.getProductIdFromUrl(),
                    timestamp: Date.now()
                });
            }
        });

        // Track zoom interactions
        document.addEventListener('click', (e) => {
            if (e.target.matches('.zoom-trigger, .main-image')) {
                this.tracker.trackEvent('product_image_zoom', {
                    productId: this.getProductIdFromUrl(),
                    timestamp: Date.now()
                });
            }
        });
    }

    trackQuantityChanges() {
        document.addEventListener('change', (e) => {
            if (e.target.matches('.quantity-input, input[name="quantity"]')) {
                const newQuantity = parseInt(e.target.value) || 1;
                
                this.tracker.trackEvent('product_quantity_change', {
                    newQuantity: newQuantity,
                    productId: this.getProductIdFromUrl(),
                    timestamp: Date.now()
                });
            }
        });
    }

    trackCheckoutSteps() {
        // Track step navigation
        document.addEventListener('click', (e) => {
            if (e.target.matches('.step-indicator, .checkout-step')) {
                const stepNumber = e.target.getAttribute('data-step') || 
                                Array.from(e.target.parentNode.children).indexOf(e.target) + 1;
                
                this.tracker.trackEvent('checkout_step_navigation', {
                    stepNumber: stepNumber,
                    cartValue: this.tracker.getCurrentCartValue(),
                    timestamp: Date.now()
                });
            }
        });

        // Track step completion (form validation success)
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.matches('.checkout-form, .step-form')) {
                const stepNumber = form.getAttribute('data-step') || '1';
                
                this.tracker.trackEvent('checkout_step_completed', {
                    stepNumber: stepNumber,
                    cartValue: this.tracker.getCurrentCartValue(),
                    timestamp: Date.now()
                });
            }
        });
    }

    trackPaymentMethodSelections() {
        document.addEventListener('change', (e) => {
            if (e.target.matches('input[name="payment_method"], .payment-option')) {
                const paymentMethod = e.target.value || e.target.getAttribute('data-method') || 'Unknown';
                
                this.tracker.trackEvent('payment_method_selection', {
                    paymentMethod: paymentMethod,
                    cartValue: this.tracker.getCurrentCartValue(),
                    timestamp: Date.now()
                });
            }
        });
    }

    trackShippingMethodSelections() {
        document.addEventListener('change', (e) => {
            if (e.target.matches('input[name="shipping_method"], .shipping-option')) {
                const shippingMethod = e.target.value || e.target.getAttribute('data-method') || 'Unknown';
                
                this.tracker.trackEvent('shipping_method_selection', {
                    shippingMethod: shippingMethod,
                    cartValue: this.tracker.getCurrentCartValue(),
                    timestamp: Date.now()
                });
            }
        });
    }

    trackDesignToolUsage() {
        // Track tool selections in shirt designer
        document.addEventListener('click', (e) => {
            if (e.target.matches('.design-tool, .tool-btn')) {
                const toolName = e.target.getAttribute('data-tool') || 
                               e.target.textContent.trim() || 'Unknown';
                
                this.tracker.trackEvent('design_tool_used', {
                    toolName: toolName,
                    timestamp: Date.now()
                });
            }
        });
    }

    trackImageUploads() {
        // Track image uploads in designer
        document.addEventListener('change', (e) => {
            if (e.target.matches('input[type="file"]') && e.target.files.length > 0) {
                const fileCount = e.target.files.length;
                const fileTypes = Array.from(e.target.files).map(f => f.type);
                
                this.tracker.trackEvent('design_image_upload', {
                    fileCount: fileCount,
                    fileTypes: fileTypes,
                    timestamp: Date.now()
                });
            }
        });
    }

    trackDesignActions() {
        // Track design manipulation actions
        document.addEventListener('click', (e) => {
            if (e.target.matches('.design-action, .rotate-btn, .scale-btn, .delete-btn')) {
                const action = e.target.getAttribute('data-action') || 
                              e.target.className.split('-')[0] || 'Unknown';
                
                this.tracker.trackEvent('design_action', {
                    action: action,
                    timestamp: Date.now()
                });
            }
        });
    }

    trackSectionVisibility(sectionIds) {
        sectionIds.forEach(sectionId => {
            const section = document.getElementById(sectionId);
            if (section) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.tracker.trackEvent('section_view', {
                                sectionId: sectionId,
                                viewTime: Date.now(),
                                timestamp: Date.now()
                            });
                        }
                    });
                }, { threshold: 0.3 });

                observer.observe(section);
            }
        });
    }

    setupExitIntentForHome() {
        // Enhanced exit intent specifically for homepage
        let exitIntentShown = false;
        
        document.addEventListener('mouseleave', (e) => {
            if (e.clientY <= 0 && !exitIntentShown) {
                this.tracker.trackEvent('exit_intent_trigger_home', {
                    timeOnPage: Date.now() - this.tracker.startTime,
                    scrollDepth: Math.round((window.scrollY / document.body.scrollHeight) * 100),
                    timestamp: Date.now()
                });
                exitIntentShown = true;
            }
        });
    }

    // =====================================
    // TRACKING GLOBAL
    // =====================================

    setupGlobalTracking() {
        // Track scroll depth
        this.trackScrollDepth();
        
        // Track time on page milestones
        this.trackTimeOnPageMilestones();
        
        // Track form interactions
        this.trackFormInteractions();
        
        // Track external link clicks
        this.trackExternalLinks();
        
        // Track search interactions
        this.trackSearchInteractions();
    }

    trackScrollDepth() {
        let maxScrollDepth = 0;
        let scrollMilestones = [25, 50, 75, 90, 100];
        let reachedMilestones = [];

        const trackScroll = () => {
            const scrollDepth = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
            
            if (scrollDepth > maxScrollDepth) {
                maxScrollDepth = scrollDepth;
                
                // Check for milestone reached
                scrollMilestones.forEach(milestone => {
                    if (scrollDepth >= milestone && !reachedMilestones.includes(milestone)) {
                        reachedMilestones.push(milestone);
                        
                        this.tracker.trackEvent('scroll_milestone', {
                            milestone: milestone,
                            page: this.currentPage,
                            timeToReach: Date.now() - this.tracker.startTime,
                            timestamp: Date.now()
                        });
                    }
                });
            }
        };

        window.addEventListener('scroll', trackScroll);
    }

    trackTimeOnPageMilestones() {
        const milestones = [30, 60, 120, 300, 600]; // 30s, 1m, 2m, 5m, 10m
        
        milestones.forEach(seconds => {
            setTimeout(() => {
                this.tracker.trackEvent('time_on_page_milestone', {
                    milestone: seconds,
                    page: this.currentPage,
                    scrollDepth: Math.round((window.scrollY / document.body.scrollHeight) * 100),
                    timestamp: Date.now()
                });
            }, seconds * 1000);
        });
    }

    trackFormInteractions() {
        // Track form focus events
        document.addEventListener('focus', (e) => {
            if (e.target.matches('input, textarea, select')) {
                this.tracker.trackEvent('form_field_focus', {
                    fieldName: e.target.name || e.target.id || 'unknown',
                    fieldType: e.target.type || e.target.tagName.toLowerCase(),
                    page: this.currentPage,
                    timestamp: Date.now()
                });
            }
        }, true);

        // Track form submissions
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const formId = form.id || form.className || 'unknown';
            
            this.tracker.trackEvent('form_submission', {
                formId: formId,
                page: this.currentPage,
                timestamp: Date.now()
            });
        });
    }

    trackExternalLinks() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.includes(window.location.hostname)) {
                this.tracker.trackEvent('external_link_click', {
                    url: link.href,
                    text: link.textContent.trim().slice(0, 50),
                    page: this.currentPage,
                    timestamp: Date.now()
                });
            }
        });
    }

    trackSearchInteractions() {
        // Track search input focus and interactions
        const searchInputs = document.querySelectorAll('input[type="search"], .search-input, input[name*="search"]');
        
        searchInputs.forEach(input => {
            input.addEventListener('focus', () => {
                this.tracker.trackEvent('search_focus', {
                    page: this.currentPage,
                    timestamp: Date.now()
                });
            });

            let searchTimeout;
            input.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (input.value.length >= 3) {
                        this.tracker.trackEvent('search_query', {
                            queryLength: input.value.length,
                            hasResults: this.checkSearchResults(),
                            page: this.currentPage,
                            timestamp: Date.now()
                        });
                    }
                }, 1000);
            });
        });
    }

    // =====================================
    // UTILIDADES
    // =====================================

    getProductIdFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id') || urlParams.get('product_id') || 'unknown';
    }

    checkSearchResults() {
        // Simple check for search results visibility
        const resultsContainers = document.querySelectorAll('.search-results, .results-container, .search-dropdown');
        return Array.from(resultsContainers).some(container => 
            container.style.display !== 'none' && container.children.length > 0
        );
    }

    // API pública para tracking manual
    trackCustomEvent(eventName, eventData = {}) {
        if (this.tracker) {
            this.tracker.trackEvent(eventName, {
                ...eventData,
                page: this.currentPage,
                timestamp: Date.now()
            });
        }
    }

    // Método para obtener métricas de la página actual
    getPageMetrics() {
        if (this.tracker) {
            const allMetrics = this.tracker.getMetrics();
            const pageEvents = this.tracker.getEvents().filter(event => 
                event.data.page === this.currentPage
            );
            
            return {
                page: this.currentPage,
                metrics: allMetrics,
                pageEvents: pageEvents,
                summary: this.generatePageSummary(pageEvents)
            };
        }
        return null;
    }

    generatePageSummary(pageEvents) {
        return {
            totalEvents: pageEvents.length,
            uniqueEventTypes: [...new Set(pageEvents.map(e => e.name))],
            sessionDuration: pageEvents.length > 0 ? 
                Math.max(...pageEvents.map(e => e.timestamp)) - Math.min(...pageEvents.map(e => e.timestamp)) : 0,
            mostCommonEvent: this.getMostCommonEvent(pageEvents)
        };
    }

    getMostCommonEvent(events) {
        const eventCounts = {};
        events.forEach(event => {
            eventCounts[event.name] = (eventCounts[event.name] || 0) + 1;
        });
        
        return Object.keys(eventCounts).reduce((a, b) => 
            eventCounts[a] > eventCounts[b] ? a : b
        );
    }
}

// Inicialización automática
window.AnalyticsIntegration = AnalyticsIntegration;

// Auto-inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    if (!window.analyticsIntegration) {
        window.analyticsIntegration = new AnalyticsIntegration();
    }
});

// Export para uso en otros archivos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AnalyticsIntegration;
}