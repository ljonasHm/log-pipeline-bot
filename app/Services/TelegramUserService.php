<?php

namespace App\Services;

use App\Models\TelegramUser;

class TelegramUserService
{
    public function findOrCreate(
        int $telegramId,
        string $name,
        int $chatId
    ): TelegramUser {
        return TelegramUser::firstOrCreate(
            [
                'telegram_id' => $telegramId
            ],
            [
                'chat_id' => $chatId,
                'name' => $name
            ]
        );
    }
}