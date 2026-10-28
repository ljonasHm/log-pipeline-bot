<?php

namespace App\Telegram\Conversations;

use App\Enums\GroupRole;
use App\Models\TelegramGroup;
use App\Services\TelegramGroupService;
use Illuminate\Database\Eloquent\Collection;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class ChangeGroupRoleConversation extends Conversation
{
    public ?int $telegramGroupId = null;

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            'Enter the group chat_id or title.'
        );

        $this->next('askGroup');
    }

    public function askGroup(Nutgram $bot): void
    {
        $input = trim($bot->message()?->text ?? '');

        if ($input === '') {
            $bot->sendMessage(
                'Enter the group chat_id or title.'
            );

            return;
        }

        $group = $this->resolveGroup($bot, $input);

        if ($group === null) {
            return;
        }

        $this->telegramGroupId = $group->id;

        $bot->sendMessage(
            text: "Group: {$group->title}\n"
                ."Chat ID: {$group->chat_id}\n"
                .'Select a new role',
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        'Receiver',
                        callback_data: 'group_role:receiver',
                    ),
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        'Without role',
                        callback_data: 'group_role:none',
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
            'group_role:receiver' => GroupRole::RECEIVER,
            'group_role:none' => GroupRole::NONE,
            default => null,
        };

        if (! $role) {
            return;
        }

        $group = TelegramGroup::query()->find($this->telegramGroupId);

        if (! $group) {
            $bot->answerCallbackQuery(
                text: 'Group not found.',
                show_alert: true,
            );

            $this->end();

            return;
        }

        $group->role = $role;
        $group->save();

        $bot->answerCallbackQuery(
            text: 'Role changed.',
        );

        $bot->editMessageText(
            text: "Group {$group->title} role has been changed to {$role->value}",
            reply_markup: InlineKeyboardMarkup::make(),
        );

        $this->end();
    }

    private function resolveGroup(Nutgram $bot, string $input): ?TelegramGroup
    {
        $result = $bot->getContainer()
            ->get(TelegramGroupService::class)
            ->findByChatIdOrTitle($input);

        if ($result instanceof Collection) {
            $bot->sendMessage(
                'Several groups have this title. Enter the chat_id.'
            );

            return null;
        }

        if ($result === null) {
            $bot->sendMessage(
                'Group not found.'
            );

            return null;
        }

        return $result;
    }
}
