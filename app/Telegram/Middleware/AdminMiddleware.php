<?php

namespace App\Telegram\Middleware;

use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Facades\Gate;
use SergiX44\Nutgram\Middleware\Link;

use App\Models\TelegramUser;

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

        if (!$telegramUser || Gate::forUser($telegramUser)->denies('admin')) {
            $bot->sendMessage(
                'No rules.'
            );

            return;
        }

        $next($bot);
    }

    private function isStartCommand(Nutgram $bot): bool
    {
        return preg_match(
            '/^\/start(?:@\w+)?(?:\s|$)/i',
            $bot->message()?->text ?? ''
        ) === 1;
    }
}
