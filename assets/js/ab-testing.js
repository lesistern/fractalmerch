/**
 * FractalMerch A/B Testing System
 * Inspired by Amazon, Shopify and best practices
 */

class ABTestManager {
    constructor() {
        this.tests = {
            // Checkout Flow Tests
            'checkout_steps': {
                variants: ['single_page', 'multi_step', 'progressive'],
                weights: [33, 34, 33],
                active: true
            },
            
            // CTA Button Tests  
            'cta_text': {
                variants: ['Comprar Ahora', 'Finalizar Pedido', 'Hacer Realidad'],
                weights: [40, 30, 30],
                active: true
            },
            
            // Trust Signals Position
            'trust_signals': {
                variants: ['badges_top', 'badges_sidebar', 'badges_bottom'],
                weights: [33, 34, 33],
                active: true
            },
            
            // Product Card Layout
            'product_layout': {
                variants: ['grid_classic', 'grid_compact', 'list_detailed'],
                weights: [50, 25, 25],
                active: true
            },
            
            // Pricing Display
            'price_format': {
                variants: ['large_bold', 'with_savings', 'installments'],
                weights: [40, 30, 30],
                active: true
            },
            
            // Header Navigation
            'nav_style': {
                variants: ['minimal', 'detailed', 'mega_menu'],
                weights: [50, 30, 20],
                active: true
            }
        };
        
        this.userId = this.getUserId();
        this.sessionId = this.getSessionId();
        this.init();
    }
    
    init() {
        this.loadUserTests();
        this.applyActiveTests();
        this.trackPageView();
    }
    
    /**
     * Get consistent user ID for test assignment
     */
    getUserId() {
        let userId = localStorage.getItem('ab_user_id');
        if (!userId) {
            userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('ab_user_id', userId);
        }
        return userId;
    }
    
    /**
     * Get session ID for current session
     */
    getSessionId() {
        let sessionId = sessionStorage.getItem('ab_session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('ab_session_id', sessionId);
        }
        return sessionId;
    }
    
    /**
     * Assign user to test variant using consistent hashing
     */
    getVariant(testName) {
        const test = this.tests[testName];
        if (!test || !test.active) {
            return test ? test.variants[0] : null;
        }
        
        // Create hash from user ID + test name for consistency
        const hashString = this.userId + testName;
        let hash = 0;
        for (let i = 0; i < hashString.length; i++) {
            const char = hashString.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        
        // Convert to positive number and get percentage
        const percentage = Math.abs(hash) % 100;
        
        // Assign based on weights
        let cumulativeWeight = 0;
        for (let i = 0; i < test.variants.length; i++) {
            cumulativeWeight += test.weights[i];
            if (percentage < cumulativeWeight) {
                return test.variants[i];
            }
        }
        
        // Fallback to first variant
        return test.variants[0];
    }
    
    /**
     * Load user's assigned tests from storage
     */
    loadUserTests() {
        this.userTests = JSON.parse(localStorage.getItem('ab_user_tests') || '{}');
        
        // Assign tests if not already assigned
        for (const testName in this.tests) {
            if (!this.userTests[testName]) {
                this.userTests[testName] = this.getVariant(testName);
            }
        }
        
        localStorage.setItem('ab_user_tests', JSON.stringify(this.userTests));
    }
    
    /**
     * Apply active tests to current page
     */
    applyActiveTests() {
        // Apply checkout steps test
        this.applyCheckoutStepsTest();
        
        // Apply CTA text test
        this.applyCTATextTest();
        
        // Apply trust signals test
        this.applyTrustSignalsTest();
        
        // Apply product layout test
        this.applyProductLayoutTest();
        
        // Apply price format test
        this.applyPriceFormatTest();
        
        // Apply navigation test
        this.applyNavigationTest();
    }
    
    /**
     * Apply checkout steps test
     */
    applyCheckoutStepsTest() {
        const variant = this.userTests['checkout_steps'];
        if (!variant || !document.querySelector('.checkout-container')) return;
        
        const checkoutContainer = document.querySelector('.checkout-container');
        checkoutContainer.setAttribute('data-checkout-variant', variant);
        
        switch (variant) {
            case 'single_page':
                checkoutContainer.classList.add('single-page-checkout');
                break;
            case 'multi_step':
                checkoutContainer.classList.add('multi-step-checkout');
                break;
            case 'progressive':
                checkoutContainer.classList.add('progressive-checkout');
                break;
        }
    }
    
    /**
     * Apply CTA text test
     */
    applyCTATextTest() {
        const variant = this.userTests['cta_text'];
        if (!variant) return;
        
        const ctaButtons = document.querySelectorAll('.cta-button, .add-to-cart-btn, .checkout-btn');
        ctaButtons.forEach(button => {
            if (button.getAttribute('data-ab-original-text')) return; // Already processed
            
            button.setAttribute('data-ab-original-text', button.textContent);
            
            switch (variant) {
                case 'Comprar Ahora':
                    if (button.classList.contains('add-to-cart-btn')) {
                        button.textContent = 'Comprar Ahora';
                    }
                    break;
                case 'Finalizar Pedido':
                    if (button.classList.contains('checkout-btn')) {
                        button.textContent = 'Finalizar Pedido';
                    }
                    break;
                case 'Hacer Realidad':
                    if (button.classList.contains('add-to-cart-btn')) {
                        button.textContent = 'Hacer Realidad';
                    }
                    break;
            }
        });
    }
    
    /**
     * Apply trust signals test
     */
    applyTrustSignalsTest() {
        const variant = this.userTests['trust_signals'];
        if (!variant) return;
        
        const trustSignals = document.querySelector('.trust-signals');
        if (trustSignals) {
            trustSignals.setAttribute('data-position', variant);
            trustSignals.className = trustSignals.className.replace(/position-\w+/, '');
            trustSignals.classList.add('position-' + variant.replace('badges_', ''));
        }
    }
    
    /**
     * Apply product layout test
     */
    applyProductLayoutTest() {
        const variant = this.userTests['product_layout'];
        if (!variant) return;
        
        const productGrid = document.querySelector('.product-grid');
        if (productGrid) {
            productGrid.setAttribute('data-layout', variant);
            productGrid.className = productGrid.className.replace(/layout-\w+/, '');
            productGrid.classList.add('layout-' + variant);
        }
    }\n    \n    /**\n     * Apply price format test\n     */\n    applyPriceFormatTest() {\n        const variant = this.userTests['price_format'];\n        if (!variant) return;\n        \n        const priceElements = document.querySelectorAll('.product-price, .price-display');\n        priceElements.forEach(price => {\n            price.setAttribute('data-format', variant);\n            price.classList.add('format-' + variant.replace('_', '-'));\n        });\n    }\n    \n    /**\n     * Apply navigation test\n     */\n    applyNavigationTest() {\n        const variant = this.userTests['nav_style'];\n        if (!variant) return;\n        \n        const navigation = document.querySelector('.nav-header');\n        if (navigation) {\n            navigation.setAttribute('data-nav-style', variant);\n            navigation.classList.add('nav-' + variant.replace('_', '-'));\n        }\n    }\n    \n    /**\n     * Track conversion event\n     */\n    trackConversion(testName, action, value = null) {\n        const variant = this.userTests[testName];\n        if (!variant) return;\n        \n        const conversionData = {\n            userId: this.userId,\n            sessionId: this.sessionId,\n            testName: testName,\n            variant: variant,\n            action: action,\n            value: value,\n            timestamp: Date.now(),\n            url: window.location.href,\n            userAgent: navigator.userAgent\n        };\n        \n        // Send to analytics\n        this.sendToAnalytics('ab_test_conversion', conversionData);\n        \n        // Store locally for backup\n        this.storeConversionLocally(conversionData);\n    }\n    \n    /**\n     * Track page view for test exposure\n     */\n    trackPageView() {\n        for (const testName in this.userTests) {\n            const variant = this.userTests[testName];\n            if (variant) {\n                const exposureData = {\n                    userId: this.userId,\n                    sessionId: this.sessionId,\n                    testName: testName,\n                    variant: variant,\n                    timestamp: Date.now(),\n                    url: window.location.href\n                };\n                \n                this.sendToAnalytics('ab_test_exposure', exposureData);\n            }\n        }\n    }\n    \n    /**\n     * Send data to analytics service\n     */\n    sendToAnalytics(eventType, data) {\n        // Google Analytics 4\n        if (typeof gtag !== 'undefined') {\n            gtag('event', eventType, {\n                custom_parameter_1: data.testName,\n                custom_parameter_2: data.variant,\n                custom_parameter_3: data.action || 'exposure',\n                value: data.value || 0\n            });\n        }\n        \n        // Send to our backend\n        fetch('/api/analytics/ab-test', {\n            method: 'POST',\n            headers: {\n                'Content-Type': 'application/json',\n                'X-Requested-With': 'XMLHttpRequest'\n            },\n            body: JSON.stringify({\n                type: eventType,\n                data: data\n            })\n        }).catch(error => {\n            console.warn('AB Test analytics failed:', error);\n        });\n    }\n    \n    /**\n     * Store conversion locally as backup\n     */\n    storeConversionLocally(data) {\n        const stored = JSON.parse(localStorage.getItem('ab_conversions') || '[]');\n        stored.push(data);\n        \n        // Keep only last 100 conversions\n        if (stored.length > 100) {\n            stored.splice(0, stored.length - 100);\n        }\n        \n        localStorage.setItem('ab_conversions', JSON.stringify(stored));\n    }\n    \n    /**\n     * Get test results for admin dashboard\n     */\n    getTestResults() {\n        return {\n            userId: this.userId,\n            tests: this.userTests,\n            conversions: JSON.parse(localStorage.getItem('ab_conversions') || '[]')\n        };\n    }\n    \n    /**\n     * Force variant for testing (admin only)\n     */\n    forceVariant(testName, variant) {\n        if (this.tests[testName] && this.tests[testName].variants.includes(variant)) {\n            this.userTests[testName] = variant;\n            localStorage.setItem('ab_user_tests', JSON.stringify(this.userTests));\n            this.applyActiveTests();\n            return true;\n        }\n        return false;\n    }\n    \n    /**\n     * Reset all tests (clear assignments)\n     */\n    resetTests() {\n        localStorage.removeItem('ab_user_tests');\n        localStorage.removeItem('ab_conversions');\n        localStorage.removeItem('ab_user_id');\n        sessionStorage.removeItem('ab_session_id');\n        window.location.reload();\n    }\n}\n\n// Auto-initialize A/B testing\nlet abTestManager;\n\ndocument.addEventListener('DOMContentLoaded', function() {\n    abTestManager = new ABTestManager();\n    \n    // Expose globally for tracking conversions\n    window.ABTest = {\n        track: (testName, action, value) => abTestManager.trackConversion(testName, action, value),\n        getResults: () => abTestManager.getTestResults(),\n        forceVariant: (testName, variant) => abTestManager.forceVariant(testName, variant),\n        reset: () => abTestManager.resetTests()\n    };\n});\n\n// Auto-track common conversions\ndocument.addEventListener('click', function(e) {\n    const target = e.target;\n    \n    // Track add to cart conversions\n    if (target.classList.contains('add-to-cart-btn') || target.closest('.add-to-cart-btn')) {\n        abTestManager?.trackConversion('cta_text', 'add_to_cart');\n        abTestManager?.trackConversion('product_layout', 'add_to_cart');\n        abTestManager?.trackConversion('price_format', 'add_to_cart');\n    }\n    \n    // Track checkout conversions\n    if (target.classList.contains('checkout-btn') || target.closest('.checkout-btn')) {\n        abTestManager?.trackConversion('checkout_steps', 'start_checkout');\n        abTestManager?.trackConversion('trust_signals', 'start_checkout');\n    }\n    \n    // Track navigation clicks\n    if (target.closest('.nav-header')) {\n        abTestManager?.trackConversion('nav_style', 'navigation_click');\n    }\n});\n\n// Track form submissions\ndocument.addEventListener('submit', function(e) {\n    const form = e.target;\n    \n    if (form.classList.contains('checkout-form')) {\n        abTestManager?.trackConversion('checkout_steps', 'complete_checkout', 1);\n    }\n    \n    if (form.classList.contains('contact-form')) {\n        abTestManager?.trackConversion('nav_style', 'contact_form', 1);\n    }\n});\n\n// Export for module systems\nif (typeof module !== 'undefined' && module.exports) {\n    module.exports = ABTestManager;\n}