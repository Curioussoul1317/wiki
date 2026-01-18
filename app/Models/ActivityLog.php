<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // Static Methods
    public static function log(
        string $action,
        Model $subject,
        ?array $properties = null,
        ?User $user = null,
        ?Team $team = null
    ): self {
        $user = $user ?? auth()->user();
        $team = $team ?? ($user ? $user->currentTeam() : null);

        return static::create([
            'team_id' => $team?->id,
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'properties' => $properties,
        ]);
    }

    // Helper Methods
    public function getDescription(): string
    {
        $userName = $this->user?->name ?? 'Someone';
        $subjectName = $this->getSubjectName();

        return match ($this->action) {
            'created' => "{$userName} created {$subjectName}",
            'updated' => "{$userName} updated {$subjectName}",
            'deleted' => "{$userName} deleted {$subjectName}",
            'viewed' => "{$userName} viewed {$subjectName}",
            'published' => "{$userName} published {$subjectName}",
            'unpublished' => "{$userName} unpublished {$subjectName}",
            'restored' => "{$userName} restored {$subjectName}",
            'commented' => "{$userName} commented on {$subjectName}",
            'shared' => "{$userName} shared {$subjectName}",
            default => "{$userName} performed {$this->action} on {$subjectName}",
        };
    }

    protected function getSubjectName(): string
    {
        if (!$this->subject) {
            return $this->properties['name'] ?? 'an item';
        }

        return match ($this->subject_type) {
            Document::class => $this->subject->title,
            Collection::class => $this->subject->name,
            Comment::class => 'a comment',
            default => 'an item',
        };
    }

    public function getIconClass(): string
    {
        return match ($this->action) {
            'created' => 'bi-plus-circle text-success',
            'updated' => 'bi-pencil text-primary',
            'deleted' => 'bi-trash text-danger',
            'viewed' => 'bi-eye text-info',
            'published' => 'bi-globe text-success',
            'unpublished' => 'bi-globe2 text-warning',
            'restored' => 'bi-arrow-counterclockwise text-info',
            'commented' => 'bi-chat text-primary',
            'shared' => 'bi-share text-info',
            default => 'bi-activity text-secondary',
        };
    }

    // Scopes
    public function scopeForTeam($query, Team $team)
    {
        return $query->where('team_id', $team->id);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}