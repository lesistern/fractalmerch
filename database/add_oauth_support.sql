-- Agregar soporte para OAuth a la tabla users
-- Ejecutar en phpMyAdmin o MySQL

-- Agregar columnas para OAuth
ALTER TABLE users 
ADD COLUMN oauth_provider VARCHAR(50) NULL AFTER password,
ADD COLUMN oauth_id VARCHAR(255) NULL AFTER oauth_provider,
ADD COLUMN oauth_token TEXT NULL AFTER oauth_id,
ADD COLUMN avatar_url VARCHAR(500) NULL AFTER oauth_token,
ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER avatar_url,
ADD COLUMN last_login TIMESTAMP NULL AFTER email_verified,
ADD COLUMN account_type ENUM('local', 'oauth') DEFAULT 'local' AFTER last_login;

-- Crear índices para optimizar búsquedas OAuth
ALTER TABLE users 
ADD INDEX idx_oauth_provider_id (oauth_provider, oauth_id),
ADD INDEX idx_email_verified (email_verified),
ADD INDEX idx_account_type (account_type);

-- Hacer que la contraseña sea opcional para usuarios OAuth
ALTER TABLE users 
MODIFY COLUMN password VARCHAR(255) NULL;

-- Crear tabla para tokens de OAuth (refresh tokens, etc.)
CREATE TABLE oauth_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(50) NOT NULL,
    access_token TEXT,
    refresh_token TEXT,
    token_expires TIMESTAMP NULL,
    scope TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_provider (user_id, provider)
);

-- Crear tabla para configuración OAuth
CREATE TABLE oauth_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(50) NOT NULL UNIQUE,
    client_id VARCHAR(255) NOT NULL,
    client_secret VARCHAR(255) NOT NULL,
    redirect_uri VARCHAR(500) NOT NULL,
    scope TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar configuración inicial para proveedores OAuth
INSERT INTO oauth_config (provider, client_id, client_secret, redirect_uri, scope) VALUES
('google', 'YOUR_GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_SECRET', 'https://fractalmerch.com.ar/auth/google/callback', 'openid email profile'),
('facebook', 'YOUR_FACEBOOK_APP_ID', 'YOUR_FACEBOOK_APP_SECRET', 'https://fractalmerch.com.ar/auth/facebook/callback', 'email,public_profile'),
('github', 'YOUR_GITHUB_CLIENT_ID', 'YOUR_GITHUB_CLIENT_SECRET', 'https://fractalmerch.com.ar/auth/github/callback', 'user:email'),
('apple', 'YOUR_APPLE_CLIENT_ID', 'YOUR_APPLE_CLIENT_SECRET', 'https://fractalmerch.com.ar/auth/apple/callback', 'name email'),
('microsoft', 'YOUR_MICROSOFT_CLIENT_ID', 'YOUR_MICROSOFT_CLIENT_SECRET', 'https://fractalmerch.com.ar/auth/microsoft/callback', 'openid email profile');

-- Crear tabla para audit log de logins
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    provider VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent TEXT,
    success BOOLEAN,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_provider (email, provider),
    INDEX idx_ip_success (ip_address, success),
    INDEX idx_created_at (created_at)
);