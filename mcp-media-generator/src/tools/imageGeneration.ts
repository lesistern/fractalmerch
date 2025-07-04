import axios from 'axios';
import fs from 'fs-extra';
import path from 'path';
import { ImageGenerationInput, MediaGenerationResponse } from '../types/index.js';

// Mock API endpoint - replace with actual service
const OPENAI_API_URL = 'https://api.openai.com/v1/images/generations';
const STABILITY_API_URL = 'https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image';

export class ImageGenerator {
  private apiKey: string;
  private outputDir: string;

  constructor() {
    this.apiKey = process.env.OPENAI_API_KEY || process.env.STABILITY_API_KEY || '';
    this.outputDir = process.env.MCP_OUTPUT_DIR || './generated-media';
    this.ensureOutputDir();
  }

  private async ensureOutputDir(): Promise<void> {
    await fs.ensureDir(this.outputDir);
  }

  async generateWithOpenAI(params: ImageGenerationInput): Promise<MediaGenerationResponse> {
    try {
      if (!this.apiKey) {
        return {
          success: false,
          error: 'OpenAI API key not configured. Set OPENAI_API_KEY environment variable.'
        };
      }

      const response = await axios.post(
        OPENAI_API_URL,
        {
          prompt: params.prompt,
          n: 1,
          size: params.size || "1024x1024",
          quality: params.quality || "standard",
          response_format: "url"
        },
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`,
            'Content-Type': 'application/json'
          }
        }
      );

      const imageUrl = response.data.data[0].url;
      const filename = `image_${Date.now()}.png`;
      const filepath = path.join(this.outputDir, filename);

      // Download and save the image
      const imageResponse = await axios.get(imageUrl, { responseType: 'stream' });
      const writer = fs.createWriteStream(filepath);
      imageResponse.data.pipe(writer);

      await new Promise<void>((resolve, reject) => {
        writer.on('finish', () => resolve());
        writer.on('error', reject);
      });

      return {
        success: true,
        url: imageUrl,
        filename: filepath,
        metadata: {
          width: parseInt(params.size?.split('x')[0] || '1024'),
          height: parseInt(params.size?.split('x')[1] || '1024'),
          format: 'png'
        }
      };
    } catch (error: any) {
      return {
        success: false,
        error: `OpenAI API error: ${error.response?.data?.error?.message || error.message}`
      };
    }
  }

  async generateWithStability(params: ImageGenerationInput): Promise<MediaGenerationResponse> {
    try {
      if (!this.apiKey) {
        return {
          success: false,
          error: 'Stability AI API key not configured. Set STABILITY_API_KEY environment variable.'
        };
      }

      const [width, height] = (params.size || "1024x1024").split('x').map(Number);

      const response = await axios.post(
        STABILITY_API_URL,
        {
          text_prompts: [
            {
              text: params.prompt,
              weight: 1
            }
          ],
          cfg_scale: 7,
          height: height,
          width: width,
          samples: 1,
          steps: 30
        },
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        }
      );

      const imageData = response.data.artifacts[0].base64;
      const filename = `stability_image_${Date.now()}.png`;
      const filepath = path.join(this.outputDir, filename);

      // Save base64 image
      await fs.writeFile(filepath, imageData, 'base64');

      return {
        success: true,
        filename: filepath,
        metadata: {
          width: width,
          height: height,
          format: 'png'
        }
      };
    } catch (error: any) {
      return {
        success: false,
        error: `Stability AI error: ${error.response?.data?.message || error.message}`
      };
    }
  }

  async generateMockImage(params: ImageGenerationInput): Promise<MediaGenerationResponse> {
    // Mock implementation for testing without API keys
    const filename = `mock_image_${Date.now()}.txt`;
    const filepath = path.join(this.outputDir, filename);
    
    const mockContent = `Mock Image Generation
Prompt: ${params.prompt}
Style: ${params.style || 'default'}
Size: ${params.size || '1024x1024'}
Quality: ${params.quality || 'standard'}
Generated at: ${new Date().toISOString()}`;

    await fs.writeFile(filepath, mockContent);

    return {
      success: true,
      filename: filepath,
      metadata: {
        width: parseInt(params.size?.split('x')[0] || '1024'),
        height: parseInt(params.size?.split('x')[1] || '1024'),
        format: 'txt'
      }
    };
  }

  async generate(params: ImageGenerationInput): Promise<MediaGenerationResponse> {
    // Try different providers based on available API keys
    if (process.env.OPENAI_API_KEY) {
      return this.generateWithOpenAI(params);
    } else if (process.env.STABILITY_API_KEY) {
      return this.generateWithStability(params);
    } else {
      // Fallback to mock for testing
      return this.generateMockImage(params);
    }
  }
}