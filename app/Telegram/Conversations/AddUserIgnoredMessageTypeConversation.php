<?php

namespace App\Telegram\Conversations;

use App\Models\MessageType;
use App\Models\TelegramUser;
use Illuminate\Database\Eloquent\Collection;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class AddUserIgnoredMessageTypeConversation extends Conversation
{
    protected ?int $telegramUserId = null;

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            'Enter the user chat_id or name.'
        );

        $this->next('askUser');
    }

    public function askUser(Nutgram $bot): void
    {
        $input = trim($bot->message()?->text ?? '');

        if ($input === '') {
            $bot->sendMessage(
                'Enter the user chat_id or name.'
            );

            return;
        }

        $user = $this->findUser($bot, $input);

        if ($user === null) {
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

        if ($user->ignoredMessageTypes()->whereKey($messageType->id)->exists()) {
            $bot->sendMessage(
                'This message type is already in the ignore list.'
            );

            $this->end();

            return;
        }

        $user->ignoredMessageTypes()->syncWithoutDetaching([$messageType->id]);

        $bot->sendMessage(
            "Message type {$messageType->name} added to the ignore list of {$user->name}."
        );

        $this->end();
    }

    private function findUser(Nutgram $bot, string $input): ?TelegramUser
    {
        if ($this->isChatId($input)) {
            $user = TelegramUser::query()->where('chat_id', $input)->first();

            if ($user !== null) {
                return $user;
            }
        }

        /** @var Collection<int, TelegramUser> $users */
        $users = TelegramUser::query()->where('name', $input)->get();

        if ($users->count() > 1) {
            $bot->sendMessage(
                'Several users have this name. Enter the chat_id.'
            );

            return null;
        }

        $user = $users->first();

        if ($user === null) {
            $bot->sendMessage(
                'User not found.'
            );

            return null;
        }

        return $user;
    }

    private function isChatId(string $input): bool
    {
        return preg_match('/^-?\d+$/', $input) === 1;
    }
}
