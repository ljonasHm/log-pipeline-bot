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
    ) {}

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
                    'Telegram users',
                    callback_data: 'telegram_users:1',
                ),
                InlineKeyboardButton::make(
                    'Servers',
                    callback_data: 'servers:1'
                ),
            );
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    'API keys',
                    callback_data: 'api_keys:1'
                ),
                InlineKeyboardButton::make(
                    'Message types',
                    callback_data: 'message_types:1'
                ),
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Ignore list',
                callback_data: 'add_own_ignored_message_type',
            ),
        );

        $bot->sendMessage(
            text: $user->name,
            reply_markup: $keyboard
        );
    }
}
