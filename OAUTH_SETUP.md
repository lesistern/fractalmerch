# Sistema OAuth para FractalMerch

## 🔐 Proveedores Implementados

- **Google OAuth 2.0** ✅
- **Facebook Login** ✅  
- **GitHub OAuth** ✅
- **Apple Sign In** ✅
- **Microsoft OAuth** ✅

## 📋 Instalación y Configuración

### 1. Ejecutar Migración de Base de Datos

```sql
-- Ejecutar en phpMyAdmin o MySQL
mysql -u root proyecto_web < database/add_oauth_support.sql
```

### 2. Configurar Credenciales OAuth

1. Copia el archivo de configuración:
```bash
cp config/oauth.example.php config/oauth.php
```

2. Edita `config/oauth.php` con tus credenciales reales

3. Agrega a `.gitignore`:
```
config/oauth.php
```

### 3. Configurar Proveedores OAuth

#### 🔴 Google OAuth 2.0

1. Ve a [Google Cloud Console](https://console.developers.google.com/)
2. Crea un proyecto o selecciona uno existente
3. Habilita la **Google+ API** y **People API**
4. Ve a **Credenciales** → **Crear credenciales** → **ID de cliente OAuth 2.0**
5. Configura:
   - **Tipo de aplicación:** Aplicación web
   - **Orígenes JavaScript autorizados:** 
     - `http://localhost` (desarrollo)
     - `https://fractalmerch.com.ar` (producción)
   - **URIs de redirección autorizados:**
     - `http://localhost/proyecto/auth/oauth-callback.php?provider=google`
     - `https://fractalmerch.com.ar/auth/oauth-callback.php?provider=google`

#### 🔵 Facebook Login

1. Ve a [Facebook for Developers](https://developers.facebook.com/)
2. Crea una aplicación → **Consumidor**
3. Agrega el producto **Facebook Login**
4. Configura en **Facebook Login** → **Configuración**:
   - **URIs de redirección OAuth válidos:**
     - `http://localhost/proyecto/auth/oauth-callback.php?provider=facebook`
     - `https://fractalmerch.com.ar/auth/oauth-callback.php?provider=facebook`
   - **Dominios de aplicación:** `fractalmerch.com.ar`

#### ⚫ GitHub OAuth

1. Ve a **GitHub Settings** → **Developer settings** → **OAuth Apps**
2. **New OAuth App**
3. Configura:
   - **Application name:** FractalMerch
   - **Homepage URL:** `https://fractalmerch.com.ar`
   - **Authorization callback URL:** `https://fractalmerch.com.ar/auth/oauth-callback.php?provider=github`

#### 🍎 Apple Sign In

1. Ve a [Apple Developer Portal](https://developer.apple.com/account/)
2. **Certificates, Identifiers & Profiles** → **Identifiers**
3. Registra un **Services ID**
4. Configura **Sign In with Apple**:
   - **Domains:** `fractalmerch.com.ar`
   - **Return URLs:** `https://fractalmerch.com.ar/auth/oauth-callback.php?provider=apple`
5. Crea una **Key** para Apple Sign In
6. Descarga `AuthKey_XXXXXXXXXX.p8`

#### 🔷 Microsoft OAuth

1. Ve a [Azure Portal](https://portal.azure.com/)
2. **Azure Active Directory** → **App registrations** → **New registration**
3. Configura:
   - **Name:** FractalMerch
   - **Redirect URI:** `https://fractalmerch.com.ar/auth/oauth-callback.php?provider=microsoft`
4. Ve a **Certificates & secrets** → **New client secret**

## 🛡️ Características de Seguridad

### Protecciones Implementadas

- **CSRF Protection:** State tokens únicos por sesión
- **Rate Limiting:** Máximo 5 intentos por IP en 5 minutos
- **IP Validation:** Opcional para mayor seguridad
- **Token Expiration:** States expiran en 10 minutos
- **Account Takeover Detection:** Detecta actividad sospechosa
- **Data Sanitization:** Sanitización de todos los datos de usuario
- **Audit Logging:** Log completo de intentos de login

### Configuración de Seguridad

Edita las configuraciones en `config/oauth.php`:

```php
$oauth_security = [
    'state_lifetime' => 600,        // 10 minutos
    'rate_limit' => [
        'attempts' => 5,            // Intentos máximos
        'window' => 300             // Ventana de tiempo (5 min)
    ],
    'allowed_domains' => [
        'localhost',
        'fractalmerch.com.ar'
    ]
];
```

## 🗄️ Estructura de Base de Datos

### Tablas Nuevas

- **oauth_tokens:** Almacena tokens de acceso y refresh
- **oauth_config:** Configuración de proveedores
- **login_attempts:** Audit log de intentos de login

### Campos Agregados a `users`

- `oauth_provider` - Proveedor OAuth utilizado
- `oauth_id` - ID único del proveedor
- `oauth_token` - Token de acceso
- `avatar_url` - URL del avatar del usuario
- `email_verified` - Estado de verificación del email
- `last_login` - Timestamp del último login
- `account_type` - Tipo de cuenta (local/oauth)

## 🎨 Interfaz de Usuario

### Página de Login

La página de login (`login.php`) ahora incluye:

- Formulario tradicional de email/contraseña
- Divisor visual "O continúa con"
- Botones OAuth con iconos y colores oficiales
- Diseño responsivo (grid 2x2 en desktop, columna en móvil)
- Soporte para modo oscuro

### Botones OAuth

- **Google:** Estilo oficial de Google (blanco con borde gris)
- **Facebook:** Azul oficial de Facebook
- **GitHub:** Negro/gris oscuro
- **Apple:** Negro sólido
- **Microsoft:** Azul oficial de Microsoft

## 🔄 Flujo de Autenticación

1. Usuario hace clic en botón OAuth
2. Redirección a `auth/oauth-login.php?provider=X`
3. Generación de state token seguro
4. Redirección al proveedor OAuth
5. Usuario autoriza la aplicación
6. Proveedor redirige a `auth/oauth-callback.php?provider=X&code=...`
7. Intercambio de código por token de acceso
8. Obtención de información del usuario
9. Creación/actualización del usuario en BD
10. Inicio de sesión automático
11. Redirección a página apropiada

## 🔧 Personalización

### Agregar Nuevo Proveedor

1. Agrega configuración en `OAuthManager.php` → `initializeProviders()`
2. Implementa mapeo de datos en `mapUserData()`
3. Agrega configuración en `oauth.example.php`
4. Crea botón en `login.php`
5. Agrega estilos CSS
6. Actualiza documentación

### Configurar Campos Personalizados

Edita `mapUserData()` en `OAuthManager.php` para agregar campos específicos del proveedor.

## 🚀 Despliegue en Producción

### Lista de Verificación

- [ ] Configurar HTTPS obligatorio
- [ ] Actualizar todas las redirect URIs a HTTPS
- [ ] Configurar variables de entorno seguras
- [ ] Activar rate limiting estricto
- [ ] Configurar logging centralizado
- [ ] Probar todos los proveedores OAuth
- [ ] Configurar alertas de seguridad
- [ ] Validar políticas de cookies
- [ ] Revisar permisos de archivos
- [ ] Configurar backup de tokens

### Variables de Entorno

```bash
# .env para producción
OAUTH_ENVIRONMENT=production
OAUTH_GOOGLE_CLIENT_ID=...
OAUTH_GOOGLE_CLIENT_SECRET=...
OAUTH_FACEBOOK_APP_ID=...
OAUTH_FACEBOOK_APP_SECRET=...
# ... etc
```

## 🐛 Troubleshooting

### Errores Comunes

**"Redirect URI mismatch"**
- Verifica que las URIs coincidan exactamente
- Incluye `http://` o `https://`
- No incluyas trailing slash si no es necesario

**"Invalid state parameter"**
- Verifica que las cookies estén habilitadas
- Revisa la configuración de sesiones PHP
- Aumenta el lifetime del state si es necesario

**"Email already exists"**
- Implementa logic para vincular cuentas existentes
- Permite login con email + proveedor diferente

### Logs de Debug

Revisa los logs en:
- `login_attempts` table en la BD
- PHP error logs
- Audit logs de seguridad

### Testing Local

Para testing local con HTTPS:
1. Usa ngrok: `ngrok http 80`
2. Actualiza las redirect URIs con la URL de ngrok
3. Configura el entorno como 'development'

## 📞 Soporte

Para problemas con OAuth:
1. Revisa los logs de `login_attempts`
2. Verifica la configuración del proveedor
3. Testea la redirect URI manualmente
4. Revisa la documentación oficial del proveedor

## 🔗 Enlaces Útiles

- [Google OAuth Playground](https://developers.google.com/oauthplayground/)
- [Facebook Graph API Explorer](https://developers.facebook.com/tools/explorer/)
- [GitHub OAuth Documentation](https://docs.github.com/en/developers/apps/building-oauth-apps)
- [Apple Sign In Documentation](https://developer.apple.com/sign-in-with-apple/)
- [Microsoft Identity Platform](https://docs.microsoft.com/en-us/azure/active-directory/develop/)

---

**Última actualización:** 2025-07-04  
**Versión:** 1.0  
**Mantenedor:** Claude Assistant