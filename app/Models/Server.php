<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\ApiKey;
use App\Models\Message;

class Server extends Model
{
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
