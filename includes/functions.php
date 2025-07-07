<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

function create_user($username, $email, $password, $role = 'user') {
    global $pdo;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$username, $email, $hashed_password, $role]);
}

function authenticate_user($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Verificar que el usuario existe y tiene contraseña (no es OAuth)
    if ($user && $user['password'] && password_verify($password, $user['password'])) {
        // Actualizar último login
        $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update_stmt->execute([$user['id']]);
        
        // Iniciar sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['avatar_url'] = $user['avatar_url'];
        $_SESSION['account_type'] = $user['account_type'] ?? 'local';
        
        return true;
    }
    
    return false;
}

function authenticate_oauth_user($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['avatar_url'] = $user['avatar_url'];
        $_SESSION['account_type'] = $user['account_type'];
        $_SESSION['oauth_provider'] = $user['oauth_provider'];
        
        return true;
    }
    
    return false;
}

function get_user_by_id($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_posts($limit = null, $offset = 0, $category_id = null) {
    global $pdo;
    
    $sql = "SELECT p.*, u.username, c.name as category_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'published'";
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $params = [];
    if ($category_id) {
        $params[] = $category_id;
    }
    
    if ($limit) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

function get_post_by_id($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT p.*, u.username, c.name as category_name 
                          FROM posts p 
                          JOIN users u ON p.user_id = u.id 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function create_post($title, $content, $user_id, $category_id = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id, category_id, status) VALUES (?, ?, ?, ?, 'published')");
    return $stmt->execute([$title, $content, $user_id, $category_id]);
}

function get_comments_by_post($post_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT c.*, u.username 
                          FROM comments c 
                          JOIN users u ON c.user_id = u.id 
                          WHERE c.post_id = ? AND c.status = 'approved' 
                          ORDER BY c.created_at ASC");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll();
}

function create_comment($post_id, $user_id, $content, $parent_id = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, parent_id, status) VALUES (?, ?, ?, ?, 'pending')");
    return $stmt->execute([$post_id, $user_id, $content, $parent_id]);
}

function get_categories() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'hace un momento';
    if ($time < 3600) return 'hace ' . floor($time/60) . ' minutos';
    if ($time < 86400) return 'hace ' . floor($time/3600) . ' horas';
    if ($time < 2629746) return 'hace ' . floor($time/86400) . ' días';
    
    return date('d/m/Y', strtotime($datetime));
}

// Funciones para el generador de imágenes
function validate_image_generation_input($prompt, $style, $size, $category) {
    $errors = [];
    
    // Validar prompt
    if (empty($prompt)) {
        $errors[] = "La descripción de la imagen es requerida";
    } elseif (strlen($prompt) < 10) {
        $errors[] = "La descripción debe tener al menos 10 caracteres";
    } elseif (strlen($prompt) > 1000) {
        $errors[] = "La descripción no puede exceder 1000 caracteres";
    }
    
    // Validar estilo
    $allowed_styles = ['realistic', 'digital-art', 'photographic', 'artistic', 'cinematic', 'cartoon', 'anime', 'fantasy'];
    if (!in_array($style, $allowed_styles)) {
        $errors[] = "Estilo no válido";
    }
    
    // Validar tamaño
    $allowed_sizes = ['512x512', '1024x1024', '1792x1024', '1024x1792'];
    if (!in_array($size, $allowed_sizes)) {
        $errors[] = "Tamaño no válido";
    }
    
    // Validar categoría
    $allowed_categories = ['productos', 'logos', 'banners', 'backgrounds', 'marketing', 'social', 'web', 'otros'];
    if (!in_array($category, $allowed_categories)) {
        $errors[] = "Categoría no válida";
    }
    
    return $errors;
}

function save_generated_image($filename, $prompt, $style, $size, $category, $user_id, $is_real = false) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO generated_images 
            (filename, prompt, style, size, category, generated_by, is_real_image, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $filename, 
            $prompt, 
            $style, 
            $size, 
            $category, 
            $user_id,
            $is_real ? 1 : 0
        ]);
        
        if ($result) {
            return $pdo->lastInsertId();
        }
        
        return false;
        
    } catch (PDOException $e) {
        error_log("Error saving generated image: " . $e->getMessage());
        return false;
    }
}

function get_generated_images($limit = 20, $category = null, $user_id = null) {
    global $pdo;
    
    $sql = "
        SELECT gi.*, u.username 
        FROM generated_images gi 
        LEFT JOIN users u ON gi.generated_by = u.id 
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($category) {
        $sql .= " AND gi.category = ?";
        $params[] = $category;
    }
    
    if ($user_id) {
        $sql .= " AND gi.generated_by = ?";
        $params[] = $user_id;
    }
    
    $sql .= " ORDER BY gi.created_at DESC LIMIT ?";
    $params[] = (int)$limit;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting generated images: " . $e->getMessage());
        return [];
    }
}

// Funciones para la gestión de productos
function get_products($search = null, $category_id = null) {
    global $pdo;

    $sql = "SELECT p.*, c.name as category_name, 
                   SUM(pv.stock) as total_stock,
                   p.updated_at
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }

    $sql .= " GROUP BY p.id ORDER BY p.updated_at DESC, p.created_at DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        // Agregar timestamp de caché para cada producto
        foreach ($products as &$product) {
            $product['cache_key'] = 'product_' . $product['id'] . '_' . strtotime($product['updated_at'] ?? $product['created_at']);
        }
        
        return $products;
    } catch (PDOException $e) {
        error_log("Error getting products: " . $e->getMessage());
        return [];
    }
}

function get_product_by_id($product_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            $stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $product['variants'] = $stmt->fetchAll();
        }
        return $product;
    } catch (PDOException $e) {
        error_log("Error getting product by ID: " . $e->getMessage());
        return false;
    }
}

function add_product($name, $description, $price, $cost, $sku, $main_image_url, $category_id, $variants) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, cost, sku, main_image_url, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $cost, $sku, $main_image_url, $category_id]);
        $product_id = $pdo->lastInsertId();

        foreach ($variants as $variant) {
            $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, size, color, measure, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $product_id,
                empty($variant['size']) ? null : $variant['size'],
                empty($variant['color']) ? null : $variant['color'],
                empty($variant['measure']) ? null : $variant['measure'],
                (int)$variant['stock']
            ]);
        }

        $pdo->commit();
        return $product_id;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error adding product: " . $e->getMessage());
        return false;
    }
}

function update_product($product_id, $name, $description, $price, $cost, $sku, $main_image_url, $category_id, $variants) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, cost = ?, sku = ?, main_image_url = ?, category_id = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$name, $description, $price, $cost, $sku, $main_image_url, $category_id, $product_id]);

        // Eliminar variantes existentes y añadir las nuevas
        $stmt = $pdo->prepare("DELETE FROM product_variants WHERE product_id = ?");
        $stmt->execute([$product_id]);

        foreach ($variants as $variant) {
            $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, size, color, measure, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $product_id,
                empty($variant['size']) ? null : $variant['size'],
                empty($variant['color']) ? null : $variant['color'],
                empty($variant['measure']) ? null : $variant['measure'],
                (int)$variant['stock']
            ]);
        }

        $pdo->commit();
        
        // Limpiar caché si existe
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating product: " . $e->getMessage());
        return false;
    }
}

function delete_product($product_id) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Las variantes se eliminarán automáticamente por ON DELETE CASCADE
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting product: " . $e->getMessage());
        return false;
    }
}

// Funciones auxiliares para el sistema administrativo que no están en config.php
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function get_user_count() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting user count: " . $e->getMessage());
        return 0;
    }
}

function get_post_count() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting post count: " . $e->getMessage());
        return 0;
    }
}

function get_product_count() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting product count: " . $e->getMessage());
        return 0;
    }
}

function get_category_count() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting category count: " . $e->getMessage());
        return 0;
    }
}

// =============================================
// CSRF PROTECTION FUNCTIONS
// =============================================

/**
 * Genera un token CSRF único para proteger formularios
 * @return string Token CSRF
 */
function generate_csrf_token() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Generar token único con datos del servidor y tiempo
    $token = bin2hex(random_bytes(32));
    $timestamp = time();
    
    // Almacenar en sesión con timestamp para expiración
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = $timestamp;
    
    return $token;
}

/**
 * Valida el token CSRF enviado en el formulario
 * @param string $token Token a validar
 * @return bool True si el token es válido, false si no
 */
function validate_csrf_token($token) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar que exista token en sesión
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Verificar que el token no haya expirado (30 minutos)
    if (time() - $_SESSION['csrf_token_time'] > 1800) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    // Verificar que el token coincida
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    
    return true;
}

/**
 * Invalida el token CSRF actual (usar después de procesamiento exitoso)
 */
function invalidate_csrf_token() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    unset($_SESSION['csrf_token']);
    unset($_SESSION['csrf_token_time']);
}

/**
 * Genera campo hidden con token CSRF para formularios
 * @return string HTML del campo hidden
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Middleware de seguridad para verificar métodos HTTP y CSRF
 * @param array $allowed_methods Métodos HTTP permitidos
 * @return bool True si la petición es segura
 */
function security_middleware($allowed_methods = ['POST']) {
    // Verificar método HTTP
    if (!in_array($_SERVER['REQUEST_METHOD'], $allowed_methods)) {
        http_response_code(405);
        die('Método no permitido');
    }
    
    // Para métodos POST, verificar CSRF
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token'])) {
            http_response_code(400);
            die('Token CSRF faltante');
        }
        
        if (!validate_csrf_token($_POST['csrf_token'])) {
            http_response_code(403);
            die('Token CSRF inválido o expirado');
        }
    }
    
    return true;
}

/**
 * Valida y sanitiza datos de entrada con validación adicional
 * @param mixed $data Datos a validar y sanitizar
 * @param string $type Tipo de validación (email, url, int, string)
 * @return mixed Datos sanitizados o false si inválidos
 */
function validate_and_sanitize_input($data, $type = 'string') {
    if (empty($data)) return false;
    
    // Sanitizar primero
    $sanitized = sanitize_input($data, $type);
    
    // Validar según tipo
    switch ($type) {
        case 'email':
            return filter_var($sanitized, FILTER_VALIDATE_EMAIL) ? $sanitized : false;
        case 'url':
            return filter_var($sanitized, FILTER_VALIDATE_URL) ? $sanitized : false;
        case 'int':
            return filter_var($sanitized, FILTER_VALIDATE_INT) !== false ? (int)$sanitized : false;
        case 'float':
            return filter_var($sanitized, FILTER_VALIDATE_FLOAT) !== false ? (float)$sanitized : false;
        case 'string':
        default:
            return $sanitized;
    }
}

/**
 * Sanitiza y valida datos de entrada
 * @param mixed $data Datos a sanitizar
 * @param string $type Tipo de validación (email, url, int, string)
 * @return mixed Datos sanitizados
 */
function sanitize_input($data, $type = 'string') {
    if (is_array($data)) {
        return array_map(function($item) use ($type) {
            return sanitize_input($item, $type);
        }, $data);
    }
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($data, FILTER_SANITIZE_URL);
        case 'int':
            return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'string':
        default:
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Valida que una petición sea AJAX
 * @return bool True si es petición AJAX
 */
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Genera respuesta JSON segura con headers de seguridad
 * @param mixed $data Datos a enviar
 * @param int $status_code Código de estado HTTP
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    exit();
}

/**
 * Verifica si el usuario tiene el rol necesario
 * @param string $required_role Rol requerido
 * @return bool True si tiene el rol
 */
function has_role($required_role) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    $role_hierarchy = [
        'admin' => 3,
        'moderator' => 2,
        'user' => 1
    ];
    
    $user_level = $role_hierarchy[$_SESSION['role']] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

/**
 * Protege páginas administrativas
 * @param string $required_role Rol mínimo requerido
 */
function require_admin($required_role = 'admin') {
    if (!isset($_SESSION['user_id']) || !has_role($required_role)) {
        header('Location: /proyecto/login.php?error=access_denied');
        exit;
    }
}

/**
 * Sanitiza datos para output seguro (previene XSS)
 * @param mixed $data Datos a sanitizar
 * @return mixed Datos sanitizados para output
 */
function sanitize_output($data) {
    if (is_array($data)) {
        return array_map('sanitize_output', $data);
    }
    
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * ADMIN PANEL OPTIMIZATIONS - Performance Enhanced Functions
 */

/**
 * Obtener productos con paginación y búsqueda optimizada
 */
function get_products_paginated($limit = 20, $offset = 0, $search = '') {
    global $pdo;
    
    $sql = "SELECT p.*, 
            COALESCE(SUM(pv.stock), 0) as total_stock,
            COUNT(pv.id) as variant_count
            FROM products p 
            LEFT JOIN product_variants pv ON p.id = pv.product_id 
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
        $search_term = "%{$search}%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Contar productos para paginación
 */
function get_products_count($search = '') {
    global $pdo;
    
    $sql = "SELECT COUNT(*) FROM products WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR sku LIKE ? OR description LIKE ?)";
        $search_term = "%{$search}%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

/**
 * Cache optimizado de estadísticas del dashboard con TTL y persistencia
 */
function get_dashboard_stats_cached($cache_duration = 300) { // 5 minutos
    // Cache persistente en archivo para mejor performance entre requests
    $cache_file = __DIR__ . '/../cache/dashboard_stats.json';
    $cache_time_file = __DIR__ . '/../cache/dashboard_stats_time.txt';
    
    // Verificar si existe cache válido
    if (file_exists($cache_file) && file_exists($cache_time_file)) {
        $cache_time = (int)file_get_contents($cache_time_file);
        if ((time() - $cache_time) <= $cache_duration) {
            return json_decode(file_get_contents($cache_file), true);
        }
    }
    
    global $pdo;
    
    try {
        // Query optimizada unificada para reducir múltiples SELECT
        $stmt = $pdo->query("
            SELECT 
                -- Conteos básicos
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM posts) as total_posts,
                (SELECT COUNT(*) FROM posts WHERE status = 'published') as published_posts,
                (SELECT COUNT(*) FROM comments) as total_comments,
                (SELECT COUNT(*) FROM comments WHERE status = 'pending') as pending_comments,
                (SELECT COUNT(*) FROM products) as total_products,
                
                -- Métricas de órdenes optimizadas
                (SELECT COUNT(*) FROM orders) as total_orders,
                (SELECT COUNT(*) FROM orders WHERE status = 'pending') as pending_orders,
                (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed') as total_revenue,
                
                -- Revenue mensual optimizado con índices
                (SELECT COALESCE(SUM(total_amount), 0) 
                 FROM orders 
                 WHERE status = 'completed' 
                 AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')) as monthly_revenue,
                
                -- Stock metrics con JOIN optimizado
                (SELECT COUNT(*) FROM product_variants WHERE stock <= 5 AND stock > 0) as low_stock_items,
                (SELECT COUNT(*) FROM product_variants WHERE stock = 0) as out_of_stock,
                
                -- Métricas adicionales para dashboard
                (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()) as today_orders,
                (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = CURDATE()) as today_revenue,
                (SELECT COUNT(DISTINCT user_id) FROM orders WHERE MONTH(created_at) = MONTH(CURDATE())) as monthly_customers
        ");
        
        $cache = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calcular métricas derivadas
        $cache['avg_order_value'] = $cache['total_orders'] > 0 ? 
            round($cache['total_revenue'] / $cache['total_orders'], 2) : 0;
        $cache['monthly_avg_order'] = $cache['monthly_customers'] > 0 ? 
            round($cache['monthly_revenue'] / $cache['monthly_customers'], 2) : 0;
        
        // Guardar en cache persistente
        if (!is_dir(dirname($cache_file))) {
            mkdir(dirname($cache_file), 0755, true);
        }
        file_put_contents($cache_file, json_encode($cache));
        file_put_contents($cache_time_file, time());
        
    } catch (Exception $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
        $cache = [
            'total_users' => 0, 'total_posts' => 0, 'published_posts' => 0,
            'total_comments' => 0, 'pending_comments' => 0, 'total_products' => 0,
            'total_orders' => 0, 'pending_orders' => 0, 'total_revenue' => 0,
            'monthly_revenue' => 0, 'low_stock_items' => 0, 'out_of_stock' => 0,
            'today_orders' => 0, 'today_revenue' => 0, 'monthly_customers' => 0,
            'avg_order_value' => 0, 'monthly_avg_order' => 0
        ];
    }
    
    return $cache;
}

/**
 * SECURITY FUNCTIONS - CSRF Protection and Enhanced Security
 */

/**
 * Validación avanzada de uploads de archivos
 */
function validate_file_upload($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']) {
    $validation = [
        'valid' => false,
        'error' => null,
        'file_info' => null
    ];
    
    // Verificar que se haya subido un archivo
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $validation['error'] = 'No se seleccionó ningún archivo';
        return $validation;
    }
    
    // Verificar errores de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'El archivo es demasiado grande (límite servidor)',
            UPLOAD_ERR_FORM_SIZE => 'El archivo es demasiado grande (límite formulario)',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta directorio temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir archivo',
            UPLOAD_ERR_EXTENSION => 'Extensión PHP bloqueó el upload'
        ];
        $validation['error'] = $errors[$file['error']] ?? 'Error desconocido de upload';
        return $validation;
    }
    
    // Verificar tamaño del archivo (máximo 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $validation['error'] = 'El archivo no puede ser mayor a 5MB';
        return $validation;
    }
    
    // Verificar tipo MIME real del archivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $actual_mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($actual_mime, $allowed_types)) {
        $validation['error'] = 'Tipo de archivo no permitido. Solo se permiten: ' . implode(', ', $allowed_types);
        return $validation;
    }
    
    // Verificar que sea realmente una imagen (para uploads de imágenes)
    if (strpos($actual_mime, 'image/') === 0) {
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            $validation['error'] = 'El archivo no es una imagen válida';
            return $validation;
        }
        
        // Verificar dimensiones mínimas y máximas
        list($width, $height) = $image_info;
        if ($width < 10 || $height < 10) {
            $validation['error'] = 'La imagen es demasiado pequeña (mínimo 10x10px)';
            return $validation;
        }
        
        if ($width > 5000 || $height > 5000) {
            $validation['error'] = 'La imagen es demasiado grande (máximo 5000x5000px)';
            return $validation;
        }
        
        $validation['file_info'] = [
            'width' => $width,
            'height' => $height,
            'mime_type' => $actual_mime,
            'size' => $file['size']
        ];
    }
    
    // Generar nombre de archivo seguro
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe_filename = uniqid('img_') . '_' . time() . '.' . strtolower($extension);
    
    $validation['valid'] = true;
    $validation['safe_filename'] = $safe_filename;
    $validation['original_name'] = $file['name'];
    
    return $validation;
}

/**
 * Rate limiting avanzado para acciones admin
 */
function admin_rate_limit($action, $max_attempts = 5, $window_minutes = 15) {
    if (!is_logged_in() || !is_admin()) {
        return true; // No aplicar rate limit a no-admins
    }
    
    $user_id = $_SESSION['user_id'];
    $key = "admin_rate_limit_{$action}_{$user_id}";
    $window_seconds = $window_minutes * 60;
    
    // Obtener intentos actuales
    $cache_file = __DIR__ . "/../cache/rate_limit_{$action}_{$user_id}.json";
    $attempts = [];
    
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data && isset($data['attempts'])) {
            $attempts = $data['attempts'];
        }
    }
    
    // Filtrar intentos dentro de la ventana de tiempo
    $current_time = time();
    $attempts = array_filter($attempts, function($timestamp) use ($current_time, $window_seconds) {
        return ($current_time - $timestamp) < $window_seconds;
    });
    
    // Verificar si se excedió el límite
    if (count($attempts) >= $max_attempts) {
        $oldest_attempt = min($attempts);
        $wait_time = $window_seconds - ($current_time - $oldest_attempt);
        error_log("Rate limit exceeded for admin user {$user_id} on action {$action}");
        return false;
    }
    
    // Registrar nuevo intento
    $attempts[] = $current_time;
    
    // Guardar en cache
    if (!is_dir(dirname($cache_file))) {
        mkdir(dirname($cache_file), 0755, true);
    }
    file_put_contents($cache_file, json_encode(['attempts' => $attempts]));
    
    return true;
}

/**
 * Audit log para acciones críticas del admin
 */
function admin_audit_log($action, $details = [], $user_id = null) {
    $user_id = $user_id ?? ($_SESSION['user_id'] ?? 0);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $user_id,
        'action' => $action,
        'details' => $details,
        'ip_address' => $ip_address,
        'user_agent' => $user_agent,
        'session_id' => session_id()
    ];
    
    // Guardar en archivo de log
    $log_file = __DIR__ . '/../logs/admin_audit.log';
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    $log_line = json_encode($log_entry) . "\n";
    file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    
    // También intentar guardar en base de datos si existe la tabla
    try {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO admin_audit_log (user_id, action, details, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user_id,
            $action,
            json_encode($details),
            $ip_address,
            substr($user_agent, 0, 255) // Limitar longitud
        ]);
    } catch (Exception $e) {
        // Si no existe la tabla, solo usar archivo de log
        error_log("Admin audit log DB error: " . $e->getMessage());
    }
}

/**
 * Validador de sesión admin con timeout automático
 */
function validate_admin_session($timeout_minutes = 60) {
    if (!is_logged_in()) {
        return false;
    }
    
    // Verificar timeout de sesión
    if (isset($_SESSION['admin_last_activity'])) {
        $inactive_time = time() - $_SESSION['admin_last_activity'];
        if ($inactive_time > ($timeout_minutes * 60)) {
            session_destroy();
            return false;
        }
    }
    
    // Verificar que siga siendo admin
    if (!is_admin()) {
        return false;
    }
    
    // Actualizar timestamp de actividad
    $_SESSION['admin_last_activity'] = time();
    
    return true;
}

// NOTA: Las funciones generate_csrf_token() y validate_csrf_token() 
// ya están definidas anteriormente en este archivo (líneas 480-524)
// con funcionalidad completa incluyendo expiración de tokens

/**
 * Validación avanzada de archivos subidos
 */
function validate_upload_security($file, $allowed_types = ['jpg', 'jpeg', 'png', 'webp', 'svg'], $max_size = 5242880) {
    // Verificar errores de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Error en la subida del archivo'];
    }
    
    // Verificar tamaño
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'Archivo demasiado grande (máx. 5MB)'];
    }
    
    // Verificar extensión
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_types)) {
        return ['valid' => false, 'error' => 'Tipo de archivo no permitido'];
    }
    
    // Verificar MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg', 
        'png' => 'image/png',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml'
    ];
    
    if (!isset($allowed_mimes[$extension]) || $mime_type !== $allowed_mimes[$extension]) {
        return ['valid' => false, 'error' => 'Tipo de contenido del archivo no válido'];
    }
    
    return ['valid' => true];
}

/**
 * Rate limiting para prevenir abuso
 */
function check_rate_limit($action, $max_attempts = 5, $time_window = 300) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = "rate_limit_{$action}_{$ip}";
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }
    
    $attempts = &$_SESSION[$key];
    
    // Reset si pasó el tiempo límite
    if (time() - $attempts['first_attempt'] > $time_window) {
        $attempts = ['count' => 0, 'first_attempt' => time()];
    }
    
    $attempts['count']++;
    
    if ($attempts['count'] > $max_attempts) {
        return false;
    }
    
    return true;
}

/**
 * Sanitización avanzada para prevenir inyecciones
 */
function sanitize_input_advanced($input, $type = 'string') {
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'string':
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * ENTERPRISE DASHBOARD INTEGRATION FUNCTIONS
 * Functions needed for the enterprise dashboard backend integration
 */

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Log user activity for audit trail
 */
function log_user_activity($action, $details = []) {
    global $pdo;
    
    if (!is_logged_in()) return;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_audit_log (user_id, action, details, ip_address, user_agent, session_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            json_encode($details),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            session_id()
        ]);
    } catch (Exception $e) {
        error_log("Failed to log user activity: " . $e->getMessage());
    }
}

/**
 * Track page view for analytics
 */
function track_page_view($url = null, $title = null) {
    global $pdo;
    
    $url = $url ?? $_SERVER['REQUEST_URI'] ?? '';
    $title = $title ?? '';
    $session_id = session_id();
    $user_id = $_SESSION['user_id'] ?? null;
    
    try {
        // Insert or update session
        $stmt = $pdo->prepare("
            INSERT INTO sessions (id, user_id, ip_address, user_agent, last_activity) 
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            user_id = VALUES(user_id),
            last_activity = NOW()
        ");
        
        $stmt->execute([
            $session_id,
            $user_id,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // Track page view
        $stmt = $pdo->prepare("
            INSERT INTO page_views (session_id, user_id, url, title, referrer, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $session_id,
            $user_id,
            $url,
            $title,
            $_SERVER['HTTP_REFERER'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
    } catch (Exception $e) {
        error_log("Failed to track page view: " . $e->getMessage());
    }
}

/**
 * Create notification
 */
function create_notification($user_id, $type, $title, $message, $priority = 'normal', $data = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, priority, data) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $user_id,
            $type,
            $title,
            $message,
            $priority,
            json_encode($data)
        ]);
        
    } catch (Exception $e) {
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get system status for health checks
 */
function get_system_status() {
    global $pdo;
    
    $status = [
        'database' => 'healthy',
        'cache' => 'healthy',
        'storage' => 'healthy',
        'overall' => 'healthy'
    ];
    
    // Check database
    try {
        $pdo->query("SELECT 1");
    } catch (Exception $e) {
        $status['database'] = 'critical';
        $status['overall'] = 'critical';
    }
    
    // Check storage
    $disk_usage = (disk_total_space('.') - disk_free_space('.')) / disk_total_space('.') * 100;
    if ($disk_usage > 95) {
        $status['storage'] = 'critical';
        $status['overall'] = 'critical';
    } elseif ($disk_usage > 85) {
        $status['storage'] = 'warning';
        if ($status['overall'] === 'healthy') {
            $status['overall'] = 'warning';
        }
    }
    
    return $status;
}

/**
 * Record performance metric
 */
function record_performance_metric($type, $name, $value, $unit = 'ms', $context = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO performance_metrics (metric_type, metric_name, value, unit, context, user_id, session_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $type,
            $name,
            $value,
            $unit,
            json_encode($context),
            $_SESSION['user_id'] ?? null,
            session_id()
        ]);
        
    } catch (Exception $e) {
        error_log("Failed to record performance metric: " . $e->getMessage());
        return false;
    }
}

/**
 * Log security event
 */
function log_security_event($type, $details = [], $severity = 'medium') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO security_events (event_type, user_id, ip_address, user_agent, details, severity) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $type,
            $_SESSION['user_id'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            json_encode($details),
            $severity
        ]);
        
    } catch (Exception $e) {
        error_log("Failed to log security event: " . $e->getMessage());
        return false;
    }
}

/**
 * Get recent activity for dashboard
 */
function get_recent_activity($limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                'order' as type,
                CONCAT('Nueva orden #', order_number) as message,
                created_at,
                'shopping-cart' as icon,
                'success' as color
            FROM orders 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            
            UNION ALL
            
            SELECT 
                'user' as type,
                CONCAT('Usuario nuevo registrado: ', username) as message,
                created_at,
                'user-plus' as icon,
                'info' as color
            FROM users 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Failed to get recent activity: " . $e->getMessage());
        return [];
    }
}

/**
 * Clear expired cache entries
 */
function clear_expired_cache() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM cache_metadata WHERE expires_at < NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    } catch (Exception $e) {
        error_log("Failed to clear expired cache: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get cache statistics
 */
function get_cache_statistics() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_entries,
                SUM(size_bytes) as total_size,
                SUM(hit_count) as total_hits,
                AVG(hit_count) as avg_hits_per_entry
            FROM cache_metadata
        ");
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_entries' => 0,
            'total_size' => 0,
            'total_hits' => 0,
            'avg_hits_per_entry' => 0
        ];
        
    } catch (Exception $e) {
        error_log("Failed to get cache statistics: " . $e->getMessage());
        return [
            'total_entries' => 0,
            'total_size' => 0,
            'total_hits' => 0,
            'avg_hits_per_entry' => 0
        ];
    }
}

?>