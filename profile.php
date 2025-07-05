<?php
require_once 'includes/functions.php';

if (!is_logged_in()) {
    flash_message('error', 'Debes iniciar sesión');
    redirect('login.php');
}

$user = get_user_by_id($_SESSION['user_id']);

// Obtener estadísticas reales del usuario
global $pdo;

// Estadísticas de pedidos
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total_amount), 0) as total_spent,
        COALESCE(SUM(discount_amount), 0) as total_saved,
        SUM(CASE WHEN status IN ('pending', 'confirmed', 'processing', 'shipped') THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed_orders
    FROM orders 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user_stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'total_orders' => 0,
    'total_spent' => 0,
    'total_saved' => 0,
    'pending_orders' => 0,
    'completed_orders' => 0
];

// Obtener cantidad de favoritos
$stmt = $pdo->prepare("SELECT COUNT(*) as favorite_products FROM user_favorites WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$favorites_count = $stmt->fetch(PDO::FETCH_ASSOC);
$user_stats['favorite_products'] = $favorites_count['favorite_products'];

// Obtener pedidos recientes
$stmt = $pdo->prepare("
    SELECT 
        o.*,
        COUNT(oi.id) as items_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener direcciones del usuario
$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener favoritos recientes
$stmt = $pdo->prepare("
    SELECT 
        p.id, p.name, p.price, p.main_image_url,
        uf.created_at as favorited_at
    FROM user_favorites uf
    JOIN products p ON uf.product_id = p.id
    WHERE uf.user_id = ?
    ORDER BY uf.created_at DESC
    LIMIT 6
");
$stmt->execute([$_SESSION['user_id']]);
$favorite_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener notificaciones no leídas
$stmt = $pdo->prepare("
    SELECT COUNT(*) as unread_count 
    FROM user_notifications 
    WHERE user_id = ? AND is_read = FALSE
");
$stmt->execute([$_SESSION['user_id']]);
$unread_notifications = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

if ($_POST) {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $bio = sanitize_input($_POST['bio']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    if (empty($username) || empty($email)) {
        $errors[] = 'Nombre de usuario y email son requeridos';
    }
    
    // Verificar si el username/email ya están en uso por otro usuario
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = 'El nombre de usuario o email ya están en uso';
    }
    
    // Si se quiere cambiar contraseña
    if (!empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = 'La contraseña actual es incorrecta';
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'Las contraseñas nuevas no coinciden';
        }
    }
    
    if (empty($errors)) {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, bio = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([$username, $email, $bio, $hashed_password, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, bio = ? WHERE id = ?");
            $result = $stmt->execute([$username, $email, $bio, $_SESSION['user_id']]);
        }
        
        if ($result) {
            $_SESSION['username'] = $username;
            flash_message('success', 'Perfil actualizado exitosamente');
            redirect('profile.php');
        } else {
            flash_message('error', 'Error al actualizar el perfil');
        }
    } else {
        foreach ($errors as $error) {
            flash_message('error', $error);
        }
    }
}

// Obtener posts del usuario
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_posts = $stmt->fetchAll();

$page_title = 'Mi Cuenta - FractalMerch';
include 'includes/header.php';

// Función para formatear precios
function format_price($price) {
    return '$' . number_format($price, 0, ',', '.');
}

// Función para status badges
function get_status_badge($status) {
    $badges = [
        'pending' => ['Pendiente', 'warning'],
        'confirmed' => ['Confirmado', 'info'],
        'processing' => ['Procesando', 'primary'],
        'shipped' => ['Enviado', 'success'],
        'delivered' => ['Entregado', 'success'],
        'cancelled' => ['Cancelado', 'danger']
    ];
    
    $badge = $badges[$status] ?? ['Desconocido', 'secondary'];
    return "<span class='status-badge status-{$badge[1]}'>{$badge[0]}</span>";
}
?>

<!-- CSS específico para el perfil moderno -->
<style>
.modern-profile {
    background: var(--bg-primary);
    min-height: calc(100vh - 200px);
}

.profile-header {
    background: linear-gradient(135deg, var(--ecommerce-primary), var(--ecommerce-secondary));
    color: white;
    padding: 2rem;
    border-radius: 0 0 20px 20px;
    margin-bottom: 2rem;
}

.profile-welcome {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.3);
    object-fit: cover;
}

.profile-avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.profile-info h1 {
    margin: 0 0 0.5rem 0;
    font-size: 1.8rem;
}

.profile-meta {
    opacity: 0.9;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.profile-stats-header {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin: 0 2rem 2rem 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: var(--ecommerce-shadow);
    text-align: center;
    border-left: 4px solid var(--ecommerce-primary);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: var(--ecommerce-primary);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.profile-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.profile-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid var(--border-color);
    overflow-x: auto;
}

.tab-btn {
    background: none;
    border: none;
    padding: 1rem 1.5rem;
    cursor: pointer;
    font-weight: 500;
    color: var(--text-secondary);
    border-radius: 10px 10px 0 0;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.tab-btn.active {
    background: var(--ecommerce-primary);
    color: white;
}

.tab-btn:hover:not(.active) {
    background: var(--bg-tertiary);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.orders-grid {
    display: grid;
    gap: 1rem;
}

.order-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: var(--ecommerce-shadow);
    transition: transform 0.2s ease;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--ecommerce-shadow-hover);
}

.order-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.order-number {
    font-weight: bold;
    color: var(--ecommerce-primary);
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-success { background: #d4edda; color: #155724; }
.status-warning { background: #fff3cd; color: #856404; }
.status-info { background: #d1ecf1; color: #0c5460; }
.status-primary { background: #cce7ff; color: #004085; }
.status-danger { background: #f8d7da; color: #721c24; }

.favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.favorite-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--ecommerce-shadow);
    transition: transform 0.2s ease;
}

.favorite-card:hover {
    transform: translateY(-5px);
}

.favorite-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.favorite-info {
    padding: 1rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.btn-primary-outline {
    background: transparent;
    border: 2px solid var(--ecommerce-primary);
    color: var(--ecommerce-primary);
    padding: 0.7rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-primary-outline:hover {
    background: var(--ecommerce-primary);
    color: white;
}

@media (max-width: 768px) {
    .profile-header {
        padding: 1rem;
    }
    
    .profile-welcome {
        text-align: center;
        flex-direction: column;
    }
    
    .profile-stats-header {
        grid-template-columns: 1fr;
        margin: 0 1rem 2rem 1rem;
    }
    
    .profile-content {
        padding: 0 1rem;
    }
}
</style>

<div class="modern-profile">
    <!-- Header del perfil -->
    <div class="profile-header">
        <div class="profile-welcome">
            <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar-placeholder">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
            
            <div class="profile-info">
                <h1>¡Hola, <?php echo htmlspecialchars($user['name'] ?? $user['username']); ?>!</h1>
                <div class="profile-meta">
                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                    <span><i class="fas fa-calendar"></i> Miembro desde <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                    <?php if ($user['account_type'] === 'oauth'): ?>
                        <span><i class="fab fa-<?php echo $user['oauth_provider']; ?>"></i> Conectado via <?php echo ucfirst($user['oauth_provider']); ?></span>
                    <?php endif; ?>
                    <?php if ($unread_notifications > 0): ?>
                        <span><i class="fas fa-bell"></i> <?php echo $unread_notifications; ?> notificaciones</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas del usuario -->
    <div class="profile-stats-header">
        <div class="stat-card">
            <div class="stat-number"><?php echo $user_stats['total_orders']; ?></div>
            <div class="stat-label">Pedidos Realizados</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo format_price($user_stats['total_spent']); ?></div>
            <div class="stat-label">Total Gastado</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $user_stats['favorite_products']; ?></div>
            <div class="stat-label">Productos Favoritos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $user['loyalty_points'] ?? 0; ?></div>
            <div class="stat-label">Puntos de Fidelidad</div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="profile-content">
        <!-- Pestañas -->
        <div class="profile-tabs">
            <button class="tab-btn active" onclick="showTab('orders')">
                <i class="fas fa-shopping-bag"></i> Mis Pedidos
            </button>
            <button class="tab-btn" onclick="showTab('favorites')">
                <i class="fas fa-heart"></i> Favoritos
            </button>
            <button class="tab-btn" onclick="showTab('addresses')">
                <i class="fas fa-map-marker-alt"></i> Direcciones
            </button>
            <button class="tab-btn" onclick="showTab('settings')">
                <i class="fas fa-cog"></i> Configuración
            </button>
        </div>

        <!-- Pestaña de Pedidos -->
        <div id="orders-tab" class="tab-content active">
            <div class="orders-grid">
                <?php if (empty($recent_orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>No tienes pedidos aún</h3>
                        <p>¡Explora nuestros productos y realiza tu primera compra!</p>
                        <a href="particulares.php" class="btn-primary-outline">
                            <i class="fas fa-shopping-cart"></i> Ir a la Tienda
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <div class="order-number">Pedido #<?php echo htmlspecialchars($order['order_number']); ?></div>
                                    <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </div>
                                </div>
                                <div>
                                    <?php echo get_status_badge($order['status']); ?>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                                <div>
                                    <strong><?php echo format_price($order['total_amount']); ?></strong>
                                    <span style="color: var(--text-secondary); margin-left: 0.5rem;">
                                        <?php echo $order['items_count']; ?> artículo(s)
                                    </span>
                                </div>
                                
                                <?php if (!empty($order['tracking_number'])): ?>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                        Tracking: <?php echo htmlspecialchars($order['tracking_number']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pestaña de Favoritos -->
        <div id="favorites-tab" class="tab-content">
            <?php if (empty($favorite_products)): ?>
                <div class="empty-state">
                    <i class="fas fa-heart"></i>
                    <h3>No tienes productos favoritos</h3>
                    <p>Agrega productos a tus favoritos para encontrarlos fácilmente después.</p>
                    <a href="particulares.php" class="btn-primary-outline">
                        <i class="fas fa-search"></i> Explorar Productos
                    </a>
                </div>
            <?php else: ?>
                <div class="favorites-grid">
                    <?php foreach ($favorite_products as $product): ?>
                        <div class="favorite-card">
                            <?php if ($product['main_image_url']): ?>
                                <img src="<?php echo htmlspecialchars($product['main_image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="favorite-image">
                            <?php else: ?>
                                <div class="favorite-image" style="background: var(--bg-tertiary); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image" style="font-size: 2rem; color: var(--text-secondary);"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="favorite-info">
                                <h4 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($product['name']); ?></h4>
                                <div style="color: var(--ecommerce-primary); font-weight: bold; font-size: 1.1rem;">
                                    <?php echo format_price($product['price']); ?>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn-primary-outline" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                        Ver Producto
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pestaña de Direcciones -->
        <div id="addresses-tab" class="tab-content">
            <?php if (empty($user_addresses)): ?>
                <div class="empty-state">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>No tienes direcciones guardadas</h3>
                    <p>Agrega direcciones para agilizar el proceso de compra.</p>
                    <button class="btn-primary-outline" onclick="alert('Función próximamente')">
                        <i class="fas fa-plus"></i> Agregar Dirección
                    </button>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($user_addresses as $address): ?>
                        <div class="order-card">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <h4 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($address['name']); ?></h4>
                                    <div style="color: var(--text-secondary);">
                                        <?php echo htmlspecialchars($address['address_line_1']); ?><br>
                                        <?php if ($address['address_line_2']): ?>
                                            <?php echo htmlspecialchars($address['address_line_2']); ?><br>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state']); ?><br>
                                        <?php echo htmlspecialchars($address['postal_code']); ?>, <?php echo htmlspecialchars($address['country']); ?>
                                    </div>
                                </div>
                                <?php if ($address['is_default']): ?>
                                    <span class="status-badge status-success">Principal</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pestaña de Configuración -->
        <div id="settings-tab" class="tab-content">
            <div style="max-width: 600px;">
                <form method="POST" action="" style="background: white; padding: 2rem; border-radius: 10px; box-shadow: var(--ecommerce-shadow);">
                    <h3 style="margin-top: 0;">Información Personal</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label for="username">Nombre de Usuario:</label>
                            <input type="text" id="username" name="username" required 
                                   value="<?php echo htmlspecialchars($user['username']); ?>"
                                   style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px;">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px;">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="bio">Biografía:</label>
                        <textarea id="bio" name="bio" rows="3" placeholder="Cuéntanos sobre ti..."
                                  style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px; resize: vertical;"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <hr style="margin: 2rem 0;">
                    <h4>Cambiar Contraseña</h4>
                    
                    <?php if ($user['account_type'] === 'oauth'): ?>
                        <div style="background: #e3f2fd; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                            <i class="fas fa-info-circle"></i> 
                            Tu cuenta está conectada via <?php echo ucfirst($user['oauth_provider']); ?>. 
                            El cambio de contraseña no está disponible para cuentas OAuth.
                        </div>
                    <?php else: ?>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="current_password">Contraseña Actual:</label>
                            <input type="password" id="current_password" name="current_password"
                                   style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px;">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div class="form-group">
                                <label for="new_password">Nueva Contraseña:</label>
                                <input type="password" id="new_password" name="new_password"
                                       style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px;">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Nueva Contraseña:</label>
                                <input type="password" id="confirm_password" name="confirm_password"
                                       style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px;">
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" 
                            style="background: var(--ecommerce-primary); color: white; border: none; padding: 0.8rem 2rem; border-radius: 5px; cursor: pointer; font-weight: 500;">
                        <i class="fas fa-save"></i> Actualizar Perfil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Ocultar todos los contenidos
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Desactivar todos los botones
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Mostrar el contenido seleccionado
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Activar el botón seleccionado
    event.target.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>