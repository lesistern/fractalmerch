document.addEventListener('DOMContentLoaded', function() {

    // --- INICIALIZACIÓN GENERAL ---
    initDarkMode();
    initializeTabs();
    initializeAlerts();
    initializeDeleteConfirmations();
    initializeReplyForms();
    initializeSearch();
    initializeFormValidation();
    initializeCharCounters();

    // --- FUNCIONES DE MODO OSCURO ---
    function initDarkMode() {
        const themeToggle = document.getElementById('checkbox');
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        const body = document.body;

        if (!themeToggle || !sunIcon || !moonIcon || !body) {
            console.error('Theme toggle elements not found');
            return;
        }

        const userPreference = localStorage.getItem('darkMode');
        const systemPreference = window.matchMedia('(prefers-color-scheme: dark)').matches;

        let isDarkMode;

        if (userPreference !== null) {
            isDarkMode = userPreference === 'true';
        } else {
            isDarkMode = systemPreference;
            localStorage.setItem('darkMode', isDarkMode.toString());
        }

        updateTheme(isDarkMode, body, themeToggle, sunIcon, moonIcon);

        themeToggle.addEventListener('change', function() {
            const isChecked = this.checked;
            localStorage.setItem('darkMode', isChecked.toString());
            updateTheme(isChecked, body, themeToggle, sunIcon, moonIcon);
        });

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (localStorage.getItem('darkMode') === null) {
                const newIsDarkMode = e.matches;
                localStorage.setItem('darkMode', newIsDarkMode.toString());
                updateTheme(newIsDarkMode, body, themeToggle, sunIcon, moonIcon);
            }
        });
    }

    function updateTheme(isDarkMode, body, toggle, sunIcon, moonIcon) {
        body.classList.toggle('dark-mode', isDarkMode);
        toggle.checked = isDarkMode;
        
        // Mostrar/ocultar iconos correctos
        if (isDarkMode) {
            sunIcon.style.opacity = '0';
            moonIcon.style.opacity = '1';
        } else {
            sunIcon.style.opacity = '1';
            moonIcon.style.opacity = '0';
        }
    }

    // --- FUNCIONALIDAD DE TABS ---
    function initializeTabs() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });
        });
    }

    // --- AUTO-DESVANECIMIENTO DE ALERTAS ---
    function initializeAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    }

    // --- CONFIRMACIÓN DE ELIMINACIÓN ---
    function initializeDeleteConfirmations() {
        document.body.addEventListener('click', function(e) {
            if (e.target.matches('a[href*="delete"]')) {
                if (!confirm('¿Estás seguro de que quieres eliminar este elemento?')) {
                    e.preventDefault();
                }
            }
        });
    }

    // --- FORMULARIOS DE RESPUESTA ---
    function initializeReplyForms() {
        document.body.addEventListener('click', function(e) {
            if (e.target.matches('.reply-link')) {
                e.preventDefault();
                const commentId = e.target.getAttribute('data-comment-id');
                const comment = document.getElementById('comment-' + commentId);
                
                let existingForm = comment.querySelector('.reply-form');
                if (existingForm) {
                    existingForm.remove();
                    return;
                }

                const replyForm = document.createElement('div');
                replyForm.className = 'reply-form';
                replyForm.innerHTML = `
                    <form method="POST" action="">
                        <input type="hidden" name="parent_id" value="${commentId}">
                        <div class="form-group">
                            <textarea name="content" placeholder="Escribe tu respuesta..." required rows="3"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-small">Responder</button>
                            <button type="button" class="btn btn-secondary btn-small cancel-reply">Cancelar</button>
                        </div>
                    </form>
                `;
                comment.appendChild(replyForm);
            }

            if (e.target.matches('.cancel-reply')) {
                e.target.closest('.reply-form').remove();
            }
        });
    }

    // --- BÚSQUEDA ---
    function initializeSearch() {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    console.log('Buscando:', this.value);
                }, 500);
            });
        }
    }

    // --- VALIDACIÓN DE FORMULARIOS ---
    function initializeFormValidation() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.style.borderColor = '#dc3545';
                        isValid = false;
                    } else {
                        field.style.borderColor = '';
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Por favor completa todos los campos requeridos.');
                }
            });
        });
    }

    // --- CONTADOR DE CARACTERES ---
    function initializeCharCounters() {
        const textareas = document.querySelectorAll('textarea[maxlength]');
        textareas.forEach(textarea => {
            const maxLength = textarea.getAttribute('maxlength');
            const counter = document.createElement('div');
            counter.className = 'char-counter';
            
            textarea.parentNode.appendChild(counter);
            
            const updateCounter = () => {
                const remaining = maxLength - textarea.value.length;
                counter.textContent = `${remaining} caracteres restantes`;
                counter.style.color = remaining < 50 ? '#dc3545' : 'inherit';
            };
            
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        });
    }
});
