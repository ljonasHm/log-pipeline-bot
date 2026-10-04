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

        if ($user->isAdmin() || $user->isReceiver()) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    'Ignored types',
                    callback_data: 'own_ignored_message_type:1',
                ),
            );
        }

        $text = $this->buildWelcomeText($user->name, $user->role);

        if ($bot->isCallbackQuery()) {
            $bot->editMessageText(
                text: $text,
                reply_markup: $keyboard,
            );

            $bot->answerCallbackQuery();

            return;
        }

        $bot->sendMessage(
            text: $text,
            reply_markup: $keyboard
        );
    }

    private function buildWelcomeText(string $name, UserRole $role): string
    {
        $suffix = match ($role) {
            UserRole::ADMIN => 'you have permission to receive messages and manage the bot.',
            UserRole::RECEIVER => 'you have permission to receive messages and edit your own ignore list.',
            UserRole::NONE => 'you do not have permission to receive messages or manage the bot. Contact the resource administrator to get access.',
        };

        return "{$name}, {$suffix}";
    }
}
