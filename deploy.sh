#!/bin/bash

# Script de deployment para Google App Engine
PROJECT_ID="proyecto-remeras-app"  # Cambiar por tu project ID

echo "🚀 Desplegando aplicación a Google App Engine..."

# 1. Verificar que estamos en el directorio correcto
if [ ! -f "app.yaml" ]; then
    echo "❌ Error: app.yaml no encontrado. Asegúrate de estar en el directorio del proyecto."
    exit 1
fi

# 2. Verificar que gcloud está configurado
if ! gcloud auth list --filter=status:ACTIVE --format="value(account)" | grep -q "@"; then
    echo "❌ Error: No estás autenticado en gcloud. Ejecuta: gcloud auth login"
    exit 1
fi

# 3. Configurar proyecto
echo "📋 Configurando proyecto..."
gcloud config set project $PROJECT_ID

# 4. Verificar que App Engine está habilitado
echo "🔧 Verificando App Engine..."
if ! gcloud app describe --project=$PROJECT_ID >/dev/null 2>&1; then
    echo "🆕 App Engine no inicializado. Inicializando..."
    gcloud app create --region=us-central --project=$PROJECT_ID
fi

# 5. Crear copia de seguridad de la configuración actual
echo "💾 Creando backup de configuración..."
cp app.yaml app.yaml.backup

# 6. Deployment
echo "📤 Desplegando aplicación..."
gcloud app deploy app.yaml --project=$PROJECT_ID --quiet

# 7. Verificar deployment
if [ $? -eq 0 ]; then
    echo "✅ Deployment exitoso!"
    echo ""
    echo "🌐 Tu aplicación está disponible en:"
    gcloud app browse --project=$PROJECT_ID
    echo ""
    echo "📊 Para ver logs:"
    echo "gcloud app logs tail -s default --project=$PROJECT_ID"
    echo ""
    echo "📈 Para ver métricas:"
    echo "https://console.cloud.google.com/appengine/services?project=$PROJECT_ID"
else
    echo "❌ Error durante el deployment"
    exit 1
fi