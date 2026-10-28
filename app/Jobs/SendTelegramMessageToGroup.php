<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\TelegramGroup;
use App\Services\TelegramMessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;

class SendTelegramMessageToGroup implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    /** @var list<int> */
    public array $backoff = [1, 5, 30];

    public int $timeout = 30;

    public function __construct(
        public int $telegramGroupId,
        public int $messageId,
    ) {}

    public function handle(TelegramMessageService $telegramMessageService): void
    {
        $group = TelegramGroup::query()->find($this->telegramGroupId);
        $message = Message::query()->with(['messageType', 'server'])->find($this->messageId);

        if ($group === null || $message === null || ! $group->is_active) {
            return;
        }

        try {
            $telegramMessageService->sendToGroup($group, $message);
        } catch (TelegramException $exception) {
            if (! $this->botWasRemoved($exception)) {
                throw $exception;
            }

            $group->is_active = false;
            $group->save();
        }
    }

    private function botWasRemoved(TelegramException $exception): bool
    {
        if ($exception->getCode() !== 403) {
            return false;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'kicked')
            || str_contains($message, 'not a member');
    }
}
