/**
 * Funciones JavaScript unificadas para todo el panel de administración
 * Este archivo contiene todas las funciones necesarias para el funcionamiento del admin
 */

// ========== FUNCIONES BÁSICAS DEL ADMIN ==========

// Sistema de toast notifications
if (typeof toast === 'undefined') {
    window.toast = {
        success: (title, message) => {
            console.log('Success:', title, message);
            showToast('success', title, message);
        },
        error: (title, message) => {
            console.log('Error:', title, message);
            showToast('error', title, message);
        },
        info: (title, message) => {
            console.log('Info:', title, message);
            showToast('info', title, message);
        },
        warning: (title, message) => {
            console.log('Warning:', title, message);
            showToast('warning', title, message);
        }
    };
}

function showToast(type, title, message) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas fa-${getToastIcon(type)}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

function getToastIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// ========== FUNCIONES DE PRODUCTOS ==========

// Funciones principales de productos
async function editProduct(productId) {
    try {
        console.log('Editing product:', productId);
        
        const response = await fetch(`manage-products.php?action=get_product&id=${productId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.product) {
            console.log('Product data received:', data.product);
            if (window.adminPanel) {
                window.adminPanel.showProductForm(data.product);
            } else {
                window.location.href = `manage-products.php?edit=${productId}`;
            }
        } else {
            toast.error('Error al cargar producto', data.message || 'Producto no encontrado');
        }
    } catch (error) {
        console.error('Error fetching product:', error);
        toast.error('Error', 'No se pudo cargar el producto');
    }
}

function viewProduct(productId) {
    console.log('Viewing product:', productId);
    window.open(`../product-detail.php?id=${productId}`, '_blank');
}

function duplicateProduct(productId) {
    console.log('Duplicating product:', productId);
    toast.info('Función pendiente', 'La duplicación de productos estará disponible pronto');
}

function deleteProduct(productId) {
    if (confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')) {
        console.log('Deleting product:', productId);
        window.location.href = `manage-products.php?delete=${productId}`;
    }
}

function shareProduct(productId) {
    console.log('Sharing product:', productId);
    toast.info('Función pendiente', 'La función de compartir estará disponible pronto');
}

function updatePrice(productId, newPrice) {
    console.log('Updating price for product:', productId, 'New price:', newPrice);
    toast.info('Función pendiente', 'La actualización de precios estará disponible pronto');
}

function updatePromotionalPrice(productId, newPrice) {
    console.log('Updating promotional price for product:', productId, 'New price:', newPrice);
    toast.info('Función pendiente', 'La actualización de precios promocionales estará disponible pronto');
}

// ========== FUNCIONES TIENDANUBE STYLE ==========

function organizeProducts() {
    console.log('Organize products functionality');
    toast.info('Función pendiente', 'La organización de productos estará disponible pronto');
}

function exportProducts() {
    console.log('Export products functionality');
    toast.info('Función pendiente', 'La exportación de productos estará disponible pronto');
}

function toggleFilters() {
    console.log('Toggle filters functionality');
    toast.info('Función pendiente', 'Los filtros avanzados estarán disponibles pronto');
}

function toggleMoreOptions() {
    console.log('Toggle more options functionality');
    toast.info('Función pendiente', 'Más opciones estarán disponibles pronto');
}

// ========== FUNCIONES DE POSTS ==========

function addPost() {
    console.log('Add post functionality');
    toast.info('Función pendiente', 'La creación de posts estará disponible pronto');
}

function editPost(postId) {
    console.log('Edit post functionality:', postId);
    toast.info('Función pendiente', 'La edición de posts estará disponible pronto');
}

function deletePost(postId) {
    if (confirm('¿Estás seguro de que quieres eliminar este post? Esta acción no se puede deshacer.')) {
        console.log('Delete post functionality:', postId);
        toast.info('Función pendiente', 'La eliminación de posts estará disponible pronto');
    }
}

function togglePostStatus(postId) {
    console.log('Toggle post status functionality:', postId);
    toast.info('Función pendiente', 'El cambio de estado de posts estará disponible pronto');
}

// ========== FUNCIONES DE COMENTARIOS ==========

function approveComment(commentId) {
    console.log('Approve comment functionality:', commentId);
    toast.info('Función pendiente', 'La aprobación de comentarios estará disponible pronto');
}

function rejectComment(commentId) {
    console.log('Reject comment functionality:', commentId);
    toast.info('Función pendiente', 'El rechazo de comentarios estará disponible pronto');
}

function deleteComment(commentId) {
    if (confirm('¿Estás seguro de que quieres eliminar este comentario? Esta acción no se puede deshacer.')) {
        console.log('Delete comment functionality:', commentId);
        toast.info('Función pendiente', 'La eliminación de comentarios estará disponible pronto');
    }
}

function replyComment(commentId) {
    console.log('Reply to comment functionality:', commentId);
    toast.info('Función pendiente', 'La respuesta a comentarios estará disponible pronto');
}

// ========== FUNCIONES DE USUARIOS ==========

function addUser() {
    console.log('Add user functionality');
    toast.info('Función pendiente', 'La creación de usuarios estará disponible pronto');
}

function editUser(userId) {
    console.log('Edit user functionality:', userId);
    toast.info('Función pendiente', 'La edición de usuarios estará disponible pronto');
}

function deleteUser(userId) {
    if (confirm('¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.')) {
        console.log('Delete user functionality:', userId);
        toast.info('Función pendiente', 'La eliminación de usuarios estará disponible pronto');
    }
}

function toggleUserStatus(userId) {
    console.log('Toggle user status functionality:', userId);
    toast.info('Función pendiente', 'El cambio de estado de usuarios estará disponible pronto');
}

// ========== FUNCIONES DE CATEGORÍAS ==========

function addCategory() {
    console.log('Add category functionality');
    toast.info('Función pendiente', 'La creación de categorías estará disponible pronto');
}

function editCategory(categoryId) {
    console.log('Edit category functionality:', categoryId);
    toast.info('Función pendiente', 'La edición de categorías estará disponible pronto');
}

function deleteCategory(categoryId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta categoría? Esta acción no se puede deshacer.')) {
        console.log('Delete category functionality:', categoryId);
        toast.info('Función pendiente', 'La eliminación de categorías estará disponible pronto');
    }
}

// ========== FUNCIONES DE DASHBOARD ==========

function refreshDashboard() {
    console.log('Refresh dashboard functionality');
    toast.info('Actualizando', 'Actualizando datos del dashboard');
}

function exportDashboard() {
    console.log('Export dashboard functionality');
    toast.info('Función pendiente', 'La exportación del dashboard estará disponible pronto');
}

// ========== FUNCIONES DE BÚSQUEDA ==========

function updateProductsCounter() {
    const visibleRows = document.querySelectorAll('.tn-product-row:not([style*="display: none"])');
    const counter = document.getElementById('productsCount');
    if (counter) {
        counter.textContent = `${visibleRows.length} productos`;
    }
}

function updateSalesCounter() {
    const visibleRows = document.querySelectorAll('.tn-product-row:not([style*="display: none"])');
    const counter = document.getElementById('salesCount');
    if (counter) {
        counter.textContent = `${visibleRows.length} órdenes`;
    }
}

function updateInventoryCounter() {
    const visibleRows = document.querySelectorAll('.tn-product-row:not([style*="display: none"])');
    const counter = document.getElementById('inventoryCount');
    if (counter) {
        counter.textContent = `${visibleRows.length} productos en inventario`;
    }
}

// Enhanced search functions
function enhancedProductSearch() {
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.tn-product-row');
            
            rows.forEach(row => {
                const productName = row.querySelector('.tn-product-name')?.textContent.toLowerCase() || '';
                const productLink = row.querySelector('.tn-product-link')?.textContent.toLowerCase() || '';
                
                const matches = productName.includes(searchTerm) || productLink.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
            
            updateProductsCounter();
        });
    }
}

function enhancedSalesSearch() {
    const searchInput = document.getElementById('salesSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.tn-product-row');
            
            rows.forEach(row => {
                const orderNumber = row.querySelector('.tn-product-link')?.textContent.toLowerCase() || '';
                const customerName = row.cells[2]?.textContent.toLowerCase() || '';
                
                const matches = orderNumber.includes(searchTerm) || customerName.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
            
            updateSalesCounter();
        });
    }
}

function enhancedInventorySearch() {
    const searchInput = document.getElementById('inventorySearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.tn-product-row');
            
            rows.forEach(row => {
                const productName = row.querySelector('.tn-product-name')?.textContent.toLowerCase() || '';
                const sku = row.cells[3]?.textContent.toLowerCase() || '';
                
                const matches = productName.includes(searchTerm) || sku.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
            
            updateInventoryCounter();
        });
    }
}

// ========== FUNCIONES DE CHECKBOX BULK ==========

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.product-checkbox, .sales-checkbox, .inventory-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked, .sales-checkbox:checked, .inventory-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (bulkActions && selectedCount) {
        if (checkedBoxes.length > 0) {
            bulkActions.style.display = 'flex';
            selectedCount.textContent = `${checkedBoxes.length} seleccionados`;
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

function bulkEdit() {
    console.log('Bulk edit functionality');
    toast.info('Función pendiente', 'La edición masiva estará disponible pronto');
}

function bulkExport() {
    console.log('Bulk export functionality');
    toast.info('Función pendiente', 'La exportación masiva estará disponible pronto');
}

function bulkToggleStatus() {
    console.log('Bulk toggle status functionality');
    toast.info('Función pendiente', 'El cambio de estado masivo estará disponible pronto');
}

function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked, .sales-checkbox:checked, .inventory-checkbox:checked');
    if (checkedBoxes.length > 0 && confirm(`¿Estás seguro de que quieres eliminar ${checkedBoxes.length} elementos? Esta acción no se puede deshacer.`)) {
        console.log('Bulk delete functionality');
        toast.info('Función pendiente', 'La eliminación masiva estará disponible pronto');
    }
}

// ========== FUNCIONES DE DROPDOWNS ==========

function toggleActionDropdown(itemId) {
    const dropdown = document.getElementById(`dropdown-${itemId}`);
    if (dropdown) {
        dropdown.classList.toggle('show');
        
        // Close other dropdowns
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            if (menu !== dropdown) {
                menu.classList.remove('show');
            }
        });
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.action-dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// ========== FUNCIONES DE SIDEBAR ==========

function initializeSidebar() {
    console.log('Initializing sidebar...');
    
    // Wait for DOM to be fully loaded
    setTimeout(() => {
        // Initialize expandable nav items
        const expandableItems = document.querySelectorAll('.nav-item-expandable');
        console.log('Found expandable items:', expandableItems.length);
        
        // Remove any existing event listeners first
        expandableItems.forEach((item, index) => {
            const navExpandable = item.querySelector('.nav-expandable');
            if (navExpandable) {
                // Clone node to remove all event listeners
                const newNavExpandable = navExpandable.cloneNode(true);
                navExpandable.parentNode.replaceChild(newNavExpandable, navExpandable);
            }
        });
        
        // Re-query after cloning
        const freshExpandableItems = document.querySelectorAll('.nav-item-expandable');
        
        freshExpandableItems.forEach((item, index) => {
            const navExpandable = item.querySelector('.nav-expandable');
            const submenu = item.querySelector('.nav-submenu');
            const menuName = item.querySelector('span') ? item.querySelector('span').textContent.trim() : `Menu ${index}`;
            
            console.log(`Setting up menu: ${menuName}`);
            
            if (navExpandable && submenu) {
                // Add event listener with proper error handling
                navExpandable.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    try {
                        // Toggle active state
                        const wasActive = item.classList.contains('active');
                        item.classList.toggle('active');
                        
                        console.log(`Menu "${menuName}" ${wasActive ? 'collapsed' : 'expanded'}`);
                        
                        // Force CSS recalculation
                        submenu.style.display = 'block';
                        void submenu.offsetHeight; // Trigger reflow
                        submenu.style.display = '';
                        
                    } catch (error) {
                        console.error(`Error toggling menu "${menuName}":`, error);
                    }
                });
                
                console.log(`✅ Event listener attached to "${menuName}"`);
            } else {
                console.log(`❌ Menu "${menuName}" missing elements:`, {
                    navExpandable: !!navExpandable,
                    submenu: !!submenu
                });
            }
        });
        
        // Auto-expand active parent menus
        document.querySelectorAll('.nav-subitem.active').forEach(activeSubitem => {
            const parentExpandable = activeSubitem.closest('.nav-item-expandable');
            if (parentExpandable) {
                parentExpandable.classList.add('active');
                console.log('Auto-expanded parent menu for active item:', activeSubitem.textContent.trim());
            }
        });
        
        // Also auto-expand if parent item has active class
        document.querySelectorAll('.nav-item-expandable.active').forEach(activeParent => {
            const menuName = activeParent.querySelector('span') ? activeParent.querySelector('span').textContent.trim() : 'Unknown';
            console.log('Parent menu is active:', menuName);
        });
        
        console.log('✅ Sidebar initialized with expandable functionality');
    }, 100);
}

// ========== INICIALIZACIÓN ==========

// Initialize all enhanced features when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar functionality
    initializeSidebar();
    
    // Initialize search functions
    enhancedProductSearch();
    enhancedSalesSearch();
    enhancedInventorySearch();
    
    // Update counters
    updateProductsCounter();
    updateSalesCounter();
    updateInventoryCounter();
    
    // Initialize admin panel if available
    if (window.adminPanel && typeof window.adminPanel.initialize === 'function') {
        window.adminPanel.initialize();
    }
    
    console.log('Admin functions initialized');
});

// Export functions for global access
window.AdminFunctions = {
    // Productos
    editProduct,
    viewProduct,
    duplicateProduct,
    deleteProduct,
    shareProduct,
    updatePrice,
    updatePromotionalPrice,
    organizeProducts,
    exportProducts,
    toggleFilters,
    toggleMoreOptions,
    // Posts
    addPost,
    editPost,
    deletePost,
    togglePostStatus,
    // Comentarios
    approveComment,
    rejectComment,
    deleteComment,
    replyComment,
    // Usuarios
    addUser,
    editUser,
    deleteUser,
    toggleUserStatus,
    // Categorías
    addCategory,
    editCategory,
    deleteCategory,
    // Dashboard
    refreshDashboard,
    exportDashboard,
    // Sidebar
    initializeSidebar,
    // Funciones generales
    toggleSelectAll,
    updateBulkActions,
    bulkEdit,
    bulkExport,
    bulkToggleStatus,
    bulkDelete,
    toggleActionDropdown,
    updateProductsCounter,
    updateSalesCounter,
    updateInventoryCounter
};