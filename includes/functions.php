<?php
require_once 'config/database.php';
require_once 'config/config.php';

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
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
    }
    
    $stmt = $pdo->prepare($sql);
    
    if ($category_id && $limit) {
        $stmt->execute([$category_id, $limit, $offset]);
    } elseif ($category_id) {
        $stmt->execute([$category_id]);
    } elseif ($limit) {
        $stmt->execute([$limit, $offset]);
    } else {
        $stmt->execute();
    }
    
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
?>