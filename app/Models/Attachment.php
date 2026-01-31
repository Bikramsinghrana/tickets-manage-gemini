<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'filename',
        'original_name',
        'mime_type',
        'file_size',
        'file_path',
        'disk',
        'attachable_type',
        'attachable_id',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the parent attachable model (Ticket or Comment)
     */
    public function attachable()
    {
        return $this->morphTo();
    }

    /**
     * User who uploaded this file
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get file URL
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a document
     */
    public function getIsDocumentAttribute(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ];
        
        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Get file icon based on type
     */
    public function getIconAttribute(): string
    {
        if ($this->is_image) {
            return 'fa-file-image';
        }
        
        return match($this->mime_type) {
            'application/pdf' => 'fa-file-pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel',
            'text/plain' => 'fa-file-alt',
            'application/zip', 'application/x-rar-compressed' => 'fa-file-archive',
            default => 'fa-file',
        };
    }

    /**
     * Delete the file from storage
     */
    public function deleteFile(): bool
    {
        if (Storage::disk($this->disk)->exists($this->file_path)) {
            return Storage::disk($this->disk)->delete($this->file_path);
        }
        return true;
    }

    /**
     * Boot method to delete file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            // Only delete file on force delete
            if ($attachment->isForceDeleting()) {
                $attachment->deleteFile();
            }
        });
    }
}
