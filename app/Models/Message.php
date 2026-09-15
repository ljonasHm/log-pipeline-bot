<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

use App\Models\Server;

class Message extends Model
{
    protected $fillable = [
        'text',
        'type'
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function scopeOfType(Builder $query, ?string $type): void {
        if ($type !== null) {
            $query->where('type', $type);
        }
    }

    public function scopeForServer(Builder $query, ?int $serverId): void {
        if ($serverId !== null) {
            $query->where('server_id', $serverId);
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