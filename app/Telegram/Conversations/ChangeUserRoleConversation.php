<?php

namespace App\Telegram\Conversations;

use App\Enums\UserRole;
use App\Models\TelegramUser;
use App\Services\TelegramUserService;
use Illuminate\Database\Eloquent\Collection;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class ChangeUserRoleConversation extends Conversation
{
    public ?int $telegramUserId = null;

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            'Enter the user chat_id or name.'
        );

        $this->next('askUser');
    }

    public function askUser(Nutgram $bot): void
    {
        $input = trim($bot->message()?->text ?? '');

        if ($input === '') {
            $bot->sendMessage(
                'Enter the user chat_id or name.'
            );

            return;
        }

        $user = $this->resolveUser($bot, $input);

        if ($user === null) {
            return;
        }

        if ($user->isPermanentAdmin()) {
            $bot->sendMessage(
                'This user\'s role cannot be changed.'
            );

            $this->end();

            return;
        }

        $this->telegramUserId = $user->id;

        $bot->sendMessage(
            text: "User:  {$user->name}\n"
                ."Chat ID: {$user->chat_id}\n"
                .'Select a new role',
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
        if (! $bot->isCallbackQuery()) {
            return;
        }

        $role = match ($bot->callbackQuery()->data) {
            'role:admin' => UserRole::ADMIN,
            'role:receiver' => UserRole::RECEIVER,
            'role:none' => UserRole::NONE,
            default => null,
        };

        if (! $role) {
            return;
        }

        $user = TelegramUser::query()->find($this->telegramUserId);

        if (! $user) {
            $bot->answerCallbackQuery(
                text: 'User not found.',
                show_alert: true
            );

            $this->end();

            return;
        }

        if ($user->isPermanentAdmin()) {
            $bot->answerCallbackQuery(
                text: 'This user\'s role cannot be changed.',
                show_alert: true,
            );

            $this->end();

            return;
        }

        $user->role = $role;
        $user->save();

        $bot->answerCallbackQuery(
            text: 'Role changed.',
        );

        $bot->editMessageText(
            text: "User {$user->name} role has been changed to {$role->value}",
            reply_markup: InlineKeyboardMarkup::make(),
        );

        $this->end();
    }

    private function resolveUser(Nutgram $bot, string $input): ?TelegramUser
    {
        $result = $bot->getContainer()
            ->get(TelegramUserService::class)
            ->findByChatIdOrName($input);

        if ($result instanceof Collection) {
            $bot->sendMessage(
                'Several users have this name. Enter the chat_id.'
            );

            return null;
        }

        if ($result === null) {
            $bot->sendMessage(
                'User not found.'
            );

            return null;
        }

        return $result;
    }
}
