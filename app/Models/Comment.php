<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_id',
        'user_id',
        'parent_id',
        'content',
        'position',
        'resolved',
    ];

    protected $casts = [
        'position' => 'array',
        'resolved' => 'boolean',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    // Helper Methods
    public function resolve(): void
    {
        $this->update(['resolved' => true]);
        $this->replies()->update(['resolved' => true]);
    }

    public function unresolve(): void
    {
        $this->update(['resolved' => false]);
        $this->replies()->update(['resolved' => false]);
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }

    public function getReplyCount(): int
    {
        return $this->replies()->count();
    }
}