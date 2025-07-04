#!/usr/bin/env node

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ErrorCode,
  ListToolsRequestSchema,
  McpError,
} from '@modelcontextprotocol/sdk/types.js';

import { ImageGenerator } from './tools/imageGeneration.js';
import { VideoGenerator } from './tools/videoGeneration.js';
import { 
  imageGenerationSchema, 
  videoGenerationSchema, 
  imageEnhancementSchema 
} from './types/index.js';

// Initialize generators
const imageGenerator = new ImageGenerator();
const videoGenerator = new VideoGenerator();

// Create server instance
const server = new Server(
  {
    name: 'mcp-media-generator',
    version: '1.0.0',
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

// List available tools
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      {
        name: 'generate_image',
        description: 'Generate images from text prompts using AI models like DALL-E or Stable Diffusion',
        inputSchema: {
          type: 'object',
          properties: {
            prompt: {
              type: 'string',
              description: 'Text description of the image to generate'
            },
            style: {
              type: 'string',
              enum: ['realistic', 'artistic', 'cartoon', 'anime', 'photographic', 'digital-art', 'cinematic', 'fantasy'],
              description: 'Style of the image'
            },
            aspect_ratio: {
              type: 'string',
              enum: ['1:1', '16:9', '9:16', '4:3', '3:4'],
              description: 'Aspect ratio of the image'
            },
            quality: {
              type: 'string',
              enum: ['standard', 'hd'],
              description: 'Quality of the image'
            },
            size: {
              type: 'string',
              enum: ['256x256', '512x512', '1024x1024', '1792x1024', '1024x1792'],
              description: 'Size of the image'
            }
          },
          required: ['prompt']
        }
      },
      {
        name: 'generate_video',
        description: 'Generate videos from text prompts using AI models like Runway or Pika',
        inputSchema: {
          type: 'object',
          properties: {
            prompt: {
              type: 'string',
              description: 'Text description of the video to generate'
            },
            duration: {
              type: 'number',
              minimum: 1,
              maximum: 30,
              description: 'Duration in seconds (1-30)'
            },
            fps: {
              type: 'number',
              minimum: 12,
              maximum: 60,
              description: 'Frames per second (12-60)'
            },
            resolution: {
              type: 'string',
              enum: ['480p', '720p', '1080p'],
              description: 'Video resolution'
            },
            style: {
              type: 'string',
              enum: ['realistic', 'animated', 'cinematic', 'documentary', 'artistic'],
              description: 'Style of the video'
            }
          },
          required: ['prompt']
        }
      },
      {
        name: 'enhance_image',
        description: 'Enhance existing images with AI processing (upscale, denoise, colorize, etc.)',
        inputSchema: {
          type: 'object',
          properties: {
            image_url: {
              type: 'string',
              format: 'uri',
              description: 'URL of the image to enhance'
            },
            enhancement_type: {
              type: 'string',
              enum: ['upscale', 'denoise', 'colorize', 'restore', 'super-resolution'],
              description: 'Type of enhancement to apply'
            },
            strength: {
              type: 'number',
              minimum: 0.1,
              maximum: 1.0,
              description: 'Enhancement strength (0.1-1.0)'
            }
          },
          required: ['image_url', 'enhancement_type']
        }
      },
      {
        name: 'get_generation_status',
        description: 'Check the status of ongoing media generation tasks',
        inputSchema: {
          type: 'object',
          properties: {
            task_id: {
              type: 'string',
              description: 'ID of the generation task to check'
            }
          },
          required: ['task_id']
        }
      }
    ]
  };
});

// Handle tool calls
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  try {
    const { name, arguments: args } = request.params;

    switch (name) {
      case 'generate_image': {
        // Validate input
        const validatedArgs = imageGenerationSchema.parse(args);
        
        // Generate image
        const result = await imageGenerator.generate(validatedArgs);
        
        if (result.success) {
          return {
            content: [
              {
                type: 'text',
                text: `Image generated successfully!\n\nPrompt: ${validatedArgs.prompt}\nFile: ${result.filename}\nStyle: ${validatedArgs.style || 'default'}\nSize: ${validatedArgs.size || '1024x1024'}\n\n${result.url ? `URL: ${result.url}` : ''}`
              }
            ]
          };
        } else {
          throw new McpError(ErrorCode.InternalError, result.error || 'Image generation failed');
        }
      }

      case 'generate_video': {
        // Validate input
        const validatedArgs = videoGenerationSchema.parse(args);
        
        // Generate video
        const result = await videoGenerator.generate(validatedArgs);
        
        if (result.success) {
          return {
            content: [
              {
                type: 'text',
                text: `Video generated successfully!\n\nPrompt: ${validatedArgs.prompt}\nFile: ${result.filename}\nDuration: ${validatedArgs.duration || 5}s\nResolution: ${validatedArgs.resolution || '720p'}\nStyle: ${validatedArgs.style || 'realistic'}\n\n${result.url ? `URL: ${result.url}` : ''}`
              }
            ]
          };
        } else {
          throw new McpError(ErrorCode.InternalError, result.error || 'Video generation failed');
        }
      }

      case 'enhance_image': {
        // Validate input
        const validatedArgs = imageEnhancementSchema.parse(args);
        
        return {
          content: [
            {
              type: 'text',
              text: `Image enhancement requested!\n\nImage URL: ${validatedArgs.image_url}\nEnhancement: ${validatedArgs.enhancement_type}\nStrength: ${validatedArgs.strength || 0.5}\n\nNote: Image enhancement is not yet implemented in this demo version.`
            }
          ]
        };
      }

      case 'get_generation_status': {
        const { task_id } = args as { task_id: string };
        
        return {
          content: [
            {
              type: 'text',
              text: `Status check for task: ${task_id}\n\nNote: Status checking is not yet implemented in this demo version. Most generations are synchronous.`
            }
          ]
        };
      }

      default:
        throw new McpError(ErrorCode.MethodNotFound, `Unknown tool: ${name}`);
    }
  } catch (error) {
    if (error instanceof McpError) {
      throw error;
    }
    
    // Handle validation errors
    if (error instanceof Error && error.name === 'ZodError') {
      throw new McpError(ErrorCode.InvalidParams, `Invalid parameters: ${error.message}`);
    }
    
    throw new McpError(ErrorCode.InternalError, `Tool execution failed: ${error instanceof Error ? error.message : String(error)}`);
  }
});

// Error handling
process.on('SIGINT', async () => {
  await server.close();
  process.exit(0);
});

// Start the server
async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  
  // Send success message to stderr so it doesn't interfere with MCP communication
  console.error('MCP Media Generator server started successfully!');
  console.error('Available tools: generate_image, generate_video, enhance_image, get_generation_status');
  console.error('Configure API keys via environment variables:');
  console.error('- OPENAI_API_KEY for DALL-E image generation');
  console.error('- STABILITY_API_KEY for Stable Diffusion');
  console.error('- RUNWAY_API_KEY for Runway video generation');
  console.error('- PIKA_API_KEY for Pika video generation');
  console.error('- MCP_OUTPUT_DIR for custom output directory (default: ./generated-media)');
}

main().catch((error) => {
  console.error('Failed to start server:', error);
  process.exit(1);
});