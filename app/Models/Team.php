<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
        });
    }

    // Relationships
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function owner()
    {
        return $this->users()->wherePivot('role', 'owner')->first();
    }

    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivotIn('role', ['owner', 'admin']);
    }

    public function editors(): BelongsToMany
    {
        return $this->users()->wherePivotIn('role', ['owner', 'admin', 'editor']);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function rootCollections(): HasMany
    {
        return $this->hasMany(Collection::class)->whereNull('parent_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Helper Methods
    public function addUser(User $user, string $role = 'viewer'): void
    {
        if (!$this->hasUser($user)) {
            $this->users()->attach($user->id, ['role' => $role]);
        }
    }

    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);
    }

    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function updateUserRole(User $user, string $role): void
    {
        $this->users()->updateExistingPivot($user->id, ['role' => $role]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }
}