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
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
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
        $sql .= " LIMIT $limit OFFSET $offset";
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
?>