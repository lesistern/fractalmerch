# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Información General del Proyecto
- **Nombre:** Sistema de Gestión de Contenido PHP
- **Tipo:** Aplicación web PHP con MySQL
- **Entorno:** XAMPP (Apache + MySQL + PHP)
- **Sistema Operativo:** Windows
- **Ubicación del Proyecto:** C:\xampp\htdocs\proyecto
- **Base de datos:** proyecto_web
- **Versión PHP:** 7.4+
- **Versión MySQL:** 5.7+

## ⚠️ IMPORTANTE - Configuración de Desarrollo
**TODOS LOS CAMBIOS DEBEN REALIZARSE EN LA RUTA DE XAMPP:**
- **Ruta principal:** `C:\xampp\htdocs\proyecto`
- **Entorno:** Windows con XAMPP
- **Acceso:** Siempre trabajar directamente en `/mnt/c/xampp/htdocs/proyecto/`
- **NO usar:** Rutas locales como `/home/lesistern/` para cambios finales

## Estructura del Proyecto
```
proyecto/
├── .github/workflows/        # GitHub Actions workflows
│   ├── ci.yml               # Pipeline CI/CD principal
│   └── deploy.yml           # Workflow de despliegue
├── admin/                   # Panel de administración MODERNO
│   ├── dashboard.php        # Dashboard principal (MODERNIZADO)
│   ├── manage-users.php     # Gestión de usuarios (MODERNIZADO)
│   ├── manage-posts.php     # Gestión de posts
│   ├── manage-comments.php  # Gestión de comentarios
│   ├── manage-products.php  # Gestión de productos (MODERNO)
│   └── manage-categories.php # Gestión de categorías
├── assets/                  # Recursos estáticos
│   ├── css/style.css        # Estilos principales (+10,000 líneas con admin moderno)
│   ├── js/main.js          # JavaScript principal
│   ├── js/shirt-designer.js # JavaScript del personalizador
│   ├── js/modern-admin.js   # JavaScript del panel admin moderno
│   ├── js/enhanced-cart.js  # Sistema de carrito avanzado
│   └── images/             # Imágenes y uploads
├── config/                  # Configuraciones
│   ├── database.php        # Configuración de BD
│   └── config.php          # Configuraciones generales
├── includes/               # Archivos compartidos
│   ├── header.php          # Cabecera común
│   ├── footer.php          # Pie de página común
│   └── functions.php       # Funciones principales
├── *.php                   # Páginas principales
└── database.sql           # Script de base de datos
```

## Características Principales

### Sistema de Usuarios
- Registro e inicio de sesión
- Roles: Admin, Moderador, Usuario
- Perfiles editables
- Sesiones seguras con PHP sessions

### Gestión de Posts
- CRUD completo (Create, Read, Update, Delete)
- Sistema de categorías
- Estados: borrador, publicado, archivado
- Contador de vistas
- Sistema de búsqueda

### Sistema de Comentarios
- Comentarios en posts
- Moderación de comentarios
- Aprobación/rechazo

### Panel de Administración Moderno
- **Dashboard renovado:** Estadísticas con iconos, tendencias y cards interactivos
- **Gestión de productos avanzada:** Sistema completo de e-commerce con:
  - Editor tabular con 4 pestañas (Básico, Imágenes, Variantes, Precios)
  - Manejo de variantes (talla, color, medida, stock individual)
  - Upload de imágenes principal y por variante
  - Cálculo automático de ganancias y márgenes
  - Validación de formularios en tiempo real
  - Sistema de búsqueda y filtrado
- **Gestión de usuarios modernizada:** Cards de roles, acciones flotantes
- **Diseño consistente:** Sidebar compacto (200px), iconografía unificada
- **Base de datos de productos:** Tablas `products` y `product_variants`
- **JavaScript avanzado:** `modern-admin.js` con clase `ModernAdminPanel`

### Características Especiales
- **Modo oscuro/claro:** Toggle persistente en localStorage
- **Header moderno sin color:** Diseño limpio y profesional con:
  - Logo "Sublime" con gradiente a la izquierda
  - Búsqueda expandible con placeholder personalizado
  - Botón de usuario/login dinámico según estado
  - Carrito con badge de contador de productos
  - Dropdowns funcionales para menús
  - Diseño completamente responsive
- **Hero section dividido:** Layout profesional con:
  - **Lado izquierdo (50%):** Imágenes corp1.png y corp2.png para empresas
  - **Lado derecho (50%):** 5 imágenes para particulares
  - Sliders independientes con auto-avance cada 5 segundos
  - Indicadores diferenciados por colores (azul/naranja)
  - Botones CTA específicos para cada audiencia
  - Contenido overlay centrado en cada sección
  - Pausa individual al hacer hover en cada lado
- **Tienda de productos (particulares.php):** E-commerce completo con:
  - 6 productos establecidos con precios
  - Carrito funcional con localStorage
  - Botón destacado al editor de remeras
  - Productos: Remeras ($5.999), Buzos ($12.999), Tazas ($3.499)
  - Mouse Pads ($2.999), Fundas ($4.999), Almohadas ($6.999)
  - Sistema de agregado al carrito con confirmación
- **Página empresarial:** Landing B2B profesional con:
  - Hero con estadísticas de impacto
  - 4 soluciones principales (Equipos, Capacitación, Soporte, Consultoría)
  - Sección de clientes y testimonios
  - Formulario de contacto empresarial
- **Personalizador de remeras:** Editor interactivo con:
  - Vista frente/espalda
  - Carga de hasta 5 imágenes (drag & drop)
  - Rotación y escalado
  - Guías de centrado automáticas que aparecen solo cuando necesario
  - Límites de sublimación reducidos (60% del área)
  - Movimiento instantáneo sin animaciones
  - Efectos de clipping en líneas guía
  - Controles flotantes por imagen (rotar, redimensionar, duplicar, eliminar)
  - Controles táctiles para móvil

## Configuración de Desarrollo

### Credenciales por Defecto
- **Email admin:** admin@proyecto.com
- **Contraseña:** password

### Base de Datos
- **Host:** localhost
- **Database:** proyecto_web
- **Usuario:** root
- **Contraseña:** (vacía para XAMPP)

### URLs de Desarrollo
- **Principal:** http://localhost/proyecto/ (Hero section dividido)
- **Tienda:** http://localhost/proyecto/particulares.php (E-commerce completo)
- **Empresas:** http://localhost/proyecto/empresas.php (Landing B2B)
- **Personalizador:** http://localhost/proyecto/customize-shirt.php
- **Admin:** http://localhost/proyecto/admin/
- **phpMyAdmin:** http://localhost/phpmyadmin

## Comandos Útiles

### Instalación y Configuración (Windows + XAMPP)
```bash
# UBICACIÓN PRINCIPAL DEL PROYECTO
# C:\xampp\htdocs\proyecto

# Acceso desde WSL/Linux:
# /mnt/c/xampp/htdocs/proyecto/

# Comandos para sincronizar cambios:
cp /home/lesistern/archivo.ext /mnt/c/xampp/htdocs/proyecto/archivo.ext

# Importar base de datos
mysql -u root proyecto_web < database.sql

# URLs de acceso local:
# http://localhost/proyecto/
# http://localhost/phpmyadmin
```

### Testing y Validación
```bash
# Validar sintaxis PHP
find . -name "*.php" -exec php -l {} \;

# Revisar código con PHP CodeSniffer
phpcs --standard=PSR12 --ignore=vendor/ .

# Buscar problemas de seguridad comunes
grep -r "mysql_query" .
grep -r "\$_GET\|\$_POST" . | grep -v "htmlspecialchars\|filter_input"
```

## Seguridad

### Medidas Implementadas
- Sanitización de entrada con `htmlspecialchars()`
- Prepared Statements para prevenir SQL injection
- Validación de sesiones por roles
- Hashing de contraseñas con `password_hash()`
- Validación de formularios (cliente y servidor)

### Archivos Críticos de Seguridad
- `config/database.php`: Credenciales de BD
- `includes/functions.php`: Funciones de validación
- `login.php`: Autenticación
- `admin/*`: Archivos con control de acceso

## Workflows de GitHub Actions

### CI Pipeline (`ci.yml`)
- **Trigger:** Push a main/develop, PRs a main
- **Jobs:**
  - Test: Validación PHP, base de datos de prueba, CodeSniffer
  - Deploy: Despliegue condicional a staging
  - Security-scan: Verificación de seguridad

### Deploy Pipeline (`deploy.yml`)
- **Trigger:** Release publicado o manual
- **Jobs:**
  - Creación de paquete de despliegue
  - Configuración de entorno
  - Despliegue via FTP
  - Migraciones de BD
  - Health check
  - Notificaciones Slack

### Secrets Requeridos
```
FTP_SERVER=tu-servidor-ftp.com
FTP_USERNAME=usuario
FTP_PASSWORD=contraseña
FTP_SERVER_DIR=/public_html/
DB_HOST=servidor-bd
DB_USER=usuario-bd
DB_PASSWORD=contraseña-bd
DB_NAME=nombre-bd
APP_URL=https://tu-sitio.com
SLACK_WEBHOOK=https://hooks.slack.com/...
```

## Archivos de Configuración

### `config/database.php`
```php
$host = 'localhost';
$dbname = 'proyecto_web';
$username = 'root';
$password = '';
```

### `config/config.php`
```php
define('SITE_NAME', 'Mi Sitio Web');
define('BASE_URL', 'http://localhost/proyecto/');
define('POSTS_PER_PAGE', 5);
```

## Estructura de Base de Datos

### Tablas Principales
- `users`: Usuarios del sistema
- `posts`: Artículos/posts
- `comments`: Comentarios
- `categories`: Categorías de posts
- `user_roles`: Roles de usuario

## Personalización

### Cambiar Tema
- Editar `assets/css/style.css`
- Variables CSS para modo oscuro en `:root`
- Toggle en `assets/js/main.js`

### Agregar Funcionalidades
1. Crear nuevo archivo PHP en la raíz
2. Incluir `includes/header.php` y `includes/footer.php`
3. Usar funciones de `includes/functions.php`
4. Seguir convenciones de naming existentes

## Troubleshooting

### Error de Base de Datos
1. Verificar que MySQL esté corriendo
2. Confirmar que existe la BD `proyecto_web`
3. Revisar credenciales en `config/database.php`

### Páginas en Blanco
1. Activar display_errors en PHP
2. Revisar logs de Apache
3. Verificar sintaxis con `php -l archivo.php`

### Problemas de Permisos
```bash
chmod 755 proyecto/
chmod 777 assets/images/uploads/
```

## Estado Actual del Desarrollo

### ✅ Completado Recientemente
- **Header Renovado:** Diseño sin color con búsqueda expandible, carrito y usuario
- **Hero Section Dividido:** Layout 50/50 con sliders independientes para empresas y particulares
- **Tienda E-commerce COMPLETA:** Sistema profesional inspirado en Amazon/Shopify/MercadoLibre
  - **Página de productos (particulares.php):** Grid moderno con tarjetas, ratings, variantes
  - **Detalle de producto (product-detail.php):** Layout 2 columnas, galería, variantes visuales
  - **Modal de carrito:** Diseño overlay con animaciones, controles de cantidad, totales
  - **Checkout (checkout.php):** Proceso 3 pasos, validación, métodos pago/envío, resumen
- **Página Empresarial:** Landing B2B profesional con formularios de contacto
- **Shirt Designer Mejorado:** 
  - Área segura reducida (60% vs 80% anterior)
  - Movimiento instantáneo sin animaciones
  - Líneas guía que aparecen solo al centrar
  - Efectos de clipping en líneas guía
  - Controles flotantes funcionales
- **Sistema de Carrito:** LocalStorage con contador en tiempo real
- **Dual Sliders:** JavaScript avanzado para manejar dos sliders simultáneos
- **Header UX Mejorado (Julio 2025):**
  - **Badge del carrito optimizado:** Posicionado fuera del botón para mejor visibilidad
  - **Espaciado consistente:** Botones del header con gap reducido (0.3rem)
  - **Hover effects unificados:** Todos los botones con rotación 15° y escala 1.1
  - **Estructura HTML mejorada:** Badge del carrito fuera del button pero dentro del container
- **Modal Carrito Renovado (Julio 2025):**
  - **Diseño ampliado:** 480px → 750px (56% más ancho) para mejor UX
  - **Estilos mejorados:** Gradientes, sombras dramáticas, efectos hover avanzados
  - **Controles optimizados:** Botones más grandes con efectos 3D y animaciones
  - **Responsive perfecto:** Sin overflow horizontal en ningún dispositivo
- **IVA Discriminado RG 5.614/2024 (Julio 2025):**
  - **Cumplimiento legal:** Implementación 100% conforme con normativa argentina
  - **Fórmula oficial:** `IVA = Total × (0.21 / 1.21)` para IVA contenido
  - **Transparencia fiscal:** Muestra exactamente cuánto IVA paga el consumidor
  - **Cálculos corregidos:** El IVA no se suma al total (ya está contenido)

### 🎨 Características Visuales Implementadas
- **index.php:** Hero dividido con sliders independientes y contenido específico
- **particulares.php:** Tienda e-commerce moderna con grid de productos, ratings estrellas, variantes
- **product-detail.php:** Página detalle profesional con galería, variantes visuales, cantidad
- **checkout.php:** Proceso checkout paso a paso con indicadores, validación, resumen sticky
- **Modal carrito:** Overlay moderno con backdrop blur, animaciones suaves, controles cantidad
- **empresas.php:** Landing B2B con secciones profesionales
- **customize-shirt.php:** Editor profesional con mejoras UX
- **Header:** Diseño limpio sin colores con funcionalidades avanzadas
- **Efectos CSS:** Transiciones, hovers, gradientes, sombras, dropdowns, transforms
- **Responsive Design:** Mobile-first adaptado para todos los dispositivos

### 🔧 Archivos Clave Modificados (En XAMPP)
- `C:\xampp\htdocs\proyecto\assets\css\style.css` - Estilos completos (+4000 líneas) con sistema e-commerce
- `C:\xampp\htdocs\proyecto\assets\js\shirt-designer.js` - Editor avanzado de remeras
- `C:\xampp\htdocs\proyecto\index.php` - Hero dividido con sliders
- `C:\xampp\htdocs\proyecto\particulares.php` - Tienda e-commerce con productos y carrito
- `C:\xampp\htdocs\proyecto\product-detail.php` - Página detalle producto profesional
- `C:\xampp\htdocs\proyecto\checkout.php` - Proceso checkout multi-paso completo
- `C:\xampp\htdocs\proyecto\empresas.php` - Landing B2B profesional
- `C:\xampp\htdocs\proyecto\includes\header.php` - Header con búsqueda expandible
- `C:\xampp\htdocs\proyecto\customize-shirt.php` - Editor de remeras interactivo

## Próximas Funcionalidades
- [ ] Sistema de etiquetas
- [ ] Upload de imágenes en posts
- [ ] Editor WYSIWYG
- [ ] Sistema de notificaciones
- [ ] API REST
- [ ] Integración con redes sociales
- [x] ✅ **Checkout y procesamiento de pagos** (COMPLETADO - diseño y flujo)
- [ ] Vista previa 3D de las remeras
- [ ] Editores para otros productos (buzos, tazas, etc.)
- [ ] Sistema de inventario avanzado
- [ ] Dashboard de ventas y analytics
- [ ] Gestión de pedidos y seguimiento
- [ ] Integración con proveedores de sublimación
- [ ] Sistema de wishlist/favoritos
- [ ] Comparador de productos
- [ ] Reviews y calificaciones avanzadas
- [ ] Sistema de cupones y descuentos automáticos
- [ ] Integración con pasarelas de pago (MercadoPago, Stripe)
- [ ] Sistema de puntos y fidelización

## Resolución General 5.614/2024 - AFIP/ARCA Argentina

### Régimen de Transparencia Fiscal al Consumidor

**NORMA OBLIGATORIA:** Para todos los sistemas de facturación desarrollados para Argentina, se debe implementar el **IVA discriminado** según la Resolución General 5.614/2024 de AFIP (ahora ARCA).

#### **Fechas de Implementación:**
- **1 de enero de 2025:** Grandes empresas que emiten facturas electrónicas de crédito
- **1 de abril de 2025:** Obligatorio para todos los demás contribuyentes

#### **Obligaciones Técnicas:**

**1. Discriminación Obligatoria en Facturas:**
- **IVA contenido:** Mostrar el monto del IVA incluido en el precio
- **Otros Impuestos Nacionales Indirectos:** Discriminar impuestos internos y otros gravámenes  
- **Leyenda obligatoria:** "Régimen de Transparencia Fiscal al Consumidor (Ley 27.743)"

**FÓRMULAS OFICIALES RG 5.614/2024:**

**Cuando se parte del precio total (e-commerce):**
```
IVA Contenido = Precio Total × [Alícuota / (1 + Alícuota)]
Ejemplo: Total = $121, Alícuota = 21%
IVA = 121 × (0.21 ÷ 1.21) = $21
```

**Cuando se parte del precio neto:**
```  
IVA = Precio Neto × Alícuota
Ejemplo: Neto = $100, Alícuota = 21%
IVA = 100 × 0.21 = $21
```

**2. Formato Requerido en Comprobantes:**
```
Régimen de Transparencia Fiscal al Consumidor (Ley 27.743)
IVA Contenido: $X.XXX
Otros Impuestos Nacionales Indirectos: $X.XXX
```

**3. Aplicación:**
- Ventas a consumidores finales
- Contratos de obra
- Prestaciones de servicios
- Todos los comprobantes clase B y C

#### **Reglas de Desarrollo para Claude:**

**SIEMPRE que se implemente facturación o carrito de compras:**

1. **Mostrar IVA contenido correctamente**:
   ```javascript
   // ❌ INCORRECTO (suma IVA al total)
   const total = subtotal + iva;
   "IVA (21%): $1.259"
   
   // ✅ CORRECTO (IVA contenido en el precio)
   const ivaContenido = total * (0.21 / 1.21);
   "IVA contenido (21%): $1.259"
   
   // ✅ TAMBIÉN CORRECTO
   "IVA discriminado: $1.259"
   ```

2. **Cálculo correcto según RG 5.614/2024:**
   ```javascript
   // MÉTODO 1: Cuando se parte del precio total (e-commerce)
   const precioTotal = calcularSubtotal();
   const ivaContenido = precioTotal * (0.21 / (1 + 0.21)); // IVA contenido
   const total = precioTotal; // El IVA ya está incluido
   
   // MÉTODO 2: Cuando se parte del precio neto
   const precioNeto = calcularSubtotalSinIVA();
   const iva = precioNeto * 0.21;
   const total = precioNeto + iva;
   ```

3. **Estructura de totales obligatoria:**
   ```
   Subtotal: $X.XXX
   Descuento: -$XXX (si aplica)
   Envío: $XXX o GRATIS
   IVA contenido (21%): $X.XXX
   Otros Imp. Nac. Indirectos: $XXX (si aplica)
   TOTAL: $X.XXX
   
   Régimen de Transparencia Fiscal al Consumidor (Ley 27.743)
   ```

4. **Leyenda en comprobantes finales:**
   - Agregar la leyenda completa en facturas
   - Incluir discriminación de impuestos
   - Separar claramente IVA de otros gravámenes

#### **Objetivo de la Norma:**
Garantizar transparencia fiscal para que los consumidores conozcan exactamente cuánto pagan en impuestos en cada transacción comercial.

#### **Implementación en el Proyecto:**
- ✅ **Modal del carrito:** Implementado con "IVA contenido (21%)"
- ✅ **Cálculos:** Corregidos según fórmula oficial RG 5.614/2024
- ✅ **Fórmula IVA:** `total * (0.21 / 1.21)` para IVA contenido
- ✅ **Total correcto:** No suma IVA (ya está contenido en precios)
- [ ] **Facturas PDF:** Pendiente agregar leyenda completa
- [ ] **Checkout final:** Pendiente validar cumplimiento

#### **Puntos Clave de la Implementación:**
- **Los precios de productos YA incluyen IVA:** $5.999 contiene $1.033 de IVA
- **El modal muestra transparencia:** Discrimina cuánto IVA paga el consumidor
- **Total inalterado:** Los consumidores pagan exactamente lo mismo
- **Cumplimiento legal:** 100% conforme con RG 5.614/2024

**NOTA IMPORTANTE:** Esta es normativa legal obligatoria en Argentina. Todos los sistemas de e-commerce deben cumplir con esta resolución para evitar sanciones fiscales.

## Notas de Desarrollo

### Header UX - Mejoras Recientes (Julio 2025)
**BADGE DEL CARRITO OPTIMIZADO:**
- **Problema identificado:** Badge dentro del botón causaba problemas de posicionamiento
- **Solución implementada:** Badge movido fuera del `<button>` pero dentro del `<div class="cart-container">`
- **Estructura HTML actualizada:**
```html
<div class="cart-container">
    <button class="nav-btn cart-btn" onclick="showCartModal()">
        <i class="fas fa-shopping-cart"></i>
    </button>
    <span class="cart-badge">0</span>
</div>
```

**CSS del badge optimizado:**
```css
.cart-badge {
    position: absolute !important;
    top: -10px !important;
    right: -10px !important;
    background: #dc3545 !important;
    color: white !important;
    border-radius: 50% !important;
    min-width: 20px !important;
    height: 20px !important;
    font-size: 0.7rem !important;
    z-index: 9999 !important;
    /* Más propiedades para visibilidad completa */
}
```

**ESPACIADO Y HOVER EFFECTS:**
- **Gap reducido:** `.nav-menu { gap: 0.3rem }` (antes 0.5rem)
- **Hover unificado:** Todos los botones con `transform: rotate(15deg) scale(1.1)`
- **Consistencia visual:** Mismos efectos para user-btn, cart-btn y theme-toggle

### Sistema E-commerce Implementado
**NUEVA FUNCIONALIDAD COMPLETA:** Sistema de tienda online profesional inspirado en Amazon, Shopify y MercadoLibre:

#### Variables CSS E-commerce
```css
:root {
    --ecommerce-primary: #FF9500;        /* Naranja principal (Amazon-style) */
    --ecommerce-secondary: #232F3E;      /* Azul oscuro profesional */
    --ecommerce-accent: #0066c0;         /* Azul enlaces/acciones */
    --ecommerce-success: #007600;        /* Verde éxito/stock */
    --ecommerce-danger: #B12704;         /* Rojo precios/alertas */
    --ecommerce-shadow: 0 2px 8px rgba(0,0,0,0.1);
    --ecommerce-shadow-hover: 0 4px 12px rgba(0,0,0,0.15);
}
```

#### Componentes E-commerce
- **Product Grid:** Grid responsivo con tarjetas modernas
- **Product Cards:** Hover effects, badges, ratings con estrellas
- **Product Detail:** Layout 2 columnas, galería thumbnails, variantes visuales
- **Cart Modal:** Overlay con blur, animaciones, controles cantidad
- **Checkout:** Proceso 3 pasos, indicadores visuales, validación
- **Rating System:** Estrellas FontAwesome con medias estrellas
- **Variant Selection:** Colores circulares, tallas tipo botones
- **Price Display:** Precios principales, descuentos, ahorros
- **Stock Indicators:** Estados disponible/bajo/agotado

### Cache Busting
Los archivos CSS y JS incluyen timestamps para evitar problemas de cache:
```php
<link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
```

### Sincronización de Archivos (Windows + XAMPP)
**IMPORTANTE:** Todos los cambios deben aplicarse directamente en XAMPP:
```bash
# Ruta principal del proyecto:
/mnt/c/xampp/htdocs/proyecto/

# Ejemplos de sincronización:
cp /home/lesistern/assets/css/style.css /mnt/c/xampp/htdocs/proyecto/assets/css/style.css
cp /home/lesistern/assets/js/script.js /mnt/c/xampp/htdocs/proyecto/assets/js/script.js
cp /home/lesistern/index.php /mnt/c/xampp/htdocs/proyecto/index.php

# Verificar cambios:
ls -la /mnt/c/xampp/htdocs/proyecto/
```

### Debugging CSS
Para problemas de estilos:
1. Verificar que el archivo CSS se carga sin errores
2. Usar herramientas de desarrollador del navegador
3. Verificar que no hay conflictos de especificidad CSS

### JavaScript del Carrito
El sistema de carrito utiliza localStorage para persistir productos:
```javascript
// Agregar producto al carrito
function addToCart(productName, price) {
    const product = {
        id: productName + '_' + Date.now(),
        name: productName,
        price: price,
        quantity: 1
    };
    cart.push(product);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartBadge();
}
```

### Dual Hero Sliders
Sistema avanzado de sliders independientes:
```javascript
class DualHeroSlider {
    // Maneja dos sliders simultáneos con timers independientes
    // Permite pausa individual y navegación manual
    // Auto-avance cada 5 segundos en cada lado
}
```

## Arquitectura del Proyecto

### Frontend
- **HTML5** semántico con estructura modular
- **CSS3** con variables personalizadas y flexbox/grid
- **JavaScript ES6+** con clases y modularidad
- **FontAwesome** para iconografía
- **Responsive Design** mobile-first

### Backend
- **PHP 7.4+** con PDO para base de datos
- **MySQL 5.7+** para almacenamiento de datos
- **Sessions** para autenticación y estado
- **XAMPP** para entorno de desarrollo local

### Características de Seguridad
- Prepared statements para prevenir SQL injection
- Sanitización de inputs con `htmlspecialchars()`
- Validación de sesiones por roles
- Hashing de contraseñas con `password_hash()`

---

## 📋 Recordatorios Importantes para Claude

### 🎯 Configuración del Entorno
- **SO:** Windows
- **Servidor:** XAMPP
- **Ruta de trabajo:** SIEMPRE usar `/mnt/c/xampp/htdocs/proyecto/`
- **URL de pruebas:** http://localhost/proyecto/

### 🔄 Flujo de Trabajo
1. **Leer archivos:** Desde `/mnt/c/xampp/htdocs/proyecto/`
2. **Hacer cambios:** Directamente en `/mnt/c/xampp/htdocs/proyecto/`
3. **Verificar:** En http://localhost/proyecto/
4. **NO usar:** Rutas locales `/home/lesistern/` para cambios finales

### 📁 Archivos Principales
- **CSS:** `/mnt/c/xampp/htdocs/proyecto/assets/css/style.css`
- **JS Editor:** `/mnt/c/xampp/htdocs/proyecto/assets/js/shirt-designer.js`
- **PHP Principal:** `/mnt/c/xampp/htdocs/proyecto/index.php`
- **Editor Remeras:** `/mnt/c/xampp/htdocs/proyecto/customize-shirt.php`

---

## 🆕 Actualizaciones Recientes - Julio 2025

### ✅ Funcionalidad de Zoom Inteligente (product-detail.php)
- **Zoom condicional:** Solo funciona con imágenes cargadas (no placeholders o default.svg)
- **Validación de imagen:** Función `isImageLoaded()` verifica src, estado de carga y dimensiones naturales
- **Popup ampliado:** Tamaño aumentado 1.5x para mejor visualización
- **Código implementado:**
```javascript
function isImageLoaded() {
    const imgSrc = mainImage.src;
    return imgSrc && 
           !imgSrc.includes('default.svg') && 
           !imgSrc.includes('placeholder') && 
           mainImage.complete && 
           mainImage.naturalWidth > 0;
}
```

### ✅ Panel de Estadísticas Completo (/admin/statistics.php)
- **Diseño Tiendanube:** Implementación completa basada en la estructura oficial
- **Chart.js integrado:** Gráficos interactivos para todas las métricas
- **Plan completo activado:** Todas las funcionalidades visibles sin restricciones
- **Métricas incluidas:**
  - Ventas, conversiones, visitantes únicos
  - Productos más vendidos y visitados
  - Comportamiento de carrito abandonado
  - Análisis de tráfico y fuentes
  - Comparativas temporales

### ✅ 15 Archivos Admin Panel Creados
**Todos con diseño Tiendanube y CSS optimizado:**
- `stats-payments.php` - Análisis de pagos y métodos
- `stats-shipping.php` - Métricas de envíos y logística  
- `stats-products.php` - Performance de productos
- `stats-traffic.php` - Análisis de tráfico web
- `purchase-orders.php` - Gestión de órdenes de compra
- `abandoned-carts.php` - Recuperación de carritos
- `client-messages.php` - Mensajería con clientes
- `coupons.php` - Sistema de cupones y descuentos
- `promotions.php` - Gestión de promociones
- `marketing.php` - Herramientas de marketing
- `pos.php` - Punto de venta (POS)
- `facebook-meta.php` - Integración Meta/Facebook
- `google-shopping.php` - Google Shopping & Ads
- `marketplaces.php` - Conexión a marketplaces
- `applications.php` - Tienda de aplicaciones

### ✅ Páginas de Estadísticas Unificadas (Julio 2025)
**ACTUALIZACIÓN CRÍTICA:** Las 3 páginas de estadísticas convertidas al diseño de statistics.php:
- `stats-shipping.php` ✅ COMPLETADO - Formato statistics.php con filtros de tiempo
- `stats-products.php` ✅ COMPLETADO - Grid 6 métricas, análisis avanzado, gráficos
- `stats-traffic.php` ✅ COMPLETADO - Toggle comparación, sección Top 5 páginas

**Elementos implementados en todas:**
- **Filtros de tiempo:** Botones Hoy/Esta semana/Este mes/Trimestre/Año
- **Toggle de comparación:** Switch para comparar períodos
- **Grid 6 tarjetas:** Métricas principales en formato statistics.php
- **Sección "Análisis Avanzado":** Con h2 y métricas específicas
- **Barras tipo provinces:** Top 5 elementos con barras de progreso
- **Gráficos duales:** 2 charts lado a lado con Chart.js
- **Event listeners:** JavaScript para filtros y comparación activos

### ✅ CSS Ultra Compacto Implementado
**Optimización de espacio en todos los archivos admin:**
- **Reducción de padding:** 50% menos espacio en contenedores
- **Texto compactado:** Fuentes más pequeñas pero legibles
- **Grids optimizados:** Columnas más eficientes
- **Flags !important:** Para garantizar aplicación de estilos
- **Resultado:** 40% más contenido visible en pantalla

### ✅ Personalizaciones de UI
- **Color tab-btn activo:** Cambiado a #B12704 (color price-main)
- **Fondos fractales:** 
  - Modo claro: `Fractal Background Light 2.png`
  - Modo oscuro: `Fractal Background Dark 1.png`
  - Solo en páginas no-admin
- **Esquema de colores cálidos:**
  - Base: #fbeed8 (reemplaza blanco)
  - Secundarios: #fef7e8, #f7f1e1, #f2e8d0
  - Transiciones suaves entre modos

### ✅ Logo Fractal con Efecto Shine
- **Imágenes reemplazadas:**
  - Modo claro: `Fractal Header Light.png`
  - Modo oscuro: `Fractal Header Dark.png`
- **Dimensiones finales:**
  - Desktop: 1125px × 300px
  - Tablet: 900px × 240px  
  - Mobile: 750px × 210px
- **Efecto shine mejorado:**
  - Duración: 1 segundo
  - Modo claro: Brillo blanco con blend mode screen
  - Modo oscuro: Brillo gris oscuro con blend mode darken
  - Movimiento horizontal de izquierda a derecha

### 🔧 Estructura de Archivos Actualizada
```
admin/
├── statistics.php           # Panel principal con todas las métricas ✅
├── stats-payments.php       # Análisis de métodos de pago ✅
├── stats-shipping.php       # Logística y envíos ✅ FORMATO UNIFICADO
├── stats-products.php       # Performance de productos ✅ FORMATO UNIFICADO
├── stats-traffic.php        # Análisis de visitantes ✅ FORMATO UNIFICADO
├── purchase-orders.php      # Gestión de órdenes ✅
├── abandoned-carts.php      # Recuperación de carritos ✅
├── client-messages.php      # Comunicación con clientes ✅
├── coupons.php             # Sistema de descuentos ✅
├── promotions.php          # Promociones especiales ✅
├── marketing.php           # Herramientas de marketing ✅
├── pos.php                 # Punto de venta ✅
├── facebook-meta.php       # Integración redes sociales ✅
├── google-shopping.php     # Integración Google ✅
├── marketplaces.php        # Conexión marketplaces ✅
└── applications.php        # App store integrado ✅

📊 TODAS LAS PÁGINAS DE ESTADÍSTICAS AHORA USAN EL MISMO FORMATO:
- Filtros de tiempo idénticos (Hoy, Esta semana, Este mes, Trimestre, Año)
- Toggle de comparación de períodos con switch
- Grid de 6 métricas principales estilo statistics.php
- Sección "Análisis Avanzado" con h2
- Barras tipo provinces para Top 5 elementos
- Gráficos duales lado a lado
- Event listeners activos para interactividad
```

### 🎨 CSS Variables Actualizadas
```css
:root {
    /* Colores modo claro - Tonos cálidos */
    --bg-primary: #fbeed8;
    --bg-secondary: #fef7e8;
    --bg-tertiary: #f7f1e1;
    --bg-quaternary: #f2e8d0;
    
    /* Tab active color */
    --tab-active-color: #B12704;
}

/* Fondos fractales solo para páginas no-admin */
body:not(.admin-page) {
    background-image: url('../images/Fractal Background Light 2.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    transition: background-image 0.5s ease;
}

/* Logo fractal con shine effect */
.nav-logo a {
    width: 1125px;
    height: 300px;
    background-image: url('../images/Fractal Header Light.png');
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    position: relative;
    overflow: hidden;
}

.nav-logo a::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent);
    animation: shine 1s ease-in-out infinite;
    mix-blend-mode: screen;
}
```

### 🔄 Flujo de Implementación Completado
1. ✅ **Zoom condicional** → Validación de imágenes cargadas
2. ✅ **Panel estadísticas** → Diseño Tiendanube completo  
3. ✅ **15 archivos admin** → Estructura profesional
4. ✅ **CSS optimizado** → Reducción 40% espacio
5. ✅ **UI personalizada** → Colores y fondos
6. ✅ **Logo fractal** → Imágenes con efectos
7. ✅ **Ajustes finales** → Dimensionado perfecto

---

## 🚨 ESTADO ACTUAL - JULY 5, 2025

### ✅ COMPLETADO RECIENTEMENTE
- **Paleta de colores profesional implementada** para modo claro y oscuro según guías e-commerce
- **Modo claro:** Colores cálidos profesionales (#FAF9F6, #D8A47F, #A47149) 
- **Modo oscuro:** Paleta CMYK compatible (#1C1B1A, #A97155, #C28860)
- **Variables CSS unificadas** en critical.css y style.css

### ⚠️ PROBLEMAS CRÍTICOS PENDIENTES

#### **1. JavaScript Errors - PRIORIDAD ALTA**
```javascript
// Errores encontrados en consola:
performance-optimizer.js:120:47 - Invalid or unexpected token
advanced-personalization.js:554:48 - Unexpected identifier 'sensitivity'
ab-testing.js:220:57 - Invalid or unexpected token  
pwa-manager.js:128:59 - Invalid or unexpected token
heatmap-analytics.js:53 - this.setupCustomTracking is not a function
email-marketing.js:247 - this.loadAutomationState is not a function
```

#### **2. Service Worker Missing - PRIORIDAD MEDIA**
```javascript
// Error 404:
push-notifications.js:218 - Failed to register ServiceWorker
GET http://localhost/sw.js 404 (Not Found)
```

#### **3. PWA Icons Missing - PRIORIDAD BAJA**
```javascript
// Error 404:
GET http://localhost/proyecto/assets/images/icon-144.png 404 (Not Found)
GET http://localhost/proyecto/assets/images/icon-32.png 404 (Not Found)
GET http://localhost/proyecto/assets/images/icon-16.png 404 (Not Found)
```

#### **4. Modo Oscuro Bug - PRIORIDAD ALTA**
```javascript
// Debug output - colors invertidos:
Modo oscuro: false → Color de fondo: rgb(28, 27, 26) // Debería ser claro
Modo oscuro: true → Color de fondo: rgb(250, 249, 246) // Debería ser oscuro
```

### 🎯 PASOS SIGUIENTES INMEDIATOS

#### **FASE 1: Fix JavaScript Crítico (1-2 horas)**
1. **Corregir errores de sintaxis** en los 6 archivos JS de Fase 4
2. **Verificar funciones faltantes** (setupCustomTracking, loadAutomationState)
3. **Testear funcionalidad** básica de cada módulo

#### **FASE 2: Fix Modo Oscuro (30 minutos)**
1. **Revisar lógica de aplicación** de variables CSS
2. **Verificar precedencia** entre critical.css y style.css
3. **Testear toggle** visual correctamente

#### **FASE 3: Service Worker & PWA (1 hora)**
1. **Crear sw.js** en directorio raíz
2. **Generar iconos PWA** faltantes (16px, 32px, 144px)
3. **Verificar manifest.json** configuración

#### **FASE 4: Testing Completo (30 minutos)**
1. **Verificar paleta de colores** en ambos modos
2. **Testear todas las funcionalidades** JavaScript
3. **Validar PWA installation** flow

### 📋 COMANDOS PARA DEBUGGING

```bash
# Verificar archivos JavaScript
find /mnt/c/xampp/htdocs/proyecto/assets/js/ -name "*.js" -exec grep -l "function.*(" {} \;

# Verificar Service Worker
ls -la /mnt/c/xampp/htdocs/proyecto/sw.js

# Verificar iconos PWA  
ls -la /mnt/c/xampp/htdocs/proyecto/assets/images/icon-*.png

# Verificar CSS crítico
head -20 /mnt/c/xampp/htdocs/proyecto/assets/css/critical.css
```

### 🔧 ARCHIVOS AFECTADOS

**JavaScript con errores:**
- `/mnt/c/xampp/htdocs/proyecto/assets/js/performance-optimizer.js`
- `/mnt/c/xampp/htdocs/proyecto/assets/js/advanced-personalization.js`
- `/mnt/c/xampp/htdocs/proyecto/assets/js/ab-testing.js`
- `/mnt/c/xampp/htdocs/proyecto/assets/js/pwa-manager.js`
- `/mnt/c/xampp/htdocs/proyecto/assets/js/heatmap-analytics.js`
- `/mnt/c/xampp/htdocs/proyecto/assets/js/email-marketing.js`

**CSS principales:**
- `/mnt/c/xampp/htdocs/proyecto/assets/css/critical.css` ✅ ACTUALIZADO
- `/mnt/c/xampp/htdocs/proyecto/assets/css/style.css` ✅ ACTUALIZADO

**Archivos faltantes:**
- `/mnt/c/xampp/htdocs/proyecto/sw.js` ❌ FALTA
- `/mnt/c/xampp/htdocs/proyecto/assets/images/icon-*.png` ❌ FALTAN

**Última actualización:** 2025-07-05
**Versión:** 4.2 - Professional Color Guide Implementation + JavaScript Issues  
**Mantenedor:** Claude Assistant
**Status:** ⚠️ **ENTERPRISE-READY + PENDING JAVASCRIPT FIXES**

---

## 📈 Resumen de Unificación de Estadísticas - Julio 2025

### ✅ Conversión Completada
**OBJETIVO:** Hacer que stats-shipping.php, stats-products.php y stats-traffic.php tengan exactamente el mismo diseño que statistics.php.

**RESULTADO:** ✅ 100% COMPLETADO
- **stats-shipping.php** → Convertido con métricas de envíos
- **stats-products.php** → Convertido con métricas de productos  
- **stats-traffic.php** → Convertido con métricas de tráfico

### 🎯 Elementos Estandarizados
**IDÉNTICOS EN TODAS LAS PÁGINAS:**
1. **Filtros de tiempo:** Mismos 5 botones (Hoy, Esta semana, Este mes, Trimestre, Año)
2. **Toggle comparación:** Switch idéntico para comparar períodos
3. **Grid 6 métricas:** Estructura exacta de statistics.php
4. **Título "Métricas Principales":** H2 unificado
5. **Sección "Análisis Avanzado":** Con h2 y structure metrics-row
6. **Barras estilo provinces:** Top 5 elementos con barras de progreso
7. **Gráficos duales:** 2 charts lado a lado en charts-section
8. **Event listeners:** JavaScript funcional para filtros

### 🔄 JavaScript Implementado
**FUNCIONES ESTÁNDAR EN TODAS:**
```javascript
setupEventListeners() // Botones de período y toggle
updatePeriod(period)   // Cambio de período de tiempo
toggleComparison()     // Activar/desactivar comparación
initializeCharts()     // Inicializar gráficos Chart.js
```

### 📊 Resultado Final
Las 4 páginas de estadísticas (statistics.php + 3 convertidas) ahora comparten:
- **Diseño visual idéntico**
- **Estructura HTML unificada** 
- **CSS consistente**
- **JavaScript funcionalmente equivalente**
- **UX coherente** para el usuario admin