<?php
require_once 'includes/functions.php';
require_once 'config/database.php';
require_once 'includes/shipping/ShippingManager.php';

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Seguimiento de Envío';

// Obtener número de seguimiento
$trackingNumber = $_GET['tracking'] ?? '';
$provider = $_GET['provider'] ?? '';

if (empty($trackingNumber) || empty($provider)) {
    header('Location: index.php');
    exit();
}

// Crear instancia del gestor de envíos
$shippingManager = new ShippingManager();

// Obtener información del envío
$shipmentStatus = $shippingManager->getShipmentStatus($provider, $trackingNumber);

include 'includes/header.php';
?>

<div class="tracking-container">
    <section class="tracking-main">
        <div class="container">
            <div class="tracking-header">
                <h1>
                    <i class="fas fa-shipping-fast"></i>
                    Seguimiento de Envío
                </h1>
                <p class="tracking-subtitle">
                    Seguí el estado de tu pedido en tiempo real
                </p>
            </div>

            <div class="tracking-search">
                <form method="GET" action="tracking.php" class="tracking-search-form">
                    <div class="search-group">
                        <input type="text" 
                               name="tracking" 
                               placeholder="Ingresá tu número de seguimiento" 
                               value="<?php echo htmlspecialchars($trackingNumber); ?>"
                               required>
                        <select name="provider" required>
                            <option value="">Seleccionar servicio</option>
                            <option value="uber_direct" <?php echo $provider === 'uber_direct' ? 'selected' : ''; ?>>Uber Direct</option>
                            <option value="andreani" <?php echo $provider === 'andreani' ? 'selected' : ''; ?>>Andreani</option>
                            <option value="pickup" <?php echo $provider === 'pickup' ? 'selected' : ''; ?>>Retiro en Sucursal</option>
                        </select>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <?php if ($shipmentStatus['success']): ?>
                <div class="tracking-results">
                    <!-- Información del envío -->
                    <div class="shipment-info-card">
                        <div class="shipment-header">
                            <div class="shipment-id">
                                <h2>
                                    <?php echo $provider === 'pickup' ? 'Retiro #' : 'Envío #'; ?>
                                    <?php echo htmlspecialchars($trackingNumber); ?>
                                </h2>
                                <span class="provider-badge <?php echo $provider; ?>">
                                    <?php
                                    switch ($provider) {
                                        case 'uber_direct':
                                            echo '<i class="fab fa-uber"></i> Uber Direct';
                                            break;
                                        case 'andreani':
                                            echo '<i class="fas fa-truck"></i> Andreani';
                                            break;
                                        case 'pickup':
                                            echo '<i class="fas fa-store"></i> Retiro en Sucursal';
                                            break;
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="shipment-status">
                                <span class="status-badge <?php echo strtolower($shipmentStatus['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $shipmentStatus['status'])); ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($provider === 'pickup'): ?>
                            <!-- Información de retiro -->
                            <div class="pickup-info">
                                <h3><i class="fas fa-map-marker-alt"></i> Información de Retiro</h3>
                                <div class="pickup-details">
                                    <div class="pickup-address">
                                        <h4>Dirección de Retiro:</h4>
                                        <p><?php echo PICKUP_ADDRESS['street']; ?></p>
                                        <p><?php echo PICKUP_ADDRESS['city'] . ', ' . PICKUP_ADDRESS['state']; ?></p>
                                        <p>CP: <?php echo PICKUP_ADDRESS['postal_code']; ?></p>
                                        <p><small><i class="fas fa-map-marker-alt"></i> Plus Code: <?php echo PICKUP_ADDRESS['plus_code']; ?></small></p>
                                    </div>
                                    <div class="pickup-hours">
                                        <h4>Horarios de Atención:</h4>
                                        <p><strong>Lunes a Viernes:</strong> 9:00 - 18:00</p>
                                        <p><strong>Sábados:</strong> 9:00 - 13:00</p>
                                        <p><strong>Domingos:</strong> Cerrado</p>
                                    </div>
                                    <div class="pickup-contact">
                                        <h4>Contacto:</h4>
                                        <p><i class="fas fa-phone"></i> <?php echo EMPRESA_TELEFONO; ?></p>
                                        <p><i class="fas fa-envelope"></i> <?php echo EMPRESA_EMAIL; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Información de delivery -->
                            <div class="delivery-info">
                                <?php if (isset($shipmentStatus['driver_name']) && $shipmentStatus['driver_name']): ?>
                                    <div class="driver-info">
                                        <h3><i class="fas fa-user"></i> Información del Repartidor</h3>
                                        <div class="driver-details">
                                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($shipmentStatus['driver_name']); ?></p>
                                            <?php if (isset($shipmentStatus['driver_phone']) && $shipmentStatus['driver_phone']): ?>
                                                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($shipmentStatus['driver_phone']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($shipmentStatus['estimated_delivery']) && $shipmentStatus['estimated_delivery']): ?>
                                    <div class="delivery-estimate">
                                        <h3><i class="fas fa-clock"></i> Estimación de Entrega</h3>
                                        <p class="delivery-time">
                                            <?php echo date('d/m/Y H:i', strtotime($shipmentStatus['estimated_delivery'])); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($shipmentStatus['tracking_url']) && $shipmentStatus['tracking_url']): ?>
                                    <div class="external-tracking">
                                        <a href="<?php echo htmlspecialchars($shipmentStatus['tracking_url']); ?>" 
                                           target="_blank" 
                                           class="btn-secondary">
                                            <i class="fas fa-external-link-alt"></i>
                                            Ver en <?php echo ucfirst($provider); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Timeline de eventos -->
                    <?php if (isset($shipmentStatus['events']) && !empty($shipmentStatus['events'])): ?>
                        <div class="tracking-timeline">
                            <h3><i class="fas fa-history"></i> Historial de Movimientos</h3>
                            <div class="timeline">
                                <?php foreach ($shipmentStatus['events'] as $event): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-time">
                                                <?php echo date('d/m/Y H:i', strtotime($event['timestamp'])); ?>
                                            </div>
                                            <div class="timeline-description">
                                                <?php echo htmlspecialchars($event['description']); ?>
                                            </div>
                                            <?php if (isset($event['location']) && $event['location']): ?>
                                                <div class="timeline-location">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo htmlspecialchars($event['location']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Timeline simplificado para cuando no hay eventos detallados -->
                        <div class="tracking-timeline">
                            <h3><i class="fas fa-history"></i> Estado del Envío</h3>
                            <div class="simple-status">
                                <div class="status-steps">
                                    <div class="status-step active">
                                        <div class="step-icon">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="step-label">Pedido Confirmado</div>
                                    </div>
                                    <div class="status-step <?php echo in_array($shipmentStatus['status'], ['in_transit', 'out_for_delivery', 'delivered']) ? 'active' : ''; ?>">
                                        <div class="step-icon">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                        <div class="step-label">En Tránsito</div>
                                    </div>
                                    <div class="status-step <?php echo $shipmentStatus['status'] === 'delivered' ? 'active' : ''; ?>">
                                        <div class="step-icon">
                                            <i class="fas fa-home"></i>
                                        </div>
                                        <div class="step-label">Entregado</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Mapa (si hay coordenadas del repartidor) -->
                    <?php if (isset($shipmentStatus['driver_location']) && 
                              $shipmentStatus['driver_location']['lat'] && 
                              $shipmentStatus['driver_location']['lng']): ?>
                        <div class="tracking-map">
                            <h3><i class="fas fa-map"></i> Ubicación en Tiempo Real</h3>
                            <div id="tracking-map-container" style="height: 300px; border-radius: 8px; overflow: hidden;">
                                <!-- Aquí se integraría un mapa como Google Maps o OpenStreetMap -->
                                <div class="map-placeholder">
                                    <i class="fas fa-map-marked-alt"></i>
                                    <p>Mapa de seguimiento en tiempo real</p>
                                    <small>Lat: <?php echo $shipmentStatus['driver_location']['lat']; ?>, 
                                           Lng: <?php echo $shipmentStatus['driver_location']['lng']; ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Auto-refresh para tracking en tiempo real -->
                <script>
                    // Refrescar la página cada 30 segundos si el envío está en tránsito
                    <?php if (in_array($shipmentStatus['status'], ['in_transit', 'out_for_delivery'])): ?>
                        setTimeout(function() {
                            window.location.reload();
                        }, 30000);
                    <?php endif; ?>
                </script>

            <?php else: ?>
                <!-- Error o no encontrado -->
                <div class="tracking-error">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>No se pudo encontrar el envío</h3>
                    <p><?php echo htmlspecialchars($shipmentStatus['error'] ?? 'Número de seguimiento inválido o servicio no disponible.'); ?></p>
                    <div class="error-actions">
                        <button onclick="window.history.back()" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Volver
                        </button>
                        <a href="index.php" class="btn-primary">
                            <i class="fas fa-home"></i>
                            Ir al Inicio
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<style>
/* Estilos específicos para tracking */
.tracking-container {
    background: var(--bg-primary);
    min-height: 100vh;
    padding: 2rem 0;
}

.tracking-header {
    text-align: center;
    margin-bottom: 3rem;
}

.tracking-header h1 {
    color: var(--text-primary);
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.tracking-header h1 i {
    color: var(--ecommerce-primary);
    margin-right: 1rem;
}

.tracking-subtitle {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.tracking-search {
    background: var(--bg-secondary);
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: var(--ecommerce-shadow);
}

.tracking-search-form .search-group {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 1rem;
    max-width: 800px;
    margin: 0 auto;
}

.tracking-search input,
.tracking-search select {
    padding: 1rem;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.tracking-search input:focus,
.tracking-search select:focus {
    outline: none;
    border-color: var(--ecommerce-primary);
}

.shipment-info-card {
    background: var(--bg-secondary);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--ecommerce-shadow);
}

.shipment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    gap: 1rem;
}

.shipment-id h2 {
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.provider-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
}

.provider-badge.uber_direct {
    background: #000000;
    color: white;
}

.provider-badge.andreani {
    background: #ff6b00;
    color: white;
}

.provider-badge.pickup {
    background: var(--ecommerce-primary);
    color: white;
}

.status-badge {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-badge.pending { background: #fef3c7; color: #92400e; }
.status-badge.confirmed { background: #dbeafe; color: #1e40af; }
.status-badge.in_transit { background: #fde68a; color: #d97706; }
.status-badge.out_for_delivery { background: #fed7aa; color: #ea580c; }
.status-badge.delivered { background: #dcfce7; color: #166534; }
.status-badge.ready_for_pickup { background: #e0e7ff; color: #3730a3; }

.pickup-info,
.delivery-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.pickup-info h3,
.delivery-info h3 {
    color: var(--text-primary);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tracking-timeline {
    background: var(--bg-secondary);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border-color);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--ecommerce-primary);
    border: 3px solid var(--bg-secondary);
}

.timeline-content {
    padding-left: 1rem;
}

.timeline-time {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.timeline-description {
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
}

.timeline-location {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.status-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    max-width: 600px;
    margin: 0 auto;
}

.status-steps::before {
    content: '';
    position: absolute;
    top: 2rem;
    left: 2rem;
    right: 2rem;
    height: 2px;
    background: var(--border-color);
    z-index: 1;
}

.status-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2;
}

.step-icon {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    background: var(--bg-primary);
    border: 3px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.status-step.active .step-icon {
    background: var(--ecommerce-primary);
    border-color: var(--ecommerce-primary);
    color: white;
}

.step-label {
    font-weight: 600;
    color: var(--text-secondary);
}

.status-step.active .step-label {
    color: var(--text-primary);
}

.tracking-error {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--bg-secondary);
    border-radius: 12px;
}

.error-icon {
    font-size: 4rem;
    color: #ef4444;
    margin-bottom: 1rem;
}

.error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.map-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: var(--bg-primary);
    color: var(--text-secondary);
}

.map-placeholder i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--ecommerce-primary);
}

@media (max-width: 768px) {
    .tracking-search-form .search-group {
        grid-template-columns: 1fr;
    }
    
    .shipment-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .pickup-info,
    .delivery-info {
        grid-template-columns: 1fr;
    }
    
    .error-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<?php include 'includes/footer.php'; ?>