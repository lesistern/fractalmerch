<?php
/**
 * Configuración OAuth para FractalMerch
 * 
 * Renombra este archivo a oauth.php y completa con tus credenciales
 * 
 * ⚠️ IMPORTANTE: Nunca committees las credenciales reales a Git
 * ⚠️ Agrega oauth.php al .gitignore
 */

// Configuración OAuth para diferentes entornos
$oauth_config = [
    // Entorno (development, production)
    'environment' => 'development', // Cambiar a 'production' en el servidor
    
    // URLs base según el entorno
    'base_urls' => [
        'development' => 'http://localhost/proyecto',
        'production' => 'https://fractalmerch.com.ar'
    ],
    
    // Google OAuth 2.0
    'google' => [
        'client_id' => 'TU_GOOGLE_CLIENT_ID.apps.googleusercontent.com',
        'client_secret' => 'TU_GOOGLE_CLIENT_SECRET',
        'redirect_uri' => '', // Se auto-genera según el entorno
        'scope' => 'openid email profile',
        'enabled' => true
    ],
    
    // Facebook Login
    'facebook' => [
        'app_id' => 'TU_FACEBOOK_APP_ID',
        'app_secret' => 'TU_FACEBOOK_APP_SECRET',
        'redirect_uri' => '', // Se auto-genera según el entorno
        'scope' => 'email,public_profile',
        'enabled' => true
    ],
    
    // GitHub OAuth
    'github' => [
        'client_id' => 'TU_GITHUB_CLIENT_ID',
        'client_secret' => 'TU_GITHUB_CLIENT_SECRET',
        'redirect_uri' => '', // Se auto-genera según el entorno
        'scope' => 'user:email',
        'enabled' => true
    ],
    
    // Apple Sign In
    'apple' => [
        'client_id' => 'com.fractalmerch.signin', // Tu Apple Service ID
        'team_id' => 'TU_APPLE_TEAM_ID',
        'key_id' => 'TU_APPLE_KEY_ID',
        'private_key_path' => 'path/to/AuthKey_XXXXXXXXXX.p8',
        'redirect_uri' => '', // Se auto-genera según el entorno
        'scope' => 'name email',
        'enabled' => false // Requiere configuración adicional
    ],
    
    // Microsoft OAuth 2.0
    'microsoft' => [
        'client_id' => 'TU_MICROSOFT_APPLICATION_ID',
        'client_secret' => 'TU_MICROSOFT_CLIENT_SECRET',
        'tenant_id' => 'common', // o tu tenant específico
        'redirect_uri' => '', // Se auto-genera según el entorno
        'scope' => 'openid email profile',
        'enabled' => true
    ]
];

// Auto-generar redirect URIs según el entorno
$base_url = $oauth_config['base_urls'][$oauth_config['environment']];

foreach ($oauth_config as $provider => &$config) {
    if (is_array($config) && isset($config['redirect_uri'])) {
        $config['redirect_uri'] = $base_url . '/auth/oauth-callback.php?provider=' . $provider;
    }
}

// Configuración de seguridad
$oauth_security = [
    // Tiempo de vida del state token (en segundos)
    'state_lifetime' => 600, // 10 minutos
    
    // Rate limiting para OAuth
    'rate_limit' => [
        'attempts' => 5,
        'window' => 300 // 5 minutos
    ],
    
    // Dominios permitidos para redirección
    'allowed_domains' => [
        'localhost',
        'fractalmerch.com.ar'
    ],
    
    // Configuración de cookies
    'cookie_settings' => [
        'secure' => $oauth_config['environment'] === 'production',
        'httponly' => true,
        'samesite' => 'Lax'
    ]
];

// Función para obtener configuración de un proveedor
function getOAuthConfig($provider) {
    global $oauth_config, $oauth_security;
    
    if (!isset($oauth_config[$provider]) || !$oauth_config[$provider]['enabled']) {
        return null;
    }
    
    return array_merge($oauth_config[$provider], ['security' => $oauth_security]);
}

// Función para validar entorno
function validateOAuthEnvironment() {
    global $oauth_config;
    
    $env = $oauth_config['environment'];
    $base_url = $oauth_config['base_urls'][$env];
    
    // Validaciones básicas
    if ($env === 'production' && strpos($base_url, 'https://') !== 0) {
        throw new Exception('Producción requiere HTTPS');
    }
    
    return true;
}

/**
 * INSTRUCCIONES DE CONFIGURACIÓN:
 * 
 * 1. GOOGLE OAUTH:
 *    - Ve a: https://console.developers.google.com/
 *    - Crea un proyecto o selecciona uno existente
 *    - Habilita la Google+ API
 *    - Crea credenciales OAuth 2.0
 *    - Agrega tu dominio a los orígenes autorizados
 *    - Agrega la redirect URI: https://fractalmerch.com.ar/auth/oauth-callback.php?provider=google
 * 
 * 2. FACEBOOK LOGIN:
 *    - Ve a: https://developers.facebook.com/
 *    - Crea una aplicación
 *    - Agrega el producto "Facebook Login"
 *    - Configura las redirect URIs válidas
 * 
 * 3. GITHUB OAUTH:
 *    - Ve a: GitHub Settings > Developer settings > OAuth Apps
 *    - Registra una nueva aplicación
 *    - Configura la Authorization callback URL
 * 
 * 4. APPLE SIGN IN:
 *    - Ve a: https://developer.apple.com/account/
 *    - Registra un Service ID
 *    - Configura el certificado y las claves
 *    - Descarga la clave privada AuthKey_XXXXXXXXXX.p8
 * 
 * 5. MICROSOFT OAUTH:
 *    - Ve a: https://portal.azure.com/
 *    - Registra una aplicación en Azure AD
 *    - Configura la redirect URI
 *    - Genera un client secret
 */
?>