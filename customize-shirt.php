<?php
require_once 'includes/functions.php';

$page_title = 'Personalizar Remera';
include 'includes/header.php';
?>

<div class="shirt-designer-container">
    <div class="designer-header">
        <h2>Personaliza tu Remera</h2>
        <p>Sube hasta 5 imágenes y personaliza tu diseño</p>
    </div>
    
    <div class="designer-main">
        <!-- Panel de control -->
        <div class="control-panel">
            <div class="panel-section">
                <h3>Vista de Remera</h3>
                <div class="shirt-view-toggle">
                    <button class="view-btn active" data-view="front">
                        <i class="fas fa-user"></i> Frente
                    </button>
                    <button class="view-btn" data-view="back">
                        <i class="fas fa-user"></i> Espalda
                    </button>
                </div>
            </div>
            
            <div class="panel-section">
                <h3>Talle de Remera</h3>
                <div class="size-selector">
                    <select id="shirt-size" onchange="updateShirtSize()">
                        <option value="S">S (48 x 68 cm)</option>
                        <option value="M" selected>M (51 x 70 cm)</option>
                        <option value="L">L (54 x 72 cm)</option>
                        <option value="XL">XL (57 x 74 cm)</option>
                        <option value="2XL">2XL (60 x 76 cm)</option>
                        <option value="3XL">3XL (63 x 78 cm)</option>
                        <option value="4XL">4XL (66 x 80 cm)</option>
                        <option value="5XL">5XL (69 x 82 cm)</option>
                        <option value="6XL">6XL (72 x 84 cm)</option>
                        <option value="7XL">7XL (75 x 86 cm)</option>
                        <option value="8XL">8XL (78 x 88 cm)</option>
                        <option value="9XL">9XL (81 x 90 cm)</option>
                        <option value="10XL">10XL (84 x 92 cm)</option>
                    </select>
                </div>
            </div>
            
            <div class="panel-section">
                <h3>Subir Imágenes</h3>
                <div class="upload-area">
                    <input type="file" id="image-upload" accept="image/*" multiple style="display: none;">
                    <button class="upload-btn" onclick="document.getElementById('image-upload').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Subir Imagen
                    </button>
                    <p class="upload-info">Máximo 5 imágenes (JPG, PNG)</p>
                </div>
                
                <div class="uploaded-images" id="uploaded-images">
                    <!-- Las imágenes subidas aparecerán aquí -->
                </div>
            </div>
            
            
            <div class="panel-section">
                <h3>Acciones</h3>
                <button class="action-btn preview" onclick="showFullPreview()">
                    <i class="fas fa-eye"></i> Vista Previa
                </button>
                <button class="action-btn primary" onclick="saveDesign()">
                    <i class="fas fa-save"></i> Guardar Diseño
                </button>
                <button class="action-btn secondary" onclick="resetDesign()">
                    <i class="fas fa-undo"></i> Reiniciar
                </button>
                <button class="action-btn success" onclick="orderShirt()">
                    <i class="fas fa-shopping-cart"></i> Ordenar
                </button>
            </div>
        </div>
        
        <!-- Área de diseño -->
        <div class="design-area">
            <div class="design-main-content">
                <div class="shirt-canvas" id="shirt-canvas">
                    <!-- Remera frente -->
                    <div class="shirt-view" id="front-view">
                        <img src="/proyecto/assets/images/remera-frente.png" 
                             alt="Remera Frente" class="shirt-template">
                        <div class="design-zone front-zone" id="front-design-zone">
                            <!-- Guías de ayuda -->
                            <div class="guide-lines">
                                <div class="center-guide-h"></div>
                                <div class="center-guide-v"></div>
                            </div>
                            <div class="sublimation-guide"></div>
                            <!-- Las imágenes del usuario aparecerán aquí -->
                        </div>
                    </div>
                    
                    <!-- Remera espalda -->
                    <div class="shirt-view" id="back-view" style="display: none;">
                        <img src="/proyecto/assets/images/remera-espalda.png" 
                             alt="Remera Espalda" class="shirt-template">
                        <div class="design-zone back-zone" id="back-design-zone">
                            <!-- Guías de ayuda -->
                            <div class="guide-lines">
                                <div class="center-guide-h"></div>
                                <div class="center-guide-v"></div>
                            </div>
                            <div class="sublimation-guide"></div>
                            <!-- Las imágenes del usuario aparecerán aquí -->
                        </div>
                    </div>
                </div>
                
                <!-- Información de ayuda -->
                <div class="help-info">
                    <div class="info-item">
                        <i class="fas fa-info-circle"></i>
                        <span>Las líneas punteadas muestran el área de sublimación según el talle seleccionado</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-cut"></i>
                        <span>Las imágenes se recortan automáticamente fuera del área de la remera</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-crosshairs"></i>
                        <span>Las guías de centro aparecen al acercar imágenes al centro</span>
                    </div>
                </div>
            </div>
            
            <!-- Controles de imagen a la derecha -->
            <div class="image-controls-right" id="image-controls" style="display: none;">
                <h4>Editar Imagen</h4>
                
                <div class="control-group">
                    <label for="size-slider">
                        <i class="fas fa-expand-arrows-alt"></i>
                        Tamaño
                    </label>
                    <input type="range" id="size-slider" min="20" max="300" value="100">
                    <span id="size-value">100px</span>
                </div>
                
                <div class="control-group">
                    <label for="rotation-slider">
                        <i class="fas fa-redo"></i>
                        Rotación
                    </label>
                    <input type="range" id="rotation-slider" min="0" max="360" value="0">
                    <span id="rotation-value">0°</span>
                </div>
                
                <div class="control-buttons">
                    <button class="control-btn" onclick="centerImage()">
                        <i class="fas fa-crosshairs"></i> Centrar
                    </button>
                    <button class="control-btn" onclick="shirtDesigner.toggleResizeMode(shirtDesigner.selectedImage)">
                        <i class="fas fa-expand-arrows-alt"></i> Redimensionar
                    </button>
                    <button class="control-btn" onclick="shirtDesigner.duplicateImage(shirtDesigner.selectedImage)">
                        <i class="fas fa-copy"></i> Duplicar
                    </button>
                    <button class="control-btn danger" onclick="deleteSelectedImage()">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Vista Previa -->
<div class="preview-modal" id="preview-modal" style="display: none;">
    <div class="preview-overlay"></div>
    <div class="preview-content">
        <div class="preview-header">
            <h3>Vista Previa del Diseño</h3>
            <button class="close-preview-btn" onclick="closeFullPreview()"></button>
        </div>
        <div class="preview-body">
            <div class="preview-shirt-container">
                <!-- Se clonará el contenido del diseño aquí -->
            </div>
        </div>
    </div>
</div>

<script src="assets/js/shirt-designer.js"></script>

<?php include 'includes/footer.php'; ?>