<?php

namespace App\Services;

use App\Enums\ApiKeyDeletionStatus;
use App\Models\ApiKey;
use App\Models\Server;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function create(Server $server, string $name): string
    {
        $identifier = Str::random(16);
        $secret = Str::random(64);

        ApiKey::create([
            'server_id' => $server->id,
            'name' => $name,
            'identifier' => $identifier,
            'key_hash' => Hash::make($secret),
        ]);

        return env('API_KEY_PREFIX', 'sk_live')."_{$identifier}_{$secret}";
    }

    public function deleteByName(string $name): ApiKeyDeletionStatus
    {
        $apiKeys = ApiKey::query()
            ->where('name', $name)
            ->limit(2)
            ->get();

        if ($apiKeys->isEmpty()) {
            return ApiKeyDeletionStatus::NOT_FOUND;
        }

        if ($apiKeys->count() > 1) {
            return ApiKeyDeletionStatus::AMBIGUOUS;
        }

        $apiKeys->first()->delete();

        return ApiKeyDeletionStatus::DELETED;
    }

    public function deleteByIdentifier(string $name, string $identifier): ApiKeyDeletionStatus
    {
        $apiKey = ApiKey::query()
            ->where('name', $name)
            ->where('identifier', $identifier)
            ->first();

        if ($apiKey === null) {
            return ApiKeyDeletionStatus::NOT_FOUND;
        }

        $apiKey->delete();

        return ApiKeyDeletionStatus::DELETED;
    }
}
