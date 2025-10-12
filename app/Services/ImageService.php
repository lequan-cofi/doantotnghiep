<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
// use Intervention\Image\Facades\Image; // Temporarily disabled

class ImageService
{
    private $disk;
    private $basePath;

    public function __construct()
    {
        $this->disk = config('filesystems.default');
        $this->basePath = 'images';
    }

    /**
     * Get base path for images
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Upload single image
     */
    public function uploadImage(UploadedFile $file, string $folder = 'general'): array
    {
        $filename = $this->generateFilename($file);
        $path = $this->basePath . '/' . $folder . '/' . date('Y/m');
        
        // Store original image
        $originalPath = $file->storeAs($path, $filename, 'public');
        
        // Create thumbnails
        $thumbnails = $this->createThumbnails($file, $path, $filename);
        
        return [
            'original' => $originalPath,
            'thumbnails' => $thumbnails,
            'url' => Storage::url($originalPath),
            'filename' => $filename,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ];
    }

    /**
     * Upload multiple images
     */
    public function uploadMultipleImages(array $files, string $folder = 'general'): array
    {
        $uploadedImages = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedImages[] = $this->uploadImage($file, $folder);
            }
        }
        
        return $uploadedImages;
    }

    /**
     * Delete image and its thumbnails
     */
    public function deleteImage(string $imagePath): bool
    {
        try {
            // Delete original
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            // Delete thumbnails
            $this->deleteThumbnails($imagePath);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting image: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete multiple images
     */
    public function deleteMultipleImages(array $imagePaths): bool
    {
        $success = true;
        
        foreach ($imagePaths as $path) {
            if (!$this->deleteImage($path)) {
                $success = false;
            }
        }
        
        return $success;
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = Str::slug($name);
        
        return $name . '_' . time() . '_' . Str::random(8) . '.' . $extension;
    }

    /**
     * Create thumbnails (temporarily disabled)
     */
    private function createThumbnails(UploadedFile $file, string $path, string $filename): array
    {
        // Temporarily disable thumbnail creation to avoid Intervention Image dependency
        // TODO: Install intervention/image package and re-enable this functionality
        return [];
        
        /*
        $thumbnails = [];
        $sizes = [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600]
        ];

        foreach ($sizes as $size => $dimensions) {
            try {
                $thumbnailPath = $path . '/thumbnails/' . $size . '_' . $filename;
                
                $image = Image::make($file)
                    ->fit($dimensions[0], $dimensions[1])
                    ->encode('jpg', 85);
                
                Storage::disk('public')->put($thumbnailPath, $image);
                $thumbnails[$size] = $thumbnailPath;
            } catch (\Exception $e) {
                \Log::error('Error creating thumbnail: ' . $e->getMessage());
            }
        }

        return $thumbnails;
        */
    }

    /**
     * Delete thumbnails
     */
    private function deleteThumbnails(string $imagePath): void
    {
        $pathInfo = pathinfo($imagePath);
        $filename = $pathInfo['filename'] . '.' . $pathInfo['extension'];
        $directory = $pathInfo['dirname'];
        
        $sizes = ['small', 'medium', 'large'];
        
        foreach ($sizes as $size) {
            $thumbnailPath = $directory . '/thumbnails/' . $size . '_' . $filename;
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
        }
    }

    /**
     * Get image URL
     */
    public function getImageUrl(string $path, string $size = 'original'): string
    {
        if ($size === 'original') {
            return Storage::url($path);
        }
        
        $pathInfo = pathinfo($path);
        $filename = $pathInfo['filename'] . '.' . $pathInfo['extension'];
        $directory = $pathInfo['dirname'];
        $thumbnailPath = $directory . '/thumbnails/' . $size . '_' . $filename;
        
        if (Storage::exists($thumbnailPath)) {
            return Storage::url($thumbnailPath);
        }
        
        return Storage::url($path);
    }

    /**
     * Validate image file
     */
    public function validateImage(UploadedFile $file): array
    {
        $errors = [];
        
        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            $errors[] = 'File size must be less than 5MB';
        }
        
        // Check MIME type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'File type must be JPEG, PNG, GIF, or WebP';
        }
        
        // Check dimensions (max 4000x4000) - temporarily disabled
        // TODO: Install intervention/image package and re-enable dimension checking
        /*
        try {
            $image = Image::make($file);
            if ($image->width() > 4000 || $image->height() > 4000) {
                $errors[] = 'Image dimensions must be less than 4000x4000 pixels';
            }
        } catch (\Exception $e) {
            $errors[] = 'Invalid image file';
        }
        */
        
        return $errors;
    }

    /**
     * Get storage statistics
     */
    public function getStorageStats(): array
    {
        $totalSize = 0;
        $fileCount = 0;
        
        $files = Storage::disk('public')->allFiles($this->basePath);
        
        foreach ($files as $file) {
            $totalSize += Storage::disk('public')->size($file);
            $fileCount++;
        }
        
        return [
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'file_count' => $fileCount
        ];
    }
}
