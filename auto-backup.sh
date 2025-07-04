#!/bin/bash

# Auto-backup script for Claude Code
# This script runs the Windows backup.bat from WSL environment

echo "🔄 Iniciando backup automático..."

# Run the Windows batch file from WSL
cmd.exe /c "cd /d C:\\xampp\\htdocs\\proyecto && backup.bat"

# Check if backup was successful
if [ $? -eq 0 ]; then
    echo "✅ Backup completado exitosamente"
else
    echo "❌ Error durante el backup"
fi