/**
 * FractalMerch Advanced Personalization Engine
 * AI-powered user experience optimization
 */

class AdvancedPersonalizationEngine {
    constructor() {
        this.config = {
            enablePersonalization: true,
            enableAI: true,
            enableBehaviorTracking: true,
            enableGeoTargeting: true,
            enableTimeBasedOptimization: true,
            cookieLifetime: 365 * 24 * 60 * 60 * 1000, // 1 year
            sessionTimeout: 30 * 60 * 1000, // 30 minutes
        };
        
        this.userProfile = {
            id: null,
            segment: 'new_visitor',
            preferences: {},
            behavior: {
                pageViews: 0,
                timeOnSite: 0,
                cartInteractions: 0,
                searchQueries: [],
                clickHeatmap: {},
                conversionFunnel: [],
                lastVisit: null,
                sessionCount: 0
            },
            demographics: {
                location: null,
                device: null,
                browser: null,
                language: null,
                timezone: null
            },
            purchaseHistory: [],
            aiInsights: {
                buyingIntent: 'low',
                productInterest: [],
                priceSensitivity: 'medium',
                designPreference: 'modern',
                conversionProbability: 0.1
            }
        };
        
        this.personalizations = {
            hero: {
                headlines: {
                    high_intent: "¡Finaliza tu pedido personalizado!",
                    medium_intent: "Crea tu remera única en minutos",
                    low_intent: "Descubrí el arte de la personalización",
                    returning: "¡Bienvenido de vuelta! Nuevos diseños te esperan"
                },
                ctas: {
                    high_intent: "Terminar mi pedido",
                    medium_intent: "Personalizar ahora",
                    low_intent: "Explorar productos",
                    returning: "Ver novedades"
                }
            },
            products: {
                sorting: {
                    price_sensitive: 'price_low_to_high',
                    quality_focused: 'rating_high_to_low',
                    trend_follower: 'newest_first',
                    loyal_customer: 'recommended_first'
                },
                badges: {
                    price_sensitive: 'MEJOR PRECIO',
                    quality_focused: 'CALIDAD PREMIUM',
                    trend_follower: 'NUEVO',
                    eco_conscious: 'ECO-FRIENDLY'
                }
            },
            pricing: {
                display: {
                    price_sensitive: 'emphasize_savings',
                    quality_focused: 'emphasize_quality',
                    convenience_focused: 'emphasize_speed'
                }
            },
            messaging: {
                urgency: {
                    high_intent: "⚡ ¡Pocos diseños como este disponibles!",
                    medium_intent: "🎨 Más de 1000 clientes satisfechos",
                    low_intent: "✨ Únete a la comunidad creativa"
                }
            }
        };
        
        this.aiModels = {
            intentPrediction: {
                weights: {
                    timeOnPage: 0.3,
                    scrollDepth: 0.2,
                    clicksOnCTA: 0.25,
                    cartInteractions: 0.15,
                    returnVisitor: 0.1
                },
                thresholds: {
                    high: 0.7,
                    medium: 0.4,
                    low: 0.2
                }
            },
            segmentation: {
                rules: [
                    {
                        segment: 'high_value_prospect',
                        conditions: {
                            timeOnSite: { '>': 300000 }, // 5+ minutes
                            pageViews: { '>': 5 },
                            cartInteractions: { '>': 2 }
                        }
                    },
                    {
                        segment: 'price_sensitive',
                        conditions: {
                            clicksOnPrices: { '>': 3 },
                            timeOnPricingSection: { '>': 30000 }
                        }
                    },
                    {
                        segment: 'design_enthusiast',
                        conditions: {
                            timeOnCustomizer: { '>': 120000 }, // 2+ minutes
                            designAttempts: { '>': 1 }
                        }
                    }
                ]
            }
        };
        
        this.init();
    }
    
    init() {
        console.log('🤖 Advanced Personalization Engine initializing...');
        
        // Load user profile
        this.loadUserProfile();
        
        // Detect user context
        this.detectUserContext();
        
        // Initialize behavior tracking
        this.initBehaviorTracking();
        
        // Apply initial personalizations
        this.applyPersonalizations();
        
        // Start AI analysis
        this.startAIAnalysis();
        
        console.log('✅ Personalization Engine active');
    }
    
    /**
     * LOAD AND SAVE USER PROFILE
     */
    loadUserProfile() {
        try {
            const saved = localStorage.getItem('fractal_user_profile');
            if (saved) {
                const parsed = JSON.parse(saved);
                this.userProfile = { ...this.userProfile, ...parsed };
                this.userProfile.behavior.sessionCount++;
                this.userProfile.behavior.lastVisit = Date.now();
            } else {
                this.userProfile.id = this.generateUserId();
                this.userProfile.behavior.lastVisit = Date.now();
                this.userProfile.behavior.sessionCount = 1;
            }
            
            this.saveUserProfile();
        } catch (error) {
            console.warn('Failed to load user profile:', error);
        }
    }
    
    saveUserProfile() {
        try {
            localStorage.setItem('fractal_user_profile', JSON.stringify(this.userProfile));
        } catch (error) {
            console.warn('Failed to save user profile:', error);
        }
    }
    
    generateUserId() {
        return 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    /**
     * DETECT USER CONTEXT
     */
    detectUserContext() {
        // Device detection
        this.userProfile.demographics.device = this.detectDevice();
        this.userProfile.demographics.browser = this.detectBrowser();
        this.userProfile.demographics.language = navigator.language || 'es-AR';
        this.userProfile.demographics.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        
        // Geo detection (if available)
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.userProfile.demographics.location = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    this.applyGeoPersonalizations();
                },
                () => {
                    // Fallback to IP-based detection
                    this.detectLocationByIP();
                }
            );
        }
        
        // Referrer analysis
        this.analyzeReferrer();
    }
    
    detectDevice() {
        const ua = navigator.userAgent;
        if (/tablet|ipad|playbook|silk/i.test(ua)) return 'tablet';
        if (/mobile|iphone|ipod|android|blackberry|opera|mini|windows\sce|palm|smartphone|iemobile/i.test(ua)) return 'mobile';
        return 'desktop';
    }
    
    detectBrowser() {
        const ua = navigator.userAgent;
        if (ua.includes('Chrome')) return 'chrome';
        if (ua.includes('Firefox')) return 'firefox';
        if (ua.includes('Safari')) return 'safari';
        if (ua.includes('Edge')) return 'edge';
        return 'other';
    }
    
    analyzeReferrer() {
        const ref = document.referrer;
        if (!ref) {
            this.userProfile.segment = 'direct_visitor';
        } else if (ref.includes('google.com')) {
            this.userProfile.segment = 'google_visitor';
        } else if (ref.includes('facebook.com') || ref.includes('instagram.com')) {
            this.userProfile.segment = 'social_visitor';
        } else {
            this.userProfile.segment = 'referral_visitor';
        }
    }
    
    async detectLocationByIP() {
        try {
            const response = await fetch('https://ipapi.co/json/');
            const data = await response.json();
            this.userProfile.demographics.location = {
                country: data.country_name,
                city: data.city,
                region: data.region
            };
            this.applyGeoPersonalizations();
        } catch (error) {
            console.warn('IP geolocation failed:', error);
        }
    }
    
    /**
     * BEHAVIOR TRACKING
     */
    initBehaviorTracking() {
        this.trackPageViews();
        this.trackTimeOnSite();
        this.trackScrollBehavior();
        this.trackClickBehavior();
        this.trackFormInteractions();
        this.trackCartBehavior();
    }
    
    trackPageViews() {
        this.userProfile.behavior.pageViews++;
        
        // Track page sequence for funnel analysis
        const currentPage = window.location.pathname;
        this.userProfile.behavior.conversionFunnel.push({
            page: currentPage,
            timestamp: Date.now(),
            timeSpent: 0
        });
        
        // Limit funnel history to last 10 pages
        if (this.userProfile.behavior.conversionFunnel.length > 10) {
            this.userProfile.behavior.conversionFunnel = this.userProfile.behavior.conversionFunnel.slice(-10);
        }
    }
    
    trackTimeOnSite() {
        this.startTime = Date.now();
        
        // Track time when user leaves
        window.addEventListener('beforeunload', () => {
            const timeSpent = Date.now() - this.startTime;
            this.userProfile.behavior.timeOnSite += timeSpent;
            
            // Update last page time spent
            if (this.userProfile.behavior.conversionFunnel.length > 0) {
                const lastIndex = this.userProfile.behavior.conversionFunnel.length - 1;
                this.userProfile.behavior.conversionFunnel[lastIndex].timeSpent = timeSpent;
            }
            
            this.saveUserProfile();
        });
        
        // Also track visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                const timeSpent = Date.now() - this.startTime;
                this.userProfile.behavior.timeOnSite += timeSpent;
                this.saveUserProfile();
            } else {
                this.startTime = Date.now();
            }
        });
    }
    
    trackScrollBehavior() {
        let maxScroll = 0;
        
        window.addEventListener('scroll', () => {
            const scrollPercent = Math.round((window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100);
            maxScroll = Math.max(maxScroll, scrollPercent);
            
            // Track scroll milestones
            if (scrollPercent >= 25 && !this.userProfile.behavior.scrollMilestones?.includes(25)) {
                this.trackEvent('scroll_25_percent');
            }
            if (scrollPercent >= 50 && !this.userProfile.behavior.scrollMilestones?.includes(50)) {
                this.trackEvent('scroll_50_percent');
            }
            if (scrollPercent >= 75 && !this.userProfile.behavior.scrollMilestones?.includes(75)) {
                this.trackEvent('scroll_75_percent');
            }
            if (scrollPercent >= 90 && !this.userProfile.behavior.scrollMilestones?.includes(90)) {
                this.trackEvent('scroll_90_percent');
            }
        });
        
        // Save max scroll on unload
        window.addEventListener('beforeunload', () => {
            this.userProfile.behavior.maxScrollPercent = maxScroll;
        });
    }
    
    trackClickBehavior() {
        document.addEventListener('click', (e) => {
            const element = e.target;
            const tag = element.tagName.toLowerCase();
            const classes = element.className || '';
            const id = element.id || '';
            
            // Track specific element types
            if (tag === 'button' || classes.includes('btn') || classes.includes('cta')) {
                this.trackEvent('button_click', {
                    element: tag,
                    classes: classes,
                    id: id,
                    text: element.textContent?.trim().substring(0, 50)
                });
            }
            
            if (classes.includes('product') || element.closest('.product-card')) {
                this.trackEvent('product_click', {
                    element: 'product_card'
                });
            }
            
            if (classes.includes('price') || element.closest('.price')) {
                this.trackEvent('price_click');
                this.userProfile.behavior.priceClicks = (this.userProfile.behavior.priceClicks || 0) + 1;
            }
            
            // Track heatmap data
            const rect = element.getBoundingClientRect();
            const clickData = {
                x: e.clientX,
                y: e.clientY,
                element: tag,
                timestamp: Date.now()
            };
            
            if (!this.userProfile.behavior.clickHeatmap[window.location.pathname]) {
                this.userProfile.behavior.clickHeatmap[window.location.pathname] = [];
            }
            
            this.userProfile.behavior.clickHeatmap[window.location.pathname].push(clickData);
            
            // Limit heatmap data to last 50 clicks per page
            if (this.userProfile.behavior.clickHeatmap[window.location.pathname].length > 50) {
                this.userProfile.behavior.clickHeatmap[window.location.pathname] = 
                    this.userProfile.behavior.clickHeatmap[window.location.pathname].slice(-50);
            }
        });
    }
    
    trackFormInteractions() {
        document.addEventListener('focus', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                this.trackEvent('form_field_focus', {
                    field: e.target.name || e.target.id,
                    type: e.target.type
                });
            }
        });
        
        document.addEventListener('submit', (e) => {
            this.trackEvent('form_submit', {
                form: e.target.id || e.target.className
            });
        });
    }
    
    trackCartBehavior() {
        // Listen for cart events
        document.addEventListener('cart_item_added', (e) => {
            this.userProfile.behavior.cartInteractions++;
            this.trackEvent('cart_add', e.detail);
        });
        
        document.addEventListener('cart_item_removed', (e) => {
            this.userProfile.behavior.cartInteractions++;
            this.trackEvent('cart_remove', e.detail);
        });
        
        document.addEventListener('checkout_started', (e) => {
            this.trackEvent('checkout_start', e.detail);
        });
    }
    
    trackEvent(eventName, data = {}) {
        if (!this.userProfile.behavior.events) {
            this.userProfile.behavior.events = [];
        }
        
        this.userProfile.behavior.events.push({
            event: eventName,
            data: data,
            timestamp: Date.now(),
            page: window.location.pathname
        });
        
        // Limit events to last 100
        if (this.userProfile.behavior.events.length > 100) {
            this.userProfile.behavior.events = this.userProfile.behavior.events.slice(-100);
        }
        
        // Trigger AI analysis on significant events
        const significantEvents = ['cart_add', 'checkout_start', 'product_click'];
        if (significantEvents.includes(eventName)) {
            this.runAIAnalysis();
        }
    }
    
    /**
     * AI ANALYSIS AND PREDICTIONS
     */
    startAIAnalysis() {
        // Run initial analysis
        this.runAIAnalysis();
        
        // Run periodic analysis
        setInterval(() => {
            this.runAIAnalysis();
        }, 60000); // Every minute
    }
    
    runAIAnalysis() {
        this.predictBuyingIntent();
        this.analyzeProductInterest();
        this.assessPriceSensitivity();
        this.determineDesignPreference();
        this.calculateConversionProbability();
        this.updateUserSegment();
        
        // Apply AI-driven personalizations
        this.applyAIPersonalizations();
    }
    
    predictBuyingIntent() {
        const weights = this.aiModels.intentPrediction.weights;
        const thresholds = this.aiModels.intentPrediction.thresholds;
        
        let score = 0;
        
        // Time on current page
        const timeOnPage = Date.now() - this.startTime;
        score += (timeOnPage / 60000) * weights.timeOnPage; // Minutes
        
        // Scroll depth
        const scrollDepth = this.userProfile.behavior.maxScrollPercent || 0;
        score += (scrollDepth / 100) * weights.scrollDepth;
        
        // CTA clicks
        const ctaClicks = this.userProfile.behavior.events?.filter(e => e.event === 'button_click').length || 0;
        score += Math.min(ctaClicks / 3, 1) * weights.clicksOnCTA;
        
        // Cart interactions
        score += Math.min(this.userProfile.behavior.cartInteractions / 5, 1) * weights.cartInteractions;
        
        // Return visitor bonus
        if (this.userProfile.behavior.sessionCount > 1) {
            score += weights.returnVisitor;
        }
        
        // Determine intent level
        if (score >= thresholds.high) {
            this.userProfile.aiInsights.buyingIntent = 'high';
        } else if (score >= thresholds.medium) {
            this.userProfile.aiInsights.buyingIntent = 'medium';
        } else {
            this.userProfile.aiInsights.buyingIntent = 'low';
        }
        
        console.log(`🎯 Buying Intent: ${this.userProfile.aiInsights.buyingIntent} (score: ${score.toFixed(2)})`);
    }
    
    analyzeProductInterest() {
        const productClicks = this.userProfile.behavior.events?.filter(e => e.event === 'product_click') || [];
        const productViews = this.userProfile.behavior.conversionFunnel?.filter(p => p.page.includes('product')) || [];
        
        // Analyze which products get most attention
        const interests = {};
        
        productClicks.forEach(click => {
            // Extract product type from context
            // This would be enhanced with actual product data
            if (click.page.includes('remeras')) interests.remeras = (interests.remeras || 0) + 1;
            if (click.page.includes('buzos')) interests.buzos = (interests.buzos || 0) + 1;
            if (click.page.includes('tazas')) interests.tazas = (interests.tazas || 0) + 1;
        });
        
        this.userProfile.aiInsights.productInterest = Object.keys(interests).sort((a, b) => interests[b] - interests[a]);
    }
    
    assessPriceSensitivity() {
        const priceClicks = this.userProfile.behavior.priceClicks || 0;
        const totalClicks = this.userProfile.behavior.events?.length || 1;
        
        const priceClickRatio = priceClicks / totalClicks;
        
        if (priceClickRatio > 0.15) {
            this.userProfile.aiInsights.priceSensitivity = 'high';
        } else if (priceClickRatio > 0.05) {
            this.userProfile.aiInsights.priceSensitivity = 'medium';
        } else {
            this.userProfile.aiInsights.priceSensitivity = 'low';
        }
    }
    
    determineDesignPreference() {
        const timeOnCustomizer = this.userProfile.behavior.events?.filter(e => e.page.includes('customize')).length || 0;
        const timeOnGallery = this.userProfile.behavior.events?.filter(e => e.event === 'product_click').length || 0;
        
        if (timeOnCustomizer > timeOnGallery) {
            this.userProfile.aiInsights.designPreference = 'creative';
        } else if (timeOnGallery > 0) {
            this.userProfile.aiInsights.designPreference = 'template';
        } else {
            this.userProfile.aiInsights.designPreference = 'modern';
        }
    }
    
    calculateConversionProbability() {
        const factors = {
            intent: {
                high: 0.4,
                medium: 0.2,
                low: 0.05
            },
            engagement: Math.min(this.userProfile.behavior.timeOnSite / 300000, 1) * 0.3, // 5 min max
            returning: this.userProfile.behavior.sessionCount > 1 ? 0.2 : 0,
            cartActivity: Math.min(this.userProfile.behavior.cartInteractions / 3, 1) * 0.1
        };
        
        const intentScore = factors.intent[this.userProfile.aiInsights.buyingIntent] || 0;
        const probability = intentScore + factors.engagement + factors.returning + factors.cartActivity;
        
        this.userProfile.aiInsights.conversionProbability = Math.min(probability, 1);
    }
    
    updateUserSegment() {
        // Apply segmentation rules
        for (const rule of this.aiModels.segmentation.rules) {
            let matches = true;
            
            for (const [key, condition] of Object.entries(rule.conditions)) {
                const userValue = this.getUserMetric(key);
                
                for (const [operator, value] of Object.entries(condition)) {
                    if (operator === '>' && userValue <= value) matches = false;
                    if (operator === '<' && userValue >= value) matches = false;
                    if (operator === '=' && userValue !== value) matches = false;
                }
            }
            
            if (matches) {
                this.userProfile.segment = rule.segment;
                break;
            }
        }
        
        console.log(`👤 User Segment: ${this.userProfile.segment}`);
    }
    
    getUserMetric(key) {
        const metrics = {
            timeOnSite: this.userProfile.behavior.timeOnSite,
            pageViews: this.userProfile.behavior.pageViews,
            cartInteractions: this.userProfile.behavior.cartInteractions,
            clicksOnPrices: this.userProfile.behavior.priceClicks || 0,
            timeOnPricingSection: 0, // Would need specific tracking
            timeOnCustomizer: 0, // Would need specific tracking
            designAttempts: 0 // Would need specific tracking
        };
        
        return metrics[key] || 0;
    }
    
    /**
     * APPLY PERSONALIZATIONS
     */
    applyPersonalizations() {
        this.personalizeHero();
        this.personalizeProducts();
        this.personalizePricing();
        this.personalizeMessaging();
    }
    
    applyAIPersonalizations() {
        const intent = this.userProfile.aiInsights.buyingIntent;
        const segment = this.userProfile.segment;
        const probability = this.userProfile.aiInsights.conversionProbability;
        
        // High-intent personalizations
        if (intent === 'high' || probability > 0.7) {
            this.showUrgencyMessages();
            this.highlightBestSellers();
            this.showPersonalizedOffers();
        }
        
        // Segment-specific personalizations
        if (segment === 'price_sensitive') {
            this.emphasizeSavings();
            this.showDiscountBadges();
        }
        
        if (segment === 'design_enthusiast') {
            this.highlightCustomizer();
            this.showDesignInspiration();
        }
        
        this.saveUserProfile();
    }
    
    personalizeHero() {
        const intent = this.userProfile.aiInsights.buyingIntent;
        const isReturning = this.userProfile.behavior.sessionCount > 1;
        
        const heroHeadline = document.querySelector('.hero-headline');
        const heroCTA = document.querySelector('.cta-primary');
        
        if (heroHeadline && heroCTA) {
            const headlines = this.personalizations.hero.headlines;
            const ctas = this.personalizations.hero.ctas;
            
            let headlineKey = intent;
            let ctaKey = intent;
            
            if (isReturning) {
                headlineKey = 'returning';
                ctaKey = 'returning';
            }
            
            if (headlines[headlineKey]) {
                heroHeadline.textContent = headlines[headlineKey];
            }
            
            if (ctas[ctaKey]) {
                heroCTA.textContent = ctas[ctaKey];
            }
        }
    }
    
    personalizeProducts() {
        const sensitivity = this.userProfile.aiInsights.priceSensitivity;
        const productCards = document.querySelectorAll('.product-card-mini');
        
        productCards.forEach(card => {
            const badge = this.personalizations.products.badges[sensitivity];
            if (badge) {
                this.addProductBadge(card, badge);
            }
        });
    }
    
    personalizePricing() {
        const display = this.userProfile.aiInsights.priceSensitivity;
        const priceElements = document.querySelectorAll('.price');
        
        priceElements.forEach(priceElement => {
            if (display === 'high') {
                this.addSavingsIndicator(priceElement);
            }
        });
    }
    
    personalizeMessaging() {
        const intent = this.userProfile.aiInsights.buyingIntent;
        const messages = this.personalizations.messaging.urgency;
        
        if (messages[intent]) {
            this.showPersonalizedMessage(messages[intent]);
        }
    }
    
    /**
     * PERSONALIZATION HELPERS
     */
    addProductBadge(card, badgeText) {
        // Avoid duplicate badges
        if (card.querySelector('.personalized-badge')) return;
        
        const badge = document.createElement('div');
        badge.className = 'personalized-badge';
        badge.textContent = badgeText;
        badge.style.cssText = `
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(45deg, var(--fractal-orange), var(--fractal-teal));
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            z-index: 10;
        `;
        
        const imageContainer = card.querySelector('.product-image');
        if (imageContainer) {
            imageContainer.style.position = 'relative';
            imageContainer.appendChild(badge);
        }
    }
    
    addSavingsIndicator(priceElement) {
        if (priceElement.querySelector('.savings-indicator')) return;
        
        const savings = document.createElement('span');
        savings.className = 'savings-indicator';
        savings.textContent = ' ¡Ahorrás $500!';
        savings.style.cssText = `
            color: var(--fractal-teal);
            font-size: 0.9rem;
            font-weight: bold;
            margin-left: 8px;
        `;
        
        priceElement.appendChild(savings);
    }
    
    showPersonalizedMessage(message) {
        // Avoid duplicate messages
        if (document.querySelector('.personalized-message')) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'personalized-message';
        messageDiv.innerHTML = message;
        messageDiv.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--fractal-gradient-light);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 500;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            animation: slideInRight 0.5s ease-out;
        `;
        
        document.body.appendChild(messageDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
    
    showUrgencyMessages() {
        // Implementation for urgency messaging
    }
    
    highlightBestSellers() {
        // Implementation for highlighting popular products
    }
    
    showPersonalizedOffers() {
        // Implementation for personalized offers
    }
    
    emphasizeSavings() {
        // Implementation for emphasizing savings
    }
    
    showDiscountBadges() {
        // Implementation for discount badges
    }
    
    highlightCustomizer() {
        // Implementation for highlighting customizer
    }
    
    showDesignInspiration() {
        // Implementation for design inspiration
    }
    
    applyGeoPersonalizations() {
        // Implementation for geo-based personalizations
    }
    
    /**
     * PUBLIC API
     */
    getPersonalizationData() {
        return {
            userProfile: this.userProfile,
            activePersonalizations: this.getActivePersonalizations()
        };
    }
    
    getActivePersonalizations() {
        return {
            segment: this.userProfile.segment,
            buyingIntent: this.userProfile.aiInsights.buyingIntent,
            conversionProbability: this.userProfile.aiInsights.conversionProbability,
            productInterest: this.userProfile.aiInsights.productInterest
        };
    }
    
    exportUserData() {
        return {
            profile: this.userProfile,
            timestamp: Date.now(),
            version: '1.0'
        };
    }
    
    clearUserData() {
        localStorage.removeItem('fractal_user_profile');
        this.userProfile = this.getDefaultProfile();
        console.log('🗑️ User data cleared');
    }
    
    getDefaultProfile() {
        return {
            id: this.generateUserId(),
            segment: 'new_visitor',
            preferences: {},
            behavior: {
                pageViews: 0,
                timeOnSite: 0,
                cartInteractions: 0,
                searchQueries: [],
                clickHeatmap: {},
                conversionFunnel: [],
                lastVisit: null,
                sessionCount: 0
            },
            demographics: {
                location: null,
                device: null,
                browser: null,
                language: null,
                timezone: null
            },
            purchaseHistory: [],
            aiInsights: {
                buyingIntent: 'low',
                productInterest: [],
                priceSensitivity: 'medium',
                designPreference: 'modern',
                conversionProbability: 0.1
            }
        };
    }
}

// Auto-initialize
window.addEventListener('DOMContentLoaded', () => {
    if (window.personalizationEngine) return;
    
    window.personalizationEngine = new AdvancedPersonalizationEngine();
    
    // Expose API for debugging
    window.getPersonalizationData = () => window.personalizationEngine.getPersonalizationData();
    window.clearPersonalizationData = () => window.personalizationEngine.clearUserData();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdvancedPersonalizationEngine;
}