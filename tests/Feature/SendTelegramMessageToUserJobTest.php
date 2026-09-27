<?php

namespace Tests\Feature;

use App\Jobs\SendTelegramMessageToUser;
use App\Models\Message;
use App\Models\TelegramUser;
use App\Services\TelegramMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class SendTelegramMessageToUserJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_message_to_user(): void
    {
        $user = TelegramUser::factory()->receiver()->create();
        $message = Message::factory()->create();

        $this->mock(TelegramMessageService::class, function (MockInterface $mock) use ($user, $message): void {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->withArgs(fn (TelegramUser $sentUser, Message $sentMessage): bool => $sentUser->is($user)
                    && $sentMessage->is($message));
        });

        (new SendTelegramMessageToUser($user->id, $message->id))->handle(
            app(TelegramMessageService::class)
        );
    }

    public function test_it_returns_quietly_when_user_is_missing(): void
    {
        $message = Message::factory()->create();

        $this->mock(TelegramMessageService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('sendToUser');
        });

        (new SendTelegramMessageToUser(999_999, $message->id))->handle(
            app(TelegramMessageService::class)
        );
    }

    public function test_it_returns_quietly_when_message_is_missing(): void
    {
        $user = TelegramUser::factory()->receiver()->create();

        $this->mock(TelegramMessageService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('sendToUser');
        });

        (new SendTelegramMessageToUser($user->id, 999_999))->handle(
            app(TelegramMessageService::class)
        );
    }
}
