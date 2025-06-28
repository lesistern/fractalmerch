# Crear Archivo de Configuración Claude Desktop

## 🔍 El archivo no existe? ¡Vamos a crearlo!

### Método 1: Desde Claude Desktop App
1. Abrir **Claude Desktop**
2. Presionar **Ctrl + ,** (Windows) para abrir Settings
3. Ir a la pestaña **Developer**
4. Hacer clic en **"Edit Config"**
5. Se abrirá el archivo (o se creará si no existe)

### Método 2: Crear manualmente
1. Presionar **Windows + R**
2. Escribir: `%APPDATA%\Claude`
3. Si la carpeta no existe, crearla
4. Crear archivo: `claude_desktop_config.json`

### Método 3: Navegador de archivos
1. Abrir **Explorador de Windows**
2. En la barra de direcciones escribir: `%APPDATA%\Claude`
3. Crear el archivo `claude_desktop_config.json`

## 📝 Contenido del archivo

Una vez creado, pegar este contenido:

```json
{
  "mcpServers": {
    "media-generator": {
      "command": "node",
      "args": ["C:\\xampp\\htdocs\\proyecto\\mcp-media-generator\\build\\index.js"],
      "env": {
        "MCP_OUTPUT_DIR": "C:\\xampp\\htdocs\\proyecto\\mcp-media-generator\\generated-media"
      }
    }
  }
}
```

## ⚠️ Ubicaciones alternativas

Si no funciona, también puede estar en:
- `C:\Users\[TU_USUARIO]\AppData\Roaming\Claude\claude_desktop_config.json`
- `C:\Users\[TU_USUARIO]\.config\Claude\claude_desktop_config.json`

## 🔧 Verificar configuración

Después de crear el archivo:
1. **Reiniciar Claude Desktop completamente**
2. Probar con: `"Genera una imagen de un paisaje"`
3. Los archivos aparecerán en: `C:\xampp\htdocs\proyecto\mcp-media-generator\generated-media\`

¡Listo para generar imágenes y videos! 🚀