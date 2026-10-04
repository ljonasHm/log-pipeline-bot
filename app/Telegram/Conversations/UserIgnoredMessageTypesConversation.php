<?php

namespace App\Telegram\Conversations;

use App\Models\TelegramUser;
use App\Services\TelegramUserService;
use App\Telegram\Handlers\UserIgnoredMessageTypeCommand;
use Illuminate\Database\Eloquent\Collection;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class UserIgnoredMessageTypesConversation extends Conversation
{
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

        $user = $this->resolveUser($bot, $input);

        if ($user === null) {
            return;
        }

        [$text, $keyboard] = app(UserIgnoredMessageTypeCommand::class)->buildList($user);

        $bot->sendMessage(
            text: $text,
            reply_markup: $keyboard,
        );

        $this->end();
    }

    private function resolveUser(Nutgram $bot, string $input): ?TelegramUser
    {
        $result = $bot->getContainer()
            ->get(TelegramUserService::class)
            ->findByChatIdOrName($input);

        if ($result instanceof Collection) {
            $bot->sendMessage(
                'Several users have this name. Enter the chat_id.'
            );

            return null;
        }

        if ($result === null) {
            $bot->sendMessage(
                'User not found.'
            );

            return null;
        }

        return $result;
    }
}
