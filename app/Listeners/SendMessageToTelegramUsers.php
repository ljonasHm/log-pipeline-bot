<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\MessageCreated;
use App\Jobs\SendTelegramMessageToUser;
use App\Models\TelegramUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;

class SendMessageToTelegramUsers implements ShouldQueue
{
    public function handle(MessageCreated $event): void
    {
        $users = TelegramUser::query()
            ->whereIn('role', [
                UserRole::ADMIN,
                UserRole::RECEIVER,
            ]);

        if ($event->message->message_type_id !== null) {
            $messageTypeId = $event->message->message_type_id;

            $users->whereDoesntHave(
                'ignoredMessageTypes',
                fn (Builder $ignored): Builder => $ignored->whereKey($messageTypeId),
            );
        }

        $users->pluck('id')
            ->each(fn (int $userId) => SendTelegramMessageToUser::dispatch(
                $userId,
                $event->message->id,
            ));
    }
}
