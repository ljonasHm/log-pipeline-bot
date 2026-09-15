<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Server;

class ApiKey extends Model 
{
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
