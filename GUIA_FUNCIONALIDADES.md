# 🚀 FractalMerch - Guía Completa de Funcionalidades Avanzadas

## 📋 Índice de Funcionalidades

1. [🤖 Advanced Personalization Engine](#-advanced-personalization-engine)
2. [🔬 A/B Testing Framework](#-ab-testing-framework)
3. [📧 Email Marketing Automation](#-email-marketing-automation)
4. [🔔 Push Notifications System](#-push-notifications-system)
5. [📱 Progressive Web App (PWA)](#-progressive-web-app-pwa)
6. [📊 Heatmap Analytics](#-heatmap-analytics)
7. [⚡ Performance Optimization](#-performance-optimization)
8. [🎯 Conversion Funnel Tracking](#-conversion-funnel-tracking)

---

## 🤖 Advanced Personalization Engine

### ¿Qué hace?
Sistema de IA que analiza el comportamiento del usuario en tiempo real para personalizar la experiencia de compra.

### Cómo funciona automáticamente:
- **Análisis de comportamiento:** Trackea clicks, tiempo en página, scroll, interacciones con carrito
- **Predicción de intención:** Calcula probabilidad de compra (high/medium/low intent)
- **Segmentación inteligente:** Clasifica usuarios (new_visitor, price_sensitive, design_enthusiast, etc.)
- **Personalización dinámica:** Cambia headlines, CTAs, productos destacados, precios según el perfil

### Personalizations que verás:
```javascript
// Ejemplos de personalización automática:
- Headlines dinámicos: "¡Finaliza tu pedido personalizado!" (high intent)
- CTAs adaptados: "Terminar mi pedido" vs "Explorar productos"
- Badges en productos: "MEJOR PRECIO" (price sensitive) vs "CALIDAD PREMIUM"
- Mensajes de urgencia: "⚡ ¡Pocos diseños como este disponibles!"
- Descuentos personalizados: "¡Ahorrás $500!" para usuarios price-sensitive
```

### Cómo usar manualmente:
```javascript
// Obtener datos de personalización
const data = window.getPersonalizationData();
console.log('Perfil del usuario:', data.userProfile);
console.log('Segmento actual:', data.activePersonalizations.segment);
console.log('Intención de compra:', data.activePersonalizations.buyingIntent);

// Limpiar datos de personalización
window.clearPersonalizationData();

// Disparar eventos para personalización
document.dispatchEvent(new CustomEvent('user_registered', {
    detail: { email: 'user@example.com', name: 'Juan' }
}));

document.dispatchEvent(new CustomEvent('cart_abandoned', {
    detail: { cartValue: 15000, items: 3 }
}));
```

---

## 🔬 A/B Testing Framework

### ¿Qué hace?
Sistema de testing que prueba diferentes versiones de elementos para optimizar conversiones.

### Tests activos automáticamente:
1. **Checkout Steps:** `single_page` vs `multi_step` vs `progressive`
2. **CTA Text:** `Comprar Ahora` vs `Finalizar Pedido` vs `Hacer Realidad`
3. **Trust Signals:** `badges_top` vs `badges_sidebar` vs `badges_bottom`
4. **Product Layout:** `grid_classic` vs `grid_compact` vs `list_detailed`
5. **Price Format:** `large_bold` vs `with_savings` vs `installments`
6. **Navigation:** `minimal` vs `detailed` vs `mega_menu`

### Cómo funciona:
- **Asignación consistente:** Cada usuario siempre ve la misma variante
- **Tracking automático:** Convierte clicks, add-to-cart, checkouts automáticamente
- **Hash-based assignment:** Usa user ID para asignar variantes de forma consistente

### Cómo usar manualmente:
```javascript
// Ver resultados de A/B testing
const results = window.ABTest.getResults();
console.log('Tests del usuario:', results.tests);
console.log('Conversiones:', results.conversions);

// Forzar una variante específica (solo para testing)
window.ABTest.forceVariant('cta_text', 'Comprar Ahora');

// Trackear conversión manualmente
window.ABTest.track('checkout_steps', 'custom_conversion', 100);

// Resetear todos los tests
window.ABTest.reset();
```

### Análisis de resultados:
```javascript
// Los datos se envían automáticamente a /api/analytics/ab-test
// Estructura del evento:
{
    userId: "user_123",
    testName: "cta_text",
    variant: "Comprar Ahora",
    action: "add_to_cart",
    value: 5999,
    timestamp: 1704067200000
}
```

---

## 📧 Email Marketing Automation

### ¿Qué hace?
Sistema completo de email marketing con campañas automatizadas y personalización avanzada.

### Campañas automáticas:
1. **Serie de Bienvenida** (3 emails):
   - Email 1: Inmediato - "¡Bienvenido a FractalMerch! 🎨"
   - Email 2: 24 horas - "Consejos para crear diseños únicos"
   - Email 3: 72 horas - "¡15% OFF en tu primera compra!"

2. **Abandono de Carrito** (3 emails):
   - Email 1: 1 hora - "Tu diseño te está esperando... 🛒"
   - Email 2: 24 horas - "¡No pierdas tu diseño! 10% de descuento"
   - Email 3: 72 horas - "Última oportunidad - Tu carrito expira pronto"

3. **Post-Compra** (3 emails):
   - Email 1: Inmediato - "¡Pedido confirmado! Tu remera está en producción"
   - Email 2: 48 horas - "Tu remera está siendo creada con amor ❤️"
   - Email 3: 7 días - "¿Cómo quedó tu remera? ¡Cuéntanos!"

4. **Reactivación** (2 emails):
   - Email 1: Usuarios inactivos - "Te extrañamos... ¡Volvé con estilo!"
   - Email 2: 5 días después - "25% OFF especial para vos 🎁"

### Personalización automática:
```javascript
// Variables de personalización disponibles:
{user_name}: "Juan Pérez"
{first_name}: "Juan"
{last_purchase}: "15/01/2025"
{favorite_category}: "Remeras"
{cart_items}: HTML con productos del carrito
{discount_code}: "WELCOME15"
{days_since_signup}: "7"
```

### A/B Testing en emails:
- **Subject lines:** 3 variantes por campaña
- **Send times:** 9 AM, 2 PM, 7 PM
- **CTA buttons:** "Finalizar Compra" vs "Crear Mi Remera" vs "Ver Mi Carrito"

### Cómo disparar campañas manualmente:
```javascript
// Disparar campaña de bienvenida
window.EmailMarketing.trigger('welcome_series', {
    email: 'usuario@example.com',
    name: 'Juan Pérez',
    signup_date: Date.now()
});

// Disparar abandono de carrito
document.dispatchEvent(new CustomEvent('cart_abandoned', {
    detail: {
        email: 'usuario@example.com',
        cart: [
            { name: 'Remera Personalizada', price: 5999 }
        ],
        cartValue: 5999
    }
}));

// Ver analytics de email marketing
const analytics = window.EmailMarketing.analytics();
console.log('Emails enviados:', analytics.metrics.sent);
console.log('Performance por campaña:', analytics.campaign_performance);

// Pausar/reanudar campañas
window.EmailMarketing.pause('cart_abandonment');
window.EmailMarketing.resume('cart_abandonment');
```

---

## 🔔 Push Notifications System

### ¿Qué hace?
Sistema de notificaciones push inteligente con prompts basados en engagement y automatización completa.

### Tipos de notificaciones:
1. **Welcome:** "¡Bienvenido a FractalMerch! 🎨"
2. **Cart Reminder:** "Tu carrito te está esperando 🛒"
3. **Order Update:** "Tu remera está en producción 📦"
4. **New Product:** "Nuevo producto disponible ✨"
5. **Special Offer:** "Oferta especial para vos 🎁"
6. **Back in Stock:** "Producto disponible otra vez 📦"
7. **Design Inspiration:** "Inspiración para tu próximo diseño 💡"

### Automatización inteligente:
```javascript
// Reglas de automatización activas:
welcome_sequence: [
    { type: 'welcome', delay: 0 },
    { type: 'design_inspiration', delay: 24h },
    { type: 'special_offer', delay: 72h }
]

cart_abandonment: [
    { type: 'cart_reminder', delay: 1h },
    { type: 'special_offer', delay: 24h }
]

order_tracking: [
    { type: 'order_update', delay: 0 } // Al cambiar estado
]
```

### Smart Permission Prompting:
- **Engagement score:** Basado en pageViews, timeOnSite, cartInteractions
- **Trigger:** Solo cuando engagement > 50%
- **Timing:** Después de 30 segundos de actividad
- **UI amigable:** Popup con beneficios claros

### Cómo usar manualmente:
```javascript
// Solicitar permisos de notificación
window.PushNotifications.request();

// Enviar notificación de prueba
window.PushNotifications.test('welcome');

// Comprobar estado de suscripción
const isSubscribed = window.PushNotifications.isSubscribed();
console.log('Usuario suscrito:', isSubscribed);

// Ver analytics
const analytics = window.PushNotifications.analytics();
console.log('Notificaciones enviadas:', analytics.sent);
console.log('Clicks:', analytics.clicked);

// Disparar eventos para automatización
document.dispatchEvent(new CustomEvent('user_registered'));
document.dispatchEvent(new CustomEvent('cart_abandoned'));
document.dispatchEvent(new CustomEvent('order_placed'));
```

### Acciones en notificaciones:
```javascript
// Cada notificación puede tener botones de acción:
actions: [
    { action: 'create', title: 'Crear Diseño' },
    { action: 'browse', title: 'Ver Productos' },
    { action: 'checkout', title: 'Finalizar Compra' },
    { action: 'track', title: 'Seguir Pedido' }
]

// Rutas automáticas por acción:
create → /customize-shirt.php
browse → /particulares.php
checkout → /checkout.php
track → /track-order.php
```

---

## 📱 Progressive Web App (PWA)

### ¿Qué hace?
Convierte FractalMerch en una app instalable con funcionalidad offline y experiencia nativa.

### Características PWA:
- **Instalable:** Prompt inteligente basado en engagement
- **Offline:** Funciona sin internet usando cache
- **Updates:** Notificaciones automáticas de actualizaciones
- **Native feel:** Pantalla completa, iconos, shortcuts

### Install Prompt inteligente:
```javascript
// Criterios para mostrar install prompt:
- pageViews >= 3
- timeOnSite > 3 minutos  
- engagement > 50%
- No previamente rechazado

// UI del prompt incluye:
- "¡Instalá FractalMerch!"
- Beneficios: "📱 Acceso instantáneo", "🚀 Carga súper rápida"
- "🔄 Funciona sin internet", "🔔 Notificaciones push"
```

### Funcionalidad Offline:
- **Páginas cached:** index.php, particulares.php, customize-shirt.php
- **Assets cached:** CSS, JS, imágenes críticas
- **Sync automático:** Al volver online sincroniza carrito y analytics
- **Offline indicator:** Banner que aparece sin conexión

### Cómo usar manualmente:
```javascript
// Forzar install prompt
window.PWA.install();

// Aplicar update
window.PWA.update();

// Ver estado PWA
const status = window.PWA.status();
console.log('Instalado:', status.isInstalled);
console.log('Actualización disponible:', status.isUpdateAvailable);
console.log('Online:', status.isOnline);

// Ver estado de red
const network = window.PWA.network();
console.log('Tipo de conexión:', network.connectionType);
console.log('Velocidad descarga:', network.downlink);

// Limpiar cache
window.PWA.clearCache();
```

### App Shortcuts:
```json
// Shortcuts disponibles en la app instalada:
[
    { name: "Personalizar Remera", url: "/customize-shirt.php" },
    { name: "Ver Productos", url: "/particulares.php" },
    { name: "Mi Carrito", url: "/checkout.php" },
    { name: "Rastrear Pedido", url: "/track-order.php" }
]
```

### Service Worker Strategies:
- **Cache First:** Imágenes, CSS, JS (rápido)
- **Network First:** APIs, PHP pages (datos frescos)
- **Stale While Revalidate:** Assets (balance velocidad/frescura)

---

## 📊 Heatmap Analytics

### ¿Qué hace?
Sistema completo de analytics de comportamiento de usuario con heatmaps, grabaciones de sesión y métricas avanzadas.

### GDPR Compliance:
- **Banner de consentimiento:** 3 opciones (Aceptar/Rechazar/Configurar)
- **Configuración granular:** Analytics, heatmaps, grabaciones por separado
- **Sampling ético:** 10% grabaciones, 25% heatmaps
- **Anonimización:** Sin datos personales en grabaciones

### Tracking automático:
```javascript
// Eventos trackeados automáticamente:
- Click tracking: Coordenadas, elementos, texto, contexto
- Scroll tracking: Profundidad, tiempo hasta scroll, milestones
- Form tracking: Interacciones, envíos, abandono de campos
- Error tracking: JavaScript errors, promise rejections
- Performance tracking: Core Web Vitals, métricas de carga

// E-commerce events específicos:
- Product views, cart actions, checkout steps
- Session recording con metadata completa
```

### Panel Admin Analytics:
- **URL:** `/admin/heatmap-analytics.php`
- **5 pestañas especializadas:**
  1. **Overview:** Métricas generales, top pages, device distribution
  2. **Heatmap:** Visualización de clics con controles de intensidad
  3. **Scroll Analysis:** Profundidad de scroll por página
  4. **User Flow:** Transiciones entre páginas
  5. **Session Recordings:** Lista de grabaciones con reproductor

### Integraciones listas:
```javascript
// Microsoft Clarity - Configurar ID
clarityId: 'YOUR_CLARITY_ID'

// Hotjar - Configurar ID  
hotjarId: 'YOUR_HOTJAR_ID'

// Custom tracking configurado automáticamente
```

### Métricas disponibles:
- **Sesiones totales** y usuarios únicos con tendencias
- **Elementos más clickeados** con ranking y frecuencia
- **Análisis de scroll** por profundidad y tiempo
- **Duración promedio** de sesiones con benchmarks
- **Flujo de navegación** entre páginas con tasas de conversión

### APIs para datos:
```javascript
// Endpoint principal
GET /api/analytics/heatmap-summary
GET /api/analytics/heatmap-clicks?page=/particulares.php
GET /api/analytics/heatmap-scrolls?page=/index.php
GET /api/analytics/heatmap-user-flow
GET /api/analytics/heatmap-recordings

// Rate limiting: 60 eventos/minuto por sesión
```

---

## ⚡ Performance Optimization

### ¿Qué hace?
Sistema de optimización automática de performance con Core Web Vitals tracking y Service Worker avanzado.

### Core Web Vitals tracking:
```javascript
// Métricas trackeadas en tiempo real:
- LCP (Largest Contentful Paint): < 2.5s ✅
- FID (First Input Delay): < 100ms ✅  
- CLS (Cumulative Layout Shift): < 0.1 ✅
- FCP (First Contentful Paint): < 1.8s ✅
- TTFB (Time to First Byte): < 800ms ✅
```

### Optimizaciones automáticas:
1. **Critical CSS inline:** Primera vista optimizada
2. **Resource preloading:** Fonts, imágenes, CSS críticos
3. **Lazy loading avanzado:** Imágenes below-the-fold
4. **Service Worker caching:** Assets críticos cached
5. **Image optimization:** WebP/AVIF con fallbacks
6. **JavaScript optimization:** Code splitting, defer

### Performance Budget Monitoring:
```javascript
// Alertas automáticas si:
- Page load time > 3 segundos
- LCP > 2.5 segundos
- FID > 100ms
- CLS > 0.1

// Envío a analytics automático:
gtag('event', 'page_load_time', {
    'metric1': loadTime
});
```

### Endpoints de métricas:
```php
// Envío de métricas
POST /api/analytics/web-vitals
POST /api/analytics/performance

// Consulta de métricas
GET /api/analytics/performance?days=7&url=/index.php

// Respuesta incluye:
{
    "summary": {
        "avg_lcp": 1800,
        "avg_fid": 85,
        "avg_cls": 0.08
    },
    "grades": {
        "lcp": "A",
        "fid": "A", 
        "cls": "A"
    }
}
```

---

## 🎯 Conversion Funnel Tracking

### ¿Qué hace?
Tracking completo del customer journey desde la primera visita hasta la compra.

### 8-Step Funnel automático:
1. **Landing:** Primera visita al sitio
2. **Browse:** Navegación por productos
3. **Product View:** Vista de producto específico
4. **Customize:** Uso del editor de remeras
5. **Add to Cart:** Agregar producto al carrito
6. **Checkout Start:** Inicio del proceso de checkout
7. **Payment:** Ingreso de datos de pago
8. **Purchase Complete:** Compra finalizada

### Exit Intent Detection:
```javascript
// Triggers automáticos:
- Movimiento del mouse hacia arriba (salir)
- Tab visibility change (cambio de pestaña)
- Scroll hacia arriba después de inactividad
- Tiempo en página > 3 minutos sin interacción

// Ofertas por segmento:
- new_visitor: "¡Primera compra con 15% OFF!"
- returning_customer: "¡Bienvenido de vuelta! 10% de descuento"
- high_intent_non_buyer: "¡Última oportunidad! Completá tu pedido"
- browser: "¿Te interesa? Llevátelo con descuento"
```

### Cart Abandonment Recovery:
```javascript
// Análisis behavioral:
- Tiempo en carrito
- Productos en carrito
- Intentos de checkout fallidos
- Razones de abandono detectadas

// Recovery automático:
- Email sequence (3 emails)
- Push notifications (2 notificaciones)
- Retargeting campaigns
- Discount escalation (5% → 10% → 15%)
```

### Segmentación avanzada:
```javascript
// 4 segmentos principales:
new_visitor: {
    conditions: { 
        registration_date: "> 7 days ago",
        purchase_count: 0 
    }
}

returning_customer: {
    conditions: {
        purchase_count: "> 0",
        last_purchase: "> 90 days ago"
    }
}

high_intent_non_buyer: {
    conditions: {
        cart_interactions: "> 3",
        time_in_checkout: "> 2 minutes",
        purchase_count: 0
    }
}

browser: {
    conditions: {
        page_views: "> 5",
        time_on_site: "> 5 minutes",
        cart_interactions: 0
    }
}
```

---

## 🔧 Cómo Activar/Desactivar Funcionalidades

### Configuración Global:
```javascript
// En cada archivo principal hay configuración:

// advanced-personalization.js
this.config = {
    enablePersonalization: true,    // Activar personalización
    enableAI: true,                 // Activar predicciones IA
    enableBehaviorTracking: true,   // Trackeo de comportamiento
    enableGeoTargeting: true        // Targeting geográfico
};

// ab-testing.js - Activar/desactivar tests individuales
this.tests = {
    'checkout_steps': { active: true },
    'cta_text': { active: false }   // Desactivar este test
};

// email-marketing.js
this.config = {
    enableEmailMarketing: true,
    enableAutomation: true,
    enablePersonalization: true,
    enableABTesting: true
};

// push-notifications.js
this.config = {
    enablePushNotifications: true,
    enableAutomation: true,
    enablePersonalization: true
};

// pwa-manager.js
this.config = {
    enablePWA: true,
    enableInstallPrompt: true,
    enableUpdateNotifications: true,
    enableOfflineMode: true
};
```

### Debug Mode:
```javascript
// Activar modo debug para ver todos los logs
localStorage.setItem('debug_mode', 'true');

// Ver todos los eventos en tiempo real
localStorage.setItem('verbose_logging', 'true');

// Mostrar indicadores A/B testing
document.querySelector('.ab-test-indicator').style.display = 'block';
```

### Analytics Endpoints:
```javascript
// Verificar que los endpoints funcionan:
GET /api/analytics/web-vitals     // Core Web Vitals
GET /api/analytics/performance    // Performance general
GET /api/analytics/heatmap-summary // Heatmap data
POST /api/analytics/ab-test       // A/B test results
POST /api/email-marketing/send    // Email sending
POST /api/push-notifications/send // Push notifications
```

---

## 📈 Monitoreo y Analytics

### Dashboards disponibles:
1. **Admin Dashboard:** `/admin/dashboard.php` - Overview general
2. **Heatmap Analytics:** `/admin/heatmap-analytics.php` - Comportamiento usuarios
3. **A/B Testing Results:** Data en localStorage + endpoints
4. **Email Performance:** Data en emailMarketing.getAnalytics()
5. **Push Performance:** Data en pushNotifications.getAnalytics()
6. **PWA Metrics:** Data en pwaManager.getInstallationStatus()

### Métricas clave a monitorear:
- **Conversion Rate:** % de visitantes que compran
- **Cart Abandonment:** % de carritos abandonados
- **Email Open Rate:** % de emails abiertos
- **Push Click Rate:** % de notificaciones clickeadas
- **PWA Install Rate:** % de usuarios que instalan la app
- **Performance Scores:** Grades A/B/C/D para Core Web Vitals

### Alertas automáticas:
- Performance budget exceeded
- Conversion rate drop > 20%
- Email bounce rate > 5%
- Push notification failure > 10%
- Service Worker errors

---

¡Esta guía cubre todas las funcionalidades implementadas en FractalMerch! Cada sistema funciona automáticamente pero también puede ser controlado manualmente según tus necesidades.

Para soporte técnico o dudas específicas, revisa los logs del navegador donde cada sistema registra su actividad detalladamente.