<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\Server;
use App\Models\ApiKey;

class ApiKeyService
{

    public function create(Server $server, string $name): string {
        $identifier = Str::random(16);
        $secret = Str::random(64);

        ApiKey::create([
            'server_id' => $server->id,
            'name' => $name,
            'identifier' => $identifier,
            'key_hash' => Hash::make($secret)
        ]);

        return "sk_live_{$identifier}_{$secret}";
    }
}
