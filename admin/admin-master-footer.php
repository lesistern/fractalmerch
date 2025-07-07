            </div>
        </main>
    </div>

    <!-- Admin Footer -->
    <footer style="background: white; border-top: 1px solid #e9ecef; padding: 20px 0; margin-top: 50px; margin-left: 200px; transition: margin-left 0.3s ease;">
        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 30px;">
            <div>
                <p style="margin: 0; color: #6c757d; font-size: 14px;">&copy; <?php echo date('Y'); ?> FractalMerch. Sistema de administración.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <span style="background: #e9ecef; color: #6c757d; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">v2.1.0</span>
                <a href="../" target="_blank" style="color: #007bff; text-decoration: none; font-size: 14px; font-weight: 500;">Ver Sitio</a>
                <a href="help.php" style="color: #007bff; text-decoration: none; font-size: 14px; font-weight: 500;">Ayuda</a>
            </div>
        </div>
    </footer>

    <!-- Global Admin Scripts -->
    <script>
    // Global admin utilities
    window.AdminUtils = {
        // Show loading state
        showLoading: function(element) {
            if (element) {
                element.disabled = true;
                const originalHtml = element.innerHTML;
                element.innerHTML = '<i class="admin-loading"></i>' + originalHtml;
                element.dataset.originalHtml = originalHtml;
            }
        },
        
        // Hide loading state
        hideLoading: function(element) {
            if (element && element.dataset.originalHtml) {
                element.disabled = false;
                element.innerHTML = element.dataset.originalHtml;
                delete element.dataset.originalHtml;
            }
        },
        
        // Show notification
        showNotification: function(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                notification.classList.add('removing');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 4000);
        },
        
        // Get notification icon
        getNotificationIcon: function(type) {
            const icons = {
                'success': 'check-circle',
                'error': 'exclamation-circle',
                'warning': 'exclamation-triangle',
                'info': 'info-circle'
            };
            return icons[type] || 'info-circle';
        },
        
        // Confirm dialog
        confirm: function(message, callback) {
            if (window.confirm(message)) {
                callback();
            }
        },
        
        // Format currency
        formatCurrency: function(amount, currency = 'ARS') {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: currency
            }).format(amount);
        },
        
        // Format date
        formatDate: function(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return new Date(date).toLocaleDateString('es-AR', { ...defaultOptions, ...options });
        },
        
        // Copy to clipboard
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showNotification('Copiado al portapapeles', 'success');
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                this.showNotification('Copiado al portapapeles', 'success');
            }
        },
        
        // Validate email
        validateEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // Modal utilities
        modal: {
            show: function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            },
            
            hide: function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            },
            
            hideAll: function() {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
                document.body.style.overflow = '';
            }
        }
    };

    // Global tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching for all admin pages
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('tab-btn')) {
                const tabName = e.target.dataset.tab;
                if (tabName) {
                    // Update button states
                    document.querySelectorAll('.tab-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    e.target.classList.add('active');
                    
                    // Update content
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    const tabContent = document.getElementById(`${tabName}-tab`);
                    if (tabContent) {
                        tabContent.classList.add('active');
                    }
                }
            }
        });
        
        // Global modal close functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('close')) {
                const modal = e.target.closest('.modal');
                if (modal) {
                    AdminUtils.modal.hide(modal.id);
                }
            }
            
            // Close modal when clicking outside
            if (e.target.classList.contains('modal')) {
                AdminUtils.modal.hide(e.target.id);
            }
        });
        
        // Escape key to close modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                AdminUtils.modal.hideAll();
            }
        });
        
        // Auto-hide notifications when clicked
        document.addEventListener('click', function(e) {
            if (e.target.closest('.notification')) {
                const notification = e.target.closest('.notification');
                notification.classList.add('removing');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        });
        
        // Form validation helpers
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let hasErrors = false;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.style.borderColor = '#dc3545';
                        hasErrors = true;
                    } else {
                        field.style.borderColor = '';
                    }
                    
                    // Email validation
                    if (field.type === 'email' && field.value && !AdminUtils.validateEmail(field.value)) {
                        field.style.borderColor = '#dc3545';
                        hasErrors = true;
                    }
                });
                
                if (hasErrors) {
                    e.preventDefault();
                    AdminUtils.showNotification('Por favor completa todos los campos requeridos', 'error');
                }
            });
        });
    });

    // Auto-refresh data every 5 minutes for admin pages
    setInterval(function() {
        if (typeof refreshAdminData === 'function') {
            refreshAdminData();
        }
    }, 300000); // 5 minutes

    // Check for updates notification (placeholder)
    if (localStorage.getItem('admin_last_check')) {
        const lastCheck = new Date(localStorage.getItem('admin_last_check'));
        const now = new Date();
        const diffHours = (now - lastCheck) / (1000 * 60 * 60);
        
        if (diffHours > 24) {
            setTimeout(() => {
                AdminUtils.showNotification('Hay actualizaciones disponibles del sistema', 'info');
            }, 3000);
            localStorage.setItem('admin_last_check', now.toISOString());
        }
    } else {
        localStorage.setItem('admin_last_check', new Date().toISOString());
    }

    // Advanced Keyboard Shortcuts Handler
    document.addEventListener('keydown', function(e) {
        // Only handle if Alt is pressed and not in input fields
        if (e.altKey && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
            switch(e.key.toLowerCase()) {
                case 'd':
                    e.preventDefault();
                    window.location.href = 'dashboard.php';
                    break;
                case 's':
                    e.preventDefault();
                    window.location.href = 'statistics.php';
                    break;
                case 'i':
                    e.preventDefault();
                    window.location.href = 'inventory-management.php';
                    break;
                case 'o':
                    e.preventDefault();
                    window.location.href = 'order-management.php';
                    break;
                case 'p':
                    e.preventDefault();
                    window.location.href = 'manage-products.php';
                    break;
                case 'u':
                    e.preventDefault();
                    window.location.href = 'manage-users.php';
                    break;
            }
        }
        
        // Ctrl/Cmd + K for global search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('admin-search');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Quick add new item with Ctrl/Cmd + N
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            const currentPage = window.location.pathname.split('/').pop();
            if (currentPage === 'manage-products.php' && typeof window.adminPanel !== 'undefined') {
                window.adminPanel.showProductForm();
            }
        }
    });

    // Quick Access Toolbar
    function createQuickAccessToolbar() {
        const toolbar = document.createElement('div');
        toolbar.className = 'quick-access-toolbar';
        toolbar.innerHTML = `
            <button class="quick-access-btn" title="Nuevo Producto (Ctrl+N)" onclick="quickAddProduct()">
                <i class="fas fa-plus"></i>
            </button>
            <button class="quick-access-btn" title="Búsqueda Rápida (Ctrl+K)" onclick="quickSearch()">
                <i class="fas fa-search"></i>
            </button>
            <button class="quick-access-btn" title="Ir al Dashboard (Alt+D)" onclick="location.href='dashboard.php'">
                <i class="fas fa-home"></i>
            </button>
        `;
        document.body.appendChild(toolbar);
    }

    // Quick action functions
    function quickAddProduct() {
        if (window.location.pathname.includes('manage-products.php') && typeof window.adminPanel !== 'undefined') {
            window.adminPanel.showProductForm();
        } else {
            window.location.href = 'manage-products.php';
        }
    }

    function quickSearch() {
        const searchInput = document.getElementById('admin-search');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    // Initialize quick access toolbar
    if (!document.querySelector('.quick-access-toolbar')) {
        createQuickAccessToolbar();
    }
    </script>

    <style>
    /* Loading animation for buttons */
    .admin-loading {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top: 2px solid #fff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 8px;
    }

    .btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive footer */
    @media (max-width: 768px) {
        footer {
            margin-left: 0 !important;
        }
        
        footer > div {
            flex-direction: column;
            gap: 15px;
            padding: 0 15px;
        }
        
        footer > div > div:last-child {
            gap: 15px;
        }
    }
    </style>

</body>
</html>