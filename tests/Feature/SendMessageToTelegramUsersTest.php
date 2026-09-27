<?php

namespace Tests\Feature;

use App\Events\MessageCreated;
use App\Jobs\SendTelegramMessageToUser;
use App\Listeners\SendMessageToTelegramUsers;
use App\Models\Message;
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
}
