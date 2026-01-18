<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    // Relationships
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper Methods
    public function getUrl(): string
    {
        return Storage::url($this->path);
    }

    public function getFormattedSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getIconClass(): string
    {
        return match (true) {
            $this->isImage() => 'bi-file-image',
            $this->isPdf() => 'bi-file-pdf',
            str_contains($this->mime_type, 'word') => 'bi-file-word',
            str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet') => 'bi-file-excel',
            str_contains($this->mime_type, 'video') => 'bi-file-play',
            str_contains($this->mime_type, 'audio') => 'bi-file-music',
            str_contains($this->mime_type, 'zip') || str_contains($this->mime_type, 'archive') => 'bi-file-zip',
            default => 'bi-file-earmark',
        };
    }

    public function delete(): bool
    {
        Storage::delete($this->path);
        return parent::delete();
    }
}