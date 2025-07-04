# Instalación y Configuración del MCP Media Generator

## 🚀 Instalación Rápida

### 1. Configurar Claude Desktop

Edita el archivo de configuración de Claude Desktop:

**Windows**: `%APPDATA%/Claude/claude_desktop_config.json`
**macOS**: `~/Library/Application Support/Claude/claude_desktop_config.json`  
**Linux**: `~/.config/Claude/claude_desktop_config.json`

```json
{
  "mcpServers": {
    "media-generator": {
      "command": "node",
      "args": ["/home/lesistern/mcp-media-generator/build/index.js"],
      "env": {
        "MCP_OUTPUT_DIR": "/home/lesistern/mcp-media-generator/generated-media"
      }
    }
  }
}
```

### 2. Configurar APIs (Opcional)

Para usar APIs reales en lugar del modo mock, añade las claves:

```json
{
  "mcpServers": {
    "media-generator": {
      "command": "node",
      "args": ["/home/lesistern/mcp-media-generator/build/index.js"],
      "env": {
        "OPENAI_API_KEY": "sk-tu-clave-aqui",
        "STABILITY_API_KEY": "sk-tu-clave-aqui",
        "RUNWAY_API_KEY": "tu-clave-aqui",
        "PIKA_API_KEY": "tu-clave-aqui",
        "MCP_OUTPUT_DIR": "/home/lesistern/mcp-media-generator/generated-media"
      }
    }
  }
}
```

### 3. Reiniciar Claude Desktop

Cierra y abre Claude Desktop para cargar la nueva configuración.

## 🧪 Prueba Inmediata

Una vez configurado, puedes usar estos comandos en Claude:

```
Genera una imagen de "un gato astronauta flotando en el espacio" con estilo cinematográfico

Genera un video de "ondas del océano al atardecer" de 5 segundos en 720p
```

## 📁 Archivos Generados

Los archivos se guardan en: `/home/lesistern/mcp-media-generator/generated-media/`

## ⚡ Modo Mock vs APIs Reales

- **Sin API keys**: Genera archivos .txt mock para testing
- **Con API keys**: Genera imágenes/videos reales usando IA

¡El MCP está listo para usar!