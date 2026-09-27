<?php

namespace App\Telegram\Handlers;

use App\Models\Server;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class ServersCommand
{
    private const PER_PAGE = 10;

    public function __invoke(Nutgram $bot, string $page = '1'): void
    {
        $page = max(1, (int) $page);

        $servers = Server::query()
            ->orderBy('id')
            ->paginate(self::PER_PAGE, page: $page);

        $text = $this->buildText($servers);
        $keyboard = $this->buildKeyboard($servers);

        $bot->editMessageText(
            text: $text,
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    private function buildText(LengthAwarePaginator $servers): string
    {
        if ($servers->isEmpty()) {
            return 'No servers.';
        }

        $firstItem = $servers->firstItem() ?? 1;

        $list = collect($servers->items())
            ->values()
            ->map(fn (Server $server, int $index) => ($firstItem + $index).'. '.$server->name)
            ->implode("\n");

        $shown = $servers->lastItem() ?? 0;
        $total = $servers->total();

        return $list."\n\n{$shown}/{$total}";
    }

    private function buildKeyboard(LengthAwarePaginator $servers): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();
        $page = $servers->currentPage();
        $navButtons = [];

        if ($page > 1) {
            $navButtons[] = InlineKeyboardButton::make(
                'Previous',
                callback_data: 'servers:'.($page - 1),
            );
        }

        if ($servers->hasMorePages()) {
            $navButtons[] = InlineKeyboardButton::make(
                'Next',
                callback_data: 'servers:'.($page + 1),
            );
        }

        if ($navButtons !== []) {
            $keyboard->addRow(...$navButtons);
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                'Add server',
                callback_data: 'add_server',
            ),
        );

        return $keyboard;
    }
}
