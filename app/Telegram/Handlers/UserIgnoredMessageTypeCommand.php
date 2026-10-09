<?php

namespace App\Telegram\Handlers;

use App\Models\MessageType;
use App\Models\TelegramUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class UserIgnoredMessageTypeCommand
{
    private const PER_PAGE = 20;

    public function __invoke(Nutgram $bot, string $userId, string $page = '1'): void
    {
        $page = max(1, (int) $page);
        $user = TelegramUser::query()->find((int) $userId);

        if ($user === null) {
            $bot->answerCallbackQuery(
                text: 'User not found.',
                show_alert: true,
            );

            return;
        }

        [$text, $keyboard] = $this->buildList($user, $page);

        $bot->editMessageText(
            text: $text,
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    /**
     * @return array{0: string, 1: InlineKeyboardMarkup}
     */
    public function buildList(TelegramUser $user, int $page = 1): array
    {
        $page = max(1, $page);

        $ignoredMessageTypes = $user->ignoredMessageTypes()
            ->orderBy('id')
            ->paginate(self::PER_PAGE, page: $page);

        return [
            $this->buildText($ignoredMessageTypes),
            $this->buildKeyboard($user, $ignoredMessageTypes),
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
                $source = $messageType->pivot->source;

                return ($firstItem + $index).'. '.$messageType->name.' — '.$source;
            })
            ->implode("\n");

        $shown = $messageTypes->lastItem() ?? 0;
        $total = $messageTypes->total();

        return $list."\n\n{$shown}/{$total}";
    }

    private function buildKeyboard(TelegramUser $user, LengthAwarePaginator $messageTypes): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();
        $userId = $user->id;
        $page = $messageTypes->currentPage();
        $navButtons = [];

        if ($page > 1) {
            $navButtons[] = InlineKeyboardButton::make(
                'Previous',
                callback_data: 'user_ignored_message_type:'.$userId.':'.($page - 1),
            );
        }

        if ($messageTypes->hasMorePages()) {
            $navButtons[] = InlineKeyboardButton::make(
                'Next',
                callback_data: 'user_ignored_message_type:'.$userId.':'.($page + 1),
            );
        }

        if ($navButtons !== []) {
            $keyboard->addRow(...$navButtons);
        }

        if (! $user->isPermanentAdmin()) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    'Add ignored message type',
                    callback_data: 'add_user_ignored_message_type:'.$userId,
                ),
            );

            $keyboard->addRow(
                InlineKeyboardButton::make(
                    'Remove ignored message type',
                    callback_data: 'remove_user_ignored_message_type:'.$userId,
                ),
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Back',
                callback_data: 'telegram_users:1',
            ),
        );

        return $keyboard;
    }
}
