<?php

namespace App\Telegram\Handlers;

use App\Enums\GroupRole;
use App\Models\TelegramGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class TelegramGroupsCommand
{
    private const PER_PAGE = 10;

    public function __invoke(Nutgram $bot, string $page = '1'): void
    {
        $page = max(1, (int) $page);

        $groups = TelegramGroup::query()
            ->orderByRaw('CASE WHEN role = ? THEN 1 WHEN role = ? THEN 2 ELSE 3 END', [
                GroupRole::RECEIVER->value,
                GroupRole::NONE->value,
            ])
            ->orderBy('id')
            ->paginate(self::PER_PAGE, page: $page);

        $text = $this->buildText($groups);
        $keyboard = $this->buildKeyboard($groups);

        $bot->editMessageText(
            text: $text,
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    private function buildText(LengthAwarePaginator $groups): string
    {
        if ($groups->isEmpty()) {
            return 'No Telegram groups.';
        }

        $firstItem = $groups->firstItem() ?? 1;

        $list = collect($groups->items())
            ->values()
            ->map(function (TelegramGroup $group, int $index) use ($firstItem): string {
                $status = $group->is_active ? 'active' : 'inactive';

                return ($firstItem + $index).'. '.$group->title.' — '.$group->role->value.' '.$group->chat_id.' — '.$status;
            })
            ->implode("\n");

        $shown = $groups->lastItem() ?? 0;
        $total = $groups->total();

        return $list."\n\n{$shown}/{$total}";
    }

    private function buildKeyboard(LengthAwarePaginator $groups): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();
        $page = $groups->currentPage();
        $navButtons = [];

        if ($page > 1) {
            $navButtons[] = InlineKeyboardButton::make(
                'Previous',
                callback_data: 'telegram_groups:'.($page - 1),
            );
        }

        if ($groups->hasMorePages()) {
            $navButtons[] = InlineKeyboardButton::make(
                'Next',
                callback_data: 'telegram_groups:'.($page + 1),
            );
        }

        if ($navButtons !== []) {
            $keyboard->addRow(...$navButtons);
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Change group role',
                callback_data: 'change_group_role',
            ),
        );

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Group ignored types',
                callback_data: 'group_ignored_types',
            ),
        );

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Back',
                callback_data: 'start',
            ),
        );

        return $keyboard;
    }
}
