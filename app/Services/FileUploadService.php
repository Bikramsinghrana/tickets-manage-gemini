<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'application/zip',
        'application/x-rar-compressed',
    ];

    protected int $maxFileSize = 10485760; // 10MB in bytes

    /**
     * Upload a file and create attachment record
     */
    public function upload(UploadedFile $file, Model $attachable, string $disk = 'public'): Attachment
    {
        $this->validateFile($file);

        $filename = $this->generateFilename($file);
        $directory = $this->getUploadDirectory($attachable);
        $path = $file->storeAs($directory, $filename, $disk);

        return Attachment::create([
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_path' => $path,
            'disk' => $disk,
            'attachable_type' => get_class($attachable),
            'attachable_id' => $attachable->id,
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(array $files, Model $attachable, string $disk = 'public'): array
    {
        $attachments = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $attachments[] = $this->upload($file, $attachable, $disk);
            }
        }
        
        return $attachments;
    }

    /**
     * Delete an attachment
     */
    public function delete(Attachment $attachment): bool
    {
        // Delete file from storage
        if (Storage::disk($attachment->disk)->exists($attachment->file_path)) {
            Storage::disk($attachment->disk)->delete($attachment->file_path);
        }

        return $attachment->delete();
    }

    /**
     * Get file contents for download
     */
    public function download(Attachment $attachment): array
    {
        $disk = Storage::disk($attachment->disk);
        
        if (!$disk->exists($attachment->file_path)) {
            throw new \RuntimeException('File not found');
        }

        return [
            'content' => $disk->get($attachment->file_path),
            'mime_type' => $attachment->mime_type,
            'filename' => $attachment->original_name,
        ];
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Invalid file upload');
        }

        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException(
                'File type not allowed. Allowed types: ' . implode(', ', $this->allowedMimeTypes)
            );
        }

        if ($file->getSize() > $this->maxFileSize) {
            throw new \InvalidArgumentException(
                'File size exceeds maximum allowed size of ' . $this->formatBytes($this->maxFileSize)
            );
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }

    /**
     * Get upload directory based on attachable type
     */
    protected function getUploadDirectory(Model $attachable): string
    {
        $type = class_basename($attachable);
        $date = now()->format('Y/m');
        
        return "attachments/{$type}/{$date}";
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Set allowed mime types
     */
    public function setAllowedMimeTypes(array $mimeTypes): self
    {
        $this->allowedMimeTypes = $mimeTypes;
        return $this;
    }

    /**
     * Set max file size
     */
    public function setMaxFileSize(int $bytes): self
    {
        $this->maxFileSize = $bytes;
        return $this;
    }
}
