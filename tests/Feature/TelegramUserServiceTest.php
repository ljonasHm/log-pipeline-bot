<?php

namespace Tests\Feature;

use App\Models\TelegramUser;
use App\Services\TelegramUserService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_finds_user_by_chat_id(): void
    {
        $user = TelegramUser::factory()->create([
            'chat_id' => 123456789,
            'name' => 'Alice',
        ]);

        $result = app(TelegramUserService::class)->findByChatIdOrName('123456789');

        $this->assertTrue($result->is($user));
    }

    public function test_finds_user_by_name(): void
    {
        $user = TelegramUser::factory()->create([
            'name' => 'Alice',
        ]);

        $result = app(TelegramUserService::class)->findByChatIdOrName('Alice');

        $this->assertTrue($result->is($user));
    }

    public function test_returns_collection_when_several_users_share_the_name(): void
    {
        TelegramUser::factory()->create(['name' => 'Alice']);
        TelegramUser::factory()->create(['name' => 'Alice']);

        $result = app(TelegramUserService::class)->findByChatIdOrName('Alice');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_returns_null_when_user_is_not_found(): void
    {
        $result = app(TelegramUserService::class)->findByChatIdOrName('missing');

        $this->assertNull($result);
    }

    public function test_falls_back_to_name_when_chat_id_lookup_misses(): void
    {
        $user = TelegramUser::factory()->create([
            'name' => '123456789',
            'chat_id' => 999,
        ]);

        $result = app(TelegramUserService::class)->findByChatIdOrName('123456789');

        $this->assertTrue($result->is($user));
    }
}
