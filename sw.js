/**
 * FractalMerch Service Worker
 * Caching strategy for performance optimization
 */

const CACHE_NAME = 'fractalmerch-v1.2.0';
const STATIC_CACHE = 'fractalmerch-static-v1.2.0';
const DYNAMIC_CACHE = 'fractalmerch-dynamic-v1.2.0';
const IMAGE_CACHE = 'fractalmerch-images-v1.2.0';

// Cache strategies
const CACHE_STRATEGIES = {
    CACHE_FIRST: 'cache-first',
    NETWORK_FIRST: 'network-first',
    STALE_WHILE_REVALIDATE: 'stale-while-revalidate',
    NETWORK_ONLY: 'network-only',
    CACHE_ONLY: 'cache-only'
};

// Static assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/assets/css/style.css',
    '/assets/js/main.js',
    '/assets/js/enhanced-cart.js',
    '/assets/js/performance-optimizer.js',
    '/assets/images/icon.png',
    '/assets/images/icon.ico',
    '/particulares.php',
    '/customize-shirt.php',
    '/offline.html'
];

// Routes and their caching strategies
const ROUTE_STRATEGIES = [
    {
        pattern: /\.(js|css)$/,
        strategy: CACHE_STRATEGIES.STALE_WHILE_REVALIDATE,
        cache: STATIC_CACHE
    },
    {
        pattern: /\.(png|jpg|jpeg|gif|webp|svg|ico)$/,
        strategy: CACHE_STRATEGIES.CACHE_FIRST,
        cache: IMAGE_CACHE,
        expiration: 30 * 24 * 60 * 60 * 1000 // 30 days
    },
    {
        pattern: /\.(php|html)$/,
        strategy: CACHE_STRATEGIES.NETWORK_FIRST,
        cache: DYNAMIC_CACHE,
        expiration: 24 * 60 * 60 * 1000 // 24 hours
    },
    {
        pattern: /\/api\//,
        strategy: CACHE_STRATEGIES.NETWORK_FIRST,
        cache: DYNAMIC_CACHE,
        expiration: 5 * 60 * 1000 // 5 minutes
    }
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('Service Worker installing...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('Caching static assets...');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('Static assets cached successfully');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('Failed to cache static assets:', error);
            })
    );
});

// Activate event - cleanup old caches
self.addEventListener('activate', event => {
    console.log('Service Worker activating...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                const deletePromises = cacheNames
                    .filter(cacheName => {
                        return cacheName !== STATIC_CACHE && 
                               cacheName !== DYNAMIC_CACHE && 
                               cacheName !== IMAGE_CACHE;
                    })
                    .map(cacheName => {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    });
                
                return Promise.all(deletePromises);
            })
            .then(() => {
                console.log('Service Worker activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - handle requests with caching strategies
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip chrome-extension and other protocols
    if (!url.protocol.startsWith('http')) {
        return;
    }
    
    // Find matching route strategy
    const route = findRouteStrategy(request.url);
    
    if (route) {
        event.respondWith(handleRequest(request, route));
    } else {
        // Default to network first for unmatched routes
        event.respondWith(
            handleRequest(request, {
                strategy: CACHE_STRATEGIES.NETWORK_FIRST,
                cache: DYNAMIC_CACHE
            })
        );
    }
});

/**
 * Find the appropriate caching strategy for a URL
 */
function findRouteStrategy(url) {
    for (const route of ROUTE_STRATEGIES) {
        if (route.pattern.test(url)) {
            return route;
        }
    }
    return null;
}

/**
 * Handle request based on caching strategy
 */
async function handleRequest(request, route) {
    const { strategy, cache: cacheName, expiration } = route;
    
    try {
        switch (strategy) {
            case CACHE_STRATEGIES.CACHE_FIRST:
                return await cacheFirst(request, cacheName, expiration);
            
            case CACHE_STRATEGIES.NETWORK_FIRST:
                return await networkFirst(request, cacheName, expiration);
            
            case CACHE_STRATEGIES.STALE_WHILE_REVALIDATE:
                return await staleWhileRevalidate(request, cacheName, expiration);
            
            case CACHE_STRATEGIES.NETWORK_ONLY:
                return await fetch(request);
            
            case CACHE_STRATEGIES.CACHE_ONLY:
                return await cacheOnly(request, cacheName);
            
            default:
                return await networkFirst(request, cacheName, expiration);
        }
    } catch (error) {
        console.error('Request handling failed:', error);
        return await handleOffline(request);
    }
}

/**
 * Cache First strategy
 */
async function cacheFirst(request, cacheName, expiration) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
        // Check if cache is expired
        if (expiration && isCacheExpired(cachedResponse, expiration)) {
            // Try to update cache in background
            updateCacheInBackground(request, cacheName);
        }
        return cachedResponse;
    }
    
    // Not in cache, fetch from network
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
        const responseClone = networkResponse.clone();
        await cache.put(request, responseClone);
    }
    
    return networkResponse;
}

/**
 * Network First strategy
 */
async function networkFirst(request, cacheName, expiration) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(cacheName);
            const responseClone = networkResponse.clone();
            await cache.put(request, responseClone);
        }
        
        return networkResponse;
    } catch (error) {
        // Network failed, try cache
        const cache = await caches.open(cacheName);
        const cachedResponse = await cache.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        throw error;
    }
}

/**
 * Stale While Revalidate strategy
 */
async function staleWhileRevalidate(request, cacheName, expiration) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    
    // Start network request immediately
    const networkPromise = fetch(request)
        .then(networkResponse => {
            if (networkResponse.ok) {
                const responseClone = networkResponse.clone();
                cache.put(request, responseClone);
            }
            return networkResponse;
        })
        .catch(error => {
            console.warn('Network request failed:', error);
            return null;
        });
    
    // Return cached response immediately if available
    if (cachedResponse) {
        return cachedResponse;
    }
    
    // If no cache, wait for network
    return await networkPromise;
}

/**
 * Cache Only strategy
 */
async function cacheOnly(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
        return cachedResponse;
    }
    
    throw new Error('Resource not found in cache');
}

/**
 * Check if cached response is expired
 */
function isCacheExpired(response, expiration) {
    if (!expiration) return false;
    
    const cachedDate = response.headers.get('date');
    if (!cachedDate) return false;
    
    const cacheTime = new Date(cachedDate).getTime();
    const now = Date.now();
    
    return (now - cacheTime) > expiration;
}

/**
 * Update cache in background
 */
async function updateCacheInBackground(request, cacheName) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(cacheName);
            await cache.put(request, networkResponse.clone());
        }
    } catch (error) {
        console.warn('Background cache update failed:', error);
    }
}

/**
 * Handle offline scenarios
 */
async function handleOffline(request) {
    const url = new URL(request.url);
    
    // For HTML pages, return offline page
    if (request.headers.get('accept') && request.headers.get('accept').includes('text/html')) {
        const cache = await caches.open(STATIC_CACHE);
        return await cache.match('/offline.html');
    }
    
    // For images, return placeholder
    if (request.url.match(/\.(png|jpg|jpeg|gif|webp|svg)$/)) {
        const cache = await caches.open(IMAGE_CACHE);
        return await cache.match('/assets/images/offline-placeholder.png');
    }
    
    // For other resources, return a generic response
    return new Response('Offline', {
        status: 503,
        statusText: 'Service Unavailable',
        headers: {
            'Content-Type': 'text/plain'
        }
    });
}

// Background sync for failed requests
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(handleBackgroundSync());
    }
});

/**
 * Handle background sync
 */
async function handleBackgroundSync() {
    // Retry failed analytics requests
    const failedRequests = await getFailedRequests();
    
    for (const request of failedRequests) {
        try {
            await fetch(request);
            await removeFailedRequest(request);
        } catch (error) {
            console.warn('Background sync failed for request:', request.url);
        }
    }
}

/**
 * Get failed requests from IndexedDB
 */
async function getFailedRequests() {
    // Implementation would use IndexedDB to store failed requests
    return [];
}

/**
 * Remove failed request from storage
 */
async function removeFailedRequest(request) {
    // Implementation would remove from IndexedDB
}

// Push notifications
self.addEventListener('push', event => {
    if (!event.data) return;
    
    const data = event.data.json();
    
    const options = {
        body: data.body,
        icon: '/assets/images/icon.png',
        badge: '/assets/images/badge.png',
        data: data.data,
        actions: data.actions || []
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    if (event.action) {
        // Handle specific action
        handleNotificationAction(event.action, event.notification.data);
    } else {
        // Default action - open app
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/')
        );
    }
});

/**
 * Handle notification actions
 */
function handleNotificationAction(action, data) {
    switch (action) {
        case 'view-product':
            clients.openWindow(data.productUrl);
            break;
        case 'view-cart':
            clients.openWindow('/cart.php');
            break;
        case 'dismiss':
            // Do nothing, notification is already closed
            break;
    }
}

// Message handling for communication with main thread
self.addEventListener('message', event => {
    console.log('Service Worker received message:', event.data);
    
    if (event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    } else if (event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    } else if (event.data.type === 'CLEAR_CACHE') {
        clearAllCaches().then(() => {
            event.ports[0].postMessage({ success: true });
        });
    }
});

async function clearAllCaches() {
    const cacheNames = await caches.keys();
    return Promise.all(
        cacheNames.map(cacheName => caches.delete(cacheName))
    );
}

console.log('FractalMerch Service Worker v1.2.0 loaded successfully');