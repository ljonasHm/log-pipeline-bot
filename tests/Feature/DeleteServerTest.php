<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Message;
use App\Models\Server;
use App\Telegram\Conversations\RemoveServerConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionProperty;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Message\Message as TelegramMessage;
use Tests\TestCase;

class DeleteServerTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_deletes_the_server_and_its_related_records(): void
    {
        $server = Server::factory()->create([
            'name' => 'web',
        ]);
        $apiKey = ApiKey::factory()->create([
            'server_id' => $server->id,
        ]);
        $message = Message::factory()->create([
            'server_id' => $server->id,
        ]);

        $this->askServerName('web', ends: true);

        $this->assertModelMissing($server);
        $this->assertModelMissing($apiKey);
        $this->assertModelMissing($message);
    }

    public function test_missing_name_does_not_delete_the_server(): void
    {
        $server = Server::factory()->create([
            'name' => 'web',
        ]);
        $apiKey = ApiKey::factory()->create([
            'server_id' => $server->id,
        ]);
        $message = Message::factory()->create([
            'server_id' => $server->id,
        ]);

        $this->askServerName('missing', ends: false);

        $this->assertModelExists($server);
        $this->assertModelExists($apiKey);
        $this->assertModelExists($message);
    }

    private function askServerName(string $name, bool $ends): void
    {
        $telegramMessage = new TelegramMessage;
        $telegramMessage->text = $name;

        $bot = Mockery::mock(Nutgram::class);
        $bot->shouldReceive('update')->andReturn(null);
        $bot->shouldReceive('message')->andReturn($telegramMessage);
        $bot->shouldReceive('sendMessage')->once();
        $bot->shouldReceive('endConversation')->times($ends ? 1 : 0);

        $conversation = new RemoveServerConversation;

        $step = new ReflectionProperty(Conversation::class, 'step');
        $step->setValue($conversation, 'askServerName');

        $conversation($bot);
    }
}
