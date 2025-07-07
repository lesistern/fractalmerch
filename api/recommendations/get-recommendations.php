<?php
/**
 * API Endpoint - Get Recommendations
 * Devuelve recomendaciones basadas en el tipo solicitado
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';
require_once '../../includes/RecommendationEngine.php';

try {
    // Validar parámetros
    $type = $_GET['type'] ?? '';
    $sessionId = $_GET['session_id'] ?? '';
    $userId = $_GET['user_id'] ?? null;
    $productId = $_GET['product_id'] ?? null;
    $limit = min(intval($_GET['limit'] ?? 6), 20); // Máximo 20 productos
    
    if (empty($type) || empty($sessionId)) {
        throw new Exception('Missing required parameters: type, session_id');
    }
    
    // Inicializar recommendation engine
    $engine = new RecommendationEngine($pdo, $sessionId, $userId);
    $recommendations = [];
    
    switch ($type) {
        case 'frequently_bought_together':
            if (!$productId) {
                throw new Exception('product_id required for frequently_bought_together');
            }
            $recommendations = $engine->getFrequentlyBoughtTogether($productId, $limit);
            break;
            
        case 'similar_products':
            if (!$productId) {
                throw new Exception('product_id required for similar_products');
            }
            $recommendations = $engine->getSimilarProducts($productId, $limit);
            break;
            
        case 'personalized':
            if (!$userId) {
                // Si no hay usuario, devolver trending como fallback
                $recommendations = $engine->getTrendingProducts($limit);
            } else {
                $recommendations = $engine->getPersonalizedRecommendations($userId, $limit);
                
                // Si no hay recomendaciones personalizadas, generar nuevas
                if (empty($recommendations)) {
                    $engine->generateUserRecommendations($userId);
                    $recommendations = $engine->getPersonalizedRecommendations($userId, $limit);
                }
                
                // Fallback a trending si aún no hay recomendaciones
                if (empty($recommendations)) {
                    $recommendations = $engine->getTrendingProducts($limit);
                }
            }
            break;
            
        case 'trending':
            $recommendations = $engine->getTrendingProducts($limit);
            break;
            
        case 'price_based':
            $currentPrice = floatval($_GET['current_price'] ?? 0);
            if ($currentPrice <= 0) {
                throw new Exception('current_price required for price_based recommendations');
            }
            $priceRange = floatval($_GET['price_range'] ?? 0.3);
            $recommendations = $engine->getPriceBasedRecommendations($currentPrice, $priceRange, $limit);
            break;
            
        case 'seasonal':
            $season = $_GET['season'] ?? null;
            $recommendations = $engine->getSeasonalRecommendations($season, $limit);
            break;
            
        default:
            throw new Exception('Invalid recommendation type: ' . $type);
    }
    
    // Procesar recomendaciones para incluir datos adicionales
    $processedRecommendations = array_map(function($product) use ($type) {
        return [
            'id' => intval($product['id']),
            'name' => $product['name'],
            'price' => floatval($product['price']),
            'main_image_url' => $product['main_image_url'] ?: 'assets/images/products/default.svg',
            'avg_rating' => floatval($product['avg_rating'] ?? 0),
            'review_count' => intval($product['review_count'] ?? 0),
            'reason' => $product['reason'] ?? null,
            'confidence_score' => floatval($product['confidence_score'] ?? 0),
            'similarity_score' => floatval($product['similarity_score'] ?? 0),
            'trend_score' => floatval($product['trend_score'] ?? 0),
            'recommendation_type' => $type
        ];
    }, $recommendations);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'type' => $type,
        'count' => count($processedRecommendations),
        'recommendations' => $processedRecommendations,
        'metadata' => [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'product_id' => $productId,
            'limit' => $limit,
            'generated_at' => date('c')
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Recommendations API Error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'recommendations' => []
    ]);
}
?>