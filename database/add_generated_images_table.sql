-- Tabla para imágenes generadas por IA en el panel admin
CREATE TABLE IF NOT EXISTS generated_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    prompt TEXT NOT NULL,
    style VARCHAR(50) DEFAULT 'realistic',
    size VARCHAR(20) DEFAULT '1024x1024',
    category VARCHAR(50) DEFAULT 'otros',
    generated_by INT,
    is_real_image BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Índices para mejorar performance
CREATE INDEX idx_generated_images_category ON generated_images(category);
CREATE INDEX idx_generated_images_created_at ON generated_images(created_at);
CREATE INDEX idx_generated_images_generated_by ON generated_images(generated_by);

-- Insertar algunos ejemplos de placeholders
INSERT INTO generated_images (filename, prompt, style, size, category, generated_by, is_real_image) VALUES
('placeholder_logo_tech.txt', 'Logo moderno para empresa de tecnología', 'digital-art', '1024x1024', 'logos', 1, FALSE),
('placeholder_banner_ecommerce.txt', 'Banner promocional para tienda online', 'photographic', '1792x1024', 'banners', 1, FALSE),
('placeholder_product_tech.txt', 'Producto tecnológico elegante sobre fondo blanco', 'realistic', '1024x1024', 'productos', 1, FALSE);