<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Events\MessageCreated;
use App\Services\TelegramMessageService;
use App\Models\TelegramUser;
use App\Enums\UserRole;

class SendMessageToTelegramUsers implements ShouldQueue
{
    public function __construct(
        private TelegramMessageService $telegramMessageService
    ) {}

    public function handle(MessageCreated $event): void
    {
        $users = TelegramUser::query()
            ->whereIn('role', [
                UserRole::ADMIN,
                UserRole::RECEIVER
            ])
            ->get();

        foreach ($users as $user) {
            $this->telegramMessageService->sendToUser(
                $user,
                $event->message
            );
        }
    }
}
