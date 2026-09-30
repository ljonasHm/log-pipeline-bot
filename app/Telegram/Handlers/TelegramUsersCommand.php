<?php

namespace App\Telegram\Handlers;

use App\Models\TelegramUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class TelegramUsersCommand
{
    private const PER_PAGE = 10;

    public function __invoke(Nutgram $bot, string $page = '1'): void
    {
        $page = max(1, (int) $page);

        $users = TelegramUser::query()
            ->orderBy('id')
            ->paginate(self::PER_PAGE, page: $page);

        $text = $this->buildText($users);
        $keyboard = $this->buildKeyboard($users);

        $bot->editMessageText(
            text: $text,
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    private function buildText(LengthAwarePaginator $users): string
    {
        if ($users->isEmpty()) {
            return 'No Telegram users.';
        }

        $firstItem = $users->firstItem() ?? 1;

        $list = collect($users->items())
            ->values()
            ->map(fn (TelegramUser $user, int $index) => ($firstItem + $index).'. '.$user->name.' — '.$user->chat_id)
            ->implode("\n");

        $shown = $users->lastItem() ?? 0;
        $total = $users->total();

        return $list."\n\n{$shown}/{$total}";
    }

    private function buildKeyboard(LengthAwarePaginator $users): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();
        $page = $users->currentPage();
        $navButtons = [];

        if ($page > 1) {
            $navButtons[] = InlineKeyboardButton::make(
                'Previous',
                callback_data: 'telegram_users:'.($page - 1),
            );
        }

        if ($users->hasMorePages()) {
            $navButtons[] = InlineKeyboardButton::make(
                'Next',
                callback_data: 'telegram_users:'.($page + 1),
            );
        }

        if ($navButtons !== []) {
            $keyboard->addRow(...$navButtons);
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Change user role',
                callback_data: 'change_user_role',
            ),
        );

        return $keyboard;
    }
}
