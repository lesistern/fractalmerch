#!/bin/bash

echo "🌐 Configurando acceso LAN para el proyecto..."
echo ""

# Obtener IP de Windows
echo "📍 Obteniendo IP de la red local..."
WINDOWS_IP=$(ip route show | grep -i default | awk '{print $3}')
echo "IP detectada de Windows: $WINDOWS_IP"

# Preguntar por la IP real de la red
echo ""
echo "⚠️  IMPORTANTE: Necesitas obtener tu IP real de red desde Windows"
echo "Ejecuta este comando en PowerShell o CMD desde Windows:"
echo "ipconfig | findstr IPv4"
echo ""
echo "Busca algo como:"
echo "   IPv4 Address. . . . . . . . . . . : 192.168.1.100"
echo "   IPv4 Address. . . . . . . . . . . : 10.0.0.50"
echo ""

# Crear archivo de configuración temporal
cat > lan-config.txt << EOF
# Configuración LAN del proyecto
# IP de Windows detectada: $WINDOWS_IP
# 
# PASOS PARA HABILITAR LAN:
# 
# 1. Obtener IP de red real desde Windows:
#    Ejecuta: ipconfig | findstr IPv4
#    Anota la IP que NO sea 127.0.0.1
#
# 2. Configurar Apache en XAMPP:
#    - Abre XAMPP Control Panel
#    - Click en "Config" junto a Apache
#    - Selecciona "Apache (httpd.conf)"
#    - Busca: Listen 80
#    - Cambia por: Listen 0.0.0.0:80
#    - Guarda y reinicia Apache
#
# 3. Configurar Firewall de Windows:
#    - Busca "Firewall de Windows"
#    - Clic en "Permitir una aplicación"
#    - Busca "Apache HTTP Server"
#    - Marca "Private" y "Public"
#    - Si no existe, clic "Permitir otra aplicación"
#    - Busca: C:\xampp\apache\bin\httpd.exe
#
# 4. Actualizar browser-sync:
#    - Ejecuta: ./update-lan-ip.sh TU_IP_REAL
#    - Ejemplo: ./update-lan-ip.sh 192.168.1.100
#
# 5. Acceder desde otros dispositivos:
#    - http://TU_IP_REAL/proyecto/
#    - http://TU_IP_REAL:3000/ (con auto-reload)
EOF

echo "📋 Instrucciones guardadas en: lan-config.txt"
echo ""
echo "🚀 Siguiente paso: Ejecuta 'ipconfig' en Windows y luego:"
echo "   ./update-lan-ip.sh TU_IP_REAL"