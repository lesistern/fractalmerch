-- Funcionalidades Premium para FractalMerch
-- Sistema completo de e-commerce avanzado

-- 1. Tabla de métodos de pago
CREATE TABLE IF NOT EXISTS user_payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('credit_card', 'debit_card', 'paypal', 'mercadopago', 'bank_transfer', 'crypto') NOT NULL,
    provider VARCHAR(50) NOT NULL, -- Visa, MasterCard, PayPal, etc.
    last_four_digits VARCHAR(4) NULL,
    cardholder_name VARCHAR(100) NULL,
    expiry_month INT NULL,
    expiry_year INT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    is_verified BOOLEAN DEFAULT FALSE,
    token VARCHAR(255) NULL, -- Token del procesador de pagos
    metadata JSON NULL, -- Datos adicionales del método
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_payment_methods_user (user_id),
    INDEX idx_payment_methods_default (is_default)
);

-- 2. Tabla de cuentas OAuth vinculadas
CREATE TABLE IF NOT EXISTS user_oauth_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider ENUM('google', 'facebook', 'apple', 'microsoft', 'github', 'twitter') NOT NULL,
    provider_id VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    name VARCHAR(255) NULL,
    avatar_url VARCHAR(500) NULL,
    access_token TEXT NULL,
    refresh_token TEXT NULL,
    token_expires_at DATETIME NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    connected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_provider_account (provider, provider_id),
    INDEX idx_oauth_accounts_user (user_id),
    INDEX idx_oauth_accounts_provider (provider)
);

-- 3. Tabla de suscripciones premium
CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_type ENUM('basic', 'premium', 'vip', 'enterprise') NOT NULL,
    status ENUM('active', 'cancelled', 'expired', 'suspended') DEFAULT 'active',
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'ARS',
    billing_cycle ENUM('monthly', 'quarterly', 'yearly') NOT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    payment_method_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES user_payment_methods(id) ON DELETE SET NULL,
    INDEX idx_subscriptions_user (user_id),
    INDEX idx_subscriptions_status (status)
);

-- 4. Tabla de cupones globales (administrador)
CREATE TABLE IF NOT EXISTS global_coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    type ENUM('percentage', 'fixed_amount', 'free_shipping', 'buy_x_get_y') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    max_discount_amount DECIMAL(10,2) NULL,
    max_uses INT NULL,
    max_uses_per_user INT DEFAULT 1,
    current_uses INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    valid_from DATETIME NOT NULL,
    valid_until DATETIME NOT NULL,
    applicable_products JSON NULL, -- IDs de productos aplicables
    applicable_categories JSON NULL, -- IDs de categorías aplicables
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_global_coupons_code (code),
    INDEX idx_global_coupons_active (is_active),
    INDEX idx_global_coupons_dates (valid_from, valid_until)
);

-- 5. Tabla de uso de cupones
CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    coupon_id INT NOT NULL,
    order_id INT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES global_coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_coupon_usage_user (user_id),
    INDEX idx_coupon_usage_coupon (coupon_id)
);

-- 6. Tabla de historial de puntos detallado
CREATE TABLE IF NOT EXISTS points_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points_change INT NOT NULL, -- Positivo para ganar, negativo para gastar
    balance_after INT NOT NULL,
    type ENUM('purchase', 'review', 'referral', 'birthday', 'redemption', 'bonus', 'adjustment') NOT NULL,
    description VARCHAR(255) NOT NULL,
    reference_id INT NULL, -- ID del pedido, reseña, etc.
    reference_type ENUM('order', 'review', 'referral', 'manual') NULL,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_points_history_user (user_id),
    INDEX idx_points_history_type (type),
    INDEX idx_points_history_date (created_at)
);

-- 7. Tabla de tickets de soporte
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ticket_number VARCHAR(20) UNIQUE NOT NULL,
    category ENUM('technical', 'billing', 'product', 'shipping', 'account', 'other') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'waiting_customer', 'resolved', 'closed') DEFAULT 'open',
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    assigned_to INT NULL,
    resolved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_support_tickets_user (user_id),
    INDEX idx_support_tickets_status (status),
    INDEX idx_support_tickets_priority (priority)
);

-- 8. Tabla de respuestas de tickets
CREATE TABLE IF NOT EXISTS support_ticket_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    is_staff BOOLEAN DEFAULT FALSE,
    message TEXT NOT NULL,
    attachments JSON NULL, -- URLs de archivos adjuntos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ticket_responses_ticket (ticket_id),
    INDEX idx_ticket_responses_date (created_at)
);

-- 9. Tabla de wishlist avanzada
CREATE TABLE IF NOT EXISTS user_wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    is_default BOOLEAN DEFAULT FALSE,
    share_token VARCHAR(32) UNIQUE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_wishlists_user (user_id),
    INDEX idx_wishlists_public (is_public)
);

-- 10. Items de wishlist
CREATE TABLE IF NOT EXISTS wishlist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wishlist_id INT NOT NULL,
    product_id INT NOT NULL,
    product_variant_id INT NULL,
    notes TEXT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wishlist_id) REFERENCES user_wishlists(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    UNIQUE KEY unique_wishlist_product (wishlist_id, product_id, product_variant_id),
    INDEX idx_wishlist_items_wishlist (wishlist_id),
    INDEX idx_wishlist_items_product (product_id)
);

-- 11. Tabla de actividad del usuario
CREATE TABLE IF NOT EXISTS user_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('login', 'logout', 'purchase', 'review', 'profile_update', 'password_change', 'address_change', 'payment_method_add') NOT NULL,
    description VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_activity_log_user (user_id),
    INDEX idx_activity_log_type (activity_type),
    INDEX idx_activity_log_date (created_at)
);

-- 12. Tabla de configuraciones de usuario
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_setting (user_id, category, setting_key),
    INDEX idx_user_preferences_user (user_id),
    INDEX idx_user_preferences_category (category)
);

-- Agregar columnas adicionales a la tabla users
ALTER TABLE users 
ADD COLUMN profile_photo VARCHAR(500) NULL AFTER avatar_url,
ADD COLUMN cover_photo VARCHAR(500) NULL AFTER profile_photo,
ADD COLUMN subscription_tier ENUM('free', 'basic', 'premium', 'vip') DEFAULT 'free' AFTER loyalty_points,
ADD COLUMN profile_completion_percentage INT DEFAULT 0 AFTER subscription_tier,
ADD COLUMN referral_code VARCHAR(10) UNIQUE NULL AFTER profile_completion_percentage,
ADD COLUMN referred_by INT NULL AFTER referral_code,
ADD COLUMN two_factor_enabled BOOLEAN DEFAULT FALSE AFTER referred_by,
ADD COLUMN two_factor_secret VARCHAR(32) NULL AFTER two_factor_enabled,
ADD COLUMN last_active_at TIMESTAMP NULL AFTER two_factor_secret,
ADD COLUMN is_premium BOOLEAN DEFAULT FALSE AFTER last_active_at,
ADD COLUMN premium_until DATETIME NULL AFTER is_premium;

-- Agregar índices para las nuevas columnas
ALTER TABLE users 
ADD INDEX idx_users_subscription (subscription_tier),
ADD INDEX idx_users_referral (referral_code),
ADD INDEX idx_users_premium (is_premium),
ADD FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL;

-- Insertar configuraciones predeterminadas
INSERT INTO user_preferences (user_id, category, setting_key, setting_value) 
SELECT id, 'notifications', 'email_marketing', 'true' FROM users
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO user_preferences (user_id, category, setting_key, setting_value) 
SELECT id, 'notifications', 'order_updates', 'true' FROM users
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO user_preferences (user_id, category, setting_key, setting_value) 
SELECT id, 'privacy', 'profile_public', 'false' FROM users
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO user_preferences (user_id, category, setting_key, setting_value) 
SELECT id, 'privacy', 'activity_tracking', 'true' FROM users
ON DUPLICATE KEY UPDATE setting_value = setting_value;