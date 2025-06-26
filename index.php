<?php
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : null;

$offset = ($page - 1) * POSTS_PER_PAGE;

if ($search) {
    $sql = "SELECT p.*, u.username, c.name as category_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'published' AND (p.title LIKE ? OR p.content LIKE ?)
            ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $search_term = "%$search%";
    $stmt->execute([$search_term, $search_term, POSTS_PER_PAGE, $offset]);
    $posts = $stmt->fetchAll();
    
    $count_sql = "SELECT COUNT(*) FROM posts WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute([$search_term, $search_term]);
    $total_posts = $count_stmt->fetchColumn();
} else {
    $posts = get_posts(POSTS_PER_PAGE, $offset, $category_id);
    
    $count_sql = "SELECT COUNT(*) FROM posts WHERE status = 'published'";
    if ($category_id) {
        $count_sql .= " AND category_id = ?";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute([$category_id]);
    } else {
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute();
    }
    $total_posts = $count_stmt->fetchColumn();
}

$total_pages = ceil($total_posts / POSTS_PER_PAGE);
$categories = get_categories();

$page_title = 'Inicio';
include 'includes/header.php';
?>

<div class="home-container">
    <div class="sidebar">
        <div class="search-box">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Buscar posts..." 
                       value="<?php echo $search; ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <div class="categories">
            <h3>Categorías</h3>
            <ul>
                <li><a href="index.php" class="<?php echo !$category_id ? 'active' : ''; ?>">Todas</a></li>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a href="index.php?category=<?php echo $category['id']; ?>" 
                           class="<?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                            <?php echo $category['name']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <div class="main-content">
        <?php if ($search): ?>
            <h2>Resultados de búsqueda para: "<?php echo $search; ?>"</h2>
        <?php endif; ?>
        
        <div class="posts-grid">
            <?php if (empty($posts)): ?>
                <p class="no-posts">No hay posts disponibles.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <h3><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></h3>
                        
                        <div class="post-meta">
                            <span>Por <?php echo $post['username']; ?></span>
                            <span><?php echo time_ago($post['created_at']); ?></span>
                            <?php if ($post['category_name']): ?>
                                <span class="category-tag"><?php echo $post['category_name']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-excerpt">
                            <?php 
                            $excerpt = $post['excerpt'] ?: substr(strip_tags($post['content']), 0, 150) . '...';
                            echo $excerpt; 
                            ?>
                        </div>
                        
                        <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more">Leer más</a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php
                    $url = 'index.php?page=' . $i;
                    if ($category_id) $url .= '&category=' . $category_id;
                    if ($search) $url .= '&search=' . urlencode($search);
                    ?>
                    <a href="<?php echo $url; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>