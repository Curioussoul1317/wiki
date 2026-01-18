<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'team_id',
        'collection_id',
        'parent_id',
        'created_by',
        'last_edited_by',
        'title',
        'slug',
        'content',
        'summary',
        'emoji',
        'cover_image',
        'is_template',
        'is_published',
        'published_at',
        'sort_order',
        'version',
        'metadata',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
        'version' => 'integer',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($document) {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
            if (empty($document->slug)) {
                $document->slug = static::generateUniqueSlug($document->title, $document->team_id);
            }
            if (empty($document->version)) {
                $document->version = 1;
            }
        });
    
        static::updating(function ($document) {
            if ($document->isDirty('content') || $document->isDirty('title')) {
                $document->version = ($document->version ?? 0) + 1;
            }
        });
    }

    public static function generateUniqueSlug(string $title, int $teamId): string
    {
        $slug = Str::slug($title);
        
        if (empty($slug)) {
            $slug = 'untitled';
        }
        
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

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Document::class, 'parent_id')->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version', 'desc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->orderBy('created_at');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function starredBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'starred_documents')
            ->withTimestamps();
    }

    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'document_views')
            ->withPivot('viewed_at');
    }

    public function shareLinks(): HasMany
    {
        return $this->hasMany(ShareLink::class);
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

        if ($this->collection) {
            foreach ($this->collection->getBreadcrumbs() as $crumb) {
                $breadcrumbs[] = array_merge($crumb, ['type' => 'collection']);
            }
        }

        foreach ($this->getAncestors() as $ancestor) {
            $breadcrumbs[] = [
                'id' => $ancestor->id,
                'name' => $ancestor->title,
                'slug' => $ancestor->slug,
                'type' => 'document',
            ];
        }

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

    public function createVersion(?string $changeSummary = null): DocumentVersion
    {
        return $this->versions()->create([
            'user_id' => auth()->id(),
            'title' => $this->title,
            'content' => $this->content,
            'version' => $this->version ?? 1,
            'change_summary' => $changeSummary,
        ]);
    }

    public function restoreVersion(DocumentVersion $version): void
    {
        $this->createVersion('Restored from version ' . $version->version);

        $this->update([
            'title' => $version->title,
            'content' => $version->content,
            'last_edited_by' => auth()->id(),
        ]);
    }

    public function markAsViewed(?User $user = null): void
    {
        $user = $user ?? auth()->user();
        
        if ($user) {
            $this->viewers()->syncWithoutDetaching([
                $user->id => ['viewed_at' => now()],
            ]);
        }
    }

    public function isStarredBy(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return false;
        }

        return $this->starredBy()->where('user_id', $user->id)->exists();
    }

    public function toggleStar(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return false;
        }

        if ($this->isStarredBy($user)) {
            $this->starredBy()->detach($user->id);
            return false;
        }

        $this->starredBy()->attach($user->id);
        return true;
    }

    public function getEmojiAttribute($value): string
    {
        return $value ?? '📄';
    }

    public function getExcerpt(int $length = 200): string
    {
        $text = strip_tags($this->content ?? '');
        
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }

    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->content ?? ''));
    }

    public function getReadingTime(): int
    {
        $wordsPerMinute = 200;
        return max(1, ceil($this->getWordCount() / $wordsPerMinute));
    }

    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function duplicate(?int $collectionId = null, ?int $parentId = null): Document
    {
        $newDocument = $this->replicate();
        $newDocument->uuid = (string) Str::uuid();
        $newDocument->title = $this->title . ' (Copy)';
        $newDocument->slug = static::generateUniqueSlug($newDocument->title, $this->team_id);
        $newDocument->collection_id = $collectionId ?? $this->collection_id;
        $newDocument->parent_id = $parentId ?? $this->parent_id;
        $newDocument->created_by = auth()->id();
        $newDocument->is_published = false;
        $newDocument->published_at = null;
        $newDocument->version = 1;
        $newDocument->save();

        // Copy tags
        $newDocument->tags()->sync($this->tags->pluck('id'));

        return $newDocument;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeNotTemplates($query)
    {
        return $query->where('is_template', false);
    }

    public function scopeInCollection($query, $collectionId)
    {
        return $query->where('collection_id', $collectionId);
    }

    public function scopeRootDocuments($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
                ->orWhere('content', 'LIKE', "%{$term}%");
        });
    }
}