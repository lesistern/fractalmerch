<?php
/**
 * Recommendation Engine
 * Sistema avanzado de recomendaciones para e-commerce
 */

require_once 'config/database.php';

class RecommendationEngine {
    private $pdo;
    private $sessionId;
    private $userId;
    
    public function __construct($pdo, $sessionId = null, $userId = null) {
        $this->pdo = $pdo;
        $this->sessionId = $sessionId ?: session_id();
        $this->userId = $userId;
    }
    
    /**
     * Trackear vista de producto
     */
    public function trackProductView($productId, $sourcePage = 'unknown', $viewDuration = 0) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO product_views (user_id, session_id, product_id, view_timestamp, view_duration, source_page, user_agent, ip_address)
                VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)
            ");
            
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            
            $stmt->execute([
                $this->userId,
                $this->sessionId,
                $productId,
                $viewDuration,
                $sourcePage,
                $userAgent,
                $ipAddress
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error tracking product view: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Trackear agregado al carrito
     */
    public function trackCartAddition($productId, $variantDetails = null, $quantity = 1) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO cart_additions (user_id, session_id, product_id, variant_details, quantity, added_timestamp)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $variantJson = $variantDetails ? json_encode($variantDetails) : null;
            
            $stmt->execute([
                $this->userId,
                $this->sessionId,
                $productId,
                $variantJson,
                $quantity
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error tracking cart addition: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener productos "Frecuentemente comprados juntos"
     */
    public function getFrequentlyBoughtTogether($productId, $limit = 4) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.main_image_url,
                    AVG(pr.rating) as avg_rating,
                    COUNT(pr.id) as review_count,
                    pbt.confidence_score,
                    pbt.frequency_count
                FROM products_bought_together pbt
                JOIN products p ON pbt.product_b_id = p.id
                LEFT JOIN product_reviews pr ON p.id = pr.product_id
                WHERE pbt.product_a_id = ? 
                AND pbt.confidence_score > 0.3
                GROUP BY p.id, pbt.confidence_score, pbt.frequency_count
                ORDER BY pbt.confidence_score DESC, pbt.frequency_count DESC
                LIMIT ?
            ");
            
            $stmt->execute([$productId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting frequently bought together: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener productos similares (content-based)
     */
    public function getSimilarProducts($productId, $limit = 4) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.main_image_url,
                    AVG(pr.rating) as avg_rating,
                    COUNT(pr.id) as review_count,
                    ps.similarity_score
                FROM product_similarity ps
                JOIN products p ON ps.product_b_id = p.id
                LEFT JOIN product_reviews pr ON p.id = pr.product_id
                WHERE ps.product_a_id = ? 
                AND ps.similarity_score > 0.5
                GROUP BY p.id, ps.similarity_score
                ORDER BY ps.similarity_score DESC
                LIMIT ?
            ");
            
            $stmt->execute([$productId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting similar products: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener recomendaciones personalizadas para usuario
     */
    public function getPersonalizedRecommendations($userId, $limit = 6) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT
                    p.id,
                    p.name,
                    p.price,
                    p.main_image_url,
                    AVG(pr.rating) as avg_rating,
                    COUNT(pr.id) as review_count,
                    ur.score,
                    ur.reason,
                    ur.recommendation_type
                FROM user_recommendations ur
                JOIN products p ON ur.recommended_product_id = p.id
                LEFT JOIN product_reviews pr ON p.id = pr.product_id
                WHERE ur.user_id = ?
                GROUP BY p.id, ur.score, ur.reason, ur.recommendation_type
                ORDER BY ur.score DESC
                LIMIT ?
            ");
            
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting personalized recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener productos trending
     */
    public function getTrendingProducts($limit = 6) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.main_image_url,
                    AVG(pr.rating) as avg_rating,
                    COUNT(pr.id) as review_count,
                    trending.view_count,
                    trending.trend_score
                FROM (
                    SELECT 
                        product_id,
                        COUNT(*) as view_count,
                        COUNT(*) * 0.7 + 
                        (COUNT(DISTINCT session_id) * 0.3) as trend_score
                    FROM product_views 
                    WHERE view_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY product_id
                    ORDER BY trend_score DESC
                    LIMIT ?
                ) trending
                JOIN products p ON trending.product_id = p.id
                LEFT JOIN product_reviews pr ON p.id = pr.product_id
                GROUP BY p.id, trending.view_count, trending.trend_score
                ORDER BY trending.trend_score DESC
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting trending products: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener recomendaciones por precio (alternativas económicas)
     */
    public function getPriceBasedRecommendations($currentPrice, $priceRange = 0.3, $limit = 4) {
        try {
            $minPrice = $currentPrice * (1 - $priceRange);
            $maxPrice = $currentPrice * (1 + $priceRange);
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.main_image_url,
                    AVG(pr.rating) as avg_rating,
                    COUNT(pr.id) as review_count,
                    ABS(p.price - ?) as price_difference
                FROM products p
                LEFT JOIN product_reviews pr ON p.id = pr.product_id
                WHERE p.price BETWEEN ? AND ?
                GROUP BY p.id
                ORDER BY price_difference ASC, AVG(pr.rating) DESC
                LIMIT ?
            ");
            
            $stmt->execute([$currentPrice, $minPrice, $maxPrice, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting price-based recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener recomendaciones estacionales
     */
    public function getSeasonalRecommendations($season = null, $limit = 4) {
        if (!$season) {
            $month = date('n');
            if ($month >= 3 && $month <= 5) $season = 'spring';
            elseif ($month >= 6 && $month <= 8) $season = 'summer';
            elseif ($month >= 9 && $month <= 11) $season = 'autumn';
            else $season = 'winter';
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.main_image_url,
                    AVG(pr.rating) as avg_rating,
                    COUNT(pr.id) as review_count,
                    pa.attribute_value as season_attribute
                FROM products p
                JOIN product_attributes pa ON p.id = pa.product_id
                LEFT JOIN product_reviews pr ON p.id = pr.product_id
                WHERE pa.attribute_name = 'season'
                AND (pa.attribute_value = ? OR pa.attribute_value = 'Todo el año')
                GROUP BY p.id, pa.attribute_value
                ORDER BY 
                    CASE WHEN pa.attribute_value = ? THEN 1 ELSE 2 END,
                    AVG(pr.rating) DESC
                LIMIT ?
            ");
            
            $stmt->execute([$season, $season, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting seasonal recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generar recomendaciones para usuario específico
     */
    public function generateUserRecommendations($userId) {
        try {
            $stmt = $this->pdo->prepare("CALL GenerateUserRecommendations(?)");
            $stmt->execute([$userId]);
            return true;
        } catch (PDOException $e) {
            error_log("Error generating user recommendations: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar matriz de productos comprados juntos
     */
    public function updateBoughtTogetherMatrix() {
        try {
            $stmt = $this->pdo->prepare("CALL UpdateProductsBoughtTogether()");
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error updating bought together matrix: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de performance del sistema de recomendaciones
     */
    public function getRecommendationStats() {
        try {
            $stats = [];
            
            // Clicks en recomendaciones
            $stmt = $this->pdo->query("
                SELECT 
                    recommendation_type,
                    COUNT(*) as total_recommendations,
                    SUM(clicked) as total_clicks,
                    SUM(purchased) as total_purchases,
                    (SUM(clicked) / COUNT(*)) * 100 as click_rate,
                    (SUM(purchased) / COUNT(*)) * 100 as conversion_rate
                FROM user_recommendations
                WHERE generated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY recommendation_type
            ");
            
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Views vs adds to cart
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(DISTINCT pv.session_id) as unique_viewers,
                    COUNT(pv.id) as total_views,
                    COUNT(DISTINCT ca.session_id) as users_added_to_cart,
                    (COUNT(DISTINCT ca.session_id) / COUNT(DISTINCT pv.session_id)) * 100 as view_to_cart_rate
                FROM product_views pv
                LEFT JOIN cart_additions ca ON pv.session_id = ca.session_id AND pv.product_id = ca.product_id
                WHERE pv.view_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            
            $stats['conversion'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting recommendation stats: " . $e->getMessage());
            return [];
        }
    }
}
?>