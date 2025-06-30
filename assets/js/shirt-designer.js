// Diseñador de Remeras - JavaScript
class ShirtDesigner {
    constructor() {
        this.uploadedImages = [];
        this.currentView = 'front';
        this.selectedImage = null;
        this.maxImages = 5;
        this.imageCounter = 0;
        this.currentShirtSize = 'M';
        
        // Dimensiones de talles en cm (ancho x alto)
        this.shirtSizes = {
            'S': { width: 48, height: 68 },
            'M': { width: 51, height: 70 },
            'L': { width: 54, height: 72 },
            'XL': { width: 57, height: 74 },
            '2XL': { width: 60, height: 76 },
            '3XL': { width: 63, height: 78 },
            '4XL': { width: 66, height: 80 },
            '5XL': { width: 69, height: 82 },
            '6XL': { width: 72, height: 84 },
            '7XL': { width: 75, height: 86 },
            '8XL': { width: 78, height: 88 },
            '9XL': { width: 81, height: 90 },
            '10XL': { width: 84, height: 92 }
        };
        
        // Papel A3: 29.7 x 42 cm
        this.a3Size = { width: 29.7, height: 42 };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupDragAndDrop();
        this.updateShirtSizeLimits();
        this.setupCanvasClipping();
    }
    
    setupEventListeners() {
        // Cambio de vista (frente/espalda)
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchView(e.target.dataset.view);
            });
        });
        
        // Upload de imágenes
        const imageUpload = document.getElementById('image-upload');
        imageUpload.addEventListener('change', (e) => {
            this.handleImageUpload(e);
        });
        
        // Controles de imagen (con verificación de existencia)
        const sizeSlider = document.getElementById('size-slider');
        const rotationSlider = document.getElementById('rotation-slider');
        
        if (sizeSlider) {
            sizeSlider.addEventListener('input', (e) => {
                e.stopPropagation();
                this.updateImageSize(e.target.value);
            });
            
            // Prevenir deselección al interactuar con el slider
            sizeSlider.addEventListener('click', (e) => {
                e.stopPropagation();
            });
            
            sizeSlider.addEventListener('mousedown', (e) => {
                e.stopPropagation();
            });
        }
        
        if (rotationSlider) {
            rotationSlider.addEventListener('input', (e) => {
                e.stopPropagation();
                this.updateImageRotation(e.target.value);
            });
            
            // Prevenir deselección al interactuar con el slider
            rotationSlider.addEventListener('click', (e) => {
                e.stopPropagation();
            });
            
            rotationSlider.addEventListener('mousedown', (e) => {
                e.stopPropagation();
            });
        }
        
        // Prevenir deselección al hacer click en los controles
        const imageControls = document.getElementById('image-controls');
        if (imageControls) {
            imageControls.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
        
        // Drag and drop de imágenes
        this.setupDesignZoneDragDrop();
        
        // Setup para click fuera del área de diseño para deseleccionar
        document.addEventListener('click', (e) => {
            // Solo deseleccionar si el click no es en una imagen, controles o sliders
            if (!e.target.closest('.design-image') && 
                !e.target.closest('.image-controls-right') && 
                !e.target.closest('.uploaded-image-item')) {
                this.deselectImage();
            }
        });
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
        // Limpiar el input para permitir subir la misma imagen de nuevo
        event.target.value = '';
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
            const imageData = {
                id: `img_${++this.imageCounter}`,
                name: file.name,
                src: e.target.result,
                size: 100,
                rotation: 0,
                x: 50,
                y: 50,
                view: null // Se asignará cuando se agregue al diseño
            };
            
            this.uploadedImages.push(imageData);
            this.renderUploadedImage(imageData);
            
            // Auto-agregar la imagen al área segura automáticamente
            this.addImageToSafeArea(imageData.id);
            
            // Mostrar controles si es la primera imagen
            if (this.uploadedImages.length === 1) {
                this.showImageControls();
            }
        };
        
        reader.readAsDataURL(file);
    }
    
    renderUploadedImage(imageData) {
        const container = document.getElementById('uploaded-images');
        
        // Verificar si ya existe el elemento
        let imageItem = document.querySelector(`[data-image-id="${imageData.id}"]`);
        if (imageItem) {
            return; // Ya existe, no crear duplicado
        }
        
        imageItem = document.createElement('div');
        imageItem.className = 'uploaded-image-item';
        imageItem.dataset.imageId = imageData.id;
        imageItem.draggable = true;
        
        // Truncar nombre del archivo si es muy largo
        const truncatedName = this.truncateFilename(imageData.name, 20);
        
        imageItem.innerHTML = `
            <img src="${imageData.src}" alt="${imageData.name}">
            <span title="${imageData.name}">${truncatedName}</span>
            <button onclick="shirtDesigner.removeUploadedImage('${imageData.id}')" style="margin-left: auto; background: none; border: none; color: #dc3545; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Drag start event para reordenamiento
        imageItem.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', imageData.id);
            e.dataTransfer.setData('application/json', JSON.stringify({
                type: 'reorder',
                imageId: imageData.id
            }));
            imageItem.classList.add('dragging');
        });
        
        imageItem.addEventListener('dragend', (e) => {
            imageItem.classList.remove('dragging');
            document.querySelectorAll('.uploaded-image-item').forEach(item => {
                item.classList.remove('drag-over');
            });
        });
        
        // Drop events para reordenamiento
        imageItem.addEventListener('dragover', (e) => {
            e.preventDefault();
            const dragData = e.dataTransfer.getData('application/json');
            if (dragData) {
                const data = JSON.parse(dragData);
                if (data.type === 'reorder') {
                    imageItem.classList.add('drag-over');
                }
            }
        });
        
        imageItem.addEventListener('dragleave', (e) => {
            imageItem.classList.remove('drag-over');
        });
        
        imageItem.addEventListener('drop', (e) => {
            e.preventDefault();
            const dragData = e.dataTransfer.getData('application/json');
            if (dragData) {
                const data = JSON.parse(dragData);
                if (data.type === 'reorder' && data.imageId !== imageData.id) {
                    this.reorderImages(data.imageId, imageData.id);
                }
            }
            imageItem.classList.remove('drag-over');
        });
        
        // Click para seleccionar
        imageItem.addEventListener('click', () => {
            this.selectUploadedImage(imageData.id);
            // Solo agregar si no está ya en el área segura
            if (!imageData.view) {
                this.addImageToSafeArea(imageData.id);
            }
        });
        
        container.appendChild(imageItem);
    }

    truncateFilename(filename, maxLength) {
        if (filename.length <= maxLength) return filename;
        
        const extension = filename.split('.').pop();
        const nameWithoutExt = filename.substring(0, filename.lastIndexOf('.'));
        const truncatedName = nameWithoutExt.substring(0, maxLength - extension.length - 4);
        
        return `${truncatedName}...${extension}`;
    }
    
    addImageToDesign(imageId, event) {
        const imageData = this.uploadedImages.find(img => img.id === imageId);
        if (!imageData) return;
        
        // Si la imagen ya está en una vista, la removemos primero
        this.removeImageFromDesign(imageId);
        
        const designZone = document.querySelector(`#${this.currentView}-design-zone`);
        const rect = designZone.getBoundingClientRect();
        
        // Posicionar automáticamente en el centro de la remera
        let x = 50, y = 50; // Centro de la remera
        
        if (event && event.clientX && event.clientY) {
            x = ((event.clientX - rect.left) / rect.width) * 100;
            y = ((event.clientY - rect.top) / rect.height) * 100;
        }
        
        imageData.view = this.currentView;
        imageData.x = Math.max(15, Math.min(85, x)); // Mantener dentro de la remera
        imageData.y = Math.max(15, Math.min(85, y)); // Mantener dentro de la remera
        
        // Aplicar tamaño máximo A3 calculado
        this.applyA3SizeLimit(imageData);
        
        this.renderDesignImage(imageData);
        this.selectDesignImage(imageId);
        
        console.log(`Imagen agregada sobre la remera - Posición: ${imageData.x.toFixed(1)}%, ${imageData.y.toFixed(1)}%`);
    }

    addImageToSafeArea(imageId) {
        const imageData = this.uploadedImages.find(img => img.id === imageId);
        if (!imageData) return;
        
        // Si la imagen ya está en el área segura, no hacer nada
        if (imageData.view === this.currentView) return;
        
        // Si la imagen ya está en una vista, la removemos primero
        this.removeImageFromDesign(imageId);
        
        imageData.view = this.currentView;
        // Centrar automáticamente sobre la remera
        imageData.x = 50;
        imageData.y = 50;
        
        // Aplicar límites A3
        this.applyA3SizeLimit(imageData);
        
        this.renderDesignImage(imageData);
        this.selectDesignImage(imageId);
        
        console.log(`Imagen centrada automáticamente sobre la remera`);
    }
    
    // Nueva función para aplicar límites A3
    applyA3SizeLimit(imageData) {
        // Calcular tamaño máximo basado en A3 y talle actual
        const maxSize = this.maxImageSizePx || 200; // Fallback a 200px si no está calculado
        
        // Aplicar el límite manteniendo proporciones
        if (imageData.size > maxSize) {
            imageData.size = maxSize;
        }
        
        // Si no tiene tamaño asignado, usar un tamaño apropiado para A3
        if (!imageData.size) {
            imageData.size = Math.min(maxSize, 150); // Tamaño inicial apropiado
        }
        
        console.log(`Tamaño A3 aplicado: ${imageData.size}px (máximo: ${maxSize}px)`);
    }

    calculateOptimalSize(imageData) {
        // Crear una imagen temporal para obtener dimensiones originales
        const tempImg = new Image();
        tempImg.onload = () => {
            const originalWidth = tempImg.naturalWidth;
            const originalHeight = tempImg.naturalHeight;
            
            // Obtener dimensiones del área segura (60% del design-zone)
            const designZone = document.querySelector(`#${imageData.view}-design-zone`);
            const safeAreaWidth = designZone.offsetWidth * 0.6; // 60% del ancho
            const safeAreaHeight = designZone.offsetHeight * 0.6; // 60% del alto
            
            // Calcular factor de escala para que quepa completamente
            const scaleX = safeAreaWidth / originalWidth;
            const scaleY = safeAreaHeight / originalHeight;
            const scale = Math.min(scaleX, scaleY) * 0.8; // 80% del máximo para dejar margen
            
            // Convertir a píxeles aproximados para el slider
            const optimalSize = Math.min(Math.max(scale * 200, 50), 300);
            imageData.size = Math.round(optimalSize);
            
            // Actualizar la imagen si ya está renderizada
            const imageElement = document.getElementById(`design-${imageData.id}`);
            if (imageElement) {
                this.updateImageStyle(imageElement, imageData);
            }
        };
        tempImg.src = imageData.src;
    }
    

    updateImageStyle(imageElement, imageData) {
        const mirrorScale = imageData.mirrored ? 'scaleX(-1)' : 'scaleX(1)';
        imageElement.style.left = `${imageData.x}%`;
        imageElement.style.top = `${imageData.y}%`;
        imageElement.style.width = `${imageData.size}px`;
        imageElement.style.height = 'auto';
        imageElement.style.transform = `translate(-50%, -50%) rotate(${imageData.rotation}deg) ${mirrorScale}`;
        imageElement.style.position = 'absolute';
    }
    
    setupImageInteraction(imageElement, imageData) {
        let isDragging = false;
        let startX, startY, startLeft, startTop;
        let dragThreshold = 5; // pixels
        let hasMoved = false;
        
        // Click para seleccionar
        imageElement.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!hasMoved) {
                this.selectDesignImage(imageData.id);
            }
        });
        
        // Mouse down para empezar drag
        imageElement.addEventListener('mousedown', (e) => {
            // No prevenir el default para permitir clicks
            if (e.target.closest('.image-controls-overlay')) {
                return; // No arrastrar si se hace click en los controles
            }
            
            isDragging = true;
            hasMoved = false;
            
            this.selectDesignImage(imageData.id);
            
            startX = e.clientX;
            startY = e.clientY;
            startLeft = imageData.x;
            startTop = imageData.y;
            
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
        
        const onMouseMove = (e) => {
            if (!isDragging) return;
            
            // Verificar si se ha movido lo suficiente para considerar un drag
            const moveDistance = Math.sqrt(
                Math.pow(e.clientX - startX, 2) + Math.pow(e.clientY - startY, 2)
            );
            
            if (moveDistance > dragThreshold) {
                hasMoved = true;
            }
            
            // El parent ahora es design-zone
            const designZone = imageElement.parentElement;
            const rect = designZone.getBoundingClientRect();
            
            const deltaX = ((e.clientX - startX) / rect.width) * 100;
            const deltaY = ((e.clientY - startY) / rect.height) * 100;
            
            // Permitir movimiento libre dentro de la zona de diseño
            imageData.x = Math.max(0, Math.min(100, startLeft + deltaX));
            imageData.y = Math.max(0, Math.min(100, startTop + deltaY));
            
            this.updateImageStyle(imageElement, imageData);
            this.checkCenterGuides(imageData);
        };
        
        const onMouseUp = () => {
            isDragging = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            this.hideCenterGuides();
            
            // Reset para el próximo click
            setTimeout(() => {
                hasMoved = false;
            }, 10);
        };
        
        // Touch events para móvil
        imageElement.addEventListener('touchstart', (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            imageElement.dispatchEvent(mouseEvent);
        });
    }
    
    selectDesignImage(imageId) {
        // Deseleccionar todas las imágenes
        document.querySelectorAll('.design-image').forEach(img => {
            img.classList.remove('selected');
        });
        
        // Seleccionar la imagen actual
        const imageElement = document.getElementById(`design-${imageId}`);
        if (imageElement) {
            imageElement.classList.add('selected');
            this.selectedImage = imageId;
            this.showImageControls();
            this.updateControlValues();
        }
    }
    
    deselectImage() {
        document.querySelectorAll('.design-image').forEach(img => {
            img.classList.remove('selected');
        });
        
        this.selectedImage = null;
        
        // Solo ocultar controles si no hay imágenes en el diseño
        const imagesInDesign = this.uploadedImages.filter(img => img.view);
        if (imagesInDesign.length === 0) {
            this.hideImageControls();
        } else {
            // Mantener controles visibles pero deshabilitados
            this.updateControlValues();
        }
    }
    
    showImageControls() {
        document.getElementById('image-controls').style.display = 'block';
    }
    
    hideImageControls() {
        // Solo ocultar si no hay imágenes en el diseño
        const imagesInDesign = this.uploadedImages.filter(img => img.view);
        if (imagesInDesign.length === 0) {
            document.getElementById('image-controls').style.display = 'none';
        }
    }
    
    updateControlValues() {
        const sizeSlider = document.getElementById('size-slider');
        const sizeValue = document.getElementById('size-value');
        const rotationSlider = document.getElementById('rotation-slider');
        const rotationValue = document.getElementById('rotation-value');
        
        if (!this.selectedImage) {
            // Si no hay imagen seleccionada, resetear valores pero mantener controles
            if (sizeSlider) {
                sizeSlider.value = 100;
                sizeSlider.disabled = true;
            }
            if (sizeValue) {
                sizeValue.textContent = '100px';
            }
            if (rotationSlider) {
                rotationSlider.value = 0;
                rotationSlider.disabled = true;
            }
            if (rotationValue) {
                rotationValue.textContent = '0°';
            }
            return;
        }
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        if (!imageData) return;
        
        if (sizeSlider) {
            // Actualizar el máximo del slider según límites A3
            const maxSize = this.maxImageSizePx || 300;
            sizeSlider.max = maxSize;
            sizeSlider.value = imageData.size;
            sizeSlider.disabled = false;
        }
        if (sizeValue) {
            sizeValue.textContent = `${imageData.size}px`;
        }
        if (rotationSlider) {
            rotationSlider.value = imageData.rotation;
            rotationSlider.disabled = false;
        }
        if (rotationValue) {
            rotationValue.textContent = `${imageData.rotation}°`;
        }
    }
    
    updateImageSize(size) {
        if (!this.selectedImage) return;

        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const imageElement = document.getElementById(`design-${this.selectedImage}`);

        if (imageData && imageElement) {
            let newSize = parseInt(size);
            
            // Aplicar límite A3 calculado dinámicamente
            const maxSize = this.maxImageSizePx || 250; // Fallback
            
            if (newSize > maxSize) {
                newSize = maxSize;
                // Actualizar el slider al valor máximo permitido
                const sizeSlider = document.getElementById('size-slider');
                if (sizeSlider) {
                    sizeSlider.value = maxSize;
                }
            }

            imageData.size = newSize;
            this.updateImageStyle(imageElement, imageData);
            
            console.log(`Tamaño actualizado: ${newSize}px (máximo A3: ${maxSize}px)`);            
            
            const sizeValue = document.getElementById('size-value');
            if (sizeValue) {
                sizeValue.textContent = `${Math.round(newSize)}px`;
            }
            // Actualizar el slider para que no supere el máximo
            const sizeSlider = document.getElementById('size-slider');
            if (sizeSlider.value > newSize) {
                sizeSlider.value = newSize;
            }
        }
    }

    updateImageRotation(rotation) {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const imageElement = document.getElementById(`design-${this.selectedImage}`);
        
        if (imageData && imageElement) {
            imageData.rotation = parseInt(rotation);
            this.updateImageStyle(imageElement, imageData);
            const rotationValue = document.getElementById('rotation-value');
            if (rotationValue) {
                rotationValue.textContent = `${rotation}°`;
            }
        }
    }

    

        toggleResizeMode(imageId) {
        // Activar modo de redimensionamiento rápido
        this.selectDesignImage(imageId);
        const imageData = this.uploadedImages.find(img => img.id === imageId);
        if (imageData) {
            // Incrementar tamaño en pasos de 25px
            imageData.size = imageData.size >= 300 ? 50 : imageData.size + 25;
            const imageElement = document.getElementById(`design-${imageId}`);
            this.updateImageStyle(imageElement, imageData);
            this.updateControlValues();
        }
    }
    
    duplicateImage(imageId) {
        const originalImage = this.uploadedImages.find(img => img.id === imageId);
        if (!originalImage) return;
        
        // Crear nueva imagen duplicada
        const duplicatedImage = {
            id: `img_${++this.imageCounter}`,
            name: `Copia de ${originalImage.name}`,
            src: originalImage.src,
            size: originalImage.size,
            rotation: originalImage.rotation,
            x: Math.min(90, originalImage.x + 10), // Offset ligeramente
            y: Math.min(90, originalImage.y + 10),
            view: originalImage.view
        };
        
        this.uploadedImages.push(duplicatedImage);
        this.renderDesignImage(duplicatedImage);
        this.selectDesignImage(duplicatedImage.id);
    }
    
    // Nuevas funciones para canvas clipping y control de talles
    updateShirtSize(newSize) {
        this.currentShirtSize = newSize;
        this.updateShirtSizeLimits();
        this.updateCanvasClipping();
        console.log(`Talle cambiado a: ${newSize}`);
    }
    
    updateShirtSizeLimits() {
        const currentSize = this.shirtSizes[this.currentShirtSize];
        const a3 = this.a3Size;
        
        // Calcular porcentaje del área A3 respecto al talle de remera
        // A3 = 29.7cm x 42cm (ancho x alto)
        this.maxImageWidthPercent = (a3.width / currentSize.width) * 100;
        this.maxImageHeightPercent = (a3.height / currentSize.height) * 100;
        
        // Limitar las imágenes al tamaño máximo A3
        this.maxImageSizePx = Math.min(
            (this.maxImageWidthPercent / 100) * 400, // 400px es el ancho aproximado del canvas
            (this.maxImageHeightPercent / 100) * 600  // 600px es el alto aproximado del canvas
        );
        
        // Actualizar guías de sublimación
        this.updateSublimationGuides();
        
        console.log(`Límites A3 para talle ${this.currentShirtSize}:`);
        console.log(`- Ancho máximo: ${this.maxImageWidthPercent.toFixed(1)}% (${a3.width}cm)`);
        console.log(`- Alto máximo: ${this.maxImageHeightPercent.toFixed(1)}% (${a3.height}cm)`);
        console.log(`- Tamaño máximo imagen: ${this.maxImageSizePx.toFixed(0)}px`);
    }
    
    updateSublimationGuides() {
        const guides = document.querySelectorAll('.sublimation-guide');
        guides.forEach(guide => {
            guide.style.width = `${this.maxImageWidthPercent}%`;
            guide.style.height = `${this.maxImageHeightPercent}%`;
            guide.style.left = '50%';
            guide.style.top = '50%';
            guide.style.transform = 'translate(-50%, -50%)';
        });
    }
    
    setupCanvasClipping() {
        // Configurar clipping para ambas vistas
        const frontZone = document.querySelector('#front-design-zone');
        const backZone = document.querySelector('#back-design-zone');
        
        if (frontZone) {
            frontZone.style.clipPath = 'url(#shirt-clip-front)';
            frontZone.style.overflow = 'hidden';
        }
        
        if (backZone) {
            backZone.style.clipPath = 'url(#shirt-clip-back)';
            backZone.style.overflow = 'hidden';
        }
        
        this.createSVGClipPaths();
    }
    
    createSVGClipPaths() {
        // Crear SVG con clip paths para las formas de remera
        let svgDefs = document.querySelector('#shirt-clip-svg');
        if (!svgDefs) {
            svgDefs = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svgDefs.id = 'shirt-clip-svg';
            svgDefs.style.position = 'absolute';
            svgDefs.style.width = '0';
            svgDefs.style.height = '0';
            svgDefs.innerHTML = `
                <defs>
                    <clipPath id="shirt-clip-front" clipPathUnits="objectBoundingBox">
                        <path d="M0.15,0.1 L0.85,0.1 L0.85,0.25 L0.9,0.25 L0.9,0.9 L0.1,0.9 L0.1,0.25 L0.15,0.25 Z"/>
                    </clipPath>
                    <clipPath id="shirt-clip-back" clipPathUnits="objectBoundingBox">
                        <path d="M0.15,0.1 L0.85,0.1 L0.85,0.25 L0.9,0.25 L0.9,0.9 L0.1,0.9 L0.1,0.25 L0.15,0.25 Z"/>
                    </clipPath>
                </defs>
            `;
            document.body.appendChild(svgDefs);
        }
    }
    
    updateCanvasClipping() {
        // Actualizar las dimensiones del clipping según el talle
        this.setupCanvasClipping();
    }
    
    // Override de la función de renderizado para aplicar clipping
    renderDesignImage(imageData) {
        const designZone = document.querySelector(`#${imageData.view}-design-zone`);
        if (!designZone) {
            console.error(`No se encontró .design-zone para la vista: ${imageData.view}`);
            return;
        }

        const imageElement = document.createElement('img');
        imageElement.className = 'design-image';
        imageElement.id = `design-${imageData.id}`;
        imageElement.src = imageData.src;
        imageElement.alt = imageData.name;

        const controlsOverlay = document.createElement('div');
        controlsOverlay.className = 'image-controls-overlay';
        controlsOverlay.innerHTML = `
            <button class="control-icon rotate" title="Rotar" onclick="shirtDesigner.rotateImage('${imageData.id}')">
                <i class="fas fa-redo"></i>
            </button>
            <button class="control-icon resize" title="Redimensionar" onclick="shirtDesigner.toggleResizeMode('${imageData.id}')">
                <i class="fas fa-expand-arrows-alt"></i>
            </button>
            <button class="control-icon duplicate" title="Duplicar" onclick="shirtDesigner.duplicateImage('${imageData.id}')">
                <i class="fas fa-copy"></i>
            </button>
            <button class="control-icon delete" title="Eliminar" onclick="shirtDesigner.deleteImage('${imageData.id}')">
                <i class="fas fa-trash"></i>
            </button>
        `;

        imageElement.appendChild(controlsOverlay);
        this.updateImageStyle(imageElement, imageData);
        this.setupImageInteraction(imageElement, imageData);
        
        // Aplicar clipping automático
        this.applyImageClipping(imageElement);
        
        designZone.appendChild(imageElement);
        this.updateImageLayers();
    }
    
    applyImageClipping(imageElement) {
        // El clipping se maneja a nivel de la zona de diseño
        // Esto asegura que cualquier imagen fuera del área de la remera se recorte
        imageElement.style.clipPath = 'inherit';
    }
}

// Inicializar el diseñador cuando la página cargue
let shirtDesigner;

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== INICIANDO SHIRT DESIGNER ===');
    console.log('Timestamp:', new Date().toISOString());
    
    // Verificar que los elementos existen
    const frontZone = document.querySelector('#front-design-zone');
    const backZone = document.querySelector('#back-design-zone');
    const frontLimits = frontZone ? frontZone.querySelector('.sublimation-limits') : null;
    const backLimits = backZone ? backZone.querySelector('.sublimation-limits') : null;
    
    console.log('Front zone:', frontZone);
    console.log('Back zone:', backZone);
    console.log('Front limits:', frontLimits);
    console.log('Back limits:', backLimits);
    
    shirtDesigner = new ShirtDesigner();
    
    // Click fuera para deseleccionar
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.design-image') && 
            !e.target.closest('.uploaded-image-item') && 
            !e.target.closest('.image-controls') &&
            !e.target.closest('.image-controls-overlay')) {
            shirtDesigner.deselectImage();
        }
    });
    
    console.log('ShirtDesigner inicializado:', shirtDesigner);
    console.log('=== SHIRT DESIGNER LISTO ===');
    
    // Test function
    window.testShirtDesigner = function() {
        console.log('Testing ShirtDesigner...');
        console.log('Current view:', shirtDesigner.currentView);
        console.log('Uploaded images:', shirtDesigner.uploadedImages);
        return shirtDesigner;
    };
});

// Funciones globales para los botones
function deleteSelectedImage() {
    shirtDesigner.deleteSelectedImage();
}

function centerImage() {
    shirtDesigner.centerImage();
}

function saveDesign() {
    shirtDesigner.saveDesign();
}

function resetDesign() {
    shirtDesigner.resetDesign();
}

function orderShirt() {
    shirtDesigner.orderShirt();
}

function updateShirtSize() {
    const sizeSelector = document.getElementById('shirt-size');
    if (sizeSelector) {
        shirtDesigner.updateShirtSize(sizeSelector.value);
    }
}

// Última actualización: 2025-06-30 - Canvas clipping y control de talles implementado