<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\TelegramUser;
use App\Services\TelegramMessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramMessageToUser implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    /** @var list<int> */
    public array $backoff = [1, 5, 30];

    public int $timeout = 30;

    public function __construct(
        public int $telegramUserId,
        public int $messageId,
    ) {}

    public function handle(TelegramMessageService $telegramMessageService): void
    {
        $user = TelegramUser::query()->find($this->telegramUserId);
        $message = Message::query()->find($this->messageId);

        if ($user === null || $message === null) {
            return;
        }

        $telegramMessageService->sendToUser($user, $message);
    }
}
