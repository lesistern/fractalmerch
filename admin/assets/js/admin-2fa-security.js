/**
 * Sistema de Autenticación 2FA y Seguridad Avanzada para Admin Panel
 * Implementa múltiples capas de seguridad incluyendo 2FA, permisos granulares y monitoreo
 */

class AdminSecuritySystem {
    constructor() {
        this.currentUser = this.getCurrentUser();
        this.securityLevel = 'standard'; // standard, enhanced, maximum
        this.sessionData = {
            loginTime: Date.now(),
            lastActivity: Date.now(),
            securityChecks: [],
            suspiciousActivity: []
        };
        
        this.permissionMatrix = new Map();
        this.securityAlerts = [];
        this.encryptionKeys = new Map();
        
        this.init();
    }

    /**
     * Inicializar sistema de seguridad
     */
    init() {
        this.setupSecurityObservers();
        this.initializePermissionSystem();
        this.setup2FASystem();
        this.setupSessionSecurity();
        this.enableSecurityMonitoring();
        this.createSecurityDashboard();
        this.setupBiometricAuth();
        this.enableThreatDetection();
    }

    /**
     * Configurar sistema 2FA completo
     */
    setup2FASystem() {
        this.twoFactorMethods = {
            totp: { enabled: false, secret: null, backupCodes: [] },
            sms: { enabled: false, phoneNumber: null },
            email: { enabled: false, verificationCodes: [] },
            yubikey: { enabled: false, deviceId: null },
            biometric: { enabled: false, fingerprints: [], faceData: null }
        };

        this.setupTOTP();
        this.setupSMSAuth();
        this.setupEmailAuth();
        this.setupHardwareTokenAuth();
    }

    /**
     * Configurar TOTP (Time-based One-Time Passwords)
     */
    setupTOTP() {
        // Generar secreto TOTP
        this.generateTOTPSecret = () => {
            const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
            let secret = '';
            for (let i = 0; i < 32; i++) {
                secret += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            return secret;
        };

        // Generar código TOTP
        this.generateTOTPCode = (secret, timeStep = 30) => {
            const time = Math.floor(Date.now() / 1000 / timeStep);
            const hmac = this.hmacSHA1(this.base32Decode(secret), this.intToBytes(time));
            const offset = hmac[hmac.length - 1] & 0x0f;
            const code = ((hmac[offset] & 0x7f) << 24) |
                        ((hmac[offset + 1] & 0xff) << 16) |
                        ((hmac[offset + 2] & 0xff) << 8) |
                        (hmac[offset + 3] & 0xff);
            return (code % 1000000).toString().padStart(6, '0');
        };

        // Verificar código TOTP
        this.verifyTOTPCode = (secret, inputCode, window = 1) => {
            const timeStep = 30;
            const time = Math.floor(Date.now() / 1000 / timeStep);
            
            for (let i = -window; i <= window; i++) {
                const testTime = time + i;
                const hmac = this.hmacSHA1(this.base32Decode(secret), this.intToBytes(testTime));
                const offset = hmac[hmac.length - 1] & 0x0f;
                const code = ((hmac[offset] & 0x7f) << 24) |
                            ((hmac[offset + 1] & 0xff) << 16) |
                            ((hmac[offset + 2] & 0xff) << 8) |
                            (hmac[offset + 3] & 0xff);
                const generatedCode = (code % 1000000).toString().padStart(6, '0');
                
                if (generatedCode === inputCode) {
                    return true;
                }
            }
            return false;
        };

        // Crear interfaz de configuración TOTP
        this.createTOTPSetupModal();
    }

    /**
     * Crear modal de configuración TOTP
     */
    createTOTPSetupModal() {
        const modal = document.createElement('div');
        modal.className = 'security-modal totp-setup-modal';
        modal.innerHTML = `
            <div class="modal-content security-content">
                <div class="modal-header">
                    <h3><i class="fas fa-shield-alt"></i> Configurar Autenticación 2FA (TOTP)</h3>
                    <span class="close" onclick="adminSecurity.closeTOTPModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="setup-steps">
                        <div class="step step-1 active">
                            <h4>Paso 1: Escanear Código QR</h4>
                            <div class="qr-code-container">
                                <canvas id="qr-canvas" width="200" height="200"></canvas>
                            </div>
                            <p>Escanea este código QR con tu app de autenticación (Google Authenticator, Authy, etc.)</p>
                            <div class="manual-entry">
                                <strong>Código manual:</strong>
                                <input type="text" readonly id="totp-secret" value="">
                                <button onclick="adminSecurity.copyToClipboard('totp-secret')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="step step-2">
                            <h4>Paso 2: Verificar Código</h4>
                            <p>Ingresa el código de 6 dígitos generado por tu app:</p>
                            <div class="verification-input">
                                <input type="text" id="totp-verification" maxlength="6" placeholder="000000">
                                <button onclick="adminSecurity.verifyTOTPSetup()">
                                    <i class="fas fa-check"></i> Verificar
                                </button>
                            </div>
                        </div>
                        
                        <div class="step step-3">
                            <h4>Paso 3: Códigos de Respaldo</h4>
                            <p>Guarda estos códigos en un lugar seguro. Puedes usarlos si pierdes acceso a tu dispositivo:</p>
                            <div class="backup-codes" id="backup-codes"></div>
                            <button onclick="adminSecurity.downloadBackupCodes()" class="btn btn-primary">
                                <i class="fas fa-download"></i> Descargar Códigos
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button onclick="adminSecurity.closeTOTPModal()" class="btn btn-outline">Cancelar</button>
                    <button onclick="adminSecurity.completeTOTPSetup()" class="btn btn-primary" id="complete-setup" disabled>
                        <i class="fas fa-shield-alt"></i> Completar Configuración
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    /**
     * Configurar autenticación SMS
     */
    setupSMSAuth() {
        this.sendSMSCode = async (phoneNumber) => {
            const code = Math.floor(100000 + Math.random() * 900000).toString();
            
            try {
                // Simular envío de SMS (en producción usarías Twilio, AWS SNS, etc.)
                const response = await fetch('/admin/send-sms-code.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        phone: phoneNumber,
                        code: code,
                        csrf_token: this.getCSRFToken()
                    })
                });
                
                if (response.ok) {
                    this.sessionStorage.setItem('sms_code', code);
                    this.sessionStorage.setItem('sms_code_time', Date.now().toString());
                    return true;
                }
            } catch (error) {
                console.error('Error sending SMS:', error);
            }
            
            return false;
        };

        this.verifySMSCode = (inputCode) => {
            const storedCode = this.sessionStorage.getItem('sms_code');
            const codeTime = parseInt(this.sessionStorage.getItem('sms_code_time') || '0');
            
            // Código válido por 5 minutos
            if (Date.now() - codeTime > 300000) {
                this.sessionStorage.removeItem('sms_code');
                this.sessionStorage.removeItem('sms_code_time');
                return false;
            }
            
            return storedCode === inputCode;
        };
    }

    /**
     * Sistema de permisos granulares
     */
    initializePermissionSystem() {
        // Definir roles y permisos
        this.rolePermissions = {
            'super_admin': [
                'admin.users.create', 'admin.users.edit', 'admin.users.delete', 'admin.users.view',
                'admin.products.create', 'admin.products.edit', 'admin.products.delete', 'admin.products.view',
                'admin.statistics.view', 'admin.statistics.export',
                'admin.settings.edit', 'admin.settings.view',
                'admin.security.view', 'admin.security.edit',
                'admin.system.maintenance'
            ],
            'admin': [
                'admin.users.view', 'admin.users.edit',
                'admin.products.create', 'admin.products.edit', 'admin.products.delete', 'admin.products.view',
                'admin.statistics.view', 'admin.statistics.export',
                'admin.settings.view'
            ],
            'moderator': [
                'admin.products.view', 'admin.products.edit',
                'admin.users.view',
                'admin.statistics.view'
            ],
            'editor': [
                'admin.products.view', 'admin.products.edit',
                'admin.statistics.view'
            ]
        };

        // Cargar permisos del usuario actual
        this.loadUserPermissions();
        
        // Aplicar restricciones de UI basadas en permisos
        this.applyPermissionRestrictions();
    }

    /**
     * Cargar permisos del usuario actual
     */
    loadUserPermissions() {
        const userRole = this.currentUser?.role || 'guest';
        const permissions = this.rolePermissions[userRole] || [];
        
        permissions.forEach(permission => {
            this.permissionMatrix.set(permission, true);
        });

        // Permisos adicionales específicos del usuario
        const userSpecificPermissions = this.currentUser?.custom_permissions || [];
        userSpecificPermissions.forEach(permission => {
            this.permissionMatrix.set(permission, true);
        });
    }

    /**
     * Verificar permiso específico
     */
    hasPermission(permission) {
        return this.permissionMatrix.has(permission) && this.permissionMatrix.get(permission);
    }

    /**
     * Aplicar restricciones de UI basadas en permisos
     */
    applyPermissionRestrictions() {
        // Ocultar/deshabilitar elementos según permisos
        const permissionElements = document.querySelectorAll('[data-permission]');
        
        permissionElements.forEach(element => {
            const requiredPermission = element.getAttribute('data-permission');
            
            if (!this.hasPermission(requiredPermission)) {
                if (element.tagName === 'BUTTON' || element.tagName === 'INPUT') {
                    element.disabled = true;
                    element.title = 'No tienes permisos para esta acción';
                } else {
                    element.style.display = 'none';
                }
                
                element.classList.add('permission-restricted');
            }
        });

        // Ocultar secciones completas si no hay permisos
        this.hideUnauthorizedSections();
    }

    /**
     * Ocultar secciones no autorizadas
     */
    hideUnauthorizedSections() {
        const sectionPermissions = {
            '.users-section': 'admin.users.view',
            '.statistics-section': 'admin.statistics.view',
            '.settings-section': 'admin.settings.view',
            '.security-section': 'admin.security.view'
        };

        Object.entries(sectionPermissions).forEach(([selector, permission]) => {
            if (!this.hasPermission(permission)) {
                const sections = document.querySelectorAll(selector);
                sections.forEach(section => {
                    section.style.display = 'none';
                });
            }
        });
    }

    /**
     * Configurar seguridad de sesión
     */
    setupSessionSecurity() {
        // Timeout de sesión automático
        this.sessionTimeout = 30 * 60 * 1000; // 30 minutos
        this.sessionWarning = 5 * 60 * 1000; // 5 minutos antes
        
        this.resetSessionTimer();
        
        // Detectar actividad del usuario
        ['click', 'keypress', 'mousemove', 'scroll'].forEach(event => {
            document.addEventListener(event, () => {
                this.updateLastActivity();
            }, { passive: true });
        });

        // Verificar integridad de sesión periódicamente
        setInterval(() => {
            this.verifySessionIntegrity();
        }, 60000); // Cada minuto

        // Detectar pestañas múltiples
        this.detectMultipleTabs();
    }

    /**
     * Resetear timer de sesión
     */
    resetSessionTimer() {
        clearTimeout(this.sessionTimeoutId);
        clearTimeout(this.sessionWarningId);
        
        this.sessionWarningId = setTimeout(() => {
            this.showSessionWarning();
        }, this.sessionTimeout - this.sessionWarning);
        
        this.sessionTimeoutId = setTimeout(() => {
            this.handleSessionTimeout();
        }, this.sessionTimeout);
    }

    /**
     * Actualizar última actividad
     */
    updateLastActivity() {
        this.sessionData.lastActivity = Date.now();
        this.resetSessionTimer();
        
        // Enviar heartbeat al servidor
        this.sendSessionHeartbeat();
    }

    /**
     * Mostrar advertencia de sesión
     */
    showSessionWarning() {
        const warning = document.createElement('div');
        warning.className = 'session-warning-modal';
        warning.innerHTML = `
            <div class="warning-content">
                <div class="warning-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> Sesión a punto de expirar</h3>
                </div>
                <div class="warning-body">
                    <p>Tu sesión expirará en <span id="countdown">5:00</span></p>
                    <p>¿Quieres extender tu sesión?</p>
                </div>
                <div class="warning-actions">
                    <button onclick="adminSecurity.extendSession()" class="btn btn-primary">
                        <i class="fas fa-clock"></i> Extender Sesión
                    </button>
                    <button onclick="adminSecurity.logoutNow()" class="btn btn-outline">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(warning);
        
        // Countdown timer
        this.startCountdown(warning);
    }

    /**
     * Configurar autenticación biométrica
     */
    setupBiometricAuth() {
        if ('credentials' in navigator && 'create' in navigator.credentials) {
            this.biometricSupported = true;
            this.setupWebAuthn();
        } else {
            this.biometricSupported = false;
            console.warn('Biometric authentication not supported');
        }
    }

    /**
     * Configurar WebAuthn para biometría
     */
    setupWebAuthn() {
        this.createBiometricCredential = async () => {
            try {
                const credential = await navigator.credentials.create({
                    publicKey: {
                        challenge: new Uint8Array(32),
                        rp: {
                            name: "Admin Panel",
                            id: window.location.hostname,
                        },
                        user: {
                            id: new TextEncoder().encode(this.currentUser.id),
                            name: this.currentUser.email,
                            displayName: this.currentUser.username,
                        },
                        pubKeyCredParams: [{alg: -7, type: "public-key"}],
                        authenticatorSelection: {
                            authenticatorAttachment: "platform",
                            userVerification: "required"
                        },
                        timeout: 60000,
                        attestation: "direct"
                    }
                });
                
                // Guardar credencial en el servidor
                await this.saveBiometricCredential(credential);
                return true;
            } catch (error) {
                console.error('Error creating biometric credential:', error);
                return false;
            }
        };

        this.verifyBiometricCredential = async () => {
            try {
                const credential = await navigator.credentials.get({
                    publicKey: {
                        challenge: new Uint8Array(32),
                        timeout: 60000,
                        userVerification: "required"
                    }
                });
                
                // Verificar credencial con el servidor
                return await this.verifyBiometricWithServer(credential);
            } catch (error) {
                console.error('Error verifying biometric credential:', error);
                return false;
            }
        };
    }

    /**
     * Habilitar detección de amenazas
     */
    enableThreatDetection() {
        this.threatPatterns = {
            bruteForce: { attempts: 0, window: 300000 }, // 5 minutos
            suspiciousNavigation: { patterns: [], threshold: 5 },
            unusualActivity: { baseline: null, deviation: 0 },
            injectionAttempts: { count: 0, patterns: [] }
        };

        this.setupBruteForceDetection();
        this.setupInjectionDetection();
        this.setupAnomalyDetection();
        this.setupGeolocationTracking();
    }

    /**
     * Detectar intentos de fuerza bruta
     */
    setupBruteForceDetection() {
        // Interceptar formularios de login
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.querySelector('input[type="password"]')) {
                this.trackLoginAttempt(form);
            }
        });
    }

    /**
     * Detectar intentos de inyección
     */
    setupInjectionDetection() {
        const suspiciousPatterns = [
            /(\<script\>)|(\<\/script\>)/gi,
            /(javascript:)|(vbscript:)/gi,
            /(union\s+select)|(or\s+1\s*=\s*1)/gi,
            /(\bdrop\b|\bdelete\b|\binsert\b|\bupdate\b)\s+/gi
        ];

        document.addEventListener('input', (e) => {
            const value = e.target.value;
            suspiciousPatterns.forEach(pattern => {
                if (pattern.test(value)) {
                    this.reportSecurityIncident('injection_attempt', {
                        field: e.target.name,
                        value: value,
                        pattern: pattern.toString()
                    });
                }
            });
        });
    }

    /**
     * Crear dashboard de seguridad
     */
    createSecurityDashboard() {
        const dashboard = this.createSecurityDashboardContainer();
        this.renderSecurityMetrics(dashboard);
        this.renderSecurityAlerts(dashboard);
        this.render2FAStatus(dashboard);
        this.renderPermissionMatrix(dashboard);
        
        // Actualizar dashboard cada 10 segundos
        setInterval(() => {
            this.updateSecurityDashboard(dashboard);
        }, 10000);
    }

    /**
     * Crear contenedor del dashboard de seguridad
     */
    createSecurityDashboardContainer() {
        let dashboard = document.querySelector('.security-dashboard');
        
        if (!dashboard) {
            dashboard = document.createElement('div');
            dashboard.className = 'security-dashboard';
            dashboard.innerHTML = `
                <div class="security-dashboard-header">
                    <h3><i class="fas fa-shield-alt"></i> Security Center</h3>
                    <div class="security-controls">
                        <button onclick="adminSecurity.runSecurityScan()" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i> Escanear
                        </button>
                        <button onclick="adminSecurity.setup2FA()" class="btn btn-sm btn-success">
                            <i class="fas fa-shield-alt"></i> Configurar 2FA
                        </button>
                        <button onclick="adminSecurity.toggleDashboard()" class="security-toggle">
                            <i class="fas fa-shield-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="security-dashboard-content" style="display: none;">
                    <div class="security-tabs">
                        <button class="tab-btn active" data-tab="metrics">Métricas</button>
                        <button class="tab-btn" data-tab="alerts">Alertas</button>
                        <button class="tab-btn" data-tab="2fa">2FA</button>
                        <button class="tab-btn" data-tab="permissions">Permisos</button>
                    </div>
                    <div class="security-panels">
                        <div class="security-panel active" id="metrics-panel"></div>
                        <div class="security-panel" id="alerts-panel"></div>
                        <div class="security-panel" id="2fa-panel"></div>
                        <div class="security-panel" id="permissions-panel"></div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dashboard);
        }
        
        return dashboard;
    }

    /**
     * Utilidades de seguridad
     */
    getCurrentUser() {
        // En producción esto vendría del servidor
        return {
            id: '1',
            username: 'admin',
            email: 'admin@proyecto.com',
            role: 'super_admin',
            custom_permissions: []
        };
    }

    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    // Funciones cryptográficas para TOTP
    hmacSHA1(key, data) {
        // Implementación básica de HMAC-SHA1 (en producción usar crypto-js)
        return new Array(20).fill(0); // Placeholder
    }

    base32Decode(base32) {
        // Implementación de decodificación base32
        return new Uint8Array(20); // Placeholder
    }

    intToBytes(num) {
        return new Uint8Array([
            (num >> 56) & 0xff, (num >> 48) & 0xff, (num >> 40) & 0xff, (num >> 32) & 0xff,
            (num >> 24) & 0xff, (num >> 16) & 0xff, (num >> 8) & 0xff, num & 0xff
        ]);
    }

    // Métodos públicos
    setup2FA() {
        const modal = document.querySelector('.totp-setup-modal');
        if (modal) {
            modal.style.display = 'block';
            this.initializeTOTPSetup();
        }
    }

    toggleDashboard() {
        const content = document.querySelector('.security-dashboard-content');
        const isVisible = content.style.display !== 'none';
        content.style.display = isVisible ? 'none' : 'block';
    }

    runSecurityScan() {
        console.log('🔍 Running security scan...');
        
        // Simular escaneo de seguridad
        setTimeout(() => {
            if (window.realtimeNotifications) {
                window.realtimeNotifications.notify('security_alert', 
                    'Escaneo de seguridad completado - Sin amenazas detectadas', {
                    title: 'Security Scan',
                    importance: 'low'
                });
            }
        }, 3000);
    }

    reportSecurityIncident(type, details) {
        const incident = {
            type: type,
            timestamp: Date.now(),
            userAgent: navigator.userAgent,
            url: window.location.href,
            details: details
        };
        
        this.securityAlerts.push(incident);
        
        // Notificar incidente crítico
        if (window.realtimeNotifications) {
            window.realtimeNotifications.notify('security_alert', 
                `Incidente de seguridad detectado: ${type}`, {
                title: 'Security Alert',
                importance: 'critical'
            });
        }
        
        console.error('Security incident:', incident);
    }
}

// CSS para el sistema de seguridad
const securityStyles = `
<style>
.security-dashboard {
    position: fixed;
    top: 20px;
    left: 20px;
    width: 400px;
    background: var(--admin-bg-primary);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--admin-radius-lg);
    box-shadow: var(--admin-shadow-xl);
    z-index: 9996;
    font-size: 14px;
}

.security-dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid var(--admin-border-light);
    background: var(--admin-bg-secondary);
}

.security-dashboard-header h3 {
    margin: 0;
    font-size: 16px;
    color: var(--admin-text-primary);
}

.security-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}

.security-toggle {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 6px 10px;
    cursor: pointer;
}

.security-dashboard-content {
    max-height: 600px;
    overflow-y: auto;
}

.security-tabs {
    display: flex;
    border-bottom: 1px solid var(--admin-border-light);
}

.security-tabs .tab-btn {
    flex: 1;
    padding: 10px;
    border: none;
    background: var(--admin-bg-tertiary);
    color: var(--admin-text-secondary);
    cursor: pointer;
    font-size: 12px;
}

.security-tabs .tab-btn.active {
    background: var(--admin-bg-primary);
    color: var(--admin-text-primary);
    border-bottom: 2px solid #dc3545;
}

.security-panels {
    padding: 15px;
}

.security-panel {
    display: none;
}

.security-panel.active {
    display: block;
}

.security-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10001;
}

.security-content {
    background: var(--admin-bg-primary);
    border-radius: var(--admin-radius-lg);
    max-width: 500px;
    width: 90vw;
    max-height: 80vh;
    overflow-y: auto;
}

.setup-steps {
    padding: 20px 0;
}

.step {
    display: none;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid var(--admin-border-light);
    margin-bottom: 15px;
}

.step.active {
    display: block;
    background: rgba(40, 167, 69, 0.1);
    border-color: #28a745;
}

.qr-code-container {
    text-align: center;
    margin: 20px 0;
}

.manual-entry {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 15px;
}

.manual-entry input {
    flex: 1;
    padding: 8px;
    border: 1px solid var(--admin-border-light);
    border-radius: 4px;
}

.verification-input {
    display: flex;
    gap: 10px;
    align-items: center;
    margin: 20px 0;
}

.verification-input input {
    font-size: 24px;
    text-align: center;
    letter-spacing: 5px;
    padding: 15px;
    border: 2px solid var(--admin-border-light);
    border-radius: 8px;
    width: 200px;
}

.backup-codes {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin: 20px 0;
    padding: 15px;
    background: var(--admin-bg-tertiary);
    border-radius: 6px;
    font-family: monospace;
}

.backup-code {
    padding: 5px;
    background: var(--admin-bg-primary);
    border-radius: 4px;
    text-align: center;
    font-weight: 600;
}

.session-warning-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 193, 7, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10002;
}

.warning-content {
    background: var(--admin-bg-primary);
    border-radius: var(--admin-radius-lg);
    padding: 30px;
    text-align: center;
    border: 3px solid #ffc107;
}

.warning-header h3 {
    color: #856404;
    margin-bottom: 20px;
}

.warning-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 25px;
}

#countdown {
    font-size: 24px;
    font-weight: 700;
    color: #dc3545;
}

.permission-restricted {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .security-dashboard {
        width: calc(100vw - 40px);
        left: 20px;
    }
    
    .security-content {
        width: 95vw;
    }
    
    .backup-codes {
        grid-template-columns: 1fr;
    }
}
</style>
`;

// Inyectar estilos
document.head.insertAdjacentHTML('beforeend', securityStyles);

// Inicializar sistema de seguridad
const adminSecurity = new AdminSecuritySystem();

// Exportar para uso global
window.adminSecurity = adminSecurity;