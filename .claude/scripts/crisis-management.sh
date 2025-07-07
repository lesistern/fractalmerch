#!/bin/bash

# 🚨 COMANDOS DE CRISIS - SECURITY FIXES

# ====================================
# COMANDOS INMEDIATOS PARA LA CRISIS ACTUAL
# ====================================

# 1. ACTIVAR MODO CRISIS
crisis_mode() {
    echo "🚨 ACTIVANDO MODO CRISIS - SECURITY FIRST"
    
    # Senior Dev toma control
    claude --context=".claude/contexts/senior-dev.md" \
    "CRISIS MODE ACTIVADO. Proyecto tiene vulnerabilidades críticas que bloquean producción:
    - 69 archivos con directory traversal
    - Solo 1 archivo con escape HTML  
    - 0 protección CSRF
    - 6 archivos JS con errores sintaxis
    
    Como Senior Dev, crea plan de acción inmediato para resolver en orden de prioridad.
    Divide tareas entre los 3 interns y da instrucciones específicas."
}

# 2. FIXES ESPECÍFICOS POR DESARROLLADOR
security_senior() {
    claude --context=".claude/contexts/senior-dev.md" \
    "TAREA CRÍTICA: Implementa protección CSRF global en el proyecto.
    Crea middleware de seguridad que se aplique a todos los forms.
    Código debe ser production-ready y seguir OWASP guidelines."
}

security_backend() {
    claude --context=".claude/contexts/intern-backend.md" \
    "TAREA CRÍTICA: Implementa sanitización HTML en TODOS los inputs del proyecto.
    Usa htmlspecialchars() con ENT_QUOTES y UTF-8 en cada input.
    Empieza por los archivos más críticos: login.php, register.php, admin."
}

security_frontend() {
    claude --context=".claude/contexts/intern-frontend.md" \
    "TAREA CRÍTICA: Corrige los 6 archivos JavaScript con errores sintaxis.
    Identifica cada error, explica la causa, implementa fix.
    Asegúrate que no se rompa funcionalidad del carrito ni checkout."
}

security_fullstack() {
    claude --context=".claude/contexts/intern-fullstack.md" \
    "TAREA CRÍTICA: Crea tests de seguridad automatizados.
    Tests deben verificar: input sanitization, CSRF protection, SQL injection prevention.
    Configura CI básico para correr tests en cada commit."
}

# 3. VALIDACIÓN Y TESTING
validate_security() {
    echo "🔍 VALIDANDO FIXES DE SEGURIDAD"
    
    claude --context=".claude/contexts/senior-dev.md" \
    "Valida que todos los security fixes implementados por el equipo sean correctos.
    Haz penetration testing básico y confirma que vulnerabilidades están resueltas.
    Da go/no-go para cada fix implementado."
}

# 4. PROGRESS TRACKING
security_status() {
    echo "📊 STATUS DE SECURITY FIXES"
    
    claude --context=".claude/contexts/senior-dev.md" \
    "Genera reporte de progreso de security fixes:
    - Vulnerabilidades resueltas vs pendientes
    - Quality de cada fix implementado  
    - Blockers actuales
    - Timeline estimado para completion
    - Next actions"
}

# 5. DEPLOYMENT READINESS
production_check() {
    echo "🚀 VERIFICACIÓN PARA PRODUCCIÓN"
    
    claude --context=".claude/contexts/senior-dev.md" \
    "Evalúa si el proyecto está listo para producción:
    - Todas las vulnerabilidades críticas resueltas
    - JS errors corregidos
    - Performance acceptable
    - Basic testing implementado
    
    Proporciona checklist final y go/no-go decision."
}

# ====================================
# WORKFLOWS DE EMERGENCIA
# ====================================

# Daily Crisis Standup (15 minutos)
crisis_standup() {
    echo "⏰ CRISIS STANDUP - 15 MIN MAX"
    
    echo "👤 Senior Dev Status:"
    security_senior
    
    echo "👤 Backend Intern Status:" 
    security_backend
    
    echo "👤 Frontend Intern Status:"
    security_frontend
    
    echo "👤 Full-Stack Intern Status:"
    security_fullstack
    
    echo "📊 Overall Status:"
    security_status
}

# Emergency Deploy Check
emergency_deploy() {
    echo "🚨 EMERGENCY DEPLOY CHECK"
    
    validate_security
    production_check
    
    echo "🎯 DEPLOY DECISION:"
    claude --context=".claude/contexts/senior-dev.md" \
    "Basándote en el estado actual de security fixes, ¿está el proyecto listo para deploy de emergencia?
    Si no, ¿qué fixes específicos faltan y cuánto tiempo tomarían?"
}

# ====================================
# SCRIPTS DE EJECUCIÓN RÁPIDA
# ====================================

# Ejecutar todos los fixes en paralelo
parallel_fixes() {
    echo "⚡ EJECUTANDO FIXES EN PARALELO"
    
    security_senior &
    security_backend &
    security_frontend &
    security_fullstack &
    
    wait
    
    echo "✅ TODOS LOS FIXES COMPLETADOS"
    validate_security
}

# ====================================
# ALIASES PARA CRISIS
# ====================================

alias crisis='crisis_mode'
alias fix-senior='security_senior'
alias fix-backend='security_backend' 
alias fix-frontend='security_frontend'
alias fix-fullstack='security_fullstack'
alias validate='validate_security'
alias status='security_status'
alias deploy-check='emergency_deploy'
alias parallel='parallel_fixes'

# ====================================
# MENU DE CRISIS INTERACTIVO
# ====================================

crisis_menu() {
    while true; do
        echo "🚨 CRISIS MANAGEMENT MENU"
        echo "========================="
        echo "1. Activar Modo Crisis"
        echo "2. Senior Dev Security Fix"
        echo "3. Backend Intern Security Fix"
        echo "4. Frontend Intern JS Fix"
        echo "5. Full-Stack Testing Setup"
        echo "6. Validar Todos los Fixes"
        echo "7. Security Status Report"
        echo "8. Production Readiness Check"
        echo "9. Crisis Standup"
        echo "10. Parallel Execution"
        echo "11. Exit Crisis Mode"
        echo "========================="
        
        read -p "Selecciona acción (1-11): " choice
        
        case $choice in
            1) crisis_mode ;;
            2) security_senior ;;
            3) security_backend ;;
            4) security_frontend ;;
            5) security_fullstack ;;
            6) validate_security ;;
            7) security_status ;;
            8) production_check ;;
            9) crisis_standup ;;
            10) parallel_fixes ;;
            11) 
                echo "🎉 Saliendo del modo crisis"
                exit 0
                ;;
            *) echo "Opción inválida" ;;
        esac
        
        echo ""
        read -p "Presiona Enter para continuar..."
        clear
    done
}

# Ejecutar crisis menu si el script es llamado directamente
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    crisis_menu
fi