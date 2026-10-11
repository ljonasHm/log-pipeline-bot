<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\MessageType;
use App\Models\Server;
use App\Models\TelegramUser;
use App\Services\TelegramMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use SergiX44\Nutgram\Nutgram;
use Tests\TestCase;

class TelegramMessageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_the_server_name_and_message_type_title_when_the_type_is_known(): void
    {
        $server = Server::factory()->create([
            'name' => 'web',
        ]);
        $messageType = MessageType::factory()->create([
            'name' => 'error',
            'title' => 'Disk error',
        ]);
        $user = TelegramUser::factory()->receiver()->create();
        $message = Message::factory()->create([
            'type' => 'error',
            'text' => 'Disk is full',
            'message_type_id' => $messageType->id,
            'server_id' => $server->id,
        ]);

        $this->mock(Nutgram::class, function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function (string $text, int|string|null $chatId) use ($user): bool {
                    return $text === "web: Disk error\n\nDisk is full"
                        && $chatId === $user->chat_id;
                });
        });

        app(TelegramMessageService::class)->sendToUser($user, $message);
    }

    public function test_sends_the_server_name_and_raw_type_when_the_message_type_is_unknown(): void
    {
        $server = Server::factory()->create([
            'name' => 'web',
        ]);
        $user = TelegramUser::factory()->receiver()->create();
        $message = Message::factory()->create([
            'type' => 'custom',
            'text' => 'Hello',
            'message_type_id' => null,
            'server_id' => $server->id,
        ]);

        $this->mock(Nutgram::class, function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function (string $text, int|string|null $chatId) use ($user): bool {
                    return $text === "web: custom\n\nHello"
                        && $chatId === $user->chat_id;
                });
        });

        app(TelegramMessageService::class)->sendToUser($user, $message);
    }
}
