-- Crear tabla de productos
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cost DECIMAL(10,2) DEFAULT 0.00,
    sku VARCHAR(100) UNIQUE,
    main_image_url VARCHAR(500),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category_id),
    INDEX idx_sku (sku),
    INDEX idx_created (created_at)
);

-- Crear tabla de variantes de productos
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(50),
    color VARCHAR(50),
    measure VARCHAR(50),
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_stock (stock)
);

-- Insertar algunos productos de ejemplo
INSERT INTO products (name, description, price, cost, sku, main_image_url, category_id) VALUES
('Remera Personalizada', 'Remera de algodón 100% personalizable con tu diseño', 5999.00, 2500.00, 'REM-001', 'assets/images/products/remera.svg', 1),
('Buzo con Capucha', 'Buzo de algodón con capucha, ideal para personalizar', 12999.00, 6000.00, 'BUZ-001', 'assets/images/products/buzo.svg', 1),
('Taza Mágica', 'Taza que cambia de color con el calor, personalizable', 3499.00, 1200.00, 'TAZ-001', 'assets/images/products/taza.svg', 2);

-- Insertar variantes de ejemplo
INSERT INTO product_variants (product_id, size, color, stock) VALUES
(1, 'S', 'Blanco', 15),
(1, 'M', 'Blanco', 20),
(1, 'L', 'Blanco', 18),
(1, 'XL', 'Blanco', 10),
(1, 'S', 'Negro', 12),
(1, 'M', 'Negro', 25),
(1, 'L', 'Negro', 22),
(1, 'XL', 'Negro', 8),
(2, 'S', 'Gris', 10),
(2, 'M', 'Gris', 15),
(2, 'L', 'Gris', 12),
(2, 'XL', 'Gris', 6),
(3, 'Único', 'Blanco', 30);

-- Verificar las tablas creadas
SELECT 'Productos creados:' as info;
SELECT id, name, price, sku FROM products;

SELECT 'Variantes creadas:' as info;
SELECT pv.id, p.name as product_name, pv.size, pv.color, pv.stock 
FROM product_variants pv 
JOIN products p ON pv.product_id = p.id;