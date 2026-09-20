<?php

namespace App\Telegram\Handlers;

use App\Enums\UserRole;
use App\Services\TelegramUserService;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

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
        
        $keyboard = InlineKeyboardMarkup::make();

        if ($user->role === UserRole::ADMIN) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    'Change user role',
                    callback_data: 'change_user_role',
                ),
                InlineKeyboardButton::make(
                    'Add server',
                    callback_data: 'add_server'
                )
            );
        }

        $bot->sendMessage(
            text: $user->name,
            reply_markup: $keyboard
        );
    }
}