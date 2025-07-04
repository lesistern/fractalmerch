#!/bin/bash

echo "🔄 Sincronizando archivos con XAMPP..."

# Verificar que XAMPP existe
if [ ! -d "/mnt/c/xampp/htdocs/proyecto/" ]; then
    echo "❌ Error: No se encuentra /mnt/c/xampp/htdocs/proyecto/"
    echo "Verifica que XAMPP esté instalado y que la carpeta proyecto exista"
    exit 1
fi

# Sincronizar todos los archivos
cp -r /home/lesistern/proyecto/* /mnt/c/xampp/htdocs/proyecto/ 2>/dev/null
cp -r /home/lesistern/proyecto/.[^.]* /mnt/c/xampp/htdocs/proyecto/ 2>/dev/null || true

echo "✅ Sincronización completa!"
echo ""
echo "📍 Archivos actualizados en:"
echo "   /mnt/c/xampp/htdocs/proyecto/"
echo ""
echo "🌐 Ahora puedes acceder desde:"
echo "   http://192.168.0.145/proyecto/test.php"
echo "   http://192.168.0.145/proyecto/debug.php"
echo "   http://192.168.0.145/proyecto/"