-- Script SQL para crear tablas necesarias para el sistema de seguridad CSRF
-- y el procesamiento de órdenes del e-commerce

-- Tabla para órdenes/pedidos
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NULL,
    
    -- Datos de contacto
    contact_email VARCHAR(255) NOT NULL,
    contact_first_name VARCHAR(100) NOT NULL,
    contact_last_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    
    -- Datos de envío
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_province VARCHAR(100) NOT NULL,
    shipping_postal_code VARCHAR(10) NOT NULL,
    shipping_method VARCHAR(50),
    
    -- Datos de pago
    payment_method VARCHAR(50) NOT NULL,
    payment_transaction_id VARCHAR(100),
    
    -- Totales
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    
    -- Estado y timestamps
    order_status ENUM('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indices
    INDEX idx_user_id (user_id),
    INDEX idx_order_status (order_status),
    INDEX idx_created_at (created_at),
    
    -- Foreign key constraint (si existe tabla users)
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabla para items de órdenes
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    
    -- Datos del producto (snapshot al momento de la compra)
    product_name VARCHAR(255) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    product_size VARCHAR(50),
    product_color VARCHAR(50),
    product_image_url VARCHAR(500),
    
    -- Total del item
    item_total DECIMAL(10,2) NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indices
    INDEX idx_order_id (order_id),
    
    -- Foreign key constraint
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Tabla para log de intentos de seguridad (opcional)
CREATE TABLE IF NOT EXISTS security_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    event_type ENUM('csrf_invalid', 'csrf_missing', 'invalid_method', 'role_violation', 'login_attempt') NOT NULL,
    event_description TEXT,
    request_uri VARCHAR(500),
    post_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indices
    INDEX idx_user_id (user_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at),
    
    -- Foreign key constraint (si existe tabla users)
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabla para tokens de sesión (opcional - para mejor control de sesiones)
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(128) UNIQUE NOT NULL,
    csrf_token VARCHAR(64),
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indices
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at),
    
    -- Foreign key constraint
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla para configuración de seguridad
CREATE TABLE IF NOT EXISTS security_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_config_key (config_key)
);

-- Insertar configuración inicial de seguridad
INSERT IGNORE INTO security_config (config_key, config_value, description) VALUES
('csrf_token_lifetime', '1800', 'Tiempo de vida del token CSRF en segundos (30 minutos)'),
('max_login_attempts', '5', 'Máximo número de intentos de login antes de bloqueo'),
('session_lifetime', '3600', 'Tiempo de vida de la sesión en segundos (1 hora)'),
('require_https', 'false', 'Requerir HTTPS para todas las peticiones'),
('log_security_events', 'true', 'Registrar eventos de seguridad en la base de datos');

-- Vista para órdenes con detalles completos
CREATE OR REPLACE VIEW order_details AS
SELECT 
    o.*,
    COUNT(oi.id) as item_count,
    GROUP_CONCAT(oi.product_name SEPARATOR ', ') as products,
    u.username,
    u.email as user_email
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN users u ON o.user_id = u.id
GROUP BY o.id;

-- Vista para estadísticas de seguridad
CREATE OR REPLACE VIEW security_stats AS
SELECT 
    DATE(created_at) as date,
    event_type,
    COUNT(*) as event_count,
    COUNT(DISTINCT ip_address) as unique_ips,
    COUNT(DISTINCT user_id) as unique_users
FROM security_log 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at), event_type
ORDER BY date DESC, event_count DESC;

-- Procedimiento para limpiar tokens expirados
DELIMITER //
CREATE PROCEDURE CleanExpiredTokens()
BEGIN
    -- Limpiar sesiones expiradas
    DELETE FROM user_sessions WHERE expires_at < NOW();
    
    -- Log de limpieza
    INSERT INTO security_log (ip_address, event_type, event_description) 
    VALUES ('system', 'csrf_invalid', 'Limpieza automática de tokens expirados');
END //
DELIMITER ;

-- Procedimiento para registrar eventos de seguridad
DELIMITER //
CREATE PROCEDURE LogSecurityEvent(
    IN p_user_id INT,
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT,
    IN p_event_type VARCHAR(50),
    IN p_description TEXT,
    IN p_request_uri VARCHAR(500),
    IN p_post_data JSON
)
BEGIN
    INSERT INTO security_log (
        user_id, ip_address, user_agent, event_type, 
        event_description, request_uri, post_data
    ) VALUES (
        p_user_id, p_ip_address, p_user_agent, p_event_type,
        p_description, p_request_uri, p_post_data
    );
END //
DELIMITER ;

-- Trigger para actualizar timestamps en orders
DELIMITER //
CREATE TRIGGER update_order_timestamp 
    BEFORE UPDATE ON orders
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END //
DELIMITER ;

-- Comentarios para documentación
ALTER TABLE orders COMMENT = 'Tabla principal de órdenes/pedidos del e-commerce con protección CSRF';
ALTER TABLE order_items COMMENT = 'Items individuales de cada orden con snapshot de productos';
ALTER TABLE security_log COMMENT = 'Log de eventos de seguridad y intentos de ataques';
ALTER TABLE user_sessions COMMENT = 'Sesiones de usuario con tokens CSRF asociados';
ALTER TABLE security_config COMMENT = 'Configuración de parámetros de seguridad del sistema';

-- Mostrar tablas creadas
SELECT 
    TABLE_NAME as 'Tabla Creada',
    TABLE_COMMENT as 'Descripción',
    CREATE_TIME as 'Fecha Creación'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN ('orders', 'order_items', 'security_log', 'user_sessions', 'security_config')
ORDER BY TABLE_NAME;