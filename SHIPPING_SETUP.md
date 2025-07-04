# Guía de Configuración de APIs de Envío

## 📦 Integración Uber Direct y Andreani

Esta guía te ayudará a configurar las APIs de Uber Direct y Andreani para el sistema de envíos.

## 🚀 Pasos de Configuración

### 1. Configurar Variables de Entorno

1. Copia el archivo `.env.example` a `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edita el archivo `.env` con tus credenciales reales:
   ```env
   # Uber Direct
   UBER_DIRECT_CLIENT_ID=tu_client_id_real
   UBER_DIRECT_CLIENT_SECRET=tu_client_secret_real
   UBER_DIRECT_SANDBOX=false  # true para testing, false para producción
   
   # Andreani
   ANDREANI_API_KEY=tu_api_key_real
   ANDREANI_CLIENT_ID=tu_client_id_real
   ANDREANI_CLIENT_SECRET=tu_client_secret_real
   ANDREANI_SANDBOX=false  # true para testing, false para producción
   
   # Webhook
   WEBHOOK_SECRET=clave_super_secreta_para_webhooks
   ```

### 2. Obtener Credenciales de Uber Direct

1. Ve a [Uber Developer Portal](https://developer.uber.com/)
2. Crea una nueva aplicación
3. Solicita acceso a "Deliveries API"
4. Obtén tu `CLIENT_ID` y `CLIENT_SECRET`
5. Configura el webhook URL: `https://tu-dominio.com/proyecto/webhook/uber-direct.php`

### 3. Obtener Credenciales de Andreani

1. Contacta a Andreani para obtener acceso a su API
2. Solicita credenciales para el entorno de desarrollo (sandbox)
3. Una vez aprobado, obtén tu `API_KEY`, `CLIENT_ID` y `CLIENT_SECRET`
4. Configura el webhook URL: `https://tu-dominio.com/proyecto/webhook/andreani.php`

### 4. Configurar Dirección de Origen

La dirección ya está configurada para **Posadas, Misiones** usando el Plus Code **H3C9+4RF**:

```php
// Dirección configurada en config/shipping_apis.php
define('PICKUP_ADDRESS', [
    'street' => 'Calle Sargento Acosta 3947',
    'city' => 'Posadas',
    'state' => 'Misiones',
    'postal_code' => '3300',
    'country' => 'AR',
    'latitude' => -27.4297,  // 27°25'46.9"S
    'longitude' => -55.9304, // 55°55'49.6"W
    'plus_code' => 'H3C9+4RF'
]);
```

**Si necesitas cambiar la dirección exacta:**
1. Actualiza el campo `street` con tu dirección real
2. Verifica que las coordenadas correspondan a tu ubicación
3. El Plus Code ya está configurado para la zona

### 5. Crear Directorio de Logs

```bash
mkdir -p logs
chmod 755 logs
```

### 6. Probar la Configuración

1. Ve a `checkout.php`
2. Completa una dirección de entrega
3. Verifica que aparezcan las opciones de envío
4. Revisa los logs en `logs/shipping.log`

## 🔧 Configuración de Webhooks

### Uber Direct Webhook Events:
- `deliveries.delivery_status` - Cambios de estado
- `deliveries.courier_update` - Actualizaciones del repartidor

### Andreani Webhook Events:
- `estado_actualizado` - Cambios de estado
- `entrega_realizada` - Entrega completada
- `excepcion` - Problemas de entrega

## 📊 URLs de Testing

### Sandbox/Testing:
- **Uber Direct:** `https://sandbox-api.uber.com`
- **Andreani:** `https://sandbox.andreani.com`

### Producción:
- **Uber Direct:** `https://api.uber.com`
- **Andreani:** `https://api.andreani.com`

## 🧪 Pruebas

### 1. Test de Cotización - Desde Posadas, Misiones
```bash
# Test envío a Buenos Aires
curl -X POST http://localhost/proyecto/api/shipping_quotes.php \
  -H "Content-Type: application/json" \
  -d '{
    "address": {
      "street": "Av. Corrientes 1234",
      "city": "CABA",
      "state": "Buenos Aires",
      "postal_code": "1043"
    },
    "items": [
      {"name": "Remera", "price": 5999, "quantity": 1}
    ]
  }'

# Test envío local en Posadas
curl -X POST http://localhost/proyecto/api/shipping_quotes.php \
  -H "Content-Type: application/json" \
  -d '{
    "address": {
      "street": "Av. Quaranta 2550",
      "city": "Posadas",
      "state": "Misiones",
      "postal_code": "3300"
    },
    "items": [
      {"name": "Remera", "price": 5999, "quantity": 1}
    ]
  }'
```

### 2. Test de Tracking
- Ve a: `http://localhost/proyecto/tracking.php?tracking=TEST123&provider=uber_direct`

## 🔍 Debugging

### Logs de Envío
```bash
tail -f logs/shipping.log
```

### Validar Configuración
```php
// En tu código PHP
$shippingManager = new ShippingManager();
$errors = $shippingManager->validateConfiguration();
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "Error: $error\n";
    }
}
```

## 📋 Checklist de Configuración

- [ ] Archivo `.env` configurado con credenciales reales
- [ ] Dirección de pickup actualizada en `config/shipping_apis.php`
- [ ] Webhooks configurados en ambas APIs
- [ ] Directorio `logs/` creado con permisos correctos
- [ ] Pruebas de cotización funcionando
- [ ] Página de tracking respondiendo correctamente

## 🚨 Problemas Comunes

### Error: "Authentication failed"
- Verifica que las credenciales sean correctas
- Asegúrate de usar el entorno correcto (sandbox vs producción)

### Error: "No quotes available"
- Verifica que la dirección esté en el área de cobertura
- Para Uber Direct: solo CABA/GBA
- Para Andreani: todo Argentina

### Error: "Webhook signature invalid"
- Verifica que `WEBHOOK_SECRET` sea el mismo en ambos lados
- Asegúrate de que el webhook esté configurado correctamente en la API

### Error: "Address validation failed"
- Formato de código postal: debe ser válido para Argentina
- Todos los campos de dirección son requeridos

## 📞 Soporte

- **Uber Direct:** [developer.uber.com/support](https://developer.uber.com/support)
- **Andreani:** Contacta a tu representante comercial

## 🔐 Seguridad

- Nunca commitees el archivo `.env` al repositorio
- Usa HTTPS en producción para los webhooks
- Mantén el `WEBHOOK_SECRET` seguro y único
- Rota las credenciales regularmente

## 📈 Monitoreo

### Métricas a Monitorear:
- Tiempo de respuesta de APIs
- Tasa de éxito de cotizaciones
- Errores de autenticación
- Webhooks fallidos

### Dashboard Recomendado:
- Total de envíos por día
- Distribución por proveedor (Uber vs Andreani vs Pickup)
- Tiempo promedio de entrega
- Costo promedio de envío

---

**¡Listo!** Tu sistema de envíos está configurado y funcionando. 🎉