<?php

namespace Tests\Feature;

use App\Enums\ApiKeyDeletionStatus;
use App\Models\ApiKey;
use App\Services\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteApiKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_name_deletes_the_api_key(): void
    {
        $apiKey = ApiKey::factory()->create([
            'name' => 'logs',
        ]);

        $status = app(ApiKeyService::class)->deleteByName('logs');

        $this->assertSame(ApiKeyDeletionStatus::DELETED, $status);
        $this->assertModelMissing($apiKey);
    }

    public function test_missing_name_does_not_delete_an_api_key(): void
    {
        $apiKey = ApiKey::factory()->create([
            'name' => 'logs',
        ]);

        $status = app(ApiKeyService::class)->deleteByName('missing');

        $this->assertSame(ApiKeyDeletionStatus::NOT_FOUND, $status);
        $this->assertModelExists($apiKey);
    }

    public function test_duplicate_name_does_not_delete_api_keys(): void
    {
        $first = ApiKey::factory()->create([
            'name' => 'logs',
        ]);
        $second = ApiKey::factory()->create([
            'name' => 'logs',
        ]);

        $status = app(ApiKeyService::class)->deleteByName('logs');

        $this->assertSame(ApiKeyDeletionStatus::AMBIGUOUS, $status);
        $this->assertModelExists($first);
        $this->assertModelExists($second);
    }

    public function test_identifier_deletes_only_the_matching_api_key(): void
    {
        $matching = ApiKey::factory()->create([
            'name' => 'logs',
            'identifier' => 'identifier-one',
        ]);
        $other = ApiKey::factory()->create([
            'name' => 'logs',
            'identifier' => 'identifier-two',
        ]);

        $status = app(ApiKeyService::class)->deleteByIdentifier('logs', 'identifier-one');

        $this->assertSame(ApiKeyDeletionStatus::DELETED, $status);
        $this->assertModelMissing($matching);
        $this->assertModelExists($other);
    }

    public function test_unknown_identifier_does_not_delete_an_api_key(): void
    {
        $apiKey = ApiKey::factory()->create([
            'name' => 'logs',
            'identifier' => 'identifier-one',
        ]);

        $status = app(ApiKeyService::class)->deleteByIdentifier('logs', 'identifier-other');

        $this->assertSame(ApiKeyDeletionStatus::NOT_FOUND, $status);
        $this->assertModelExists($apiKey);
    }
}
