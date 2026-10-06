<?php

namespace Tests\Feature;

use App\Enums\IgnoredMessageTypeSource;
use App\Models\MessageType;
use App\Models\TelegramUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IgnoredMessageTypeSourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_source_on_the_pivot_when_attaching(): void
    {
        $user = TelegramUser::factory()->receiver()->create();
        $userAdded = MessageType::factory()->create();
        $adminAdded = MessageType::factory()->create();

        $user->ignoredMessageTypes()->attach($userAdded->id, [
            'source' => IgnoredMessageTypeSource::USER->value,
        ]);
        $user->ignoredMessageTypes()->attach($adminAdded->id, [
            'source' => IgnoredMessageTypeSource::ADMIN->value,
        ]);

        $this->assertDatabaseHas('message_type_telegram_user', [
            'telegram_user_id' => $user->id,
            'message_type_id' => $userAdded->id,
            'source' => IgnoredMessageTypeSource::USER->value,
        ]);

        $this->assertDatabaseHas('message_type_telegram_user', [
            'telegram_user_id' => $user->id,
            'message_type_id' => $adminAdded->id,
            'source' => IgnoredMessageTypeSource::ADMIN->value,
        ]);
    }

    public function test_it_defaults_source_to_user_when_not_provided(): void
    {
        $user = TelegramUser::factory()->receiver()->create();
        $messageType = MessageType::factory()->create();

        $user->ignoredMessageTypes()->attach($messageType);

        $this->assertDatabaseHas('message_type_telegram_user', [
            'telegram_user_id' => $user->id,
            'message_type_id' => $messageType->id,
            'source' => IgnoredMessageTypeSource::USER->value,
        ]);
    }

    public function test_receiver_can_query_only_user_sourced_ignored_types(): void
    {
        $user = TelegramUser::factory()->receiver()->create();
        $userAdded = MessageType::factory()->create();
        $adminAdded = MessageType::factory()->create();

        $user->ignoredMessageTypes()->attach($userAdded->id, [
            'source' => IgnoredMessageTypeSource::USER->value,
        ]);
        $user->ignoredMessageTypes()->attach($adminAdded->id, [
            'source' => IgnoredMessageTypeSource::ADMIN->value,
        ]);

        $visibleIds = $user->ignoredMessageTypes()
            ->wherePivot('source', IgnoredMessageTypeSource::USER->value)
            ->pluck('message_types.id');

        $this->assertEqualsCanonicalizing([$userAdded->id], $visibleIds->all());
    }
}
