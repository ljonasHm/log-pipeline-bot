<?php

namespace App\Telegram\Handlers;

use App\Models\MessageType;
use App\Models\TelegramGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class GroupIgnoredMessageTypeCommand
{
    private const PER_PAGE = 20;

    public function __invoke(Nutgram $bot, string $groupId, string $page = '1'): void
    {
        $page = max(1, (int) $page);
        $group = TelegramGroup::query()->find((int) $groupId);

        if ($group === null) {
            $bot->answerCallbackQuery(
                text: 'Group not found.',
                show_alert: true,
            );

            return;
        }

        [$text, $keyboard] = $this->buildList($group, $page);

        $bot->editMessageText(
            text: $text,
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    /**
     * @return array{0: string, 1: InlineKeyboardMarkup}
     */
    public function buildList(TelegramGroup $group, int $page = 1): array
    {
        $page = max(1, $page);

        $ignoredMessageTypes = $group->ignoredMessageTypes()
            ->orderBy('id')
            ->paginate(self::PER_PAGE, page: $page);

        return [
            $this->buildText($ignoredMessageTypes),
            $this->buildKeyboard($group, $ignoredMessageTypes),
        ];
    }

    private function buildText(LengthAwarePaginator $messageTypes): string
    {
        if ($messageTypes->isEmpty()) {
            return 'No ignored types.';
        }

        $firstItem = $messageTypes->firstItem() ?? 1;

        $list = collect($messageTypes->items())
            ->values()
            ->map(function (MessageType $messageType, int $index) use ($firstItem): string {
                return ($firstItem + $index).'. '.$messageType->name;
            })
            ->implode("\n");

        $shown = $messageTypes->lastItem() ?? 0;
        $total = $messageTypes->total();

        return $list."\n\n{$shown}/{$total}";
    }

    private function buildKeyboard(TelegramGroup $group, LengthAwarePaginator $messageTypes): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();
        $groupId = $group->id;
        $page = $messageTypes->currentPage();
        $navButtons = [];

        if ($page > 1) {
            $navButtons[] = InlineKeyboardButton::make(
                'Previous',
                callback_data: 'group_ignored_message_type:'.$groupId.':'.($page - 1),
            );
        }

        if ($messageTypes->hasMorePages()) {
            $navButtons[] = InlineKeyboardButton::make(
                'Next',
                callback_data: 'group_ignored_message_type:'.$groupId.':'.($page + 1),
            );
        }

        if ($navButtons !== []) {
            $keyboard->addRow(...$navButtons);
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Add ignored message type',
                callback_data: 'add_group_ignored_message_type:'.$groupId,
            ),
        );

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Remove ignored message type',
                callback_data: 'remove_group_ignored_message_type:'.$groupId,
            ),
        );

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Back',
                callback_data: 'telegram_groups:1',
            ),
        );

        return $keyboard;
    }
}
