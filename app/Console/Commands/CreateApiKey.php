<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\ApiKeyService;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

class CreateApiKey extends Command
{
    protected $signature = 'api-key:create
                            {server_id : ID сервера}
                            {name : Название API-ключа}';

    protected $description = 'Create an API key for a server';

    public function handle(ApiKeyService $apiKeyService): int
    {
        $server = Server::find($this->argument('server_id'));

        if (!$server) {
            $this->error('Server not found.');

            return self::FAILURE;
        }

        $key = $apiKeyService->create(
            $server,
            $this->argument('name'),
        );

        $this->info('API key created successfully.');
        $this->newLine();

        $this->line("API key: {$key}");

        return self::SUCCESS;
    }
}
