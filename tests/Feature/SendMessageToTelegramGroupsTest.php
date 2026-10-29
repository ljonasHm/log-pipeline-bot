<?php

namespace Tests\Feature;

use App\Events\MessageCreated;
use App\Jobs\SendTelegramMessageToGroup;
use App\Listeners\SendMessageToTelegramGroups;
use App\Models\Message;
use App\Models\MessageType;
use App\Models\TelegramGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendMessageToTelegramGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_a_job_for_each_active_receiver(): void
    {
        Queue::fake();

        $receiver = TelegramGroup::factory()->receiver()->create();
        TelegramGroup::factory()->receiver()->inactive()->create();
        TelegramGroup::factory()->create();
        $message = Message::factory()->create();

        app(SendMessageToTelegramGroups::class)->handle(new MessageCreated($message));

        Queue::assertPushed(SendTelegramMessageToGroup::class, 1);

        Queue::assertPushed(
            SendTelegramMessageToGroup::class,
            fn (SendTelegramMessageToGroup $job): bool => $job->telegramGroupId === $receiver->id
                && $job->messageId === $message->id,
        );
    }

    public function test_it_does_not_dispatch_a_job_for_groups_that_ignore_the_message_type(): void
    {
        Queue::fake();

        $messageType = MessageType::factory()->create();
        $ignored = TelegramGroup::factory()->receiver()->create();
        $ignored->ignoredMessageTypes()->attach($messageType);
        $receiver = TelegramGroup::factory()->receiver()->create();
        $message = Message::factory()->create([
            'type' => $messageType->name,
            'message_type_id' => $messageType->id,
        ]);

        app(SendMessageToTelegramGroups::class)->handle(new MessageCreated($message));

        Queue::assertPushed(SendTelegramMessageToGroup::class, 1);

        Queue::assertPushed(
            SendTelegramMessageToGroup::class,
            fn (SendTelegramMessageToGroup $job): bool => $job->telegramGroupId === $receiver->id
                && $job->messageId === $message->id,
        );
    }

    public function test_it_dispatches_jobs_when_the_message_type_is_unknown(): void
    {
        Queue::fake();

        $messageType = MessageType::factory()->create();
        $receiver = TelegramGroup::factory()->receiver()->create();
        $receiver->ignoredMessageTypes()->attach($messageType);
        $message = Message::factory()->create([
            'type' => 'custom',
            'message_type_id' => null,
        ]);

        app(SendMessageToTelegramGroups::class)->handle(new MessageCreated($message));

        Queue::assertPushed(
            SendTelegramMessageToGroup::class,
            fn (SendTelegramMessageToGroup $job): bool => $job->telegramGroupId === $receiver->id
                && $job->messageId === $message->id,
        );
    }
}
