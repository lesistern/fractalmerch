/**
 * FractalMerch Supplier Integration System
 * Automated integration with sublimation and printing suppliers
 */

class SupplierIntegration {
    constructor(config = {}) {
        this.config = {
            apiBaseUrl: config.apiBaseUrl || '/api/suppliers',
            enableRealTimeSync: config.enableRealTimeSync !== false,
            syncInterval: config.syncInterval || 300000, // 5 minutes
            enableAutomatedOrdering: config.enableAutomatedOrdering || false,
            enablePriceUpdates: config.enablePriceUpdates !== false,
            enableStockSync: config.enableStockSync !== false,
            retryAttempts: config.retryAttempts || 3,
            timeout: config.timeout || 30000, // 30 seconds
            ...config
        };
        
        this.suppliers = new Map();
        this.activeIntegrations = new Map();
        this.pendingOrders = new Map();
        this.syncQueue = [];
        this.errorLog = [];
        this.syncTimer = null;
        
        // Supported supplier types and their configurations
        this.supplierTypes = {
            'printful': {
                name: 'Printful',
                apiVersion: 'v1',
                baseUrl: 'https://api.printful.com',
                authType: 'bearer',
                rateLimit: 120, // requests per minute
                features: ['products', 'orders', 'shipping', 'mockups', 'webhooks'],
                productMapping: {
                    'shirt': ['unisex_staple_t_shirt', 'bella_canvas_3001'],
                    'hoodie': ['unisex_heavy_cotton_hoodie'],
                    'mug': ['white_mug_11oz'],
                    'poster': ['enhanced_matte_paper_poster']
                }
            },
            'gooten': {
                name: 'Gooten',
                apiVersion: 'v1',
                baseUrl: 'https://api.gooten.com/v1',
                authType: 'api_key',
                rateLimit: 100,
                features: ['products', 'orders', 'shipping', 'tracking'],
                productMapping: {
                    'shirt': ['1', '2', '3'], // Gooten product IDs
                    'hoodie': ['10', '11'],
                    'mug': ['20', '21'],
                    'poster': ['30', '31']
                }
            },
            'printify': {
                name: 'Printify',
                apiVersion: 'v1',
                baseUrl: 'https://api.printify.com/v1',
                authType: 'bearer',
                rateLimit: 90,
                features: ['products', 'orders', 'shops', 'uploads'],
                productMapping: {
                    'shirt': ['5', '6', '7'],
                    'hoodie': ['15', '16'],
                    'mug': ['25', '26'],
                    'poster': ['35', '36']
                }
            },
            'teespring': {
                name: 'Spring (formerly Teespring)',
                apiVersion: 'v2',
                baseUrl: 'https://api.teespring.com/v2',
                authType: 'bearer',
                rateLimit: 60,
                features: ['products', 'orders', 'designs'],
                productMapping: {
                    'shirt': ['standard-t-shirt', 'premium-t-shirt'],
                    'hoodie': ['pullover-hoodie', 'zip-hoodie'],
                    'mug': ['ceramic-mug']
                }
            },
            'custom_local': {
                name: 'Proveedor Local Personalizado',
                apiVersion: 'custom',
                baseUrl: '',
                authType: 'custom',
                rateLimit: 30,
                features: ['orders', 'status', 'pricing'],
                productMapping: {}
            }
        };
        
        this.eventHandlers = {
            supplierConnected: [],
            orderPlaced: [],
            orderUpdated: [],
            stockUpdated: [],
            priceUpdated: [],
            integrationError: []
        };
        
        this.init();
    }
    
    init() {
        console.log('Initializing Supplier Integration System...');
        
        this.loadSuppliers();
        this.setupRealTimeSync();
        this.setupEventListeners();
        this.initializeWebhooks();
        this.loadPendingOrders();
        
        console.log('Supplier Integration System initialized successfully');
    }
    
    /**
     * Load configured suppliers
     */
    async loadSuppliers() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/get-suppliers.php`);
            const data = await response.json();
            
            if (data.success) {
                for (const supplier of data.suppliers) {
                    await this.addSupplier(supplier);
                }
                
                console.log(`Loaded ${this.suppliers.size} suppliers`);
            }
        } catch (error) {
            console.error('Error loading suppliers:', error);
        }
    }
    
    /**
     * Add supplier integration
     */
    async addSupplier(supplierData) {
        const supplier = {
            id: supplierData.id,
            name: supplierData.name,
            type: supplierData.type,
            apiKey: supplierData.api_key,
            apiSecret: supplierData.api_secret,
            baseUrl: supplierData.base_url || this.supplierTypes[supplierData.type]?.baseUrl,
            isActive: supplierData.is_active === '1',
            settings: JSON.parse(supplierData.settings || '{}'),
            lastSync: supplierData.last_sync ? new Date(supplierData.last_sync) : null,
            syncStatus: 'disconnected',
            products: new Map(),
            orders: new Map(),
            webhookUrl: supplierData.webhook_url,
            rateLimiter: this.createRateLimiter(supplierData.type)
        };
        
        this.suppliers.set(supplier.id, supplier);
        
        if (supplier.isActive) {
            await this.connectSupplier(supplier.id);
        }
        
        return supplier;
    }
    
    /**
     * Connect to supplier API
     */
    async connectSupplier(supplierId) {
        const supplier = this.suppliers.get(supplierId);
        if (!supplier) {
            throw new Error('Supplier not found');
        }
        
        const supplierType = this.supplierTypes[supplier.type];
        if (!supplierType) {
            throw new Error(`Unsupported supplier type: ${supplier.type}`);
        }
        
        try {
            // Test connection
            const connectionTest = await this.testConnection(supplier);
            
            if (connectionTest.success) {
                supplier.syncStatus = 'connected';
                this.activeIntegrations.set(supplierId, {
                    supplier: supplier,
                    lastActivity: new Date(),
                    requestCount: 0,
                    errorCount: 0
                });
                
                // Load supplier products
                await this.syncSupplierProducts(supplierId);
                
                // Setup webhooks if supported
                if (supplierType.features.includes('webhooks')) {
                    await this.setupWebhook(supplierId);
                }
                
                this.triggerEvent('supplierConnected', { supplier });
                console.log(`Connected to supplier: ${supplier.name}`);
                
            } else {
                supplier.syncStatus = 'error';
                throw new Error(connectionTest.error || 'Connection test failed');
            }
        } catch (error) {
            supplier.syncStatus = 'error';
            this.logError(supplierId, 'connection_failed', error.message);
            console.error(`Failed to connect to supplier ${supplier.name}:`, error);
        }
    }
    
    /**
     * Test connection to supplier API
     */
    async testConnection(supplier) {
        const supplierType = this.supplierTypes[supplier.type];
        
        try {
            const headers = this.buildAuthHeaders(supplier);
            const response = await this.makeRequest(supplier, 'GET', '/ping', null, headers);
            
            return { success: true, data: response };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    /**
     * Build authentication headers
     */
    buildAuthHeaders(supplier) {
        const supplierType = this.supplierTypes[supplier.type];
        const headers = {
            'Content-Type': 'application/json',
            'User-Agent': 'FractalMerch/1.0'
        };
        
        switch (supplierType.authType) {
            case 'bearer':
                headers['Authorization'] = `Bearer ${supplier.apiKey}`;
                break;
            case 'api_key':
                headers['X-API-Key'] = supplier.apiKey;
                break;
            case 'basic':
                const auth = btoa(`${supplier.apiKey}:${supplier.apiSecret}`);
                headers['Authorization'] = `Basic ${auth}`;
                break;
            case 'custom':
                // Handle custom authentication
                Object.assign(headers, supplier.settings.customHeaders || {});
                break;
        }
        
        return headers;
    }
    
    /**
     * Make API request to supplier
     */
    async makeRequest(supplier, method, endpoint, data = null, customHeaders = {}) {
        const supplierType = this.supplierTypes[supplier.type];
        
        // Check rate limits
        if (!await this.checkRateLimit(supplier.id)) {
            throw new Error('Rate limit exceeded');
        }
        
        const url = `${supplier.baseUrl}${endpoint}`;
        const headers = {
            ...this.buildAuthHeaders(supplier),
            ...customHeaders
        };
        
        const options = {
            method,
            headers,
            timeout: this.config.timeout
        };
        
        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            options.body = JSON.stringify(data);
        }
        
        const integration = this.activeIntegrations.get(supplier.id);
        if (integration) {
            integration.requestCount++;
            integration.lastActivity = new Date();
        }
        
        try {
            const response = await fetch(url, options);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return await response.json();
        } catch (error) {
            if (integration) {
                integration.errorCount++;
            }
            this.logError(supplier.id, 'api_request_failed', error.message, { method, endpoint });
            throw error;
        }
    }
    
    /**
     * Create rate limiter for supplier
     */
    createRateLimiter(supplierType) {
        const config = this.supplierTypes[supplierType];
        const maxRequests = config?.rateLimit || 60;
        
        return {
            requests: [],
            maxRequests,
            timeWindow: 60000, // 1 minute
            
            canMakeRequest() {
                const now = Date.now();
                // Remove old requests outside time window
                this.requests = this.requests.filter(time => now - time < this.timeWindow);
                return this.requests.length < this.maxRequests;
            },
            
            recordRequest() {
                this.requests.push(Date.now());
            }
        };
    }
    
    /**
     * Check rate limit
     */
    async checkRateLimit(supplierId) {
        const supplier = this.suppliers.get(supplierId);
        if (!supplier || !supplier.rateLimiter) return true;
        
        if (!supplier.rateLimiter.canMakeRequest()) {
            console.warn(`Rate limit reached for supplier ${supplier.name}`);
            return false;
        }
        
        supplier.rateLimiter.recordRequest();
        return true;
    }
    
    /**
     * Sync supplier products
     */
    async syncSupplierProducts(supplierId) {
        const supplier = this.suppliers.get(supplierId);
        if (!supplier) return;
        
        try {
            const products = await this.getSupplierProducts(supplier);
            
            for (const product of products) {
                supplier.products.set(product.id, {
                    id: product.id,
                    name: product.name,
                    type: product.type,
                    variants: product.variants || [],
                    basePrice: product.base_price,
                    availability: product.availability,
                    printAreas: product.print_areas || [],
                    mockupUrl: product.mockup_url,
                    lastUpdated: new Date()
                });
            }
            
            supplier.lastSync = new Date();
            console.log(`Synced ${products.length} products from ${supplier.name}`);
            
        } catch (error) {
            this.logError(supplierId, 'product_sync_failed', error.message);
            console.error(`Failed to sync products from ${supplier.name}:`, error);
        }
    }
    
    /**
     * Get supplier products
     */
    async getSupplierProducts(supplier) {
        switch (supplier.type) {
            case 'printful':
                return await this.getPrintfulProducts(supplier);
            case 'gooten':
                return await this.getGootenProducts(supplier);
            case 'printify':
                return await this.getPrintifyProducts(supplier);
            case 'teespring':
                return await this.getTeeSpringProducts(supplier);
            case 'custom_local':
                return await this.getCustomProducts(supplier);
            default:
                throw new Error(`Unsupported supplier type: ${supplier.type}`);
        }
    }
    
    /**
     * Get Printful products
     */
    async getPrintfulProducts(supplier) {
        const response = await this.makeRequest(supplier, 'GET', '/products');
        
        return response.result.map(product => ({
            id: product.id,
            name: product.title,
            type: this.mapProductType(product.type),
            variants: product.variants || [],
            base_price: product.price || 0,
            availability: true,
            print_areas: product.print_areas || [],
            mockup_url: product.image
        }));
    }
    
    /**
     * Get Gooten products
     */
    async getGootenProducts(supplier) {
        const response = await this.makeRequest(supplier, 'GET', '/products');
        
        return response.Products.map(product => ({
            id: product.Id,
            name: product.Name,
            type: this.mapProductType(product.Category),
            variants: product.ProductVariants || [],
            base_price: product.PriceInfo?.Price || 0,
            availability: product.IsActive,
            print_areas: [],
            mockup_url: product.ProductImage
        }));
    }
    
    /**
     * Get Printify products
     */
    async getPrintifyProducts(supplier) {
        const response = await this.makeRequest(supplier, 'GET', '/catalog/blueprints.json');
        
        return response.data.map(blueprint => ({
            id: blueprint.id,
            name: blueprint.title,
            type: this.mapProductType(blueprint.brand),
            variants: blueprint.variants || [],
            base_price: 0, // Printify requires variant-specific pricing
            availability: true,
            print_areas: blueprint.print_areas || [],
            mockup_url: blueprint.images?.[0]?.src
        }));
    }
    
    /**
     * Get TeeSpring products
     */
    async getTeeSpringProducts(supplier) {
        const response = await this.makeRequest(supplier, 'GET', '/products');
        
        return response.data.map(product => ({
            id: product.id,
            name: product.name,
            type: this.mapProductType(product.product_type),
            variants: product.variants || [],
            base_price: product.base_price || 0,
            availability: product.active,
            print_areas: product.print_areas || [],
            mockup_url: product.mockup_url
        }));
    }
    
    /**
     * Get custom supplier products
     */
    async getCustomProducts(supplier) {
        // For custom suppliers, use configured endpoint
        const endpoint = supplier.settings.productsEndpoint || '/products';
        const response = await this.makeRequest(supplier, 'GET', endpoint);
        
        // Assume custom format matches our standard
        return response.products || response.data || [];
    }
    
    /**
     * Map product type from supplier to our internal types
     */
    mapProductType(supplierType) {
        const typeMapping = {
            't-shirt': 'shirt',
            'tshirt': 'shirt',
            'shirt': 'shirt',
            'hoodie': 'hoodie',
            'sweatshirt': 'hoodie',
            'mug': 'mug',
            'cup': 'mug',
            'poster': 'poster',
            'print': 'poster',
            'mousepad': 'mousepad',
            'phone-case': 'phone_case',
            'pillow': 'pillow',
            'cushion': 'pillow'
        };
        
        return typeMapping[supplierType.toLowerCase()] || 'other';
    }
    
    /**
     * Place order with supplier
     */
    async placeOrder(supplierId, orderData) {
        const supplier = this.suppliers.get(supplierId);
        if (!supplier) {
            throw new Error('Supplier not found');
        }
        
        if (supplier.syncStatus !== 'connected') {
            throw new Error('Supplier not connected');
        }
        
        try {
            const supplierOrder = await this.createSupplierOrder(supplier, orderData);
            
            // Store pending order
            const pendingOrder = {
                id: supplierOrder.id,
                supplierId: supplierId,
                internalOrderId: orderData.orderId,
                supplierOrderId: supplierOrder.id,
                status: supplierOrder.status || 'pending',
                items: orderData.items,
                totalCost: supplierOrder.total_cost || 0,
                estimatedShipping: supplierOrder.estimated_shipping,
                trackingNumber: supplierOrder.tracking_number,
                createdAt: new Date(),
                lastUpdated: new Date()
            };
            
            this.pendingOrders.set(supplierOrder.id, pendingOrder);
            supplier.orders.set(supplierOrder.id, pendingOrder);
            
            // Save to database
            await this.savePendingOrder(pendingOrder);
            
            this.triggerEvent('orderPlaced', { supplier, order: pendingOrder });
            console.log(`Order placed with ${supplier.name}: ${supplierOrder.id}`);
            
            return pendingOrder;
            
        } catch (error) {
            this.logError(supplierId, 'order_placement_failed', error.message, { orderData });
            throw error;
        }
    }
    
    /**
     * Create supplier-specific order
     */
    async createSupplierOrder(supplier, orderData) {
        switch (supplier.type) {
            case 'printful':
                return await this.createPrintfulOrder(supplier, orderData);
            case 'gooten':
                return await this.createGootenOrder(supplier, orderData);
            case 'printify':
                return await this.createPrintifyOrder(supplier, orderData);
            case 'teespring':
                return await this.createTeeSpringOrder(supplier, orderData);
            case 'custom_local':
                return await this.createCustomOrder(supplier, orderData);
            default:
                throw new Error(`Unsupported supplier type: ${supplier.type}`);
        }
    }
    
    /**
     * Create Printful order
     */
    async createPrintfulOrder(supplier, orderData) {
        const printfulOrder = {
            recipient: {
                name: orderData.shipping.name,
                address1: orderData.shipping.address1,
                address2: orderData.shipping.address2,
                city: orderData.shipping.city,
                state_code: orderData.shipping.state,
                country_code: orderData.shipping.country,
                zip: orderData.shipping.zip,
                phone: orderData.shipping.phone,
                email: orderData.shipping.email
            },
            items: orderData.items.map(item => ({
                variant_id: item.variantId,
                quantity: item.quantity,
                files: item.designFiles?.map(file => ({
                    type: file.type,
                    url: file.url
                })) || []
            }))
        };
        
        const response = await this.makeRequest(supplier, 'POST', '/orders', printfulOrder);
        return response.result;
    }
    
    /**
     * Create Gooten order
     */
    async createGootenOrder(supplier, orderData) {
        const gootenOrder = {
            OrderId: orderData.orderId,
            Items: orderData.items.map(item => ({
                Sku: item.sku,
                Quantity: item.quantity,
                ProductId: item.productId
            })),
            ShipToAddress: {
                FirstName: orderData.shipping.firstName,
                LastName: orderData.shipping.lastName,
                Line1: orderData.shipping.address1,
                Line2: orderData.shipping.address2,
                City: orderData.shipping.city,
                State: orderData.shipping.state,
                PostalCode: orderData.shipping.zip,
                CountryCode: orderData.shipping.country
            }
        };
        
        const response = await this.makeRequest(supplier, 'POST', '/orders', gootenOrder);
        return response;
    }
    
    /**
     * Create Printify order
     */
    async createPrintifyOrder(supplier, orderData) {
        const printifyOrder = {
            external_id: orderData.orderId,
            shipping_method: 1, // Standard shipping
            send_shipping_notification: false,
            address_to: {
                first_name: orderData.shipping.firstName,
                last_name: orderData.shipping.lastName,
                address1: orderData.shipping.address1,
                address2: orderData.shipping.address2,
                city: orderData.shipping.city,
                region: orderData.shipping.state,
                zip: orderData.shipping.zip,
                country: orderData.shipping.country,
                phone: orderData.shipping.phone,
                email: orderData.shipping.email
            },
            line_items: orderData.items.map(item => ({
                product_id: item.productId,
                variant_id: item.variantId,
                quantity: item.quantity,
                print_areas: item.printAreas || {}
            }))
        };
        
        const response = await this.makeRequest(supplier, 'POST', '/shops/{shop_id}/orders.json', printifyOrder);
        return response;
    }
    
    /**
     * Setup real-time sync
     */
    setupRealTimeSync() {
        if (!this.config.enableRealTimeSync) return;
        
        this.syncTimer = setInterval(() => {
            this.performPeriodicSync();
        }, this.config.syncInterval);
    }
    
    /**
     * Perform periodic sync
     */
    async performPeriodicSync() {
        console.log('Performing periodic supplier sync...');
        
        for (const [supplierId, supplier] of this.suppliers) {
            if (supplier.isActive && supplier.syncStatus === 'connected') {
                try {
                    await this.syncSupplierData(supplierId);
                } catch (error) {
                    console.error(`Error syncing supplier ${supplier.name}:`, error);
                }
            }
        }
    }
    
    /**
     * Sync supplier data
     */
    async syncSupplierData(supplierId) {
        // Sync orders status
        await this.syncOrderStatuses(supplierId);
        
        // Sync stock levels if enabled
        if (this.config.enableStockSync) {
            await this.syncStockLevels(supplierId);
        }
        
        // Sync prices if enabled
        if (this.config.enablePriceUpdates) {
            await this.syncPricing(supplierId);
        }
    }
    
    /**
     * Sync order statuses
     */
    async syncOrderStatuses(supplierId) {
        const supplier = this.suppliers.get(supplierId);
        if (!supplier) return;
        
        const pendingOrders = Array.from(supplier.orders.values())
            .filter(order => !['delivered', 'cancelled', 'refunded'].includes(order.status));
        
        for (const order of pendingOrders) {
            try {
                const updatedOrder = await this.getOrderStatus(supplier, order.supplierOrderId);
                
                if (updatedOrder.status !== order.status) {
                    order.status = updatedOrder.status;
                    order.lastUpdated = new Date();
                    
                    if (updatedOrder.tracking_number) {
                        order.trackingNumber = updatedOrder.tracking_number;
                    }
                    
                    // Update in database
                    await this.updatePendingOrder(order);
                    
                    this.triggerEvent('orderUpdated', { supplier, order });
                }
            } catch (error) {
                this.logError(supplierId, 'status_sync_failed', error.message, { orderId: order.id });
            }
        }
    }
    
    /**
     * Setup webhooks
     */
    async setupWebhook(supplierId) {
        const supplier = this.suppliers.get(supplierId);
        if (!supplier) return;
        
        const webhookUrl = `${window.location.origin}/api/webhooks/supplier/${supplierId}`;
        
        try {
            await this.registerWebhook(supplier, webhookUrl);
            supplier.webhookUrl = webhookUrl;
            console.log(`Webhook setup for ${supplier.name}: ${webhookUrl}`);
        } catch (error) {
            this.logError(supplierId, 'webhook_setup_failed', error.message);
        }
    }
    
    /**
     * Register webhook with supplier
     */
    async registerWebhook(supplier, webhookUrl) {
        const events = ['order.updated', 'order.shipped', 'order.cancelled'];
        
        switch (supplier.type) {
            case 'printful':
                return await this.makeRequest(supplier, 'POST', '/webhooks', {
                    url: webhookUrl,
                    types: events
                });
            
            case 'printify':
                return await this.makeRequest(supplier, 'POST', '/shops/{shop_id}/webhooks.json', {
                    topic: 'order:updated',
                    url: webhookUrl
                });
            
            default:
                console.warn(`Webhooks not supported for supplier type: ${supplier.type}`);
        }
    }
    
    /**
     * Handle webhook data
     */
    async handleWebhook(supplierId, webhookData) {
        const supplier = this.suppliers.get(supplierId);
        if (!supplier) return;
        
        try {
            switch (webhookData.type || webhookData.event) {
                case 'order.updated':
                case 'order:updated':
                    await this.handleOrderUpdate(supplier, webhookData.data);
                    break;
                
                case 'order.shipped':
                case 'order:shipped':
                    await this.handleOrderShipped(supplier, webhookData.data);
                    break;
                
                case 'order.cancelled':
                case 'order:cancelled':
                    await this.handleOrderCancelled(supplier, webhookData.data);
                    break;
                
                case 'stock.updated':
                    await this.handleStockUpdate(supplier, webhookData.data);
                    break;
                
                default:
                    console.log(`Unhandled webhook event: ${webhookData.type}`);
            }
        } catch (error) {
            this.logError(supplierId, 'webhook_processing_failed', error.message, webhookData);
        }
    }
    
    /**
     * Handle order update webhook
     */
    async handleOrderUpdate(supplier, orderData) {
        const order = supplier.orders.get(orderData.id);
        if (!order) return;
        
        order.status = orderData.status;
        order.lastUpdated = new Date();
        
        if (orderData.tracking_number) {
            order.trackingNumber = orderData.tracking_number;
        }
        
        await this.updatePendingOrder(order);
        this.triggerEvent('orderUpdated', { supplier, order });
    }
    
    /**
     * Log error
     */
    logError(supplierId, errorType, message, context = {}) {
        const error = {
            supplierId,
            errorType,
            message,
            context,
            timestamp: new Date()
        };
        
        this.errorLog.unshift(error);
        
        // Keep only last 100 errors
        if (this.errorLog.length > 100) {
            this.errorLog = this.errorLog.slice(0, 100);
        }
        
        // Trigger error event
        this.triggerEvent('integrationError', { error });
        
        console.error(`Supplier Integration Error (${supplierId}):`, error);
    }
    
    /**
     * Get integration statistics
     */
    getIntegrationStats() {
        const stats = {
            totalSuppliers: this.suppliers.size,
            activeIntegrations: this.activeIntegrations.size,
            pendingOrders: this.pendingOrders.size,
            errorCount: this.errorLog.length,
            bySupplier: {}
        };
        
        this.suppliers.forEach((supplier, id) => {
            const integration = this.activeIntegrations.get(id);
            stats.bySupplier[id] = {
                name: supplier.name,
                status: supplier.syncStatus,
                orderCount: supplier.orders.size,
                productCount: supplier.products.size,
                requestCount: integration?.requestCount || 0,
                errorCount: integration?.errorCount || 0,
                lastActivity: integration?.lastActivity
            };
        });
        
        return stats;
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for inventory updates
        document.addEventListener('inventoryUpdated', (event) => {
            this.handleInventoryUpdate(event.detail);
        });
        
        // Listen for order creation
        document.addEventListener('orderCreated', (event) => {
            this.handleOrderCreated(event.detail);
        });
    }
    
    /**
     * Handle order created
     */
    async handleOrderCreated(orderData) {
        if (!this.config.enableAutomatedOrdering) return;
        
        const order = orderData.order;
        
        // Check if any items need supplier fulfillment
        const itemsNeedingFulfillment = order.items.filter(item => 
            item.requiresSupplier && item.supplierId
        );
        
        if (itemsNeedingFulfillment.length > 0) {
            // Group items by supplier
            const itemsBySupplier = {};
            itemsNeedingFulfillment.forEach(item => {
                if (!itemsBySupplier[item.supplierId]) {
                    itemsBySupplier[item.supplierId] = [];
                }
                itemsBySupplier[item.supplierId].push(item);
            });
            
            // Place orders with each supplier
            for (const [supplierId, items] of Object.entries(itemsBySupplier)) {
                try {
                    await this.placeOrder(parseInt(supplierId), {
                        orderId: order.id,
                        items: items,
                        shipping: order.shippingAddress,
                        billing: order.billingAddress
                    });
                } catch (error) {
                    console.error(`Failed to place order with supplier ${supplierId}:`, error);
                }
            }
        }
    }
    
    /**
     * Trigger event
     */
    triggerEvent(eventName, data) {
        if (this.eventHandlers[eventName]) {
            this.eventHandlers[eventName].forEach(handler => {
                try {
                    handler(data);
                } catch (error) {
                    console.error(`Error in ${eventName} event handler:`, error);
                }
            });
        }
        
        // Also dispatch DOM event
        document.dispatchEvent(new CustomEvent(`supplier${eventName.charAt(0).toUpperCase() + eventName.slice(1)}`, {
            detail: data
        }));
    }
    
    /**
     * Add event listener
     */
    on(eventName, handler) {
        if (!this.eventHandlers[eventName]) {
            this.eventHandlers[eventName] = [];
        }
        this.eventHandlers[eventName].push(handler);
    }
    
    /**
     * Cleanup resources
     */
    destroy() {
        if (this.syncTimer) {
            clearInterval(this.syncTimer);
        }
        
        this.suppliers.clear();
        this.activeIntegrations.clear();
        this.pendingOrders.clear();
        this.syncQueue = [];
        this.errorLog = [];
        
        console.log('Supplier Integration System destroyed');
    }
    
    // Placeholder methods for future implementation
    async initializeWebhooks() { /* Implementation */ }
    async loadPendingOrders() { /* Implementation */ }
    async savePendingOrder(order) { /* Implementation */ }
    async updatePendingOrder(order) { /* Implementation */ }
    async getOrderStatus(supplier, orderId) { /* Implementation */ }
    async syncStockLevels(supplierId) { /* Implementation */ }
    async syncPricing(supplierId) { /* Implementation */ }
    async handleInventoryUpdate(data) { /* Implementation */ }
    async handleOrderShipped(supplier, data) { /* Implementation */ }
    async handleOrderCancelled(supplier, data) { /* Implementation */ }
    async handleStockUpdate(supplier, data) { /* Implementation */ }
    async createTeeSpringOrder(supplier, orderData) { /* Implementation */ }
    async createCustomOrder(supplier, orderData) { /* Implementation */ }
}

// Initialize supplier integration
document.addEventListener('DOMContentLoaded', () => {
    const config = {
        apiBaseUrl: '/api/suppliers',
        enableRealTimeSync: true,
        syncInterval: 300000, // 5 minutes
        enableAutomatedOrdering: false, // Set to true to enable automatic order placement
        enablePriceUpdates: true,
        enableStockSync: true,
        retryAttempts: 3,
        timeout: 30000
    };
    
    window.supplierIntegration = new SupplierIntegration(config);
    
    // Setup global event handlers
    window.supplierIntegration.on('supplierConnected', (data) => {
        console.log('Supplier connected:', data.supplier.name);
    });
    
    window.supplierIntegration.on('orderPlaced', (data) => {
        console.log(`Order placed with ${data.supplier.name}:`, data.order.id);
    });
    
    window.supplierIntegration.on('integrationError', (data) => {
        console.error('Supplier integration error:', data.error);
    });
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SupplierIntegration;
}