<?php

namespace App\Telegram\Handlers;

use App\Models\ApiKey;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class ApiKeysCommand
{
    private const PER_PAGE = 10;

    public function __invoke(Nutgram $bot, string $page = '1'): void
    {
        $page = max(1, (int) $page);

        $apiKeys = ApiKey::query()
            ->with('server')
            ->orderBy('id')
            ->paginate(self::PER_PAGE, page: $page);

        $text = $this->buildText($apiKeys);
        $keyboard = $this->buildKeyboard($apiKeys);

        $bot->editMessageText(
            text: $text,
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    private function buildText(LengthAwarePaginator $apiKeys): string
    {
        if ($apiKeys->isEmpty()) {
            return 'No API keys.';
        }

        $firstItem = $apiKeys->firstItem() ?? 1;

        $list = collect($apiKeys->items())
            ->values()
            ->map(fn (ApiKey $apiKey, int $index) => ($firstItem + $index).'. '.$apiKey->name.' — '.$apiKey->server->name)
            ->implode("\n");

        $shown = $apiKeys->lastItem() ?? 0;
        $total = $apiKeys->total();

        return $list."\n\n{$shown}/{$total}";
    }

    private function buildKeyboard(LengthAwarePaginator $apiKeys): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();
        $page = $apiKeys->currentPage();
        $navButtons = [];

        if ($page > 1) {
            $navButtons[] = InlineKeyboardButton::make(
                'Previous',
                callback_data: 'api_keys:'.($page - 1),
            );
        }

        if ($apiKeys->hasMorePages()) {
            $navButtons[] = InlineKeyboardButton::make(
                'Next',
                callback_data: 'api_keys:'.($page + 1),
            );
        }

        if ($navButtons !== []) {
            $keyboard->addRow(...$navButtons);
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Add API key',
                callback_data: 'add_api_key',
            ),
        );

        return $keyboard;
    }
}
