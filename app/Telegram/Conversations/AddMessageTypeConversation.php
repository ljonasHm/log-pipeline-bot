<?php

namespace App\Telegram\Conversations;

use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

use App\Models\MessageType;

class AddMessageTypeConversation extends Conversation
{
    protected ?string $typeKey = null;

    public function start(Nutgram $bot): void {
        $bot->sendMessage(
            'Enter the new type key.'
        );

        $this->next('askTypeKey');
    }

    public function askTypeKey(Nutgram $bot): void {

        $newTypeKey = $bot->message()->text;

        $isTypeExists = MessageType::query()
            ->where('name', $newTypeKey)
            ->exists();

        if (!$isTypeExists) {
            $bot->sendMessage(
                'Type with this name already exists.'
            );

            return;
        }

        $this->typeKey = $newTypeKey;

        $bot->sendMessage(
            'Enter the new type title.'
        );

        $this->next('askTypeTitle');
    }

    public function askTypeTitle(Nutgram $bot): void {

        $newTypeTitle = $bot->message()->text;
        
        $messageType = MessageType::create([
            'name' => $this->typeKey,
            'title' => $newTypeTitle
        ]);

        $bot->sendMessage(
            "Message type {$messageType->key} with title {$messageType->title} created."
        );

        $this->end();
    }
}
