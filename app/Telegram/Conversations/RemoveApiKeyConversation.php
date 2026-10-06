<?php

namespace App\Telegram\Conversations;

use App\Enums\ApiKeyDeletionStatus;
use App\Services\ApiKeyService;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class RemoveApiKeyConversation extends Conversation
{
    protected ?string $apiKeyName = null;

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            'Enter the API key name.'
        );

        $this->next('askApiKeyName');
    }

    public function askApiKeyName(Nutgram $bot): void
    {
        $name = trim($bot->message()?->text ?? '');

        $apiKeyService = $bot->getContainer()->get(ApiKeyService::class);

        $status = $apiKeyService->deleteByName($name);

        if ($status === ApiKeyDeletionStatus::NOT_FOUND) {
            $bot->sendMessage(
                'API key with this name not found.'
            );

            return;
        }

        if ($status === ApiKeyDeletionStatus::AMBIGUOUS) {
            $this->apiKeyName = $name;

            $bot->sendMessage(
                'Several API keys have this name. Enter the identifier.'
            );

            $this->next('askIdentifier');

            return;
        }

        $bot->sendMessage(
            "API key {$name} removed."
        );

        $this->end();
    }

    public function askIdentifier(Nutgram $bot): void
    {
        $identifier = trim($bot->message()?->text ?? '');

        $apiKeyService = $bot->getContainer()->get(ApiKeyService::class);

        $status = $apiKeyService->deleteByIdentifier($this->apiKeyName ?? '', $identifier);

        if ($status === ApiKeyDeletionStatus::NOT_FOUND) {
            $bot->sendMessage(
                'API key with this identifier not found.'
            );

            return;
        }

        $bot->sendMessage(
            "API key {$this->apiKeyName} removed."
        );

        $this->end();
    }
}
