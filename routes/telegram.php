<?php

/** @var Nutgram $bot */

use App\Telegram\Conversations\AddApiKeyConversation;
use App\Telegram\Conversations\AddMessageTypeConversation;
use App\Telegram\Conversations\AddOwnIgnoredMessageTypeConversation;
use App\Telegram\Conversations\AddServerConversation;
use App\Telegram\Conversations\AddUserIgnoredMessageTypeConversation;
use App\Telegram\Conversations\ChangeUserRoleConversation;
use App\Telegram\Handlers\ApiKeysCommand;
use App\Telegram\Handlers\MessageTypesCommand;
use App\Telegram\Handlers\ServersCommand;
use App\Telegram\Handlers\StartCommand;
use App\Telegram\Handlers\TelegramUsersCommand;
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

$bot->onCommand('start', StartCommand::class);

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
    'change_user_role',
    ChangeUserRoleConversation::class
);

$bot->onCallbackQueryData(
    'add_server',
    AddServerConversation::class
);

$bot->onCallbackQueryData(
    'add_api_key',
    AddApiKeyConversation::class
);

$bot->onCallbackQueryData(
    'add_message_type',
    AddMessageTypeConversation::class
);

$bot->onCallbackQueryData(
    'add_user_ignored_message_type',
    AddUserIgnoredMessageTypeConversation::class
);

$bot->onCallbackQueryData(
    'add_own_ignored_message_type',
    AddOwnIgnoredMessageTypeConversation::class
);
