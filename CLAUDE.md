# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Información General del Proyecto
- **Nombre:** Sistema de Gestión de Contenido PHP
- **Tipo:** Aplicación web PHP con MySQL
- **Entorno:** XAMPP (Apache + MySQL + PHP)
- **Base de datos:** proyecto_web
- **Versión PHP:** 7.4+
- **Versión MySQL:** 5.7+

## Estructura del Proyecto
```
proyecto/
├── .github/workflows/        # GitHub Actions workflows
│   ├── ci.yml               # Pipeline CI/CD principal
│   └── deploy.yml           # Workflow de despliegue
├── admin/                   # Panel de administración
│   ├── dashboard.php        # Dashboard principal
│   ├── manage-users.php     # Gestión de usuarios
│   ├── manage-posts.php     # Gestión de posts
│   ├── manage-comments.php  # Gestión de comentarios
│   └── manage-categories.php # Gestión de categorías
├── assets/                  # Recursos estáticos
│   ├── css/style.css        # Estilos principales
│   ├── js/main.js          # JavaScript principal
│   ├── js/shirt-designer.js # JavaScript del personalizador
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

### Instalación
```bash
# Copiar proyecto a XAMPP
cp -r proyecto/ /opt/lampp/htdocs/  # Linux
# o
# Copiar a C:/xampp/htdocs/ en Windows

# En WSL (para desarrollo):
cp -r /home/lesistern/* /mnt/c/xampp/htdocs/proyecto/

# Importar base de datos
mysql -u root proyecto_web < database.sql
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
- **Tienda E-commerce:** Página completa de productos con carrito funcional
- **Página Empresarial:** Landing B2B profesional con formularios de contacto
- **Shirt Designer Mejorado:** 
  - Área segura reducida (60% vs 80% anterior)
  - Movimiento instantáneo sin animaciones
  - Líneas guía que aparecen solo al centrar
  - Efectos de clipping en líneas guía
  - Controles flotantes funcionales
- **Sistema de Carrito:** LocalStorage con contador en tiempo real
- **Dual Sliders:** JavaScript avanzado para manejar dos sliders simultáneos

### 🎨 Características Visuales Implementadas
- **index.php:** Hero dividido con sliders independientes y contenido específico
- **particulares.php:** Tienda completa con 6 productos y carrito funcional
- **empresas.php:** Landing B2B con secciones profesionales
- **customize-shirt.php:** Editor profesional con mejoras UX
- **Header:** Diseño limpio sin colores con funcionalidades avanzadas
- **Efectos CSS:** Transiciones, hovers, gradientes, sombras, dropdowns
- **Responsive Design:** Adaptado para móviles y tablets

### 🔧 Archivos Clave Modificados
- `/assets/css/style.css` - Estilos completos (+2400 líneas con hero dividido, tienda, empresas)
- `/assets/js/shirt-designer.js` - Lógica mejorada del editor
- `/index.php` - Hero dividido con clase DualHeroSlider
- `/particulares.php` - Tienda completa con carrito JavaScript
- `/empresas.php` - Landing B2B profesional
- `/includes/header.php` - Header renovado con búsqueda, usuario y carrito

## Próximas Funcionalidades
- [ ] Sistema de etiquetas
- [ ] Upload de imágenes en posts
- [ ] Editor WYSIWYG
- [ ] Sistema de notificaciones
- [ ] API REST
- [ ] Integración con redes sociales
- [ ] Checkout y procesamiento de pagos
- [ ] Vista previa 3D de las remeras
- [ ] Editores para otros productos (buzos, tazas, etc.)
- [ ] Sistema de inventario
- [ ] Dashboard de ventas
- [ ] Gestión de pedidos
- [ ] Integración con proveedores de sublimación

## Notas de Desarrollo

### Cache Busting
Los archivos CSS y JS incluyen timestamps para evitar problemas de cache:
```php
<link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
```

### Sincronización de Archivos
Desarrollo en WSL con sincronización a XAMPP:
```bash
# Copiar cambios a XAMPP
cp /home/lesistern/assets/css/style.css /mnt/c/xampp/htdocs/proyecto/assets/css/style.css
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

**Última actualización:** 2025-06-27
**Versión:** 2.0
**Mantenedor:** Claude Assistant