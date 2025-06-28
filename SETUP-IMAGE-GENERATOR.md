# Setup del Generador de Imágenes Administrativo

## 🎯 **Sistema Completado**

Se ha creado un sistema completo de generación de imágenes placeholder para uso administrativo.

## 📁 **Archivos Creados/Modificados**

### Nuevos Archivos:
- `/admin/generate-images.php` - Interfaz de generación
- `/admin/placeholder-gallery.php` - Galería de imágenes generadas  
- `/api/generate-image.php` - API endpoint para generación
- `/mcp-integration.php` - Clase de integración con MCP
- `/database/add_generated_images_table.sql` - Script de base de datos

### Archivos Modificados:
- `/admin/dashboard.php` - Añadido enlace al generador

## 🗄️ **Configuración de Base de Datos**

**IMPORTANTE:** Ejecutar este SQL en phpMyAdmin:

```sql
-- Crear tabla para imágenes generadas
CREATE TABLE IF NOT EXISTS generated_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    prompt TEXT NOT NULL,
    style VARCHAR(50) DEFAULT 'realistic',
    size VARCHAR(20) DEFAULT '1024x1024',
    category VARCHAR(50) DEFAULT 'otros',
    generated_by INT,
    is_real_image BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Índices para mejorar performance
CREATE INDEX idx_generated_images_category ON generated_images(category);
CREATE INDEX idx_generated_images_created_at ON generated_images(created_at);
CREATE INDEX idx_generated_images_generated_by ON generated_images(generated_by);
```

## 🚀 **Cómo Usar**

### 1. Acceso Administrativo
- URL: `http://localhost/proyecto/admin/generate-images.php`
- **Requiere:** Login como Admin

### 2. Generar Imágenes
- Ingresar descripción (prompt)
- Seleccionar estilo, tamaño y categoría
- Click en "Generar Imagen Placeholder"

### 3. Ver Galería
- URL: `http://localhost/proyecto/admin/placeholder-gallery.php`
- Filtrar por categoría
- Eliminar imágenes no deseadas

## 🔧 **Modos de Funcionamiento**

### Modo Mock (Por Defecto)
- **Sin APIs configuradas**
- Genera archivos `.txt` placeholder
- Perfecto para testing y desarrollo
- No requiere claves de API

### Modo Real (Con APIs)
- **Con APIs configuradas**
- Genera imágenes PNG/JPG reales
- Requiere Node.js en el servidor
- Variables de entorno:
  ```bash
  OPENAI_API_KEY=sk-tu-clave-aqui
  STABILITY_API_KEY=sk-tu-clave-aqui
  ```

## 📂 **Estructura de Archivos**

```
proyecto/
├── admin/
│   ├── generate-images.php      # Generador principal
│   ├── placeholder-gallery.php  # Galería de placeholders
│   └── dashboard.php            # Dashboard modificado
├── api/
│   └── generate-image.php       # API endpoint
├── assets/images/generated/     # Imágenes web (se crea automáticamente)
├── mcp-media-generator/         # Proyecto MCP
│   └── generated-media/         # Archivos generados (se crea automáticamente)
├── mcp-integration.php          # Clase de integración
└── database/
    └── add_generated_images_table.sql
```

## 🎨 **Características del Sistema**

### Categorías Disponibles:
- **Productos** - Imágenes de productos
- **Logos** - Logotipos empresariales  
- **Banners** - Banners promocionales
- **Fondos** - Backgrounds y texturas
- **Marketing** - Material publicitario
- **Redes Sociales** - Contenido para RRSS
- **Web Design** - Elementos web
- **Otros** - Categoría general

### Estilos Disponibles:
- Realista, Arte Digital, Fotográfico
- Artístico, Cinematográfico, Cartoon
- Anime, Fantasía

### Tamaños Soportados:
- Cuadrado: 1024x1024, 512x512
- Horizontal: 1792x1024
- Vertical: 1024x1792

## 📊 **Panel de Estadísticas**

- Total de imágenes generadas
- Imágenes reales vs placeholders
- Distribución por categorías
- Historial mensual

## 🔒 **Seguridad**

- ✅ Solo accesible para administradores
- ✅ Validación de entrada con filtros
- ✅ Protección contra inyecciones
- ✅ Gestión segura de archivos
- ✅ Logs de actividad por usuario

## 🎯 **Casos de Uso**

1. **Desarrollo Web** - Placeholders para diseño
2. **Prototipos** - Imágenes temporales para mockups
3. **Testing** - Contenido de prueba
4. **Marketing** - Borradores visuales rápidos
5. **E-commerce** - Imágenes producto temporal

## 📝 **Próximas Mejoras**

- [ ] Bulk generation (múltiples imágenes)
- [ ] Templates predefinidos por industria
- [ ] Integración con editor de remeras
- [ ] Export masivo de placeholders
- [ ] Watermarks automáticos
- [ ] Resize y optimización automática

---

**Sistema listo para usar en:** `http://localhost/proyecto/admin/`

**Estado:** ✅ Completamente funcional en modo mock
**Upgrade:** Configurar APIs para imágenes reales