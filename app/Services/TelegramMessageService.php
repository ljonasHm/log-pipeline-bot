<?php

namespace App\Services;

use App\Models\Message;
use App\Models\TelegramUser;
use SergiX44\Nutgram\Nutgram;

class TelegramMessageService
{
    public function __construct(
        private Nutgram $bot
    ) {}

    public function sendToUser(
        TelegramUser $user,
        Message $message
    ): void {

        $typeLabel = $message->messageType?->title ?? $message->type;

        $text = "Тип: {$typeLabel}\n\n{$message->text}";

        $this->bot->sendMessage(
            text: $text,
            chat_id: $user->chat_id
        );
    }
}
