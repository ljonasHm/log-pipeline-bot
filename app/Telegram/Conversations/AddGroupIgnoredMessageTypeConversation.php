<?php

namespace App\Telegram\Conversations;

use App\Models\MessageType;
use App\Models\TelegramGroup;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class AddGroupIgnoredMessageTypeConversation extends Conversation
{
    protected ?int $telegramGroupId = null;

    public function start(Nutgram $bot, string $groupId): void
    {
        $group = TelegramGroup::query()->find((int) $groupId);

        if ($group === null) {
            $bot->sendMessage(
                'Group not found.'
            );

            $this->end();

            return;
        }

        $this->telegramGroupId = $group->id;

        $bot->sendMessage(
            'Enter the message type key.'
        );

        $this->next('askTypeKey');
    }

    public function askTypeKey(Nutgram $bot): void
    {
        $typeKey = trim($bot->message()?->text ?? '');

        $messageType = MessageType::query()->withName($typeKey)->first();

        if ($messageType === null) {
            $bot->sendMessage(
                'Message type with this key not found.'
            );

            return;
        }

        $group = TelegramGroup::query()->find($this->telegramGroupId);

        if ($group === null) {
            $bot->sendMessage(
                'Group not found.'
            );

            $this->end();

            return;
        }

        if ($group->ignoredMessageTypes()->whereKey($messageType->id)->exists()) {
            $bot->sendMessage(
                'This message type is already in the ignore list.'
            );

            $this->end();

            return;
        }

        $group->ignoredMessageTypes()->syncWithoutDetaching([
            $messageType->id,
        ]);

        $bot->sendMessage(
            "Message type {$messageType->name} added to the ignore list of {$group->title}."
        );

        $this->end();
    }
}
