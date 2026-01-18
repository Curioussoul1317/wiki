<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShareLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'created_by',
        'token',
        'permission',
        'requires_password',
        'password',
        'expires_at',
        'view_count',
    ];

    protected $casts = [
        'requires_password' => 'boolean',
        'expires_at' => 'datetime',
        'view_count' => 'integer',
    ];

    protected $hidden = [
        'password',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shareLink) {
            if (empty($shareLink->token)) {
                $shareLink->token = Str::random(32);
            }
        });
    }

    // Relationships
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper Methods
    public function getUrl(): string
    {
        return route('share.show', $this->token);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    public function checkPassword(string $password): bool
    {
        if (!$this->requires_password) {
            return true;
        }

        return Hash::check($password, $this->password);
    }

    public function setPassword(string $password): void
    {
        $this->update([
            'requires_password' => true,
            'password' => Hash::make($password),
        ]);
    }

    public function removePassword(): void
    {
        $this->update([
            'requires_password' => false,
            'password' => null,
        ]);
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function canEdit(): bool
    {
        return $this->permission === 'edit';
    }
}