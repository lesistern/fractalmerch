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
        imageData.x = Math.max(5, Math.min(95, x));
        imageData.y = Math.max(5, Math.min(95, y));
        
        this.renderDesignImage(imageData);
        this.selectDesignImage(imageId);
    }
    
    renderDesignImage(imageData) {
        const designZone = document.querySelector(`#${imageData.view}-design-zone`);
        
        const imageElement = document.createElement('img');
        imageElement.className = 'design-image';
        imageElement.id = `design-${imageData.id}`;
        imageElement.src = imageData.src;
        imageElement.alt = imageData.name;
        
        this.updateImageStyle(imageElement, imageData);
        
        // Event listeners para interacción
        this.setupImageInteraction(imageElement, imageData);
        
        designZone.appendChild(imageElement);
    }
    
    updateImageStyle(imageElement, imageData) {
        imageElement.style.left = `${imageData.x}%`;
        imageElement.style.top = `${imageData.y}%`;
        imageElement.style.width = `${imageData.size}px`;
        imageElement.style.height = 'auto';
        imageElement.style.transform = `translate(-50%, -50%) rotate(${imageData.rotation}deg)`;
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
            
            const designZone = imageElement.parentElement;
            const rect = designZone.getBoundingClientRect();
            
            const deltaX = ((e.clientX - startX) / rect.width) * 100;
            const deltaY = ((e.clientY - startY) / rect.height) * 100;
            
            imageData.x = Math.max(5, Math.min(95, startLeft + deltaX));
            imageData.y = Math.max(5, Math.min(95, startTop + deltaY));
            
            this.updateImageStyle(imageElement, imageData);
            this.checkCenterGuides(imageData);
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
        const imageElement = document.getElementById(`design-${this.selectedImage}`);
        
        if (imageData && imageElement) {
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
}

// Inicializar el diseñador cuando la página cargue
let shirtDesigner;

document.addEventListener('DOMContentLoaded', function() {
    shirtDesigner = new ShirtDesigner();
    
    // Click fuera para deseleccionar
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.design-image') && !e.target.closest('.uploaded-image-item')) {
            shirtDesigner.deselectImage();
        }
    });
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