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
            
            <div class="panel-section" id="image-controls" style="display: none;">
                <h3>Controles de Imagen</h3>
                <div class="control-group">
                    <label>Tamaño:</label>
                    <input type="range" id="size-slider" min="50" max="300" value="100" class="slider">
                    <span id="size-value">100%</span>
                </div>
                
                <div class="control-group">
                    <label>Rotación:</label>
                    <input type="range" id="rotation-slider" min="0" max="360" value="0" class="slider">
                    <span id="rotation-value">0°</span>
                </div>
                
                <div class="control-group">
                    <button class="control-btn" onclick="deleteSelectedImage()">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                    <button class="control-btn" onclick="centerImage()">
                        <i class="fas fa-crosshairs"></i> Centrar
                    </button>
                </div>
            </div>
            
            <div class="panel-section">
                <h3>Acciones</h3>
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
            <div class="shirt-canvas" id="shirt-canvas">
                <!-- Remera frente -->
                <div class="shirt-view" id="front-view">
                    <img src="https://drive.usercontent.google.com/download?id=1oqO8dZpEswMEiRapgN4qt_Kp_QiUJrWN&export=view&authuser=0" 
                         alt="Remera Frente" class="shirt-template">
                    <div class="design-zone front-zone" id="front-design-zone">
                        <!-- Guías de ayuda -->
                        <div class="guide-lines">
                            <div class="center-guide-h"></div>
                            <div class="center-guide-v"></div>
                            <div class="sublimation-limits"></div>
                        </div>
                        <!-- Las imágenes del usuario aparecerán aquí -->
                    </div>
                </div>
                
                <!-- Remera espalda -->
                <div class="shirt-view" id="back-view" style="display: none;">
                    <img src="https://drive.usercontent.google.com/download?id=1_y_UYYLL-iDMfXvOne_h_DpLBTxmXhid&export=view&authuser=0" 
                         alt="Remera Espalda" class="shirt-template">
                    <div class="design-zone back-zone" id="back-design-zone">
                        <!-- Guías de ayuda -->
                        <div class="guide-lines">
                            <div class="center-guide-h"></div>
                            <div class="center-guide-v"></div>
                            <div class="sublimation-limits"></div>
                        </div>
                        <!-- Las imágenes del usuario aparecerán aquí -->
                    </div>
                </div>
            </div>
            
            <!-- Información de ayuda -->
            <div class="help-info">
                <div class="info-item">
                    <i class="fas fa-info-circle"></i>
                    <span>Las líneas punteadas muestran los límites de sublimación</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-crosshairs"></i>
                    <span>Las guías de centro aparecen al acercar imágenes al centro</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/shirt-designer.js"></script>

<?php include 'includes/footer.php'; ?>