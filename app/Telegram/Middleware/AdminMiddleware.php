<?php

namespace App\Telegram\Middleware;

use App\Models\TelegramUser;
use App\Telegram\Conversations\AddOwnIgnoredMessageTypeConversation;
use App\Telegram\Conversations\RemoveOwnIgnoredMessageTypeConversation;
use Illuminate\Support\Facades\Gate;
use SergiX44\Nutgram\Middleware\Link;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ChatType;

class AdminMiddleware
{
    public function __invoke(Nutgram $bot, Link $next): void
    {
        if ($this->isGroupSyncUpdate($bot)) {
            $next($bot);

            return;
        }

        if ($this->isNonPrivateChat($bot)) {
            return;
        }

        if ($this->isStartCommand($bot)) {
            $next($bot);

            return;
        }

        $telegramId = $bot->user()?->id;

        $telegramUser = $telegramId !== null
            ? TelegramUser::findByTelegramId($telegramId)
            : null;

        if ($telegramUser === null) {
            $bot->sendMessage(
                'No rules.'
            );

            return;
        }

        if ($this->isOwnIgnoreList($bot) && Gate::forUser($telegramUser)->allows('change-own-ignore')) {
            $next($bot);

            return;
        }

        if (Gate::forUser($telegramUser)->denies('manage')) {
            $bot->sendMessage(
                'No rules.'
            );

            return;
        }

        $next($bot);
    }

    private function isGroupSyncUpdate(Nutgram $bot): bool
    {
        if (! $this->isGroupOrSupergroup($bot)) {
            return false;
        }

        if ($bot->isMyChatMember()) {
            return true;
        }

        return $bot->message()?->migrate_to_chat_id !== null;
    }

    private function isNonPrivateChat(Nutgram $bot): bool
    {
        $type = $this->chatType($bot);

        return $type !== null && $type !== ChatType::PRIVATE;
    }

    private function isGroupOrSupergroup(Nutgram $bot): bool
    {
        $type = $this->chatType($bot);

        return $type === ChatType::GROUP || $type === ChatType::SUPERGROUP;
    }

    private function chatType(Nutgram $bot): ?ChatType
    {
        $type = $bot->chat()?->type;

        if ($type instanceof ChatType) {
            return $type;
        }

        if (! is_string($type)) {
            return null;
        }

        return ChatType::tryFrom($type);
    }

    private function isOwnIgnoreList(Nutgram $bot): bool
    {
        $callbackData = $bot->callbackQuery()?->data;

        if ($callbackData !== null) {
            if (
                $callbackData === 'add_own_ignored_message_type'
                || $callbackData === 'remove_own_ignored_message_type'
                || str_starts_with($callbackData, 'own_ignored_message_type:')
            ) {
                return true;
            }
        }

        $conversation = $bot->currentConversation(
            $bot->userId(),
            $bot->chatId(),
            $bot->messageThreadId(),
        );

        return $conversation instanceof AddOwnIgnoredMessageTypeConversation
            || $conversation instanceof RemoveOwnIgnoredMessageTypeConversation;
    }

    private function isStartCommand(Nutgram $bot): bool
    {
        if ($bot->callbackQuery()?->data === 'start') {
            return true;
        }

        return preg_match(
            '/^\/start(?:@\w+)?(?:\s|$)/i',
            $bot->message()?->text ?? ''
        ) === 1;
    }
}
