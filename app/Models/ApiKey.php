<?php

namespace App\Models;

use Database\Factories\ApiKeyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    /** @use HasFactory<ApiKeyFactory> */
    use HasFactory;

    protected $fillable = [
        'server_id',
        'name',
        'identifier',
        'key_hash',
        'active',
        'last_used_at',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
