<?php

/** @var Nutgram $bot */

use App\Telegram\Conversations\AddApiKeyConversation;
use App\Telegram\Conversations\AddGroupIgnoredMessageTypeConversation;
use App\Telegram\Conversations\AddMessageTypeConversation;
use App\Telegram\Conversations\AddOwnIgnoredMessageTypeConversation;
use App\Telegram\Conversations\AddServerConversation;
use App\Telegram\Conversations\AddUserIgnoredMessageTypeConversation;
use App\Telegram\Conversations\ChangeGroupRoleConversation;
use App\Telegram\Conversations\ChangeUserRoleConversation;
use App\Telegram\Conversations\GroupIgnoredMessageTypesConversation;
use App\Telegram\Conversations\RemoveApiKeyConversation;
use App\Telegram\Conversations\RemoveGroupIgnoredMessageTypeConversation;
use App\Telegram\Conversations\RemoveOwnIgnoredMessageTypeConversation;
use App\Telegram\Conversations\RemoveServerConversation;
use App\Telegram\Conversations\RemoveUserIgnoredMessageTypeConversation;
use App\Telegram\Conversations\UserIgnoredMessageTypesConversation;
use App\Telegram\Handlers\ApiKeysCommand;
use App\Telegram\Handlers\GroupIgnoredMessageTypeCommand;
use App\Telegram\Handlers\MessageTypesCommand;
use App\Telegram\Handlers\MigrateTelegramGroupHandler;
use App\Telegram\Handlers\OwnIgnoredMessageTypeCommand;
use App\Telegram\Handlers\ServersCommand;
use App\Telegram\Handlers\StartCommand;
use App\Telegram\Handlers\SyncTelegramGroupHandler;
use App\Telegram\Handlers\TelegramGroupsCommand;
use App\Telegram\Handlers\TelegramUsersCommand;
use App\Telegram\Handlers\UserIgnoredMessageTypeCommand;
use App\Telegram\Middleware\AdminMiddleware;
use SergiX44\Nutgram\Nutgram;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot->middleware(AdminMiddleware::class);

$bot->onMyChatMember(SyncTelegramGroupHandler::class);

$bot->onMigrateToChatId(MigrateTelegramGroupHandler::class);

$bot->onCommand('start', StartCommand::class);

$bot->onCallbackQueryData(
    'start',
    StartCommand::class
);

$bot->onCallbackQueryData(
    'servers:{page}',
    ServersCommand::class
);

$bot->onCallbackQueryData(
    'api_keys:{page}',
    ApiKeysCommand::class
);

$bot->onCallbackQueryData(
    'telegram_users:{page}',
    TelegramUsersCommand::class
);

$bot->onCallbackQueryData(
    'message_types:{page}',
    MessageTypesCommand::class
);

$bot->onCallbackQueryData(
    'own_ignored_message_type:{page}',
    OwnIgnoredMessageTypeCommand::class
);

$bot->onCallbackQueryData(
    'change_user_role',
    ChangeUserRoleConversation::class
);

$bot->onCallbackQueryData(
    'add_server',
    AddServerConversation::class
);

$bot->onCallbackQueryData(
    'remove_server',
    RemoveServerConversation::class
);

$bot->onCallbackQueryData(
    'add_api_key',
    AddApiKeyConversation::class
);

$bot->onCallbackQueryData(
    'remove_api_key',
    RemoveApiKeyConversation::class
);

$bot->onCallbackQueryData(
    'add_message_type',
    AddMessageTypeConversation::class
);

$bot->onCallbackQueryData(
    'user_ignored_types',
    UserIgnoredMessageTypesConversation::class
);

$bot->onCallbackQueryData(
    'user_ignored_message_type:{userId}:{page}',
    UserIgnoredMessageTypeCommand::class
);

$bot->onCallbackQueryData(
    'add_user_ignored_message_type:{userId}',
    AddUserIgnoredMessageTypeConversation::class
);

$bot->onCallbackQueryData(
    'add_own_ignored_message_type',
    AddOwnIgnoredMessageTypeConversation::class
);

$bot->onCallbackQueryData(
    'remove_own_ignored_message_type',
    RemoveOwnIgnoredMessageTypeConversation::class
);

$bot->onCallbackQueryData(
    'remove_user_ignored_message_type:{userId}',
    RemoveUserIgnoredMessageTypeConversation::class
);

$bot->onCallbackQueryData(
    'telegram_groups:{page}',
    TelegramGroupsCommand::class
);

$bot->onCallbackQueryData(
    'change_group_role',
    ChangeGroupRoleConversation::class
);

$bot->onCallbackQueryData(
    'group_ignored_types',
    GroupIgnoredMessageTypesConversation::class
);

$bot->onCallbackQueryData(
    'group_ignored_message_type:{groupId}:{page}',
    GroupIgnoredMessageTypeCommand::class
);

$bot->onCallbackQueryData(
    'add_group_ignored_message_type:{groupId}',
    AddGroupIgnoredMessageTypeConversation::class
);

$bot->onCallbackQueryData(
    'remove_group_ignored_message_type:{groupId}',
    RemoveGroupIgnoredMessageTypeConversation::class
);
