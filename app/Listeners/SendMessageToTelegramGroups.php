<?php

namespace App\Listeners;

use App\Enums\GroupRole;
use App\Events\MessageCreated;
use App\Jobs\SendTelegramMessageToGroup;
use App\Models\TelegramGroup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;

class SendMessageToTelegramGroups implements ShouldQueue
{
    public function handle(MessageCreated $event): void
    {
        $groups = TelegramGroup::query()
            ->where('role', GroupRole::RECEIVER)
            ->where('is_active', true);

        if ($event->message->message_type_id !== null) {
            $messageTypeId = $event->message->message_type_id;

            $groups->whereDoesntHave(
                'ignoredMessageTypes',
                fn (Builder $ignored): Builder => $ignored->whereKey($messageTypeId),
            );
        }

        $groups->pluck('id')
            ->each(fn (int $groupId) => SendTelegramMessageToGroup::dispatch(
                $groupId,
                $event->message->id,
            ));
    }
}
