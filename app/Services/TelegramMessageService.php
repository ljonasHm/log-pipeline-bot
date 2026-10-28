<?php

namespace App\Services;

use App\Models\Message;
use App\Models\TelegramGroup;
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
        $this->send($user->chat_id, $message);
    }

    public function sendToGroup(
        TelegramGroup $group,
        Message $message
    ): void {
        $this->send($group->chat_id, $message);
    }

    private function send(int|string $chatId, Message $message): void
    {
        $typeLabel = $message->messageType?->title ?? $message->type;
        $serverName = $message->server->name;

        $text = "{$serverName}: {$typeLabel}\n\n{$message->text}";

        $this->bot->sendMessage(
            text: $text,
            chat_id: $chatId
        );
    }
}
