// Funciones principales del sitio
document.addEventListener('DOMContentLoaded', function() {
    
    // Inicializar modo oscuro desde localStorage
    initDarkMode();
    
    // Funcionalidad de tabs en el perfil
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.textContent.toLowerCase().replace(' ', '-');
            
            // Remover clase active de todos los botones y contenidos
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Agregar clase active al botón clickeado y su contenido correspondiente
            this.classList.add('active');
            
            if (tabName.includes('posts')) {
                document.getElementById('posts-tab').classList.add('active');
            } else if (tabName.includes('configuración')) {
                document.getElementById('settings-tab').classList.add('active');
            }
        });
    });
    
    // Auto-desvanecimiento de alertas
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // Confirmación de eliminación
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar este elemento?')) {
                e.preventDefault();
            }
        });
    });
    
    // Funcionalidad de respuesta a comentarios
    const replyLinks = document.querySelectorAll('.reply-link');
    replyLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const commentId = this.getAttribute('data-comment-id');
            const comment = document.getElementById('comment-' + commentId);
            
            // Verificar si ya existe un formulario de respuesta
            let existingForm = comment.querySelector('.reply-form');
            if (existingForm) {
                existingForm.remove();
                return;
            }
            
            // Crear formulario de respuesta
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
            
            // Funcionalidad del botón cancelar
            replyForm.querySelector('.cancel-reply').addEventListener('click', function() {
                replyForm.remove();
            });
        });
    });
    
    // Búsqueda en tiempo real (opcional)
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Aquí podrías implementar búsqueda AJAX
                console.log('Buscando:', this.value);
            }, 500);
        });
    }
    
    // Validación de formularios
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
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor completa todos los campos requeridos');
            }
        });
    });
    
    // Contador de caracteres para textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        if (maxLength) {
            const counter = document.createElement('div');
            counter.className = 'char-counter';
            counter.style.fontSize = '0.875rem';
            counter.style.color = '#666';
            counter.style.textAlign = 'right';
            counter.style.marginTop = '0.25rem';
            
            textarea.parentNode.appendChild(counter);
            
            function updateCounter() {
                const remaining = maxLength - textarea.value.length;
                counter.textContent = `${remaining} caracteres restantes`;
                counter.style.color = remaining < 50 ? '#dc3545' : '#666';
            }
            
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        }
    });
});

// Funciones globales
function showTab(tabName) {
    const tabContents = document.querySelectorAll('.tab-content');
    const tabButtons = document.querySelectorAll('.tab-button');
    
    tabContents.forEach(content => content.classList.remove('active'));
    tabButtons.forEach(button => button.classList.remove('active'));
    
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}

// Función para mostrar/ocultar elementos
function toggle(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}

// Función para copiar texto al portapapeles
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copiado al portapapeles');
    }, function() {
        alert('Error al copiar');
    });
}

// Funciones de modo oscuro
function initDarkMode() {
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    const body = document.body;
    const icon = document.getElementById('dark-mode-icon');
    
    if (isDarkMode) {
        body.classList.remove('light-mode');
        body.classList.add('dark-mode');
        if (icon) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    } else {
        body.classList.remove('dark-mode');
        body.classList.add('light-mode');
        if (icon) {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    }
}

function toggleDarkMode() {
    const body = document.body;
    const icon = document.getElementById('dark-mode-icon');
    const isDarkMode = body.classList.contains('dark-mode');
    
    if (isDarkMode) {
        body.classList.remove('dark-mode');
        body.classList.add('light-mode');
        localStorage.setItem('darkMode', 'false');
        if (icon) {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    } else {
        body.classList.remove('light-mode');
        body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'true');
        if (icon) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    }
}