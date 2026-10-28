<?php

namespace App\Services;

use App\Models\TelegramGroup;
use Illuminate\Database\Eloquent\Collection;
use SergiX44\Nutgram\Telegram\Properties\ChatMemberStatus;
use SergiX44\Nutgram\Telegram\Properties\ChatType;
use SergiX44\Nutgram\Telegram\Types\Chat\Chat;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMember;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberRestricted;

class TelegramGroupService
{
    public function syncMembership(Chat $chat, ChatMember $member): ?TelegramGroup
    {
        if (! $this->isGroupChat($chat)) {
            return null;
        }

        return TelegramGroup::query()->updateOrCreate(
            [
                'chat_id' => $chat->id,
            ],
            [
                'title' => $this->title($chat),
                'is_active' => $this->canReceive($member),
            ],
        );
    }

    public function relocate(int $fromChatId, int $toChatId): void
    {
        if ($fromChatId === $toChatId) {
            return;
        }

        $group = TelegramGroup::query()->where('chat_id', $fromChatId)->first();

        if ($group === null) {
            return;
        }

        $group->chat_id = $toChatId;
        $group->save();
    }

    /**
     * @return TelegramGroup|Collection<int, TelegramGroup>|null
     */
    public function findByChatIdOrTitle(string $input): TelegramGroup|Collection|null
    {
        if ($this->isChatId($input)) {
            $group = TelegramGroup::query()->where('chat_id', $input)->first();

            if ($group !== null) {
                return $group;
            }
        }

        /** @var Collection<int, TelegramGroup> $groups */
        $groups = TelegramGroup::query()->where('title', $input)->get();

        if ($groups->count() > 1) {
            return $groups;
        }

        return $groups->first();
    }

    private function title(Chat $chat): string
    {
        $title = trim((string) $chat->title);

        if ($title === '') {
            return (string) $chat->id;
        }

        return $title;
    }

    private function canReceive(ChatMember $member): bool
    {
        $status = $member->status instanceof ChatMemberStatus
            ? $member->status
            : ChatMemberStatus::tryFrom((string) $member->status);

        if (in_array($status, [
            ChatMemberStatus::CREATOR,
            ChatMemberStatus::ADMINISTRATOR,
            ChatMemberStatus::MEMBER,
        ], true)) {
            return true;
        }

        if ($status !== ChatMemberStatus::RESTRICTED || ! $member instanceof ChatMemberRestricted) {
            return false;
        }

        return $member->is_member && $member->can_send_messages;
    }

    private function isGroupChat(Chat $chat): bool
    {
        $type = $chat->type instanceof ChatType
            ? $chat->type
            : ChatType::tryFrom((string) $chat->type);

        return $type === ChatType::GROUP || $type === ChatType::SUPERGROUP;
    }

    private function isChatId(string $input): bool
    {
        return preg_match('/^-?\d+$/', $input) === 1;
    }
}
