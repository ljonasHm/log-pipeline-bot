<?php

namespace App\Telegram\Middleware;

use App\Models\TelegramUser;
use App\Telegram\Conversations\AddOwnIgnoredMessageTypeConversation;
use Illuminate\Support\Facades\Gate;
use SergiX44\Nutgram\Middleware\Link;
use SergiX44\Nutgram\Nutgram;

class AdminMiddleware
{
    public function __invoke(Nutgram $bot, Link $next): void
    {
        if ($this->isStartCommand($bot)) {
            $next($bot);

            return;
        }

        $telegramId = $bot->user()?->id;

        $telegramUser = $telegramId !== null
            ? TelegramUser::findByTelegramId($telegramId)
            : null;


        if ($telegramUser === null) {
            $bot->sendMessage(
                'No rules.'
            );

            return;
        }

        if ($this->isOwnIgnoreList($bot) && Gate::forUser($telegramUser)->allows('change-own-ignore')) {
            $next($bot);

            return;
        }

        if (Gate::forUser($telegramUser)->denies('manage')) {
            $bot->sendMessage(
                'No rules.'
            );

            return;
        }

        $next($bot);
    }

    private function isOwnIgnoreList(Nutgram $bot): bool
    {
        if ($bot->callbackQuery()?->data === 'add_own_ignored_message_type') {
            return true;
        }

        $conversation = $bot->currentConversation(
            $bot->userId(),
            $bot->chatId(),
            $bot->messageThreadId(),
        );

        return $conversation instanceof AddOwnIgnoredMessageTypeConversation;
    }

    private function isStartCommand(Nutgram $bot): bool
    {
        if ($bot->callbackQuery()?->data === 'start') {
            return true;
        }

        return preg_match(
            '/^\/start(?:@\w+)?(?:\s|$)/i',
            $bot->message()?->text ?? ''
        ) === 1;
    }
}
