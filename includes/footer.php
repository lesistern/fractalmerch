    </main>

    <!-- WhatsApp flotante -->
    <div class="whatsapp-float" id="whatsappFloat">
        <a href="https://wa.me/1234567890" target="_blank">
            <img src="assets/images/whatsapp-logo.png" alt="WhatsApp" class="whatsapp-icon">
        </a>
    </div>

    <!-- Footer con redes sociales -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="social-icons">
                <a href="#" class="social-icon facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="social-icon instagram">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        // Funcionalidad de tema oscuro/claro
        class ThemeManager {
            constructor() {
                this.themeToggle = document.getElementById('theme-toggle');
                this.body = document.body;
                this.init();
            }

            init() {
                // Detectar preferencia del sistema
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const savedTheme = localStorage.getItem('theme');
                
                // Aplicar tema inicial
                if (savedTheme) {
                    this.applyTheme(savedTheme === 'dark');
                } else {
                    this.applyTheme(prefersDark);
                }

                // Event listener para el toggle
                this.themeToggle.addEventListener('change', () => {
                    const isDark = this.themeToggle.checked;
                    this.applyTheme(isDark);
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                });

                // Escuchar cambios en las preferencias del sistema
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                    if (!localStorage.getItem('theme')) {
                        this.applyTheme(e.matches);
                    }
                });
            }

            applyTheme(isDark) {
                if (isDark) {
                    this.body.classList.add('dark-mode');
                    this.themeToggle.checked = true;
                } else {
                    this.body.classList.remove('dark-mode');
                    this.themeToggle.checked = false;
                }
            }
        }

        // Script para WhatsApp flotante que se mueve con scroll
        function initWhatsAppFloat() {
            window.addEventListener('scroll', function() {
                const whatsappFloat = document.getElementById('whatsappFloat');
                if (!whatsappFloat) return;
                
                const scrollY = window.scrollY;
                const windowHeight = window.innerHeight;
                const documentHeight = document.documentElement.scrollHeight;
                
                // Calcular posición basada en el scroll
                const scrollPercent = scrollY / (documentHeight - windowHeight);
                const newBottom = 20 + (scrollPercent * 100);
                
                whatsappFloat.style.bottom = Math.min(newBottom, 120) + 'px';
            });
        }

        // Funcionalidad del buscador móvil
        class MobileSearch {
            constructor() {
                this.searchBtn = document.getElementById('mobile-search-btn');
                this.searchContainer = document.getElementById('mobile-search-container');
                this.searchClose = document.getElementById('mobile-search-close');
                this.searchInput = document.querySelector('.mobile-search-input');
                this.init();
            }

            init() {
                if (this.searchBtn) {
                    this.searchBtn.addEventListener('click', () => this.openSearch());
                }
                if (this.searchClose) {
                    this.searchClose.addEventListener('click', () => this.closeSearch());
                }
                // Cerrar al presionar Escape
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.closeSearch();
                    }
                });
            }

            openSearch() {
                this.searchContainer.classList.add('active');
                setTimeout(() => {
                    this.searchInput.focus();
                }, 300);
            }

            closeSearch() {
                this.searchContainer.classList.remove('active');
                this.searchInput.value = '';
            }
        }

        // Inicializar cuando la página cargue
        document.addEventListener('DOMContentLoaded', function() {
            new ThemeManager();
            initWhatsAppFloat();
            new MobileSearch();
        });
    </script>
</body>
</html>