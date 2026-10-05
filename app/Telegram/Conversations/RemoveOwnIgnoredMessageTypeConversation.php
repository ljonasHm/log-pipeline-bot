<?php

namespace App\Telegram\Conversations;

use App\Enums\IgnoredMessageTypeSource;
use App\Models\MessageType;
use App\Models\TelegramUser;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class RemoveOwnIgnoredMessageTypeConversation extends Conversation
{
    protected ?int $telegramUserId = null;

    public function start(Nutgram $bot): void
    {
        $telegramId = $bot->user()?->id;

        $user = $telegramId !== null
            ? TelegramUser::findByTelegramId($telegramId)
            : null;

        if ($user === null) {
            $bot->sendMessage(
                'User not found.'
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

        $existing = $user->ignoredMessageTypes()
            ->whereKey($messageType->id)
            ->first();

        if ($existing === null) {
            $bot->sendMessage(
                'This message type is not in your ignore list.'
            );

            $this->end();

            return;
        }

        $source = IgnoredMessageTypeSource::tryFrom($existing->pivot->source);

        if ($user->isReceiver() && $source === IgnoredMessageTypeSource::ADMIN) {
            $bot->sendMessage(
                'This message type was added by an admin and cannot be removed.'
            );

            $this->end();

            return;
        }

        $user->ignoredMessageTypes()->detach($messageType->id);

        $bot->sendMessage(
            "Message type {$messageType->name} removed from your ignore list."
        );

        $this->end();
    }
}
