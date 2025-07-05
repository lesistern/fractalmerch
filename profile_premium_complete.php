<?php
require_once 'includes/functions.php';
require_once 'includes/photo_uploader.php';

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

// Obtener estadísticas premium adicionales
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT pm.id) as payment_methods_count,
        COUNT(DISTINCT oa.id) as oauth_accounts_count,
        COUNT(DISTINCT st.id) as support_tickets_count,
        COUNT(DISTINCT wl.id) as wishlists_count
    FROM users u
    LEFT JOIN user_payment_methods pm ON u.id = pm.user_id
    LEFT JOIN user_oauth_accounts oa ON u.id = oa.user_id AND oa.is_active = TRUE
    LEFT JOIN support_tickets st ON u.id = st.user_id
    LEFT JOIN user_wishlists wl ON u.id = wl.user_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$premium_stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'payment_methods_count' => 0,
    'oauth_accounts_count' => 0,
    'support_tickets_count' => 0,
    'wishlists_count' => 0
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

// Obtener métodos de pago
$stmt = $pdo->prepare("SELECT * FROM user_payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener cuentas OAuth vinculadas
$stmt = $pdo->prepare("SELECT * FROM user_oauth_accounts WHERE user_id = ? AND is_active = TRUE ORDER BY connected_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$oauth_accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener tickets de soporte
$stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$support_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener historial de puntos
$stmt = $pdo->prepare("SELECT * FROM points_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$points_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener cupones disponibles
$stmt = $pdo->prepare("
    SELECT gc.* 
    FROM global_coupons gc
    LEFT JOIN coupon_usage cu ON gc.id = cu.coupon_id AND cu.user_id = ?
    WHERE gc.is_active = TRUE 
    AND gc.valid_until > NOW()
    AND (gc.max_uses_per_user IS NULL OR 
         COALESCE((SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = gc.id AND user_id = ?), 0) < gc.max_uses_per_user)
    ORDER BY gc.value DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$available_coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Obtener actividad reciente
$stmt = $pdo->prepare("SELECT * FROM user_activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formularios
if ($_POST) {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $bio = sanitize_input($_POST['bio']);
    $phone = sanitize_input($_POST['phone'] ?? '');
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
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, bio = ?, phone = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([$username, $email, $bio, $phone, $hashed_password, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, bio = ?, phone = ? WHERE id = ?");
            $result = $stmt->execute([$username, $email, $bio, $phone, $_SESSION['user_id']]);
        }
        
        if ($result) {
            $_SESSION['username'] = $username;
            flash_message('success', 'Perfil actualizado exitosamente');
            redirect('profile_premium_complete.php');
        } else {
            flash_message('error', 'Error al actualizar el perfil');
        }
    } else {
        foreach ($errors as $error) {
            flash_message('error', $error);
        }
    }
}

$page_title = 'Mi Cuenta Premium - FractalMerch';
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
        'cancelled' => ['Cancelado', 'danger'],
        'open' => ['Abierto', 'primary'],
        'in_progress' => ['En Proceso', 'warning'],
        'resolved' => ['Resuelto', 'success'],
        'closed' => ['Cerrado', 'secondary']
    ];
    
    $badge = $badges[$status] ?? ['Desconocido', 'secondary'];
    return "<span class='status-badge status-{$badge[1]}'>{$badge[0]}</span>";
}

// Función para obtener URL de foto
function get_photo_url($filename, $size = 'original') {
    if (!$filename) return null;
    
    if ($size === 'original') {
        return 'assets/images/profiles/' . $filename;
    } else {
        $thumbnail_filename = str_replace('.', '_' . $size . '.', $filename);
        return 'assets/images/profiles/' . $thumbnail_filename;
    }
}
?>

<!-- CSS específico para el perfil premium -->
<style>
.premium-profile {
    background: var(--bg-primary);
    min-height: calc(100vh - 200px);
}

.premium-header {
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0;
    border-radius: 0 0 20px 20px;
    margin-bottom: 2rem;
    overflow: hidden;
}

.cover-photo {
    width: 100%;
    height: 200px;
    object-fit: cover;
    position: relative;
}

.cover-photo-placeholder {
    width: 100%;
    height: 200px;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: rgba(255,255,255,0.5);
}

.profile-info-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    padding: 2rem;
}

.profile-avatar-container {
    position: relative;
    display: inline-block;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 5px solid white;
    object-fit: cover;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.profile-avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 5px solid white;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: rgba(255,255,255,0.7);
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.photo-upload-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: var(--ecommerce-primary);
    color: white;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.photo-upload-btn:hover {
    background: var(--ecommerce-secondary);
    transform: scale(1.1);
}

.cover-upload-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cover-upload-btn:hover {
    background: rgba(0,0,0,0.9);
}

.premium-badge {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
    display: inline-block;
    margin-left: 0.5rem;
}

.premium-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin: 0 2rem 2rem 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: var(--ecommerce-shadow);
    text-align: center;
    border-left: 4px solid var(--ecommerce-primary);
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--ecommerce-shadow-hover);
}

.stat-card.premium {
    border-left-color: #f093fb;
    background: linear-gradient(135deg, #fff 0%, #f8f9ff 100%);
}

.stat-number {
    font-size: 2.2rem;
    font-weight: bold;
    color: var(--ecommerce-primary);
    margin-bottom: 0.5rem;
}

.stat-number.premium {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
}

.premium-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid var(--border-color);
    overflow-x: auto;
    padding: 0 2rem;
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
    position: relative;
}

.tab-btn.active {
    background: linear-gradient(135deg, var(--ecommerce-primary), var(--ecommerce-secondary));
    color: white;
}

.tab-btn.premium {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.tab-btn:hover:not(.active) {
    background: var(--bg-tertiary);
    transform: translateY(-2px);
}

.tab-content {
    display: none;
    padding: 0 2rem;
}

.tab-content.active {
    display: block;
}

.premium-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: var(--ecommerce-shadow);
    margin-bottom: 1.5rem;
    transition: transform 0.2s ease;
}

.premium-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--ecommerce-shadow-hover);
}

.premium-card.gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.payment-method-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 2px solid var(--border-color);
    border-radius: 10px;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.payment-method-card:hover {
    border-color: var(--ecommerce-primary);
    transform: translateY(-1px);
}

.payment-method-card.default {
    border-color: var(--ecommerce-primary);
    background: rgba(255, 149, 0, 0.05);
}

.payment-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: var(--ecommerce-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.oauth-account-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 2px solid var(--border-color);
    border-radius: 10px;
    margin-bottom: 1rem;
}

.oauth-account-card.google { border-left-color: #db4437; }
.oauth-account-card.facebook { border-left-color: #3b5998; }
.oauth-account-card.apple { border-left-color: #000000; }
.oauth-account-card.microsoft { border-left-color: #00a1f1; }

.oauth-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.oauth-icon.google { background: #db4437; }
.oauth-icon.facebook { background: #3b5998; }
.oauth-icon.apple { background: #000000; }
.oauth-icon.microsoft { background: #00a1f1; }

.points-timeline {
    position: relative;
    padding-left: 2rem;
}

.points-timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border-color);
}

.points-item {
    position: relative;
    margin-bottom: 1.5rem;
    background: white;
    padding: 1rem;
    border-radius: 10px;
    box-shadow: var(--ecommerce-shadow);
}

.points-item::before {
    content: '';
    position: absolute;
    left: -25px;
    top: 1rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--ecommerce-primary);
}

.points-item.positive::before {
    background: #28a745;
}

.points-item.negative::before {
    background: #dc3545;
}

.coupon-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 1rem;
    position: relative;
    overflow: hidden;
}

.coupon-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.1);
    border-radius: 0 0 0 50px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--ecommerce-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-area {
    border: 2px dashed var(--border-color);
    border-radius: 10px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: var(--ecommerce-primary);
    background: rgba(255, 149, 0, 0.05);
}

.upload-area.dragover {
    border-color: var(--ecommerce-primary);
    background: rgba(255, 149, 0, 0.1);
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
.status-secondary { background: #e2e3e5; color: #41464b; }

@media (max-width: 768px) {
    .premium-header {
        margin: 0 -1rem 2rem -1rem;
    }
    
    .premium-stats {
        grid-template-columns: 1fr;
        margin: 0 1rem 2rem 1rem;
    }
    
    .premium-tabs {
        padding: 0 1rem;
    }
    
    .tab-content {
        padding: 0 1rem;
    }
    
    .profile-info-overlay {
        padding: 1rem;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
    }
    
    .profile-avatar-placeholder {
        width: 80px;
        height: 80px;
        font-size: 2rem;
    }
}
</style>

<div class="premium-profile">
    <!-- Header Premium con foto de portada -->
    <div class="premium-header">
        <?php if (!empty($user['cover_photo'])): ?>
            <img src="<?php echo get_photo_url($user['cover_photo']); ?>" alt="Portada" class="cover-photo">
        <?php else: ?>
            <div class="cover-photo-placeholder">
                <i class="fas fa-camera"></i>
            </div>
        <?php endif; ?>
        
        <button class="cover-upload-btn" onclick="uploadCoverPhoto()">
            <i class="fas fa-camera"></i> Cambiar Portada
        </button>
        
        <div class="profile-info-overlay">
            <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                <div class="profile-avatar-container">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="<?php echo get_photo_url($user['profile_photo']); ?>" alt="Avatar" class="profile-avatar">
                    <?php elseif (!empty($user['avatar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" class="profile-avatar">
                    <?php else: ?>
                        <div class="profile-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <button class="photo-upload-btn" onclick="uploadProfilePhoto()">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                
                <div>
                    <h1 style="margin: 0 0 0.5rem 0; font-size: 2rem;">
                        ¡Hola, <?php echo htmlspecialchars($user['name'] ?? $user['username']); ?>!
                        <?php if ($user['is_premium'] || $user['subscription_tier'] !== 'free'): ?>
                            <span class="premium-badge">PREMIUM</span>
                        <?php endif; ?>
                    </h1>
                    <div style="opacity: 0.9; display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.9rem;">
                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                        <span><i class="fas fa-calendar"></i> Miembro desde <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                        <?php if ($user['account_type'] === 'oauth'): ?>
                            <span><i class="fab fa-<?php echo $user['oauth_provider']; ?>"></i> Conectado via <?php echo ucfirst($user['oauth_provider']); ?></span>
                        <?php endif; ?>
                        <?php if ($unread_notifications > 0): ?>
                            <span><i class="fas fa-bell"></i> <?php echo $unread_notifications; ?> notificaciones</span>
                        <?php endif; ?>
                        <?php if ($user['referral_code']): ?>
                            <span><i class="fas fa-share-alt"></i> Código: <?php echo $user['referral_code']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Premium -->
    <div class="premium-stats">
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
        <div class="stat-card premium">
            <div class="stat-number premium"><?php echo $premium_stats['payment_methods_count']; ?></div>
            <div class="stat-label">Métodos de Pago</div>
        </div>
        <div class="stat-card premium">
            <div class="stat-number premium"><?php echo $premium_stats['oauth_accounts_count']; ?></div>
            <div class="stat-label">Cuentas Vinculadas</div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div>
        <!-- Pestañas Premium -->
        <div class="premium-tabs">
            <button class="tab-btn active" onclick="showTab('dashboard')">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </button>
            <button class="tab-btn" onclick="showTab('orders')">
                <i class="fas fa-shopping-bag"></i> Mis Pedidos
            </button>
            <button class="tab-btn" onclick="showTab('favorites')">
                <i class="fas fa-heart"></i> Favoritos
            </button>
            <button class="tab-btn" onclick="showTab('addresses')">
                <i class="fas fa-map-marker-alt"></i> Direcciones
            </button>
            <button class="tab-btn premium" onclick="showTab('payments')">
                <i class="fas fa-credit-card"></i> Métodos de Pago
            </button>
            <button class="tab-btn premium" onclick="showTab('oauth')">
                <i class="fas fa-link"></i> Cuentas Vinculadas
            </button>
            <button class="tab-btn premium" onclick="showTab('points')">
                <i class="fas fa-star"></i> Puntos & Cupones
            </button>
            <button class="tab-btn premium" onclick="showTab('support')">
                <i class="fas fa-headset"></i> Soporte
            </button>
            <button class="tab-btn premium" onclick="showTab('security')">
                <i class="fas fa-shield-alt"></i> Seguridad
            </button>
            <button class="tab-btn" onclick="showTab('settings')">
                <i class="fas fa-cog"></i> Configuración
            </button>
        </div>

        <!-- Pestaña Dashboard -->
        <div id="dashboard-tab" class="tab-content active">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <!-- Pedidos Recientes -->
                <div class="premium-card">
                    <h3><i class="fas fa-shopping-bag"></i> Pedidos Recientes</h3>
                    <?php if (empty($recent_orders)): ?>
                        <p style="text-align: center; color: var(--text-secondary);">No hay pedidos recientes</p>
                    <?php else: ?>
                        <?php foreach (array_slice($recent_orders, 0, 3) as $order): ?>
                            <div style="border-bottom: 1px solid var(--border-color); padding: 0.5rem 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                        <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                            <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <?php echo get_status_badge($order['status']); ?>
                                        <div style="font-weight: bold; color: var(--ecommerce-primary);">
                                            <?php echo format_price($order['total_amount']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Cupones Disponibles -->
                <div class="premium-card">
                    <h3><i class="fas fa-tags"></i> Cupones Disponibles</h3>
                    <?php if (empty($available_coupons)): ?>
                        <p style="text-align: center; color: var(--text-secondary);">No hay cupones disponibles</p>
                    <?php else: ?>
                        <?php foreach (array_slice($available_coupons, 0, 2) as $coupon): ?>
                            <div class="coupon-card" style="margin-bottom: 1rem; padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($coupon['code']); ?></strong>
                                        <div style="font-size: 0.9rem; opacity: 0.9;">
                                            <?php echo htmlspecialchars($coupon['name']); ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right; font-size: 1.2rem; font-weight: bold;">
                                        <?php if ($coupon['type'] === 'percentage'): ?>
                                            <?php echo $coupon['value']; ?>% OFF
                                        <?php else: ?>
                                            $<?php echo number_format($coupon['value'], 0); ?> OFF
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Actividad Reciente -->
                <div class="premium-card">
                    <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
                    <?php if (empty($recent_activity)): ?>
                        <p style="text-align: center; color: var(--text-secondary);">No hay actividad reciente</p>
                    <?php else: ?>
                        <?php foreach (array_slice($recent_activity, 0, 4) as $activity): ?>
                            <div class="activity-item" style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                                <div class="activity-icon" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    <i class="fas fa-<?php echo $activity['activity_type'] === 'login' ? 'sign-in-alt' : 'user-edit'; ?>"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-size: 0.9rem;"><?php echo htmlspecialchars($activity['description']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                        <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pestaña de Pedidos -->
        <div id="orders-tab" class="tab-content">
            <div style="display: grid; gap: 1rem;">
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
                        <div class="premium-card">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <div>
                                    <div style="font-weight: bold; color: var(--ecommerce-primary); font-size: 1.1rem;">
                                        Pedido #<?php echo htmlspecialchars($order['order_number']); ?>
                                    </div>
                                    <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </div>
                                </div>
                                <div>
                                    <?php echo get_status_badge($order['status']); ?>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="font-size: 1.2rem; color: var(--ecommerce-primary);">
                                        <?php echo format_price($order['total_amount']); ?>
                                    </strong>
                                    <span style="color: var(--text-secondary); margin-left: 0.5rem;">
                                        <?php echo $order['items_count']; ?> artículo(s)
                                    </span>
                                </div>
                                
                                <?php if (!empty($order['tracking_number'])): ?>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                        <i class="fas fa-truck"></i> Tracking: <?php echo htmlspecialchars($order['tracking_number']); ?>
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
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($favorite_products as $product): ?>
                        <div class="premium-card" style="overflow: hidden;">
                            <?php if ($product['main_image_url']): ?>
                                <img src="<?php echo htmlspecialchars($product['main_image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem;">
                            <?php else: ?>
                                <div style="width: 100%; height: 200px; background: var(--bg-tertiary); display: flex; align-items: center; justify-content: center; border-radius: 10px; margin-bottom: 1rem;">
                                    <i class="fas fa-image" style="font-size: 2rem; color: var(--text-secondary);"></i>
                                </div>
                            <?php endif; ?>
                            
                            <h4 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($product['name']); ?></h4>
                            <div style="color: var(--ecommerce-primary); font-weight: bold; font-size: 1.1rem; margin-bottom: 1rem;">
                                <?php echo format_price($product['price']); ?>
                            </div>
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn-primary-outline" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                Ver Producto
                            </a>
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
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($user_addresses as $address): ?>
                        <div class="premium-card">
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

        <!-- Pestaña de Métodos de Pago -->
        <div id="payments-tab" class="tab-content">
            <div class="premium-card gradient">
                <h3><i class="fas fa-credit-card"></i> Mis Métodos de Pago</h3>
                <p>Administra tus métodos de pago de forma segura</p>
            </div>
            
            <?php if (empty($payment_methods)): ?>
                <div class="empty-state">
                    <i class="fas fa-credit-card"></i>
                    <h3>No tienes métodos de pago</h3>
                    <p>Agrega un método de pago para compras más rápidas y seguras.</p>
                    <button class="btn-primary-outline" onclick="alert('Función próximamente')">
                        <i class="fas fa-plus"></i> Agregar Método de Pago
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($payment_methods as $payment): ?>
                    <div class="payment-method-card <?php echo $payment['is_default'] ? 'default' : ''; ?>">
                        <div class="payment-icon">
                            <?php echo strtoupper(substr($payment['provider'], 0, 2)); ?>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 0.3rem 0;"><?php echo htmlspecialchars($payment['provider']); ?></h4>
                            <div style="color: var(--text-secondary);">
                                **** **** **** <?php echo htmlspecialchars($payment['last_four_digits']); ?>
                            </div>
                            <?php if ($payment['cardholder_name']): ?>
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($payment['cardholder_name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <?php if ($payment['is_default']): ?>
                                <span class="status-badge status-success">Principal</span>
                            <?php endif; ?>
                            <?php if ($payment['is_verified']): ?>
                                <span class="status-badge status-info">Verificado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pestaña de Cuentas OAuth -->
        <div id="oauth-tab" class="tab-content">
            <div class="premium-card gradient">
                <h3><i class="fas fa-link"></i> Cuentas Vinculadas</h3>
                <p>Conecta múltiples cuentas para un acceso más fácil</p>
            </div>
            
            <?php if (empty($oauth_accounts)): ?>
                <div class="empty-state">
                    <i class="fas fa-link"></i>
                    <h3>No tienes cuentas vinculadas</h3>
                    <p>Conecta cuentas de Google, Facebook, Apple y más para acceso rápido.</p>
                </div>
            <?php else: ?>
                <?php foreach ($oauth_accounts as $oauth): ?>
                    <div class="oauth-account-card <?php echo $oauth['provider']; ?>">
                        <div class="oauth-icon <?php echo $oauth['provider']; ?>">
                            <i class="fab fa-<?php echo $oauth['provider']; ?>"></i>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 0.3rem 0;"><?php echo ucfirst($oauth['provider']); ?></h4>
                            <div style="color: var(--text-secondary);">
                                <?php echo htmlspecialchars($oauth['email'] ?? $oauth['name']); ?>
                            </div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                Conectado el <?php echo date('d/m/Y', strtotime($oauth['connected_at'])); ?>
                            </div>
                        </div>
                        <div>
                            <?php if ($oauth['is_primary']): ?>
                                <span class="status-badge status-success">Principal</span>
                            <?php endif; ?>
                            <button style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer;" onclick="alert('Función próximamente')">
                                Desvincular
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Botones para vincular nuevas cuentas -->
            <div class="premium-card">
                <h4>Vincular Nueva Cuenta</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <button style="background: #db4437; color: white; border: none; padding: 0.8rem; border-radius: 8px; cursor: pointer;" onclick="alert('Función próximamente')">
                        <i class="fab fa-google"></i> Google
                    </button>
                    <button style="background: #3b5998; color: white; border: none; padding: 0.8rem; border-radius: 8px; cursor: pointer;" onclick="alert('Función próximamente')">
                        <i class="fab fa-facebook"></i> Facebook
                    </button>
                    <button style="background: #000000; color: white; border: none; padding: 0.8rem; border-radius: 8px; cursor: pointer;" onclick="alert('Función próximamente')">
                        <i class="fab fa-apple"></i> Apple
                    </button>
                    <button style="background: #00a1f1; color: white; border: none; padding: 0.8rem; border-radius: 8px; cursor: pointer;" onclick="alert('Función próximamente')">
                        <i class="fab fa-microsoft"></i> Microsoft
                    </button>
                </div>
            </div>
        </div>

        <!-- Pestaña de Puntos y Cupones -->
        <div id="points-tab" class="tab-content">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <!-- Balance de Puntos -->
                <div class="premium-card gradient">
                    <h3><i class="fas fa-star"></i> Puntos de Fidelidad</h3>
                    <div style="font-size: 3rem; font-weight: bold; text-align: center; margin: 1rem 0;">
                        <?php echo $user['loyalty_points'] ?? 0; ?>
                    </div>
                    <p style="text-align: center; margin: 0;">Puntos disponibles</p>
                </div>
                
                <!-- Cupones Disponibles -->
                <div class="premium-card">
                    <h3><i class="fas fa-tags"></i> Cupones Disponibles</h3>
                    <?php if (empty($available_coupons)): ?>
                        <p style="text-align: center; color: var(--text-secondary);">No hay cupones disponibles</p>
                    <?php else: ?>
                        <?php foreach ($available_coupons as $coupon): ?>
                            <div class="coupon-card">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($coupon['code']); ?></strong>
                                        <div style="font-size: 0.9rem; opacity: 0.9;">
                                            <?php echo htmlspecialchars($coupon['name']); ?>
                                        </div>
                                        <div style="font-size: 0.8rem; opacity: 0.8;">
                                            Válido hasta <?php echo date('d/m/Y', strtotime($coupon['valid_until'])); ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right; font-size: 1.2rem; font-weight: bold;">
                                        <?php if ($coupon['type'] === 'percentage'): ?>
                                            <?php echo $coupon['value']; ?>% OFF
                                        <?php else: ?>
                                            $<?php echo number_format($coupon['value'], 0); ?> OFF
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Historial de Puntos -->
            <div class="premium-card">
                <h3><i class="fas fa-history"></i> Historial de Puntos</h3>
                <?php if (empty($points_history)): ?>
                    <p style="text-align: center; color: var(--text-secondary);">No hay actividad de puntos</p>
                <?php else: ?>
                    <div class="points-timeline">
                        <?php foreach ($points_history as $point): ?>
                            <div class="points-item <?php echo $point['points_change'] > 0 ? 'positive' : 'negative'; ?>">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($point['description']); ?></strong>
                                        <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                            <?php echo date('d/m/Y H:i', strtotime($point['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-weight: bold; color: <?php echo $point['points_change'] > 0 ? '#28a745' : '#dc3545'; ?>;">
                                            <?php echo $point['points_change'] > 0 ? '+' : ''; ?><?php echo $point['points_change']; ?> pts
                                        </div>
                                        <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                            Balance: <?php echo $point['balance_after']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pestaña de Soporte -->
        <div id="support-tab" class="tab-content">
            <div class="premium-card gradient">
                <h3><i class="fas fa-headset"></i> Centro de Soporte</h3>
                <p>¿Necesitas ayuda? Nuestro equipo está aquí para ti</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="premium-card" style="text-align: center;">
                    <i class="fas fa-ticket-alt" style="font-size: 2rem; color: var(--ecommerce-primary); margin-bottom: 1rem;"></i>
                    <h4>Crear Ticket</h4>
                    <p>Reporta un problema o solicita ayuda</p>
                    <button class="btn-primary-outline" onclick="alert('Función próximamente')">
                        Nuevo Ticket
                    </button>
                </div>
                
                <div class="premium-card" style="text-align: center;">
                    <i class="fas fa-question-circle" style="font-size: 2rem; color: var(--ecommerce-primary); margin-bottom: 1rem;"></i>
                    <h4>FAQ</h4>
                    <p>Respuestas a preguntas frecuentes</p>
                    <button class="btn-primary-outline" onclick="alert('Función próximamente')">
                        Ver FAQ
                    </button>
                </div>
                
                <div class="premium-card" style="text-align: center;">
                    <i class="fas fa-comments" style="font-size: 2rem; color: var(--ecommerce-primary); margin-bottom: 1rem;"></i>
                    <h4>Chat en Vivo</h4>
                    <p>Habla directamente con soporte</p>
                    <button class="btn-primary-outline" onclick="alert('Función próximamente')">
                        Iniciar Chat
                    </button>
                </div>
            </div>
            
            <!-- Tickets Recientes -->
            <div class="premium-card">
                <h3><i class="fas fa-list"></i> Mis Tickets</h3>
                <?php if (empty($support_tickets)): ?>
                    <p style="text-align: center; color: var(--text-secondary);">No tienes tickets de soporte</p>
                <?php else: ?>
                    <?php foreach ($support_tickets as $ticket): ?>
                        <div style="border-bottom: 1px solid var(--border-color); padding: 1rem 0;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <h4 style="margin: 0 0 0.5rem 0;">
                                        #<?php echo htmlspecialchars($ticket['ticket_number']); ?> - 
                                        <?php echo htmlspecialchars($ticket['subject']); ?>
                                    </h4>
                                    <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                        <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?>
                                    </div>
                                </div>
                                <div>
                                    <?php echo get_status_badge($ticket['status']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pestaña de Seguridad -->
        <div id="security-tab" class="tab-content">
            <div class="premium-card gradient">
                <h3><i class="fas fa-shield-alt"></i> Seguridad de la Cuenta</h3>
                <p>Protege tu cuenta con nuestras funciones de seguridad avanzadas</p>
            </div>
            
            <!-- Autenticación de Dos Factores -->
            <div class="premium-card">
                <h3><i class="fas fa-mobile-alt"></i> Autenticación de Dos Factores (2FA)</h3>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p>Agrega una capa extra de seguridad a tu cuenta</p>
                        <div style="color: var(--text-secondary); font-size: 0.9rem;">
                            Estado: <?php echo $user['two_factor_enabled'] ? 'Activado' : 'Desactivado'; ?>
                        </div>
                    </div>
                    <div>
                        <?php if ($user['two_factor_enabled']): ?>
                            <button style="background: #dc3545; color: white; border: none; padding: 0.7rem 1.5rem; border-radius: 5px; cursor: pointer;" onclick="alert('Función próximamente')">
                                Desactivar 2FA
                            </button>
                        <?php else: ?>
                            <button style="background: #28a745; color: white; border: none; padding: 0.7rem 1.5rem; border-radius: 5px; cursor: pointer;" onclick="alert('Función próximamente')">
                                Activar 2FA
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sesiones Activas -->
            <div class="premium-card">
                <h3><i class="fas fa-desktop"></i> Sesiones Activas</h3>
                <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; margin: 1rem 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>Sesión Actual</strong>
                            <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                <?php echo $_SERVER['HTTP_USER_AGENT'] ?? 'Navegador desconocido'; ?>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                IP: <?php echo $_SERVER['REMOTE_ADDR'] ?? 'IP desconocida'; ?>
                            </div>
                        </div>
                        <span class="status-badge status-success">Activa</span>
                    </div>
                </div>
                <button class="btn-primary-outline" onclick="alert('Función próximamente')">
                    Ver Todas las Sesiones
                </button>
            </div>
            
            <!-- Cambio de Contraseña -->
            <div class="premium-card">
                <h3><i class="fas fa-key"></i> Cambiar Contraseña</h3>
                <?php if ($user['account_type'] === 'oauth'): ?>
                    <div style="background: #e3f2fd; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                        <i class="fas fa-info-circle"></i> 
                        Tu cuenta está conectada via <?php echo ucfirst($user['oauth_provider']); ?>. 
                        El cambio de contraseña no está disponible para cuentas OAuth.
                    </div>
                <?php else: ?>
                    <p>Se recomienda cambiar tu contraseña periódicamente</p>
                    <button class="btn-primary-outline" onclick="document.getElementById('settings-tab').classList.add('active'); document.getElementById('security-tab').classList.remove('active'); showTab('settings');">
                        Cambiar Contraseña
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pestaña de Configuración -->
        <div id="settings-tab" class="tab-content">
            <div style="max-width: 800px;">
                <form method="POST" action="" style="background: white; padding: 2rem; border-radius: 15px; box-shadow: var(--ecommerce-shadow);">
                    <h3 style="margin-top: 0;"><i class="fas fa-user-edit"></i> Información Personal</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label for="username">Nombre de Usuario:</label>
                            <input type="text" id="username" name="username" required 
                                   value="<?php echo htmlspecialchars($user['username']); ?>"
                                   style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px; margin-top: 0.5rem;">
                        </div>
                        
                        <div>
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px; margin-top: 0.5rem;">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="phone">Teléfono:</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                               style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px; margin-top: 0.5rem;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="bio">Biografía:</label>
                        <textarea id="bio" name="bio" rows="3" placeholder="Cuéntanos sobre ti..."
                                  style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px; resize: vertical; margin-top: 0.5rem;"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <hr style="margin: 2rem 0;">
                    <h4><i class="fas fa-lock"></i> Cambiar Contraseña</h4>
                    
                    <?php if ($user['account_type'] === 'oauth'): ?>
                        <div style="background: #e3f2fd; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                            <i class="fas fa-info-circle"></i> 
                            Tu cuenta está conectada via <?php echo ucfirst($user['oauth_provider']); ?>. 
                            El cambio de contraseña no está disponible para cuentas OAuth.
                        </div>
                    <?php else: ?>
                        <div style="margin-bottom: 1rem;">
                            <label for="current_password">Contraseña Actual:</label>
                            <input type="password" id="current_password" name="current_password"
                                   style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px; margin-top: 0.5rem;">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label for="new_password">Nueva Contraseña:</label>
                                <input type="password" id="new_password" name="new_password"
                                       style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px; margin-top: 0.5rem;">
                            </div>
                            
                            <div>
                                <label for="confirm_password">Confirmar Nueva Contraseña:</label>
                                <input type="password" id="confirm_password" name="confirm_password"
                                       style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 5px; margin-top: 0.5rem;">
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" 
                            style="background: linear-gradient(135deg, var(--ecommerce-primary), var(--ecommerce-secondary)); color: white; border: none; padding: 0.8rem 2rem; border-radius: 5px; cursor: pointer; font-weight: 500;">
                        <i class="fas fa-save"></i> Actualizar Perfil
                    </button>
                </form>
            </div>
        </div>

        <!-- Input oculto para subida de fotos -->
        <input type="file" id="photo-upload" accept="image/*" style="display: none;" onchange="handlePhotoUpload(this)">
        <input type="file" id="cover-upload" accept="image/*" style="display: none;" onchange="handleCoverUpload(this)">
    </div>
</div>

<!-- JavaScript para funcionalidades premium -->
<script>
let currentPhotoType = 'profile';

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

function uploadProfilePhoto() {
    currentPhotoType = 'profile';
    document.getElementById('photo-upload').click();
}

function uploadCoverPhoto() {
    currentPhotoType = 'cover';
    document.getElementById('cover-upload').click();
}

function handlePhotoUpload(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('type', currentPhotoType);
        
        // Mostrar loading
        showLoadingMessage('Subiendo foto...');
        
        fetch('ajax/upload_photo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoadingMessage();
            if (data.success) {
                showSuccessMessage(data.message);
                // Recargar la página para mostrar la nueva foto
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch(error => {
            hideLoadingMessage();
            showErrorMessage('Error al subir la foto');
        });
    }
}

function handleCoverUpload(input) {
    currentPhotoType = 'cover';
    handlePhotoUpload(input);
}

function deletePhoto(type) {
    if (confirm('¿Estás seguro de que quieres eliminar esta foto?')) {
        fetch('ajax/delete_photo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ type: type })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch(error => {
            showErrorMessage('Error al eliminar la foto');
        });
    }
}

function showLoadingMessage(message) {
    // Implementar mensaje de carga
    console.log(message);
}

function hideLoadingMessage() {
    // Ocultar mensaje de carga
}

function showSuccessMessage(message) {
    // Implementar mensaje de éxito
    alert(message);
}

function showErrorMessage(message) {
    // Implementar mensaje de error
    alert(message);
}

// Inicializar tooltips y otras funcionalidades premium
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidades adicionales se pueden agregar aquí
});
</script>

<?php include 'includes/footer.php'; ?>