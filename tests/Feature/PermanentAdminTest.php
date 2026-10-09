<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\TelegramUser;
use App\Telegram\Conversations\AddUserIgnoredMessageTypeConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionProperty;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use Tests\TestCase;

class PermanentAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_permanent_admin_with_no_role_is_an_admin(): void
    {
        $user = TelegramUser::factory()->create([
            'chat_id' => 424242,
            'role' => UserRole::NONE,
        ]);

        config(['telegram.permanent_admin_chat_ids' => [424242]]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isReceiver());
    }

    public function test_user_outside_the_list_keeps_the_database_role(): void
    {
        config(['telegram.permanent_admin_chat_ids' => []]);

        $admin = TelegramUser::factory()->admin()->create();
        $receiver = TelegramUser::factory()->receiver()->create();
        $user = TelegramUser::factory()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isReceiver());
        $this->assertFalse($receiver->isAdmin());
        $this->assertTrue($receiver->isReceiver());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isReceiver());
    }

    public function test_role_command_does_not_change_a_permanent_admin(): void
    {
        $user = TelegramUser::factory()->create([
            'chat_id' => 424242,
            'role' => UserRole::NONE,
        ]);

        config(['telegram.permanent_admin_chat_ids' => [424242]]);

        $this->artisan('user:role', [
            'role' => 'receiver',
            '--chat-id' => $user->chat_id,
        ])->assertFailed();

        $this->assertSame(UserRole::NONE, $user->fresh()->role);
    }

    public function test_adding_an_ignored_type_does_not_change_a_permanent_admin(): void
    {
        $user = TelegramUser::factory()->create([
            'chat_id' => 424242,
        ]);

        config(['telegram.permanent_admin_chat_ids' => [424242]]);

        $bot = Mockery::mock(Nutgram::class);
        $bot->shouldReceive('update')->andReturn(null);
        $bot->shouldReceive('sendMessage')->once()->with('This user\'s ignore list cannot be changed.');
        $bot->shouldReceive('endConversation')->once();

        $conversation = new AddUserIgnoredMessageTypeConversation;

        $step = new ReflectionProperty(Conversation::class, 'step');
        $step->setValue($conversation, 'start');

        $conversation($bot, (string) $user->id);

        $this->assertSame(0, $user->ignoredMessageTypes()->count());
    }
}
