<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Server;

class ApiKey extends Model 
{
    public function server(): BelongsTo 
    {
        return $this->belongsTo(Server::class);
    }
}
