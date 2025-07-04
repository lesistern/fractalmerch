# MCP Media Generator

Un servidor MCP (Model Context Protocol) para generar imágenes y videos usando IA.

## 🚀 Características

- **Generación de Imágenes**: Soporte para OpenAI DALL-E y Stability AI
- **Generación de Videos**: Integración con Runway ML y Pika Labs
- **Mejora de Imágenes**: Upscaling, denoising, colorización (próximamente)
- **Múltiples Estilos**: Realista, artístico, animado, cinematográfico
- **Formatos Flexibles**: Diferentes tamaños, resoluciones y duraciones

## 📦 Instalación

```bash
# Clonar el repositorio
git clone <repository-url>
cd mcp-media-generator

# Instalar dependencias
npm install

# Compilar el proyecto
npm run build
```

## 🔧 Configuración

### Variables de Entorno

Configura las siguientes variables de entorno según los servicios que quieras usar:

```bash
# Para generación de imágenes
export OPENAI_API_KEY="tu-openai-api-key"
export STABILITY_API_KEY="tu-stability-ai-api-key"

# Para generación de videos
export RUNWAY_API_KEY="tu-runway-api-key"
export PIKA_API_KEY="tu-pika-api-key"

# Directorio de salida (opcional)
export MCP_OUTPUT_DIR="./generated-media"
```

### Integración con Claude Desktop

Añade la configuración al archivo de configuración de Claude Desktop:

**Windows**: `%APPDATA%/Claude/claude_desktop_config.json`
**macOS**: `~/Library/Application Support/Claude/claude_desktop_config.json`
**Linux**: `~/.config/Claude/claude_desktop_config.json`

```json
{
  "mcpServers": {
    "media-generator": {
      "command": "node",
      "args": ["/ruta/completa/a/mcp-media-generator/build/index.js"],
      "env": {
        "OPENAI_API_KEY": "tu-openai-api-key",
        "STABILITY_API_KEY": "tu-stability-ai-api-key",
        "RUNWAY_API_KEY": "tu-runway-api-key",
        "PIKA_API_KEY": "tu-pika-api-key",
        "MCP_OUTPUT_DIR": "/ruta/a/directorio/salida"
      }
    }
  }
}
```

## 🛠️ Herramientas Disponibles

### 1. generate_image

Genera imágenes desde prompts de texto.

**Parámetros:**
- `prompt` (requerido): Descripción de la imagen
- `style`: realista, artístico, cartoon, anime, fotográfico, digital-art, cinematográfico, fantasy
- `size`: 256x256, 512x512, 1024x1024, 1792x1024, 1024x1792
- `quality`: standard, hd
- `aspect_ratio`: 1:1, 16:9, 9:16, 4:3, 3:4

**Ejemplo:**
```
Genera una imagen de "un gato astronauta flotando en el espacio" con estilo cinematográfico
```

### 2. generate_video

Genera videos desde prompts de texto.

**Parámetros:**
- `prompt` (requerido): Descripción del video
- `duration`: Duración en segundos (1-30)
- `fps`: Frames por segundo (12-60)
- `resolution`: 480p, 720p, 1080p
- `style`: realista, animado, cinematográfico, documental, artístico

**Ejemplo:**
```
Genera un video de "ondas del océano al atardecer" de 10 segundos en 1080p con estilo cinematográfico
```

### 3. enhance_image

Mejora imágenes existentes (próximamente).

**Parámetros:**
- `image_url` (requerido): URL de la imagen
- `enhancement_type`: upscale, denoise, colorize, restore, super-resolution
- `strength`: Intensidad del efecto (0.1-1.0)

### 4. get_generation_status

Verifica el estado de tareas de generación en curso.

**Parámetros:**
- `task_id` (requerido): ID de la tarea

## 🔄 Desarrollo

```bash
# Modo desarrollo (compilación automática)
npm run dev

# Ejecutar el servidor
npm start

# Inspeccionar el servidor MCP
npm run inspect
```

## 🧪 Testing

El servidor incluye implementaciones mock que funcionan sin APIs externas para testing:

```bash
# Ejecutar sin API keys para modo mock
node build/index.js
```

## 📁 Estructura del Proyecto

```
mcp-media-generator/
├── src/
│   ├── tools/
│   │   ├── imageGeneration.ts    # Lógica de generación de imágenes
│   │   └── videoGeneration.ts    # Lógica de generación de videos
│   ├── types/
│   │   └── index.ts             # Tipos y schemas de validación
│   └── index.ts                 # Servidor MCP principal
├── build/                       # Archivos compilados
├── generated-media/             # Archivos generados (por defecto)
├── package.json
├── tsconfig.json
└── README.md
```

## 🔒 Seguridad

- Las API keys se configuran como variables de entorno
- Los archivos se guardan en un directorio configurable
- Validación de entrada con Zod schemas
- Manejo de errores robusto

## 🌐 APIs Soportadas

### Generación de Imágenes
- **OpenAI DALL-E**: Imágenes de alta calidad con prompt engineering avanzado
- **Stability AI**: Stable Diffusion con control fino de parámetros

### Generación de Videos
- **Runway ML**: Videos realistas y cinematográficos
- **Pika Labs**: Videos animados y estilizados

## 📈 Próximas Funcionalidades

- [ ] Mejora de imágenes con Real-ESRGAN
- [ ] Generación de música con AI
- [ ] Edición de videos con IA
- [ ] Integración con Midjourney
- [ ] Cache inteligente de resultados
- [ ] Webhooks para tareas largas
- [ ] UI web para gestión

## 🐛 Troubleshooting

### Error: "API key not configured"
- Verifica que las variables de entorno estén configuradas correctamente
- Asegúrate de que las API keys sean válidas y tengan los permisos necesarios

### Error: "Module not found"
- Ejecuta `npm run build` para compilar el proyecto
- Verifica que todas las dependencias estén instaladas

### Videos/Imágenes no se generan
- Revisa los logs del servidor para errores específicos
- Verifica que tengas créditos/cuota disponible en las APIs
- Comprueba que el directorio de salida tenga permisos de escritura

## 📄 Licencia

MIT License - ver el archivo LICENSE para más detalles.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Si encuentras problemas o tienes preguntas:

1. Revisa la documentación y troubleshooting
2. Busca en los issues existentes
3. Crea un nuevo issue con detalles específicos

---

**Desarrollado con ❤️ para la comunidad MCP**