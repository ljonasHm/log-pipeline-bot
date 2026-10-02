<?php

namespace App\Telegram\Handlers;

use App\Models\MessageType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class MessageTypesCommand
{
    private const PER_PAGE = 10;

    public function __invoke(Nutgram $bot, string $page = '1'): void
    {
        $page = max(1, (int) $page);

        $messageTypes = MessageType::query()
            ->orderBy('id')
            ->paginate(self::PER_PAGE, page: $page);

        $text = $this->buildText($messageTypes);
        $keyboard = $this->buildKeyboard($messageTypes);

        $bot->editMessageText(
            text: $text,
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    private function buildText(LengthAwarePaginator $messageTypes): string
    {
        if ($messageTypes->isEmpty()) {
            return 'No message types.';
        }

        $firstItem = $messageTypes->firstItem() ?? 1;

        $list = collect($messageTypes->items())
            ->values()
            ->map(fn (MessageType $messageType, int $index) => ($firstItem + $index).'. '.$messageType->name.' — '.$messageType->title)
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
                callback_data: 'message_types:'.($page - 1),
            );
        }

        if ($messageTypes->hasMorePages()) {
            $navButtons[] = InlineKeyboardButton::make(
                'Next',
                callback_data: 'message_types:'.($page + 1),
            );
        }

        if ($navButtons !== []) {
            $keyboard->addRow(...$navButtons);
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Add message type',
                callback_data: 'add_message_type',
            ),
        );

        return $keyboard;
    }
}
