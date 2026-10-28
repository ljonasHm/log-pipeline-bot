<?php

namespace Tests\Feature;

use App\Enums\GroupRole;
use App\Models\MessageType;
use App\Models\TelegramGroup;
use App\Services\TelegramGroupService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use SergiX44\Nutgram\Telegram\Properties\ChatType;
use SergiX44\Nutgram\Telegram\Types\Chat\Chat;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberAdministrator;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberBanned;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberLeft;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberMember;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberRestricted;
use Tests\TestCase;

class TelegramGroupServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function activeMembers(): array
    {
        return [
            'member' => [ChatMemberMember::class],
            'administrator' => [ChatMemberAdministrator::class],
        ];
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function removedMembers(): array
    {
        return [
            'left' => [ChatMemberLeft::class],
            'kicked' => [ChatMemberBanned::class],
        ];
    }

    /**
     * @return array<string, array{0: ChatType}>
     */
    public static function ignoredChatTypes(): array
    {
        return [
            'private' => [ChatType::PRIVATE],
            'channel' => [ChatType::CHANNEL],
        ];
    }

    #[DataProvider('activeMembers')]
    public function test_creates_an_active_group_with_no_role_when_the_bot_can_send(string $memberClass): void
    {
        $group = app(TelegramGroupService::class)->syncMembership(
            $this->groupChat(-100, 'Logs'),
            new $memberClass,
        );

        $this->assertInstanceOf(TelegramGroup::class, $group);
        $this->assertSame('Logs', $group->title);
        $this->assertSame(-100, $group->chat_id);
        $this->assertSame(GroupRole::NONE, $group->role);
        $this->assertTrue($group->is_active);
    }

    public function test_stores_the_chat_id_when_the_group_has_no_title(): void
    {
        $group = app(TelegramGroupService::class)->syncMembership(
            $this->groupChat(-100, ''),
            new ChatMemberMember,
        );

        $this->assertSame('-100', $group->title);
    }

    #[DataProvider('removedMembers')]
    public function test_deactivates_the_group_when_the_bot_leaves_or_is_kicked(string $memberClass): void
    {
        $chat = $this->groupChat(-100, 'Logs');
        $service = app(TelegramGroupService::class);
        $service->syncMembership($chat, new ChatMemberMember);

        $group = $service->syncMembership($chat, new $memberClass);

        $this->assertFalse($group->is_active);
        $this->assertSame(1, TelegramGroup::query()->count());
    }

    public function test_keeps_the_role_and_ignore_list_when_the_bot_rejoins(): void
    {
        $chat = $this->groupChat(-100, 'Ops');
        $service = app(TelegramGroupService::class);
        $service->syncMembership($chat, new ChatMemberMember);
        $group = TelegramGroup::query()->first();
        $group->role = GroupRole::RECEIVER;
        $group->save();
        $messageType = MessageType::factory()->create();
        $group->ignoredMessageTypes()->attach($messageType);
        $service->syncMembership($chat, new ChatMemberBanned);

        $chat->title = 'Ops renamed';
        $service->syncMembership($chat, new ChatMemberAdministrator);
        $group->refresh();

        $this->assertTrue($group->is_active);
        $this->assertSame(GroupRole::RECEIVER, $group->role);
        $this->assertSame('Ops renamed', $group->title);
        $this->assertTrue($group->ignoredMessageTypes()->whereKey($messageType->id)->exists());
        $this->assertSame(1, TelegramGroup::query()->count());
    }

    public function test_deactivates_a_restricted_bot_that_cannot_send_messages(): void
    {
        $member = new ChatMemberRestricted;
        $member->is_member = true;
        $member->can_send_messages = false;

        $group = app(TelegramGroupService::class)->syncMembership(
            $this->groupChat(-100, 'Logs'),
            $member,
        );

        $this->assertFalse($group->is_active);
    }

    public function test_keeps_a_restricted_member_active_when_it_can_send_messages(): void
    {
        $member = new ChatMemberRestricted;
        $member->is_member = true;
        $member->can_send_messages = true;

        $group = app(TelegramGroupService::class)->syncMembership(
            $this->groupChat(-100, 'Logs'),
            $member,
        );

        $this->assertTrue($group->is_active);
        $this->assertSame(GroupRole::NONE, $group->role);
    }

    public function test_deactivates_a_restricted_bot_that_is_not_a_member(): void
    {
        $member = new ChatMemberRestricted;
        $member->is_member = false;
        $member->can_send_messages = true;

        $group = app(TelegramGroupService::class)->syncMembership(
            $this->groupChat(-100, 'Logs'),
            $member,
        );

        $this->assertFalse($group->is_active);
    }

    #[DataProvider('ignoredChatTypes')]
    public function test_does_not_store_private_chats_or_channels(ChatType $type): void
    {
        $chat = new Chat;
        $chat->id = 42;
        $chat->type = $type;
        $chat->title = 'Not a group';

        $group = app(TelegramGroupService::class)->syncMembership($chat, new ChatMemberMember);

        $this->assertNull($group);
        $this->assertSame(0, TelegramGroup::query()->count());
    }

    public function test_moves_the_group_to_the_supergroup_chat_id(): void
    {
        $group = TelegramGroup::factory()->receiver()->create([
            'chat_id' => -100,
        ]);

        app(TelegramGroupService::class)->relocate(-100, -100200300);

        $group->refresh();

        $this->assertSame(-100200300, $group->chat_id);
        $this->assertSame(GroupRole::RECEIVER, $group->role);
    }

    public function test_leaves_groups_unchanged_when_the_old_chat_is_unknown(): void
    {
        app(TelegramGroupService::class)->relocate(-1, -2);

        $this->assertSame(0, TelegramGroup::query()->count());
    }

    public function test_finds_a_group_by_chat_id(): void
    {
        $group = TelegramGroup::factory()->create([
            'chat_id' => -100123,
            'title' => 'Logs',
        ]);

        $result = app(TelegramGroupService::class)->findByChatIdOrTitle('-100123');

        $this->assertTrue($result->is($group));
    }

    public function test_finds_a_group_by_title(): void
    {
        $group = TelegramGroup::factory()->create([
            'title' => 'Logs',
        ]);

        $result = app(TelegramGroupService::class)->findByChatIdOrTitle('Logs');

        $this->assertTrue($result->is($group));
    }

    public function test_returns_a_collection_when_several_groups_share_the_title(): void
    {
        TelegramGroup::factory()->create(['title' => 'Logs']);
        TelegramGroup::factory()->create(['title' => 'Logs']);

        $result = app(TelegramGroupService::class)->findByChatIdOrTitle('Logs');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_returns_null_when_a_group_is_not_found(): void
    {
        $result = app(TelegramGroupService::class)->findByChatIdOrTitle('missing');

        $this->assertNull($result);
    }

    private function groupChat(int $id, string $title): Chat
    {
        $chat = new Chat;
        $chat->id = $id;
        $chat->type = ChatType::SUPERGROUP;
        $chat->title = $title;

        return $chat;
    }
}
