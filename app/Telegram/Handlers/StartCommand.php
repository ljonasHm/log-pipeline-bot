<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramUserService;
use SergiX44\Nutgram\Nutgram;

class StartCommand
{
    public function __construct(
        private TelegramUserService $telegramUserService,
    ) {
    }

    public function __invoke(Nutgram $bot): void
    {
        $telegramUser = $bot->user();
        $chat = $bot->chat();

        $user = $this->telegramUserService->findOrCreate(
            $telegramUser->id,
            $telegramUser->first_name,
            $chat->id
        );

        $bot->sendMessage(
            $user->name
        );
    }
}