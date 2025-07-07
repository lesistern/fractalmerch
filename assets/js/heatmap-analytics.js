/**
 * FractalMerch Heatmap & User Analytics System
 * Advanced user behavior tracking with Microsoft Clarity and Hotjar integration
 */

class HeatmapAnalytics {
    constructor(config = {}) {
        this.config = {
            clarityId: config.clarityId || null,
            hotjarId: config.hotjarId || null,
            recordingSampleRate: config.recordingSampleRate || 10, // 10% of sessions
            heatmapSampleRate: config.heatmapSampleRate || 25, // 25% of sessions
            enableClickTracking: config.enableClickTracking !== false,
            enableScrollTracking: config.enableScrollTracking !== false,
            enableFormTracking: config.enableFormTracking !== false,
            enableErrorTracking: config.enableErrorTracking !== false,
            enableCustomEvents: config.enableCustomEvents !== false,
            enableGDPRCompliance: config.enableGDPRCompliance !== false,
            maxSessionDuration: config.maxSessionDuration || 30 * 60 * 1000, // 30 minutes
            ...config
        };
        
        this.sessionData = {
            sessionId: this.generateSessionId(),
            startTime: Date.now(),
            userId: this.getUserId(),
            isRecording: false,
            isHeatmapActive: false,
            events: [],
            userInteractions: [],
            errorEvents: [],
            performanceMetrics: {},
            customAttributes: {}
        };
        
        this.trackingEnabled = false;
        this.gdprConsent = null;
        
        this.init();
    }
    
    init() {
        console.log('HeatmapAnalytics init() started');
        
        // Check GDPR consent first
        this.checkGDPRConsent();
        console.log('GDPR consent checked, trackingEnabled:', this.trackingEnabled);
        
        // Initialize tracking if consent is given
        if (this.trackingEnabled) {
            console.log('Initializing tracking services...');
            this.initializeServices();
            this.setupCustomTracking();
            this.setupEventListeners();
            this.startSessionRecording();
        }
        
        // Always setup GDPR consent handler
        this.setupGDPRHandler();
        console.log('HeatmapAnalytics init() completed');
    }
    
    /**
     * Check GDPR consent status
     */
    checkGDPRConsent() {
        if (!this.config.enableGDPRCompliance) {
            this.trackingEnabled = true;
            return;
        }
        
        const consent = localStorage.getItem('analytics_consent');
        this.gdprConsent = consent ? JSON.parse(consent) : null;
        
        if (this.gdprConsent && this.gdprConsent.analytics === true) {
            this.trackingEnabled = true;
        } else {
            this.showConsentBanner();
        }
    }
    
    /**
     * Show GDPR consent banner
     */
    showConsentBanner() {
        console.log('Showing GDPR consent banner');
        
        if (document.getElementById('gdpr-consent-banner')) {
            console.log('Banner already exists, skipping...');
            return;
        }
        
        const banner = document.createElement('div');
        banner.id = 'gdpr-consent-banner';
        banner.style.cssText = `
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            background: #2c3e50;
            color: white;
            padding: 15px;
            z-index: 999999;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.4);
            border-top: 3px solid #3498db;
            pointer-events: auto;
        `;
        
        banner.innerHTML = `
            <div class="consent-banner-container" style="max-width: 1200px; margin: 0 auto; padding: 0 10px;">
                <div class="consent-content" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                    <div class="consent-text" style="flex: 1; min-width: 280px; margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <span style="font-size: 20px;">🍪</span>
                            <strong style="font-size: 16px; color: #ecf0f1;">Análisis de experiencia</strong>
                        </div>
                        <p style="margin: 0; font-size: 13px; color: #bdc3c7; line-height: 1.4;">
                            Utilizamos herramientas como Microsoft Clarity y Hotjar para mejorar tu experiencia de navegación y entender cómo interactúas con nuestro sitio. 
                            <a href="/privacy-policy.php" style="color: #3498db; text-decoration: underline;">Más información</a>
                        </p>
                    </div>
                    <div class="consent-buttons" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                        <button id="consent-accept" style="background: #27ae60; color: white; border: none; padding: 14px 28px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; min-width: 100px; transition: all 0.3s ease; touch-action: manipulation; user-select: none; -webkit-tap-highlight-color: rgba(0,0,0,0);">
                            Aceptar
                        </button>
                        <button id="consent-decline" style="background: transparent; color: #bdc3c7; border: 1px solid #bdc3c7; padding: 14px 24px; border-radius: 6px; cursor: pointer; font-size: 14px; min-width: 100px; transition: all 0.3s ease; touch-action: manipulation; user-select: none; -webkit-tap-highlight-color: rgba(0,0,0,0);">
                            Rechazar
                        </button>
                    </div>
                </div>
            </div>
            
            <style>
                @media (max-width: 768px) {
                    #gdpr-consent-banner {
                        padding: 16px 12px !important;
                        font-size: 13px !important;
                    }
                    .consent-banner-container {
                        padding: 0 8px !important;
                    }
                    .consent-content {
                        flex-direction: column !important;
                        align-items: stretch !important;
                        gap: 16px !important;
                    }
                    .consent-text {
                        min-width: 100% !important;
                        margin-bottom: 0 !important;
                        text-align: center !important;
                    }
                    .consent-text p {
                        font-size: 13px !important;
                        line-height: 1.5 !important;
                    }
                    .consent-buttons {
                        justify-content: center !important;
                        width: 100% !important;
                        gap: 12px !important;
                    }
                    .consent-buttons button {
                        flex: 1 !important;
                        max-width: 160px !important;
                        padding: 16px 20px !important;
                        font-size: 15px !important;
                        min-height: 50px !important;
                        border-radius: 8px !important;
                    }
                }
                
                @media (max-width: 480px) {
                    #gdpr-consent-banner {
                        padding: 20px 16px !important;
                    }
                    .consent-buttons {
                        flex-direction: column !important;
                        gap: 12px !important;
                    }
                    .consent-buttons button {
                        width: 100% !important;
                        max-width: none !important;
                        padding: 18px 24px !important;
                        font-size: 16px !important;
                        min-height: 54px !important;
                    }
                }
                
                #consent-accept:hover {
                    background: #219a52 !important;
                    transform: translateY(-1px) !important;
                }
                
                #consent-decline:hover {
                    background: #34495e !important;
                    color: white !important;
                    border-color: #34495e !important;
                }
            </style>
        `;
        
        document.body.appendChild(banner);
        
        // Event listeners for consent buttons with proper error handling
        const self = this; // Guardar referencia a this
        
        // Wait for DOM to be ready and then attach event listeners
        setTimeout(() => {
            const acceptBtn = document.getElementById('consent-accept');
            const declineBtn = document.getElementById('consent-decline');
            
            if (acceptBtn) {
                acceptBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Consent accept button clicked');
                    self.setConsent({ analytics: true, heatmaps: true, recordings: true });
                    if (banner && banner.parentNode) {
                        banner.remove();
                    }
                });
                
                // Add touch event for mobile
                acceptBtn.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Consent accept touched (mobile)');
                    self.setConsent({ analytics: true, heatmaps: true, recordings: true });
                    if (banner && banner.parentNode) {
                        banner.remove();
                    }
                });
            }
            
            if (declineBtn) {
                declineBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Consent decline button clicked');
                    self.setConsent({ analytics: false, heatmaps: false, recordings: false });
                    if (banner && banner.parentNode) {
                        banner.remove();
                    }
                });
                
                // Add touch event for mobile
                declineBtn.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Consent decline touched (mobile)');
                    self.setConsent({ analytics: false, heatmaps: false, recordings: false });
                    if (banner && banner.parentNode) {
                        banner.remove();
                    }
                });
            }
        }, 100);
    }
    
    /**
     * Show detailed consent settings
     */
    showConsentSettings() {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        `;
        
        modal.innerHTML = `
            <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: 20px;">
                <h3 style="margin-top: 0; color: #2c3e50;">Configuración de Privacidad</h3>
                <p style="color: #7f8c8d; margin-bottom: 25px;">
                    Controla qué datos nos permites recopilar para mejorar tu experiencia.
                </p>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; margin-bottom: 15px; cursor: pointer;">
                        <input type="checkbox" id="analytics-consent" checked style="margin-right: 10px;">
                        <div>
                            <strong>Análisis básico</strong><br>
                            <small style="color: #7f8c8d;">Métricas de rendimiento y uso general del sitio</small>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: center; margin-bottom: 15px; cursor: pointer;">
                        <input type="checkbox" id="heatmaps-consent" checked style="margin-right: 10px;">
                        <div>
                            <strong>Mapas de calor</strong><br>
                            <small style="color: #7f8c8d;">Dónde haces clic y cómo navegas por las páginas</small>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: center; margin-bottom: 15px; cursor: pointer;">
                        <input type="checkbox" id="recordings-consent" style="margin-right: 10px;">
                        <div>
                            <strong>Grabaciones de sesión</strong><br>
                            <small style="color: #7f8c8d;">Grabaciones anónimas de tu navegación (sin datos personales)</small>
                        </div>
                    </label>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button id="settings-cancel" style="background: #95a5a6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                        Cancelar
                    </button>
                    <button id="settings-save" style="background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        Guardar Preferencias
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const self = this; // Guardar referencia a this
        
        document.getElementById('settings-cancel').onclick = () => modal.remove();
        document.getElementById('settings-save').onclick = () => {
            const consent = {
                analytics: document.getElementById('analytics-consent').checked,
                heatmaps: document.getElementById('heatmaps-consent').checked,
                recordings: document.getElementById('recordings-consent').checked
            };
            self.setConsent(consent);
            modal.remove();
            document.getElementById('gdpr-consent-banner')?.remove();
        };
    }
    
    /**
     * Set user consent preferences
     */
    setConsent(consent) {
        console.log('Setting consent:', consent);
        
        this.gdprConsent = {
            ...consent,
            timestamp: Date.now(),
            version: '1.0'
        };
        
        localStorage.setItem('analytics_consent', JSON.stringify(this.gdprConsent));
        console.log('Consent saved to localStorage:', this.gdprConsent);
        
        if (consent.analytics || consent.heatmaps || consent.recordings) {
            this.trackingEnabled = true;
            console.log('Tracking enabled, initializing services...');
            this.initializeServices();
            this.setupCustomTracking();
            this.setupEventListeners();
            this.startSessionRecording();
        } else {
            this.trackingEnabled = false;
            console.log('Tracking disabled');
        }
    }
    
    /**
     * Initialize third-party analytics services
     */
    initializeServices() {
        console.log('initializeServices() called, config:', this.config);
        
        // Microsoft Clarity
        if (this.config.clarityId && this.gdprConsent?.analytics) {
            console.log('Initializing Microsoft Clarity with ID:', this.config.clarityId);
            this.initMicrosoftClarity();
        } else if (!this.config.clarityId && this.gdprConsent?.analytics) {
            console.log('📊 Microsoft Clarity ID not configured. To enable Clarity:');
            console.log('1. Sign up at https://clarity.microsoft.com/');
            console.log('2. Add your site and get the Clarity ID');
            console.log('3. Set clarityId in the config object');
        }
        
        // Hotjar
        if (this.config.hotjarId && this.gdprConsent?.heatmaps) {
            console.log('Initializing Hotjar with ID:', this.config.hotjarId);
            this.initHotjar();
        } else if (!this.config.hotjarId && this.gdprConsent?.heatmaps) {
            console.log('🔥 Hotjar ID not configured. To enable Hotjar:');
            console.log('1. Sign up at https://www.hotjar.com/');
            console.log('2. Create a new site and get the Site ID');
            console.log('3. Set hotjarId in the config object');
        }
        
        // Custom tracking (always enabled if consent given)
        if (this.gdprConsent?.analytics || this.gdprConsent?.heatmaps) {
            this.initCustomTracking();
        }
    }
    
    /**
     * Initialize Microsoft Clarity
     */
    initMicrosoftClarity() {
        if (window.clarity) return; // Already loaded
        
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", this.config.clarityId);
        
        // Set custom user attributes
        setTimeout(() => {
            if (window.clarity) {
                clarity("set", "user_segment", this.getUserSegment());
                clarity("set", "session_type", this.getSessionType());
                clarity("set", "device_type", this.getDeviceType());
            }
        }, 1000);
    }
    
    /**
     * Initialize Hotjar
     */
    initHotjar() {
        if (window.hj) return; // Already loaded
        
        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid: this.config.hotjarId, hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
        
        // Set Hotjar attributes
        setTimeout(() => {
            if (window.hj) {
                hj('identify', this.sessionData.userId, {
                    user_segment: this.getUserSegment(),
                    session_type: this.getSessionType(),
                    device_type: this.getDeviceType()
                });
            }
        }, 1000);
    }
    
    /**
     * Initialize custom tracking
     */
    initCustomTracking() {
        // Determine if this session should be recorded
        const shouldRecord = Math.random() * 100 < this.config.recordingSampleRate;
        const shouldHeatmap = Math.random() * 100 < this.config.heatmapSampleRate;
        
        this.sessionData.isRecording = shouldRecord && this.gdprConsent?.recordings;
        this.sessionData.isHeatmapActive = shouldHeatmap && this.gdprConsent?.heatmaps;
        
        console.log('HeatmapAnalytics initialized:', {
            recording: this.sessionData.isRecording,
            heatmap: this.sessionData.isHeatmapActive,
            consent: this.gdprConsent
        });
    }
    
    /**
     * Setup custom event listeners
     */
    setupEventListeners() {
        if (!this.trackingEnabled) return;
        
        // Click tracking
        if (this.config.enableClickTracking) {
            this.setupClickTracking();
        }
        
        // Scroll tracking
        if (this.config.enableScrollTracking) {
            this.setupScrollTracking();
        }
        
        // Form tracking
        if (this.config.enableFormTracking) {
            this.setupFormTracking();
        }
        
        // Error tracking
        if (this.config.enableErrorTracking) {
            this.setupErrorTracking();
        }
        
        // Performance tracking
        this.setupPerformanceTracking();
        
        // Custom events
        if (this.config.enableCustomEvents) {
            this.setupCustomEventTracking();
        }
    }
    
    /**
     * Setup custom tracking methods
     */
    setupCustomTracking() {
        if (!this.trackingEnabled) return;
        
        console.log('Setting up custom tracking methods...');
        
        // Setup advanced user interaction tracking
        this.setupAdvancedInteractionTracking();
        
        // Setup performance monitoring
        this.setupPerformanceTracking();
        
        // Setup conversion tracking
        this.setupConversionTracking();
        
        // Setup A/B testing integration
        this.setupABTestingIntegration();
        
        console.log('Custom tracking methods setup completed');
    }
    
    /**
     * Setup advanced interaction tracking
     */
    setupAdvancedInteractionTracking() {
        // Track element visibility
        if ('IntersectionObserver' in window) {
            const visibilityObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.trackCustomEvent('element_visible', {
                            element: this.getElementSelector(entry.target),
                            page: window.location.pathname,
                            timestamp: Date.now()
                        });
                    }
                });
            }, { threshold: 0.5 });
            
            // Observe important elements
            document.querySelectorAll('[data-track-visibility]').forEach(el => {
                visibilityObserver.observe(el);
            });
        }
        
        // Track copy/paste events
        document.addEventListener('copy', () => {
            this.trackCustomEvent('text_copied', {
                page: window.location.pathname,
                timestamp: Date.now()
            });
        });
        
        document.addEventListener('paste', (event) => {
            this.trackCustomEvent('text_pasted', {
                target: this.getElementSelector(event.target),
                page: window.location.pathname,
                timestamp: Date.now()
            });
        });
    }
    
    /**
     * Setup performance tracking
     */
    setupPerformanceTracking() {
        // Track page load performance
        window.addEventListener('load', () => {
            setTimeout(() => {
                if (window.performance && window.performance.timing) {
                    const timing = window.performance.timing;
                    const performanceData = {
                        page_load_time: timing.loadEventEnd - timing.navigationStart,
                        dom_ready_time: timing.domContentLoadedEventEnd - timing.navigationStart,
                        first_byte_time: timing.responseStart - timing.navigationStart,
                        page: window.location.pathname,
                        timestamp: Date.now()
                    };
                    
                    this.trackCustomEvent('page_performance', performanceData);
                }
            }, 1000);
        });
        
        // Track Core Web Vitals if available
        if ('PerformanceObserver' in window) {
            try {
                new PerformanceObserver((list) => {
                    list.getEntries().forEach((entry) => {
                        this.trackCustomEvent('core_web_vital', {
                            metric: entry.name,
                            value: entry.value,
                            rating: entry.value < 100 ? 'good' : entry.value < 300 ? 'needs-improvement' : 'poor',
                            page: window.location.pathname,
                            timestamp: Date.now()
                        });
                    });
                }).observe({ entryTypes: ['largest-contentful-paint', 'first-input', 'cumulative-layout-shift'] });
            } catch (e) {
                console.warn('Core Web Vitals tracking not supported');
            }
        }
    }
    
    /**
     * Setup conversion tracking
     */
    setupConversionTracking() {
        // Track form submissions as conversions
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (form.matches('[data-conversion-form]')) {
                this.trackCustomEvent('conversion_form_submit', {
                    form_id: form.id || 'unnamed',
                    form_name: form.getAttribute('data-conversion-form'),
                    page: window.location.pathname,
                    timestamp: Date.now()
                });
            }
        });
        
        // Track button clicks as micro-conversions
        document.addEventListener('click', (event) => {
            if (event.target.matches('[data-conversion-button]')) {
                this.trackCustomEvent('micro_conversion', {
                    button_id: event.target.id || 'unnamed',
                    button_text: event.target.textContent?.trim(),
                    conversion_type: event.target.getAttribute('data-conversion-button'),
                    page: window.location.pathname,
                    timestamp: Date.now()
                });
            }
        });
    }
    
    /**
     * Setup A/B testing integration
     */
    setupABTestingIntegration() {
        // Track A/B test variations
        const trackABTestVariation = (testName, variation) => {
            this.trackCustomEvent('ab_test_variation', {
                test_name: testName,
                variation: variation,
                page: window.location.pathname,
                timestamp: Date.now()
            });
        };
        
        // Expose method globally for A/B testing frameworks
        window.trackABTestVariation = trackABTestVariation;
        
        // Auto-detect common A/B testing tools
        setTimeout(() => {
            // Google Optimize
            if (window.gtag && window.google_optimize) {
                this.trackCustomEvent('ab_testing_tool_detected', {
                    tool: 'google_optimize',
                    timestamp: Date.now()
                });
            }
            
            // Optimizely
            if (window.optimizely) {
                this.trackCustomEvent('ab_testing_tool_detected', {
                    tool: 'optimizely',
                    timestamp: Date.now()
                });
            }
        }, 2000);
    }
    
    /**
     * Setup click tracking
     */
    setupClickTracking() {
        document.addEventListener('click', (event) => {
            if (!this.sessionData.isHeatmapActive) return;
            
            const clickData = {
                type: 'click',
                timestamp: Date.now(),
                x: event.clientX,
                y: event.clientY,
                pageX: event.pageX,
                pageY: event.pageY,
                target: this.getElementSelector(event.target),
                text: event.target.textContent?.trim().substring(0, 100),
                tagName: event.target.tagName,
                className: event.target.className,
                id: event.target.id,
                url: window.location.href,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            };
            
            this.trackEvent('click', clickData);
            
            // Track special elements
            if (event.target.matches('button, a, [role="button"]')) {
                this.trackCustomEvent('button_click', {
                    button_text: clickData.text,
                    button_type: event.target.type || 'link',
                    page: window.location.pathname
                });
            }
        }, { passive: true });
    }
    
    /**
     * Setup scroll tracking
     */
    setupScrollTracking() {
        let maxScroll = 0;
        let scrollDepthMarkers = [25, 50, 75, 90, 100];
        let triggeredMarkers = new Set();
        
        const trackScroll = () => {
            if (!this.sessionData.isHeatmapActive) return;
            
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const documentHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = Math.round((scrollTop / documentHeight) * 100);
            
            maxScroll = Math.max(maxScroll, scrollPercent);
            
            // Track scroll depth milestones
            scrollDepthMarkers.forEach(marker => {
                if (scrollPercent >= marker && !triggeredMarkers.has(marker)) {
                    triggeredMarkers.add(marker);
                    this.trackCustomEvent('scroll_depth', {
                        depth_percent: marker,
                        page: window.location.pathname,
                        time_to_depth: Date.now() - this.sessionData.startTime
                    });
                }
            });
        };
        
        window.addEventListener('scroll', trackScroll, { passive: true });
        
        // Track scroll on page unload
        window.addEventListener('beforeunload', () => {
            this.trackCustomEvent('final_scroll_depth', {
                max_scroll_percent: maxScroll,
                page: window.location.pathname,
                session_duration: Date.now() - this.sessionData.startTime
            });
        });
    }
    
    /**
     * Setup form tracking
     */
    setupFormTracking() {
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!form.matches('form')) return;
            
            const formData = {
                form_id: form.id || 'unnamed',
                form_action: form.action,
                form_method: form.method,
                field_count: form.elements.length,
                page: window.location.pathname,
                timestamp: Date.now()
            };
            
            this.trackCustomEvent('form_submit', formData);
        });
        
        // Track form field interactions
        document.addEventListener('focusin', (event) => {
            if (!event.target.matches('input, textarea, select')) return;
            
            this.trackCustomEvent('form_field_focus', {
                field_name: event.target.name || event.target.id || 'unnamed',
                field_type: event.target.type || event.target.tagName.toLowerCase(),
                page: window.location.pathname
            });
        });
    }
    
    /**
     * Setup error tracking
     */
    setupErrorTracking() {
        // JavaScript errors
        window.addEventListener('error', (event) => {
            const errorData = {
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                stack: event.error?.stack,
                page: window.location.href,
                timestamp: Date.now()
            };
            
            this.sessionData.errorEvents.push(errorData);
            this.trackCustomEvent('javascript_error', errorData);
        });
        
        // Promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            const errorData = {
                reason: event.reason?.toString(),
                stack: event.reason?.stack,
                page: window.location.href,
                timestamp: Date.now()
            };
            
            this.sessionData.errorEvents.push(errorData);
            this.trackCustomEvent('promise_rejection', errorData);
        });
    }
    
    /**
     * Setup performance tracking
     */
    setupPerformanceTracking() {
        // Track page load performance
        window.addEventListener('load', () => {
            setTimeout(() => {
                const navigation = performance.getEntriesByType('navigation')[0];
                if (navigation) {
                    const performanceData = {
                        page_load_time: navigation.loadEventEnd - navigation.loadEventStart,
                        dns_lookup_time: navigation.domainLookupEnd - navigation.domainLookupStart,
                        tcp_connect_time: navigation.connectEnd - navigation.connectStart,
                        server_response_time: navigation.responseEnd - navigation.responseStart,
                        dom_content_loaded: navigation.domContentLoadedEventEnd - navigation.navigationStart,
                        page: window.location.pathname
                    };
                    
                    this.sessionData.performanceMetrics = performanceData;
                    this.trackCustomEvent('page_performance', performanceData);
                }
            }, 0);
        });
        
        // Track Core Web Vitals if available
        if ('web-vital' in window) {
            ['CLS', 'FID', 'FCP', 'LCP', 'TTFB'].forEach(metric => {
                window.webVitals?.getCLS?.(this.handleWebVital.bind(this));
                window.webVitals?.getFID?.(this.handleWebVital.bind(this));
                window.webVitals?.getFCP?.(this.handleWebVital.bind(this));
                window.webVitals?.getLCP?.(this.handleWebVital.bind(this));
                window.webVitals?.getTTFB?.(this.handleWebVital.bind(this));
            });
        }
    }
    
    /**
     * Handle Web Vitals data
     */
    handleWebVital(metric) {
        this.trackCustomEvent('web_vital', {
            name: metric.name,
            value: metric.value,
            delta: metric.delta,
            id: metric.id,
            page: window.location.pathname
        });
    }
    
    /**
     * Setup custom event tracking for e-commerce events
     */
    setupCustomEventTracking() {
        // Track product views
        window.addEventListener('product_view', (event) => {
            this.trackCustomEvent('product_view', event.detail);
        });
        
        // Track cart events
        window.addEventListener('add_to_cart', (event) => {
            this.trackCustomEvent('add_to_cart', event.detail);
        });
        
        window.addEventListener('remove_from_cart', (event) => {
            this.trackCustomEvent('remove_from_cart', event.detail);
        });
        
        // Track checkout events
        window.addEventListener('checkout_step', (event) => {
            this.trackCustomEvent('checkout_step', event.detail);
        });
        
        // Track search events
        window.addEventListener('search', (event) => {
            this.trackCustomEvent('search', event.detail);
        });
    }
    
    /**
     * Start session recording
     */
    startSessionRecording() {
        if (!this.sessionData.isRecording) return;
        
        // Custom session recording logic would go here
        // This is a placeholder for the recording functionality
        console.log('Session recording started for session:', this.sessionData.sessionId);
        
        // Track session milestones
        setTimeout(() => {
            this.trackCustomEvent('session_milestone', {
                milestone: '30_seconds',
                session_duration: 30000
            });
        }, 30000);
        
        setTimeout(() => {
            this.trackCustomEvent('session_milestone', {
                milestone: '2_minutes',
                session_duration: 120000
            });
        }, 120000);
        
        setTimeout(() => {
            this.trackCustomEvent('session_milestone', {
                milestone: '5_minutes',
                session_duration: 300000
            });
        }, 300000);
    }
    
    /**
     * Track custom event
     */
    trackEvent(type, data) {
        if (!this.trackingEnabled) return;
        
        const event = {
            type,
            data,
            timestamp: Date.now(),
            sessionId: this.sessionData.sessionId,
            userId: this.sessionData.userId,
            url: window.location.href
        };
        
        this.sessionData.events.push(event);
        
        // Send to backend if recording is enabled
        if (this.sessionData.isRecording) {
            this.sendEventToBackend(event);
        }
    }
    
    /**
     * Track custom event with third-party services
     */
    trackCustomEvent(eventName, properties = {}) {
        if (!this.trackingEnabled) return;
        
        // Send to Microsoft Clarity
        if (window.clarity && this.gdprConsent?.analytics) {
            clarity('event', eventName, properties);
        }
        
        // Send to Hotjar
        if (window.hj && this.gdprConsent?.heatmaps) {
            hj('event', eventName);
        }
        
        // Track in our own system
        this.trackEvent('custom', { eventName, properties });
    }
    
    /**
     * Send event to backend
     */
    async sendEventToBackend(event) {
        try {
            await fetch('/api/analytics/heatmap-event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(event)
            });
        } catch (error) {
            console.warn('Failed to send analytics event:', error);
        }
    }
    
    /**
     * Get element selector
     */
    getElementSelector(element) {
        if (element.id) return `#${element.id}`;
        if (element.className) return `.${element.className.split(' ')[0]}`;
        return element.tagName.toLowerCase();
    }
    
    /**
     * Get user segment
     */
    getUserSegment() {
        // Determine user segment based on behavior
        const cartData = JSON.parse(localStorage.getItem('cart') || '[]');
        const hasAccount = localStorage.getItem('user_token');
        const visitCount = parseInt(localStorage.getItem('visit_count') || '1');
        
        if (hasAccount && visitCount > 5) return 'returning_customer';
        if (cartData.length > 0 && !hasAccount) return 'high_intent_visitor';
        if (visitCount === 1) return 'new_visitor';
        return 'returning_visitor';
    }
    
    /**
     * Get session type
     */
    getSessionType() {
        const referrer = document.referrer;
        if (!referrer) return 'direct';
        if (referrer.includes('google.com')) return 'organic_search';
        if (referrer.includes('facebook.com') || referrer.includes('instagram.com')) return 'social';
        return 'referral';
    }
    
    /**
     * Get device type
     */
    getDeviceType() {
        const width = window.innerWidth;
        if (width < 768) return 'mobile';
        if (width < 1024) return 'tablet';
        return 'desktop';
    }
    
    /**
     * Generate unique session ID
     */
    generateSessionId() {
        return 'hs_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    /**
     * Get or create user ID
     */
    getUserId() {
        let userId = localStorage.getItem('heatmap_user_id');
        if (!userId) {
            userId = 'hu_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('heatmap_user_id', userId);
        }
        return userId;
    }
    
    /**
     * Setup GDPR consent handler
     */
    setupGDPRHandler() {
        // Allow users to change consent preferences
        window.updateAnalyticsConsent = (newConsent) => {
            this.setConsent(newConsent);
        };
        
        // Add consent management to privacy page
        if (window.location.pathname.includes('privacy')) {
            this.addConsentManagement();
        }
    }
    
    /**
     * Add consent management UI to privacy page
     */
    addConsentManagement() {
        const container = document.querySelector('#privacy-controls, .privacy-controls, #consent-management');
        if (!container) return;
        
        const consentUI = document.createElement('div');
        consentUI.innerHTML = `
            <div class="consent-management">
                <h3>Configuración de Analytics</h3>
                <p>Controla qué datos recopilamos para mejorar tu experiencia:</p>
                
                <div class="consent-options">
                    <label>
                        <input type="checkbox" id="analytics-toggle" ${this.gdprConsent?.analytics ? 'checked' : ''}>
                        Análisis básico del sitio web
                    </label>
                    <label>
                        <input type="checkbox" id="heatmaps-toggle" ${this.gdprConsent?.heatmaps ? 'checked' : ''}>
                        Mapas de calor y análisis de comportamiento
                    </label>
                    <label>
                        <input type="checkbox" id="recordings-toggle" ${this.gdprConsent?.recordings ? 'checked' : ''}>
                        Grabaciones de sesión (anónimas)
                    </label>
                </div>
                
                <button id="update-consent">Actualizar Preferencias</button>
            </div>
        `;
        
        container.appendChild(consentUI);
        
        document.getElementById('update-consent').onclick = () => {
            const newConsent = {
                analytics: document.getElementById('analytics-toggle').checked,
                heatmaps: document.getElementById('heatmaps-toggle').checked,
                recordings: document.getElementById('recordings-toggle').checked
            };
            this.setConsent(newConsent);
            alert('Preferencias actualizadas correctamente');
        };
    }
    
    /**
     * Get session summary for analytics
     */
    getSessionSummary() {
        return {
            sessionId: this.sessionData.sessionId,
            userId: this.sessionData.userId,
            startTime: this.sessionData.startTime,
            duration: Date.now() - this.sessionData.startTime,
            eventCount: this.sessionData.events.length,
            errorCount: this.sessionData.errorEvents.length,
            isRecording: this.sessionData.isRecording,
            isHeatmapActive: this.sessionData.isHeatmapActive,
            userSegment: this.getUserSegment(),
            sessionType: this.getSessionType(),
            deviceType: this.getDeviceType(),
            performanceMetrics: this.sessionData.performanceMetrics,
            gdprConsent: this.gdprConsent
        };
    }
}

// Initialize heatmap analytics
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing HeatmapAnalytics...');
    
    // Configuration - these would typically come from your backend
    const config = {
        clarityId: null, // Set to actual Microsoft Clarity ID when available (e.g., 'abcd1234')
        hotjarId: null,  // Set to actual Hotjar ID when available (e.g., '1234567')
        recordingSampleRate: 10,       // 10% of sessions
        heatmapSampleRate: 25,        // 25% of sessions
        enableGDPRCompliance: true,
        enableClickTracking: true,
        enableScrollTracking: true,
        enableFormTracking: true,
        enableErrorTracking: true,
        enableCustomEvents: true,
        
        // Para testing/desarrollo - cambiar por IDs reales en producción
        testMode: true, // En producción, cambiar a false y usar IDs reales
        
        // IDs de ejemplo para testing (no funcionales)
        // clarityId: 'example123',  // Reemplazar por ID real de Microsoft Clarity
        // hotjarId: '1234567',     // Reemplazar por ID real de Hotjar
    };
    
    window.heatmapAnalytics = new HeatmapAnalytics(config);
    console.log('HeatmapAnalytics initialized:', window.heatmapAnalytics);
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HeatmapAnalytics;
}

/*
====================================================================================
🔥 HOTJAR + 📊 MICROSOFT CLARITY - CONFIGURACIÓN PARA PRODUCCIÓN
====================================================================================

Para activar completamente Hotjar y Microsoft Clarity en tu sitio web, sigue estos pasos:

📊 MICROSOFT CLARITY:
1. Ve a https://clarity.microsoft.com/
2. Crea una cuenta gratuita con tu email/Microsoft
3. Haz clic en "Add new project"
4. Ingresa la URL de tu sitio: https://fractalmerch.com.ar
5. Copia el Clarity ID (ej: "abcd1234")
6. En este archivo, cambia:
   clarityId: null  →  clarityId: 'abcd1234'

🔥 HOTJAR:
1. Ve a https://www.hotjar.com/
2. Crea una cuenta gratuita (plan gratuito hasta 35 sesiones/día)
3. Haz clic en "Add new site"
4. Ingresa la URL: https://fractalmerch.com.ar
5. Selecciona el tipo de sitio (E-commerce)
6. Copia el Site ID (ej: "1234567")
7. En este archivo, cambia:
   hotjarId: null  →  hotjarId: '1234567'

⚙️ CONFIGURACIÓN FINAL:
En línea ~961-962, actualiza:

const config = {
    clarityId: 'TU_CLARITY_ID_AQUI',  // ← Pega tu ID de Clarity
    hotjarId: 'TU_HOTJAR_ID_AQUI',    // ← Pega tu ID de Hotjar
    testMode: false,                   // ← Cambiar a false en producción
    ...resto de la configuración
};

✅ VERIFICACIÓN:
Después de configurar, abre la consola del navegador y verifica:
- "Initializing Microsoft Clarity with ID: ..."
- "Initializing Hotjar with ID: ..."

📈 BENEFICIOS:
- Microsoft Clarity: Heatmaps, grabaciones de sesión, insights de UX
- Hotjar: Feedback forms, encuestas, análisis de conversión
- GDPR compliant: Banner de consentimiento automático
- Performance optimized: Sampling rates y lazy loading

🔒 PRIVACIDAD:
El sistema ya incluye:
- Banner de consentimiento GDPR
- Opciones granulares de tracking
- Sampling responsable (10% recording, 25% heatmaps)
- Anonimización de datos personales

====================================================================================
*/