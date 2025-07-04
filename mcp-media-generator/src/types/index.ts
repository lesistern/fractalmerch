import { z } from 'zod';

// Image generation schemas
export const imageGenerationSchema = z.object({
  prompt: z.string().describe("Text description of the image to generate"),
  style: z.enum([
    "realistic", 
    "artistic", 
    "cartoon", 
    "anime", 
    "photographic", 
    "digital-art",
    "cinematic",
    "fantasy"
  ]).optional().describe("Style of the image"),
  aspect_ratio: z.enum([
    "1:1", 
    "16:9", 
    "9:16", 
    "4:3", 
    "3:4"
  ]).optional().describe("Aspect ratio of the image"),
  quality: z.enum(["standard", "hd"]).optional().describe("Quality of the image"),
  size: z.enum(["256x256", "512x512", "1024x1024", "1792x1024", "1024x1792"]).optional().describe("Size of the image")
});

// Video generation schemas  
export const videoGenerationSchema = z.object({
  prompt: z.string().describe("Text description of the video to generate"),
  duration: z.number().min(1).max(30).optional().describe("Duration in seconds (1-30)"),
  fps: z.number().min(12).max(60).optional().describe("Frames per second (12-60)"),
  resolution: z.enum(["480p", "720p", "1080p"]).optional().describe("Video resolution"),
  style: z.enum([
    "realistic", 
    "animated", 
    "cinematic", 
    "documentary",
    "artistic"
  ]).optional().describe("Style of the video")
});

// Text-to-image enhancement schemas
export const imageEnhancementSchema = z.object({
  image_url: z.string().url().describe("URL of the image to enhance"),
  enhancement_type: z.enum([
    "upscale", 
    "denoise", 
    "colorize", 
    "restore",
    "super-resolution"
  ]).describe("Type of enhancement to apply"),
  strength: z.number().min(0.1).max(1.0).optional().describe("Enhancement strength (0.1-1.0)")
});

// Response types
export interface MediaGenerationResponse {
  success: boolean;
  url?: string;
  filename?: string;
  error?: string;
  metadata?: {
    width?: number;
    height?: number;
    format?: string;
    size?: number;
    duration?: number;
  };
}

export type ImageGenerationInput = z.infer<typeof imageGenerationSchema>;
export type VideoGenerationInput = z.infer<typeof videoGenerationSchema>;
export type ImageEnhancementInput = z.infer<typeof imageEnhancementSchema>;