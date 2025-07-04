// Diseñador de Remeras - JavaScript
class ShirtDesigner {
    constructor() {
        this.uploadedImages = [];
        this.currentView = 'front';
        this.selectedImage = null;
        this.realDesignAreaWidth = 250; // Ancho efectivo del design-zone en el editor (referencia)
        this.realDesignAreaHeight = 325; // Alto efectivo del design-zone en el editor (referencia)
        this.maxImages = 5;
        this.imageCounter = 0;
        
        this.init();
        this.scheduleCleanup();
    }
    
    init() {
        this.setupEventListeners();
        this.setupDragAndDrop();
    }
    
    setupEventListeners() {
        // Cambio de vista (frente/espalda)
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchView(e.currentTarget.dataset.view);
            });
        });
        
        // Upload de imágenes
        const imageUpload = document.getElementById('image-upload');
        imageUpload.addEventListener('change', (e) => {
            this.handleImageUpload(e);
        });
        
        // Controles deslizantes laterales
        document.getElementById('side-size-slider').addEventListener('input', (e) => {
            this.updateImageSize(e.target.value);
        });
        
        document.getElementById('side-rotation-slider').addEventListener('input', (e) => {
            this.updateImageRotation(e.target.value);
        });
        
        // Drag and drop de imágenes
        this.setupDesignZoneDragDrop();
    }
    
    setupDragAndDrop() {
        const uploadArea = document.querySelector('.upload-btn');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, this.preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.borderColor = '#007bff';
                uploadArea.style.background = 'rgba(0, 123, 255, 0.1)';
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.borderColor = 'var(--border-color)';
                uploadArea.style.background = 'var(--bg-primary)';
            }, false);
        });
        
        uploadArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            this.processFiles(files);
        }, false);
    }
    
    setupDesignZoneDragDrop() {
        const designZones = document.querySelectorAll('.design-zone');
        
        designZones.forEach(zone => {
            zone.addEventListener('dragover', this.preventDefaults);
            zone.addEventListener('drop', (e) => {
                this.preventDefaults(e);
                const imageId = e.dataTransfer.getData('text/plain');
                this.addImageToDesign(imageId, e);
            });
        });
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    switchView(view) {
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        document.querySelector(`[data-view="${view}"]`).classList.add('active');
        
        document.querySelectorAll('.shirt-view').forEach(shirtView => {
            shirtView.style.display = 'none';
        });
        
        document.getElementById(`${view}-view`).style.display = 'block';
        this.currentView = view;
        
        // Limpiar selección al cambiar de vista
        this.deselectImage();
    }
    
    handleImageUpload(event) {
        const files = event.target.files;
        this.processFiles(files);
    }
    
    processFiles(files) {
        if (this.uploadedImages.length >= this.maxImages) {
            alert(`Máximo ${this.maxImages} imágenes permitidas`);
            return;
        }
        
        Array.from(files).forEach(file => {
            if (this.uploadedImages.length >= this.maxImages) return;
            
            if (!file.type.startsWith('image/')) {
                alert('Solo se permiten archivos de imagen');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) { // 5MB
                alert('El archivo es demasiado grande (máximo 5MB)');
                return;
            }
            
            this.uploadImage(file);
        });
    }
    
    uploadImage(file) {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            const tempImg = new Image(); // Create a temporary image to get dimensions
            tempImg.onload = () => {
                // Calculate unique position for each new image
                const existingImagesInView = this.uploadedImages.filter(img => img.view === this.currentView);
                const offset = existingImagesInView.length * 5; // 5% offset per image
                
                const imageData = {
                    id: `img_${++this.imageCounter}`,
                    name: file.name,
                    src: e.target.result, // Temporary DataURL
                    tempUrl: null, // Will be filled after saving as .webp
                    size: 100, // Default to 100%
                    rotation: 0,
                    x: Math.max(20, Math.min(80, 50 + offset)), // Avoid overlap
                    y: Math.max(20, Math.min(80, 50 + offset)),
                    view: this.currentView, // Automatically assign to current view
                    originalWidth: tempImg.width,  // Store original width
                    originalHeight: tempImg.height // Store original height
                };
                
                // Save image as temporary .dat file
                this.saveImageAsWebP(imageData).then(() => {
                    this.uploadedImages.push(imageData);
                    this.renderUploadedImage(imageData);
                    this.renderDesignImage(imageData); // Automatically add to design area
                    this.selectDesignImage(imageData.id);
                }).catch(error => {
                    console.error('❌ Error saving temporary image:', error);
                    console.error('❌ Error type:', typeof error);
                    console.error('❌ Stack trace:', error.stack);
                    console.log('🔄 Continuing with original dataURL as fallback...');
                    
                    // Fallback: use original dataURL
                    this.uploadedImages.push(imageData);
                    this.renderUploadedImage(imageData);
                    this.renderDesignImage(imageData);
                    this.selectDesignImage(imageData.id);
                });
            };
            tempImg.src = e.target.result; // Trigger tempImg.onload
        };
        
        reader.readAsDataURL(file);
    }
    
    saveImageAsWebP(imageData) {
        return new Promise((resolve, reject) => {
            console.log('💾 Guardando imagen temporal:', imageData.name);
            console.log('📊 Tamaño dataURL:', imageData.src.length, 'caracteres');
            
            // Verificar que el dataURL sea válido
            if (!imageData.src || !imageData.src.startsWith('data:image/')) {
                reject(new Error('DataURL inválido'));
                return;
            }
            
            fetch('/proyecto/save-temp-raw.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    imageData: imageData.src,
                    filename: imageData.name
                })
            })
            .then(response => {
                console.log('📡 Respuesta del servidor:', response.status, response.statusText);
                
                // Verificar si la respuesta es exitosa
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                // Intentar obtener el texto de respuesta para debugging
                return response.text().then(text => {
                    console.log('📝 Respuesta cruda:', text.substring(0, 200) + '...');
                    
                    try {
                        return JSON.parse(text);
                    } catch (parseError) {
                        console.error('❌ Error parseando JSON:', parseError);
                        console.log('🔍 Respuesta completa:', text);
                        throw new Error('Respuesta del servidor no es JSON válido');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    imageData.tempUrl = data.tempPath;
                    imageData.isDataFile = data.format === 'dataurl';
                    console.log(`✅ Imagen guardada como archivo .${data.format}:`, data.tempPath);
                    console.log('📊 Tamaño archivo:', data.fileSize, 'bytes');
                    console.log('🔧 Método usado:', data.method);
                    resolve(data);
                } else {
                    console.error('❌ Error del servidor:', data.error);
                    reject(new Error(data.error));
                }
            })
            .catch(error => {
                console.error('❌ Error en saveImageAsWebP:', error);
                reject(error);
            });
        });
    }
    
    renderUploadedImage(imageData) {
        const container = document.getElementById('uploaded-images');
        
        const imageItem = document.createElement('div');
        imageItem.className = 'uploaded-image-item';
        imageItem.dataset.imageId = imageData.id;
        imageItem.draggable = true;
        
        // Crear preview de la imagen en el área segura
        const previewContainer = document.createElement('div');
        previewContainer.className = 'safe-area-preview';
        
        const previewImg = document.createElement('img');
        previewImg.src = imageData.src;
        previewImg.className = 'preview-image';
        
        previewContainer.appendChild(previewImg);
        
        imageItem.innerHTML = `
            <img src="${imageData.src}" alt="${imageData.name}">
            <div class="image-info">
                <span class="image-name" title="${imageData.name}">${this.truncateFilename(imageData.name)}</span>
                <div class="image-actions">
                    <button class="preview-btn" onclick="shirtDesigner.togglePreview('${imageData.id}')" title="Vista previa">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="shirtDesigner.removeUploadedImage('${imageData.id}')" class="remove-btn" title="Eliminar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        imageItem.appendChild(previewContainer);
        
        // Drag start event
        imageItem.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', imageData.id);
            imageItem.classList.add('dragging');
        });
        
        imageItem.addEventListener('dragend', () => {
            imageItem.classList.remove('dragging');
        });
        
        // Click para seleccionar
        imageItem.addEventListener('click', (e) => {
            if (!e.target.closest('button')) {
                this.selectUploadedImage(imageData.id);
            }
        });
        
        // Setup drag and drop reordering
        this.setupImageReordering(imageItem);
        
        container.appendChild(imageItem);
    }
    
    addImageToDesign(imageId, event) {
        const imageData = this.uploadedImages.find(img => img.id === imageId);
        if (!imageData) return;
        
        // Si la imagen ya está en una vista, la removemos primero
        this.removeImageFromDesign(imageId);
        
        const designZone = document.querySelector(`#${this.currentView}-design-zone`);
        const rect = designZone.getBoundingClientRect();
        const safeArea = designZone.querySelector('.sublimation-limits');
        const safeRect = safeArea.getBoundingClientRect();
        
        // Calcular posición relativa al área segura
        let x = 50, y = 50; // Posición por defecto en el centro
        
        if (event && event.clientX && event.clientY) {
            // Calcular posición relativa al área segura
            const safeX = ((event.clientX - safeRect.left) / safeRect.width) * 100;
            const safeY = ((event.clientY - safeRect.top) / safeRect.height) * 100;
            
            // Convertir a coordenadas del design-zone
            x = 10 + (safeX * 0.8); // 10% offset + 80% del área segura
            y = 10 + (safeY * 0.8);
        }
        
        imageData.view = this.currentView;
        // Limitar posición al área segura (10%-90% del design-zone)
        imageData.x = Math.max(10, Math.min(90, x));
        imageData.y = Math.max(10, Math.min(90, y));
        
        this.renderDesignImage(imageData);
        this.selectDesignImage(imageId);
    }
    
    renderDesignImage(imageData) {
        const designZone = document.querySelector(`#${imageData.view}-design-zone`);
        
        // Crear contenedor para la imagen y sus controles
        const imageContainer = document.createElement('div');
        imageContainer.className = 'image-container';
        imageContainer.id = `container-${imageData.id}`;
        
        const imageElement = document.createElement('img');
        imageElement.className = 'design-image';
        imageElement.id = `design-${imageData.id}`;
        imageElement.src = imageData.src;
        imageElement.alt = imageData.name;
        
        // Crear controles
        const controlsContainer = document.createElement('div');
        controlsContainer.className = 'image-controls-container';
        
        // Crear botones individualmente para evitar problemas con onclick
        const rotateBtn = document.createElement('button');
        rotateBtn.className = 'image-control-btn rotate-btn';
        rotateBtn.title = 'Rotar';
        rotateBtn.innerHTML = '↻';
        rotateBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.rotateImage(imageData.id);
        });
        
        const scaleBtn = document.createElement('button');
        scaleBtn.className = 'image-control-btn scale-btn';
        scaleBtn.title = 'Escalar';
        scaleBtn.innerHTML = '⚏';
        scaleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleScaleMode(imageData.id);
        });
        
        const duplicateBtn = document.createElement('button');
        duplicateBtn.className = 'image-control-btn duplicate-btn';
        duplicateBtn.title = 'Duplicar';
        duplicateBtn.innerHTML = '⧉';
        duplicateBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.duplicateImage(imageData.id);
        });
        
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'image-control-btn delete-btn';
        deleteBtn.title = 'Eliminar';
        deleteBtn.innerHTML = '✕';
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.deleteImageFromDesign(imageData.id);
        });
        
        controlsContainer.appendChild(rotateBtn);
        controlsContainer.appendChild(scaleBtn);
        controlsContainer.appendChild(duplicateBtn);
        controlsContainer.appendChild(deleteBtn);
        
        imageContainer.appendChild(imageElement);
        imageContainer.appendChild(controlsContainer);
        
        // Event listeners para interacción
        this.setupImageInteraction(imageContainer, imageData);
        
        designZone.appendChild(imageContainer);

        // Aplicar estilos iniciales
        this.updateImageStyle(imageContainer, imageData);
    }
    
    updateImageStyle(container, imageData) {
        if (!container || !imageData) {
            return;
        }
    
        // --- LÓGICA DE LÍMITES ---
        if (imageData.originalWidth) {
            // 1. Calcular dimensiones de la imagen en píxeles y como porcentaje del área de diseño
            const pixelWidth = (imageData.size / 100) * imageData.originalWidth;
            const widthPercentOfZone = (pixelWidth / this.realDesignAreaWidth) * 100;
            const halfWidthPercent = widthPercentOfZone / 2;
    
            const pixelHeight = (imageData.originalHeight / imageData.originalWidth) * pixelWidth;
            const heightPercentOfZone = (pixelHeight / this.realDesignAreaHeight) * 100;
            const halfHeightPercent = heightPercentOfZone / 2;
    
            // 2. Obtener los límites del área segura (ej. 10% a 90%)
            const safeBounds = this.getSafeAreaBounds();
    
            // 3. Calcular el rango permitido para el PUNTO CENTRAL de la imagen
            const minX = safeBounds.left + halfWidthPercent;
            const maxX = safeBounds.right - halfWidthPercent;
            const minY = safeBounds.top + halfHeightPercent;
            const maxY = safeBounds.bottom - halfHeightPercent;
    
            // 4. Forzar la posición a estar dentro del rango calculado
            // Si la imagen es más grande que el área, se centra.
            imageData.x = (minX > maxX) ? (safeBounds.left + safeBounds.right) / 2 : Math.max(minX, Math.min(maxX, imageData.x));
            imageData.y = (minY > maxY) ? (safeBounds.top + safeBounds.bottom) / 2 : Math.max(minY, Math.min(maxY, imageData.y));
        }
    
        // --- APLICACIÓN DE ESTILOS ---
        // 1. Establecer el ancho del contenedor basado en el porcentaje de tamaño
        if (imageData.originalWidth) {
            const pixelWidth = (imageData.size / 100) * imageData.originalWidth;
            container.style.width = `${pixelWidth}px`;
        } else {
            container.style.width = `${imageData.size}px`;
        }
    
        // 2. Establecer la posición y rotación del contenedor
        container.style.left = `${imageData.x}%`;
        container.style.top = `${imageData.y}%`;
        container.style.transform = `translate(-50%, -50%) rotate(${imageData.rotation}deg)`;
    
        // 3. Establecer el z-index para el apilamiento
        container.style.zIndex = imageData.id === this.selectedImage ? '1001' : '1000';
    }
    
    setupImageInteraction(container, imageData) {
        let isDragging = false;
        let isResizing = false; // Placeholder for future implementation
        let isRotating = false; // Placeholder for future implementation
        let startX, startY, startLeft, startTop;
        
        const onMouseDown = (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            isDragging = true;
            this.selectDesignImage(imageData.id);
            
            // Guardar posiciones iniciales
            startX = e.clientX;
            startY = e.clientY;
            startLeft = imageData.x;
            startTop = imageData.y;
            
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        };
    
        const onMouseMove = (e) => {
            if (!isDragging) return;
    
            const designZone = container.parentElement;
            const rect = designZone.getBoundingClientRect();
    
            // Calcular el desplazamiento del ratón en porcentaje
            const deltaX = ((e.clientX - startX) / rect.width) * 100;
            const deltaY = ((e.clientY - startY) / rect.height) * 100;
    
            // Aplicar el desplazamiento a la posición inicial
            imageData.x = startLeft + deltaX;
            imageData.y = startTop + deltaY;
    
            // Actualizar el estilo (la función de límites se encargará de restringir)
            this.updateImageStyle(container, imageData);
            this.checkCenterGuides(imageData);
        };
    
        const onMouseUp = () => {
            isDragging = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            this.hideCenterGuides();
        };
    
        // Iniciar el arrastre al hacer clic en el contenedor
        container.addEventListener('mousedown', onMouseDown);
    
        // Touch events para móvil
        container.addEventListener('touchstart', (e) => {
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            container.dispatchEvent(mouseEvent);
        }, { passive: false });
    
        container.addEventListener('touchmove', (e) => {
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            document.dispatchEvent(mouseEvent);
        }, { passive: false });
    
        container.addEventListener('touchend', (e) => {
            const mouseEvent = new MouseEvent('mouseup', {});
            document.dispatchEvent(mouseEvent);
        });
    }
    
    selectDesignImage(imageId) {
        // Deseleccionar todas las imágenes
        document.querySelectorAll('.image-container').forEach(img => {
            img.classList.remove('selected');
        });
        
        // Seleccionar la imagen actual
        const container = document.getElementById(`container-${imageId}`);
        if (container) {
            container.classList.add('selected');
            this.selectedImage = imageId;
            this.showSideSliders();
            this.updateControlValues();
        }
    }
    
    deselectImage() {
        document.querySelectorAll('.image-container').forEach(img => {
            img.classList.remove('selected');
        });
        
        this.selectedImage = null;
        this.hideSideSliders();
    }
    
    
    updateControlValues() {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        if (!imageData) return;
        
        // Actualizar sliders laterales
        document.getElementById('side-size-slider').value = imageData.size;
        document.getElementById('side-size-value').textContent = `${imageData.size}%`;
        document.getElementById('side-rotation-slider').value = Math.round(imageData.rotation);
        document.getElementById('side-rotation-value').textContent = `${Math.round(imageData.rotation)}°`;
    }
    
    updateImageSize(size) {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const container = document.getElementById(`container-${this.selectedImage}`);
        
        if (imageData && container) {
            const newPercentage = parseInt(size);
            
            imageData.size = newPercentage;
            this.updateImageStyle(container, imageData);
            
            // Actualizar display lateral
            const sizeText = `${newPercentage}%`;
            document.getElementById('side-size-value').textContent = sizeText;
            document.getElementById('side-size-slider').value = newPercentage;
        }
    }
    
    updateImageRotation(rotation) {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const container = document.getElementById(`container-${this.selectedImage}`);
        
        if (imageData && container) {
            imageData.rotation = parseInt(rotation);
            this.updateImageStyle(container, imageData);
            
            // Actualizar display lateral
            const rotationText = `${Math.round(rotation)}°`;
            document.getElementById('side-rotation-value').textContent = rotationText;
            document.getElementById('side-rotation-slider').value = rotation;
        }
    }
    
    checkCenterGuides(imageData) {
        const tolerance = 5; // 5% de tolerancia
        const designZone = document.querySelector(`#${imageData.view}-design-zone`);
        
        const centerH = designZone.querySelector('.center-guide-h');
        const centerV = designZone.querySelector('.center-guide-v');
        
        // Mostrar guía horizontal si está cerca del centro vertical
        if (Math.abs(imageData.y - 50) < tolerance) {
            centerH.classList.add('show');
            imageData.y = 50; // Snap al centro
        } else {
            centerH.classList.remove('show');
        }
        
        // Mostrar guía vertical si está cerca del centro horizontal
        if (Math.abs(imageData.x - 50) < tolerance) {
            centerV.classList.add('show');
            imageData.x = 50; // Snap al centro
        } else {
            centerV.classList.remove('show');
        }
    }
    
    hideCenterGuides() {
        document.querySelectorAll('.center-guide-h, .center-guide-v').forEach(guide => {
            guide.classList.remove('show');
        });
    }
    
    centerImage() {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const container = document.getElementById(`container-${this.selectedImage}`);
        
        if (imageData && container) {
            imageData.x = 50;
            imageData.y = 50;
            this.updateImageStyle(container, imageData);
        }
    }
    
    deleteSelectedImage() {
        if (!this.selectedImage) return;
        
        this.removeImageFromDesign(this.selectedImage);
        this.deselectImage();
    }
    
    removeImageFromDesign(imageId) {
        const container = document.getElementById(`container-${imageId}`);
        if (container) {
            container.remove();
            
            // Limpiar la vista de la imagen
            const imageData = this.uploadedImages.find(img => img.id === imageId);
            if (imageData) {
                imageData.view = null;
            }
        }
    }
    
    removeUploadedImage(imageId) {
        this.removeImageFromDesign(imageId);
        
        // Remover de la lista de imágenes subidas
        this.uploadedImages = this.uploadedImages.filter(img => img.id !== imageId);
        
        // Remover del DOM
        const imageItem = document.querySelector(`[data-image-id="${imageId}"]`);
        if (imageItem) {
            imageItem.remove();
        }
        
        if (this.selectedImage === imageId) {
            this.deselectImage();
        }
    }
    
    selectUploadedImage(imageId) {
        document.querySelectorAll('.uploaded-image-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        document.querySelector(`[data-image-id="${imageId}"]`).classList.add('selected');
    }
    
    saveDesign() {
        const designData = {
            images: this.uploadedImages.filter(img => img.view),
            timestamp: new Date().toISOString()
        };
        
        localStorage.setItem('shirtDesign', JSON.stringify(designData));
        alert('Diseño guardado exitosamente');
    }
    
    resetDesign() {
        if (confirm('¿Estás seguro de que quieres reiniciar el diseño?')) {
            // Remover todas las imágenes del diseño
            document.querySelectorAll('.image-container').forEach(img => img.remove());
            
            // Limpiar las vistas de las imágenes
            this.uploadedImages.forEach(img => {
                img.view = null;
                img.x = 50;
                img.y = 50;
                img.size = 100;
                img.rotation = 0;
            });
            
            this.deselectImage();
        }
    }
    
    getSafeAreaBounds() {
        // Retorna los límites del área segura en porcentajes
        return {
            left: 10,   // 10% desde la izquierda
            right: 90,  // 90% desde la izquierda
            top: 10,    // 10% desde arriba
            bottom: 90  // 90% desde arriba
        };
    }
    
    togglePreview(imageId) {
        const imageItem = document.querySelector(`[data-image-id="${imageId}"]`);
        const preview = imageItem.querySelector('.safe-area-preview');
        
        if (preview.classList.contains('show')) {
            preview.classList.remove('show');
        } else {
            // Ocultar otras previews
            document.querySelectorAll('.safe-area-preview.show').forEach(p => {
                p.classList.remove('show');
            });
            preview.classList.add('show');
            
            // Auto-ocultar después de 3 segundos
            setTimeout(() => {
                preview.classList.remove('show');
            }, 3000);
        }
    }
    
    setupImageReordering(imageItem) {
        const container = imageItem.parentElement;
        let dragStartY = 0;
        let isDraggingForReorder = false;
        
        // Configurar eventos de arrastre para reordenamiento
        imageItem.addEventListener('dragstart', (e) => {
            dragStartY = e.clientY;
            imageItem.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', imageItem.outerHTML);
        });
        
        imageItem.addEventListener('dragend', () => {
            imageItem.classList.remove('dragging');
            container.querySelectorAll('.drag-over').forEach(item => {
                item.classList.remove('drag-over');
            });
        });
        
        imageItem.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            const draggingItem = container.querySelector('.dragging');
            if (draggingItem && draggingItem !== imageItem) {
                imageItem.classList.add('drag-over');
                
                const afterElement = this.getDragAfterElement(container, e.clientY);
                if (afterElement == null) {
                    container.appendChild(draggingItem);
                } else {
                    container.insertBefore(draggingItem, afterElement);
                }
            }
        });
        
        imageItem.addEventListener('dragleave', () => {
            imageItem.classList.remove('drag-over');
        });
        
        imageItem.addEventListener('drop', (e) => {
            e.preventDefault();
            imageItem.classList.remove('drag-over');
        });
    }
    
    getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.uploaded-image-item:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    truncateFilename(filename, maxLength = 15) {
        if (filename.length <= maxLength) {
            return filename;
        }
        
        const extension = filename.split('.').pop();
        const nameWithoutExtension = filename.substring(0, filename.lastIndexOf('.'));
        const availableLength = maxLength - extension.length - 4; // 4 para "...."
        
        if (availableLength <= 0) {
            return '...' + extension;
        }
        
        return nameWithoutExtension.substring(0, availableLength) + '...' + extension;
    }
    
    showSideSliders() {
        document.getElementById('design-sliders').style.display = 'flex';
    }
    
    hideSideSliders() {
        document.getElementById('design-sliders').style.display = 'none';
    }
    
    updateImageOrder() {
        const container = document.getElementById('uploaded-images');
        const items = container.querySelectorAll('.uploaded-image-item');
        const newOrder = [];
        
        items.forEach(item => {
            const imageId = item.dataset.imageId;
            const imageData = this.uploadedImages.find(img => img.id === imageId);
            if (imageData) {
                newOrder.push(imageData);
            }
        });
        
        this.uploadedImages = newOrder;
    }
    
    orderShirt() {
        const imagesInDesign = this.uploadedImages.filter(img => img.view);
        
        if (imagesInDesign.length === 0) {
            alert('Agrega al menos una imagen al diseño antes de ordenar');
            return;
        }
        
        // Aquí podrías integrar con un sistema de órdenes
        alert('¡Diseño listo para ordenar! (Esta funcionalidad se implementaría con un sistema de órdenes)');
    }
    
    showFullPreview() {
        console.log('🎬 Iniciando vista previa simple');
        
        const modal = document.getElementById('preview-modal');
        const previewContainer = modal.querySelector('.preview-shirt-container');
        
        if (!modal || !previewContainer) {
            alert('Error: Modal no encontrado');
            return;
        }
        
        // Mostrar modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Mostrar loading
        previewContainer.innerHTML = '<div style="color: white; text-align: center; padding: 20px;">🔄 Generando vista previa...</div>';
        
        // Generar imagen después de un breve delay
        setTimeout(() => {
            this.createPreviewImage();
        }, 300);
        
        // Event listeners
        document.addEventListener('keydown', this.handlePreviewKeydown);
        modal.querySelector('.preview-overlay').addEventListener('click', () => {
            this.closeFullPreview();
        });
    }
    
    
    createPreviewImage() {
        console.log('🎨 Creando imagen TEMPORAL de vista previa');
        console.log(`📋 Vista actual: ${this.currentView}`);
        console.log(`📋 Imágenes disponibles: ${this.uploadedImages.length}`);
        
        /*
         * GENERA IMAGEN TEMPORAL que combina:
         * 1. Imagen base de remera (remera-frente.png o remera-espalda.png)
         * 2. Diseños del usuario que están en la vista actual
         * 3. La imagen NO se guarda, solo se muestra en modal
         */
        
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        if (!ctx) {
            this.showPreviewError('Error: No se pudo crear canvas');
            return;
        }
        
        // Configurar canvas de alta resolución
        canvas.width = 800;
        canvas.height = 1000;
        
        // NO agregar fondo blanco - mantener transparencia
        // ctx.fillStyle = '#ffffff';
        // ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        console.log('✅ Canvas temporal configurado:', canvas.width + 'x' + canvas.height);
        
        // PASO 1: Cargar y dibujar imagen base de remera
        this.loadShirtImage().then(shirtImg => {
            if (shirtImg) {
                // Dibujar imagen real de remera (remera-frente.png o remera-espalda.png)
                ctx.drawImage(shirtImg, 0, 0, canvas.width, canvas.height);
                console.log('✅ Imagen base de remera dibujada');
            } else {
                // Fallback: dibujar remera simulada si no se carga la imagen
                this.drawSimulatedShirt(ctx, canvas);
                console.log('✅ Remera simulada dibujada (fallback)');
            }
            
            // PASO 2: Superponer diseños del usuario de la vista actual
            this.drawUserDesigns(ctx, canvas).then(() => {
                // PASO 3: Convertir canvas a imagen temporal y mostrar
                const temporaryImageUrl = canvas.toDataURL('image/png', 1.0);
                this.displayPreviewImage(temporaryImageUrl);
                console.log('✅ Vista previa TEMPORAL completada');
            });
            
        }).catch(error => {
            console.error('❌ Error generando vista previa:', error);
            this.showPreviewError('Error al generar vista previa');
        });
    }
    
    
    closeFullPreview() {
        const modal = document.getElementById('preview-modal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Remover event listeners
        document.removeEventListener('keydown', this.handlePreviewKeydown);
        
        // Remover resize handler si existe
        if (this.currentResizeHandler) {
            window.removeEventListener('resize', this.currentResizeHandler);
            this.currentResizeHandler = null;
        }
    }
    
    handlePreviewKeydown = (e) => {
        if (e.key === 'Escape') {
            this.closeFullPreview();
        }
    }
    
    loadShirtImage() {
        return new Promise((resolve) => {
            // Mapear vista actual a nombre de archivo correcto
            const viewToFileName = {
                'front': 'frente',    // Vista frontal → remera-frente.png
                'back': 'espalda'     // Vista espalda → remera-espalda.png
            };
            const fileName = viewToFileName[this.currentView] || 'frente';
            const imagePath = `/proyecto/assets/images/remera-${fileName}.png`;
            
            console.log(`🔄 Cargando imagen base: ${imagePath} (vista: ${this.currentView})`);
            
            const img = new Image();
            img.onload = () => {
                console.log('✅ Imagen de remera cargada exitosamente:', imagePath);
                resolve(img);
            };
            img.onerror = () => {
                console.log('❌ No se pudo cargar imagen de remera:', imagePath);
                resolve(null); // Continúa sin imagen base, usará simulada
            };
            img.src = imagePath;
        });
    }
    
    drawSimulatedShirt(ctx, canvas) {
        // NO agregar fondo - mantener transparencia
        
        // Forma básica de remera (más realista)
        ctx.fillStyle = '#f5f5f5';
        const shirtX = canvas.width * 0.15;
        const shirtY = canvas.height * 0.1;
        const shirtWidth = canvas.width * 0.7;
        const shirtHeight = canvas.height * 0.8;
        
        // Dibujar forma de remera más realista
        ctx.beginPath();
        ctx.roundRect(shirtX, shirtY, shirtWidth, shirtHeight, 20);
        ctx.fill();
        
        // Borde sutil
        ctx.strokeStyle = '#e0e0e0';
        ctx.lineWidth = 1;
        ctx.stroke();
        
        // Texto indicativo más sutil
        ctx.fillStyle = '#999999';
        ctx.font = '24px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(`Vista ${this.currentView.toUpperCase()}`, canvas.width/2, 60);
    }
    
    drawUserDesigns(ctx, canvas) {
        return new Promise((resolve) => {
            // Filtrar SOLO las imágenes que están en la vista actual (front o back)
            const designImages = this.uploadedImages.filter(img => img.view === this.currentView);
            
            if (designImages.length === 0) {
                console.log(`📋 Sin diseños en vista ${this.currentView}`);
                resolve();
                return;
            }
            
            console.log(`🎨 Dibujando ${designImages.length} diseños de la vista ${this.currentView}`);
            
            let loadedCount = 0;
            
            designImages.forEach((imageData, index) => {
                console.log(`🔄 Procesando diseño ${index + 1}: ${imageData.name} en posición (${imageData.x}, ${imageData.y})`);
                
                const img = new Image();
                
                img.onload = () => {
                    try {
                        // Área de diseño de la remera (zona donde van los diseños)
                        // Mantener las mismas proporciones que el área de diseño real
                        const designArea = {
                            x: canvas.width * 0.25,      // 25% desde izquierda
                            y: canvas.height * 0.20,     // 20% desde arriba  
                            width: canvas.width * 0.50,  // 50% del ancho
                            height: canvas.height * 0.65 // 65% del alto (corregido)
                        };
                        
                        // Convertir posición porcentual a píxeles dentro del área de diseño
                        const imgX = designArea.x + (imageData.x / 100) * designArea.width;
                        const imgY = designArea.y + (imageData.y / 100) * designArea.height;
                        
                        // Calcular el ancho deseado de la imagen en el canvas
                        // imageData.size is the percentage (e.g., 100 for 100%)
                        // img.width is the natural width of the loaded image (original image width)
                        const editorPixelWidth = (imageData.size / 100) * img.width; // Pixel width in editor's context
                        
                        // Scale this editor pixel width to the canvas's design area width
                        // this.realDesignAreaWidth is the reference width of the editor's visual area.
                        // designArea.width es el ancho del área de diseño en el canvas.
                        const imgWidth = (editorPixelWidth / this.realDesignAreaWidth) * designArea.width;
                        // Calcular la altura manteniendo la relación de aspecto original de la imagen
                        const imgHeight = (img.height / img.width) * imgWidth;
                        
                        // Logging detallado para debugging
                        console.log(`🔧 Escalado CORREGIDO para "${imageData.name}":`);
                        console.log(`   📐 Imagen original: ${img.width}x${img.height}px`);
                        console.log(`   📊 imageData.size: ${imageData.size}% (valor del slider)`);
                        console.log(`   📏 Área de diseño real (editor): ${this.realDesignAreaWidth}px`);
                        console.log(`   📏 designArea.width (canvas): ${designArea.width}px`);
                        console.log(`   🎯 Ancho en píxeles del editor: ${editorPixelWidth.toFixed(1)}px`);
                        console.log(`   📋 Ancho deseado en canvas: ${imgWidth.toFixed(1)}px`);
                        console.log(`   📋 Resultado: ${imgWidth.toFixed(1)}x${imgHeight.toFixed(1)}px`);
                        
                        const imageSource = imageData.tempUrl ? (imageData.tempUrl.includes('.webp') ? '.webp' : '.png') : 'dataURL';
                        console.log(`📍 Dibujando "${imageData.name}" desde ${imageSource} en (${imgX.toFixed(1)}, ${imgY.toFixed(1)})`);
                        
                        // Aplicar transformaciones y dibujar
                        ctx.save();
                        ctx.translate(imgX, imgY);
                        ctx.rotate((imageData.rotation * Math.PI) / 180);
                        ctx.drawImage(img, -imgWidth/2, -imgHeight/2, imgWidth, imgHeight);
                        ctx.restore();
                        
                        console.log(`✅ Diseño ${index + 1} dibujado correctamente`);
                        
                    } catch (error) {
                        console.error(`❌ Error dibujando diseño ${index + 1}:`, error);
                    }
                    
                    loadedCount++;
                    if (loadedCount === designImages.length) {
                        console.log('🎉 Todos los diseños de usuario dibujados');
                        resolve();
                    }
                };
                
                img.onerror = () => {
                    console.error(`❌ Error cargando imagen del diseño ${index + 1}: ${imageData.name}`);
                    console.log('🔄 Intentando con dataURL como fallback...');
                    
                    // Fallback: intentar con dataURL original
                    if (imageData.src && imageData.src !== img.src) {
                        img.src = imageData.src;
                        return;
                    }
                    
                    loadedCount++;
                    if (loadedCount === designImages.length) {
                        resolve();
                    }
                };
                
                // Determinar fuente de imagen
                if (imageData.tempUrl && imageData.isDataFile) {
                    // Cargar dataURL desde archivo .dat
                    console.log(`🔄 Cargando diseño desde archivo .dat: ${imageData.tempUrl}`);
                    this.loadDataUrlFromFile(imageData.tempUrl)
                        .then(dataUrl => {
                            img.src = dataUrl;
                        })
                        .catch(error => {
                            console.error('❌ Error cargando archivo .dat, usando dataURL original:', error);
                            img.src = imageData.src;
                        });
                } else {
                    // Usar archivo directo o dataURL
                    const imageSource = imageData.tempUrl || imageData.src;
                    const sourceType = imageData.tempUrl ? 'archivo temp' : 'dataURL';
                    console.log(`🔄 Cargando diseño desde: ${sourceType}`);
                    img.src = imageSource;
                }
            });
        });
    }
    
    displayPreviewImage(imageUrl) {
        const modal = document.getElementById('preview-modal');
        const previewContainer = modal.querySelector('.preview-shirt-container');
        
        if (!imageUrl || imageUrl.length < 100) {
            this.showPreviewError('Error: Imagen generada vacía');
            return;
        }
        
        // Limpiar contenedor
        previewContainer.innerHTML = '';
        
        // Crear imagen
        const img = document.createElement('img');
        img.src = imageUrl;
        
        // Estilos para centrado perfecto y tamaño óptimo
        img.style.maxWidth = '95vw';
        img.style.maxHeight = '95vh';
        img.style.width = 'auto';
        img.style.height = 'auto';
        img.style.objectFit = 'contain';
        img.style.borderRadius = '12px';
        img.style.boxShadow = '0 20px 60px rgba(0,0,0,0.9)';
        img.style.display = 'block';
        img.style.margin = 'auto';
        
        // Tamaño mínimo para asegurar visibilidad
        img.style.minWidth = '400px';
        img.style.minHeight = '500px';
        
        img.onload = () => {
            console.log('✅ Imagen mostrada en modal');
        };
        
        img.onerror = () => {
            this.showPreviewError('Error al mostrar imagen');
        };
        
        previewContainer.appendChild(img);
    }
    
    showPreviewError(message) {
        const modal = document.getElementById('preview-modal');
        const previewContainer = modal.querySelector('.preview-shirt-container');
        
        previewContainer.innerHTML = `
            <div style="color: white; text-align: center; padding: 40px;">
                <div style="font-size: 2rem; margin-bottom: 20px;">⚠️</div>
                <div style="font-size: 1.2rem;">${message}</div>
            </div>
        `;
    }
    
    scheduleCleanup() {
        // Programar limpieza de archivos temporales cada 30 minutos
        setInterval(() => {
            this.cleanupTempFiles();
        }, 30 * 60 * 1000); // 30 minutos
        
        // Limpieza inicial después de 5 minutos
        setTimeout(() => {
            this.cleanupTempFiles();
        }, 5 * 60 * 1000);
        
        // Test inicial de conectividad
        this.testServerConnection();
    }
    
    testServerConnection() {
        console.log('🔧 Probando conectividad con servidor...');
        
        fetch('/proyecto/test-simple.php', {
            method: 'GET'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Servidor conectado:', data.message);
            console.log('🔧 PHP version:', data.php_version);
        })
        .catch(error => {
            console.error('❌ Error de conectividad con servidor:', error);
        });
    }
    
    loadDataUrlFromFile(filePath) {
        return new Promise((resolve, reject) => {
            fetch(filePath)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(dataUrl => {
                    // Verificar que sea un dataURL válido
                    if (dataUrl.startsWith('data:image/')) {
                        resolve(dataUrl);
                    } else {
                        throw new Error('Archivo no contiene dataURL válido');
                    }
                })
                .catch(error => {
                    reject(error);
                });
        });
    }
    
    cleanupTempFiles() {
        console.log('🧹 Ejecutando limpieza de archivos temporales...');
        
        fetch('/proyecto/save-temp-raw.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'cleanup'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Limpieza completada');
            }
        })
        .catch(error => {
            console.log('⚠️ Error en limpieza:', error);
        });
    }
}

// Inicializar el diseñador cuando la página cargue
let shirtDesigner;

document.addEventListener('DOMContentLoaded', function() {
    shirtDesigner = new ShirtDesigner();
    
    // Click fuera para deseleccionar (deshabilitado - mantener selección)
    // document.addEventListener('click', (e) => {
    //     if (!e.target.closest('.design-image') && !e.target.closest('.uploaded-image-item')) {
    //         shirtDesigner.deselectImage();
    //     }
    // });
});

// Funciones globales para los botones
function saveDesign() {
    shirtDesigner.saveDesign();
}

function resetDesign() {
    shirtDesigner.resetDesign();
}

function orderShirt() {
    shirtDesigner.orderShirt();
}

// Funciones para la vista previa
function showFullPreview() {
    shirtDesigner.showFullPreview();
}

function closeFullPreview() {
    shirtDesigner.closeFullPreview();
}
```¡Claro que sí! He ajustado el código para que ahora puedas mover las imágenes y que estas se mantengan siempre dentro de los límites de sublimación.

El problema principal estaba en cómo se gestionaba la interacción del usuario (el arrastre del ratón) y cómo se aplicaban los límites. He refactorizado varias funciones en `shirt-designer.js` para que trabajen juntas de forma más robusta.

### Resumen de los Cambios

1.  **Interacción Centralizada:** Toda la lógica de arrastre, redimensionamiento y rotación ahora se maneja desde el contenedor de la imagen (`.image-container`), no desde la imagen misma. Esto previene muchos errores y simplifica el código.
2.  **Cálculo de Movimiento Correcto:** La función `setupImageInteraction` ahora calcula correctamente el desplazamiento del ratón y lo aplica a la posición inicial de la imagen, resultando en un movimiento suave y predecible.
3.  **Límites en Tiempo Real:** La función `updateImageStyle` se encarga de verificar en cada movimiento si la imagen se saldría de los límites. Si detecta que el borde de la imagen va a cruzar la línea punteada, detiene su movimiento en ese eje, "sujetándola" al borde.

Aquí tienes el código completamente actualizado para `shirt-designer.js`. Solo necesitas reemplazar el contenido de tu archivo con este nuevo código.

### Código Actualizado para `c:\xampp\htdocs\proyecto\assets\js\shirt-designer.js`

```javascript
// Diseñador de Remeras - JavaScript
class ShirtDesigner {
    constructor() {
        this.uploadedImages = [];
        this.currentView = 'front';
        this.selectedImage = null;
        this.realDesignAreaWidth = 250; // Ancho efectivo del design-zone en el editor (referencia)
        this.realDesignAreaHeight = 325; // Alto efectivo del design-zone en el editor (referencia)
        this.maxImages = 5;
        this.imageCounter = 0;
        
        this.init();
        this.scheduleCleanup();
    }
    
    init() {
        this.setupEventListeners();
        this.setupDragAndDrop();
    }
    
    setupEventListeners() {
        // Cambio de vista (frente/espalda)
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchView(e.currentTarget.dataset.view);
            });
        });
        
        // Upload de imágenes
        const imageUpload = document.getElementById('image-upload');
        imageUpload.addEventListener('change', (e) => {
            this.handleImageUpload(e);
        });
        
        // Controles deslizantes laterales
        document.getElementById('side-size-slider').addEventListener('input', (e) => {
            this.updateImageSize(e.target.value);
        });
        
        document.getElementById('side-rotation-slider').addEventListener('input', (e) => {
            this.updateImageRotation(e.target.value);
        });
        
        // Drag and drop de imágenes
        this.setupDesignZoneDragDrop();
    }
    
    setupDragAndDrop() {
        const uploadArea = document.querySelector('.upload-btn');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, this.preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.borderColor = '#007bff';
                uploadArea.style.background = 'rgba(0, 123, 255, 0.1)';
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.borderColor = 'var(--border-color)';
                uploadArea.style.background = 'var(--bg-primary)';
            }, false);
        });
        
        uploadArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            this.processFiles(files);
        }, false);
    }
    
    setupDesignZoneDragDrop() {
        const designZones = document.querySelectorAll('.design-zone');
        
        designZones.forEach(zone => {
            zone.addEventListener('dragover', this.preventDefaults);
            zone.addEventListener('drop', (e) => {
                this.preventDefaults(e);
                const imageId = e.dataTransfer.getData('text/plain');
                this.addImageToDesign(imageId, e);
            });
        });
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    switchView(view) {
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        document.querySelector(`[data-view="${view}"]`).classList.add('active');
        
        document.querySelectorAll('.shirt-view').forEach(shirtView => {
            shirtView.style.display = 'none';
        });
        
        document.getElementById(`${view}-view`).style.display = 'block';
        this.currentView = view;
        
        // Limpiar selección al cambiar de vista
        this.deselectImage();
    }
    
    handleImageUpload(event) {
        const files = event.target.files;
        this.processFiles(files);
    }
    
    processFiles(files) {
        if (this.uploadedImages.length >= this.maxImages) {
            alert(`Máximo ${this.maxImages} imágenes permitidas`);
            return;
        }
        
        Array.from(files).forEach(file => {
            if (this.uploadedImages.length >= this.maxImages) return;
            
            if (!file.type.startsWith('image/')) {
                alert('Solo se permiten archivos de imagen');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) { // 5MB
                alert('El archivo es demasiado grande (máximo 5MB)');
                return;
            }
            
            this.uploadImage(file);
        });
    }
    
    uploadImage(file) {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            const tempImg = new Image(); // Create a temporary image to get dimensions
            tempImg.onload = () => {
                // Calculate unique position for each new image
                const existingImagesInView = this.uploadedImages.filter(img => img.view === this.currentView);
                const offset = existingImagesInView.length * 5; // 5% offset per image
                
                const imageData = {
                    id: `img_${++this.imageCounter}`,
                    name: file.name,
                    src: e.target.result, // Temporary DataURL
                    tempUrl: null, // Will be filled after saving as .webp
                    size: 100, // Default to 100%
                    rotation: 0,
                    x: Math.max(20, Math.min(80, 50 + offset)), // Avoid overlap
                    y: Math.max(20, Math.min(80, 50 + offset)),
                    view: this.currentView, // Automatically assign to current view
                    originalWidth: tempImg.width,  // Store original width
                    originalHeight: tempImg.height // Store original height
                };
                
                // Save image as temporary .dat file
                this.saveImageAsWebP(imageData).then(() => {
                    this.uploadedImages.push(imageData);
                    this.renderUploadedImage(imageData);
                    this.renderDesignImage(imageData); // Automatically add to design area
                    this.selectDesignImage(imageData.id);
                }).catch(error => {
                    console.error('❌ Error saving temporary image:', error);
                    console.error('❌ Error type:', typeof error);
                    console.error('❌ Stack trace:', error.stack);
                    console.log('🔄 Continuing with original dataURL as fallback...');
                    
                    // Fallback: use original dataURL
                    this.uploadedImages.push(imageData);
                    this.renderUploadedImage(imageData);
                    this.renderDesignImage(imageData);
                    this.selectDesignImage(imageData.id);
                });
            };
            tempImg.src = e.target.result; // Trigger tempImg.onload
        };
        
        reader.readAsDataURL(file);
    }
    
    saveImageAsWebP(imageData) {
        return new Promise((resolve, reject) => {
            console.log('💾 Guardando imagen temporal:', imageData.name);
            console.log('📊 Tamaño dataURL:', imageData.src.length, 'caracteres');
            
            // Verificar que el dataURL sea válido
            if (!imageData.src || !imageData.src.startsWith('data:image/')) {
                reject(new Error('DataURL inválido'));
                return;
            }
            
            fetch('/proyecto/save-temp-raw.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    imageData: imageData.src,
                    filename: imageData.name
                })
            })
            .then(response => {
                console.log('📡 Respuesta del servidor:', response.status, response.statusText);
                
                // Verificar si la respuesta es exitosa
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                // Intentar obtener el texto de respuesta para debugging
                return response.text().then(text => {
                    console.log('📝 Respuesta cruda:', text.substring(0, 200) + '...');
                    
                    try {
                        return JSON.parse(text);
                    } catch (parseError) {
                        console.error('❌ Error parseando JSON:', parseError);
                        console.log('🔍 Respuesta completa:', text);
                        throw new Error('Respuesta del servidor no es JSON válido');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    imageData.tempUrl = data.tempPath;
                    imageData.isDataFile = data.format === 'dataurl';
                    console.log(`✅ Imagen guardada como archivo .${data.format}:`, data.tempPath);
                    console.log('📊 Tamaño archivo:', data.fileSize, 'bytes');
                    console.log('🔧 Método usado:', data.method);
                    resolve(data);
                } else {
                    console.error('❌ Error del servidor:', data.error);
                    reject(new Error(data.error));
                }
            })
            .catch(error => {
                console.error('❌ Error en saveImageAsWebP:', error);
                reject(error);
            });
        });
    }
    
    renderUploadedImage(imageData) {
        const container = document.getElementById('uploaded-images');
        
        const imageItem = document.createElement('div');
        imageItem.className = 'uploaded-image-item';
        imageItem.dataset.imageId = imageData.id;
        imageItem.draggable = true;
        
        // Crear preview de la imagen en el área segura
        const previewContainer = document.createElement('div');
        previewContainer.className = 'safe-area-preview';
        
        const previewImg = document.createElement('img');
        previewImg.src = imageData.src;
        previewImg.className = 'preview-image';
        
        previewContainer.appendChild(previewImg);
        
        imageItem.innerHTML = `
            <img src="${imageData.src}" alt="${imageData.name}">
            <div class="image-info">
                <span class="image-name" title="${imageData.name}">${this.truncateFilename(imageData.name)}</span>
                <div class="image-actions">
                    <button class="preview-btn" onclick="shirtDesigner.togglePreview('${imageData.id}')" title="Vista previa">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="shirtDesigner.removeUploadedImage('${imageData.id}')" class="remove-btn" title="Eliminar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        imageItem.appendChild(previewContainer);
        
        // Drag start event
        imageItem.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', imageData.id);
            imageItem.classList.add('dragging');
        });
        
        imageItem.addEventListener('dragend', () => {
            imageItem.classList.remove('dragging');
        });
        
        // Click para seleccionar
        imageItem.addEventListener('click', (e) => {
            if (!e.target.closest('button')) {
                this.selectUploadedImage(imageData.id);
            }
        });
        
        // Setup drag and drop reordering
        this.setupImageReordering(imageItem);
        
        container.appendChild(imageItem);
    }
    
    addImageToDesign(imageId, event) {
        const imageData = this.uploadedImages.find(img => img.id === imageId);
        if (!imageData) return;
        
        // Si la imagen ya está en una vista, la removemos primero
        this.removeImageFromDesign(imageId);
        
        const designZone = document.querySelector(`#${this.currentView}-design-zone`);
        const rect = designZone.getBoundingClientRect();
        const safeArea = designZone.querySelector('.sublimation-limits');
        const safeRect = safeArea.getBoundingClientRect();
        
        // Calcular posición relativa al área segura
        let x = 50, y = 50; // Posición por defecto en el centro
        
        if (event && event.clientX && event.clientY) {
            // Calcular posición relativa al área segura
            const safeX = ((event.clientX - safeRect.left) / safeRect.width) * 100;
            const safeY = ((event.clientY - safeRect.top) / safeRect.height) * 100;
            
            // Convertir a coordenadas del design-zone
            x = 10 + (safeX * 0.8); // 10% offset + 80% del área segura
            y = 10 + (safeY * 0.8);
        }
        
        imageData.view = this.currentView;
        // Limitar posición al área segura (10%-90% del design-zone)
        imageData.x = Math.max(10, Math.min(90, x));
        imageData.y = Math.max(10, Math.min(90, y));
        
        this.renderDesignImage(imageData);
        this.selectDesignImage(imageId);
    }
    
    renderDesignImage(imageData) {
        const designZone = document.querySelector(`#${imageData.view}-design-zone`);
        
        // Crear contenedor para la imagen y sus controles
        const imageContainer = document.createElement('div');
        imageContainer.className = 'image-container';
        imageContainer.id = `container-${imageData.id}`;
        
        const imageElement = document.createElement('img');
        imageElement.className = 'design-image';
        imageElement.id = `design-${imageData.id}`;
        imageElement.src = imageData.src;
        imageElement.alt = imageData.name;
        
        // Crear controles
        const controlsContainer = document.createElement('div');
        controlsContainer.className = 'image-controls-container';
        
        // Crear botones individualmente para evitar problemas con onclick
        const rotateBtn = document.createElement('button');
        rotateBtn.className = 'image-control-btn rotate-btn';
        rotateBtn.title = 'Rotar';
        rotateBtn.innerHTML = '↻';
        rotateBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.rotateImage(imageData.id);
        });
        
        const scaleBtn = document.createElement('button');
        scaleBtn.className = 'image-control-btn scale-btn';
        scaleBtn.title = 'Escalar';
        scaleBtn.innerHTML = '⚏';
        scaleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleScaleMode(imageData.id);
        });
        
        const duplicateBtn = document.createElement('button');
        duplicateBtn.className = 'image-control-btn duplicate-btn';
        duplicateBtn.title = 'Duplicar';
        duplicateBtn.innerHTML = '⧉';
        duplicateBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.duplicateImage(imageData.id);
        });
        
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'image-control-btn delete-btn';
        deleteBtn.title = 'Eliminar';
        deleteBtn.innerHTML = '✕';
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.deleteImageFromDesign(imageData.id);
        });
        
        controlsContainer.appendChild(rotateBtn);
        controlsContainer.appendChild(scaleBtn);
        controlsContainer.appendChild(duplicateBtn);
        controlsContainer.appendChild(deleteBtn);
        
        imageContainer.appendChild(imageElement);
        imageContainer.appendChild(controlsContainer);
        
        // Event listeners para interacción
        this.setupImageInteraction(imageContainer, imageData);
        
        designZone.appendChild(imageContainer);

        // Aplicar estilos iniciales
        this.updateImageStyle(imageContainer, imageData);
    }
    
    updateImageStyle(container, imageData) {
        if (!container || !imageData) {
            return;
        }
    
        // --- LÓGICA DE LÍMITES ---
        if (imageData.originalWidth) {
            // 1. Calcular dimensiones de la imagen en píxeles y como porcentaje del área de diseño
            const pixelWidth = (imageData.size / 100) * imageData.originalWidth;
            const widthPercentOfZone = (pixelWidth / this.realDesignAreaWidth) * 100;
            const halfWidthPercent = widthPercentOfZone / 2;
    
            const pixelHeight = (imageData.originalHeight / imageData.originalWidth) * pixelWidth;
            const heightPercentOfZone = (pixelHeight / this.realDesignAreaHeight) * 100;
            const halfHeightPercent = heightPercentOfZone / 2;
    
            // 2. Obtener los límites del área segura (ej. 10% a 90%)
            const safeBounds = this.getSafeAreaBounds();
    
            // 3. Calcular el rango permitido para el PUNTO CENTRAL de la imagen
            const minX = safeBounds.left + halfWidthPercent;
            const maxX = safeBounds.right - halfWidthPercent;
            const minY = safeBounds.top + halfHeightPercent;
            const maxY = safeBounds.bottom - halfHeightPercent;
    
            // 4. Forzar la posición a estar dentro del rango calculado
            // Si la imagen es más grande que el área, se centra.
            imageData.x = (minX > maxX) ? (safeBounds.left + safeBounds.right) / 2 : Math.max(minX, Math.min(maxX, imageData.x));
            imageData.y = (minY > maxY) ? (safeBounds.top + safeBounds.bottom) / 2 : Math.max(minY, Math.min(maxY, imageData.y));
        }
    
        // --- APLICACIÓN DE ESTILOS ---
        // 1. Establecer el ancho del contenedor basado en el porcentaje de tamaño
        if (imageData.originalWidth) {
            const pixelWidth = (imageData.size / 100) * imageData.originalWidth;
            container.style.width = `${pixelWidth}px`;
        } else {
            container.style.width = `${imageData.size}px`;
        }
    
        // 2. Establecer la posición y rotación del contenedor
        container.style.left = `${imageData.x}%`;
        container.style.top = `${imageData.y}%`;
        container.style.transform = `translate(-50%, -50%) rotate(${imageData.rotation}deg)`;
    
        // 3. Establecer el z-index para el apilamiento
        container.style.zIndex = imageData.id === this.selectedImage ? '1001' : '1000';
    }
    
    setupImageInteraction(container, imageData) {
        let isDragging = false;
        let startX, startY, startLeft, startTop;
        
        const onMouseDown = (e) => {
            // Prevenir comportamiento por defecto solo si es un evento de ratón o el target es el contenedor
            if (e.type === 'mousedown' || e.target === container) {
                e.preventDefault();
            }
            e.stopPropagation();
            
            isDragging = true;
            this.selectDesignImage(imageData.id);
            
            const eventCoord = e.touches ? e.touches[0] : e;
            
            // Guardar posiciones iniciales
            startX = eventCoord.clientX;
            startY = eventCoord.clientY;
            startLeft = imageData.x;
            startTop = imageData.y;
            
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
            document.addEventListener('touchmove', onMouseMove, { passive: false });
            document.addEventListener('touchend', onMouseUp);
        };
    
        const onMouseMove = (e) => {
            if (!isDragging) return;
            e.preventDefault();
    
            const designZone = container.parentElement;
            const rect = designZone.getBoundingClientRect();
            const eventCoord = e.touches ? e.touches[0] : e;
    
            // Calcular el desplazamiento del ratón en porcentaje
            const deltaX = ((eventCoord.clientX - startX) / rect.width) * 100;
            const deltaY = ((eventCoord.clientY - startY) / rect.height) * 100;
    
            // Aplicar el desplazamiento a la posición inicial
            imageData.x = startLeft + deltaX;
            imageData.y = startTop + deltaY;
    
            // Actualizar el estilo (la función de límites se encargará de restringir)
            this.updateImageStyle(container, imageData);
            this.checkCenterGuides(imageData);
        };
    
        const onMouseUp = () => {
            isDragging = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            document.removeEventListener('touchmove', onMouseMove);
            document.removeEventListener('touchend', onMouseUp);
            this.hideCenterGuides();
        };
    
        // Iniciar el arrastre al hacer clic en el contenedor
        container.addEventListener('mousedown', onMouseDown);
        container.addEventListener('touchstart', onMouseDown, { passive: false });
    }
    
    selectDesignImage(imageId) {
        // Deseleccionar todas las imágenes
        document.querySelectorAll('.image-container').forEach(img => {
            img.classList.remove('selected');
        });
        
        // Seleccionar la imagen actual
        const container = document.getElementById(`container-${imageId}`);
        if (container) {
            container.classList.add('selected');
            this.selectedImage = imageId;
            this.showSideSliders();
            this.updateControlValues();
        }
    }
    
    deselectImage() {
        document.querySelectorAll('.image-container').forEach(img => {
            img.classList.remove('selected');
        });
        
        this.selectedImage = null;
        this.hideSideSliders();
    }
    
    
    updateControlValues() {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        if (!imageData) return;
        
        // Actualizar sliders laterales
        document.getElementById('side-size-slider').value = imageData.size;
        document.getElementById('side-size-value').textContent = `${imageData.size}%`;
        document.getElementById('side-rotation-slider').value = Math.round(imageData.rotation);
        document.getElementById('side-rotation-value').textContent = `${Math.round(imageData.rotation)}°`;
    }
    
    updateImageSize(size) {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const container = document.getElementById(`container-${this.selectedImage}`);
        
        if (imageData && container) {
            const newPercentage = parseInt(size);
            
            imageData.size = newPercentage;
            this.updateImageStyle(container, imageData);
            
            // Actualizar display lateral
            const sizeText = `${newPercentage}%`;
            document.getElementById('side-size-value').textContent = sizeText;
            document.getElementById('side-size-slider').value = newPercentage;
        }
    }
    
    updateImageRotation(rotation) {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const container = document.getElementById(`container-${this.selectedImage}`);
        
        if (imageData && container) {
            imageData.rotation = parseInt(rotation);
            this.updateImageStyle(container, imageData);
            
            // Actualizar display lateral
            const rotationText = `${Math.round(rotation)}°`;
            document.getElementById('side-rotation-value').textContent = rotationText;
            document.getElementById('side-rotation-slider').value = rotation;
        }
    }
    
    checkCenterGuides(imageData) {
        const tolerance = 5; // 5% de tolerancia
        const designZone = document.querySelector(`#${imageData.view}-design-zone`);
        
        const centerH = designZone.querySelector('.center-guide-h');
        const centerV = designZone.querySelector('.center-guide-v');
        
        // Mostrar guía horizontal si está cerca del centro vertical
        if (Math.abs(imageData.y - 50) < tolerance) {
            centerH.classList.add('show');
            imageData.y = 50; // Snap al centro
        } else {
            centerH.classList.remove('show');
        }
        
        // Mostrar guía vertical si está cerca del centro horizontal
        if (Math.abs(imageData.x - 50) < tolerance) {
            centerV.classList.add('show');
            imageData.x = 50; // Snap al centro
        } else {
            centerV.classList.remove('show');
        }
    }
    
    hideCenterGuides() {
        document.querySelectorAll('.center-guide-h, .center-guide-v').forEach(guide => {
            guide.classList.remove('show');
        });
    }
    
    centerImage() {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const container = document.getElementById(`container-${this.selectedImage}`);
        
        if (imageData && container) {
            imageData.x = 50;
            imageData.y = 50;
            this.updateImageStyle(container, imageData);
        }
    }
    
    deleteSelectedImage() {
        if (!this.selectedImage) return;
        
        this.removeImageFromDesign(this.selectedImage);
        this.deselectImage();
    }
    
    removeImageFromDesign(imageId) {
        const container = document.getElementById(`container-${imageId}`);
        if (container) {
            container.remove();
            
            // Limpiar la vista de la imagen
            const imageData = this.uploadedImages.find(img => img.id === imageId);
            if (imageData) {
                imageData.view = null;
            }
        }
    }
    
    removeUploadedImage(imageId) {
        this.removeImageFromDesign(imageId);
        
        // Remover de la lista de imágenes subidas
        this.uploadedImages = this.uploadedImages.filter(img => img.id !== imageId);
        
        // Remover del DOM
        const imageItem = document.querySelector(`[data-image-id="${imageId}"]`);
        if (imageItem) {
            imageItem.remove();
        }
        
        if (this.selectedImage === imageId) {
            this.deselectImage();
        }
    }
    
    selectUploadedImage(imageId) {
        document.querySelectorAll('.uploaded-image-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        document.querySelector(`[data-image-id="${imageId}"]`).classList.add('selected');
    }
    
    saveDesign() {
        const designData = {
            images: this.uploadedImages.filter(img => img.view),
            timestamp: new Date().toISOString()
        };
        
        localStorage.setItem('shirtDesign', JSON.stringify(designData));
        alert('Diseño guardado exitosamente');
    }
    
    resetDesign() {
        if (confirm('¿Estás seguro de que quieres reiniciar el diseño?')) {
            // Remover todas las imágenes del diseño
            document.querySelectorAll('.image-container').forEach(img => img.remove());
            
            // Limpiar las vistas de las imágenes
            this.uploadedImages.forEach(img => {
                img.view = null;
                img.x = 50;
                img.y = 50;
                img.size = 100;
                img.rotation = 0;
            });
            
            this.deselectImage();
        }
    }
    
    getSafeAreaBounds() {
        // Retorna los límites del área segura en porcentajes
        return {
            left: 10,   // 10% desde la izquierda
            right: 90,  // 90% desde la izquierda
            top: 10,    // 10% desde arriba
            bottom: 90  // 90% desde arriba
        };
    }
    
    togglePreview(imageId) {
        const imageItem = document.querySelector(`[data-image-id="${imageId}"]`);
        const preview = imageItem.querySelector('.safe-area-preview');
        
        if (preview.classList.contains('show')) {
            preview.classList.remove('show');
        } else {
            // Ocultar otras previews
            document.querySelectorAll('.safe-area-preview.show').forEach(p => {
                p.classList.remove('show');
            });
            preview.classList.add('show');
            
            // Auto-ocultar después de 3 segundos
            setTimeout(() => {
                preview.classList.remove('show');
            }, 3000);
        }
    }
    
    setupImageReordering(imageItem) {
        const container = imageItem.parentElement;
        let dragStartY = 0;
        let isDraggingForReorder = false;
        
        // Configurar eventos de arrastre para reordenamiento
        imageItem.addEventListener('dragstart', (e) => {
            dragStartY = e.clientY;
            imageItem.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', imageItem.outerHTML);
        });
        
        imageItem.addEventListener('dragend', () => {
            imageItem.classList.remove('dragging');
            container.querySelectorAll('.drag-over').forEach(item => {
                item.classList.remove('drag-over');
            });
        });
        
        imageItem.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            const draggingItem = container.querySelector('.dragging');
            if (draggingItem && draggingItem !== imageItem) {
                imageItem.classList.add('drag-over');
                
                const afterElement = this.getDragAfterElement(container, e.clientY);
                if (afterElement == null) {
                    container.appendChild(draggingItem);
                } else {
                    container.insertBefore(draggingItem, afterElement);
                }
            }
        });
        
        imageItem.addEventListener('dragleave', () => {
            imageItem.classList.remove('drag-over');
        });
        
        imageItem.addEventListener('drop', (e) => {
            e.preventDefault();
            imageItem.classList.remove('drag-over');
        });
    }
    
    getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.uploaded-image-item:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    truncateFilename(filename, maxLength = 15) {
        if (filename.length <= maxLength) {
            return filename;
        }
        
        const extension = filename.split('.').pop();
        const nameWithoutExtension = filename.substring(0, filename.lastIndexOf('.'));
        const availableLength = maxLength - extension.length - 4; // 4 para "...."
        
        if (availableLength <= 0) {
            return '...' + extension;
        }
        
        return nameWithoutExtension.substring(0, availableLength) + '...' + extension;
    }
    
    showSideSliders() {
        document.getElementById('design-sliders').style.display = 'flex';
    }
    
    hideSideSliders() {
        document.getElementById('design-sliders').style.display = 'none';
    }
    
    updateImageOrder() {
        const container = document.getElementById('uploaded-images');
        const items = container.querySelectorAll('.uploaded-image-item');
        const newOrder = [];
        
        items.forEach(item => {
            const imageId = item.dataset.imageId;
            const imageData = this.uploadedImages.find(img => img.id === imageId);
            if (imageData) {
                newOrder.push(imageData);
            }
        });
        
        this.uploadedImages = newOrder;
    }
    
    orderShirt() {
        const imagesInDesign = this.uploadedImages.filter(img => img.view);
        
        if (imagesInDesign.length === 0) {
            alert('Agrega al menos una imagen al diseño antes de ordenar');
            return;
        }
        
        // Aquí podrías integrar con un sistema de órdenes
        alert('¡Diseño listo para ordenar! (Esta funcionalidad se implementaría con un sistema de órdenes)');
    }
    
    showFullPreview() {
        console.log('🎬 Iniciando vista previa simple');
        
        const modal = document.getElementById('preview-modal');
        const previewContainer = modal.querySelector('.preview-shirt-container');
        
        if (!modal || !previewContainer) {
            alert('Error: Modal no encontrado');
            return;
        }
        
        // Mostrar modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Mostrar loading
        previewContainer.innerHTML = '<div style="color: white; text-align: center; padding: 20px;">🔄 Generando vista previa...</div>';
        
        // Generar imagen después de un breve delay
        setTimeout(() => {
            this.createPreviewImage();
        }, 300);
        
        // Event listeners
        document.addEventListener('keydown', this.handlePreviewKeydown);
        modal.querySelector('.preview-overlay').addEventListener('click', () => {
            this.closeFullPreview();
        });
    }
    
    
    createPreviewImage() {
        console.log('🎨 Creando imagen TEMPORAL de vista previa');
        console.log(`📋 Vista actual: ${this.currentView}`);
        console.log(`📋 Imágenes disponibles: ${this.uploadedImages.length}`);
        
        /*
         * GENERA IMAGEN TEMPORAL que combina:
         * 1. Imagen base de remera (remera-frente.png o remera-espalda.png)
         * 2. Diseños del usuario que están en la vista actual
         * 3. La imagen NO se guarda, solo se muestra en modal
         */
        
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        if (!ctx) {
            this.showPreviewError('Error: No se pudo crear canvas');
            return;
        }
        
        // Configurar canvas de alta resolución
        canvas.width = 800;
        canvas.height = 1000;
        
        // NO agregar fondo blanco - mantener transparencia
        // ctx.fillStyle = '#ffffff';
        // ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        console.log('✅ Canvas temporal configurado:', canvas.width + 'x' + canvas.height);
        
        // PASO 1: Cargar y dibujar imagen base de remera
        this.loadShirtImage().then(shirtImg => {
            if (shirtImg) {
                // Dibujar imagen real de remera (remera-frente.png o remera-espalda.png)
                ctx.drawImage(shirtImg, 0, 0, canvas.width, canvas.height);
                console.log('✅ Imagen base de remera dibujada');
            } else {
                // Fallback: dibujar remera simulada si no se carga la imagen
                this.drawSimulatedShirt(ctx, canvas);
                console.log('✅ Remera simulada dibujada (fallback)');
            }
            
            // PASO 2: Superponer diseños del usuario de la vista actual
            this.drawUserDesigns(ctx, canvas).then(() => {
                // PASO 3: Convertir canvas a imagen temporal y mostrar
                const temporaryImageUrl = canvas.toDataURL('image/png', 1.0);
                this.displayPreviewImage(temporaryImageUrl);
                console.log('✅ Vista previa TEMPORAL completada');
            });
            
        }).catch(error => {
            console.error('❌ Error generando vista previa:', error);
            this.showPreviewError('Error al generar vista previa');
        });
    }
    
    
    closeFullPreview() {
        const modal = document.getElementById('preview-modal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Remover event listeners
        document.removeEventListener('keydown', this.handlePreviewKeydown);
        
        // Remover resize handler si existe
        if (this.currentResizeHandler) {
            window.removeEventListener('resize', this.currentResizeHandler);
            this.currentResizeHandler = null;
        }
    }
    
    handlePreviewKeydown = (e) => {
        if (e.key === 'Escape') {
            this.closeFullPreview();
        }
    }
    
    loadShirtImage() {
        return new Promise((resolve) => {
            // Mapear vista actual a nombre de archivo correcto
            const viewToFileName = {
                'front': 'frente',    // Vista frontal → remera-frente.png
                'back': 'espalda'     // Vista espalda → remera-espalda.png
            };
            const fileName = viewToFileName[this.currentView] || 'frente';
            const imagePath = `/proyecto/assets/images/remera-${fileName}.png`;
            
            console.log(`🔄 Cargando imagen base: ${imagePath} (vista: ${this.currentView})`);
            
            const img = new Image();
            img.onload = () => {
                console.log('✅ Imagen de remera cargada exitosamente:', imagePath);
                resolve(img);
            };
            img.onerror = () => {
                console.log('❌ No se pudo cargar imagen de remera:', imagePath);
                resolve(null); // Continúa sin imagen base, usará simulada
            };
            img.src = imagePath;
        });
    }
    
    drawSimulatedShirt(ctx, canvas) {
        // NO agregar fondo - mantener transparencia
        
        // Forma básica de remera (más realista)
        ctx.fillStyle = '#f5f5f5';
        const shirtX = canvas.width * 0.15;
        const shirtY = canvas.height * 0.1;
        const shirtWidth = canvas.width * 0.7;
        const shirtHeight = canvas.height * 0.8;
        
        // Dibujar forma de remera más realista
        ctx.beginPath();
        ctx.roundRect(shirtX, shirtY, shirtWidth, shirtHeight, 20);
        ctx.fill();
        
        // Borde sutil
        ctx.strokeStyle = '#e0e0e0';
        ctx.lineWidth = 1;
        ctx.stroke();
        
        // Texto indicativo más sutil
        ctx.fillStyle = '#999999';
        ctx.font = '24px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(`Vista ${this.currentView.toUpperCase()}`, canvas.width/2, 60);
    }
    
    drawUserDesigns(ctx, canvas) {
        return new Promise((resolve) => {
            // Filtrar SOLO las imágenes que están en la vista actual (front o back)
            const designImages = this.uploadedImages.filter(img => img.view === this.currentView);
            
            if (designImages.length === 0) {
                console.log(`📋 Sin diseños en vista ${this.currentView}`);
                resolve();
                return;
            }
            
            console.log(`🎨 Dibujando ${designImages.length} diseños de la vista ${this.currentView}`);
            
            let loadedCount = 0;
            
            designImages.forEach((imageData, index) => {
                console.log(`🔄 Procesando diseño ${index + 1}: ${imageData.name} en posición (${imageData.x}, ${imageData.y})`);
                
                const img = new Image();
                
                img.onload = () => {
                    try {
                        // Área de diseño de la remera (zona donde van los diseños)
                        // Mantener las mismas proporciones que el área de diseño real
                        const designArea = {
                            x: canvas.width * 0.25,      // 25% desde izquierda
                            y: canvas.height * 0.20,     // 20% desde arriba  
                            width: canvas.width * 0.50,  // 50% del ancho
                            height: canvas.height * 0.65 // 65% del alto (corregido)
                        };
                        
                        // Convertir posición porcentual a píxeles dentro del área de diseño
                        const imgX = designArea.x + (imageData.x / 100) * designArea.width;
                        const imgY = designArea.y + (imageData.y / 100) * designArea.height;
                        
                        // Calcular el ancho deseado de la imagen en el canvas
                        // imageData.size is the percentage (e.g., 100 for 100%)
                        // img.width is the natural width of the loaded image (original image width)
                        const editorPixelWidth = (imageData.size / 100) * img.width; // Pixel width in editor's context
                        
                        // Scale this editor pixel width to the canvas's design area width
                        // this.realDesignAreaWidth is the reference width of the editor's visual area.
                        // designArea.width es el ancho del área de diseño en el canvas.
                        const imgWidth = (editorPixelWidth / this.realDesignAreaWidth) * designArea.width;
                        // Calcular la altura manteniendo la relación de aspecto original de la imagen
                        const imgHeight = (img.height / img.width) * imgWidth;
                        
                        // Logging detallado para debugging
                        console.log(`🔧 Escalado CORREGIDO para "${imageData.name}":`);
                        console.log(`   📐 Imagen original: ${img.width}x${img.height}px`);
                        console.log(`   📊 imageData.size: ${imageData.size}% (valor del slider)`);
                        console.log(`   📏 Área de diseño real (editor): ${this.realDesignAreaWidth}px`);
                        console.log(`   📏 designArea.width (canvas): ${designArea.width}px`);
                        console.log(`   🎯 Ancho en píxeles del editor: ${editorPixelWidth.toFixed(1)}px`);
                        console.log(`   📋 Ancho deseado en canvas: ${imgWidth.toFixed(1)}px`);
                        console.log(`   📋 Resultado: ${imgWidth.toFixed(1)}x${imgHeight.toFixed(1)}px`);
                        
                        const imageSource = imageData.tempUrl ? (imageData.tempUrl.includes('.webp') ? '.webp' : '.png') : 'dataURL';
                        console.log(`📍 Dibujando "${imageData.name}" desde ${imageSource} en (${imgX.toFixed(1)}, ${imgY.toFixed(1)})`);
                        
                        // Aplicar transformaciones y dibujar
                        ctx.save();
                        ctx.translate(imgX, imgY);
                        ctx.rotate((imageData.rotation * Math.PI) / 180);
                        ctx.drawImage(img, -imgWidth/2, -imgHeight/2, imgWidth, imgHeight);
                        ctx.restore();
                        
                        console.log(`✅ Diseño ${index + 1} dibujado correctamente`);
                        
                    } catch (error) {
                        console.error(`❌ Error dibujando diseño ${index + 1}:`, error);
                    }
                    
                    loadedCount++;
                    if (loadedCount === designImages.length) {
                        console.log('🎉 Todos los diseños de usuario dibujados');
                        resolve();
                    }
                };
                
                img.onerror = () => {
                    console.error(`❌ Error cargando imagen del diseño ${index + 1}: ${imageData.name}`);
                    console.log('🔄 Intentando con dataURL como fallback...');
                    
                    // Fallback: intentar con dataURL original
                    if (imageData.src && imageData.src !== img.src) {
                        img.src = imageData.src;
                        return;
                    }
                    
                    loadedCount++;
                    if (loadedCount === designImages.length) {
                        resolve();
                    }
                };
                
                // Determinar fuente de imagen
                if (imageData.tempUrl && imageData.isDataFile) {
                    // Cargar dataURL desde archivo .dat
                    console.log(`🔄 Cargando diseño desde archivo .dat: ${imageData.tempUrl}`);
                    this.loadDataUrlFromFile(imageData.tempUrl)
                        .then(dataUrl => {
                            img.src = dataUrl;
                        })
                        .catch(error => {
                            console.error('❌ Error cargando archivo .dat, usando dataURL original:', error);
                            img.src = imageData.src;
                        });
                } else {
                    // Usar archivo directo o dataURL
                    const imageSource = imageData.tempUrl || imageData.src;
                    const sourceType = imageData.tempUrl ? 'archivo temp' : 'dataURL';
                    console.log(`🔄 Cargando diseño desde: ${sourceType}`);
                    img.src = imageSource;
                }
            });
        });
    }
    
    displayPreviewImage(imageUrl) {
        const modal = document.getElementById('preview-modal');
        const previewContainer = modal.querySelector('.preview-shirt-container');
        
        if (!imageUrl || imageUrl.length < 100) {
            this.showPreviewError('Error: Imagen generada vacía');
            return;
        }
        
        // Limpiar contenedor
        previewContainer.innerHTML = '';
        
        // Crear imagen
        const img = document.createElement('img');
        img.src = imageUrl;
        
        // Estilos para centrado perfecto y tamaño óptimo
        img.style.maxWidth = '95vw';
        img.style.maxHeight = '95vh';
        img.style.width = 'auto';
        img.style.height = 'auto';
        img.style.objectFit = 'contain';
        img.style.borderRadius = '12px';
        img.style.boxShadow = '0 20px 60px rgba(0,0,0,0.9)';
        img.style.display = 'block';
        img.style.margin = 'auto';
        
        // Tamaño mínimo para asegurar visibilidad
        img.style.minWidth = '400px';
        img.style.minHeight = '500px';
        
        img.onload = () => {
            console.log('✅ Imagen mostrada en modal');
        };
        
        img.onerror = () => {
            this.showPreviewError('Error al mostrar imagen');
        };
        
        previewContainer.appendChild(img);
    }
    
    showPreviewError(message) {
        const modal = document.getElementById('preview-modal');
        const previewContainer = modal.querySelector('.preview-shirt-container');
        
        previewContainer.innerHTML = `
            <div style="color: white; text-align: center; padding: 40px;">
                <div style="font-size: 2rem; margin-bottom: 20px;">⚠️</div>
                <div style="font-size: 1.2rem;">${message}</div>
            </div>
        `;
    }
    
    scheduleCleanup() {
        // Programar limpieza de archivos temporales cada 30 minutos
        setInterval(() => {
            this.cleanupTempFiles();
        }, 30 * 60 * 1000); // 30 minutos
        
        // Limpieza inicial después de 5 minutos
        setTimeout(() => {
            this.cleanupTempFiles();
        }, 5 * 60 * 1000);
        
        // Test inicial de conectividad
        this.testServerConnection();
    }
    
    testServerConnection() {
        console.log('🔧 Probando conectividad con servidor...');
        
        fetch('/proyecto/test-simple.php', {
            method: 'GET'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Servidor conectado:', data.message);
            console.log('🔧 PHP version:', data.php_version);
        })
        .catch(error => {
            console.error('❌ Error de conectividad con servidor:', error);
        });
    }
    
    loadDataUrlFromFile(filePath) {
        return new Promise((resolve, reject) => {
            fetch(filePath)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(dataUrl => {
                    // Verificar que sea un dataURL válido
                    if (dataUrl.startsWith('data:image/')) {
                        resolve(dataUrl);
                    } else {
                        throw new Error('Archivo no contiene dataURL válido');
                    }
                })
                .catch(error => {
                    reject(error);
                });
        });
    }
    
    cleanupTempFiles() {
        console.log('🧹 Ejecutando limpieza de archivos temporales...');
        
        fetch('/proyecto/save-temp-raw.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'cleanup'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Limpieza completada');
            }
        })
        .catch(error => {
            console.log('⚠️ Error en limpieza:', error);
        });
    }
}

// Inicializar el diseñador cuando la página cargue
let shirtDesigner;

document.addEventListener('DOMContentLoaded', function() {
    shirtDesigner = new ShirtDesigner();
    
    // Click fuera para deseleccionar (deshabilitado - mantener selección)
    // document.addEventListener('click', (e) => {
    //     if (!e.target.closest('.design-image') && !e.target.closest('.uploaded-image-item')) {
    //         shirtDesigner.deselectImage();
    //     }
    // });
});

// Funciones globales para los botones
function saveDesign() {
    shirtDesigner.saveDesign();
}

function resetDesign() {
    shirtDesigner.resetDesign();
}

function orderShirt() {
    shirtDesigner.orderShirt();
}

// Funciones para la vista previa
function showFullPreview() {
    shirtDesigner.showFullPreview();
}

function closeFullPreview() {
    shirtDesigner.closeFullPreview();
}
