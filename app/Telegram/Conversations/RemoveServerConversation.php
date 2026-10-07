<?php

namespace App\Telegram\Conversations;

use App\Models\Server;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class RemoveServerConversation extends Conversation
{
    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            'Enter the server name.'
        );

        $this->next('askServerName');
    }

    public function askServerName(Nutgram $bot): void
    {
        $name = trim($bot->message()?->text ?? '');

        $server = Server::query()
            ->where('name', $name)
            ->first();

        if ($server === null) {
            $bot->sendMessage(
                'Server with this name not found.'
            );

            return;
        }

        $server->delete();

        $bot->sendMessage(
            "Server {$name} removed."
        );

        $this->end();
    }
}
