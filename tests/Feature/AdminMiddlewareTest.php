<?php

namespace Tests\Feature;

use App\Telegram\Middleware\AdminMiddleware;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use SergiX44\Nutgram\Middleware\Link;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ChatType;
use SergiX44\Nutgram\Telegram\Types\Chat\Chat;
use SergiX44\Nutgram\Telegram\Types\Message\Message as TelegramMessage;
use SergiX44\Nutgram\Telegram\Types\User\User;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_command_does_not_continue_or_reply(): void
    {
        $bot = $this->bot(ChatType::GROUP);
        $message = new TelegramMessage;
        $message->text = '/start';
        $bot->shouldReceive('isMyChatMember')->andReturn(false);
        $bot->shouldReceive('message')->andReturn($message);
        $bot->shouldNotReceive('sendMessage');
        $bot->shouldNotReceive('invoke');
        $bot->shouldNotReceive('user');

        (new AdminMiddleware)($bot, new Link(static fn () => null));
    }

    public function test_channel_update_does_not_continue_or_reply(): void
    {
        $bot = $this->bot(ChatType::CHANNEL);
        $bot->shouldNotReceive('sendMessage');
        $bot->shouldNotReceive('invoke');

        (new AdminMiddleware)($bot, new Link(static fn () => null));
    }

    public function test_group_membership_update_continues_without_a_reply(): void
    {
        $bot = $this->bot(ChatType::SUPERGROUP);
        $bot->shouldReceive('isMyChatMember')->andReturn(true);
        $bot->shouldReceive('invoke')->once();
        $bot->shouldNotReceive('sendMessage');

        (new AdminMiddleware)($bot, new Link(static fn () => null));
    }

    public function test_group_migration_update_continues_without_a_reply(): void
    {
        $bot = $this->bot(ChatType::GROUP);
        $message = new TelegramMessage;
        $message->migrate_to_chat_id = -100200300;
        $bot->shouldReceive('isMyChatMember')->andReturn(false);
        $bot->shouldReceive('message')->andReturn($message);
        $bot->shouldReceive('invoke')->once();
        $bot->shouldNotReceive('sendMessage');

        (new AdminMiddleware)($bot, new Link(static fn () => null));
    }

    public function test_private_chat_without_a_user_still_replies_that_there_are_no_rules(): void
    {
        $bot = $this->bot(ChatType::PRIVATE);
        $user = new User;
        $user->id = 42;
        $bot->shouldReceive('callbackQuery')->andReturn(null);
        $bot->shouldReceive('message')->andReturn(null);
        $bot->shouldReceive('user')->andReturn($user);
        $bot->shouldReceive('sendMessage')->once()->with('No rules.');
        $bot->shouldNotReceive('invoke');

        (new AdminMiddleware)($bot, new Link(static fn () => null));
    }

    private function bot(ChatType $type): Nutgram
    {
        $chat = new Chat;
        $chat->id = $type === ChatType::PRIVATE ? 42 : -100123;
        $chat->type = $type;

        $bot = Mockery::mock(Nutgram::class);
        $bot->shouldReceive('chat')->andReturn($chat);

        return $bot;
    }
}
