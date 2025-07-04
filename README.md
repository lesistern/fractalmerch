# Proyecto Web PHP - Sistema de Gestión de Contenido

Un sistema completo de gestión de contenido desarrollado en PHP con MySQL, diseñado para funcionar con XAMPP.

## 🚀 Características Principales

### Autenticación y Usuarios
- ✅ Sistema de registro e inicio de sesión
- ✅ Gestión de roles (Admin, Moderador, Usuario)
- ✅ Perfiles de usuario editables
- ✅ Sesiones seguras

### Gestión de Contenido
- ✅ CRUD completo para posts/artículos
- ✅ Sistema de categorías
- ✅ Editor de contenido
- ✅ Estados de publicación (borrador, publicado, archivado)

### Sistema de Comentarios
- ✅ Comentarios en posts
- ✅ Sistema de moderación
- ✅ Respuestas anidadas (preparado)
- ✅ Aprobación/rechazo de comentarios

### Panel de Administración
- ✅ Dashboard con estadísticas
- ✅ Gestión de usuarios y roles
- ✅ Gestión de posts y comentarios
- ✅ Gestión de categorías

### Características Adicionales
- ✅ Sistema de búsqueda
- ✅ Paginación
- ✅ Contador de vistas
- ✅ Diseño responsive
- ✅ Validación de formularios
- ✅ Medidas de seguridad básicas
- ✅ **Modo oscuro/claro** con toggle persistente
- ✅ **Personalizador de remeras** avanzado

### 🎨 Personalizador de Remeras
- ✅ Editor interactivo frente/espalda
- ✅ Carga de hasta 5 imágenes (drag & drop)
- ✅ Rotación y escalado de imágenes
- ✅ Guías de centrado automáticas
- ✅ Límites de sublimación visibles
- ✅ Controles táctiles para móvil
- ✅ Guardado/carga de diseños

## 📋 Requisitos del Sistema

- **XAMPP** (Apache + MySQL + PHP)
- **PHP 7.4+**
- **MySQL 5.7+**
- **Navegador web moderno**

## ⚙️ Instalación

### 1. Preparar el Entorno
1. Instala XAMPP desde [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Inicia Apache y MySQL desde el panel de control de XAMPP

### 2. Configurar la Base de Datos
1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
2. Crea una nueva base de datos llamada `proyecto_web`
3. Importa el archivo `database.sql` incluido en el proyecto

### 3. Instalar el Proyecto
1. Copia la carpeta `proyecto` a `C:/xampp/htdocs/` (Windows) o `/opt/lampp/htdocs/` (Linux)
2. Asegúrate de que la estructura sea: `htdocs/proyecto/`

### 4. Configurar la Aplicación
1. Verifica la configuración en `config/database.php`
2. Ajusta los parámetros si es necesario:
   ```php
   $host = 'localhost';
   $dbname = 'proyecto_web';
   $username = 'root';
   $password = '';
   ```

### 5. Acceder al Sistema
1. Abre tu navegador
2. Ve a: `http://localhost/proyecto/`
3. ¡Listo para usar!

## 👤 Credenciales por Defecto

**Administrador:**
- Email: `admin@proyecto.com`
- Contraseña: `password` (cambiar después del primer login)

## 📁 Estructura del Proyecto

```
proyecto/
├── config/
│   ├── database.php          # Configuración de base de datos
│   └── config.php            # Configuraciones generales
├── includes/
│   ├── header.php            # Cabecera común
│   ├── footer.php            # Pie de página común
│   └── functions.php         # Funciones principales
├── admin/
│   ├── dashboard.php         # Panel principal de admin
│   ├── manage-users.php      # Gestión de usuarios
│   ├── manage-posts.php      # Gestión de posts
│   ├── manage-comments.php   # Gestión de comentarios
│   └── manage-categories.php # Gestión de categorías
├── assets/
│   ├── css/
│   │   └── style.css         # Estilos principales
│   ├── js/
│   │   └── main.js          # JavaScript principal
│   └── images/              # Imágenes del sitio
├── index.php                # Página principal
├── login.php                # Inicio de sesión
├── register.php             # Registro de usuarios
├── profile.php              # Perfil de usuario
├── post.php                 # Visualización de posts
├── create-post.php          # Crear nuevo post
├── edit-post.php            # Editar post
├── delete-post.php          # Eliminar post
├── customize-shirt.php      # Personalizador de remeras
├── logout.php               # Cerrar sesión
├── database.sql             # Script de base de datos
└── README.md               # Este archivo
```

## 🔒 Medidas de Seguridad

- **Sanitización de entrada:** Todos los datos de usuario son limpiados
- **Prepared Statements:** Prevención de inyección SQL
- **Validación de sesiones:** Control de acceso por roles
- **Hashing de contraseñas:** Usando `password_hash()` de PHP
- **Validación de formularios:** Cliente y servidor

## 🎨 Funcionalidades de Usuario

### Para Usuarios Regulares:
- Crear y gestionar posts
- Comentar en posts
- Editar perfil personal
- Ver estadísticas propias

### Para Moderadores:
- Todo lo anterior, más:
- Moderar comentarios
- Gestionar posts de otros usuarios

### Para Administradores:
- Control total del sistema
- Gestión de usuarios y roles
- Acceso al panel de administración
- Gestión de categorías

## 🔧 Personalización

### Cambiar Configuraciones
Edita `config/config.php` para modificar:
- Nombre del sitio
- URL base
- Límites de paginación
- Configuraciones de upload

### Personalizar Estilos
Modifica `assets/css/style.css` para cambiar:
- Colores del tema
- Tipografías
- Diseño responsive
- Animaciones

### Agregar Funcionalidades
- Crea nuevos archivos PHP siguiendo la estructura existente
- Usa las funciones en `includes/functions.php`
- Sigue las convenciones de naming

### Modo Oscuro
- El toggle se encuentra en la barra de navegación
- La preferencia se guarda en localStorage
- Todos los elementos soportan ambos modos

### Personalizador de Remeras
- Accede desde el menú "Personalizar Remera"
- Sube imágenes por drag & drop o click
- Usa los controles para ajustar tamaño y rotación
- Las guías aparecen automáticamente al centrar

## 🐛 Solución de Problemas

### Error de Conexión a la Base de Datos
1. Verifica que MySQL esté corriendo en XAMPP
2. Confirma que la base de datos `proyecto_web` existe
3. Revisa las credenciales en `config/database.php`

### Problemas de Permisos
1. Asegúrate de que la carpeta tenga permisos de escritura
2. En Linux: `chmod 755 proyecto/`

### Páginas en Blanco
1. Activa la visualización de errores en PHP
2. Revisa los logs de Apache
3. Verifica la sintaxis PHP

## 📱 Responsive Design

El sitio está optimizado para:
- **Desktop:** > 768px
- **Tablet:** 768px - 1024px  
- **Mobile:** < 768px

## 🔄 Actualizaciones Futuras

Funcionalidades planeadas:
- [ ] Sistema de etiquetas
- [ ] Upload de imágenes
- [ ] Editor WYSIWYG
- [ ] Sistema de notificaciones
- [ ] API REST
- [ ] Integración con redes sociales

## 📞 Soporte

Para reportar bugs o solicitar características:
1. Crea un issue detallando el problema
2. Incluye información del entorno
3. Proporciona pasos para reproducir

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Puedes usarlo libremente para proyectos personales o comerciales.

---

**¡Gracias por usar nuestro sistema de gestión de contenido!** 🎉