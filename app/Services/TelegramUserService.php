<?php

namespace App\Services;

use App\Models\TelegramUser;
use Illuminate\Database\Eloquent\Collection;

class TelegramUserService
{
    public function findOrCreate(
        int $telegramId,
        string $name,
        int $chatId
    ): TelegramUser {
        return TelegramUser::firstOrCreate(
            [
                'telegram_id' => $telegramId,
            ],
            [
                'chat_id' => $chatId,
                'name' => $name,
            ]
        );
    }

    /**
     * @return TelegramUser|Collection<int, TelegramUser>|null
     */
    public function findByChatIdOrName(string $input): TelegramUser|Collection|null
    {
        if ($this->isChatId($input)) {
            $user = TelegramUser::query()->where('chat_id', $input)->first();

            if ($user !== null) {
                return $user;
            }
        }

        /** @var Collection<int, TelegramUser> $users */
        $users = TelegramUser::query()->where('name', $input)->get();

        if ($users->count() > 1) {
            return $users;
        }

        return $users->first();
    }

    private function isChatId(string $input): bool
    {
        return preg_match('/^-?\d+$/', $input) === 1;
    }
}
