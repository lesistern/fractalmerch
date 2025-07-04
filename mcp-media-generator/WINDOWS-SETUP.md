# Configuración MCP Media Generator en Windows

## 📍 Ubicación del Proyecto
```
C:\xampp\htdocs\proyecto\mcp-media-generator\
```

## 🔧 Configuración para Claude Desktop

### 1. Abrir archivo de configuración
**Ubicación**: `%APPDATA%\Claude\claude_desktop_config.json`

**Ruta completa**: `C:\Users\[TU_USUARIO]\AppData\Roaming\Claude\claude_desktop_config.json`

### 2. Añadir configuración del MCP

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

### 3. Con APIs reales (opcional)

```json
{
  "mcpServers": {
    "media-generator": {
      "command": "node",
      "args": ["C:\\xampp\\htdocs\\proyecto\\mcp-media-generator\\build\\index.js"],
      "env": {
        "OPENAI_API_KEY": "sk-tu-clave-openai",
        "STABILITY_API_KEY": "sk-tu-clave-stability",
        "RUNWAY_API_KEY": "tu-clave-runway",
        "PIKA_API_KEY": "tu-clave-pika",
        "MCP_OUTPUT_DIR": "C:\\xampp\\htdocs\\proyecto\\mcp-media-generator\\generated-media"
      }
    }
  }
}
```

## ⚡ Comandos de Prueba

Una vez configurado en Claude Desktop:

```
Genera una imagen de "un logo moderno para una empresa tech" con estilo digital-art

Genera un video de "lluvia cayendo en una ventana" de 5 segundos en 720p con estilo cinematográfico
```

## 📁 Archivos Generados

Se guardan en: `C:\xampp\htdocs\proyecto\mcp-media-generator\generated-media\`

## 🔧 Solución de Problemas

### Si no funciona:
1. Verificar que Node.js esté instalado en Windows
2. Comprobar que la ruta en claude_desktop_config.json sea correcta
3. Reiniciar Claude Desktop completamente
4. Verificar que el archivo build/index.js existe

### Verificar instalación:
Desde CMD en Windows:
```cmd
cd C:\xampp\htdocs\proyecto\mcp-media-generator
node build/index.js
```

¡Listo para usar! 🚀