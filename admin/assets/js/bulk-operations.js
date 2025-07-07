/**
 * Sistema de Operaciones Bulk para Admin Panel
 * Permite seleccionar múltiples elementos y realizar acciones en lote
 */

class BulkOperations {
    constructor() {
        this.selectedItems = new Set();
        this.currentTable = null;
        this.bulkActions = {};
        
        this.init();
    }

    /**
     * Inicializar sistema de operaciones bulk
     */
    init() {
        this.setupTableEnhancements();
        this.setupBulkActionsToolbar();
        this.setupKeyboardShortcuts();
        this.registerBulkActions();
    }

    /**
     * Configurar mejoras en tablas para selección múltiple
     */
    setupTableEnhancements() {
        const tables = document.querySelectorAll('.products-table, .data-table');
        
        tables.forEach(table => {
            this.enhanceTable(table);
        });
    }

    /**
     * Mejorar tabla individual con funcionalidad bulk
     */
    enhanceTable(table) {
        this.currentTable = table;
        
        // Agregar checkbox de "seleccionar todo" en header
        this.addSelectAllCheckbox(table);
        
        // Agregar checkboxes individuales a cada fila
        this.addRowCheckboxes(table);
        
        // Configurar eventos de selección
        this.setupSelectionEvents(table);
    }

    /**
     * Agregar checkbox "seleccionar todo"
     */
    addSelectAllCheckbox(table) {
        const thead = table.querySelector('thead tr');
        if (!thead) return;

        const th = document.createElement('th');
        th.className = 'select-column';
        th.innerHTML = `
            <div class="checkbox-container">
                <input type="checkbox" class="select-all-checkbox" id="selectAll">
                <label for="selectAll" class="checkbox-label">
                    <i class="fas fa-check"></i>
                </label>
            </div>
        `;
        
        thead.insertBefore(th, thead.firstChild);
    }

    /**
     * Agregar checkboxes a filas individuales
     */
    addRowCheckboxes(table) {
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach((row, index) => {
            const td = document.createElement('td');
            td.className = 'select-column';
            
            const rowId = this.getRowId(row, index);
            td.innerHTML = `
                <div class="checkbox-container">
                    <input type="checkbox" class="row-checkbox" id="row-${rowId}" data-row-id="${rowId}">
                    <label for="row-${rowId}" class="checkbox-label">
                        <i class="fas fa-check"></i>
                    </label>
                </div>
            `;
            
            row.insertBefore(td, row.firstChild);
        });
    }

    /**
     * Obtener ID único de la fila
     */
    getRowId(row, fallbackIndex) {
        // Intentar obtener ID desde data attributes o enlaces de edición
        const editLink = row.querySelector('a[href*="edit"], [onclick*="edit"]');
        if (editLink) {
            const match = editLink.getAttribute('href')?.match(/[?&]id=(\d+)/) ||
                         editLink.getAttribute('onclick')?.match(/\((\d+)\)/);
            if (match) return match[1];
        }
        
        return fallbackIndex;
    }

    /**
     * Configurar eventos de selección
     */
    setupSelectionEvents(table) {
        // Evento para "seleccionar todo"
        const selectAllCheckbox = table.querySelector('.select-all-checkbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                this.toggleSelectAll(e.target.checked);
            });
        }

        // Eventos para checkboxes individuales
        const rowCheckboxes = table.querySelectorAll('.row-checkbox');
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.toggleRowSelection(e.target.dataset.rowId, e.target.checked);
            });
        });

        // Selección con Shift+Click
        let lastSelectedIndex = -1;
        rowCheckboxes.forEach((checkbox, index) => {
            checkbox.addEventListener('click', (e) => {
                if (e.shiftKey && lastSelectedIndex !== -1) {
                    this.selectRange(lastSelectedIndex, index);
                }
                lastSelectedIndex = index;
            });
        });
    }

    /**
     * Toggle selección de todos los elementos
     */
    toggleSelectAll(select) {
        const rowCheckboxes = this.currentTable.querySelectorAll('.row-checkbox');
        
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = select;
            this.toggleRowSelection(checkbox.dataset.rowId, select);
        });

        this.updateBulkActionsVisibility();
    }

    /**
     * Toggle selección de fila individual
     */
    toggleRowSelection(rowId, select) {
        if (select) {
            this.selectedItems.add(rowId);
        } else {
            this.selectedItems.delete(rowId);
        }

        // Actualizar checkbox "seleccionar todo"
        this.updateSelectAllState();
        
        // Actualizar toolbar de acciones bulk
        this.updateBulkActionsVisibility();
        
        // Highlight visual de fila seleccionada
        this.updateRowHighlight(rowId, select);
    }

    /**
     * Seleccionar rango de elementos
     */
    selectRange(startIndex, endIndex) {
        const start = Math.min(startIndex, endIndex);
        const end = Math.max(startIndex, endIndex);
        
        const rowCheckboxes = this.currentTable.querySelectorAll('.row-checkbox');
        
        for (let i = start; i <= end; i++) {
            if (rowCheckboxes[i]) {
                rowCheckboxes[i].checked = true;
                this.toggleRowSelection(rowCheckboxes[i].dataset.rowId, true);
            }
        }
    }

    /**
     * Actualizar estado del checkbox "seleccionar todo"
     */
    updateSelectAllState() {
        const selectAllCheckbox = this.currentTable.querySelector('.select-all-checkbox');
        const rowCheckboxes = this.currentTable.querySelectorAll('.row-checkbox');
        const checkedBoxes = this.currentTable.querySelectorAll('.row-checkbox:checked');
        
        if (selectAllCheckbox) {
            if (checkedBoxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedBoxes.length === rowCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }
    }

    /**
     * Actualizar highlight visual de filas
     */
    updateRowHighlight(rowId, selected) {
        const checkbox = this.currentTable.querySelector(`[data-row-id="${rowId}"]`);
        if (checkbox) {
            const row = checkbox.closest('tr');
            if (selected) {
                row.classList.add('row-selected');
            } else {
                row.classList.remove('row-selected');
            }
        }
    }

    /**
     * Configurar toolbar de acciones bulk
     */
    setupBulkActionsToolbar() {
        const toolbar = this.createBulkToolbar();
        this.insertToolbar(toolbar);
        this.updateBulkActionsVisibility();
    }

    /**
     * Crear toolbar de acciones bulk
     */
    createBulkToolbar() {
        const toolbar = document.createElement('div');
        toolbar.className = 'bulk-actions-toolbar';
        toolbar.style.display = 'none';
        
        toolbar.innerHTML = `
            <div class="bulk-actions-content">
                <div class="selection-info">
                    <span class="selected-count">0 elementos seleccionados</span>
                    <button class="clear-selection-btn" onclick="bulkOps.clearSelection()">
                        <i class="fas fa-times"></i> Limpiar selección
                    </button>
                </div>
                <div class="bulk-actions-list">
                    <select class="bulk-action-select">
                        <option value="">Seleccionar acción...</option>
                        <option value="delete">🗑️ Eliminar seleccionados</option>
                        <option value="export">📥 Exportar seleccionados</option>
                        <option value="duplicate">📋 Duplicar seleccionados</option>
                        <option value="status">📊 Cambiar estado</option>
                        <option value="category">🏷️ Cambiar categoría</option>
                        <option value="price_update">💰 Actualizar precios</option>
                    </select>
                    <button class="execute-bulk-btn" onclick="bulkOps.executeBulkAction()">
                        <i class="fas fa-play"></i> Ejecutar
                    </button>
                </div>
            </div>
        `;
        
        return toolbar;
    }

    /**
     * Insertar toolbar en la página
     */
    insertToolbar(toolbar) {
        const pageHeader = document.querySelector('.page-header');
        if (pageHeader) {
            pageHeader.appendChild(toolbar);
        } else {
            document.body.insertBefore(toolbar, document.body.firstChild);
        }
    }

    /**
     * Actualizar visibilidad del toolbar
     */
    updateBulkActionsVisibility() {
        const toolbar = document.querySelector('.bulk-actions-toolbar');
        const countElement = toolbar?.querySelector('.selected-count');
        
        if (this.selectedItems.size > 0) {
            toolbar.style.display = 'block';
            if (countElement) {
                countElement.textContent = `${this.selectedItems.size} elemento${this.selectedItems.size !== 1 ? 's' : ''} seleccionado${this.selectedItems.size !== 1 ? 's' : ''}`;
            }
        } else {
            toolbar.style.display = 'none';
        }
    }

    /**
     * Registrar acciones bulk disponibles
     */
    registerBulkActions() {
        this.bulkActions = {
            'delete': {
                name: 'Eliminar elementos',
                icon: 'fas fa-trash',
                action: this.bulkDelete.bind(this),
                confirm: true,
                confirmMessage: '¿Estás seguro de que quieres eliminar los elementos seleccionados?'
            },
            'export': {
                name: 'Exportar elementos',
                icon: 'fas fa-download',
                action: this.bulkExport.bind(this),
                confirm: false
            },
            'duplicate': {
                name: 'Duplicar elementos',
                icon: 'fas fa-copy',
                action: this.bulkDuplicate.bind(this),
                confirm: true,
                confirmMessage: '¿Duplicar los elementos seleccionados?'
            },
            'status': {
                name: 'Cambiar estado',
                icon: 'fas fa-toggle-on',
                action: this.bulkStatusChange.bind(this),
                confirm: false,
                requiresInput: true,
                inputType: 'select',
                inputOptions: [
                    { value: 'published', label: 'Publicado' },
                    { value: 'draft', label: 'Borrador' },
                    { value: 'archived', label: 'Archivado' }
                ]
            },
            'price_update': {
                name: 'Actualizar precios',
                icon: 'fas fa-dollar-sign',
                action: this.bulkPriceUpdate.bind(this),
                confirm: true,
                requiresInput: true,
                inputType: 'number',
                inputLabel: 'Porcentaje de cambio (+/- %)'
            }
        };
    }

    /**
     * Ejecutar acción bulk seleccionada
     */
    async executeBulkAction() {
        const select = document.querySelector('.bulk-action-select');
        const actionKey = select.value;
        
        if (!actionKey || this.selectedItems.size === 0) {
            alert('Selecciona una acción y al menos un elemento');
            return;
        }

        const action = this.bulkActions[actionKey];
        if (!action) return;

        // Confirmar acción si es necesario
        if (action.confirm && !confirm(action.confirmMessage || `¿Ejecutar "${action.name}" en ${this.selectedItems.size} elementos?`)) {
            return;
        }

        // Obtener input adicional si es necesario
        let inputValue = null;
        if (action.requiresInput) {
            inputValue = await this.getActionInput(action);
            if (inputValue === null) return; // Usuario canceló
        }

        // Mostrar loading
        this.showBulkLoading(true);

        try {
            await action.action(Array.from(this.selectedItems), inputValue);
            this.showBulkSuccess(`${action.name} ejecutado exitosamente`);
            this.clearSelection();
            
            // Recargar tabla o página
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            
        } catch (error) {
            console.error('Error en acción bulk:', error);
            this.showBulkError(`Error: ${error.message}`);
        } finally {
            this.showBulkLoading(false);
        }
    }

    /**
     * Obtener input del usuario para acciones que lo requieren
     */
    async getActionInput(action) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'bulk-input-modal';
            
            let inputHTML = '';
            if (action.inputType === 'select') {
                inputHTML = `
                    <select class="bulk-input" required>
                        <option value="">Selecciona una opción...</option>
                        ${action.inputOptions.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')}
                    </select>
                `;
            } else {
                inputHTML = `
                    <input type="${action.inputType}" class="bulk-input" placeholder="${action.inputLabel || 'Ingresa un valor'}" required>
                `;
            }
            
            modal.innerHTML = `
                <div class="bulk-input-content">
                    <h3>${action.name}</h3>
                    <p>Aplicar a ${this.selectedItems.size} elemento${this.selectedItems.size !== 1 ? 's' : ''}:</p>
                    <div class="input-group">
                        <label>${action.inputLabel || 'Valor:'}</label>
                        ${inputHTML}
                    </div>
                    <div class="modal-actions">
                        <button class="btn-cancel" onclick="this.closest('.bulk-input-modal').remove(); resolve(null);">Cancelar</button>
                        <button class="btn-confirm" onclick="
                            const value = this.closest('.bulk-input-content').querySelector('.bulk-input').value;
                            this.closest('.bulk-input-modal').remove();
                            resolve(value);
                        ">Aplicar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Auto-focus en input
            setTimeout(() => {
                modal.querySelector('.bulk-input').focus();
            }, 100);
        });
    }

    /**
     * Implementaciones de acciones bulk específicas
     */
    async bulkDelete(itemIds) {
        // Simular eliminación
        const response = await fetch('bulk_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete',
                ids: itemIds
            })
        });
        
        if (!response.ok) throw new Error('Error en la eliminación');
        return response.json();
    }

    async bulkExport(itemIds) {
        // Simular exportación
        const data = {
            action: 'export',
            ids: itemIds,
            format: 'csv'
        };
        
        const response = await fetch('bulk_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) throw new Error('Error en la exportación');
        
        // Descargar archivo
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `export_${Date.now()}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    async bulkDuplicate(itemIds) {
        const response = await fetch('bulk_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'duplicate',
                ids: itemIds
            })
        });
        
        if (!response.ok) throw new Error('Error en la duplicación');
        return response.json();
    }

    async bulkStatusChange(itemIds, newStatus) {
        const response = await fetch('bulk_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'status_change',
                ids: itemIds,
                status: newStatus
            })
        });
        
        if (!response.ok) throw new Error('Error al cambiar estado');
        return response.json();
    }

    async bulkPriceUpdate(itemIds, percentage) {
        const response = await fetch('bulk_operations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'price_update',
                ids: itemIds,
                percentage: parseFloat(percentage)
            })
        });
        
        if (!response.ok) throw new Error('Error al actualizar precios');
        return response.json();
    }

    /**
     * Utilidades de UI
     */
    showBulkLoading(show) {
        const btn = document.querySelector('.execute-bulk-btn');
        if (show) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play"></i> Ejecutar';
        }
    }

    showBulkSuccess(message) {
        this.showNotification(message, 'success');
    }

    showBulkError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `bulk-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    /**
     * Limpiar selección
     */
    clearSelection() {
        this.selectedItems.clear();
        
        // Desmarcar todos los checkboxes
        const checkboxes = document.querySelectorAll('.row-checkbox, .select-all-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.indeterminate = false;
        });
        
        // Remover highlights
        document.querySelectorAll('.row-selected').forEach(row => {
            row.classList.remove('row-selected');
        });
        
        this.updateBulkActionsVisibility();
    }

    /**
     * Configurar atajos de teclado
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+A para seleccionar todo
            if (e.ctrlKey && e.key === 'a' && !e.target.matches('input, textarea')) {
                e.preventDefault();
                const selectAllCheckbox = document.querySelector('.select-all-checkbox');
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = true;
                    this.toggleSelectAll(true);
                }
            }
            
            // Delete para eliminar seleccionados
            if (e.key === 'Delete' && this.selectedItems.size > 0 && !e.target.matches('input, textarea')) {
                e.preventDefault();
                document.querySelector('.bulk-action-select').value = 'delete';
                this.executeBulkAction();
            }
            
            // Escape para limpiar selección
            if (e.key === 'Escape') {
                this.clearSelection();
            }
        });
    }
}

// CSS para operaciones bulk
const bulkStyles = `
<style>
/* Columna de selección */
.select-column {
    width: 50px;
    text-align: center;
}

.checkbox-container {
    position: relative;
    display: inline-block;
}

.checkbox-container input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkbox-label {
    position: relative;
    display: inline-block;
    width: 20px;
    height: 20px;
    background: var(--admin-bg-secondary);
    border: 2px solid var(--admin-border-light);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.checkbox-label i {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.checkbox-container input:checked + .checkbox-label {
    background: var(--admin-accent-blue);
    border-color: var(--admin-accent-blue);
}

.checkbox-container input:checked + .checkbox-label i {
    opacity: 1;
}

.checkbox-container input:indeterminate + .checkbox-label {
    background: var(--admin-accent-blue);
    border-color: var(--admin-accent-blue);
}

.checkbox-container input:indeterminate + .checkbox-label::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 10px;
    height: 2px;
    background: white;
}

/* Filas seleccionadas */
.row-selected {
    background: rgba(9, 105, 218, 0.1) !important;
    border-left: 3px solid var(--admin-accent-blue) !important;
}

/* Toolbar de acciones bulk */
.bulk-actions-toolbar {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    background: var(--admin-bg-primary);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-lg);
    box-shadow: var(--admin-shadow-xl);
    z-index: 1000;
    min-width: 600px;
    max-width: 90vw;
}

.bulk-actions-content {
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.selection-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.selected-count {
    font-weight: 600;
    color: var(--admin-text-primary);
}

.clear-selection-btn {
    background: transparent;
    border: 1px solid var(--admin-border-light);
    color: var(--admin-text-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: var(--admin-radius-sm);
    cursor: pointer;
    font-size: 0.875rem;
}

.bulk-actions-list {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.bulk-action-select {
    padding: 0.5rem;
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-md);
    background: var(--admin-bg-secondary);
    color: var(--admin-text-primary);
    min-width: 200px;
}

.execute-bulk-btn {
    background: var(--admin-accent-blue);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: var(--admin-radius-md);
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s ease;
}

.execute-bulk-btn:hover:not(:disabled) {
    background: var(--admin-accent-blue-hover);
    transform: translateY(-1px);
}

.execute-bulk-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Modal de input bulk */
.bulk-input-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.bulk-input-content {
    background: var(--admin-bg-primary);
    border-radius: var(--admin-radius-lg);
    padding: 2rem;
    max-width: 400px;
    width: 90vw;
}

.bulk-input-content h3 {
    margin-bottom: 1rem;
    color: var(--admin-text-primary);
}

.input-group {
    margin: 1.5rem 0;
}

.input-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--admin-text-primary);
    font-weight: 500;
}

.bulk-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-md);
    background: var(--admin-bg-secondary);
    color: var(--admin-text-primary);
}

.modal-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.btn-cancel, .btn-confirm {
    padding: 0.5rem 1rem;
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-md);
    cursor: pointer;
    font-weight: 500;
}

.btn-cancel {
    background: var(--admin-bg-secondary);
    color: var(--admin-text-primary);
}

.btn-confirm {
    background: var(--admin-accent-blue);
    color: white;
    border-color: var(--admin-accent-blue);
}

/* Notificaciones bulk */
.bulk-notification {
    position: fixed;
    top: 2rem;
    right: 2rem;
    padding: 1rem 1.5rem;
    border-radius: var(--admin-radius-md);
    box-shadow: var(--admin-shadow-lg);
    z-index: 10001;
    animation: slideInRight 0.3s ease;
}

.bulk-notification.success {
    background: #10b981;
    color: white;
}

.bulk-notification.error {
    background: #ef4444;
    color: white;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Responsive */
@media (max-width: 768px) {
    .bulk-actions-toolbar {
        min-width: auto;
        left: 1rem;
        right: 1rem;
        transform: none;
    }
    
    .bulk-actions-content {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .bulk-actions-list {
        justify-content: stretch;
    }
    
    .bulk-action-select {
        min-width: auto;
        flex: 1;
    }
}
</style>
`;

// Inyectar estilos
document.head.insertAdjacentHTML('beforeend', bulkStyles);

// Inicializar sistema de operaciones bulk
const bulkOps = new BulkOperations();

// Exportar para uso global
window.bulkOps = bulkOps;