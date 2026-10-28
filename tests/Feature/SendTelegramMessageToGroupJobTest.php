<?php

namespace Tests\Feature;

use App\Jobs\SendTelegramMessageToGroup;
use App\Models\Message;
use App\Models\TelegramGroup;
use App\Services\TelegramMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;
use Tests\TestCase;

class SendTelegramMessageToGroupJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function removalErrors(): array
    {
        return [
            'kicked' => ['Forbidden: bot was kicked from the group chat'],
            'not a member' => ['Forbidden: bot is not a member of the supergroup chat'],
        ];
    }

    public function test_it_sends_a_message_to_an_active_group(): void
    {
        $group = TelegramGroup::factory()->receiver()->create();
        $message = Message::factory()->create();

        $this->mock(TelegramMessageService::class, function (MockInterface $mock) use ($group, $message): void {
            $mock->shouldReceive('sendToGroup')
                ->once()
                ->withArgs(fn (TelegramGroup $sentGroup, Message $sentMessage): bool => $sentGroup->is($group)
                    && $sentMessage->is($message));
        });

        (new SendTelegramMessageToGroup($group->id, $message->id))->handle(
            app(TelegramMessageService::class),
        );
    }

    public function test_it_returns_quietly_when_the_group_is_inactive(): void
    {
        $group = TelegramGroup::factory()->receiver()->inactive()->create();
        $message = Message::factory()->create();

        $this->mock(TelegramMessageService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('sendToGroup');
        });

        (new SendTelegramMessageToGroup($group->id, $message->id))->handle(
            app(TelegramMessageService::class),
        );
    }

    public function test_it_returns_quietly_when_the_group_is_missing(): void
    {
        $message = Message::factory()->create();

        $this->mock(TelegramMessageService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('sendToGroup');
        });

        (new SendTelegramMessageToGroup(999_999, $message->id))->handle(
            app(TelegramMessageService::class),
        );
    }

    #[DataProvider('removalErrors')]
    public function test_it_deactivates_the_group_when_the_bot_was_removed(string $error): void
    {
        $group = TelegramGroup::factory()->receiver()->create();
        $message = Message::factory()->create();

        $this->mock(TelegramMessageService::class, function (MockInterface $mock) use ($error): void {
            $mock->shouldReceive('sendToGroup')
                ->once()
                ->andThrow(new TelegramException($error, 403));
        });

        (new SendTelegramMessageToGroup($group->id, $message->id))->handle(
            app(TelegramMessageService::class),
        );

        $this->assertFalse($group->fresh()->is_active);
    }

    public function test_it_keeps_the_group_active_when_telegram_rejects_the_message(): void
    {
        $group = TelegramGroup::factory()->receiver()->create();
        $message = Message::factory()->create();

        $this->mock(TelegramMessageService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendToGroup')
                ->once()
                ->andThrow(new TelegramException('Bad Request: message is too long', 400));
        });

        try {
            (new SendTelegramMessageToGroup($group->id, $message->id))->handle(
                app(TelegramMessageService::class),
            );
            $this->fail('Telegram rejection was swallowed.');
        } catch (TelegramException $exception) {
            $this->assertSame(400, $exception->getCode());
        }

        $this->assertTrue($group->fresh()->is_active);
    }

    public function test_it_keeps_the_group_active_when_the_bot_lacks_send_permission(): void
    {
        $group = TelegramGroup::factory()->receiver()->create();
        $message = Message::factory()->create();

        $this->mock(TelegramMessageService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendToGroup')
                ->once()
                ->andThrow(new TelegramException('Forbidden: not enough rights to send text messages to the chat', 403));
        });

        try {
            (new SendTelegramMessageToGroup($group->id, $message->id))->handle(
                app(TelegramMessageService::class),
            );
            $this->fail('Permission error was swallowed.');
        } catch (TelegramException $exception) {
            $this->assertSame(403, $exception->getCode());
        }

        $this->assertTrue($group->fresh()->is_active);
    }
}
