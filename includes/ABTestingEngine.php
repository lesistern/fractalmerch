<?php
/**
 * A/B Testing Engine for Recommendations
 * Sistema de testing A/B para optimizar recomendaciones
 */

class ABTestingEngine {
    private $pdo;
    private $sessionId;
    private $userId;
    private $tests;
    
    public function __construct($pdo, $sessionId = null, $userId = null) {
        $this->pdo = $pdo;
        $this->sessionId = $sessionId ?: session_id();
        $this->userId = $userId;
        $this->tests = $this->loadActiveTests();
    }
    
    /**
     * Definir tests A/B activos
     */
    private function loadActiveTests() {
        return [
            'recommendation_algorithm' => [
                'name' => 'Algoritmo de Recomendaciones',
                'variants' => [
                    'A' => ['weight' => 50, 'description' => 'Algoritmo colaborativo'],
                    'B' => ['weight' => 50, 'description' => 'Algoritmo híbrido']
                ],
                'metrics' => ['click_rate', 'conversion_rate', 'revenue_per_user']
            ],
            'recommendation_display' => [
                'name' => 'Display de Recomendaciones',
                'variants' => [
                    'A' => ['weight' => 33, 'description' => 'Grid 4 columnas'],
                    'B' => ['weight' => 33, 'description' => 'Grid 3 columnas'],
                    'C' => ['weight' => 34, 'description' => 'Carousel horizontal']
                ],
                'metrics' => ['engagement', 'time_on_page', 'scroll_depth']
            ],
            'recommendation_titles' => [
                'name' => 'Títulos de Recomendaciones',
                'variants' => [
                    'A' => ['weight' => 50, 'description' => 'Títulos descriptivos'],
                    'B' => ['weight' => 50, 'description' => 'Títulos emotivos']
                ],
                'metrics' => ['click_rate', 'engagement']
            ],
            'price_based_prominence' => [
                'name' => 'Prominencia de Precios',
                'variants' => [
                    'A' => ['weight' => 50, 'description' => 'Precio normal'],
                    'B' => ['weight' => 50, 'description' => 'Precio destacado']
                ],
                'metrics' => ['conversion_rate', 'average_order_value']
            ]
        ];
    }
    
    /**
     * Asignar usuario a un variant de test
     */
    public function assignToTest($testName) {
        if (!isset($this->tests[$testName])) {
            return 'A'; // Default variant
        }
        
        // Verificar si ya está asignado
        $existing = $this->getExistingAssignment($testName);
        if ($existing) {
            return $existing;
        }
        
        // Asignar nuevo variant basado en pesos
        $variant = $this->selectVariant($this->tests[$testName]['variants']);
        
        // Guardar asignación
        $this->saveAssignment($testName, $variant);
        
        return $variant;
    }
    
    /**
     * Obtener asignación existente
     */
    private function getExistingAssignment($testName) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT variant 
                FROM ab_test_assignments 
                WHERE test_name = ? AND (user_id = ? OR session_id = ?)
                ORDER BY user_id DESC, created_at DESC
                LIMIT 1
            ");
            
            $stmt->execute([$testName, $this->userId, $this->sessionId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['variant'] : null;
        } catch (PDOException $e) {
            error_log("Error getting AB test assignment: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Seleccionar variant basado en pesos
     */
    private function selectVariant($variants) {
        $random = mt_rand(1, 100);
        $cumulative = 0;
        
        foreach ($variants as $variant => $config) {
            $cumulative += $config['weight'];
            if ($random <= $cumulative) {
                return $variant;
            }
        }
        
        return array_key_first($variants); // Fallback
    }
    
    /**
     * Guardar asignación de test
     */
    private function saveAssignment($testName, $variant) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ab_test_assignments (test_name, variant, user_id, session_id, created_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE variant = VALUES(variant), updated_at = NOW()
            ");
            
            $stmt->execute([$testName, $variant, $this->userId, $this->sessionId]);
        } catch (PDOException $e) {
            error_log("Error saving AB test assignment: " . $e->getMessage());
        }
    }
    
    /**
     * Trackear evento de test
     */
    public function trackEvent($testName, $variant, $eventType, $eventData = []) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ab_test_events (
                    test_name, variant, event_type, user_id, session_id, 
                    event_data, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $testName,
                $variant,
                $eventType,
                $this->userId,
                $this->sessionId,
                json_encode($eventData)
            ]);
        } catch (PDOException $e) {
            error_log("Error tracking AB test event: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener configuración de variant para recomendaciones
     */
    public function getRecommendationConfig() {
        $config = [
            'algorithm' => $this->assignToTest('recommendation_algorithm'),
            'display' => $this->assignToTest('recommendation_display'),
            'titles' => $this->assignToTest('recommendation_titles'),
            'price_prominence' => $this->assignToTest('price_based_prominence')
        ];
        
        // Trackear exposición
        foreach ($config as $testName => $variant) {
            $this->trackEvent("recommendation_$testName", $variant, 'exposure');
        }
        
        return $config;
    }
    
    /**
     * Obtener títulos personalizados según test
     */
    public function getRecommendationTitles($type, $variant = null) {
        if (!$variant) {
            $variant = $this->assignToTest('recommendation_titles');
        }
        
        $titles = [
            'A' => [ // Descriptivos
                'frequently_bought_together' => 'Productos Frecuentemente Comprados Juntos',
                'similar_products' => 'Productos Similares',
                'personalized' => 'Recomendaciones Personalizadas',
                'trending' => 'Productos Más Populares',
                'price_based' => 'En Tu Rango de Precio',
                'seasonal' => 'Productos de Temporada'
            ],
            'B' => [ // Emotivos
                'frequently_bought_together' => '🛒 ¡Otros compraron esto contigo!',
                'similar_products' => '🔍 ¡Te van a encantar!',
                'personalized' => '✨ ¡Especialmente para ti!',
                'trending' => '🔥 ¡Todo el mundo los quiere!',
                'price_based' => '💰 ¡Perfectos para tu presupuesto!',
                'seasonal' => '🌟 ¡Ideales para esta época!'
            ]
        ];
        
        return $titles[$variant][$type] ?? $titles['A'][$type];
    }
    
    /**
     * Obtener estadísticas de tests
     */
    public function getTestStats($testName, $days = 30) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    variant,
                    COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id ELSE session_id END) as unique_users,
                    COUNT(*) as total_events,
                    SUM(CASE WHEN event_type = 'exposure' THEN 1 ELSE 0 END) as exposures,
                    SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks,
                    SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) as conversions,
                    AVG(CASE WHEN event_type = 'revenue' THEN CAST(JSON_EXTRACT(event_data, '$.amount') AS DECIMAL(10,2)) ELSE 0 END) as avg_revenue
                FROM ab_test_events 
                WHERE test_name = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY variant
                ORDER BY variant
            ");
            
            $stmt->execute([$testName, $days]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular métricas
            $stats = [];
            foreach ($results as $row) {
                $clickRate = $row['exposures'] > 0 ? ($row['clicks'] / $row['exposures']) * 100 : 0;
                $conversionRate = $row['clicks'] > 0 ? ($row['conversions'] / $row['clicks']) * 100 : 0;
                
                $stats[$row['variant']] = [
                    'unique_users' => $row['unique_users'],
                    'exposures' => $row['exposures'],
                    'clicks' => $row['clicks'],
                    'conversions' => $row['conversions'],
                    'click_rate' => round($clickRate, 2),
                    'conversion_rate' => round($conversionRate, 2),
                    'avg_revenue' => round($row['avg_revenue'], 2)
                ];
            }
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting AB test stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcular significancia estadística
     */
    public function calculateSignificance($testName, $metric = 'conversion_rate') {
        $stats = $this->getTestStats($testName);
        
        if (count($stats) < 2) {
            return ['significant' => false, 'confidence' => 0];
        }
        
        $variants = array_keys($stats);
        $a = $stats[$variants[0]];
        $b = $stats[$variants[1]];
        
        // Calcular z-score para conversion rate
        if ($metric === 'conversion_rate') {
            $p1 = $a['conversions'] / $a['exposures'];
            $p2 = $b['conversions'] / $b['exposures'];
            $p_pool = ($a['conversions'] + $b['conversions']) / ($a['exposures'] + $b['exposures']);
            
            $se = sqrt($p_pool * (1 - $p_pool) * (1/$a['exposures'] + 1/$b['exposures']));
            
            if ($se == 0) {
                return ['significant' => false, 'confidence' => 0];
            }
            
            $z = abs($p1 - $p2) / $se;
            
            // Aproximación de confianza
            $confidence = 0;
            if ($z >= 1.96) $confidence = 95;
            elseif ($z >= 1.645) $confidence = 90;
            elseif ($z >= 1.28) $confidence = 80;
            
            return [
                'significant' => $confidence >= 95,
                'confidence' => $confidence,
                'z_score' => round($z, 3),
                'lift' => round((($p2 - $p1) / $p1) * 100, 2)
            ];
        }
        
        return ['significant' => false, 'confidence' => 0];
    }
    
    /**
     * Crear tablas necesarias para A/B testing
     */
    public function createTables() {
        try {
            // Tabla de asignaciones
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS ab_test_assignments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    test_name VARCHAR(100) NOT NULL,
                    variant VARCHAR(10) NOT NULL,
                    user_id INT NULL,
                    session_id VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_assignment (test_name, user_id, session_id),
                    INDEX idx_test_name (test_name),
                    INDEX idx_user_id (user_id),
                    INDEX idx_session_id (session_id)
                )
            ");
            
            // Tabla de eventos
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS ab_test_events (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    test_name VARCHAR(100) NOT NULL,
                    variant VARCHAR(10) NOT NULL,
                    event_type VARCHAR(50) NOT NULL,
                    user_id INT NULL,
                    session_id VARCHAR(255) NOT NULL,
                    event_data JSON NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_test_name (test_name),
                    INDEX idx_variant (variant),
                    INDEX idx_event_type (event_type),
                    INDEX idx_user_id (user_id),
                    INDEX idx_session_id (session_id),
                    INDEX idx_created_at (created_at)
                )
            ");
            
            return true;
        } catch (PDOException $e) {
            error_log("Error creating AB test tables: " . $e->getMessage());
            return false;
        }
    }
}
?>