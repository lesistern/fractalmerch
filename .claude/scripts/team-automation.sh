#!/bin/bash

# ====================================
# CLAUDE CLI TEAM AUTOMATION SYSTEM
# ====================================

# Configuración de rutas
PROJECT_ROOT="C:/xampp/htdocs/proyecto"
CLAUDE_DIR=".claude"
CONTEXTS_DIR="$CLAUDE_DIR/contexts"
LOGS_DIR="$CLAUDE_DIR/logs"

# Crear directorios si no existen
mkdir -p $CONTEXTS_DIR $LOGS_DIR

# ====================================
# FUNCIONES DE AI DEVELOPERS
# ====================================

senior_dev() {
    local task="$1"
    local file="$2"
    
    echo "🔧 Senior Dev trabajando en: $task"
    
    claude --context="$CONTEXTS_DIR/senior-dev.md" \
           --log="$LOGS_DIR/senior-dev-$(date +%Y%m%d-%H%M%S).log" \
           "Como Senior Developer, $task en el archivo $file. 
            Prioridad: seguridad y performance. 
            Proyecto: PHP e-commerce con vulnerabilidades críticas.
            Proporciona solución técnica detallada y código seguro."
}

intern_frontend() {
    local task="$1"
    local file="$2"
    
    echo "🎨 Frontend Intern trabajando en: $task"
    
    claude --context="$CONTEXTS_DIR/intern-frontend.md" \
           --log="$LOGS_DIR/intern-frontend-$(date +%Y%m%d-%H%M%S).log" \
           "Como Frontend Intern, $task en $file. 
            Enfoque: JavaScript, CSS, UX mobile.
            Nivel: intermedio, dispuesto a aprender.
            Pide explicaciones cuando sea necesario."
}

intern_backend() {
    local task="$1"
    local file="$2"
    
    echo "⚙️ Backend Intern trabajando en: $task"
    
    claude --context="$CONTEXTS_DIR/intern-backend.md" \
           --log="$LOGS_DIR/intern-backend-$(date +%Y%m%d-%H%M%S).log" \
           "Como Backend Intern, $task en $file.
            Enfoque: PHP, MySQL, seguridad, APIs.
            Prioridad actual: corregir vulnerabilidades de seguridad.
            Implementa siguiendo mejores prácticas."
}

intern_fullstack() {
    local task="$1"
    local feature="$2"
    
    echo "🔄 Full-Stack Intern trabajando en: $task"
    
    claude --context="$CONTEXTS_DIR/intern-fullstack.md" \
           --log="$LOGS_DIR/intern-fullstack-$(date +%Y%m%d-%H%M%S).log" \
           "Como Full-Stack Intern, $task para la feature $feature.
            Implementa solución completa: frontend + backend + tests.
            Enfoque en documentación y calidad."
}

# ====================================
# WORKFLOWS AUTOMATIZADOS
# ====================================

daily_standup() {
    echo "📅 DAILY STANDUP AUTOMÁTICO"
    
    # Senior Dev review
    senior_dev "genera reporte de estado del proyecto con métricas críticas" ""
    
    # Cada intern reporta su progreso
    intern_frontend "reporta progreso en tareas de frontend y blockers" ""
    intern_backend "reporta progreso en fixes de seguridad y backend" ""
    intern_fullstack "reporta features completadas y testing status" ""
}

security_audit() {
    echo "🔒 AUDITORÍA DE SEGURIDAD AUTOMÁTICA"
    
    # Senior Dev hace auditoría completa
    senior_dev "realiza auditoría completa de seguridad del proyecto" "todos los archivos PHP"
    
    # Backend Intern implementa fixes
    intern_backend "implementa los fixes de seguridad prioritarios identificados" "archivos vulnerables"
}

code_review() {
    local files="$1"
    
    echo "👁️ CODE REVIEW AUTOMÁTICO"
    
    senior_dev "realiza code review detallado con feedback constructivo" "$files"
}

performance_optimization() {
    echo "⚡ OPTIMIZACIÓN DE PERFORMANCE"
    
    # Frontend optimiza assets
    intern_frontend "optimiza CSS y JavaScript para reducir tamaño y mejorar carga" "assets/"
    
    # Backend optimiza queries
    intern_backend "analiza y optimiza queries de base de datos para performance" "archivos PHP con DB"
    
    # Senior supervisa
    senior_dev "revisa optimizaciones de performance y sugiere mejoras arquitectónicas" "proyecto completo"
}

feature_implementation() {
    local feature="$1"
    
    echo "🚀 IMPLEMENTACIÓN DE FEATURE: $feature"
    
    # Planning por Senior Dev
    senior_dev "diseña arquitectura e implementación para la feature $feature" ""
    
    # Implementación distribuida
    intern_frontend "implementa componentes frontend para $feature" ""
    intern_backend "implementa lógica backend para $feature" ""
    intern_fullstack "integra frontend y backend, crea tests para $feature" ""
}

emergency_fix() {
    local issue="$1"
    
    echo "🚨 FIX DE EMERGENCIA: $issue"
    
    # Senior Dev lidera
    senior_dev "analiza y proporciona solución inmediata para el problema crítico: $issue" ""
    
    # Todos los interns apoyan
    intern_backend "implementa fix de emergencia para: $issue" ""
    intern_frontend "verifica que el fix no rompa frontend para: $issue" ""
    intern_fullstack "crea tests de regresión para: $issue" ""
}

# ====================================
# COMANDOS SHORTCUTS
# ====================================

# Aliases para comandos rápidos
alias sd='senior_dev'
alias if='intern_frontend' 
alias ib='intern_backend'
alias ifs='intern_fullstack'

# Workflows
alias standup='daily_standup'
alias security='security_audit'
alias review='code_review'
alias optimize='performance_optimization'
alias feature='feature_implementation'
alias emergency='emergency_fix'

# ====================================
# MENU INTERACTIVO
# ====================================

show_menu() {
    echo "🤖 CLAUDE CLI TEAM AUTOMATION"
    echo "=============================="
    echo "1. Daily Standup"
    echo "2. Security Audit"
    echo "3. Code Review"
    echo "4. Performance Optimization"
    echo "5. Feature Implementation"
    echo "6. Emergency Fix"
    echo "7. Custom Senior Dev Task"
    echo "8. Custom Intern Task"
    echo "9. Exit"
    echo "=============================="
}

team_automation() {
    while true; do
        show_menu
        read -p "Selecciona opción (1-9): " choice
        
        case $choice in
            1) daily_standup ;;
            2) security_audit ;;
            3) 
                read -p "Archivos a revisar: " files
                code_review "$files"
                ;;
            4) performance_optimization ;;
            5)
                read -p "Nombre de la feature: " feature_name
                feature_implementation "$feature_name"
                ;;
            6)
                read -p "Describe el problema crítico: " issue
                emergency_fix "$issue"
                ;;
            7)
                read -p "Tarea para Senior Dev: " task
                read -p "Archivo(s): " file
                senior_dev "$task" "$file"
                ;;
            8)
                echo "a) Frontend  b) Backend  c) Full-Stack"
                read -p "Tipo de intern: " intern_type
                read -p "Tarea: " task
                read -p "Archivo/Feature: " target
                
                case $intern_type in
                    a) intern_frontend "$task" "$target" ;;
                    b) intern_backend "$task" "$target" ;;
                    c) intern_fullstack "$task" "$target" ;;
                esac
                ;;
            9) exit 0 ;;
            *) echo "Opción inválida" ;;
        esac
        
        echo ""
        read -p "Presiona Enter para continuar..."
        clear
    done
}

# ====================================
# EJECUTAR SI ES LLAMADO DIRECTAMENTE
# ====================================

if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    team_automation
fi