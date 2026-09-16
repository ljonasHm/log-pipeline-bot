<?php

namespace App\Telegram\Conversations;

use App\Enums\UserRole;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

use App\Models\TelegramUser;
use App\Enums\UserRole;

class ChangeUserRoleConversation extends Conversation
{
    protected ?string $step = 'askTelegramId';

    public ?int $telegramId = null;

    public function askTelegramId(Nutgram $bot): void 
    {
        $bot->sendMessage(
            'Enter the user`s Telegram ID'
        );

        $this->next('askRole');
    }

    public function askRole(Nutgram $bot): void 
    {
        $telegramId = $bot->message()?->text;

        if (!ctype_digit($telegramId ?? '')) {
            $bot->sendMessage(
                'Telegram ID must consist of numbers only.'
            );
            
            return;
        }

        $user = TelegramUser::query()
            ->where('telegram_id', $telegramId)
            ->first();

        if (!$user) {
            $bot->sendMessage(
                'User with this Telegram ID not found'
            );

            return;
        }

        $this->telegramId = (int) $telegramId;

        $bot->sendMessage(
            text: "User:  {$user->name}\n"
                . "Telegram ID: {$user->telegramId}\n"
                . "Select a new role",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        'Admin',
                        callback_data: 'role:admin'
                    ),
                    InlineKeyboardButton::make(
                        'Receiver',
                        callback_data: 'role:receiver',
                    ),
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        'Without role',
                        callback_data: 'role:none',
                    ),
                ),
        );

        $this->next('changeRole');
    }

    public function changeRole(Nutgram $bot): void
    {
        if (!$bot->isCallbackQuery()) {
            return;
        }

        $callbackData = $bot->callbackQuery()->data;

        $role = match ($callbackData) {
            'role:admin' => UserRole::ADMIN,
            'role:receiver' => UserRole::RECEIVER,
            'role:none' => UserRole::NONE,
            default => null,
        };

        if (!$role) {
            return;
        }

        $user = TelegramUser::query()
            ->where('telegram_id', $this->telegramId)
            ->first();

        if (!$user) {
            $bot->answerCallbackQuery(
                text: 'User not found.',
            );

            $this->end();

            return;
        }

        $user->update([
            'role' => $role,
        ]);

        $bot->answerCallbackQuery(
            text: 'Role changes.',
        );

        $bot->sendMessage(
            "User {$user->name} role has been changed to {$role->value}"
        );

        $this->end();
    }
}
