<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🐛 Debug Sidebar - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-bug"></i> Debug Sidebar</h1>
                <p class="header-subtitle">Diagnóstico completo del menú lateral</p>
            </div>
        </div>

        <section class="debug-section">
            <h2>Diagnóstico del Sidebar</h2>
            
            <div class="debug-actions">
                <button onclick="runFullDiagnosis()" class="debug-btn primary">
                    <i class="fas fa-search"></i> Diagnóstico Completo
                </button>
                <button onclick="debugEventListeners()" class="debug-btn secondary">
                    <i class="fas fa-mouse-pointer"></i> Verificar Event Listeners
                </button>
                <button onclick="debugCSS()" class="debug-btn info">
                    <i class="fas fa-paint-brush"></i> Verificar CSS
                </button>
                <button onclick="simulateClicks()" class="debug-btn warning">
                    <i class="fas fa-hand-pointer"></i> Simular Clicks
                </button>
                <button onclick="clearConsole()" class="debug-btn danger">
                    <i class="fas fa-trash"></i> Limpiar Console
                </button>
            </div>

            <div id="debug-output" class="debug-output"></div>
        </section>
    </div>
</div>

<script>
let debugLog = [];

function log(message, type = 'info') {
    const timestamp = new Date().toLocaleTimeString();
    debugLog.push({ timestamp, message, type });
    console.log(`[${timestamp}] ${message}`);
    updateDebugOutput();
}

function updateDebugOutput() {
    const output = document.getElementById('debug-output');
    if (!output) return;
    
    let html = '<div class="debug-logs">';
    debugLog.forEach(entry => {
        html += `<div class="debug-entry ${entry.type}">
            <span class="timestamp">[${entry.timestamp}]</span>
            <span class="message">${entry.message}</span>
        </div>`;
    });
    html += '</div>';
    output.innerHTML = html;
    
    // Auto scroll to bottom
    output.scrollTop = output.scrollHeight;
}

function runFullDiagnosis() {
    debugLog = [];
    log('🔍 Iniciando diagnóstico completo del sidebar...', 'info');
    
    // Check if AdminFunctions is loaded
    const adminFunctionsLoaded = typeof window.AdminFunctions !== 'undefined';
    log(`AdminFunctions cargado: ${adminFunctionsLoaded ? '✅' : '❌'}`, adminFunctionsLoaded ? 'success' : 'error');
    
    // Check expandable items
    const expandableItems = document.querySelectorAll('.nav-item-expandable');
    log(`Elementos expandibles encontrados: ${expandableItems.length}`, 'info');
    
    // Check each expandable item
    expandableItems.forEach((item, index) => {
        const menuName = item.querySelector('span') ? item.querySelector('span').textContent.trim() : `Menu ${index}`;
        const navExpandable = item.querySelector('.nav-expandable');
        const submenu = item.querySelector('.nav-submenu');
        const subItems = item.querySelectorAll('.nav-subitem');
        const isActive = item.classList.contains('active');
        
        log(`Menu "${menuName}":`, 'info');
        log(`  - Expandible: ${navExpandable ? '✅' : '❌'}`, navExpandable ? 'success' : 'error');
        log(`  - Submenu: ${submenu ? '✅' : '❌'}`, submenu ? 'success' : 'error');
        log(`  - Sub-items: ${subItems.length}`, 'info');
        log(`  - Estado: ${isActive ? 'Expandido' : 'Contraído'}`, isActive ? 'success' : 'info');
        
        if (submenu) {
            const computedStyle = window.getComputedStyle(submenu);
            log(`  - Max-height: ${computedStyle.maxHeight}`, 'info');
            log(`  - Overflow: ${computedStyle.overflow}`, 'info');
            log(`  - Transition: ${computedStyle.transition}`, 'info');
        }
    });
    
    log('✅ Diagnóstico completo finalizado', 'success');
}

function debugEventListeners() {
    log('🖱️ Verificando event listeners...', 'info');
    
    const expandableItems = document.querySelectorAll('.nav-item-expandable');
    let listenersAttached = 0;
    
    expandableItems.forEach((item, index) => {
        const menuName = item.querySelector('span') ? item.querySelector('span').textContent.trim() : `Menu ${index}`;
        const navExpandable = item.querySelector('.nav-expandable');
        
        if (navExpandable) {
            // Check if click listener is attached by checking for any listeners
            const hasListener = navExpandable.onclick || 
                               navExpandable.getAttribute('onclick') ||
                               navExpandable.listeners;
            
            if (hasListener) {
                listenersAttached++;
                log(`✅ "${menuName}" tiene event listener`, 'success');
            } else {
                log(`❌ "${menuName}" NO tiene event listener`, 'error');
                
                // Try to attach listener manually
                navExpandable.addEventListener('click', function(e) {
                    e.preventDefault();
                    item.classList.toggle('active');
                    log(`🔧 Listener manual agregado para "${menuName}"`, 'warning');
                });
                log(`🔧 Listener manual agregado para "${menuName}"`, 'warning');
            }
        }
    });
    
    log(`Total listeners encontrados: ${listenersAttached}/${expandableItems.length}`, 'info');
}

function debugCSS() {
    log('🎨 Verificando CSS...', 'info');
    
    const expandableItems = document.querySelectorAll('.nav-item-expandable');
    
    expandableItems.forEach((item, index) => {
        const menuName = item.querySelector('span') ? item.querySelector('span').textContent.trim() : `Menu ${index}`;
        const submenu = item.querySelector('.nav-submenu');
        
        if (submenu) {
            const computedStyle = window.getComputedStyle(submenu);
            const itemStyle = window.getComputedStyle(item);
            
            log(`CSS para "${menuName}":`, 'info');
            log(`  - Submenu max-height: ${computedStyle.maxHeight}`, 'info');
            log(`  - Submenu overflow: ${computedStyle.overflow}`, 'info');
            log(`  - Submenu transition: ${computedStyle.transition}`, 'info');
            log(`  - Item classes: ${item.className}`, 'info');
            
            // Check if active class changes max-height
            item.classList.add('active');
            const activeStyle = window.getComputedStyle(submenu);
            log(`  - Active max-height: ${activeStyle.maxHeight}`, 'success');
            
            item.classList.remove('active');
            const inactiveStyle = window.getComputedStyle(submenu);
            log(`  - Inactive max-height: ${inactiveStyle.maxHeight}`, 'info');
        }
    });
}

function simulateClicks() {
    log('👆 Simulando clicks en todos los menús...', 'info');
    
    const expandableItems = document.querySelectorAll('.nav-item-expandable');
    
    expandableItems.forEach((item, index) => {
        const menuName = item.querySelector('span') ? item.querySelector('span').textContent.trim() : `Menu ${index}`;
        const navExpandable = item.querySelector('.nav-expandable');
        
        if (navExpandable) {
            setTimeout(() => {
                log(`Simulando click en "${menuName}"...`, 'info');
                
                const wasActive = item.classList.contains('active');
                
                // Simulate click event
                const clickEvent = new MouseEvent('click', {
                    view: window,
                    bubbles: true,
                    cancelable: true
                });
                
                navExpandable.dispatchEvent(clickEvent);
                
                setTimeout(() => {
                    const isActive = item.classList.contains('active');
                    if (isActive !== wasActive) {
                        log(`✅ "${menuName}" cambió estado: ${isActive ? 'Expandido' : 'Contraído'}`, 'success');
                    } else {
                        log(`❌ "${menuName}" NO cambió estado`, 'error');
                        
                        // Try manual toggle
                        item.classList.toggle('active');
                        log(`🔧 Toggle manual aplicado a "${menuName}"`, 'warning');
                    }
                }, 100);
                
            }, index * 500);
        }
    });
}

function clearConsole() {
    debugLog = [];
    console.clear();
    updateDebugOutput();
    log('🧹 Console limpiado', 'info');
}

// Auto-run diagnosis on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        log('🚀 Página cargada, ejecutando diagnóstico automático...', 'info');
        runFullDiagnosis();
        
        // Test the fix by trying to toggle all menus
        setTimeout(() => {
            log('🔧 Probando toggles automáticos...', 'info');
            const expandableItems = document.querySelectorAll('.nav-item-expandable');
            expandableItems.forEach((item, index) => {
                setTimeout(() => {
                    const menuName = item.querySelector('span')?.textContent.trim() || `Menu ${index}`;
                    const navExpandable = item.querySelector('.nav-expandable');
                    if (navExpandable) {
                        navExpandable.click();
                        log(`✅ Click automático en "${menuName}"`, 'success');
                    }
                }, index * 200);
            });
        }, 2000);
    }, 1000);
});
</script>

<style>
.debug-section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.debug-section h2 {
    color: #333;
    margin-bottom: 1.5rem;
}

.debug-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.debug-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.debug-btn.primary {
    background: #007bff;
    color: white;
}

.debug-btn.secondary {
    background: #6c757d;
    color: white;
}

.debug-btn.info {
    background: #17a2b8;
    color: white;
}

.debug-btn.warning {
    background: #ffc107;
    color: #212529;
}

.debug-btn.danger {
    background: #dc3545;
    color: white;
}

.debug-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.debug-output {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 1.5rem;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #333;
}

.debug-logs {
    line-height: 1.6;
}

.debug-entry {
    margin-bottom: 0.5rem;
    padding: 0.25rem 0;
}

.debug-entry.info {
    color: #d4d4d4;
}

.debug-entry.success {
    color: #4caf50;
}

.debug-entry.error {
    color: #f44336;
}

.debug-entry.warning {
    color: #ff9800;
}

.timestamp {
    color: #888;
    margin-right: 0.5rem;
}

.message {
    white-space: pre-wrap;
}

/* Optimización compacta */
.modern-admin-main { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.debug-section { padding: 1.5rem !important; }
.debug-actions { margin-bottom: 1.5rem !important; gap: 0.75rem !important; }
.debug-btn { padding: 0.6rem 1.2rem !important; font-size: 0.9rem !important; }
.debug-output { padding: 1rem !important; max-height: 400px !important; }
</style>

</body>
</html>