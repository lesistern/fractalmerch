/**
 * FractalMerch Email Marketing Automation System
 * Advanced email campaigns with personalization and automation
 */

class EmailMarketingSystem {
    constructor() {
        this.config = {
            enableEmailMarketing: true,
            enableAutomation: true,
            enablePersonalization: true,
            enableABTesting: true,
            apiEndpoint: '/api/email-marketing/',
            unsubscribeUrl: '/unsubscribe.php',
            trackingPixelUrl: '/api/email-tracking/pixel.php',
            retryAttempts: 3,
            rateLimitPerHour: 100
        };
        
        this.campaigns = {
            welcome_series: {
                name: 'Serie de Bienvenida',
                trigger: 'user_signup',
                emails: [
                    {
                        delay: 0,
                        template: 'welcome_email',
                        subject: '¡Bienvenido a FractalMerch! 🎨',
                        priority: 'high'
                    },
                    {
                        delay: 86400000, // 24 hours
                        template: 'design_tips',
                        subject: 'Consejos para crear diseños únicos',
                        priority: 'medium'
                    },
                    {
                        delay: 259200000, // 72 hours
                        template: 'first_purchase_incentive',
                        subject: '¡15% OFF en tu primera compra!',
                        priority: 'high'
                    }
                ]
            },
            
            cart_abandonment: {
                name: 'Abandono de Carrito',
                trigger: 'cart_abandoned',
                emails: [
                    {
                        delay: 3600000, // 1 hour
                        template: 'cart_reminder',
                        subject: 'Tu diseño te está esperando... 🛒',
                        priority: 'high'
                    },
                    {
                        delay: 86400000, // 24 hours
                        template: 'cart_incentive',
                        subject: '¡No pierdas tu diseño! 10% de descuento',
                        priority: 'medium'
                    },
                    {
                        delay: 259200000, // 72 hours
                        template: 'final_reminder',
                        subject: 'Última oportunidad - Tu carrito expira pronto',
                        priority: 'low'
                    }
                ]
            },
            
            post_purchase: {
                name: 'Post-Compra',
                trigger: 'purchase_completed',
                emails: [
                    {
                        delay: 0,
                        template: 'order_confirmation',
                        subject: '¡Pedido confirmado! Tu remera está en producción',
                        priority: 'high'
                    },
                    {
                        delay: 172800000, // 48 hours
                        template: 'production_update',
                        subject: 'Tu remera está siendo creada con amor ❤️',
                        priority: 'medium'
                    },
                    {
                        delay: 604800000, // 7 days
                        template: 'review_request',
                        subject: '¿Cómo quedó tu remera? ¡Cuéntanos!',
                        priority: 'low'
                    }
                ]
            },
            
            reactivation: {
                name: 'Reactivación',
                trigger: 'user_inactive',
                emails: [
                    {
                        delay: 0,
                        template: 'we_miss_you',
                        subject: 'Te extrañamos... ¡Volvé con estilo!',
                        priority: 'medium'
                    },
                    {
                        delay: 432000000, // 5 days
                        template: 'special_offer',
                        subject: '25% OFF especial para vos 🎁',
                        priority: 'high'
                    }
                ]
            },
            
            seasonal_campaigns: {
                name: 'Campañas Estacionales',
                trigger: 'seasonal_event',
                emails: [
                    {
                        delay: 0,
                        template: 'seasonal_collection',
                        subject: 'Nueva colección: {season} 2025',
                        priority: 'medium'
                    }
                ]
            }
        };
        
        this.templates = {
            welcome_email: {
                subject: '¡Bienvenido a FractalMerch! 🎨',
                preheader: 'Tu viaje creativo comienza aquí',
                content: this.getWelcomeEmailContent()
            },
            cart_reminder: {
                subject: 'Tu diseño te está esperando... 🛒',
                preheader: 'No dejes que tu creatividad se pierda',
                content: this.getCartReminderContent()
            },
            order_confirmation: {
                subject: '¡Pedido confirmado! Tu remera está en producción',
                preheader: 'Detalles de tu pedido y seguimiento',
                content: this.getOrderConfirmationContent()
            }
        };
        
        this.segmentation = {
            new_users: {
                conditions: {
                    registration_date: { '>': Date.now() - 7 * 24 * 60 * 60 * 1000 },
                    purchase_count: 0
                },
                campaigns: ['welcome_series']
            },
            active_customers: {
                conditions: {
                    purchase_count: { '>': 0 },
                    last_purchase: { '>': Date.now() - 90 * 24 * 60 * 60 * 1000 }
                },
                campaigns: ['seasonal_campaigns', 'upsell']
            },
            vip_customers: {
                conditions: {
                    purchase_count: { '>': 5 },
                    total_spent: { '>': 50000 }
                },
                campaigns: ['vip_exclusive', 'early_access']
            },
            inactive_users: {
                conditions: {
                    last_activity: { '<': Date.now() - 60 * 24 * 60 * 60 * 1000 },
                    purchase_count: { '>': 0 }
                },
                campaigns: ['reactivation']
            }
        };
        
        this.personalization = {
            variables: {
                user_name: '{user_name}',
                first_name: '{first_name}',
                last_purchase: '{last_purchase}',
                favorite_category: '{favorite_category}',
                cart_items: '{cart_items}',
                recommendations: '{recommendations}',
                discount_code: '{discount_code}',
                days_since_signup: '{days_since_signup}'
            },
            dynamic_content: {
                product_recommendations: this.getProductRecommendations,
                discount_calculator: this.calculatePersonalizedDiscount,
                content_based_on_behavior: this.getContentBasedOnBehavior
            }
        };
        
        this.abTesting = {
            subject_lines: {
                'cart_abandonment_subjects': [
                    'Tu diseño te está esperando... 🛒',
                    '¿Olvidaste algo? Tu carrito tiene productos geniales',
                    'Tu remera personalizada está a un click'
                ],
                'welcome_subjects': [
                    '¡Bienvenido a FractalMerch! 🎨',
                    'Tu creatividad tiene un nuevo hogar',
                    '¡Hola! Empezá a crear tu primera remera'
                ]
            },
            send_times: [
                { hour: 9, description: 'Mañana temprano' },
                { hour: 14, description: 'Después del almuerzo' },
                { hour: 19, description: 'Noche' }
            ],
            content_variants: {
                cta_buttons: [
                    'Finalizar Compra',
                    'Crear Mi Remera',
                    'Ver Mi Carrito'
                ]
            }
        };
        
        this.analytics = {
            metrics: {
                sent: 0,
                delivered: 0,
                opened: 0,
                clicked: 0,
                converted: 0,
                unsubscribed: 0,
                bounced: 0
            },
            campaign_performance: {},
            user_engagement: {}
        };
        
        this.init();
    }
    
    init() {
        console.log('📧 Email Marketing System initializing...');
        
        // Initialize event listeners
        this.initEventListeners();
        
        // Load saved automation state
        this.loadAutomationState();
        
        // Start automation processor
        this.startAutomationProcessor();
        
        console.log('✅ Email Marketing System active');
    }
    
    /**
     * EVENT LISTENERS AND TRIGGERS
     */
    initEventListeners() {
        // User signup trigger
        document.addEventListener('user_registered', (e) => {
            this.triggerCampaign('welcome_series', e.detail);
        });
        
        // Cart abandonment trigger
        document.addEventListener('cart_abandoned', (e) => {
            this.triggerCampaign('cart_abandonment', e.detail);
        });
        
        // Purchase completed trigger
        document.addEventListener('purchase_completed', (e) => {
            this.triggerCampaign('post_purchase', e.detail);
        });
        
        // Newsletter subscription
        document.addEventListener('newsletter_signup', (e) => {
            this.subscribeToNewsletter(e.detail);
        });
        
        // Track email interactions
        this.trackEmailInteractions();
    }
    
    /**
     * CAMPAIGN MANAGEMENT
     */
    triggerCampaign(campaignType, userData) {
        if (!this.config.enableEmailMarketing) return;
        
        const campaign = this.campaigns[campaignType];
        if (!campaign) {
            console.warn(`Campaign type "${campaignType}" not found`);
            return;
        }
        
        console.log(`🚀 Triggering campaign: ${campaign.name} for user:`, userData.email);
        
        // Check if user is eligible for campaign
        if (!this.isUserEligible(userData, campaignType)) {
            console.log('User not eligible for this campaign');
            return;
        }
        
        // Schedule campaign emails
        campaign.emails.forEach(email => {
            this.scheduleEmail({
                ...email,
                campaignType,
                userData,
                scheduledFor: Date.now() + email.delay
            });
        });
        
        this.trackCampaignTrigger(campaignType, userData);
    }
    
    scheduleEmail(emailData) {
        const scheduledEmails = this.getScheduledEmails();
        const emailId = this.generateEmailId();
        
        const scheduledEmail = {
            id: emailId,
            ...emailData,
            status: 'scheduled',
            createdAt: Date.now(),
            attempts: 0
        };
        
        scheduledEmails.push(scheduledEmail);
        this.saveScheduledEmails(scheduledEmails);
        
        console.log(`📅 Email scheduled for ${new Date(emailData.scheduledFor).toLocaleString()}`);
    }
    
    /**
     * AUTOMATION PROCESSOR
     */
    startAutomationProcessor() {
        // Process scheduled emails every minute
        setInterval(() => {
            this.processScheduledEmails();
        }, 60000);
        
        // Check for user inactivity daily
        setInterval(() => {
            this.checkUserInactivity();
        }, 24 * 60 * 60 * 1000);
    }
    
    processScheduledEmails() {
        const scheduledEmails = this.getScheduledEmails();
        const now = Date.now();
        
        const readyEmails = scheduledEmails.filter(email => 
            email.status === 'scheduled' && 
            email.scheduledFor <= now
        );
        
        readyEmails.forEach(email => {
            this.sendEmail(email);
        });
    }
    
    async sendEmail(emailData) {
        try {
            console.log(`📤 Sending email: ${emailData.template} to ${emailData.userData.email}`);
            
            // Generate personalized content
            const personalizedContent = this.personalizeContent(emailData);
            
            // Apply A/B testing
            const optimizedContent = this.applyABTesting(personalizedContent, emailData);
            
            // Prepare email payload
            const emailPayload = {
                to: emailData.userData.email,
                subject: optimizedContent.subject,
                html: optimizedContent.html,
                text: optimizedContent.text,
                campaign: emailData.campaignType,
                template: emailData.template,
                userId: emailData.userData.id,
                trackingId: this.generateTrackingId()
            };
            
            // Send via backend API
            const response = await fetch(this.config.apiEndpoint + 'send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(emailPayload)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.markEmailAsSent(emailData.id);
                this.updateAnalytics('sent', emailData);
                console.log('✅ Email sent successfully');
            } else {
                throw new Error(result.error || 'Unknown error');
            }
            
        } catch (error) {
            console.error('❌ Email sending failed:', error);
            this.handleEmailError(emailData, error);
        }
    }
    
    /**
     * PERSONALIZATION
     */
    personalizeContent(emailData) {
        const template = this.templates[emailData.template];
        if (!template) {
            console.warn(`Template "${emailData.template}" not found`);
            return this.getDefaultTemplate();
        }
        
        let content = template.content;
        let subject = template.subject;
        
        // Replace personalization variables
        Object.entries(this.personalization.variables).forEach(([key, placeholder]) => {
            const value = this.getPersonalizationValue(key, emailData.userData);
            content = content.replace(new RegExp(placeholder, 'g'), value);
            subject = subject.replace(new RegExp(placeholder, 'g'), value);
        });
        
        // Add dynamic content
        content = this.addDynamicContent(content, emailData);
        
        return {
            subject,
            html: this.buildHTMLTemplate(content, emailData),
            text: this.buildTextTemplate(content, emailData)
        };
    }
    
    getPersonalizationValue(key, userData) {
        const personalizations = {
            user_name: userData.name || userData.email,
            first_name: userData.first_name || userData.name?.split(' ')[0] || 'Amigo',
            last_purchase: userData.last_purchase_date ? new Date(userData.last_purchase_date).toLocaleDateString() : 'Nunca',
            favorite_category: userData.favorite_category || 'Remeras',
            days_since_signup: Math.floor((Date.now() - userData.signup_date) / (24 * 60 * 60 * 1000))
        };
        
        return personalizations[key] || '';
    }
    
    addDynamicContent(content, emailData) {
        // Add product recommendations
        if (content.includes('{recommendations}')) {
            const recommendations = this.getProductRecommendations(emailData.userData);
            content = content.replace('{recommendations}', recommendations);
        }
        
        // Add discount code
        if (content.includes('{discount_code}')) {
            const discountCode = this.generateDiscountCode(emailData.userData);
            content = content.replace('{discount_code}', discountCode);
        }
        
        // Add cart items
        if (content.includes('{cart_items}') && emailData.userData.cart) {
            const cartItems = this.formatCartItems(emailData.userData.cart);
            content = content.replace('{cart_items}', cartItems);
        }
        
        return content;
    }
    
    /**
     * A/B TESTING
     */
    applyABTesting(content, emailData) {
        if (!this.config.enableABTesting) return content;
        
        // Test subject lines
        const subjectVariants = this.abTesting.subject_lines[emailData.campaignType + '_subjects'];
        if (subjectVariants) {
            const variantIndex = this.getABTestVariant(emailData.userData.id, 'subject_' + emailData.template);
            content.subject = subjectVariants[variantIndex % subjectVariants.length];
        }
        
        // Test CTA buttons
        const ctaVariants = this.abTesting.content_variants.cta_buttons;
        if (content.html.includes('{cta_button}')) {
            const variantIndex = this.getABTestVariant(emailData.userData.id, 'cta_' + emailData.template);
            const ctaText = ctaVariants[variantIndex % ctaVariants.length];
            content.html = content.html.replace('{cta_button}', ctaText);
        }
        
        // Track A/B test assignment
        this.trackABTestAssignment(emailData, content);
        
        return content;
    }
    
    getABTestVariant(userId, testName) {
        const hashString = userId + testName;
        let hash = 0;
        for (let i = 0; i < hashString.length; i++) {
            const char = hashString.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return Math.abs(hash) % 100;
    }
    
    /**
     * TEMPLATE BUILDERS
     */
    buildHTMLTemplate(content, emailData) {
        return `
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>${this.templates[emailData.template]?.subject || 'FractalMerch'}</title>
            <style>
                ${this.getEmailCSS()}
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <img src="${window.location.origin}/assets/images/logo.png" alt="FractalMerch" class="logo">
                </div>
                <div class="email-content">
                    ${content}
                </div>
                <div class="email-footer">
                    <p>¿No querés recibir más emails? <a href="${this.config.unsubscribeUrl}?token=${emailData.userData.unsubscribe_token}">Cancelar suscripción</a></p>
                    <p>FractalMerch - Diseños únicos, calidad premium</p>
                </div>
            </div>
            <img src="${this.config.trackingPixelUrl}?id=${emailData.userData.id}&campaign=${emailData.campaignType}" width="1" height="1" style="display:none;">
        </body>
        </html>
        `;
    }
    
    buildTextTemplate(content, emailData) {
        // Strip HTML and format for text email
        return content
            .replace(/<[^>]*>/g, '')
            .replace(/\s+/g, ' ')
            .trim() + 
            `\n\n---\nFractalMerch - Diseños únicos, calidad premium\nCancelar suscripción: ${this.config.unsubscribeUrl}?token=${emailData.userData.unsubscribe_token}`;
    }
    
    /**
     * EMAIL CONTENT TEMPLATES
     */
    getWelcomeEmailContent() {
        return `
        <h1>¡Hola {first_name}! 👋</h1>
        <p>Te damos la bienvenida a FractalMerch, donde tu creatividad no tiene límites.</p>
        <div class="welcome-benefits">
            <h3>¿Qué podés hacer?</h3>
            <ul>
                <li>🎨 Crear diseños únicos con nuestro editor</li>
                <li>👕 Personalizar remeras, buzos y más</li>
                <li>🚚 Recibir tu producto en 3-5 días</li>
                <li>💯 Garantía de calidad premium</li>
            </ul>
        </div>
        <div class="cta-section">
            <a href="${window.location.origin}/customize-shirt.php" class="cta-button">Crear Mi Primera Remera</a>
        </div>
        <p>¡Tu viaje creativo empieza ahora!</p>
        `;
    }
    
    getCartReminderContent() {
        return `
        <h1>¡Hola {first_name}!</h1>
        <p>Notamos que dejaste algunos productos increíbles en tu carrito.</p>
        <div class="cart-items">
            {cart_items}
        </div>
        <p>No dejes que tu creatividad se pierda. ¡Finalizá tu pedido ahora!</p>
        <div class="cta-section">
            <a href="${window.location.origin}/checkout.php" class="cta-button">{cta_button}</a>
        </div>
        <p><strong>¡Oferta especial!</strong> Usá el código <strong>{discount_code}</strong> y obtené un 10% de descuento.</p>
        `;
    }
    
    getOrderConfirmationContent() {
        return `
        <h1>¡Gracias por tu pedido, {first_name}! 🎉</h1>
        <p>Tu remera personalizada está siendo creada con mucho amor y atención al detalle.</p>
        <div class="order-details">
            <h3>Detalles de tu pedido:</h3>
            {order_details}
        </div>
        <div class="timeline">
            <h3>¿Qué sigue?</h3>
            <ol>
                <li>📋 Revisamos tu diseño (hoy)</li>
                <li>🖨️ Imprimimos con sublimación HD (1-2 días)</li>
                <li>📦 Empacamos con cuidado (1 día)</li>
                <li>🚚 Enviamos a tu dirección (2-5 días)</li>
            </ol>
        </div>
        <p>Te mantendremos informado en cada paso. ¡Gracias por confiar en nosotros!</p>
        `;
    }
    
    /**
     * ANALYTICS AND TRACKING
     */
    trackEmailInteractions() {
        // Track email opens via hidden images
        // Track clicks via link wrapping
        // This would be implemented on the backend
    }
    
    updateAnalytics(event, emailData) {
        this.analytics.metrics[event]++;
        
        if (!this.analytics.campaign_performance[emailData.campaignType]) {
            this.analytics.campaign_performance[emailData.campaignType] = {
                sent: 0, opened: 0, clicked: 0, converted: 0
            };
        }
        
        this.analytics.campaign_performance[emailData.campaignType][event]++;
        
        // Save analytics to localStorage
        localStorage.setItem('email_analytics', JSON.stringify(this.analytics));
    }
    
    /**
     * UTILITY FUNCTIONS
     */
    generateEmailId() {
        return 'email_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    generateTrackingId() {
        return 'track_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    generateDiscountCode(userData) {
        const codes = ['WELCOME15', 'SAVE10', 'FIRST15', 'CREATE10'];
        return codes[Math.floor(Math.random() * codes.length)];
    }
    
    getScheduledEmails() {
        return JSON.parse(localStorage.getItem('scheduled_emails') || '[]');
    }
    
    saveScheduledEmails(emails) {
        localStorage.setItem('scheduled_emails', JSON.stringify(emails));
    }
    
    markEmailAsSent(emailId) {
        const emails = this.getScheduledEmails();
        const emailIndex = emails.findIndex(e => e.id === emailId);
        if (emailIndex !== -1) {
            emails[emailIndex].status = 'sent';
            emails[emailIndex].sentAt = Date.now();
            this.saveScheduledEmails(emails);
        }
    }
    
    getEmailCSS() {
        return `
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .email-container { max-width: 600px; margin: 0 auto; background: #fff; }
        .email-header { text-align: center; padding: 20px; background: linear-gradient(135deg, #d2691e 0%, #008b8b 50%, #3e2723 100%); }
        .logo { max-width: 150px; height: auto; }
        .email-content { padding: 30px; }
        .cta-button { display: inline-block; background: #d2691e; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .email-footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        `;
    }
    
    isUserEligible(userData, campaignType) {
        // Check user preferences, unsubscribe status, etc.
        return !userData.unsubscribed && !userData.email_bounced;
    }
    
    handleEmailError(emailData, error) {
        const emails = this.getScheduledEmails();
        const emailIndex = emails.findIndex(e => e.id === emailData.id);
        
        if (emailIndex !== -1) {
            emails[emailIndex].attempts++;
            
            if (emails[emailIndex].attempts < this.config.retryAttempts) {
                // Retry with exponential backoff
                emails[emailIndex].scheduledFor = Date.now() + (emails[emailIndex].attempts * 60000);
                emails[emailIndex].status = 'scheduled';
            } else {
                emails[emailIndex].status = 'failed';
                emails[emailIndex].error = error.message;
            }
            
            this.saveScheduledEmails(emails);
        }
    }
    
    /**
     * PUBLIC API
     */
    getAnalytics() {
        return this.analytics;
    }
    
    pauseCampaign(campaignType) {
        this.campaigns[campaignType].active = false;
        console.log(`Campaign ${campaignType} paused`);
    }
    
    resumeCampaign(campaignType) {
        this.campaigns[campaignType].active = true;
        console.log(`Campaign ${campaignType} resumed`);
    }
    
    clearScheduledEmails() {
        localStorage.removeItem('scheduled_emails');
        console.log('All scheduled emails cleared');
    }
}

// Auto-initialize
window.addEventListener('DOMContentLoaded', () => {
    if (window.emailMarketing) return;
    
    window.emailMarketing = new EmailMarketingSystem();
    
    // Expose API
    window.EmailMarketing = {
        trigger: (campaign, userData) => window.emailMarketing.triggerCampaign(campaign, userData),
        analytics: () => window.emailMarketing.getAnalytics(),
        pause: (campaign) => window.emailMarketing.pauseCampaign(campaign),
        resume: (campaign) => window.emailMarketing.resumeCampaign(campaign)
    };
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EmailMarketingSystem;
}