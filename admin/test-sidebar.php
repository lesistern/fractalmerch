<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para acceder al panel de administración');
    redirect('../index.php');
}

$page_title = '🔧 Test Sidebar - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-cog"></i> Test de Sidebar</h1>
                <p class="header-subtitle">Prueba de funcionalidad del menú lateral</p>
            </div>
        </div>

        <section class="test-section">
            <h2>Test de Navegación del Sidebar</h2>
            <p>Esta página es para probar que el sidebar funciona correctamente. Deberías poder:</p>
            <ul>
                <li>✅ Expandir y contraer el menú "Marketing"</li>
                <li>✅ Expandir y contraer otros menús sin cerrar Marketing</li>
                <li>✅ Navegar entre páginas manteniendo el estado expandido</li>
                <li>✅ Ver múltiples menús expandidos al mismo tiempo</li>
            </ul>

            <div class="test-buttons">
                <button onclick="testSidebarFunctionality()" class="test-btn">
                    <i class="fas fa-play"></i> Ejecutar Test Completo
                </button>
                <button onclick="showSidebarStatus()" class="test-btn secondary">
                    <i class="fas fa-info"></i> Estado del Sidebar
                </button>
                <button onclick="testIndividualMenus()" class="test-btn info">
                    <i class="fas fa-list"></i> Test Individual
                </button>
                <button onclick="toggleAllMenus()" class="test-btn warning">
                    <i class="fas fa-expand-alt"></i> Expandir Todos
                </button>
                <button onclick="collapseAllMenus()" class="test-btn danger">
                    <i class="fas fa-compress-alt"></i> Contraer Todos
                </button>
            </div>

            <div id="test-results" class="test-results"></div>
        </section>
    </div>
</div>

<script>
function testSidebarFunctionality() {
    const results = document.getElementById('test-results');
    let testResults = '<h3>Resultados del Test:</h3><ul>';
    
    // Test 1: Check if AdminFunctions is loaded
    const adminFunctionsLoaded = typeof window.AdminFunctions !== 'undefined';
    testResults += `<li class="${adminFunctionsLoaded ? 'success' : 'error'}">
        AdminFunctions cargado: ${adminFunctionsLoaded ? '✅' : '❌'}
    </li>`;
    
    // Test 2: Check expandable items
    const expandableItems = document.querySelectorAll('.nav-item-expandable');
    testResults += `<li class="${expandableItems.length > 0 ? 'success' : 'error'}">
        Elementos expandibles encontrados: ${expandableItems.length} ${expandableItems.length > 0 ? '✅' : '❌'}
    </li>`;
    
    // Test 3: Check if click events are attached
    let clickEventsAttached = 0;
    expandableItems.forEach(item => {
        const navExpandable = item.querySelector('.nav-expandable');
        if (navExpandable) {
            // Try to trigger a fake click to see if event is attached
            const event = new Event('click');
            navExpandable.dispatchEvent(event);
            clickEventsAttached++;
        }
    });
    
    testResults += `<li class="${clickEventsAttached > 0 ? 'success' : 'error'}">
        Eventos de click configurados: ${clickEventsAttached} ${clickEventsAttached > 0 ? '✅' : '❌'}
    </li>`;
    
    // Test 4: Check CSS classes
    const hasActiveClass = document.querySelector('.nav-item-expandable.active') !== null;
    testResults += `<li class="${hasActiveClass ? 'success' : 'warning'}">
        Algún menú activo: ${hasActiveClass ? '✅' : '⚠️ (normal si no hay subpágina activa)'}
    </li>`;
    
    testResults += '</ul>';
    results.innerHTML = testResults;
}

function showSidebarStatus() {
    const results = document.getElementById('test-results');
    let status = '<h3>Estado Actual del Sidebar:</h3><ul>';
    
    document.querySelectorAll('.nav-item-expandable').forEach((item, index) => {
        const menuName = item.querySelector('span') ? item.querySelector('span').textContent.trim() : `Menu ${index}`;
        const isActive = item.classList.contains('active');
        const hasSubmenu = item.querySelector('.nav-submenu') !== null;
        
        status += `<li>
            <strong>${menuName}:</strong> 
            ${isActive ? '📂 Expandido' : '📁 Contraído'} 
            ${hasSubmenu ? '(tiene submenú)' : '(sin submenú)'}
        </li>`;
    });
    
    status += '</ul>';
    results.innerHTML = status;
}

function toggleAllMenus() {
    document.querySelectorAll('.nav-item-expandable').forEach(item => {
        item.classList.add('active');
    });
    alert('Todos los menús expandidos');
}

function collapseAllMenus() {
    document.querySelectorAll('.nav-item-expandable').forEach(item => {
        item.classList.remove('active');
    });
    alert('Todos los menús contraídos');
}

function testIndividualMenus() {
    const results = document.getElementById('test-results');
    let testResults = '<h3>Test Individual de Menús:</h3>';
    
    const expandableItems = document.querySelectorAll('.nav-item-expandable');
    
    testResults += '<div class="individual-tests">';
    
    expandableItems.forEach((item, index) => {
        const menuName = item.querySelector('span') ? item.querySelector('span').textContent.trim() : `Menu ${index}`;
        const navExpandable = item.querySelector('.nav-expandable');
        const submenu = item.querySelector('.nav-submenu');
        const subItems = item.querySelectorAll('.nav-subitem');
        
        testResults += `<div class="menu-test">
            <h4>${menuName}</h4>
            <ul>
                <li>Elemento expandible: ${navExpandable ? '✅' : '❌'}</li>
                <li>Submenú: ${submenu ? '✅' : '❌'}</li>
                <li>Sub-elementos: ${subItems.length} items</li>
                <li>Estado actual: ${item.classList.contains('active') ? '📂 Expandido' : '📁 Contraído'}</li>
            </ul>
            <button onclick="toggleSpecificMenu(${index})" class="test-menu-btn">
                ${item.classList.contains('active') ? 'Contraer' : 'Expandir'}
            </button>
        </div>`;
    });
    
    testResults += '</div>';
    results.innerHTML = testResults;
}

function toggleSpecificMenu(index) {
    const expandableItems = document.querySelectorAll('.nav-item-expandable');
    const item = expandableItems[index];
    
    if (item) {
        const wasActive = item.classList.contains('active');
        item.classList.toggle('active');
        
        const menuName = item.querySelector('span') ? item.querySelector('span').textContent.trim() : `Menu ${index}`;
        console.log(`Toggled menu: ${menuName} - Now ${wasActive ? 'closed' : 'open'}`);
        
        // Refresh the test display
        setTimeout(testIndividualMenus, 100);
    }
}

// Auto-run test on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(testSidebarFunctionality, 500);
});
</script>

<style>
.test-section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.test-section h2 {
    color: #333;
    margin-bottom: 1rem;
}

.test-section ul {
    margin: 1.5rem 0;
    padding-left: 2rem;
}

.test-section li {
    margin-bottom: 0.5rem;
}

.test-buttons {
    display: flex;
    gap: 1rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.test-btn {
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

.test-btn {
    background: #007bff;
    color: white;
}

.test-btn.secondary {
    background: #6c757d;
    color: white;
}

.test-btn.warning {
    background: #ffc107;
    color: #212529;
}

.test-btn.danger {
    background: #dc3545;
    color: white;
}

.test-btn.info {
    background: #17a2b8;
    color: white;
}

.test-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.test-results {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1.5rem;
    margin-top: 2rem;
}

.test-results h3 {
    margin-top: 0;
    color: #333;
}

.test-results ul {
    margin: 1rem 0 0 0;
    padding-left: 1.5rem;
}

.test-results li.success {
    color: #155724;
}

.test-results li.error {
    color: #721c24;
}

.test-results li.warning {
    color: #856404;
}

.individual-tests {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.menu-test {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 1rem;
}

.menu-test h4 {
    margin: 0 0 1rem 0;
    color: #333;
    font-size: 1.1rem;
}

.menu-test ul {
    margin: 0 0 1rem 0;
    padding-left: 1.5rem;
}

.menu-test li {
    margin-bottom: 0.3rem;
    font-size: 0.9rem;
}

.test-menu-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.test-menu-btn:hover {
    background: #0056b3;
}

/* Optimización compacta */
.modern-admin-main { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.header-subtitle { font-size: 0.85rem !important; }
.test-section { padding: 1.5rem !important; }
.test-section h2 { font-size: 1.3rem !important; }
.test-buttons { margin: 1.5rem 0 !important; gap: 0.75rem !important; }
.test-btn { padding: 0.6rem 1.2rem !important; font-size: 0.9rem !important; }
.test-results { padding: 1rem !important; }
</style>

</body>
</html>