# 🚀 Guía de Onboarding - Nuevo Miembro del Equipo

## Bienvenido al Equipo! 👋

Esta guía te ayudará a configurar tu entorno y empezar a contribuir al proyecto rápidamente.

## 📋 Día 1: Setup Inicial

### 1. Instalar Herramientas Necesarias
- [ ] **XAMPP** (https://www.apachefriends.org/)
  - PHP 7.4+
  - MySQL 5.7+
  - Apache
- [ ] **Cursor** (https://cursor.sh/)
- [ ] **Claude CLI** (desde Cursor)
- [ ] **Git** (https://git-scm.com/)
- [ ] **Node.js** (para herramientas de desarrollo)

### 2. Clonar el Proyecto
```bash
cd C:\xampp\htdocs
git clone [REPO_URL] proyecto
cd proyecto
```

### 3. Configurar Base de Datos
```bash
# Abrir phpMyAdmin
http://localhost/phpmyadmin

# Crear base de datos
CREATE DATABASE proyecto_web;

# Importar estructura
mysql -u root proyecto_web < database.sql
```

### 4. Configurar Cursor
1. Abrir proyecto en Cursor
2. Instalar extensiones recomendadas:
   - PHP Intelephense
   - ESLint
   - Prettier
3. Configurar Claude CLI (Ctrl+K para test)

### 5. Verificar Instalación
```bash
# Abrir en navegador
http://localhost/proyecto/

# Credenciales admin
Email: admin@proyecto.com
Password: password
```

## 📖 Día 2: Conocer el Proyecto

### Estructura de Archivos
```
proyecto/
├── admin/          # Panel de administración
├── assets/         # CSS, JS, imágenes
├── config/         # Configuraciones
├── includes/       # Archivos compartidos
└── *.php          # Páginas principales
```

### Páginas Principales
1. **index.php** - Homepage con hero dividido
2. **particulares.php** - Tienda e-commerce
3. **empresas.php** - Landing B2B
4. **product-detail.php** - Detalle de producto
5. **checkout.php** - Proceso de compra
6. **customize-shirt.php** - Editor de remeras

### Leer Documentación
- [ ] **CLAUDE.md** - Información completa del proyecto
- [ ] **TEAM_WORKFLOW.md** - Procesos del equipo
- [ ] **QUICK_COMMANDS.md** - Comandos útiles

## 🎯 Día 3: Primera Tarea

### 1. Crear tu Branch
```bash
git checkout develop
git pull origin develop
git checkout -b feature/TUNOM-primera-tarea
```

### 2. Tarea de Práctica: Agregar tu perfil al About
```php
// En about.php, agregar tu card al equipo
<div class="team-member">
    <img src="assets/images/team/tu-foto.jpg" alt="Tu Nombre">
    <h3>Tu Nombre</h3>
    <p>Tu Rol</p>
    <p>Breve descripción</p>
</div>
```

### 3. Usar Claude CLI
```bash
# Pedir ayuda
claude "cómo agrego mi perfil al equipo en about.php"

# Revisar tu código
claude "revisa mi código en about.php"

# Generar commit message
claude "genera mensaje de commit para mis cambios"
```

### 4. Hacer Commit y PR
```bash
git add .
git commit -m "feat[TEAM-001] Agregar [Tu Nombre] al equipo"
git push origin feature/TUNOM-primera-tarea
```

## 🛠️ Semana 1: Tareas de Aprendizaje

### Lunes: Entender el Sistema de Usuarios
```bash
claude "explícame cómo funciona el sistema de autenticación"
```
- [ ] Revisar `login.php` y `register.php`
- [ ] Entender roles y permisos
- [ ] Hacer diagrama de flujo

### Martes: Explorar el E-commerce
```bash
claude "guíame por el flujo de compra completo"
```
- [ ] Agregar productos al carrito
- [ ] Completar un checkout
- [ ] Revisar `particulares.php`

### Miércoles: Panel Admin
```bash
claude "qué funcionalidades tiene el panel admin"
```
- [ ] Explorar `/admin/dashboard.php`
- [ ] Crear un producto nuevo
- [ ] Revisar estadísticas

### Jueves: Debugging Básico
```bash
claude "enséñame a debuggear en este proyecto"
```
- [ ] Activar error reporting
- [ ] Usar console.log estratégicamente
- [ ] Revisar logs de Apache

### Viernes: Tu Primera Feature
```bash
claude "sugiere una feature simple para empezar"
```
- [ ] Implementar feature asignada
- [ ] Escribir tests
- [ ] Crear PR para review

## 💡 Tips para Interns

### Comandos Claude Más Útiles
```bash
# Cuando estés perdido
claude "qué hace este código: [PEGAR CÓDIGO]"

# Para errores
claude "error: [PEGAR ERROR] ¿cómo lo arreglo?"

# Para aprender
claude "mejores prácticas para [TEMA]"

# Para implementar
claude "cómo implemento [FEATURE] en [ARCHIVO]"
```

### Atajos de Cursor
- **Ctrl+K**: Claude inline (preguntas rápidas)
- **Ctrl+L**: Claude chat (conversaciones)
- **Ctrl+P**: Buscar archivos
- **Ctrl+Shift+F**: Buscar en proyecto

### Flujo de Trabajo Diario
1. **9:00 AM** - Daily standup
   ```bash
   claude "qué hice ayer y qué haré hoy"
   ```

2. **9:30 AM** - Revisar tareas
   ```bash
   claude "lista mis tareas pendientes"
   ```

3. **10:00 AM - 12:00 PM** - Coding
   ```bash
   claude "ayúdame con [TAREA]"
   ```

4. **2:00 PM - 5:00 PM** - Coding + Tests
   ```bash
   claude "genera tests para lo que implementé"
   ```

5. **5:00 PM** - Commit y push
   ```bash
   claude "revisa mi código antes de commit"
   ```

## 📚 Recursos de Aprendizaje

### PHP
- [PHP Manual](https://www.php.net/manual/es/)
- [PSR-12 Standard](https://www.php-fig.org/psr/psr-12/)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)

### JavaScript
- [MDN Web Docs](https://developer.mozilla.org/es/)
- [JavaScript.info](https://javascript.info/)
- [ES6 Features](http://es6-features.org/)

### Git
- [Pro Git Book](https://git-scm.com/book/es/v2)
- [Git Flow](https://www.atlassian.com/es/git/tutorials/comparing-workflows/gitflow-workflow)

### Seguridad
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security](https://www.php.net/manual/es/security.php)

## 🤝 Canales de Comunicación

### Slack/Discord Channels
- **#general** - Comunicación general
- **#dev-help** - Ayuda técnica
- **#code-review** - Revisiones de código
- **#random** - Off-topic

### Reuniones Regulares
- **Lunes 9:00 AM** - Sprint Planning
- **Diario 9:00 AM** - Daily Standup
- **Viernes 4:00 PM** - Demo & Retrospectiva

### Contactos Clave
- **Senior Dev**: [Nombre] - Preguntas técnicas
- **CEO**: [Nombre] - Visión y estrategia
- **HR**: [Nombre] - Temas administrativos

## ✅ Checklist Primera Semana

### Setup
- [ ] XAMPP instalado y funcionando
- [ ] Proyecto clonado y corriendo
- [ ] Cursor configurado con Claude
- [ ] Acceso a repositorio Git
- [ ] Cuenta en sistema de tickets

### Conocimiento
- [ ] Leí toda la documentación
- [ ] Entiendo la arquitectura
- [ ] Conozco el flujo de trabajo
- [ ] Sé usar Claude CLI
- [ ] Completé tarea de práctica

### Social
- [ ] Me presenté al equipo
- [ ] Tengo mentor asignado
- [ ] Estoy en canales de comunicación
- [ ] Agendé 1:1 con manager
- [ ] Participé en daily standup

## 🎉 Bienvenido al Equipo!

Recuerda:
- **No hay preguntas tontas** - Siempre pregunta
- **Claude es tu amigo** - Úsalo constantemente
- **Code review es aprendizaje** - No lo tomes personal
- **Fail fast, learn faster** - Los errores son OK
- **Documenta todo** - Tu yo futuro te lo agradecerá

**¿Listo para empezar? ¡Hagamos código increíble juntos! 🚀**

---

*Si tienes dudas, usa:*
```bash
claude "tengo una duda sobre el onboarding: [TU DUDA]"
```