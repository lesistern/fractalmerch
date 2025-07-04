#!/bin/bash

echo "🚀 Configurando entorno de desarrollo..."

# Verificar que XAMPP esté corriendo
if ! curl -s http://localhost/proyecto/ > /dev/null; then
    echo "⚠️  XAMPP no está corriendo o el proyecto no está accesible en http://localhost/proyecto/"
    echo "Por favor:"
    echo "1. Inicia XAMPP (Apache y MySQL)"
    echo "2. Asegúrate que el proyecto esté en htdocs/proyecto/"
    echo "3. Vuelve a ejecutar este script"
    exit 1
fi

echo "✅ XAMPP está corriendo correctamente"

# Verificar base de datos
echo "🗄️  Verificando base de datos..."
if ! mysql -u root -e "USE proyecto_web;" 2>/dev/null; then
    echo "⚠️  Base de datos 'proyecto_web' no encontrada"
    echo "Importando base de datos..."
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS proyecto_web;"
    mysql -u root proyecto_web < database.sql
    echo "✅ Base de datos importada"
else
    echo "✅ Base de datos OK"
fi

echo "🎉 ¡Todo listo! Ejecuta 'npm run dev' para iniciar el desarrollo"
echo ""
echo "📋 Comandos disponibles:"
echo "  npm run dev    - Inicia servidor de desarrollo con auto-reload"
echo "  npm run serve  - Solo servidor browser-sync"
echo "  npm run watch  - Solo monitoreo de archivos"
echo ""
echo "🌐 Tu sitio estará disponible en:"
echo "  http://localhost:3000 (con auto-reload)"
echo "  http://localhost/proyecto (XAMPP directo)"