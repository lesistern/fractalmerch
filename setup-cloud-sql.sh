#!/bin/bash

# Script para configurar Cloud SQL para el proyecto
# Ejecutar después de crear el proyecto en Google Cloud Platform

# Variables (actualizar con tus valores)
PROJECT_ID="proyecto-remeras-app"
REGION="us-central1"
INSTANCE_NAME="proyecto-db"
DATABASE_NAME="proyecto_web"
ROOT_PASSWORD="your-secure-password-here"

echo "🚀 Configurando Cloud SQL para el proyecto..."
echo "Project ID: $PROJECT_ID"
echo "Region: $REGION"
echo "Instance: $INSTANCE_NAME"

# 1. Crear instancia de Cloud SQL
echo "📦 Creando instancia de Cloud SQL..."
gcloud sql instances create $INSTANCE_NAME \
    --project=$PROJECT_ID \
    --database-version=MYSQL_8_0 \
    --tier=db-f1-micro \
    --region=$REGION \
    --storage-size=10GB \
    --storage-type=SSD \
    --backup-start-time=03:00 \
    --enable-bin-log \
    --maintenance-release-channel=production \
    --maintenance-window-day=SUN \
    --maintenance-window-hour=4 \
    --root-password=$ROOT_PASSWORD

# 2. Crear base de datos
echo "🗄️ Creando base de datos..."
gcloud sql databases create $DATABASE_NAME \
    --instance=$INSTANCE_NAME \
    --project=$PROJECT_ID

# 3. Obtener connection name
CONNECTION_NAME=$(gcloud sql instances describe $INSTANCE_NAME --project=$PROJECT_ID --format="value(connectionName)")
echo "🔗 Connection Name: $CONNECTION_NAME"

# 4. Actualizar app.yaml con los valores correctos
echo "📝 Actualizando app.yaml..."
sed -i.bak "s/PROJECT_ID:REGION:INSTANCE_NAME/$CONNECTION_NAME/g" app.yaml
sed -i.bak "s/your-secure-password/$ROOT_PASSWORD/g" app.yaml

# 5. Importar datos iniciales (opcional)
echo "📥 ¿Quieres importar la base de datos actual? (y/n)"
read -r import_db
if [ "$import_db" = "y" ]; then
    echo "Importando database.sql..."
    gcloud sql import sql $INSTANCE_NAME gs://YOUR_BUCKET/database.sql \
        --database=$DATABASE_NAME \
        --project=$PROJECT_ID
    echo "💡 Nota: Necesitas subir database.sql a un bucket de Cloud Storage primero"
fi

echo "✅ Configuración de Cloud SQL completada!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Actualizar las variables en app.yaml:"
echo "   - DB_HOST: /cloudsql/$CONNECTION_NAME"
echo "   - DB_PASSWORD: $ROOT_PASSWORD"
echo ""
echo "2. Desplegar la aplicación:"
echo "   gcloud app deploy"
echo ""
echo "3. Ver la aplicación:"
echo "   gcloud app browse"