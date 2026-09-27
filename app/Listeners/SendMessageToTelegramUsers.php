<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\MessageCreated;
use App\Jobs\SendTelegramMessageToUser;
use App\Models\TelegramUser;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMessageToTelegramUsers implements ShouldQueue
{
    public function handle(MessageCreated $event): void
    {
        TelegramUser::query()
            ->whereIn('role', [
                UserRole::ADMIN,
                UserRole::RECEIVER,
            ])
            ->pluck('id')
            ->each(fn (int $userId) => SendTelegramMessageToUser::dispatch(
                $userId,
                $event->message->id,
            ));
    }
}
