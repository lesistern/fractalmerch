// Diseñador de Remeras - JavaScript
class ShirtDesigner {
    constructor() {
        this.uploadedImages = [];
        this.currentView = 'front';
        this.selectedImage = null;
        this.maxImages = 5;
        this.imageCounter = 0;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupDragAndDrop();
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
        
        // Controles de imagen
        document.getElementById('size-slider').addEventListener('input', (e) => {
            this.updateImageSize(e.target.value);
        });
        
        document.getElementById('rotation-slider').addEventListener('input', (e) => {
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
        };
        
        reader.readAsDataURL(file);
    }
    
    renderUploadedImage(imageData) {
        const container = document.getElementById('uploaded-images');
        
        const imageItem = document.createElement('div');
        imageItem.className = 'uploaded-image-item';
        imageItem.dataset.imageId = imageData.id;
        imageItem.draggable = true;
        
        imageItem.innerHTML = `
            <img src="${imageData.src}" alt="${imageData.name}">
            <span>${imageData.name}</span>
            <button onclick="shirtDesigner.removeUploadedImage('${imageData.id}')" style="margin-left: auto; background: none; border: none; color: #dc3545; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Drag start event
        imageItem.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', imageData.id);
        });
        
        // Click para seleccionar
        imageItem.addEventListener('click', () => {
            this.selectUploadedImage(imageData.id);
        });
        
        container.appendChild(imageItem);
    }
    
    addImageToDesign(imageId, event) {
        const imageData = this.uploadedImages.find(img => img.id === imageId);
        if (!imageData) return;
        
        // Si la imagen ya está en una vista, la removemos primero
        this.removeImageFromDesign(imageId);
        
        const designZone = document.querySelector(`#${this.currentView}-design-zone`);
        const rect = designZone.getBoundingClientRect();
        
        // Calcular posición relativa al evento drop
        let x = 50, y = 50; // Posición por defecto en el centro
        
        if (event && event.clientX && event.clientY) {
            x = ((event.clientX - rect.left) / rect.width) * 100;
            y = ((event.clientY - rect.top) / rect.height) * 100;
        }
        
        imageData.view = this.currentView;
        // Ajustar coordenadas para el área segura (20% a 80% del design-zone)
        imageData.x = Math.max(20, Math.min(80, x));
        imageData.y = Math.max(20, Math.min(80, y));
        
        this.renderDesignImage(imageData);
        this.selectDesignImage(imageId);
    }
    
    renderDesignImage(imageData) {
        // Buscar el contenedor .sublimation-limits dentro de la vista actual
        const designZone = document.querySelector(`#${imageData.view}-design-zone`);
        const sublimationLimits = designZone ? designZone.querySelector('.sublimation-limits') : null;
        
        if (!sublimationLimits) {
            console.error(`No se encontró .sublimation-limits para la vista: ${imageData.view}`);
            return;
        }
        
        console.log('Agregando imagen al sublimation-limits:', sublimationLimits);
        console.log('Datos de imagen:', imageData);
        
        const imageElement = document.createElement('img');
        imageElement.className = 'design-image';
        imageElement.id = `design-${imageData.id}`;
        imageElement.src = imageData.src;
        imageElement.alt = imageData.name;
        
        // Crear controles flotantes
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
        
        // Event listeners para interacción
        this.setupImageInteraction(imageElement, imageData);
        
        sublimationLimits.appendChild(imageElement);
    }
    
    updateImageStyle(imageElement, imageData) {
        // Las coordenadas están en porcentaje del design-zone (20%-80%)
        // Convertir a porcentaje del sublimation-limits (0%-100%)
        const relativeX = (imageData.x - 20) * (100/60); // Convertir de design-zone a sublimation-limits
        const relativeY = (imageData.y - 20) * (100/60);
        
        imageElement.style.left = `${relativeX}%`;
        imageElement.style.top = `${relativeY}%`;
        imageElement.style.width = `${imageData.size}px`;
        imageElement.style.height = 'auto';
        imageElement.style.transform = `translate(-50%, -50%) rotate(${imageData.rotation}deg)`;
        imageElement.style.transition = 'none';
        imageElement.style.position = 'absolute';
    }
    
    setupImageInteraction(imageElement, imageData) {
        let isDragging = false;
        let startX, startY, startLeft, startTop;
        
        // Click para seleccionar
        imageElement.addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectDesignImage(imageData.id);
        });
        
        // Mouse down para empezar drag
        imageElement.addEventListener('mousedown', (e) => {
            e.preventDefault();
            isDragging = true;
            
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
            
            // El parent ahora es .sublimation-limits, necesitamos el design-zone para calcular
            const sublimationLimits = imageElement.parentElement;
            const guideLines = sublimationLimits.parentElement; 
            const designZone = guideLines.parentElement; // sublimation-limits -> guide-lines -> design-zone
            const rect = designZone.getBoundingClientRect();
            
            const deltaX = ((e.clientX - startX) / rect.width) * 100;
            const deltaY = ((e.clientY - startY) / rect.height) * 100;
            
            // Limitar movimiento al área segura (20% a 80% del design-zone)
            imageData.x = Math.max(20, Math.min(80, startLeft + deltaX));
            imageData.y = Math.max(20, Math.min(80, startTop + deltaY));
            
            this.updateImageStyle(imageElement, imageData);
            this.checkCenterGuides(imageData);
            console.log('Moviendo imagen:', imageData.x, imageData.y);
        };
        
        const onMouseUp = () => {
            isDragging = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            this.hideCenterGuides();
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
        this.hideImageControls();
    }
    
    showImageControls() {
        document.getElementById('image-controls').style.display = 'block';
    }
    
    hideImageControls() {
        document.getElementById('image-controls').style.display = 'none';
    }
    
    updateControlValues() {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        if (!imageData) return;
        
        document.getElementById('size-slider').value = imageData.size;
        document.getElementById('size-value').textContent = `${imageData.size}%`;
        document.getElementById('rotation-slider').value = imageData.rotation;
        document.getElementById('rotation-value').textContent = `${imageData.rotation}°`;
    }
    
    updateImageSize(size) {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const imageElement = document.getElementById(`design-${this.selectedImage}`);
        
        if (imageData && imageElement) {
            imageData.size = parseInt(size);
            this.updateImageStyle(imageElement, imageData);
            document.getElementById('size-value').textContent = `${size}%`;
        }
    }
    
    updateImageRotation(rotation) {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const imageElement = document.getElementById(`design-${this.selectedImage}`);
        
        if (imageData && imageElement) {
            imageData.rotation = parseInt(rotation);
            this.updateImageStyle(imageElement, imageData);
            document.getElementById('rotation-value').textContent = `${rotation}°`;
        }
    }
    
    checkCenterGuides(imageData) {
        const tolerance = 3; // 3% de tolerancia más estricta
        const designZone = document.querySelector(`#${imageData.view}-design-zone`);
        const imageElement = document.getElementById(`design-${imageData.id}`);
        
        const centerH = designZone.querySelector('.center-guide-h');
        const centerV = designZone.querySelector('.center-guide-v');
        
        let isSnappedH = false;
        let isSnappedV = false;
        
        // Mostrar guía horizontal solo cuando está exactamente centrada en Y
        if (Math.abs(imageData.y - 50) < tolerance) {
            centerH.classList.add('show');
            imageData.y = 50; // Snap al centro exacto
            isSnappedH = true;
            console.log('Snap horizontal activado');
        } else {
            centerH.classList.remove('show');
        }
        
        // Mostrar guía vertical solo cuando está exactamente centrada en X  
        if (Math.abs(imageData.x - 50) < tolerance) {
            centerV.classList.add('show');
            imageData.x = 50; // Snap al centro exacto
            isSnappedV = true;
            console.log('Snap vertical activado');
        } else {
            centerV.classList.remove('show');
        }
        
        // Aplicar clipping según la posición
        if (imageElement) {
            // Limpiar clases previas
            imageElement.classList.remove('snapped-center-h', 'snapped-center-v', 'snapped-center-both');
            
            // Aplicar clipping según la posición
            if (isSnappedH && isSnappedV) {
                imageElement.classList.add('snapped-center-both');
                console.log('Clipping aplicado: both');
            } else if (isSnappedH) {
                imageElement.classList.add('snapped-center-h');
                console.log('Clipping aplicado: horizontal');
            } else if (isSnappedV) {
                imageElement.classList.add('snapped-center-v');
                console.log('Clipping aplicado: vertical');
            }
        }
    }
    
    hideCenterGuides() {
        document.querySelectorAll('.center-guide-h, .center-guide-v').forEach(guide => {
            guide.classList.remove('show');
        });
        
        // Quitar clipping de todas las imágenes
        document.querySelectorAll('.design-image').forEach(img => {
            img.classList.remove('snapped-center-h', 'snapped-center-v', 'snapped-center-both');
        });
    }
    
    centerImage() {
        if (!this.selectedImage) return;
        
        const imageData = this.uploadedImages.find(img => img.id === this.selectedImage);
        const imageElement = document.getElementById(`design-${this.selectedImage}`);
        
        if (imageData && imageElement) {
            // Centrar en el área segura (center del 20%-80% = 50%)
            imageData.x = 50;
            imageData.y = 50;
            this.updateImageStyle(imageElement, imageData);
        }
    }
    
    deleteSelectedImage() {
        if (!this.selectedImage) return;
        
        this.removeImageFromDesign(this.selectedImage);
        this.deselectImage();
    }
    
    removeImageFromDesign(imageId) {
        const imageElement = document.getElementById(`design-${imageId}`);
        
        if (imageElement) {
            imageElement.remove();
        }
        
        // Limpiar la vista de la imagen
        const imageData = this.uploadedImages.find(img => img.id === imageId);
        if (imageData) {
            imageData.view = null;
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
            document.querySelectorAll('.design-image').forEach(img => img.remove());
            
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
    
    orderShirt() {
        const imagesInDesign = this.uploadedImages.filter(img => img.view);
        
        if (imagesInDesign.length === 0) {
            alert('Agrega al menos una imagen al diseño antes de ordenar');
            return;
        }
        
        // Aquí podrías integrar con un sistema de órdenes
        alert('¡Diseño listo para ordenar! (Esta funcionalidad se implementaría con un sistema de órdenes)');
    }
    
    // Nuevas funciones para controles de imagen
    rotateImage(imageId) {
        const imageData = this.uploadedImages.find(img => img.id === imageId);
        const imageElement = document.getElementById(`design-${imageId}`);
        
        if (imageData && imageElement) {
            imageData.rotation = (imageData.rotation + 90) % 360;
            this.updateImageStyle(imageElement, imageData);
            this.selectDesignImage(imageId);
            this.updateControlValues();
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
    
    deleteImage(imageId) {
        this.removeImageFromDesign(imageId);
        this.deselectImage();
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
        if (!e.target.closest('.design-image') && !e.target.closest('.uploaded-image-item')) {
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

// Última actualización: 2025-06-27 15:48 - Guías dinámicas y clipping implementados