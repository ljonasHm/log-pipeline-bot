<?php

namespace App\Telegram\Conversations;

use App\Models\TelegramGroup;
use App\Services\TelegramGroupService;
use App\Telegram\Handlers\GroupIgnoredMessageTypeCommand;
use Illuminate\Database\Eloquent\Collection;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class GroupIgnoredMessageTypesConversation extends Conversation
{
    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            'Enter the group chat_id or title.'
        );

        $this->next('askGroup');
    }

    public function askGroup(Nutgram $bot): void
    {
        $input = trim($bot->message()?->text ?? '');

        if ($input === '') {
            $bot->sendMessage(
                'Enter the group chat_id or title.'
            );

            return;
        }

        $group = $this->resolveGroup($bot, $input);

        if ($group === null) {
            return;
        }

        [$text, $keyboard] = app(GroupIgnoredMessageTypeCommand::class)->buildList($group);

        $bot->sendMessage(
            text: $text,
            reply_markup: $keyboard,
        );

        $this->end();
    }

    private function resolveGroup(Nutgram $bot, string $input): ?TelegramGroup
    {
        $result = $bot->getContainer()
            ->get(TelegramGroupService::class)
            ->findByChatIdOrTitle($input);

        if ($result instanceof Collection) {
            $bot->sendMessage(
                'Several groups have this title. Enter the chat_id.'
            );

            return null;
        }

        if ($result === null) {
            $bot->sendMessage(
                'Group not found.'
            );

            return null;
        }

        return $result;
    }
}
