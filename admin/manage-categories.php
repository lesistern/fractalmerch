<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    flash_message('error', 'No tienes permisos para gestionar categorías');
    redirect('../index.php');
}

// Procesar acciones
if ($_POST) {
    if (isset($_POST['create_category'])) {
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            if ($stmt->execute([$name, $description])) {
                flash_message('success', 'Categoría creada exitosamente');
            } else {
                flash_message('error', 'Error al crear la categoría');
            }
        } else {
            flash_message('error', 'El nombre de la categoría es requerido');
        }
    }
    
    if (isset($_POST['edit_category'])) {
        $id = (int)$_POST['category_id'];
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        
        if (!empty($name)) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $id])) {
                flash_message('success', 'Categoría actualizada exitosamente');
            } else {
                flash_message('error', 'Error al actualizar la categoría');
            }
        } else {
            flash_message('error', 'El nombre de la categoría es requerido');
        }
    }
    
    redirect('manage-categories.php');
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Verificar si hay posts con esta categoría
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
    $stmt->execute([$id]);
    $post_count = $stmt->fetchColumn();
    
    if ($post_count > 0) {
        flash_message('error', "No se puede eliminar la categoría porque tiene $post_count posts asociados");
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            flash_message('success', 'Categoría eliminada exitosamente');
        } else {
            flash_message('error', 'Error al eliminar la categoría');
        }
    }
    
    redirect('manage-categories.php');
}

// Obtener categorías con conteo de posts
$stmt = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM posts WHERE category_id = c.id) as post_count
    FROM categories c 
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();

// Obtener categoría para editar
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_category = $stmt->fetch();
}

$page_title = '📂 Gestionar Categorías - Panel Admin';
include 'admin-dashboard-header.php';
?>

<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

<div class="modern-admin-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="modern-admin-main">
        <div class="tiendanube-header">
            <div class="header-left">
                <h1><i class="fas fa-tags"></i> Categorías</h1>
                <p class="header-subtitle">Organiza el contenido con categorías temáticas</p>
            </div>
            <div class="header-actions">
                <button onclick="toggleCategoryForm()" class="tn-btn tn-btn-primary">
                    <i class="fas fa-plus"></i> Nueva categoría
                </button>
                <button onclick="exportCategories()" class="tn-btn tn-btn-secondary">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>
        </div>

        <!-- Formulario de categoría (inicialmente oculto) -->
        <section class="tn-card category-form-section" id="categoryForm" style="<?php echo $edit_category ? 'display: block;' : 'display: none;'; ?>">
            <div class="tn-card-header">
                <h2><?php echo $edit_category ? 'Editar categoría' : 'Nueva categoría'; ?></h2>
                <button onclick="closeCategoryForm()" class="tn-btn-ghost">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="" class="tn-form">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>
                
                <div class="tn-form-grid">
                    <div class="tn-form-group">
                        <label for="name" class="tn-label">Nombre de la categoría *</label>
                        <input type="text" id="name" name="name" class="tn-input" required 
                               value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>"
                               placeholder="Ej: Tecnología, Lifestyle, Negocios">
                    </div>
                    
                    <div class="tn-form-group full-width">
                        <label for="description" class="tn-label">Descripción</label>
                        <textarea id="description" name="description" class="tn-textarea" rows="3"
                                  placeholder="Describe brevemente esta categoría..."><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="tn-form-actions">
                    <button type="submit" name="<?php echo $edit_category ? 'edit_category' : 'create_category'; ?>" 
                            class="tn-btn tn-btn-primary">
                        <i class="fas fa-save"></i>
                        <?php echo $edit_category ? 'Actualizar categoría' : 'Crear categoría'; ?>
                    </button>
                    
                    <?php if ($edit_category): ?>
                        <a href="manage-categories.php" class="tn-btn tn-btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- Lista de categorías -->
        <section class="tn-card">
            <div class="tn-card-header">
                <div class="header-left">
                    <h2>Categorías existentes</h2>
                    <span class="tn-badge tn-badge-neutral"><?php echo count($categories); ?> categorías</span>
                </div>
                <div class="tn-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar categorías..." id="categorySearch">
                </div>
            </div>

            <?php if (empty($categories)): ?>
                <div class="tn-empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3>No hay categorías</h3>
                    <p>Crea tu primera categoría para organizar el contenido</p>
                    <button onclick="toggleCategoryForm()" class="tn-btn tn-btn-primary">
                        <i class="fas fa-plus"></i> Crear categoría
                    </button>
                </div>
            <?php else: ?>
                <div class="tn-table-container">
                    <table class="tn-table">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th>Posts</th>
                                <th>Creada</th>
                                <th class="tn-table-actions">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesTableBody">
                            <?php foreach ($categories as $category): ?>
                                <tr class="tn-table-row">
                                    <td>
                                        <div class="tn-table-cell-content">
                                            <div class="category-info">
                                                <strong class="category-name"><?php echo htmlspecialchars($category['name']); ?></strong>
                                                <span class="category-id">#<?php echo $category['id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-description">
                                            <?php echo htmlspecialchars($category['description']) ?: '<em class="text-muted">Sin descripción</em>'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="tn-metric">
                                            <span class="metric-value"><?php echo $category['post_count']; ?></span>
                                            <span class="metric-label">posts</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="tn-date">
                                            <?php echo date('d M Y', strtotime($category['created_at'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="tn-action-group">
                                            <a href="?edit=<?php echo $category['id']; ?>" 
                                               class="tn-btn-action" title="Editar categoría">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($category['post_count'] == 0): ?>
                                                <button onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>')"
                                                        class="tn-btn-action tn-btn-danger" title="Eliminar categoría">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="tn-btn-action disabled" title="No se puede eliminar: tiene posts asociados">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<script>
// Funciones de la página
function toggleCategoryForm() {
    const form = document.getElementById('categoryForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
        document.getElementById('name').focus();
    } else {
        form.style.display = 'none';
    }
}

function closeCategoryForm() {
    document.getElementById('categoryForm').style.display = 'none';
    // Clear form if it's not an edit
    <?php if (!$edit_category): ?>
    document.getElementById('name').value = '';
    document.getElementById('description').value = '';
    <?php endif; ?>
}

function deleteCategory(id, name) {
    if (confirm(`¿Estás seguro de que quieres eliminar la categoría "${name}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `?delete=${id}`;
    }
}

function exportCategories() {
    // Implementar exportación
    console.log('Export categories functionality');
    toast.info('Función pendiente', 'La exportación estará disponible pronto');
}

// Búsqueda de categorías
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('categorySearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#categoriesTableBody .tn-table-row');
            
            rows.forEach(row => {
                const categoryName = row.querySelector('.category-name')?.textContent.toLowerCase() || '';
                const categoryDesc = row.querySelector('.category-description')?.textContent.toLowerCase() || '';
                
                const matches = categoryName.includes(searchTerm) || categoryDesc.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
        });
    }
});

// Auto-expandir formulario si hay categoría para editar
<?php if ($edit_category): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('categoryForm').style.display = 'block';
    document.getElementById('name').focus();
});
<?php endif; ?>
</script>

<style>
/* Estilos específicos para gestión de categorías */
.category-form-section {
    margin-bottom: 2rem;
}

.tn-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.tn-form-group.full-width {
    grid-column: 1 / -1;
}

.category-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.category-name {
    font-weight: 600;
    color: var(--tn-text-primary);
}

.category-id {
    font-size: 0.75rem;
    color: var(--tn-text-muted);
    font-weight: 400;
}

.category-description {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.tn-metric {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.metric-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--tn-primary);
}

.metric-label {
    font-size: 0.75rem;
    color: var(--tn-text-muted);
}

.tn-btn-action.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* Responsive */
@media (max-width: 768px) {
    .tn-form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .tiendanube-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .header-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .category-description {
        max-width: 200px;
    }
}

/* Optimización compacta */
.modern-admin-main { padding: 1.5rem !important; }
.tiendanube-header { padding: 1rem 1.5rem !important; }
.tn-card { padding: 1.5rem !important; }
.tn-form-actions { margin-top: 1.5rem !important; }
</style>

</body>
</html>