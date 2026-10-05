<?php

namespace App\Telegram\Handlers;

use App\Enums\IgnoredMessageTypeSource;
use App\Models\MessageType;
use App\Models\TelegramUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class OwnIgnoredMessageTypeCommand
{
    private const PER_PAGE = 20;

    public function __invoke(Nutgram $bot, string $page = '1'): void
    {
        $page = max(1, (int) $page);
        $telegramId = $bot->user()?->id;
        $user = $telegramId !== null
            ? TelegramUser::findByTelegramId($telegramId)
            : null;

        if ($user === null) {
            $bot->answerCallbackQuery(
                text: 'User not found.',
                show_alert: true,
            );

            return;
        }

        $query = $user->ignoredMessageTypes()->orderBy('id');

        if ($user->isReceiver()) {
            $query->wherePivot('source', IgnoredMessageTypeSource::USER->value);
        }

        $ignoredMessageTypes = $query->paginate(self::PER_PAGE, page: $page);

        $text = $this->buildText($ignoredMessageTypes);
        $keyboard = $this->buildKeyboard($ignoredMessageTypes);

        $bot->editMessageText(
            text: $text,
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    private function buildText(LengthAwarePaginator $messageTypes): string
    {
        if ($messageTypes->isEmpty()) {
            return 'No ignored types.';
        }

        $firstItem = $messageTypes->firstItem() ?? 1;

        $list = collect($messageTypes->items())
            ->values()
            ->map(fn (MessageType $messageType, int $index) => ($firstItem + $index).'. '.$messageType->name)
            ->implode("\n");

        $shown = $messageTypes->lastItem() ?? 0;
        $total = $messageTypes->total();

        return $list."\n\n{$shown}/{$total}";
    }

    private function buildKeyboard(LengthAwarePaginator $messageTypes): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();
        $page = $messageTypes->currentPage();
        $navButtons = [];

        if ($page > 1) {
            $navButtons[] = InlineKeyboardButton::make(
                'Previous',
                callback_data: 'own_ignored_message_type:'.($page - 1),
            );
        }

        if ($messageTypes->hasMorePages()) {
            $navButtons[] = InlineKeyboardButton::make(
                'Next',
                callback_data: 'own_ignored_message_type:'.($page + 1),
            );
        }

        if ($navButtons !== []) {
            $keyboard->addRow(...$navButtons);
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Add ignored message type',
                callback_data: 'add_own_ignored_message_type',
            ),
        );

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Remove ignored message type',
                callback_data: 'remove_own_ignored_message_type',
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
