<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Message extends Model
{
    protected $fillable = [
        'text',
        'type'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType(Builder $query, ?string $type): void {
        if ($type !== null) {
            $query->where('type', $type);
        }
    }

    public function scopeForUser(Builder $quert, ?int $userId): void {
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
    }

    public function scopeSearch(Builder $query, ?string $search): void {
        if ($search !== null) {
            $query->where(
                'text',
                'like',
                "%{$search}%"
            );
        }
    }
}