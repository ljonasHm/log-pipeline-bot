<?php

namespace Tests\Feature;

use App\Events\MessageCreated;
use App\Jobs\SendTelegramMessageToUser;
use App\Listeners\SendMessageToTelegramUsers;
use App\Models\Message;
use App\Models\MessageType;
use App\Models\TelegramUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendMessageToTelegramUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_a_job_for_each_admin_and_receiver(): void
    {
        Queue::fake();

        $admin = TelegramUser::factory()->admin()->create();
        $receiver = TelegramUser::factory()->receiver()->create();
        TelegramUser::factory()->create();

        $message = Message::factory()->create();

        app(SendMessageToTelegramUsers::class)->handle(new MessageCreated($message));

        Queue::assertPushed(SendTelegramMessageToUser::class, 2);

        Queue::assertPushed(
            SendTelegramMessageToUser::class,
            fn (SendTelegramMessageToUser $job): bool => $job->telegramUserId === $admin->id
                && $job->messageId === $message->id,
        );

        Queue::assertPushed(
            SendTelegramMessageToUser::class,
            fn (SendTelegramMessageToUser $job): bool => $job->telegramUserId === $receiver->id
                && $job->messageId === $message->id,
        );
    }

    public function test_it_does_not_dispatch_a_job_for_users_who_ignore_the_message_type(): void
    {
        Queue::fake();

        $messageType = MessageType::factory()->create();
        $ignored = TelegramUser::factory()->receiver()->create();
        $ignored->ignoredMessageTypes()->attach($messageType);
        $receiver = TelegramUser::factory()->admin()->create();
        $message = Message::factory()->create([
            'type' => $messageType->name,
            'message_type_id' => $messageType->id,
        ]);

        app(SendMessageToTelegramUsers::class)->handle(new MessageCreated($message));

        Queue::assertPushed(SendTelegramMessageToUser::class, 1);

        Queue::assertPushed(
            SendTelegramMessageToUser::class,
            fn (SendTelegramMessageToUser $job): bool => $job->telegramUserId === $receiver->id
                && $job->messageId === $message->id,
        );
    }

    public function test_it_dispatches_jobs_when_the_message_type_is_unknown(): void
    {
        Queue::fake();

        $messageType = MessageType::factory()->create();
        $receiver = TelegramUser::factory()->receiver()->create();
        $receiver->ignoredMessageTypes()->attach($messageType);
        $message = Message::factory()->create([
            'type' => 'custom',
            'message_type_id' => null,
        ]);

        app(SendMessageToTelegramUsers::class)->handle(new MessageCreated($message));

        Queue::assertPushed(
            SendTelegramMessageToUser::class,
            fn (SendTelegramMessageToUser $job): bool => $job->telegramUserId === $receiver->id
                && $job->messageId === $message->id,
        );
    }

    public function test_it_dispatches_a_job_for_a_permanent_admin_without_a_role(): void
    {
        Queue::fake();

        $permanentAdmin = TelegramUser::factory()->create([
            'chat_id' => 424242,
        ]);
        TelegramUser::factory()->create();
        $message = Message::factory()->create();

        config(['telegram.permanent_admin_chat_ids' => [424242]]);

        app(SendMessageToTelegramUsers::class)->handle(new MessageCreated($message));

        Queue::assertPushed(SendTelegramMessageToUser::class, 1);

        Queue::assertPushed(
            SendTelegramMessageToUser::class,
            fn (SendTelegramMessageToUser $job): bool => $job->telegramUserId === $permanentAdmin->id
                && $job->messageId === $message->id,
        );
    }

    public function test_it_does_not_dispatch_a_job_for_a_permanent_admin_who_ignores_the_message_type(): void
    {
        Queue::fake();

        $messageType = MessageType::factory()->create();
        $permanentAdmin = TelegramUser::factory()->create([
            'chat_id' => 424242,
        ]);
        $permanentAdmin->ignoredMessageTypes()->attach($messageType);
        $message = Message::factory()->create([
            'type' => $messageType->name,
            'message_type_id' => $messageType->id,
        ]);

        config(['telegram.permanent_admin_chat_ids' => [424242]]);

        app(SendMessageToTelegramUsers::class)->handle(new MessageCreated($message));

        Queue::assertNothingPushed();
    }
}
