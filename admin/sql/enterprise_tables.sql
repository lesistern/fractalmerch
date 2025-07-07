-- ENTERPRISE DASHBOARD TABLES
-- Professional database schema for enterprise dashboard features
-- Author: Claude Assistant
-- Version: 1.0.0 Enterprise
-- Date: 2025-01-07

-- Create database if not exists
-- CREATE DATABASE IF NOT EXISTS proyecto_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE proyecto_web;

-- =============================================================================
-- CORE ENTERPRISE TABLES
-- =============================================================================

-- Enhanced orders table with enterprise features
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    shipping_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'ARS',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    shipping_address TEXT,
    billing_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_created (created_at),
    INDEX idx_orders_total (total_amount),
    INDEX idx_orders_payment (payment_status),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items for detailed tracking
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    product_snapshot JSON, -- Store product details at time of order
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_order_items_order (order_id),
    INDEX idx_order_items_product (product_id),
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =============================================================================
-- PERFORMANCE MONITORING TABLES
-- =============================================================================

-- Session tracking for real-time analytics
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data JSON,
    
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_activity (last_activity),
    INDEX idx_sessions_ip (ip_address),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Page views for analytics
CREATE TABLE IF NOT EXISTS page_views (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(128),
    user_id INT NULL,
    url VARCHAR(500) NOT NULL,
    title VARCHAR(200),
    referrer VARCHAR(500),
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    load_time INT, -- milliseconds
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_pageviews_session (session_id),
    INDEX idx_pageviews_user (user_id),
    INDEX idx_pageviews_url (url(100)),
    INDEX idx_pageviews_created (created_at),
    INDEX idx_pageviews_ip (ip_address),
    
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Error logging for monitoring
CREATE TABLE IF NOT EXISTS error_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    error_level ENUM('emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug') NOT NULL,
    message TEXT NOT NULL,
    context JSON,
    file VARCHAR(500),
    line INT,
    user_id INT NULL,
    session_id VARCHAR(128),
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_error_logs_level (error_level),
    INDEX idx_error_logs_created (created_at),
    INDEX idx_error_logs_user (user_id),
    INDEX idx_error_logs_request (request_id),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL
);

-- Performance metrics tracking
CREATE TABLE IF NOT EXISTS performance_metrics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    metric_type ENUM('api_call', 'page_load', 'database_query', 'memory_usage', 'cpu_usage') NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    value DECIMAL(10,4) NOT NULL,
    unit VARCHAR(20), -- ms, mb, %, etc.
    context JSON,
    user_id INT NULL,
    session_id VARCHAR(128),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_perf_type (metric_type),
    INDEX idx_perf_name (metric_name),
    INDEX idx_perf_created (created_at),
    INDEX idx_perf_user (user_id),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL
);

-- =============================================================================
-- NOTIFICATION SYSTEM
-- =============================================================================

-- Notifications for real-time alerts
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL, -- NULL for system-wide notifications
    type ENUM('info', 'success', 'warning', 'error', 'system') NOT NULL DEFAULT 'info',
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'read', 'dismissed') DEFAULT 'pending',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    action_url VARCHAR(500),
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    
    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_status (status),
    INDEX idx_notifications_type (type),
    INDEX idx_notifications_priority (priority),
    INDEX idx_notifications_created (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================================================
-- AUDIT AND SECURITY TABLES
-- =============================================================================

-- Admin audit log for security tracking
CREATE TABLE IF NOT EXISTS admin_audit_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50), -- user, product, order, etc.
    resource_id INT,
    old_values JSON,
    new_values JSON,
    details JSON,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    session_id VARCHAR(128),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_resource (resource_type, resource_id),
    INDEX idx_audit_created (created_at),
    INDEX idx_audit_ip (ip_address),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL
);

-- Security events tracking
CREATE TABLE IF NOT EXISTS security_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_type ENUM('login_attempt', 'failed_login', 'account_locked', 'password_change', 'permission_denied', 'suspicious_activity') NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    details JSON,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    resolved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_security_type (event_type),
    INDEX idx_security_user (user_id),
    INDEX idx_security_ip (ip_address),
    INDEX idx_security_severity (severity),
    INDEX idx_security_created (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================================================
-- CACHING AND OPTIMIZATION TABLES
-- =============================================================================

-- Cache metadata for intelligent invalidation
CREATE TABLE IF NOT EXISTS cache_metadata (
    id VARCHAR(255) PRIMARY KEY,
    cache_key VARCHAR(255) NOT NULL,
    cache_tags JSON, -- Array of tags for group invalidation
    expires_at TIMESTAMP NOT NULL,
    size_bytes INT DEFAULT 0,
    hit_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_cache_expires (expires_at),
    INDEX idx_cache_tags (cache_tags(100)),
    INDEX idx_cache_created (created_at)
);

-- =============================================================================
-- BUSINESS INTELLIGENCE TABLES
-- =============================================================================

-- Cart items for abandoned cart analysis
CREATE TABLE IF NOT EXISTS cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(128) NOT NULL,
    user_id INT NULL,
    product_id INT NOT NULL,
    variant_id INT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_cart_session (session_id),
    INDEX idx_cart_user (user_id),
    INDEX idx_cart_product (product_id),
    INDEX idx_cart_created (created_at),
    
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Product reviews for analytics
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NULL, -- Link to verified purchase
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    verified_purchase BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_reviews_product (product_id),
    INDEX idx_reviews_user (user_id),
    INDEX idx_reviews_rating (rating),
    INDEX idx_reviews_status (status),
    INDEX idx_reviews_created (created_at),
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_user_product_order (user_id, product_id, order_id)
);

-- =============================================================================
-- VIEWS FOR OPTIMIZED QUERIES
-- =============================================================================

-- Revenue analytics view
CREATE OR REPLACE VIEW revenue_analytics AS
SELECT 
    DATE(created_at) as date,
    COUNT(*) as order_count,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value,
    COUNT(DISTINCT user_id) as unique_customers,
    SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as completed_revenue,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders
FROM orders
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Product performance view
CREATE OR REPLACE VIEW product_performance AS
SELECT 
    p.id,
    p.name,
    p.price,
    p.cost,
    (p.price - p.cost) as profit_margin,
    COUNT(oi.id) as total_sold,
    SUM(oi.quantity) as units_sold,
    SUM(oi.total) as total_revenue,
    AVG(pr.rating) as avg_rating,
    COUNT(pr.id) as review_count,
    SUM(pv.stock) as total_stock
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
LEFT JOIN product_reviews pr ON p.id = pr.product_id AND pr.status = 'approved'
LEFT JOIN product_variants pv ON p.id = pv.product_id
WHERE p.status = 'active'
GROUP BY p.id
ORDER BY total_revenue DESC;

-- User analytics view
CREATE OR REPLACE VIEW user_analytics AS
SELECT 
    u.id,
    u.username,
    u.email,
    u.created_at as registration_date,
    COUNT(DISTINCT o.id) as total_orders,
    SUM(o.total_amount) as lifetime_value,
    AVG(o.total_amount) as avg_order_value,
    MAX(o.created_at) as last_order_date,
    DATEDIFF(CURDATE(), MAX(o.created_at)) as days_since_last_order,
    COUNT(DISTINCT pr.id) as reviews_written
FROM users u
LEFT JOIN orders o ON u.id = o.user_id AND o.status = 'completed'
LEFT JOIN product_reviews pr ON u.id = pr.user_id
GROUP BY u.id
ORDER BY lifetime_value DESC;

-- =============================================================================
-- INDEXES FOR PERFORMANCE
-- =============================================================================

-- Additional performance indexes
CREATE INDEX idx_orders_completed_revenue ON orders(status, total_amount, created_at);
CREATE INDEX idx_products_active_price ON products(status, price);
CREATE INDEX idx_users_created_role ON users(created_at, role);

-- Composite indexes for common queries
CREATE INDEX idx_order_items_product_total ON order_items(product_id, total, created_at);
CREATE INDEX idx_sessions_active_users ON sessions(last_activity, user_id);
CREATE INDEX idx_pageviews_analytics ON page_views(created_at, url(100), user_id);

-- =============================================================================
-- SAMPLE DATA INSERTION (Optional for testing)
-- =============================================================================

-- Insert sample sessions for testing
INSERT IGNORE INTO sessions (id, user_id, ip_address, user_agent, last_activity) VALUES
('sess_001', 1, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', NOW()),
('sess_002', 2, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', NOW()),
('sess_003', NULL, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36', NOW());

-- Insert sample notifications
INSERT IGNORE INTO notifications (user_id, type, title, message, priority) VALUES
(1, 'info', 'Bienvenido al Dashboard', 'El sistema de dashboard empresarial está ahora activo.', 'normal'),
(NULL, 'system', 'Sistema Actualizado', 'Nueva versión del dashboard implementada con funciones empresariales.', 'high');

-- =============================================================================
-- MAINTENANCE PROCEDURES
-- =============================================================================

DELIMITER //

-- Procedure to clean old logs
CREATE PROCEDURE CleanOldLogs()
BEGIN
    -- Clean error logs older than 90 days
    DELETE FROM error_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    
    -- Clean page views older than 30 days
    DELETE FROM page_views WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Clean performance metrics older than 7 days
    DELETE FROM performance_metrics WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
    -- Clean expired sessions
    DELETE FROM sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR);
    
    -- Clean expired cache entries
    DELETE FROM cache_metadata WHERE expires_at < NOW();
    
    -- Clean old security events (keep for 1 year)
    DELETE FROM security_events WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY) AND resolved = TRUE;
END //

-- Procedure to update statistics
CREATE PROCEDURE UpdateDashboardStats()
BEGIN
    -- This could be called periodically to update materialized views or summary tables
    -- For now, it's a placeholder for future optimization
    SELECT 'Dashboard stats updated' as message;
END //

DELIMITER ;

-- =============================================================================
-- EVENTS FOR AUTOMATIC MAINTENANCE
-- =============================================================================

-- Create event to clean logs daily at 2 AM
CREATE EVENT IF NOT EXISTS daily_log_cleanup
ON SCHEDULE EVERY 1 DAY
STARTS (TIMESTAMP(CURRENT_DATE) + INTERVAL 1 DAY + INTERVAL 2 HOUR)
DO
  CALL CleanOldLogs();

-- Enable event scheduler
SET GLOBAL event_scheduler = ON;

-- =============================================================================
-- FINAL NOTES
-- =============================================================================

-- This schema provides:
-- 1. Comprehensive order and analytics tracking
-- 2. Real-time session and performance monitoring
-- 3. Notification system for alerts
-- 4. Security audit trails
-- 5. Business intelligence data structures
-- 6. Optimized indexes for performance
-- 7. Automatic maintenance procedures
--
-- The schema is designed for enterprise-level scalability and performance
-- All tables include proper foreign keys, indexes, and constraints
-- Views are provided for common analytical queries
-- Maintenance procedures help keep the database clean and performant