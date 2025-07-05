<?php
/**
 * Feedback Submission API
 * Handles user feedback and survey responses
 */

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $required = ['survey_type', 'user_id', 'session_id', 'question', 'answer', 'timestamp'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Connect to database
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create feedback table if not exists
    createFeedbackTable($pdo);
    
    // Insert feedback response
    $feedbackId = insertFeedbackResponse($pdo, $data);
    
    // Process NPS score if applicable
    if ($data['survey_type'] === 'nps') {
        processNPSScore($pdo, $data, $feedbackId);
    }
    
    // Trigger follow-up actions based on feedback
    triggerFollowUpActions($data);
    
    echo json_encode([
        'success' => true,
        'message' => 'Feedback submitted successfully',
        'feedback_id' => $feedbackId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
    
    error_log("Feedback API Error: " . $e->getMessage());
}

/**
 * Insert feedback response into database
 */
function insertFeedbackResponse($pdo, $data) {
    $sql = "INSERT INTO feedback_responses (
        user_id, session_id, survey_type, question, answer, follow_up_answer,
        context, rating_score, multiple_choice_answer, text_answer,
        timestamp, ip_address, user_agent, created_at
    ) VALUES (
        :user_id, :session_id, :survey_type, :question, :answer, :follow_up,
        :context, :rating_score, :multiple_choice, :text_answer,
        :timestamp, :ip_address, :user_agent, NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    
    // Determine answer type and parse accordingly
    $ratingScore = null;
    $multipleChoice = null;
    $textAnswer = null;
    
    if (is_numeric($data['answer'])) {
        $ratingScore = intval($data['answer']);
    } elseif (is_string($data['answer'])) {
        if (strlen($data['answer']) > 100) {
            $textAnswer = $data['answer'];
        } else {
            $multipleChoice = $data['answer'];
        }
    }
    
    $stmt->execute([
        'user_id' => sanitize_string($data['user_id']),
        'session_id' => sanitize_string($data['session_id']),
        'survey_type' => sanitize_string($data['survey_type']),
        'question' => sanitize_string($data['question']),
        'answer' => sanitize_string(is_array($data['answer']) ? json_encode($data['answer']) : $data['answer']),
        'follow_up' => sanitize_string($data['followUp'] ?? ''),
        'context' => sanitize_string($data['context'] ?? ''),
        'rating_score' => $ratingScore,
        'multiple_choice' => $multipleChoice,
        'text_answer' => $textAnswer,
        'timestamp' => intval($data['timestamp']),
        'ip_address' => getUserIP(),
        'user_agent' => sanitize_string($_SERVER['HTTP_USER_AGENT'] ?? '')
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Process NPS score and categorize user
 */
function processNPSScore($pdo, $data, $feedbackId) {
    $score = intval($data['answer']);
    
    // Determine NPS category
    $category = 'detractor'; // 0-6
    if ($score >= 9) {
        $category = 'promoter'; // 9-10
    } elseif ($score >= 7) {
        $category = 'passive'; // 7-8
    }
    
    // Insert NPS specific data
    $sql = "INSERT INTO nps_scores (
        feedback_id, user_id, score, category, follow_up_text, created_at
    ) VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $feedbackId,
        $data['user_id'],
        $score,
        $category,
        $data['followUp'] ?? ''
    ]);
    
    // Update user's NPS history
    updateUserNPSHistory($pdo, $data['user_id'], $score, $category);
}

/**
 * Update user's NPS history and segment
 */
function updateUserNPSHistory($pdo, $userId, $score, $category) {
    // Check if user already has NPS history
    $stmt = $pdo->prepare("
        SELECT id, avg_score, response_count, last_category 
        FROM user_nps_history 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing record
        $newCount = $existing['response_count'] + 1;
        $newAvgScore = (($existing['avg_score'] * $existing['response_count']) + $score) / $newCount;
        
        $updateSql = "
            UPDATE user_nps_history 
            SET avg_score = ?, response_count = ?, last_score = ?, 
                last_category = ?, updated_at = NOW()
            WHERE user_id = ?
        ";
        
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([$newAvgScore, $newCount, $score, $category, $userId]);
    } else {
        // Create new record
        $insertSql = "
            INSERT INTO user_nps_history (
                user_id, avg_score, response_count, last_score, 
                last_category, created_at
            ) VALUES (?, ?, 1, ?, ?, NOW())
        ";
        
        $stmt = $pdo->prepare($insertSql);
        $stmt->execute([$userId, $score, $score, $category]);
    }
}

/**
 * Trigger follow-up actions based on feedback
 */
function triggerFollowUpActions($data) {
    $surveyType = $data['survey_type'];
    $answer = $data['answer'];
    $context = $data['context'] ?? '';
    
    // Handle negative feedback
    if ($surveyType === 'nps' && intval($answer) <= 6) {
        // Schedule follow-up email for detractors
        scheduleDetractorFollowUp($data);
    }
    
    if ($surveyType === 'satisfaction' && intval($answer) <= 2) {
        // Alert customer service for very dissatisfied customers
        alertCustomerService($data);
    }
    
    // Handle feature requests
    if ($surveyType === 'feature_request' && !empty($data['answer'])) {
        // Forward to product team
        forwardToProductTeam($data);
    }
    
    // Handle checkout issues
    if ($surveyType === 'checkout_experience' && strpos($data['answer'], 'complicado') !== false) {\n        // Analyze checkout friction\n        analyzeCheckoutFriction($data);\n    }\n}\n\n/**\n * Schedule follow-up email for detractors\n */\nfunction scheduleDetractorFollowUp($data) {\n    // This would integrate with your email system\n    // For now, just log the intent\n    error_log(\"Detractor follow-up needed for user: {$data['user_id']}, Score: {$data['answer']}\");\n    \n    // You could also store this in a queue table for processing\n    // INSERT INTO email_queue (user_id, template, priority, scheduled_at)\n}\n\n/**\n * Alert customer service for urgent issues\n */\nfunction alertCustomerService($data) {\n    // This would send an alert to customer service\n    error_log(\"Customer service alert: Very dissatisfied customer - User: {$data['user_id']}\");\n    \n    // Could integrate with Slack, email, or ticketing system\n}\n\n/**\n * Forward feature requests to product team\n */\nfunction forwardToProductTeam($data) {\n    // Log feature request\n    error_log(\"Feature request from user {$data['user_id']}: {$data['answer']}\");\n    \n    // Could integrate with project management tools like Jira, Trello, etc.\n}\n\n/**\n * Analyze checkout friction points\n */\nfunction analyzeCheckoutFriction($data) {\n    // Log checkout issue for analysis\n    error_log(\"Checkout friction reported by user {$data['user_id']}: {$data['answer']}\");\n    \n    // Could trigger A/B test adjustments or UX improvements\n}\n\n/**\n * Create feedback tables if they don't exist\n */\nfunction createFeedbackTable($pdo) {\n    // Main feedback responses table\n    $sql = \"CREATE TABLE IF NOT EXISTS feedback_responses (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        user_id VARCHAR(100) NOT NULL,\n        session_id VARCHAR(100) NOT NULL,\n        survey_type VARCHAR(50) NOT NULL,\n        question TEXT NOT NULL,\n        answer TEXT,\n        follow_up_answer TEXT,\n        context VARCHAR(100),\n        rating_score INT NULL,\n        multiple_choice_answer VARCHAR(255) NULL,\n        text_answer TEXT NULL,\n        timestamp BIGINT NOT NULL,\n        ip_address VARCHAR(45),\n        user_agent TEXT,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        INDEX idx_user (user_id),\n        INDEX idx_session (session_id),\n        INDEX idx_survey_type (survey_type),\n        INDEX idx_context (context),\n        INDEX idx_rating (rating_score),\n        INDEX idx_created (created_at)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\";\n    \n    $pdo->exec($sql);\n    \n    // NPS specific table\n    $sql = \"CREATE TABLE IF NOT EXISTS nps_scores (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        feedback_id INT NOT NULL,\n        user_id VARCHAR(100) NOT NULL,\n        score INT NOT NULL,\n        category ENUM('detractor', 'passive', 'promoter') NOT NULL,\n        follow_up_text TEXT,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        FOREIGN KEY (feedback_id) REFERENCES feedback_responses(id),\n        INDEX idx_user (user_id),\n        INDEX idx_score (score),\n        INDEX idx_category (category),\n        INDEX idx_created (created_at)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\";\n    \n    $pdo->exec($sql);\n    \n    // User NPS history table\n    $sql = \"CREATE TABLE IF NOT EXISTS user_nps_history (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        user_id VARCHAR(100) NOT NULL UNIQUE,\n        avg_score DECIMAL(3,2) NOT NULL,\n        response_count INT NOT NULL DEFAULT 1,\n        last_score INT NOT NULL,\n        last_category ENUM('detractor', 'passive', 'promoter') NOT NULL,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n        INDEX idx_user (user_id),\n        INDEX idx_avg_score (avg_score),\n        INDEX idx_category (last_category)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\";\n    \n    $pdo->exec($sql);\n    \n    // Feedback analytics summary table\n    $sql = \"CREATE TABLE IF NOT EXISTS feedback_analytics (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        date DATE NOT NULL,\n        survey_type VARCHAR(50) NOT NULL,\n        total_responses INT DEFAULT 0,\n        avg_rating DECIMAL(3,2) NULL,\n        nps_score DECIMAL(5,2) NULL,\n        detractors_count INT DEFAULT 0,\n        passives_count INT DEFAULT 0,\n        promoters_count INT DEFAULT 0,\n        satisfaction_avg DECIMAL(3,2) NULL,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n        UNIQUE KEY unique_date_survey (date, survey_type),\n        INDEX idx_date (date),\n        INDEX idx_survey_type (survey_type),\n        INDEX idx_nps (nps_score)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\";\n    \n    $pdo->exec($sql);\n}\n\n/**\n * Get user's real IP address\n */\nfunction getUserIP() {\n    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];\n    \n    foreach ($ipKeys as $key) {\n        if (!empty($_SERVER[$key])) {\n            $ips = explode(',', $_SERVER[$key]);\n            $ip = trim($ips[0]);\n            \n            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {\n                return $ip;\n            }\n        }\n    }\n    \n    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';\n}\n\n/**\n * Sanitize string input\n */\nfunction sanitize_string($str) {\n    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');\n}\n\n// Endpoint for getting feedback analytics (GET request)\nif ($_SERVER['REQUEST_METHOD'] === 'GET') {\n    try {\n        $type = $_GET['type'] ?? 'summary';\n        $timeframe = $_GET['timeframe'] ?? '30d';\n        \n        $analytics = getFeedbackAnalytics($pdo, $type, $timeframe);\n        \n        echo json_encode([\n            'success' => true,\n            'data' => $analytics,\n            'timeframe' => $timeframe\n        ]);\n        \n    } catch (Exception $e) {\n        http_response_code(500);\n        echo json_encode([\n            'error' => $e->getMessage(),\n            'success' => false\n        ]);\n    }\n}\n\n/**\n * Get feedback analytics\n */\nfunction getFeedbackAnalytics($pdo, $type, $timeframe) {\n    $whereClause = getTimeframeWhere($timeframe);\n    \n    switch ($type) {\n        case 'nps':\n            return getNPSAnalytics($pdo, $whereClause);\n        case 'satisfaction':\n            return getSatisfactionAnalytics($pdo, $whereClause);\n        case 'summary':\n            return getFeedbackSummary($pdo, $whereClause);\n        default:\n            throw new Exception('Invalid analytics type');\n    }\n}\n\n/**\n * Get NPS analytics\n */\nfunction getNPSAnalytics($pdo, $whereClause) {\n    // Overall NPS calculation\n    $stmt = $pdo->query(\"\n        SELECT \n            COUNT(*) as total_responses,\n            SUM(CASE WHEN category = 'promoter' THEN 1 ELSE 0 END) as promoters,\n            SUM(CASE WHEN category = 'passive' THEN 1 ELSE 0 END) as passives,\n            SUM(CASE WHEN category = 'detractor' THEN 1 ELSE 0 END) as detractors,\n            AVG(score) as avg_score\n        FROM nps_scores \n        WHERE {$whereClause}\n    \");\n    \n    $data = $stmt->fetch(PDO::FETCH_ASSOC);\n    \n    // Calculate NPS score\n    $total = $data['total_responses'];\n    $npsScore = $total > 0 ? \n        (($data['promoters'] - $data['detractors']) / $total) * 100 : 0;\n    \n    return [\n        'nps_score' => round($npsScore, 1),\n        'total_responses' => $total,\n        'promoters' => intval($data['promoters']),\n        'passives' => intval($data['passives']),\n        'detractors' => intval($data['detractors']),\n        'avg_score' => round(floatval($data['avg_score']), 1)\n    ];\n}\n\n/**\n * Get satisfaction analytics\n */\nfunction getSatisfactionAnalytics($pdo, $whereClause) {\n    $stmt = $pdo->query(\"\n        SELECT \n            AVG(rating_score) as avg_satisfaction,\n            COUNT(*) as total_responses,\n            SUM(CASE WHEN rating_score >= 4 THEN 1 ELSE 0 END) as positive,\n            SUM(CASE WHEN rating_score = 3 THEN 1 ELSE 0 END) as neutral,\n            SUM(CASE WHEN rating_score <= 2 THEN 1 ELSE 0 END) as negative\n        FROM feedback_responses \n        WHERE survey_type = 'satisfaction' AND rating_score IS NOT NULL \n        AND {$whereClause}\n    \");\n    \n    $data = $stmt->fetch(PDO::FETCH_ASSOC);\n    \n    return [\n        'avg_satisfaction' => round(floatval($data['avg_satisfaction']), 1),\n        'total_responses' => intval($data['total_responses']),\n        'positive' => intval($data['positive']),\n        'neutral' => intval($data['neutral']),\n        'negative' => intval($data['negative'])\n    ];\n}\n\n/**\n * Get feedback summary\n */\nfunction getFeedbackSummary($pdo, $whereClause) {\n    // Total responses by survey type\n    $stmt = $pdo->query(\"\n        SELECT \n            survey_type,\n            COUNT(*) as count,\n            AVG(rating_score) as avg_rating\n        FROM feedback_responses \n        WHERE {$whereClause}\n        GROUP BY survey_type\n        ORDER BY count DESC\n    \");\n    \n    $surveyTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);\n    \n    // Recent feedback\n    $stmt = $pdo->query(\"\n        SELECT \n            survey_type,\n            question,\n            answer,\n            follow_up_answer,\n            rating_score,\n            created_at\n        FROM feedback_responses \n        WHERE {$whereClause}\n        ORDER BY created_at DESC \n        LIMIT 10\n    \");\n    \n    $recentFeedback = $stmt->fetchAll(PDO::FETCH_ASSOC);\n    \n    return [\n        'survey_types' => $surveyTypes,\n        'recent_feedback' => $recentFeedback\n    ];\n}\n\n/**\n * Get timeframe WHERE clause\n */\nfunction getTimeframeWhere($timeframe) {\n    switch ($timeframe) {\n        case '1d':\n            return \"created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)\";\n        case '7d':\n            return \"created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)\";\n        case '30d':\n            return \"created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)\";\n        case '90d':\n            return \"created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)\";\n        default:\n            return \"created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)\";\n    }\n}\n?>