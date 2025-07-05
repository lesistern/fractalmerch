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
            redirect('profile_premium.php');
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

        <!-- Las demás pestañas mantienen el contenido original pero con clases premium -->
        <!-- Pestaña de Pedidos -->
        <div id="orders-tab" class="tab-content">
            <div style="display: grid; gap: 1rem;">
                <?php if (empty($recent_orders)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <i class="fas fa-shopping-bag" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>No tienes pedidos aún</h3>
                        <p>¡Explora nuestros productos y realiza tu primera compra!</p>
                        <a href="particulares.php" style="background: var(--ecommerce-primary); color: white; padding: 0.7rem 1.5rem; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 1rem;">
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

        <!-- Input oculto para subida de fotos -->
        <input type="file" id="photo-upload" accept="image/*" style="display: none;" onchange="handlePhotoUpload(this)">
        <input type="file" id="cover-upload" accept="image/*" style="display: none;" onchange="handleCoverUpload(this)">

        <!-- Continúa con el resto del contenido... -->
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