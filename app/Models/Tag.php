<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'color',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class);
    }

    // Helper Methods
    public function getDocumentCount(): int
    {
        return $this->documents()->count();
    }

    public function getColorAttribute($value): string
    {
        return $value ?? '#6366f1';
    }
}