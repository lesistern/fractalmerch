<?php
/**
 * Cart Recovery Email System
 * Handles abandoned cart email sequences
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
    $required = ['email', 'cartData', 'abandonmentTime'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Connect to database
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Schedule email recovery sequence
    $recoveryId = scheduleEmailRecovery($pdo, $data);
    
    echo json_encode([
        'success' => true,
        'message' => 'Email recovery sequence scheduled',
        'recovery_id' => $recoveryId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
    
    error_log("Cart Recovery API Error: " . $e->getMessage());
}

/**
 * Schedule email recovery sequence
 */
function scheduleEmailRecovery($pdo, $data) {
    $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('Invalid email address');
    }
    
    $cartData = $data['cartData'];
    $sessionData = $data['sessionData'] ?? [];
    $userSegment = $data['userSegment'] ?? 'unknown';
    
    // Find or create abandonment record
    $abandonmentId = findOrCreateAbandonment($pdo, $data);
    
    // Schedule email sequence based on user segment
    $emailSequence = getEmailSequenceForSegment($userSegment, $cartData);
    
    foreach ($emailSequence as $emailConfig) {
        scheduleEmail($pdo, $abandonmentId, $email, $emailConfig, $cartData);
    }
    
    return $abandonmentId;
}

/**
 * Find or create abandonment record
 */
function findOrCreateAbandonment($pdo, $data) {
    $sessionId = $data['sessionData']['sessionId'] ?? 'unknown';
    $userId = $data['sessionData']['userId'] ?? 'unknown';
    
    // Check if abandonment already exists for this session
    $stmt = $pdo->prepare("
        SELECT id FROM cart_abandonments 
        WHERE session_id = ? AND user_id = ? 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$sessionId, $userId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        return $existing['id'];
    }
    
    // Create new abandonment record
    $cartData = $data['cartData'];
    $stmt = $pdo->prepare("
        INSERT INTO cart_abandonments (
            session_id, user_id, abandonment_reason, funnel_step, 
            cart_value, item_count, cart_items, time_in_funnel, 
            previous_steps, timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $sessionId,
        $userId,
        'email_recovery',
        $data['sessionData']['currentStep'] ?? 'unknown',
        $cartData['total'] ?? 0,
        count($cartData['items'] ?? []),
        json_encode($cartData['items'] ?? []),
        0, // Will be calculated if needed
        json_encode($data['sessionData']['previousSteps'] ?? []),
        $data['abandonmentTime']
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Get email sequence configuration based on user segment
 */
function getEmailSequenceForSegment($segment, $cartData) {\n    $cartValue = $cartData['total'] ?? 0;\n    \n    $sequences = [\n        'new_visitor' => [\n            [\n                'type' => 'immediate',\n                'delay_minutes' => 0,\n                'template' => 'welcome_abandonment',\n                'subject' => '¡No olvides tu diseño! Tu carrito te está esperando',\n                'discount' => 0\n            ],\n            [\n                'type' => '1_hour',\n                'delay_minutes' => 60,\n                'template' => 'first_reminder',\n                'subject' => '¿Aún interesado? Tu carrito sigue disponible',\n                'discount' => 5\n            ],\n            [\n                'type' => '24_hour',\n                'delay_minutes' => 1440,\n                'template' => 'final_chance',\n                'subject' => 'Última oportunidad: 10% de descuento en tu carrito',\n                'discount' => 10\n            ]\n        ],\n        \n        'returning_customer' => [\n            [\n                'type' => 'immediate',\n                'delay_minutes' => 0,\n                'template' => 'returning_customer',\n                'subject' => 'Tu carrito te extraña - Completa tu pedido',\n                'discount' => 0\n            ],\n            [\n                'type' => '24_hour',\n                'delay_minutes' => 1440,\n                'template' => 'loyal_customer_offer',\n                'subject' => 'Oferta especial para ti: 15% de descuento',\n                'discount' => 15\n            ]\n        ],\n        \n        'high_intent_non_buyer' => [\n            [\n                'type' => 'immediate',\n                'delay_minutes' => 15,\n                'template' => 'urgent_offer',\n                'subject' => '¡Espera! Tenemos una oferta especial para ti',\n                'discount' => 15\n            ],\n            [\n                'type' => '1_hour',\n                'delay_minutes' => 60,\n                'template' => 'price_sensitive',\n                'subject' => 'Precio especial por tiempo limitado',\n                'discount' => 20\n            ],\n            [\n                'type' => '72_hour',\n                'delay_minutes' => 4320,\n                'template' => 'final_offer',\n                'subject' => 'Último intento: 25% de descuento',\n                'discount' => 25\n            ]\n        ],\n        \n        'browser' => [\n            [\n                'type' => '1_hour',\n                'delay_minutes' => 60,\n                'template' => 'gentle_reminder',\n                'subject' => 'Vimos que te gustaron nuestros productos',\n                'discount' => 0\n            ],\n            [\n                'type' => '72_hour',\n                'delay_minutes' => 4320,\n                'template' => 'browser_engagement',\n                'subject' => 'Descubre lo que otros están comprando',\n                'discount' => 10\n            ]\n        ]\n    ];\n    \n    // Adjust discounts based on cart value\n    $sequence = $sequences[$segment] ?? $sequences['new_visitor'];\n    \n    if ($cartValue > 15000) {\n        // High value carts get lower discounts\n        foreach ($sequence as &$email) {\n            $email['discount'] = max(0, $email['discount'] - 5);\n        }\n    } elseif ($cartValue < 5000) {\n        // Low value carts get higher discounts\n        foreach ($sequence as &$email) {\n            $email['discount'] = min(30, $email['discount'] + 5);\n        }\n    }\n    \n    return $sequence;\n}\n\n/**\n * Schedule individual email\n */\nfunction scheduleEmail($pdo, $abandonmentId, $email, $config, $cartData) {\n    $sendAt = date('Y-m-d H:i:s', time() + ($config['delay_minutes'] * 60));\n    \n    $stmt = $pdo->prepare(\"\n        INSERT INTO email_recovery (\n            abandonment_id, email_address, email_type, \n            email_subject, email_template, discount_percent,\n            cart_data, scheduled_at, created_at\n        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())\n    \");\n    \n    $stmt->execute([\n        $abandonmentId,\n        $email,\n        $config['type'],\n        $config['subject'],\n        $config['template'],\n        $config['discount'],\n        json_encode($cartData),\n        $sendAt\n    ]);\n    \n    return $pdo->lastInsertId();\n}\n\n/**\n * Process scheduled emails (called by cron job)\n */\nfunction processScheduledEmails($pdo) {\n    // Get emails ready to send\n    $stmt = $pdo->query(\"\n        SELECT er.*, ca.session_id, ca.user_id, ca.cart_value\n        FROM email_recovery er\n        JOIN cart_abandonments ca ON er.abandonment_id = ca.id\n        WHERE er.sent_at IS NULL \n        AND er.scheduled_at <= NOW()\n        AND ca.recovered = FALSE\n        ORDER BY er.scheduled_at ASC\n        LIMIT 50\n    \");\n    \n    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);\n    \n    foreach ($emails as $email) {\n        try {\n            $sent = sendRecoveryEmail($email);\n            \n            if ($sent) {\n                // Mark as sent\n                $updateStmt = $pdo->prepare(\"\n                    UPDATE email_recovery \n                    SET sent_at = NOW() \n                    WHERE id = ?\n                \");\n                $updateStmt->execute([$email['id']]);\n                \n                // Update abandonment record\n                $updateAbandonmentStmt = $pdo->prepare(\"\n                    UPDATE cart_abandonments \n                    SET recovery_email_sent = TRUE \n                    WHERE id = ?\n                \");\n                $updateAbandonmentStmt->execute([$email['abandonment_id']]);\n            }\n        } catch (Exception $e) {\n            error_log(\"Failed to send recovery email {$email['id']}: \" . $e->getMessage());\n        }\n    }\n    \n    return count($emails);\n}\n\n/**\n * Send recovery email\n */\nfunction sendRecoveryEmail($emailData) {\n    $cartData = json_decode($emailData['cart_data'], true);\n    $recoveryUrl = generateRecoveryUrl($emailData['session_id'], $emailData['user_id']);\n    \n    // Generate email content\n    $emailContent = generateEmailContent(\n        $emailData['email_template'], \n        $cartData, \n        $emailData['discount_percent'],\n        $recoveryUrl\n    );\n    \n    // Send email (implement your email sending logic here)\n    // This could be SMTP, SendGrid, Mailgun, etc.\n    $sent = sendEmail(\n        $emailData['email_address'],\n        $emailData['email_subject'],\n        $emailContent['html'],\n        $emailContent['text']\n    );\n    \n    return $sent;\n}\n\n/**\n * Generate recovery URL with tracking\n */\nfunction generateRecoveryUrl($sessionId, $userId) {\n    $token = generateSecureToken($sessionId, $userId);\n    return \"https://fractalmerch.com.ar/cart-recovery.php?token={$token}\";\n}\n\n/**\n * Generate secure token for cart recovery\n */\nfunction generateSecureToken($sessionId, $userId) {\n    $data = $sessionId . '|' . $userId . '|' . time();\n    return base64_encode(openssl_encrypt($data, 'AES-256-CBC', 'your-secret-key', 0, 'your-iv-here-16b'));\n}\n\n/**\n * Generate email content based on template\n */\nfunction generateEmailContent($template, $cartData, $discountPercent, $recoveryUrl) {\n    $items = $cartData['items'] ?? [];\n    $total = $cartData['total'] ?? 0;\n    \n    $templates = [\n        'welcome_abandonment' => [\n            'html' => generateWelcomeAbandonmentHTML($items, $total, $discountPercent, $recoveryUrl),\n            'text' => generateWelcomeAbandonmentText($items, $total, $discountPercent, $recoveryUrl)\n        ],\n        'first_reminder' => [\n            'html' => generateFirstReminderHTML($items, $total, $discountPercent, $recoveryUrl),\n            'text' => generateFirstReminderText($items, $total, $discountPercent, $recoveryUrl)\n        ],\n        'final_chance' => [\n            'html' => generateFinalChanceHTML($items, $total, $discountPercent, $recoveryUrl),\n            'text' => generateFinalChanceText($items, $total, $discountPercent, $recoveryUrl)\n        ]\n        // Add more templates as needed\n    ];\n    \n    return $templates[$template] ?? $templates['welcome_abandonment'];\n}\n\n/**\n * Generate welcome abandonment email HTML\n */\nfunction generateWelcomeAbandonmentHTML($items, $total, $discount, $recoveryUrl) {\n    $discountCode = $discount > 0 ? 'VUELVE' . $discount : '';\n    \n    $itemsHtml = '';\n    foreach ($items as $item) {\n        $itemsHtml .= \"\n            <tr>\n                <td style='padding: 10px; border-bottom: 1px solid #eee;'>\n                    <strong>{$item['name']}</strong><br>\n                    Cantidad: {$item['quantity']}<br>\n                    Precio: $\" . number_format($item['price'], 0, ',', '.') . \"\n                </td>\n            </tr>\n        \";\n    }\n    \n    return \"\n    <!DOCTYPE html>\n    <html>\n    <head>\n        <meta charset='UTF-8'>\n        <title>Tu carrito te está esperando</title>\n    </head>\n    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>\n        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>\n            <div style='text-align: center; margin-bottom: 30px;'>\n                <img src='https://fractalmerch.com.ar/assets/images/icon.png' alt='FractalMerch' style='width: 80px;'>\n                <h1 style='color: #FF9500; margin: 20px 0;'>¡Tu carrito te está esperando!</h1>\n            </div>\n            \n            <p>Hola,</p>\n            <p>Vimos que agregaste algunos productos increíbles a tu carrito pero no completaste la compra. ¡No te preocupes, guardamos todo para ti!</p>\n            \n            <div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n                <h3>Productos en tu carrito:</h3>\n                <table style='width: 100%;'>\n                    {$itemsHtml}\n                </table>\n                <div style='text-align: right; margin-top: 15px; font-size: 18px; font-weight: bold;'>\n                    Total: $\" . number_format($total, 0, ',', '.') . \"\n                </div>\n            </div>\n            \n            \" . ($discount > 0 ? \"\n            <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center;'>\n                <h3 style='color: #856404; margin: 0 0 10px 0;'>🎉 ¡Oferta especial para ti!</h3>\n                <p style='margin: 0; font-size: 16px;'>Usa el código <strong>{$discountCode}</strong> y obtén <strong>{$discount}% de descuento</strong></p>\n            </div>\n            \" : '') . \"\n            \n            <div style='text-align: center; margin: 30px 0;'>\n                <a href='{$recoveryUrl}' style='background: #FF9500; color: white; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;'>Completar mi pedido</a>\n            </div>\n            \n            <p>Si tienes alguna pregunta, no dudes en contactarnos. ¡Estamos aquí para ayudarte!</p>\n            \n            <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px;'>\n                <p>FractalMerch - Donde tu creatividad cobra vida</p>\n                <p>Si no deseas recibir estos emails, <a href='#'>haz clic aquí</a></p>\n            </div>\n        </div>\n    </body>\n    </html>\n    \";\n}\n\n/**\n * Generate welcome abandonment email text version\n */\nfunction generateWelcomeAbandonmentText($items, $total, $discount, $recoveryUrl) {\n    $itemsText = '';\n    foreach ($items as $item) {\n        $itemsText .= \"- {$item['name']} (Cantidad: {$item['quantity']}, Precio: $\" . number_format($item['price'], 0, ',', '.') . \")\\n\";\n    }\n    \n    $discountText = $discount > 0 ? \"\\n\\n🎉 ¡OFERTA ESPECIAL!\\nUsa el código VUELVE{$discount} y obtén {$discount}% de descuento\\n\" : '';\n    \n    return \"\nFRACTALMERCH - Tu carrito te está esperando\n\nHola,\n\nVimos que agregaste algunos productos increíbles a tu carrito pero no completaste la compra. ¡No te preocupes, guardamos todo para ti!\n\nProductos en tu carrito:\n{$itemsText}\nTotal: $\" . number_format($total, 0, ',', '.') . \"\n{$discountText}\n\nCompleta tu pedido aquí: {$recoveryUrl}\n\nSi tienes alguna pregunta, no dudes en contactarnos. ¡Estamos aquí para ayudarte!\n\nFractalMerch - Donde tu creatividad cobra vida\n\";\n}\n\n/**\n * Send email using configured service\n */\nfunction sendEmail($to, $subject, $htmlBody, $textBody) {\n    // Implement your email sending logic here\n    // This is a placeholder - replace with actual email service\n    \n    // Example using PHP mail() function (not recommended for production)\n    $headers = [\n        'MIME-Version: 1.0',\n        'Content-type: text/html; charset=utf-8',\n        'From: FractalMerch <noreply@fractalmerch.com.ar>',\n        'Reply-To: support@fractalmerch.com.ar',\n        'X-Mailer: PHP/' . phpversion()\n    ];\n    \n    return mail($to, $subject, $htmlBody, implode(\"\\r\\n\", $headers));\n}\n\n// Add more template generation functions as needed...\nfunction generateFirstReminderHTML($items, $total, $discount, $recoveryUrl) {\n    // Similar structure to welcome abandonment but with different messaging\n    return generateWelcomeAbandonmentHTML($items, $total, $discount, $recoveryUrl);\n}\n\nfunction generateFirstReminderText($items, $total, $discount, $recoveryUrl) {\n    return generateWelcomeAbandonmentText($items, $total, $discount, $recoveryUrl);\n}\n\nfunction generateFinalChanceHTML($items, $total, $discount, $recoveryUrl) {\n    // More urgent messaging for final chance emails\n    return generateWelcomeAbandonmentHTML($items, $total, $discount, $recoveryUrl);\n}\n\nfunction generateFinalChanceText($items, $total, $discount, $recoveryUrl) {\n    return generateWelcomeAbandonmentText($items, $total, $discount, $recoveryUrl);\n}\n?>