# Quick Commands Guide - Claude CLI

## 🚀 Comandos Rápidos por Rol

### 👔 CEO Commands

```bash
# Status del proyecto
claude "status del proyecto y métricas principales"

# Análisis de competencia
claude "analiza features que tienen Tiendanube/Shopify que podríamos implementar"

# ROI de features
claude "calcula el ROI estimado de implementar sistema de reviews vs cupones"

# Reporte semanal
claude "genera reporte ejecutivo semanal con KPIs del e-commerce"
```

### 👨‍💻 Senior Developer Commands

```bash
# Code review rápido
claude "revisa los últimos 5 commits y dame feedback"

# Análisis de deuda técnica
claude "identifica los 5 archivos con mayor deuda técnica"

# Optimización
claude "encuentra los queries N+1 en el proyecto"

# Seguridad
claude "busca vulnerabilidades XSS y SQL injection"

# Arquitectura
claude "sugiere cómo implementar patrón Repository en este proyecto"
```

### 👶 Intern Developer Commands

```bash
# Ayuda con error
claude "error: Uncaught TypeError: Cannot read property 'length' of undefined"

# Implementar feature simple
claude "cómo agrego un contador de visitas a product-detail.php"

# Entender código
claude "explícame qué hace la función processCheckout() paso a paso"

# Crear función
claude "ayúdame a crear función para validar email en PHP"

# Debug
claude "mi carrito no actualiza el badge, ayúdame a debuggear"
```

## 🎯 Comandos por Tarea Común

### 🛒 E-commerce
```bash
# Agregar producto
claude "cómo agrego un nuevo producto a la tienda"

# Sistema de descuentos
claude "implementa sistema de cupones de descuento"

# Inventario
claude "agrega control de stock a productos"

# Métodos de pago
claude "integra MercadoPago al checkout"
```

### 🎨 Frontend
```bash
# Responsive
claude "haz responsive el modal del carrito para móviles"

# Animaciones
claude "agrega animación de loading al checkout"

# Optimizar CSS
claude "encuentra CSS no utilizado y elimínalo"

# Dark mode
claude "arregla el bug del modo oscuro que está invertido"
```

### 🔧 Backend
```bash
# API endpoint
claude "crea endpoint GET /api/products con paginación"

# Validación
claude "agrega validación server-side al formulario de contacto"

# Caché
claude "implementa caché para las queries de productos"

# Logs
claude "agrega sistema de logs para errores críticos"
```

### 📊 Base de Datos
```bash
# Migración
claude "crea migración para agregar tabla 'orders'"

# Índices
claude "sugiere índices para optimizar queries lentos"

# Backup
claude "script para backup automático de BD"

# Normalización
claude "analiza si la BD está correctamente normalizada"
```

## 💻 Aliases Útiles (agregar a .bashrc o .zshrc)

```bash
# Alias para CEO
alias claude-status="claude 'dame status general del proyecto con métricas'"
alias claude-roadmap="claude 'sugiere roadmap para próximo mes'"

# Alias para Senior Dev  
alias claude-review="claude 'revisa código en el archivo actual'"
alias claude-security="claude 'busca vulnerabilidades de seguridad'"
alias claude-refactor="claude 'sugiere cómo refactorizar este código'"

# Alias para Interns
alias claude-help="claude 'explícame este código'"
alias claude-fix="claude 'ayúdame con este error'"
alias claude-implement="claude 'ayúdame a implementar'"

# Alias generales
alias claude-test="claude 'genera tests para esta función'"
alias claude-docs="claude 'documenta este código'"
alias claude-clean="claude 'limpia y optimiza este código'"
```

## 🔥 One-Liners Más Usados

```bash
# Encontrar todos los TODOs
claude "lista todos los TODOs y FIXMEs del proyecto con prioridad"

# Generar README
claude "actualiza el README.md con las últimas features"

# Verificar estándares
claude "verifica que este archivo siga PSR-12"

# Crear componente
claude "crea componente PHP reutilizable para [feature]"

# Optimizar imágenes
claude "genera script para optimizar todas las imágenes del proyecto"

# Crear seeders
claude "crea seeder con datos de prueba para productos"

# Documentar API
claude "genera documentación OpenAPI para los endpoints"

# Análisis de performance
claude "analiza qué está haciendo lento el sitio"

# Generar sitemap
claude "genera sitemap.xml dinámico"

# Validar SEO
claude "revisa SEO de product-detail.php y sugiere mejoras"
```

## 📝 Templates de Prompts

### Para Debugging
```
claude "Tengo este error:
[PEGAR ERROR]
En el archivo: [ARCHIVO]
Línea: [LÍNEA]
Contexto: [QUÉ ESTABA HACIENDO]
¿Cómo lo soluciono?"
```

### Para Nueva Feature
```
claude "Necesito implementar [FEATURE]
Requisitos:
- [REQ 1]
- [REQ 2]
Restricciones:
- [RESTRICCIÓN]
¿Cuál es la mejor manera de hacerlo?"
```

### Para Optimización
```
claude "Este código funciona pero es lento:
[CÓDIGO]
¿Cómo lo optimizo manteniendo la misma funcionalidad?"
```

### Para Testing
```
claude "Genera tests completos para:
Archivo: [ARCHIVO]
Función: [FUNCIÓN]
Casos edge que debe cubrir:
- [CASO 1]
- [CASO 2]"
```

## ⚡ Shortcuts de Cursor + Claude

| Shortcut | Acción | Útil para |
|----------|---------|-----------|
| Ctrl+K | Abrir Claude inline | Preguntas rápidas |
| Ctrl+L | Abrir Claude chat | Conversaciones largas |
| Ctrl+Shift+K | Claude con contexto | Análisis profundo |
| Alt+Enter | Aplicar sugerencia | Implementación rápida |

## 🎮 Comandos Gamificados para Interns

```bash
# Nivel 1: Beginner
claude "explica qué es MVC en el contexto de este proyecto"

# Nivel 2: Junior
claude "implementa validación de formulario con PHP y JS"

# Nivel 3: Mid-level
claude "refactoriza el carrito para usar patrón Observer"

# Nivel 4: Advanced
claude "optimiza las queries usando eager loading"

# Nivel 5: Senior
claude "diseña arquitectura para microservicios de este proyecto"
```

---

**Pro tip:** Guarda tus comandos más usados en un archivo `.claude-history` para reference rápida