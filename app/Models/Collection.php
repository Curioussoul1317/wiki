<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Collection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'parent_id',
        'created_by',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'sort_order',
        'permission',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($collection) {
            if (empty($collection->slug)) {
                $collection->slug = static::generateUniqueSlug($collection->name, $collection->team_id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, int $teamId): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('team_id', $teamId)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Collection::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Collection::class, 'parent_id')->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class)->orderBy('sort_order');
    }

    public function allDocuments(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // Helper Methods
    public function getAncestors(): \Illuminate\Support\Collection
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->prepend($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        
        foreach ($this->getAncestors() as $ancestor) {
            $breadcrumbs[] = [
                'id' => $ancestor->id,
                'name' => $ancestor->name,
                'slug' => $ancestor->slug,
            ];
        }
        
        $breadcrumbs[] = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];

        return $breadcrumbs;
    }

    public function getAllDescendants(): \Illuminate\Support\Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }

    public function getDocumentCount(): int
    {
        $count = $this->documents()->count();

        foreach ($this->children as $child) {
            $count += $child->getDocumentCount();
        }

        return $count;
    }

    public function getIconAttribute($value): string
    {
        return $value ?? '📁';
    }

    public function getColorAttribute($value): string
    {
        return $value ?? '#6366f1';
    }

    public function isAccessibleBy(User $user): bool
    {
        if ($this->permission === 'public') {
            return true;
        }

        if ($this->permission === 'team') {
            return $user->belongsToTeam($this->team);
        }

        // Private - only creator and admins
        return $this->created_by === $user->id || $user->isTeamAdmin($this->team);
    }
}