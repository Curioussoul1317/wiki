<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'preferences' => 'array',
    ];

    // Relationships
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function starredDocuments(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'starred_documents')
            ->withTimestamps();
    }

    public function recentDocuments(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_views')
            ->withPivot('viewed_at')
            ->orderByPivot('viewed_at', 'desc');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Helper Methods
    public function currentTeam()
    {
        $teamId = session('current_team_id');
        
        if ($teamId) {
            return $this->teams()->find($teamId);
        }
        
        return $this->teams()->first();
    }

    public function switchTeam(Team $team): void
    {
        if ($this->belongsToTeam($team)) {
            session(['current_team_id' => $team->id]);
        }
    }

    public function belongsToTeam(Team $team): bool
    {
        return $this->teams()->where('team_id', $team->id)->exists();
    }

    public function teamRole(Team $team): ?string
    {
        $membership = $this->teams()->where('team_id', $team->id)->first();
        return $membership?->pivot->role;
    }

    public function isTeamOwner(Team $team): bool
    {
        return $this->teamRole($team) === 'owner';
    }

    public function isTeamAdmin(Team $team): bool
    {
        return in_array($this->teamRole($team), ['owner', 'admin']);
    }

    public function canEditInTeam(Team $team): bool
    {
        return in_array($this->teamRole($team), ['owner', 'admin', 'editor']);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }
}