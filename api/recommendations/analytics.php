<?php
/**
 * API Endpoint - Recommendations Analytics
 * Devuelve analytics del sistema de recomendaciones
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../../config/database.php';
require_once '../../includes/RecommendationEngine.php';

try {
    // Validar parámetros
    $timeframe = intval($_GET['timeframe'] ?? 30); // días
    $timeframe = max(1, min($timeframe, 365)); // entre 1 y 365 días
    
    // Inicializar recommendation engine
    $engine = new RecommendationEngine($pdo);
    
    // Métricas principales
    $mainMetrics = getMainMetrics($pdo, $timeframe);
    
    // Performance por tipo de recomendación
    $byType = getRecommendationTypeMetrics($pdo, $timeframe);
    
    // Top productos
    $topProducts = getTopProducts($pdo, $timeframe);
    
    // Tendencias diarias
    $dailyTrends = getDailyTrends($pdo, $timeframe);
    
    // Distribución por tipo
    $distribution = getTypeDistribution($pdo, $timeframe);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'timeframe' => $timeframe,
        'metrics' => $mainMetrics,
        'by_type' => $byType,
        'top_products' => $topProducts,
        'daily_trends' => $dailyTrends,
        'distribution' => $distribution,
        'generated_at' => date('c')
    ]);
    
} catch (Exception $e) {
    error_log("Analytics API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Obtener métricas principales
 */
function getMainMetrics($pdo, $timeframe) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_impressions,
                SUM(CASE WHEN event_type = 'recommendation_click' THEN 1 ELSE 0 END) as total_clicks,
                SUM(CASE WHEN event_type = 'recommendation_conversion' THEN 1 ELSE 0 END) as total_conversions,
                AVG(CASE WHEN event_type = 'recommendation_conversion' 
                    THEN CAST(JSON_EXTRACT(event_data, '$.price') AS DECIMAL(10,2)) 
                    ELSE 0 END) as avg_conversion_value,
                COUNT(DISTINCT session_id) as unique_sessions
            FROM analytics_events 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND event_type IN ('recommendation_impression', 'recommendation_click', 'recommendation_conversion')
        ");
        
        $stmt->execute([$timeframe]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $clickRate = $result['total_impressions'] > 0 ? 
            ($result['total_clicks'] / $result['total_impressions']) * 100 : 0;
            
        $conversionRate = $result['total_clicks'] > 0 ? 
            ($result['total_conversions'] / $result['total_clicks']) * 100 : 0;
            
        $attributedRevenue = $result['total_conversions'] * $result['avg_conversion_value'];
        
        return [
            'total_impressions' => intval($result['total_impressions']),
            'total_clicks' => intval($result['total_clicks']),
            'total_conversions' => intval($result['total_conversions']),
            'click_rate' => round($clickRate, 2),
            'conversion_rate' => round($conversionRate, 2),
            'attributed_revenue' => round($attributedRevenue, 2),
            'unique_sessions' => intval($result['unique_sessions'])
        ];
    } catch (PDOException $e) {
        error_log("Error getting main metrics: " . $e->getMessage());
        return [
            'total_impressions' => 0,
            'total_clicks' => 0,
            'total_conversions' => 0,
            'click_rate' => 0,
            'conversion_rate' => 0,
            'attributed_revenue' => 0,
            'unique_sessions' => 0
        ];
    }
}

/**
 * Obtener métricas por tipo de recomendación
 */
function getRecommendationTypeMetrics($pdo, $timeframe) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                JSON_EXTRACT(event_data, '$.recommendation_type') as rec_type,
                COUNT(CASE WHEN event_type = 'recommendation_impression' THEN 1 END) as impressions,
                COUNT(CASE WHEN event_type = 'recommendation_click' THEN 1 END) as clicks,
                COUNT(CASE WHEN event_type = 'recommendation_conversion' THEN 1 END) as conversions,
                SUM(CASE WHEN event_type = 'recommendation_conversion' 
                    THEN CAST(JSON_EXTRACT(event_data, '$.price') AS DECIMAL(10,2)) 
                    ELSE 0 END) as revenue
            FROM analytics_events 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND event_type IN ('recommendation_impression', 'recommendation_click', 'recommendation_conversion')
            AND JSON_EXTRACT(event_data, '$.recommendation_type') IS NOT NULL
            GROUP BY rec_type
        ");
        
        $stmt->execute([$timeframe]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $metrics = [];
        foreach ($results as $row) {
            $type = trim($row['rec_type'], '"'); // Remover comillas del JSON
            $clickRate = $row['impressions'] > 0 ? ($row['clicks'] / $row['impressions']) * 100 : 0;
            $conversionRate = $row['clicks'] > 0 ? ($row['conversions'] / $row['clicks']) * 100 : 0;
            
            $metrics[$type] = [
                'impressions' => intval($row['impressions']),
                'clicks' => intval($row['clicks']),
                'conversions' => intval($row['conversions']),
                'click_rate' => round($clickRate, 2),
                'conversion_rate' => round($conversionRate, 2),
                'revenue' => round($row['revenue'], 2)
            ];
        }
        
        return $metrics;
    } catch (PDOException $e) {
        error_log("Error getting type metrics: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener top productos desde recomendaciones
 */
function getTopProducts($pdo, $timeframe) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.name,
                COUNT(CASE WHEN ae.event_type = 'recommendation_impression' THEN 1 END) as impressions,
                COUNT(CASE WHEN ae.event_type = 'recommendation_click' THEN 1 END) as clicks,
                COUNT(CASE WHEN ae.event_type = 'recommendation_conversion' THEN 1 END) as conversions,
                SUM(CASE WHEN ae.event_type = 'recommendation_conversion' 
                    THEN CAST(JSON_EXTRACT(ae.event_data, '$.price') AS DECIMAL(10,2)) 
                    ELSE 0 END) as revenue,
                p.cost * COUNT(CASE WHEN ae.event_type = 'recommendation_conversion' THEN 1 END) as total_cost
            FROM analytics_events ae
            JOIN products p ON p.id = CAST(JSON_EXTRACT(ae.event_data, '$.product_id') AS UNSIGNED)
            WHERE ae.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND ae.event_type IN ('recommendation_impression', 'recommendation_click', 'recommendation_conversion')
            AND JSON_EXTRACT(ae.event_data, '$.product_id') IS NOT NULL
            GROUP BY p.id, p.name, p.cost
            HAVING impressions > 0
            ORDER BY revenue DESC, conversions DESC
            LIMIT 10
        ");
        
        $stmt->execute([$timeframe]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $products = [];
        foreach ($results as $row) {
            $clickRate = $row['impressions'] > 0 ? ($row['clicks'] / $row['impressions']) * 100 : 0;
            $roi = $row['total_cost'] > 0 ? (($row['revenue'] - $row['total_cost']) / $row['total_cost']) * 100 : 0;
            
            $products[] = [
                'id' => intval($row['id']),
                'name' => $row['name'],
                'impressions' => intval($row['impressions']),
                'clicks' => intval($row['clicks']),
                'conversions' => intval($row['conversions']),
                'click_rate' => round($clickRate, 2),
                'revenue' => round($row['revenue'], 2),
                'roi' => round($roi, 0)
            ];
        }
        
        return $products;
    } catch (PDOException $e) {
        error_log("Error getting top products: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener tendencias diarias
 */
function getDailyTrends($pdo, $timeframe) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(CASE WHEN event_type = 'recommendation_impression' THEN 1 END) as impressions,
                COUNT(CASE WHEN event_type = 'recommendation_click' THEN 1 END) as clicks,
                COUNT(CASE WHEN event_type = 'recommendation_conversion' THEN 1 END) as conversions,
                SUM(CASE WHEN event_type = 'recommendation_conversion' 
                    THEN CAST(JSON_EXTRACT(event_data, '$.price') AS DECIMAL(10,2)) 
                    ELSE 0 END) as revenue
            FROM analytics_events 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND event_type IN ('recommendation_impression', 'recommendation_click', 'recommendation_conversion')
            GROUP BY DATE(created_at)
            ORDER BY date DESC
            LIMIT ?
        ");
        
        $stmt->execute([$timeframe, $timeframe]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $trends = [];
        foreach ($results as $row) {
            $clickRate = $row['impressions'] > 0 ? ($row['clicks'] / $row['impressions']) * 100 : 0;
            $conversionRate = $row['clicks'] > 0 ? ($row['conversions'] / $row['clicks']) * 100 : 0;
            
            $trends[] = [
                'date' => $row['date'],
                'impressions' => intval($row['impressions']),
                'clicks' => intval($row['clicks']),
                'conversions' => intval($row['conversions']),
                'click_rate' => round($clickRate, 2),
                'conversion_rate' => round($conversionRate, 2),
                'revenue' => round($row['revenue'], 2)
            ];
        }
        
        return array_reverse($trends); // Orden cronológico
    } catch (PDOException $e) {
        error_log("Error getting daily trends: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener distribución por tipo
 */
function getTypeDistribution($pdo, $timeframe) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                JSON_EXTRACT(event_data, '$.recommendation_type') as rec_type,
                COUNT(*) as count
            FROM analytics_events 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND event_type = 'recommendation_impression'
            AND JSON_EXTRACT(event_data, '$.recommendation_type') IS NOT NULL
            GROUP BY rec_type
            ORDER BY count DESC
        ");
        
        $stmt->execute([$timeframe]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $distribution = [];
        $total = 0;
        
        foreach ($results as $row) {
            $total += $row['count'];
        }
        
        foreach ($results as $row) {
            $type = trim($row['rec_type'], '"');
            $percentage = $total > 0 ? ($row['count'] / $total) * 100 : 0;
            
            $distribution[] = [
                'type' => $type,
                'count' => intval($row['count']),
                'percentage' => round($percentage, 1)
            ];
        }
        
        return $distribution;
    } catch (PDOException $e) {
        error_log("Error getting type distribution: " . $e->getMessage());
        return [];
    }
}
?>