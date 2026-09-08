<?php

namespace App\Services;

use SergiX44\Nutgram\Nutgram;
use App\Models\TelegramUser;
use App\Models\Message;

class TelegramMessageService {
    public function __construct(
        private Nutgram $bot
    ) {

    }

    public function sendToUser(
        TelegramUser $user,
        Message $message
    ): void {

        $text = "Тип: {$message->type}\n\n{$message->text}";

        $this->bot->sendMessage(
            text: $text,
            chat_id: $user->chat_id
        );
    }
}