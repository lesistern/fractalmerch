-- database/add_products_table.sql

-- Tabla de Productos
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    cost DECIMAL(10, 2) DEFAULT 0.00,
    sku VARCHAR(100) UNIQUE NOT NULL,
    main_image_url VARCHAR(255),
    category_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabla de Variantes de Productos (para talles, colores, medidas, stock)
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(50) NULL, -- Ej: "S", "M", "L", "XL"
    color VARCHAR(50) NULL, -- Ej: "Rojo", "Azul", "#FF0000"
    measure VARCHAR(50) NULL, -- Ej: "330ml" para tazas, "40x40cm" para almohadas
    stock INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE (product_id, size, color, measure) -- Asegura que no haya variantes duplicadas
);

-- Datos de ejemplo para la tabla 'products'
INSERT INTO products (name, description, price, cost, sku, main_image_url, category_id) VALUES
('Remera Básica', 'Remera de algodón 100% peinado, ideal para uso diario y personalización.', 5999.00, 2500.00, 'REM001', 'assets/images/remera-frente.png', NULL),
('Buzo Canguro', 'Buzo con capucha y bolsillo frontal, mezcla de algodón y poliéster.', 12999.00, 6000.00, 'BUZ001', 'assets/images/buzo.png', NULL),
('Taza Cerámica 330ml', 'Taza de cerámica blanca de alta calidad, apta para microondas y lavavajillas.', 3499.00, 1200.00, 'TAZ001', 'assets/images/taza.png', NULL),
('Mouse Pad Ergonómico', 'Mouse pad con base antideslizante y superficie suave para mayor precisión.', 2999.00, 1000.00, 'MOU001', 'assets/images/mousepad.png', NULL);

-- Datos de ejemplo para la tabla 'product_variants'
-- Variantes para Remera Básica (REM001)
INSERT INTO product_variants (product_id, size, color, stock) VALUES
((SELECT id FROM products WHERE sku = 'REM001'), 'S', 'Blanco', 50),
((SELECT id FROM products WHERE sku = 'REM001'), 'M', 'Blanco', 75),
((SELECT id FROM products WHERE sku = 'REM001'), 'L', 'Blanco', 60),
((SELECT id FROM products WHERE sku = 'REM001'), 'XL', 'Blanco', 40),
((SELECT id FROM products WHERE sku = 'REM001'), 'S', 'Negro', 30),
((SELECT id FROM products WHERE sku = 'REM001'), 'M', 'Negro', 45),
((SELECT id FROM products WHERE sku = 'REM001'), 'L', 'Negro', 35);

-- Variantes para Buzo Canguro (BUZ001)
INSERT INTO product_variants (product_id, size, color, stock) VALUES
((SELECT id FROM products WHERE sku = 'BUZ001'), 'M', 'Gris', 20),
((SELECT id FROM products WHERE sku = 'BUZ001'), 'L', 'Gris', 25),
((SELECT id FROM products WHERE sku = 'BUZ001'), 'XL', 'Gris', 15);

-- Variantes para Taza Cerámica 330ml (TAZ001)
INSERT INTO product_variants (product_id, measure, stock) VALUES
((SELECT id FROM products WHERE sku = 'TAZ001'), '330ml', 100);

-- Variantes para Mouse Pad Ergonómico (MOU001)
INSERT INTO product_variants (product_id, stock) VALUES
((SELECT id FROM products WHERE sku = 'MOU001'), 80);