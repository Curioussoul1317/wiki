<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'title',
        'content',
        'version',
        'change_summary',
    ];

    protected $casts = [
        'version' => 'integer',
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
    public function getExcerpt(int $length = 200): string
    {
        $text = strip_tags($this->content ?? '');
        
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }

    public function getDiff(DocumentVersion $otherVersion): array
    {
        // Simple diff - you might want to use a proper diff library
        $oldLines = explode("\n", $otherVersion->content ?? '');
        $newLines = explode("\n", $this->content ?? '');

        return [
            'old' => $oldLines,
            'new' => $newLines,
            'added' => count($newLines) - count($oldLines),
        ];
    }
}