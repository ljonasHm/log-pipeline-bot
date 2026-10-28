<?php

namespace App\Telegram\Handlers;

use App\Services\TelegramGroupService;
use SergiX44\Nutgram\Nutgram;

class SyncTelegramGroupHandler
{
    public function __construct(
        private TelegramGroupService $telegramGroupService,
    ) {}

    public function __invoke(Nutgram $bot): void
    {
        $update = $bot->chatMember();

        if ($update === null) {
            return;
        }

        $this->telegramGroupService->syncMembership(
            $update->chat,
            $update->new_chat_member,
        );
    }
}
