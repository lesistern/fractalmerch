#!/bin/bash

if [ -z "$1" ]; then
    echo "❌ Error: Necesitas proporcionar la IP de red"
    echo "Uso: $0 192.168.1.100"
    echo ""
    echo "Para obtener tu IP, ejecuta en Windows:"
    echo "ipconfig | findstr IPv4"
    exit 1
fi

LAN_IP=$1

echo "🌐 Configurando acceso LAN con IP: $LAN_IP"

# Actualizar browser-sync config
echo "📝 Actualizando bs-config.js..."
sed -i "s/proxy: \".*\",/proxy: \"$LAN_IP\/proyecto\",/" bs-config.js

# Actualizar package.json
echo "📝 Actualizando package.json..."
sed -i "s/--proxy '[^']*'/--proxy '$LAN_IP\/proyecto'/" package.json

# Actualizar configuración para LAN
cat > bs-config.js << EOF
module.exports = {
    proxy: "$LAN_IP/proyecto",
    host: "0.0.0.0",
    port: 3000,
    files: [
        "**/*.php",
        "**/*.css", 
        "**/*.js",
        "**/*.html"
    ],
    ignore: [
        "node_modules",
        "*.map"
    ],
    reloadDelay: 300,
    injectChanges: true,
    notify: {
        styles: [
            "display: none; ",
            "padding: 15px;",
            "font-family: sans-serif;",
            "position: fixed;",
            "font-size: 0.9em;",
            "z-index: 9999;",
            "bottom: 0px;",
            "right: 0px;",
            "border-bottom-left-radius: 5px;",
            "background-color: #1B2032;",
            "opacity: 0.4;",
            "margin: 0;",
            "color: white;",
            "text-align: center"
        ]
    },
    open: false,
    logLevel: "info",
    logPrefix: "🚀 Proyecto LAN",
    browser: "default",
    cors: true,
    xip: false,
    hostnameSuffix: false,
    reloadOnRestart: true,
    ghostMode: {
        clicks: true,
        forms: true,
        scroll: true
    },
    watchEvents: [
        'add',
        'change',
        'unlink',
        'addDir',
        'unlinkDir'
    ]
};
EOF

echo "✅ Configuración actualizada!"
echo ""
echo "🌍 URLs de acceso:"
echo "   Local: http://localhost/proyecto/"
echo "   LAN:   http://$LAN_IP/proyecto/"
echo "   Auto-reload: http://$LAN_IP:3000/"
echo ""
echo "📱 Desde otros dispositivos de la red:"
echo "   http://$LAN_IP/proyecto/"
echo "   http://$LAN_IP:3000/ (con auto-reload)"
echo ""
echo "⚠️  RECUERDA:"
echo "1. Configurar Apache para escuchar en 0.0.0.0:80"
echo "2. Abrir puertos en el Firewall de Windows"
echo "3. Reiniciar Apache después de cambios"
echo ""
echo "🚀 Ahora ejecuta: npm run dev"