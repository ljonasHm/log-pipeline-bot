<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramGroupService;
use SergiX44\Nutgram\Nutgram;

class MigrateTelegramGroupHandler
{
    public function __construct(
        private TelegramGroupService $telegramGroupService,
    ) {}

    public function __invoke(Nutgram $bot): void
    {
        $message = $bot->message();

        if ($message?->migrate_to_chat_id === null) {
            return;
        }

        $this->telegramGroupService->relocate(
            $message->chat->id,
            $message->migrate_to_chat_id,
        );
    }
}
