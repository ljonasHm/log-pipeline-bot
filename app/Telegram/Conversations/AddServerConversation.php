<?php

namespace App\Telegram\Conversations;

use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

use App\Models\Server;

class AddServerConversation extends Conversation
{
    public function start(Nutgram $bot): void {
        $bot->sendMessage(
            'Enter the new server name'
        );

        $this->next('askServerName');
    }

    public function askServerName(Nutgram $bot): void {

        $serverName = $bot->message()?->text;

        $isServerExists = Server::query()
            ->where('name', $serverName)
            ->exists();

        if ($isServerExists) {
            $bot->sendMessage(
                'Server with this name already exists'
            );

            return;
        }

        Server::create([
            'name' => $serverName
        ]);


        $bot->sendMessage(
            "Server {$serverName} created." 
        );

        $this->end();
    }
}
