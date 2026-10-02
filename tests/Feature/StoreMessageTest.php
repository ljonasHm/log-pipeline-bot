<?php

namespace Tests\Feature;

use App\Events\MessageCreated;
use App\Models\Message;
use App\Models\MessageType;
use App\Models\Server;
use App\Services\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class StoreMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_known_message_type_is_linked_when_a_message_is_stored(): void
    {
        $messageType = MessageType::factory()->create([
            'name' => 'Error',
        ]);
        $apiKey = app(ApiKeyService::class)->create(Server::factory()->create(), 'logs');

        Event::fake([MessageCreated::class]);

        $response = $this->postJson('/api/messages', [
            'text' => 'Disk is full',
            'type' => 'ERROR',
        ], [
            'X-API-Key' => $apiKey,
        ]);

        $response->assertCreated();

        $message = Message::query()->where('text', 'Disk is full')->first();

        $this->assertNotNull($message);
        $this->assertSame('error', $message->type);
        $this->assertSame($messageType->id, $message->message_type_id);
        Event::assertDispatched(MessageCreated::class);
    }

    public function test_unknown_message_type_is_stored_without_a_link(): void
    {
        MessageType::factory()->create([
            'name' => 'error',
        ]);
        $apiKey = app(ApiKeyService::class)->create(Server::factory()->create(), 'logs');

        Event::fake([MessageCreated::class]);

        $response = $this->postJson('/api/messages', [
            'text' => 'Custom notice',
            'type' => 'custom',
        ], [
            'X-API-Key' => $apiKey,
        ]);

        $response->assertCreated();

        $message = Message::query()->where('text', 'Custom notice')->first();

        $this->assertNotNull($message);
        $this->assertSame('custom', $message->type);
        $this->assertNull($message->message_type_id);
    }
}
