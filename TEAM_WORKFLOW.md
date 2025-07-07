# Team Workflow Guide - Claude CLI + Cursor

## 🏢 Estructura Organizacional

### CEO (Chief Executive Officer)
**Responsabilidades:**
- Definir visión y estrategia del producto
- Aprobar decisiones arquitectónicas mayores
- Gestionar recursos y prioridades
- Revisar métricas y KPIs
- Comunicación con stakeholders

**Uso de Claude CLI:**
```bash
# Revisar estado general del proyecto
claude "Dame un resumen ejecutivo del estado actual del proyecto, problemas críticos y próximos hitos"

# Analizar métricas de negocio
claude "Analiza las métricas de conversión y engagement del e-commerce"

# Planificación estratégica
claude "Basándote en el estado actual, sugiere las próximas 3 features prioritarias para el Q1"
```

### Senior Developer (Tech Lead)
**Responsabilidades:**
- Arquitectura y decisiones técnicas
- Code reviews y mentoring
- Gestión de deuda técnica
- Definir estándares de código
- Resolver problemas complejos

**Uso de Claude CLI:**
```bash
# Análisis de arquitectura
claude "Analiza la arquitectura actual del proyecto y sugiere mejoras de performance"

# Code review automático
claude "Revisa los últimos commits y dame feedback sobre calidad del código"

# Documentación técnica
claude "Genera documentación técnica para el sistema de carrito y checkout"

# Refactoring
claude "Identifica código duplicado en el proyecto y sugiere cómo refactorizarlo"
```

### Intern Developers (2-3 personas)
**Responsabilidades:**
- Implementar features asignadas
- Corregir bugs
- Escribir tests unitarios
- Documentar código
- Aprender mejores prácticas

**Uso de Claude CLI:**
```bash
# Implementar features
claude "Ayúdame a implementar la funcionalidad de wishlist en particulares.php"

# Debugging
claude "Tengo este error: [error]. ¿Cómo lo soluciono?"

# Aprendizaje
claude "Explícame cómo funciona el sistema de autenticación en este proyecto"

# Tests
claude "Genera tests unitarios para la función addToCart()"
```

## 📋 Flujos de Trabajo Optimizados

### 1. Daily Standup Virtual (9:00 AM)
```bash
# Comando para todos los miembros del equipo
claude "Resume mis tareas pendientes y lo que completé ayer basándote en los commits"
```

### 2. Sprint Planning (Lunes)
```bash
# CEO + Senior Dev
claude "Basándote en el backlog actual, sugiere las tareas prioritarias para este sprint"

# Asignación de tareas
claude "Divide estas features en tareas específicas para 3 intern developers según complejidad"
```

### 3. Feature Development Flow

#### Paso 1: Análisis (Intern)
```bash
claude "Analiza los requerimientos para [FEATURE] y genera una lista de subtareas"
```

#### Paso 2: Implementación (Intern)
```bash
claude "Implementa [FEATURE] siguiendo los estándares del proyecto"
```

#### Paso 3: Testing (Intern)
```bash
claude "Genera tests para la feature [FEATURE] que acabo de implementar"
```

#### Paso 4: Code Review (Senior Dev)
```bash
claude "Revisa el código de [FEATURE] en manage-products.php y dame feedback"
```

#### Paso 5: Deployment Check (Senior Dev)
```bash
claude "Verifica que [FEATURE] esté lista para producción y genera checklist de deployment"
```

## 🔧 Configuración de Cursor + Claude CLI

### Workspace Settings (.vscode/settings.json)
```json
{
  "claude-cli": {
    "defaultContext": "CLAUDE.md",
    "autoLoadContext": true,
    "teamMode": true
  },
  "editor.formatOnSave": true,
  "php.validate.executablePath": "C:\\xampp\\php\\php.exe"
}
```

### Snippets por Rol (.vscode/claude-snippets.json)

#### CEO Snippets
```json
{
  "status": {
    "prefix": "!status",
    "body": "claude \"Dame un resumen ejecutivo del proyecto con métricas clave\""
  },
  "roadmap": {
    "prefix": "!roadmap",
    "body": "claude \"Genera un roadmap para los próximos 3 meses basado en prioridades\""
  }
}
```

#### Senior Dev Snippets
```json
{
  "review": {
    "prefix": "!review",
    "body": "claude \"Revisa el código en ${TM_FILEPATH} y sugiere mejoras\""
  },
  "security": {
    "prefix": "!security",
    "body": "claude \"Analiza vulnerabilidades de seguridad en ${TM_FILEPATH}\""
  },
  "performance": {
    "prefix": "!perf",
    "body": "claude \"Analiza y optimiza el performance de ${TM_FILEPATH}\""
  }
}
```

#### Intern Snippets
```json
{
  "help": {
    "prefix": "!help",
    "body": "claude \"Explícame cómo funciona ${TM_SELECTED_TEXT}\""
  },
  "implement": {
    "prefix": "!impl",
    "body": "claude \"Ayúdame a implementar ${1:feature} en ${TM_FILEPATH}\""
  },
  "fix": {
    "prefix": "!fix",
    "body": "claude \"Tengo este error: ${TM_SELECTED_TEXT}. ¿Cómo lo soluciono?\""
  },
  "test": {
    "prefix": "!test",
    "body": "claude \"Genera tests para la función ${TM_SELECTED_TEXT}\""
  }
}
```

## 🚀 Mejores Prácticas

### 1. Convenciones de Commits
```bash
# Formato: [TIPO][TICKET] Descripción
# Tipos: feat, fix, docs, style, refactor, test, chore

# Ejemplos:
git commit -m "feat[SHOP-123] Agregar wishlist a productos"
git commit -m "fix[BUG-456] Corregir cálculo de IVA en checkout"
git commit -m "docs[DOC-789] Actualizar README con nuevas features"
```

### 2. Branch Strategy
```
main
  └── develop
       ├── feature/SHOP-123-wishlist (Intern 1)
       ├── feature/SHOP-124-reviews (Intern 2)
       └── fix/BUG-456-iva-calc (Intern 3)
```

### 3. Code Review Checklist
```bash
# Comando para Senior Dev antes de aprobar PR
claude "Revisa este PR según el checklist:
- [ ] Sigue estándares de código PHP PSR-12
- [ ] Tiene tests unitarios
- [ ] No introduce vulnerabilidades de seguridad
- [ ] Performance es óptimo
- [ ] Documentación actualizada
- [ ] No hay código duplicado"
```

### 4. Definition of Done
- Código implementado y funcionando
- Tests escritos y pasando
- Code review aprobado
- Documentación actualizada
- Sin errores en consola
- Responsive en todos los dispositivos
- Performance optimizado

## 📊 Métricas y Seguimiento

### Para CEO
```bash
# Dashboard de métricas semanales
claude "Genera reporte semanal con:
- Features completadas vs planeadas
- Bugs encontrados vs resueltos
- Velocidad del equipo
- Cobertura de tests
- Deuda técnica"
```

### Para Senior Dev
```bash
# Análisis de calidad de código
claude "Analiza la calidad del código con métricas:
- Complejidad ciclomática
- Duplicación de código
- Cobertura de tests
- Vulnerabilidades de seguridad"
```

## 🔄 Automatizaciones Recomendadas

### GitHub Actions Workflows
```yaml
# .github/workflows/auto-review.yml
name: Auto Code Review
on: [pull_request]
jobs:
  claude-review:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Claude Review
        run: |
          claude "Revisa los cambios en este PR y comenta"
```

### Pre-commit Hooks
```bash
# .git/hooks/pre-commit
#!/bin/sh
claude "Verifica que mi código siga los estándares antes de commit"
```

## 🎯 Templates de Tareas

### Template para Nueva Feature
```markdown
## Feature: [Nombre]
**Asignado a:** [Intern Name]
**Prioridad:** Alta/Media/Baja
**Estimación:** X días

### Descripción
[Descripción detallada]

### Criterios de Aceptación
- [ ] Criterio 1
- [ ] Criterio 2
- [ ] Criterio 3

### Pasos de Implementación
1. Analizar requerimientos con Claude
2. Crear branch feature/TICKET-descripcion
3. Implementar siguiendo estándares
4. Escribir tests
5. Crear PR y solicitar review
```

### Template para Bug Fix
```markdown
## Bug: [Descripción]
**Reportado por:** [Usuario]
**Severidad:** Crítica/Alta/Media/Baja
**Asignado a:** [Developer]

### Pasos para Reproducir
1. Paso 1
2. Paso 2
3. Resultado actual vs esperado

### Solución Propuesta
[Usar Claude para analizar]
```

## 💡 Comandos Útiles para Todo el Equipo

```bash
# Generar documentación automática
claude "Documenta todas las funciones en /admin/manage-products.php"

# Encontrar código duplicado
claude "Encuentra código duplicado en el proyecto y sugiere cómo consolidarlo"

# Optimizar queries SQL
claude "Analiza y optimiza las queries SQL en el proyecto"

# Generar tests faltantes
claude "Identifica funciones sin tests y genera tests unitarios"

# Revisar seguridad
claude "Haz un análisis de seguridad completo del proyecto"

# Mejorar UX
claude "Sugiere mejoras de UX para el checkout basándote en mejores prácticas"
```

## 📚 Recursos de Aprendizaje

### Para Interns
- PHP: Manual oficial y PSR standards
- JavaScript: MDN y ES6+ features
- Git: Pro Git book
- Testing: PHPUnit documentation
- Security: OWASP guidelines

### Sesiones de Mentoring (Senior Dev → Interns)
- Lunes: Code Review grupal
- Miércoles: Arquitectura y patrones
- Viernes: Debugging y optimización

---

**Última actualización:** Julio 2025
**Versión:** 1.0
**Mantenedor:** Tech Lead