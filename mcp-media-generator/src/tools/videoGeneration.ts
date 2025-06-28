import axios from 'axios';
import fs from 'fs-extra';
import path from 'path';
import { VideoGenerationInput, MediaGenerationResponse } from '../types/index.js';

// Video generation API endpoints
const RUNWAY_API_URL = 'https://api.runwayml.com/v1/generate';
const PIKA_API_URL = 'https://api.pika.art/v1/videos';

export class VideoGenerator {
  private apiKey: string;
  private outputDir: string;

  constructor() {
    this.apiKey = process.env.RUNWAY_API_KEY || process.env.PIKA_API_KEY || '';
    this.outputDir = process.env.MCP_OUTPUT_DIR || './generated-media';
    this.ensureOutputDir();
  }

  private async ensureOutputDir(): Promise<void> {
    await fs.ensureDir(this.outputDir);
  }

  async generateWithRunway(params: VideoGenerationInput): Promise<MediaGenerationResponse> {
    try {
      if (!this.apiKey) {
        return {
          success: false,
          error: 'Runway API key not configured. Set RUNWAY_API_KEY environment variable.'
        };
      }

      // Initiate video generation
      const response = await axios.post(
        RUNWAY_API_URL,
        {
          prompt: params.prompt,
          duration: params.duration || 5,
          resolution: params.resolution || "720p",
          style: params.style || "realistic",
          fps: params.fps || 24
        },
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`,
            'Content-Type': 'application/json'
          }
        }
      );

      const taskId = response.data.id;
      
      // Poll for completion (simplified - in real implementation, use webhooks)
      let attempts = 0;
      const maxAttempts = 30; // 5 minutes with 10s intervals
      
      while (attempts < maxAttempts) {
        const statusResponse = await axios.get(
          `${RUNWAY_API_URL}/${taskId}/status`,
          {
            headers: {
              'Authorization': `Bearer ${this.apiKey}`
            }
          }
        );

        if (statusResponse.data.status === 'completed') {
          const videoUrl = statusResponse.data.output.url;
          const filename = `runway_video_${Date.now()}.mp4`;
          const filepath = path.join(this.outputDir, filename);

          // Download video
          const videoResponse = await axios.get(videoUrl, { responseType: 'stream' });
          const writer = fs.createWriteStream(filepath);
          videoResponse.data.pipe(writer);

          await new Promise<void>((resolve, reject) => {
            writer.on('finish', () => resolve());
            writer.on('error', reject);
          });

          return {
            success: true,
            url: videoUrl,
            filename: filepath,
            metadata: {
              duration: params.duration || 5,
              format: 'mp4',
              width: this.getResolutionWidth(params.resolution || "720p"),
              height: this.getResolutionHeight(params.resolution || "720p")
            }
          };
        } else if (statusResponse.data.status === 'failed') {
          return {
            success: false,
            error: `Video generation failed: ${statusResponse.data.error}`
          };
        }

        // Wait 10 seconds before next check
        await new Promise(resolve => setTimeout(resolve, 10000));
        attempts++;
      }

      return {
        success: false,
        error: 'Video generation timed out'
      };
    } catch (error: any) {
      return {
        success: false,
        error: `Runway API error: ${error.response?.data?.message || error.message}`
      };
    }
  }

  async generateWithPika(params: VideoGenerationInput): Promise<MediaGenerationResponse> {
    try {
      if (!this.apiKey) {
        return {
          success: false,
          error: 'Pika API key not configured. Set PIKA_API_KEY environment variable.'
        };
      }

      const response = await axios.post(
        PIKA_API_URL,
        {
          prompt: params.prompt,
          duration: params.duration || 3,
          aspect_ratio: this.convertResolutionToAspectRatio(params.resolution || "720p"),
          motion: params.style === "animated" ? "high" : "medium",
          guidance_scale: 12,
          negative_prompt: "blurry, low quality, distorted"
        },
        {
          headers: {
            'Authorization': `Bearer ${this.apiKey}`,
            'Content-Type': 'application/json'
          }
        }
      );

      const taskId = response.data.id;
      
      // Simplified polling (similar to Runway)
      let attempts = 0;
      const maxAttempts = 20;
      
      while (attempts < maxAttempts) {
        const statusResponse = await axios.get(
          `${PIKA_API_URL}/${taskId}`,
          {
            headers: {
              'Authorization': `Bearer ${this.apiKey}`
            }
          }
        );

        if (statusResponse.data.status === 'succeeded') {
          const videoUrl = statusResponse.data.video.url;
          const filename = `pika_video_${Date.now()}.mp4`;
          const filepath = path.join(this.outputDir, filename);

          // Download video
          const videoResponse = await axios.get(videoUrl, { responseType: 'stream' });
          const writer = fs.createWriteStream(filepath);
          videoResponse.data.pipe(writer);

          await new Promise<void>((resolve, reject) => {
            writer.on('finish', () => resolve());
            writer.on('error', reject);
          });

          return {
            success: true,
            url: videoUrl,
            filename: filepath,
            metadata: {
              duration: params.duration || 3,
              format: 'mp4',
              width: this.getResolutionWidth(params.resolution || "720p"),
              height: this.getResolutionHeight(params.resolution || "720p")
            }
          };
        } else if (statusResponse.data.status === 'failed') {
          return {
            success: false,
            error: `Video generation failed: ${statusResponse.data.failure_reason}`
          };
        }

        await new Promise(resolve => setTimeout(resolve, 15000));
        attempts++;
      }

      return {
        success: false,
        error: 'Video generation timed out'
      };
    } catch (error: any) {
      return {
        success: false,
        error: `Pika API error: ${error.response?.data?.message || error.message}`
      };
    }
  }

  async generateMockVideo(params: VideoGenerationInput): Promise<MediaGenerationResponse> {
    // Mock implementation for testing without API keys
    const filename = `mock_video_${Date.now()}.txt`;
    const filepath = path.join(this.outputDir, filename);
    
    const mockContent = `Mock Video Generation
Prompt: ${params.prompt}
Duration: ${params.duration || 5} seconds
Resolution: ${params.resolution || '720p'}
FPS: ${params.fps || 24}
Style: ${params.style || 'realistic'}
Generated at: ${new Date().toISOString()}

This is a mock video file. In a real implementation, this would be an MP4 video file.`;

    await fs.writeFile(filepath, mockContent);

    return {
      success: true,
      filename: filepath,
      metadata: {
        duration: params.duration || 5,
        format: 'txt',
        width: this.getResolutionWidth(params.resolution || "720p"),
        height: this.getResolutionHeight(params.resolution || "720p")
      }
    };
  }

  private getResolutionWidth(resolution: string): number {
    const resolutionMap: { [key: string]: number } = {
      "480p": 854,
      "720p": 1280,
      "1080p": 1920
    };
    return resolutionMap[resolution] || 1280;
  }

  private getResolutionHeight(resolution: string): number {
    const resolutionMap: { [key: string]: number } = {
      "480p": 480,
      "720p": 720,
      "1080p": 1080
    };
    return resolutionMap[resolution] || 720;
  }

  private convertResolutionToAspectRatio(resolution: string): string {
    // Most video resolutions are 16:9
    return "16:9";
  }

  async generate(params: VideoGenerationInput): Promise<MediaGenerationResponse> {
    // Try different providers based on available API keys
    if (process.env.RUNWAY_API_KEY) {
      return this.generateWithRunway(params);
    } else if (process.env.PIKA_API_KEY) {
      return this.generateWithPika(params);
    } else {
      // Fallback to mock for testing
      return this.generateMockVideo(params);
    }
  }
}