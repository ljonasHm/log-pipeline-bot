<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\MessageType;
use App\Models\TelegramUser;
use App\Services\TelegramMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use SergiX44\Nutgram\Nutgram;
use Tests\TestCase;

class TelegramMessageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_the_message_type_title_when_the_type_is_known(): void
    {
        $messageType = MessageType::factory()->create([
            'name' => 'error',
            'title' => 'Disk error',
        ]);
        $user = TelegramUser::factory()->receiver()->create();
        $message = Message::factory()->create([
            'type' => 'error',
            'text' => 'Disk is full',
            'message_type_id' => $messageType->id,
        ]);

        $this->mock(Nutgram::class, function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function (string $text, int|string|null $chatId) use ($user): bool {
                    return $text === "Тип: Disk error\n\nDisk is full"
                        && $chatId === $user->chat_id;
                });
        });

        app(TelegramMessageService::class)->sendToUser($user, $message);
    }

    public function test_sends_the_raw_type_when_the_message_type_is_unknown(): void
    {
        $user = TelegramUser::factory()->receiver()->create();
        $message = Message::factory()->create([
            'type' => 'custom',
            'text' => 'Hello',
            'message_type_id' => null,
        ]);

        $this->mock(Nutgram::class, function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function (string $text, int|string|null $chatId) use ($user): bool {
                    return $text === "Тип: custom\n\nHello"
                        && $chatId === $user->chat_id;
                });
        });

        app(TelegramMessageService::class)->sendToUser($user, $message);
    }
}
