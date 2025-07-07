-- Sistema de Recomendaciones - Database Schema
-- Ejecutar después de las tablas principales de e-commerce

-- Tabla de vistas de productos (tracking de comportamiento)
CREATE TABLE IF NOT EXISTS product_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    view_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    view_duration INT DEFAULT 0, -- segundos
    source_page VARCHAR(100) NULL, -- home, search, category, etc.
    user_agent TEXT NULL,
    ip_address VARCHAR(45) NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_views_user (user_id),
    INDEX idx_views_session (session_id),
    INDEX idx_views_product (product_id),
    INDEX idx_views_timestamp (view_timestamp)
);

-- Tabla de productos agregados al carrito (tracking)
CREATE TABLE IF NOT EXISTS cart_additions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    variant_details JSON NULL, -- size, color, etc.
    quantity INT DEFAULT 1,
    added_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    removed_timestamp TIMESTAMP NULL,
    purchased_in_order_id INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (purchased_in_order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_cart_user (user_id),
    INDEX idx_cart_session (session_id),
    INDEX idx_cart_product (product_id),
    INDEX idx_cart_timestamp (added_timestamp)
);

-- Tabla de productos comprados juntos (para "Frequently Bought Together")
CREATE TABLE IF NOT EXISTS products_bought_together (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_a_id INT NOT NULL,
    product_b_id INT NOT NULL,
    frequency_count INT DEFAULT 1,
    confidence_score DECIMAL(5,4) DEFAULT 0.0000, -- 0-1 score
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_a_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (product_b_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_pair (product_a_id, product_b_id),
    INDEX idx_bought_together_a (product_a_id),
    INDEX idx_bought_together_b (product_b_id),
    INDEX idx_bought_together_score (confidence_score)
);

-- Tabla de recomendaciones personalizadas por usuario
CREATE TABLE IF NOT EXISTS user_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recommended_product_id INT NOT NULL,
    recommendation_type ENUM('collaborative', 'content_based', 'hybrid', 'trending', 'seasonal') NOT NULL,
    score DECIMAL(5,4) NOT NULL, -- 0-1 score
    reason VARCHAR(255) NULL, -- "Porque compraste X", "Trending ahora", etc.
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    clicked BOOLEAN DEFAULT FALSE,
    clicked_at TIMESTAMP NULL,
    purchased BOOLEAN DEFAULT FALSE,
    purchased_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recommended_product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_type (user_id, recommended_product_id, recommendation_type),
    INDEX idx_user_recommendations (user_id),
    INDEX idx_recommendations_score (score),
    INDEX idx_recommendations_type (recommendation_type)
);

-- Tabla de similitud entre productos (content-based filtering)
CREATE TABLE IF NOT EXISTS product_similarity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_a_id INT NOT NULL,
    product_b_id INT NOT NULL,
    similarity_score DECIMAL(5,4) NOT NULL, -- 0-1 score
    similarity_factors JSON NULL, -- price_range, category, features, etc.
    last_calculated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_a_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (product_b_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_similarity_pair (product_a_id, product_b_id),
    INDEX idx_similarity_a (product_a_id),
    INDEX idx_similarity_b (product_b_id),
    INDEX idx_similarity_score (similarity_score)
);

-- Tabla de categorías de productos (extendida para recomendaciones)
CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    parent_id INT NULL,
    description TEXT NULL,
    seasonal_weight DECIMAL(3,2) DEFAULT 1.00, -- para seasonal recommendations
    trending_multiplier DECIMAL(3,2) DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
);

-- Tabla de atributos de productos para content-based filtering
CREATE TABLE IF NOT EXISTS product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_name VARCHAR(100) NOT NULL, -- "material", "season", "gender", "style"
    attribute_value VARCHAR(255) NOT NULL,
    weight DECIMAL(3,2) DEFAULT 1.00, -- importancia del atributo
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_attribute (product_id, attribute_name),
    INDEX idx_attributes_product (product_id),
    INDEX idx_attributes_name (attribute_name)
);

-- Insertar datos iniciales para el catálogo actual
INSERT INTO product_categories (name, description, seasonal_weight, trending_multiplier) VALUES
('Ropa', 'Productos de vestimenta personalizable', 1.20, 1.15),
('Accesorios', 'Accesorios personalizables para uso diario', 1.00, 1.10),
('Hogar', 'Productos para el hogar y descanso', 0.90, 1.05),
('Oficina', 'Productos para oficina y trabajo', 1.10, 1.20),
('Tecnología', 'Accesorios tecnológicos personalizables', 1.00, 1.25);

-- Insertar atributos para productos existentes
INSERT INTO product_attributes (product_id, attribute_name, attribute_value, weight) VALUES
-- Remera (ID: 1)
(1, 'category', 'Ropa', 1.0),
(1, 'material', 'Algodón', 0.8),
(1, 'season', 'Todo el año', 1.0),
(1, 'gender', 'Unisex', 0.7),
(1, 'style', 'Casual', 0.9),
(1, 'price_range', 'Medio', 0.8),

-- Buzo (ID: 2)  
(2, 'category', 'Ropa', 1.0),
(2, 'material', 'Algodón-Poliéster', 0.8),
(2, 'season', 'Invierno', 1.2),
(2, 'gender', 'Unisex', 0.7),
(2, 'style', 'Casual-Deportivo', 0.9),
(2, 'price_range', 'Alto', 0.9),

-- Taza (ID: 3)
(3, 'category', 'Hogar', 1.0),
(3, 'material', 'Cerámica', 0.8),
(3, 'season', 'Todo el año', 1.0),
(3, 'gender', 'Unisex', 0.5),
(3, 'style', 'Funcional', 0.8),
(3, 'price_range', 'Bajo', 0.7),

-- Mouse Pad (ID: 4)
(4, 'category', 'Tecnología', 1.0),
(4, 'material', 'Goma-Tela', 0.8),
(4, 'season', 'Todo el año', 1.0),
(4, 'gender', 'Unisex', 0.5),
(4, 'style', 'Funcional', 0.9),
(4, 'price_range', 'Bajo', 0.7),

-- Funda (ID: 5)
(5, 'category', 'Tecnología', 1.0),
(5, 'material', 'Silicona', 0.8),
(5, 'season', 'Todo el año', 1.0),
(5, 'gender', 'Unisex', 0.5),
(5, 'style', 'Protectivo', 0.9),
(5, 'price_range', 'Medio', 0.8),

-- Almohada (ID: 6)
(6, 'category', 'Hogar', 1.0),
(6, 'material', 'Algodón-Relleno', 0.8),
(6, 'season', 'Todo el año', 1.0),
(6, 'gender', 'Unisex', 0.5),
(6, 'style', 'Confort', 0.9),
(6, 'price_range', 'Medio-Alto', 0.8);

-- Insertar similitudes iniciales entre productos
INSERT INTO product_similarity (product_a_id, product_b_id, similarity_score, similarity_factors) VALUES
-- Remera similar a Buzo (ambos ropa)
(1, 2, 0.85, '{"category": 1.0, "material": 0.8, "style": 0.9, "gender": 1.0}'),
(2, 1, 0.85, '{"category": 1.0, "material": 0.8, "style": 0.9, "gender": 1.0}'),

-- Taza similar a Mouse Pad (productos de escritorio/oficina)
(3, 4, 0.75, '{"usage": 0.9, "price_range": 0.7, "gender": 1.0, "style": 0.6}'),
(4, 3, 0.75, '{"usage": 0.9, "price_range": 0.7, "gender": 1.0, "style": 0.6}'),

-- Mouse Pad similar a Funda (ambos tecnología)
(4, 5, 0.70, '{"category": 1.0, "usage": 0.8, "gender": 1.0, "style": 0.4}'),
(5, 4, 0.70, '{"category": 1.0, "usage": 0.8, "gender": 1.0, "style": 0.4}'),

-- Buzo similar a Almohada (productos de confort)
(2, 6, 0.60, '{"comfort": 0.9, "season": 0.5, "price_range": 0.7, "style": 0.3}'),
(6, 2, 0.60, '{"comfort": 0.9, "season": 0.5, "price_range": 0.7, "style": 0.3}'),

-- Remera similar a Taza (precio accesible)
(1, 3, 0.55, '{"price_range": 0.8, "gender": 1.0, "season": 1.0, "category": 0.0}'),
(3, 1, 0.55, '{"price_range": 0.8, "gender": 1.0, "season": 1.0, "category": 0.0}');

-- Crear stored procedures para calcular recomendaciones

DELIMITER //

-- Procedure para actualizar productos comprados juntos
CREATE PROCEDURE UpdateProductsBoughtTogether()
BEGIN
    -- Limpiar datos antiguos (más de 30 días)
    DELETE FROM products_bought_together WHERE last_updated < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Insertar/actualizar productos comprados juntos desde order_items
    INSERT INTO products_bought_together (product_a_id, product_b_id, frequency_count, confidence_score)
    SELECT 
        oi1.product_id as product_a_id,
        oi2.product_id as product_b_id,
        COUNT(*) as frequency_count,
        (COUNT(*) / (SELECT COUNT(DISTINCT order_id) FROM order_items WHERE product_id = oi1.product_id)) as confidence_score
    FROM order_items oi1
    JOIN order_items oi2 ON oi1.order_id = oi2.order_id AND oi1.product_id < oi2.product_id
    WHERE oi1.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
    GROUP BY oi1.product_id, oi2.product_id
    HAVING COUNT(*) >= 2
    ON DUPLICATE KEY UPDATE
        frequency_count = frequency_count + VALUES(frequency_count),
        confidence_score = VALUES(confidence_score),
        last_updated = NOW();
END //

-- Procedure para generar recomendaciones de usuario
CREATE PROCEDURE GenerateUserRecommendations(IN target_user_id INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE current_product_id INT;
    
    -- Cursor para productos que el usuario ha visto pero no comprado
    DECLARE product_cursor CURSOR FOR 
        SELECT DISTINCT pv.product_id
        FROM product_views pv
        WHERE pv.user_id = target_user_id
        AND pv.product_id NOT IN (
            SELECT DISTINCT oi.product_id 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE o.user_id = target_user_id
        )
        AND pv.view_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Limpiar recomendaciones antiguas del usuario
    DELETE FROM user_recommendations WHERE user_id = target_user_id;
    
    OPEN product_cursor;
    
    read_loop: LOOP
        FETCH product_cursor INTO current_product_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Insertar recomendaciones basadas en productos similares
        INSERT IGNORE INTO user_recommendations (user_id, recommended_product_id, recommendation_type, score, reason)
        SELECT 
            target_user_id,
            ps.product_b_id,
            'content_based',
            ps.similarity_score * 0.8, -- reducir score para content-based
            CONCAT('Porque viste ', (SELECT name FROM products WHERE id = current_product_id))
        FROM product_similarity ps
        WHERE ps.product_a_id = current_product_id
        AND ps.similarity_score > 0.5
        AND ps.product_b_id NOT IN (
            SELECT DISTINCT oi.product_id 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE o.user_id = target_user_id
        );
        
    END LOOP;
    
    CLOSE product_cursor;
    
    -- Agregar recomendaciones trending (productos más vistos últimamente)
    INSERT IGNORE INTO user_recommendations (user_id, recommended_product_id, recommendation_type, score, reason)
    SELECT 
        target_user_id,
        pv.product_id,
        'trending',
        0.6 + (view_count / 100.0), -- base score + popularity bonus
        'Trending ahora'
    FROM (
        SELECT 
            product_id, 
            COUNT(*) as view_count
        FROM product_views 
        WHERE view_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY product_id
        ORDER BY view_count DESC
        LIMIT 3
    ) pv
    WHERE pv.product_id NOT IN (
        SELECT DISTINCT oi.product_id 
        FROM order_items oi 
        JOIN orders o ON oi.order_id = o.id 
        WHERE o.user_id = target_user_id
    );
    
END //

DELIMITER ;