/**
 * FractalMerch Performance Optimizer
 * Advanced performance optimization with Core Web Vitals tracking
 */
class PerformanceOptimizer {
    constructor(config = {}) {
        this.config = {
            enableImageOptimization: config.enableImageOptimization !== false,
            enableLazyLoading: config.enableLazyLoading !== false,
            enableWebVitals: config.enableWebVitals !== false,
            enablePreloading: config.enablePreloading !== false,
            enableServiceWorker: config.enableServiceWorker !== false,
            ...config
        };
        
        this.metrics = {
            lcp: 0,
            fid: 0,
            cls: 0,
            fcp: 0,
            ttfb: 0
        };
        
        this.init();
    }
    
    init() {
        console.log('PerformanceOptimizer initialized');
        
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.startOptimization();
            });
        } else {
            this.startOptimization();
        }
    }
    
    startOptimization() {
        if (this.config.enableImageOptimization) {
            this.initImageOptimization();
        }
        
        if (this.config.enableWebVitals) {
            this.initWebVitalsTracking();
        }
        
        if (this.config.enablePreloading) {
            this.initPreloadOptimization();
        }
        
        // Initialize critical resources optimization
        this.initCriticalResourcesOptimization();
        
        // Initialize cache optimization
        this.initCacheOptimization();
        
        // Start performance monitoring
        this.startPerformanceMonitoring();
    }
    
    /**
     * LAZY LOADING & IMAGE OPTIMIZATION
     */
    initLazyLoading() {
        const images = document.querySelectorAll('img[data-src], img[loading="lazy"]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        observer.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for browsers without IntersectionObserver
            images.forEach(img => this.loadImage(img));
        }
    }
    
    loadImage(img) {
        const src = img.dataset.src || img.src;
        
        if (src) {
            const optimizedSrc = this.getOptimizedImageUrl(img, src);
            
            // Create a new image to preload
            const newImg = new Image();
            newImg.onload = () => {
                img.src = optimizedSrc;
                img.classList.add('loaded');
                this.trackImageLoad(img, optimizedSrc);
            };
            newImg.src = optimizedSrc;
        }
    }
    
    getOptimizedImageUrl(img, src) {
        // Get the image's displayed dimensions
        const rect = img.getBoundingClientRect();
        const devicePixelRatio = window.devicePixelRatio || 1;
        
        const width = Math.ceil(rect.width * devicePixelRatio);
        const height = Math.ceil(rect.height * devicePixelRatio);
        
        // Check if we have a resize API or use original
        if (src.includes('/assets/images/')) {
            // Add resize parameters for our images
            const params = new URLSearchParams({
                w: Math.min(width, 1920), // Max width
                h: Math.min(height, 1080), // Max height
                q: this.getImageQuality(),
                f: this.getImageFormat()
            });
            
            return `${src}?${params.toString()}`;
        }
        
        return src;
    }
    
    /**
     * Get optimal image quality based on connection
     */
    getImageQuality() {
        const connection = navigator.connection;
        if (!connection) return 85;
        
        // Adjust quality based on connection speed
        if (connection.effectiveType === '4g') return 85;
        if (connection.effectiveType === '3g') return 75;
        if (connection.effectiveType === '2g') return 60;
        
        return 70;
    }
    
    /**
     * Get optimal image format
     */
    getImageFormat() {
        // Check WebP support
        if (this.supportsWebP()) return 'webp';
        
        // Check AVIF support
        if (this.supportsAVIF()) return 'avif';
        
        return 'jpg';
    }
    
    /**
     * Check WebP support
     */
    supportsWebP() {
        const canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    }
    
    /**
     * Check AVIF support
     */
    supportsAVIF() {
        const canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        return canvas.toDataURL('image/avif').indexOf('data:image/avif') === 0;
    }
    
    /**
     * Initialize image optimization
     */
    initImageOptimization() {
        // Preload critical images
        this.preloadCriticalImages();
        
        // Setup responsive images
        this.setupResponsiveImages();
        
        // Optimize existing images
        this.optimizeLoadedImages();
        
        // Initialize lazy loading
        this.initLazyLoading();
    }
    
    /**
     * Preload critical images (above the fold)
     */
    preloadCriticalImages() {
        const criticalImages = document.querySelectorAll('.hero-image, .logo, .critical-image');
        
        criticalImages.forEach(img => {
            if (img.dataset.src || img.src) {
                const link = document.createElement('link');
                link.rel = 'preload';
                link.as = 'image';
                link.href = img.dataset.src || img.src;
                
                // Add to head
                document.head.appendChild(link);
            }
        });
    }
    
    /**
     * Setup responsive images with srcset
     */
    setupResponsiveImages() {
        const images = document.querySelectorAll('img[data-sizes]');
        
        images.forEach(img => {
            const sizes = JSON.parse(img.dataset.sizes || '{}');
            const srcset = [];
            
            Object.keys(sizes).forEach(size => {
                srcset.push(`${sizes[size]} ${size}w`);
            });
            
            if (srcset.length > 0) {
                img.srcset = srcset.join(', ');
                img.sizes = '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw';
            }
        });
    }
    
    /**
     * Optimize already loaded images
     */
    optimizeLoadedImages() {
        const images = document.querySelectorAll('img');
        
        images.forEach(img => {
            // Add decoding attribute
            img.decoding = 'async';
            
            // Add loading attribute if not set
            if (!img.loading && !img.classList.contains('critical-image')) {
                img.loading = 'lazy';
            }
        });
    }
    
    /**
     * Initialize critical resources optimization
     */
    initCriticalResourcesOptimization() {
        // Preload critical CSS
        this.preloadCriticalCSS();
        
        // Preload critical JavaScript
        this.preloadCriticalJS();
        
        // Setup font optimization
        this.optimizeFonts();
    }
    
    /**
     * Preload critical CSS
     */
    preloadCriticalCSS() {
        const criticalCSS = ['style.css', 'critical.css'];
        
        criticalCSS.forEach(css => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'style';
            link.href = `/assets/css/${css}`;
            
            document.head.appendChild(link);
        });
    }
    
    /**
     * Preload critical JavaScript
     */
    preloadCriticalJS() {
        const criticalJS = ['main.js', 'enhanced-cart.js'];
        
        criticalJS.forEach(js => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'script';
            link.href = `/assets/js/${js}`;
            
            document.head.appendChild(link);
        });
    }
    
    /**
     * Optimize font loading
     */
    optimizeFonts() {
        // Preload critical fonts
        const fonts = [
            { family: 'Inter', weight: '400', display: 'swap' },
            { family: 'Inter', weight: '600', display: 'swap' }
        ];
        
        fonts.forEach(font => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'font';
            link.type = 'font/woff2';
            link.crossOrigin = 'anonymous';
            link.href = `/assets/fonts/inter-${font.weight}.woff2`;
            
            document.head.appendChild(link);
        });
    }
    
    /**
     * Initialize Web Vitals tracking
     */
    initWebVitalsTracking() {
        // Track Largest Contentful Paint (LCP)
        this.trackLCP();
        
        // Track First Input Delay (FID)
        this.trackFID();
        
        // Track Cumulative Layout Shift (CLS)
        this.trackCLS();
        
        // Track First Contentful Paint (FCP)
        this.trackFCP();
        
        // Track Time to First Byte (TTFB)
        this.trackTTFB();
    }
    
    /**
     * Track Largest Contentful Paint
     */
    trackLCP() {
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];
                
                this.metrics.lcp = lastEntry.renderTime || lastEntry.loadTime;
                this.reportWebVital('LCP', this.metrics.lcp);
            });
            
            observer.observe({ entryTypes: ['largest-contentful-paint'] });
        }
    }
    
    /**
     * Track First Input Delay
     */
    trackFID() {
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                const firstInput = list.getEntries()[0];
                
                this.metrics.fid = firstInput.processingStart - firstInput.startTime;
                this.reportWebVital('FID', this.metrics.fid);
            });
            
            observer.observe({ entryTypes: ['first-input'] });
        }
    }
    
    /**
     * Track Cumulative Layout Shift
     */
    trackCLS() {
        if ('PerformanceObserver' in window) {
            let clsValue = 0;
            
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                }
                
                this.metrics.cls = clsValue;
                this.reportWebVital('CLS', this.metrics.cls);
            });
            
            observer.observe({ entryTypes: ['layout-shift'] });
        }
    }
    
    /**
     * Track First Contentful Paint
     */
    trackFCP() {
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const fcpEntry = entries.find(entry => entry.name === 'first-contentful-paint');
                
                if (fcpEntry) {
                    this.metrics.fcp = fcpEntry.startTime;
                    this.reportWebVital('FCP', this.metrics.fcp);
                }
            });
            
            observer.observe({ entryTypes: ['paint'] });
        }
    }
    
    /**
     * Track Time to First Byte
     */
    trackTTFB() {
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const navigationEntry = entries[0];
                
                if (navigationEntry) {
                    this.metrics.ttfb = navigationEntry.responseStart - navigationEntry.requestStart;
                    this.reportWebVital('TTFB', this.metrics.ttfb);
                }
            });
            
            observer.observe({ entryTypes: ['navigation'] });
        }
    }
    
    /**
     * Report Web Vital metric
     */
    reportWebVital(name, value) {
        // Send to analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'web_vital', {
                metric_name: name,
                metric_value: Math.round(value),
                metric_delta: Math.round(value)
            });
        }
        
        // Send to our backend
        this.sendMetricToBackend(name, value);
        
        // Log for debugging
        console.log(`Web Vital - ${name}: ${value}ms`);
    }
    
    /**
     * Send metric to backend
     */
    sendMetricToBackend(name, value) {
        fetch('/api/analytics/web-vitals', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                metric: name,
                value: value,
                url: window.location.href,
                timestamp: Date.now()
            })
        }).catch(error => {
            console.warn('Failed to send web vital:', error);
        });
    }
    
    /**
     * Initialize preload optimization
     */
    initPreloadOptimization() {
        // Preload next page resources on hover
        this.setupHoverPreloading();
        
        // Preload based on user behavior
        this.setupBehaviorPreloading();
    }
    
    /**
     * Setup hover preloading
     */
    setupHoverPreloading() {
        const links = document.querySelectorAll('a[href]');
        
        links.forEach(link => {
            link.addEventListener('mouseenter', () => {
                this.preloadPage(link.href);
            }, { once: true });
        });
    }
    
    /**
     * Preload page resources
     */
    preloadPage(url) {
        // Don't preload external links
        if (!url.includes(window.location.origin)) return;
        
        // Don't preload if already preloaded
        if (document.querySelector(`link[rel="prefetch"][href="${url}"]`)) return;
        
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        
        document.head.appendChild(link);
    }
    
    /**
     * Setup behavior-based preloading
     */
    setupBehaviorPreloading() {
        // Preload product detail pages when hovering over product cards
        document.addEventListener('mouseenter', (e) => {
            const productCard = e.target.closest('.product-card');
            if (productCard) {
                const link = productCard.querySelector('a[href*="product-detail"]');
                if (link) {
                    this.preloadPage(link.href);
                }
            }
        });
    }
    
    /**
     * Initialize cache optimization
     */
    initCacheOptimization() {
        // Service Worker registration
        this.registerServiceWorker();
        
        // Local Storage optimization
        this.optimizeLocalStorage();
    }
    
    /**
     * Register Service Worker for caching
     */
    registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered:', registration);
                })
                .catch(error => {
                    console.log('SW registration failed:', error);
                });
        }
    }
    
    /**
     * Optimize Local Storage usage
     */
    optimizeLocalStorage() {
        try {
            // Clean old cart data
            const cartData = localStorage.getItem('cart');
            if (cartData) {
                const cart = JSON.parse(cartData);
                if (cart.timestamp && Date.now() - cart.timestamp > 7 * 24 * 60 * 60 * 1000) {
                    localStorage.removeItem('cart');
                }
            }
            
            // Clean old A/B test data
            const abData = localStorage.getItem('ab_conversions');
            if (abData) {
                const conversions = JSON.parse(abData);
                const recent = conversions.filter(c => Date.now() - c.timestamp < 30 * 24 * 60 * 60 * 1000);
                localStorage.setItem('ab_conversions', JSON.stringify(recent));
            }
            
        } catch (error) {
            console.warn('LocalStorage optimization failed:', error);
        }
    }
    
    /**
     * Track image load performance
     */
    trackImageLoad(img, src) {
        const loadTime = performance.now();
        
        // Track in analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'image_load', {
                image_src: src,
                load_time: Math.round(loadTime)
            });
        }
    }
    
    /**
     * Start performance monitoring
     */
    startPerformanceMonitoring() {
        // Monitor every 30 seconds
        setInterval(() => {
            this.checkPerformance();
        }, 30000);
        
        // Monitor on visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                this.checkPerformance();
            }
        });
    }
    
    /**
     * Check overall performance
     */
    checkPerformance() {
        const now = performance.now();
        const navigation = performance.getEntriesByType('navigation')[0];
        
        if (navigation) {
            const metrics = {
                domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
                ttfb: navigation.responseStart - navigation.requestStart,
                domInteractive: navigation.domInteractive - navigation.navigationStart
            };
            
            // Send performance report
            this.sendPerformanceReport(metrics);
        }
    }
    
    /**
     * Send performance report to backend
     */
    sendPerformanceReport(metrics) {
        fetch('/api/analytics/performance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                metrics: metrics,
                url: window.location.href,
                timestamp: Date.now(),
                userAgent: navigator.userAgent,
                connection: navigator.connection ? {
                    effectiveType: navigator.connection.effectiveType,
                    downlink: navigator.connection.downlink
                } : null
            })
        }).catch(error => {
            console.warn('Performance report failed:', error);
        });
    }
    
    /**
     * Get current performance metrics
     */
    getMetrics() {
        return this.metrics;
    }
}

// Auto-initialize performance optimizer
let performanceOptimizer;

document.addEventListener('DOMContentLoaded', function() {
    performanceOptimizer = new PerformanceOptimizer();
    
    // Expose globally
    window.PerformanceOptimizer = performanceOptimizer;
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PerformanceOptimizer;
}