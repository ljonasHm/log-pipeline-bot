<?php

namespace App\Telegram\Conversations;

use App\Enums\IgnoredMessageTypeSource;
use App\Models\MessageType;
use App\Models\TelegramUser;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class AddUserIgnoredMessageTypeConversation extends Conversation
{
    protected ?int $telegramUserId = null;

    public function start(Nutgram $bot, string $userId): void
    {
        $user = TelegramUser::query()->find((int) $userId);

        if ($user === null) {
            $bot->sendMessage(
                'User not found.'
            );

            $this->end();

            return;
        }

        if ($user->isPermanentAdmin()) {
            $bot->sendMessage(
                'This user\'s ignore list cannot be changed.'
            );

            $this->end();

            return;
        }

        $this->telegramUserId = $user->id;

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

        $user = TelegramUser::query()->find($this->telegramUserId);

        if ($user === null) {
            $bot->sendMessage(
                'User not found.'
            );

            $this->end();

            return;
        }

        if ($user->isPermanentAdmin()) {
            $bot->sendMessage(
                'This user\'s ignore list cannot be changed.'
            );

            $this->end();

            return;
        }

        if ($user->ignoredMessageTypes()->whereKey($messageType->id)->exists()) {
            $bot->sendMessage(
                'This message type is already in the ignore list.'
            );

            $this->end();

            return;
        }

        $user->ignoredMessageTypes()->syncWithoutDetaching([
            $messageType->id => ['source' => IgnoredMessageTypeSource::ADMIN->value],
        ]);

        $bot->sendMessage(
            "Message type {$messageType->name} added to the ignore list of {$user->name}."
        );

        $this->end();
    }
}
