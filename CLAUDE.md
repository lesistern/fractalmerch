# CLAUDE.md - Referencia del Proyecto

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
- **Personalizador de remeras:** Editor interactivo con:
  - Vista frente/espalda
  - Carga de hasta 5 imágenes (drag & drop)
  - Rotación y escalado
  - Guías de centrado automáticas
  - Límites de sublimación visibles
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
- **Principal:** http://localhost/proyecto/
- **Admin:** http://localhost/proyecto/admin/
- **phpMyAdmin:** http://localhost/phpmyadmin

## Comandos Útiles

### Instalación
```bash
# Copiar proyecto a XAMPP
cp -r proyecto/ /opt/lampp/htdocs/  # Linux
# o
# Copiar a C:/xampp/htdocs/ en Windows

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

## Próximas Funcionalidades
- [ ] Sistema de etiquetas
- [ ] Upload de imágenes en posts
- [ ] Editor WYSIWYG
- [ ] Sistema de notificaciones
- [ ] API REST
- [ ] Integración con redes sociales

---

**Última actualización:** $(date)
**Versión:** 1.0
**Mantenedor:** Claude Assistant