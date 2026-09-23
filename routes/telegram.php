<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use SergiX44\Nutgram\Nutgram;

use App\Telegram\Handlers\StartCommand;
use App\Telegram\Conversations\ChangeUserRoleConversation;
use App\Telegram\Conversations\AddServerConversation;
use App\Telegram\Conversations\AddApiKeyConversation;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot->onCommand('start', StartCommand::class);

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
