<?php

namespace App\Telegram\Conversations;

use App\Enums\UserRole;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

use App\Models\TelegramUser;

class ChangeUserRoleConversation extends Conversation
{
    public ?int $targetTelegramId = null;

    public function start(Nutgram $bot): void 
    {
        $telegramId = $bot->user()->id;

        $user = TelegramUser::query()
            ->where('telegram_id', $telegramId)
            ->first();

        if (!$user || $user->role !== UserRole::ADMIN) {
            $bot->sendMessage(
                'No rules.'
            );

            $this->end();

            return;
        }

        $bot->sendMessage(
            'Enter the user`s Telegram ID'
        );

        $this->next('askTelegramId');
    }

    public function askTelegramId(Nutgram $bot): void 
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

        $this->targetTelegramId = (int) $telegramId;

        $bot->sendMessage(
            text: "User:  {$user->name}\n"
                . "Telegram ID: {$user->telegram_id}\n"
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

        $role = match ($bot->callbackQuery()->data) {
            'role:admin' => UserRole::ADMIN,
            'role:receiver' => UserRole::RECEIVER,
            'role:none' => UserRole::NONE,
            default => null,
        };

        if (!$role) {
            return;
        }

        $user = TelegramUser::query()
            ->where('telegram_id', $this->targetTelegramId)
            ->first();

        if (!$user) {
            $bot->answerCallbackQuery(
                text: 'User not found.',
                show_alert: true
            );

            $this->end();

            return;
        }

        $user->role = $role;
        $user->save();

        $bot->answerCallbackQuery(
            text: 'Role changed.',
        );

        $bot->sendMessage(
            "User {$user->name} role has been changed to {$role->value}"
        );

        $this->end();
    }
}
