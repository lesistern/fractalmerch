/**
 * Modern Admin Panel JavaScript
 * Sistema administrativo avanzado para gestión de productos
 */

class ModernAdminPanel {
    constructor() {
        this.currentStep = 1;
        this.maxSteps = 4;
        this.editingProduct = null;
        this.variantCounter = 0;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeProductForm();
    }

    bindEvents() {
        // Form toggle buttons
        const toggleBtn = document.getElementById('toggleProductForm');
        const closeBtn = document.getElementById('closeFormPanel');
        const cancelBtn = document.getElementById('cancelForm');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.showProductForm());
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.hideProductForm());
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.hideProductForm());
        }

        // Tab navigation
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tabId = e.target.dataset.tab;
                this.switchTab(tabId);
            });
        });

        // Variant management
        const addVariantBtn = document.getElementById('addVariantBtn');
        if (addVariantBtn) {
            addVariantBtn.addEventListener('click', () => this.addVariant());
        }

        // Price calculation
        const priceInput = document.getElementById('price');
        const costInput = document.getElementById('cost');
        if (priceInput) priceInput.addEventListener('input', () => this.calculateProfit());
        if (costInput) costInput.addEventListener('input', () => this.calculateProfit());

        // Image upload
        const mainImageFile = document.getElementById('mainImageFile');
        const mainImagePreview = document.getElementById('mainImagePreview');
        
        if (mainImageFile) {
            mainImageFile.addEventListener('change', (e) => this.handleMainImageUpload(e));
        }
        
        // Make image preview clickeable
        if (mainImagePreview) {
            mainImagePreview.addEventListener('click', () => {
                if (mainImageFile) mainImageFile.click();
            });
        }

        // Search and filter
        const searchInput = document.getElementById('productSearch');
        const categoryFilter = document.getElementById('categoryFilter');
        
        if (searchInput) {
            searchInput.addEventListener('input', () => this.filterProducts());
        }
        if (categoryFilter) {
            categoryFilter.addEventListener('change', () => this.filterProducts());
        }

        // Form submission
        const productForm = document.querySelector('.modern-product-form');
        if (productForm) {
            productForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Close modal on overlay click
        const overlay = document.getElementById('productFormOverlay');
        if (overlay) {
            overlay.addEventListener('click', () => this.hideProductForm());
        }
    }

    initializeProductForm() {
        // Initialize first variant if none exist
        const variantsContainer = document.getElementById('variantsContainer');
        if (variantsContainer && variantsContainer.children.length === 0) {
            this.addVariant();
        }
    }

    showProductForm(productData = null) {
        const panel = document.getElementById('productFormPanel');
        const overlay = document.getElementById('productFormOverlay');
        
        if (panel && overlay) {
            // Show overlay and panel
            overlay.classList.add('show');
            panel.style.display = 'block';
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            
            if (productData) {
                console.log('Editing product:', productData);
                this.editingProduct = productData;
                // Wait a bit for the panel to be visible, then populate
                setTimeout(() => {
                    this.populateForm(productData);
                }, 100);
            } else {
                console.log('Creating new product');
                this.editingProduct = null;
                setTimeout(() => {
                    this.resetForm();
                }, 100);
            }
            
            // Update form title
            const formTitle = document.getElementById('formTitle');
            if (formTitle) {
                formTitle.innerHTML = `
                    <i class="fas fa-${productData ? 'edit' : 'plus-circle'}"></i>
                    ${productData ? 'Editar Producto' : 'Nuevo Producto'}
                `;
            }
            
            // Ensure first tab is active
            setTimeout(() => {
                this.switchTab('basic');
            }, 150);
        }
    }

    hideProductForm() {
        const panel = document.getElementById('productFormPanel');
        const overlay = document.getElementById('productFormOverlay');
        
        if (panel && overlay) {
            // Hide overlay and panel
            overlay.classList.remove('show');
            panel.style.display = 'none';
            
            // Restore body scroll
            document.body.style.overflow = '';
            
            this.resetForm();
            this.editingProduct = null;
        }
    }

    switchTab(tabId) {
        // Remove active class from all tabs and panels
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
        
        // Add active class to selected tab and panel
        const targetBtn = document.querySelector(`[data-tab="${tabId}"]`);
        const targetPanel = document.getElementById(`${tabId}-tab`);
        
        if (targetBtn) targetBtn.classList.add('active');
        if (targetPanel) targetPanel.classList.add('active');
    }

    addVariant() {
        const container = document.getElementById('variantsContainer');
        if (!container) return;

        const index = this.variantCounter++;
        const variantHTML = `
            <div class="variant-card" data-index="${index}">
                <div class="variant-header">
                    <h4>Variante #${index + 1}</h4>
                    <button type="button" class="btn-remove" onclick="window.adminPanel.removeVariant(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="variant-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-ruler"></i>
                                Talle/Tamaño
                            </label>
                            <input type="text" name="variants[${index}][size]" placeholder="S, M, L, XL..." class="form-input">
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-palette"></i>
                                Color
                            </label>
                            <input type="text" name="variants[${index}][color]" placeholder="Rojo, Azul, Verde..." class="form-input">
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-weight"></i>
                                Medida
                            </label>
                            <input type="text" name="variants[${index}][measure]" placeholder="330ml, 500g..." class="form-input">
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-boxes"></i>
                                Stock
                            </label>
                            <input type="number" name="variants[${index}][stock]" value="0" min="0" required class="form-input">
                        </div>
                    </div>
                    <div class="variant-image-upload">
                        <label>
                            <i class="fas fa-image"></i>
                            Imagen de Variante
                        </label>
                        <div class="mini-image-preview" onclick="this.querySelector('input').click()">
                            <input type="file" accept="image/*" style="display: none;" onchange="window.adminPanel.handleVariantImageUpload(this)">
                            <div class="mini-upload-placeholder">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', variantHTML);
        console.log(`Added variant ${index}`);
    }

    removeVariant(button) {
        const variantCards = document.querySelectorAll('.variant-card');
        if (variantCards.length > 1) {
            button.closest('.variant-card').remove();
            this.updateVariantNumbers();
        } else {
            this.showMessage('Debe haber al menos una variante', 'warning');
        }
    }

    updateVariantNumbers() {
        document.querySelectorAll('.variant-card').forEach((card, index) => {
            const header = card.querySelector('h4');
            if (header) {
                header.textContent = `Variante #${index + 1}`;
            }
            card.dataset.index = index;
            
            // Update input names
            card.querySelectorAll('input[name^="variants["]').forEach(input => {
                if (input.name.includes('variants[')) {
                    const field = input.name.split('][')[1].replace(']', '');
                    input.name = `variants[${index}][${field}]`;
                }
            });
        });
    }

    calculateProfit() {
        const priceInput = document.getElementById('price');
        const costInput = document.getElementById('cost');
        const marginEl = document.getElementById('profitMargin');
        const percentageEl = document.getElementById('profitPercentage');
        
        if (!priceInput || !costInput || !marginEl || !percentageEl) return;

        const price = parseFloat(priceInput.value) || 0;
        const cost = parseFloat(costInput.value) || 0;
        
        const margin = price - cost;
        const percentage = cost > 0 ? ((margin / cost) * 100) : 0;
        
        marginEl.textContent = `$${margin.toFixed(2)}`;
        percentageEl.textContent = `${percentage.toFixed(1)}%`;
        
        // Color coding
        marginEl.style.color = margin > 0 ? '#10b981' : '#ef4444';
        percentageEl.style.color = percentage > 0 ? '#10b981' : '#ef4444';
    }

    handleMainImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('mainImagePreview');
            if (preview) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">`;
            }
        };
        reader.readAsDataURL(file);
    }

    handleVariantImageUpload(input) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const placeholder = input.parentElement.querySelector('.mini-upload-placeholder');
            if (placeholder) {
                placeholder.innerHTML = `<img src="${e.target.result}" alt="Variant Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">`;
            }
        };
        reader.readAsDataURL(file);
    }

    addImageSlot() {
        const gallery = document.getElementById('imageGallery');
        const addBtn = gallery?.querySelector('.add-image-btn');
        
        if (!gallery || !addBtn) return;

        const imageSlot = document.createElement('div');
        imageSlot.className = 'image-slot';
        imageSlot.innerHTML = `
            <input type="file" accept="image/*" style="display: none;">
            <div class="image-placeholder">
                <i class="fas fa-camera"></i>
            </div>
            <button type="button" class="remove-image" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        gallery.insertBefore(imageSlot, addBtn);
        
        // Add click event for file selection
        const fileInput = imageSlot.querySelector('input[type="file"]');
        fileInput.addEventListener('change', (e) => {
            if (e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const placeholder = imageSlot.querySelector('.image-placeholder');
                    placeholder.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
        
        imageSlot.addEventListener('click', () => {
            if (!imageSlot.querySelector('img')) {
                fileInput.click();
            }
        });
    }

    populateForm(productData) {
        console.log('Populating form with data:', productData);
        
        // Populate basic fields
        const fields = ['name', 'description', 'price', 'cost', 'sku', 'main_image_url', 'category_id'];
        fields.forEach(field => {
            const input = document.getElementById(field);
            if (input && productData[field] !== undefined && productData[field] !== null) {
                input.value = productData[field];
                console.log(`Set ${field} to:`, productData[field]);
            }
        });

        // Add hidden product ID field
        let productIdInput = document.querySelector('input[name="product_id"]');
        if (!productIdInput) {
            productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'product_id';
            const form = document.querySelector('.modern-product-form');
            if (form) {
                form.prepend(productIdInput);
            }
        }
        productIdInput.value = productData.id;
        console.log('Set product_id to:', productData.id);

        // Populate main image preview
        if (productData.main_image_url) {
            const preview = document.getElementById('mainImagePreview');
            if (preview) {
                preview.innerHTML = `<img src="${productData.main_image_url}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">`;
            }
        }

        // Clear existing variants and populate with product variants
        const container = document.getElementById('variantsContainer');
        if (container) {
            container.innerHTML = '';
            this.variantCounter = 0;

            if (productData.variants && productData.variants.length > 0) {
                console.log('Adding variants:', productData.variants);
                productData.variants.forEach((variant, index) => {
                    this.addVariant();
                    
                    // Wait a bit for the variant to be added to DOM
                    setTimeout(() => {
                        const variantCards = container.querySelectorAll('.variant-card');
                        const variantCard = variantCards[index];
                        
                        if (variantCard) {
                            // Populate variant fields using the actual index
                            const sizeInput = variantCard.querySelector(`input[name*="[size]"]`);
                            const colorInput = variantCard.querySelector(`input[name*="[color]"]`);
                            const measureInput = variantCard.querySelector(`input[name*="[measure]"]`);
                            const stockInput = variantCard.querySelector(`input[name*="[stock]"]`);
                            
                            if (sizeInput) sizeInput.value = variant.size || '';
                            if (colorInput) colorInput.value = variant.color || '';
                            if (measureInput) measureInput.value = variant.measure || '';
                            if (stockInput) stockInput.value = variant.stock || 0;
                            
                            console.log(`Populated variant ${index}:`, variant);
                        } else {
                            console.error(`Could not find variant card for index ${index}`);
                        }
                    }, 50 * index); // Stagger the population
                });
            } else {
                console.log('No variants found, adding default variant');
                this.addVariant(); // Add at least one variant
            }
        }

        // Calculate profit after a delay to ensure fields are populated
        setTimeout(() => {
            this.calculateProfit();
        }, 200);
    }

    resetForm() {
        const form = document.querySelector('.modern-product-form');
        if (form) {
            form.reset();
            
            // Remove product ID if exists
            const productIdInput = form.querySelector('input[name="product_id"]');
            if (productIdInput) {
                productIdInput.remove();
            }
        }

        // Reset image preview
        const preview = document.getElementById('mainImagePreview');
        if (preview) {
            preview.innerHTML = `
                <div class="upload-placeholder">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Arrastra o haz clic para subir</p>
                    <small>JPG, PNG o SVG</small>
                </div>
            `;
        }

        // Reset variants
        const container = document.getElementById('variantsContainer');
        if (container) {
            container.innerHTML = '';
            this.variantCounter = 0;
            this.addVariant(); // Add one default variant
        }

        // Reset profit display
        const marginEl = document.getElementById('profitMargin');
        const percentageEl = document.getElementById('profitPercentage');
        if (marginEl) marginEl.textContent = '$0.00';
        if (percentageEl) percentageEl.textContent = '0%';

        // Switch to first tab
        this.switchTab('basic');
    }

    filterProducts() {
        const searchTerm = document.getElementById('productSearch')?.value.toLowerCase() || '';
        const categoryFilter = document.getElementById('categoryFilter')?.value || '';
        const productCards = document.querySelectorAll('.product-card');
        
        productCards.forEach(card => {
            const productName = card.querySelector('h3')?.textContent.toLowerCase() || '';
            const productSku = card.querySelector('.product-sku')?.textContent.toLowerCase() || '';
            
            const matchesSearch = !searchTerm || 
                productName.includes(searchTerm) || 
                productSku.includes(searchTerm);
            
            // For category filtering, you'd need to add data-category attribute to cards
            const matchesCategory = !categoryFilter; // Simplified for now
            
            card.style.display = matchesSearch && matchesCategory ? 'block' : 'none';
        });
    }

    handleFormSubmit(e) {
        if (!this.validateForm()) {
            e.preventDefault();
            return false;
        }
        
        // Form is valid, let it submit normally
        this.showMessage('Guardando producto...', 'info');
    }

    validateForm() {
        const requiredFields = document.querySelectorAll('.modern-product-form [required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        });
        
        if (!isValid) {
            this.showMessage('Por favor completa todos los campos requeridos', 'error');
        }
        
        return isValid;
    }

    showMessage(message, type = 'info') {
        const messageEl = document.createElement('div');
        messageEl.className = `admin-message ${type}`;
        messageEl.innerHTML = `
            <div class="message-content">
                <i class="fas fa-${this.getMessageIcon(type)}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        document.body.appendChild(messageEl);
        
        setTimeout(() => {
            messageEl.remove();
        }, 5000);
    }

    getMessageIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// Toast Notification System
class ToastManager {
    constructor() {
        this.container = document.getElementById('toastContainer');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toastContainer';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    }

    show(message, type = 'info', title = '', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        toast.innerHTML = `
            <i class="toast-icon ${icons[type]}"></i>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : ''}
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        this.container.appendChild(toast);

        if (duration > 0) {
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, duration);
        }

        return toast;
    }

    success(message, title = 'Éxito') {
        return this.show(message, 'success', title);
    }

    error(message, title = 'Error') {
        return this.show(message, 'error', title);
    }

    warning(message, title = 'Advertencia') {
        return this.show(message, 'warning', title);
    }

    info(message, title = 'Información') {
        return this.show(message, 'info', title);
    }
}

// Initialize Toast Manager
window.toast = new ToastManager();

// Bulk Selection Management
let selectedProducts = new Set();

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedProducts.clear();
    checkboxes.forEach(cb => selectedProducts.add(parseInt(cb.value)));
    
    if (selectedProducts.size > 0) {
        bulkActions.style.display = 'flex';
        selectedCount.textContent = `${selectedProducts.size} seleccionado${selectedProducts.size > 1 ? 's' : ''}`;
    } else {
        bulkActions.style.display = 'none';
    }
}

function toggleSelectAll(checkbox) {
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    productCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

// Action Dropdown Management
function toggleActionDropdown(productId) {
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu.id !== `dropdown-${productId}`) {
            menu.classList.remove('show');
        }
    });
    
    const dropdown = document.getElementById(`dropdown-${productId}`);
    dropdown.classList.toggle('show');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Individual Product Actions
function viewProduct(productId) {
    window.open(`../product-detail.php?id=${productId}`, '_blank');
    toast.info('Abriendo vista del producto en nueva pestaña');
}

function duplicateProduct(productId) {
    if (!confirm('¿Estás seguro de que quieres duplicar este producto?')) {
        return;
    }
    
    const btn = event.target.closest('button');
    btn.classList.add('btn-loading');
    
    fetch(`manage-products.php?action=duplicate&id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toast.success('Producto duplicado exitosamente');
                setTimeout(() => location.reload(), 1000);
            } else {
                toast.error(data.message || 'Error al duplicar el producto');
            }
        })
        .catch(error => {
            toast.error('Error de conexión al duplicar producto');
            console.error('Error:', error);
        })
        .finally(() => {
            btn.classList.remove('btn-loading');
        });
}

function toggleProductStatus(productId, isActive) {
    const action = isActive ? 'deactivate' : 'activate';
    const btn = event.target.closest('button');
    btn.classList.add('btn-loading');
    
    fetch(`manage-products.php?action=${action}&id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toast.success(`Producto ${isActive ? 'desactivado' : 'activado'} exitosamente`);
                setTimeout(() => location.reload(), 1000);
            } else {
                toast.error(data.message || 'Error al cambiar estado del producto');
            }
        })
        .catch(error => {
            toast.error('Error de conexión al cambiar estado');
            console.error('Error:', error);
        })
        .finally(() => {
            btn.classList.remove('btn-loading');
        });
}

function exportProduct(productId) {
    window.location.href = `manage-products.php?action=export&id=${productId}`;
    toast.info('Descargando datos del producto...');
}

function addToCollection(productId) {
    // This would open a modal to select collections
    toast.info('Funcionalidad de colecciones en desarrollo');
}

function viewAnalytics(productId) {
    // This would show analytics for the product
    toast.info('Vista de analíticas en desarrollo');
}

// Bulk Actions
function bulkEdit() {
    if (selectedProducts.size === 0) return;
    
    toast.warning('Editor masivo en desarrollo');
}

function bulkExport() {
    if (selectedProducts.size === 0) return;
    
    const productIds = Array.from(selectedProducts).join(',');
    window.location.href = `manage-products.php?action=bulk_export&ids=${productIds}`;
    toast.info(`Exportando ${selectedProducts.size} productos...`);
}

function bulkToggleStatus() {
    if (selectedProducts.size === 0) return;
    
    if (!confirm(`¿Cambiar el estado de ${selectedProducts.size} productos?`)) {
        return;
    }
    
    const productIds = Array.from(selectedProducts).join(',');
    
    fetch('manage-products.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=bulk_toggle_status&ids=${productIds}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toast.success(`Estado cambiado para ${selectedProducts.size} productos`);
            setTimeout(() => location.reload(), 1000);
        } else {
            toast.error(data.message || 'Error al cambiar estados');
        }
    })
    .catch(error => {
        toast.error('Error de conexión en operación masiva');
        console.error('Error:', error);
    });
}

function bulkDelete() {
    if (selectedProducts.size === 0) return;
    
    if (!confirm(`⚠️ ¿Estás seguro de eliminar ${selectedProducts.size} productos? Esta acción no se puede deshacer.`)) {
        return;
    }
    
    const productIds = Array.from(selectedProducts).join(',');
    
    fetch('manage-products.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=bulk_delete&ids=${productIds}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toast.success(`${selectedProducts.size} productos eliminados exitosamente`);
            setTimeout(() => location.reload(), 1000);
        } else {
            toast.error(data.message || 'Error al eliminar productos');
        }
    })
    .catch(error => {
        toast.error('Error de conexión en eliminación masiva');
        console.error('Error:', error);
    });
}

// Enhanced Search and Filtering
function initializeFilters() {
    const searchInput = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterProducts();
            }, 300);
        });
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterProducts);
    }
}

function filterProducts() {
    const searchTerm = document.getElementById('productSearch')?.value.toLowerCase() || '';
    const categoryId = document.getElementById('categoryFilter')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    
    const rows = document.querySelectorAll('.products-table tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const productName = row.querySelector('.product-name-cell')?.textContent.toLowerCase() || '';
        const productSku = row.querySelector('.product-sku-cell')?.textContent.toLowerCase() || '';
        const productStatus = row.querySelector('td:nth-child(7)')?.textContent.toLowerCase() || '';
        
        const matchesSearch = !searchTerm || 
            productName.includes(searchTerm) || 
            productSku.includes(searchTerm);
        
        const matchesCategory = !categoryId; // This would need category data in the row
        
        const matchesStatus = !status || 
            (status === 'active' && productStatus.includes('activo')) ||
            (status === 'inactive' && productStatus.includes('sin stock'));
        
        if (matchesSearch && matchesCategory && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update results count
    const resultsInfo = document.querySelector('.results-info');
    if (resultsInfo) {
        resultsInfo.textContent = `Mostrando ${visibleCount} productos`;
    }
}

// View Toggle (Table/Grid)
function initializeViewToggle() {
    const viewButtons = document.querySelectorAll('.view-btn');
    
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            viewButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const view = this.dataset.view;
            // Switch between table and grid view
            toast.info(`Vista ${view === 'table' ? 'tabla' : 'grid'} activada`);
        });
    });
}

// Global functions for compatibility
function editProduct(productId) {
    // Show loading indicator
    if (window.adminPanel) {
        window.adminPanel.showMessage('Cargando producto...', 'info');
    }
    
    // Fetch product data via AJAX and show the form
    fetch(`manage-products.php?action=get_product&id=${productId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(productData => {
            if (productData.success) {
                console.log('Successfully loaded product:', productData.product);
                if (window.adminPanel) {
                    window.adminPanel.showProductForm(productData.product);
                } else {
                    console.error('AdminPanel not available');
                    alert('Error: El panel de administración no está disponible.');
                }
            } else {
                console.error('Error loading product:', productData.message);
                alert('Error al cargar el producto: ' + productData.message);
            }
        })
        .catch(error => {
            console.error('Error fetching product:', error);
            alert('Error al cargar el producto. Por favor, intenta de nuevo.');
        });
}

function deleteProduct(productId) {
    if (confirm('¿Estás seguro de que quieres eliminar este producto y todas sus variantes?')) {
        window.location.href = `manage-products.php?delete=${productId}`;
    }
}

function addImageSlot() {
    if (window.adminPanel) {
        window.adminPanel.addImageSlot();
    }
}

function removeVariant(button) {
    if (window.adminPanel) {
        window.adminPanel.removeVariant(button);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.adminPanel = new ModernAdminPanel();
    
    // Debug: Add test button if in development
    if (window.location.hostname === 'localhost') {
        console.log('Modern Admin Panel initialized successfully');
        console.log('editProduct function available:', typeof editProduct === 'function');
        console.log('AdminPanel instance:', window.adminPanel);
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernAdminPanel;
}