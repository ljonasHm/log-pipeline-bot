<?php

namespace App\Telegram\Conversations;

use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

use App\Models\Server;
use App\Services\ApiKeyService;

class AddApiKeyConversation extends Conversation
{
    protected ?string $serverName = null;

    public function start(Nutgram $bot): void {
        $bot->sendMessage(
            'Enter the server name to add a key.'
        );

        $this->next('askServerName');
    }

    public function askServerName(Nutgram $bot): void {

        $serverName = $bot->message()->text;
        
        $isServerExists = Server::query()
            ->where('name', $serverName)
            ->exists();
        
        if (!$isServerExists) {
            $bot->sendMessage(
                'Server with this name not found.'
            );

            return;
        }

        $this->serverName = $serverName;
        $bot->sendMessage(
            'Enter the name for the new API key.'
        );

        $this->next('askApiKeyName');
    }

    public function askApiKeyName(
            Nutgram $bot,
        ): void {
        $apiKeyName = $bot->message()->text;
        $server = Server::query()
            ->where('name', $this->serverName)
            ->first();

        $apiKeyService = $bot->getContainer()->get(ApiKeyService::class);

        $key = $apiKeyService->create(
            $server,
            $apiKeyName
        );

        $bot->sendMessage(
            "API key created successfully.\nAPI key: {$key}"
        );

        $this->end();
    }
}