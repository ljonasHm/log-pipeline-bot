<?php

namespace App\Telegram\Conversations;

use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

use App\Models\TelegramUser;
use App\Models\Server;
use App\Enums\UserRole;

class AddServerConversation extends Conversation
{
    public function start(Nutgram $bot): void {
        $telegramId = $bot->user()->id;

        $user = TelegramUser::query()
            ->where('telegram_id', $telegramId)
            ->first();

        if (!$user || $user->role !== UserRole::ADMIN) {
            $bot->sendMessage(
                'No rules.'
            );

            $this->end();

            return;
        }

        $bot->sendMessage(
            'Enter the new server name'
        );

        $this->next('askTelegramId');
    }

    public function askServerName(Nutgram $bot): void {

        $telegramId = $bot->user()->id;

        $user = TelegramUser::query()
            ->where('telegram_id', $telegramId)
            ->first();

        if (!$user || $user->role !== UserRole::ADMIN) {
            $bot->sendMessage(
                'No rules.'
            );

            $this->end();

            return;
        }

        $server_name = $bot->message()?->text;

        $existed_server = Server::query()
            ->where('name', $server_name)
            ->first();

        if ($existed_server) {
            $bot->sendMessage(
                'Server with this name already exists'
            );

            return;
        }

        Server::create([
            'name' => $server_name
        ]);


        $bot->sendMessage(
            "Server ${server_name} created." 
        );

        $this->end();
    }
}
