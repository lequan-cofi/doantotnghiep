<?php

namespace App\Services;

use App\Models\ChatAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileUploadService
{
    const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB
    const ALLOWED_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const ALLOWED_DOCUMENT_TYPES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'];
    const ALLOWED_VIDEO_TYPES = ['mp4', 'avi', 'mov', 'wmv'];

    /**
     * Upload chat attachment
     */
    public function uploadChatAttachment(UploadedFile $file, int $userId)
    {
        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        // Determine storage path
        $path = 'chat-attachments/' . date('Y/m/d') . '/' . $filename;

        // Store file
        $storedPath = $file->storeAs('chat-attachments/' . date('Y/m/d'), $filename, 'public');

        // If it's an image, create thumbnail
        $thumbnailPath = null;
        if ($this->isImage($file)) {
            $thumbnailPath = $this->createThumbnail($storedPath, $filename);
        }

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'file_extension' => $extension,
            'thumbnail_path' => $thumbnailPath,
            'uploaded_by' => $userId,
        ];
    }

    /**
     * Create thumbnail for image
     */
    private function createThumbnail(string $imagePath, string $filename)
    {
        try {
            $fullPath = storage_path('app/public/' . $imagePath);
            $thumbnailFilename = 'thumb_' . $filename;
            $thumbnailPath = dirname($imagePath) . '/' . $thumbnailFilename;

            // Create thumbnail using Intervention Image
            $img = Image::make($fullPath);
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save(storage_path('app/public/' . $thumbnailPath));

            return $thumbnailPath;
        } catch (\Exception $e) {
            // If thumbnail creation fails, continue without thumbnail
            return null;
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file)
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('File size exceeds maximum allowed size of 20MB');
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = array_merge(
            self::ALLOWED_IMAGE_TYPES,
            self::ALLOWED_DOCUMENT_TYPES,
            self::ALLOWED_VIDEO_TYPES
        );

        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedExtensions));
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = [
            // Images
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            // Documents
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed',
            // Videos
            'video/mp4',
            'video/avi',
            'video/quicktime',
            'video/x-ms-wmv',
        ];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new \Exception('File MIME type not allowed');
        }

        // Additional security checks
        $this->performSecurityChecks($file);
    }

    /**
     * Perform security checks on file
     */
    private function performSecurityChecks(UploadedFile $file)
    {
        // Check for malicious file extensions
        $filename = $file->getClientOriginalName();
        $dangerousExtensions = ['exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar'];
        
        foreach ($dangerousExtensions as $ext) {
            if (str_contains(strtolower($filename), '.' . $ext)) {
                throw new \Exception('File type not allowed for security reasons');
            }
        }

        // Check file content for suspicious patterns (basic check)
        $content = file_get_contents($file->getPathname());
        $suspiciousPatterns = [
            '<?php',
            '<script',
            'javascript:',
            'vbscript:',
            'data:text/html',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                throw new \Exception('File contains suspicious content');
            }
        }
    }

    /**
     * Check if file is an image
     */
    private function isImage(UploadedFile $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, self::ALLOWED_IMAGE_TYPES);
    }

    /**
     * Delete attachment and its files
     */
    public function deleteAttachment(ChatAttachment $attachment)
    {
        try {
            // Delete main file
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            // Delete thumbnail if exists
            $thumbnailPath = dirname($attachment->file_path) . '/thumb_' . basename($attachment->file_path);
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            // Delete database record
            $attachment->delete();

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete attachment: ' . $e->getMessage());
        }
    }

    /**
     * Get file URL
     */
    public function getFileUrl(string $filePath)
    {
        return Storage::disk('public')->url($filePath);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(string $filePath)
    {
        $thumbnailPath = dirname($filePath) . '/thumb_' . basename($filePath);
        
        if (Storage::disk('public')->exists($thumbnailPath)) {
            return Storage::disk('public')->url($thumbnailPath);
        }

        return null;
    }

    /**
     * Get file info
     */
    public function getFileInfo(string $filePath)
    {
        if (!Storage::disk('public')->exists($filePath)) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($filePath);
        
        return [
            'size' => filesize($fullPath),
            'size_human' => $this->formatBytes(filesize($fullPath)),
            'mime_type' => mime_content_type($fullPath),
            'last_modified' => filemtime($fullPath),
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Clean up old attachments (for maintenance)
     */
    public function cleanupOldAttachments(int $daysOld = 90)
    {
        $cutoffDate = now()->subDays($daysOld);
        
        $oldAttachments = ChatAttachment::where('created_at', '<', $cutoffDate)->get();
        
        foreach ($oldAttachments as $attachment) {
            $this->deleteAttachment($attachment);
        }
        
        return $oldAttachments->count();
    }
}
