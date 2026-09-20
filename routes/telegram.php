<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\StartConversation;

use App\Telegram\Handlers\StartCommand;
use App\Telegram\Conversations\ChangeUserRoleConversation;

use App\Models\TelegramUser;
use App\Enums\UserRole;

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

$bot->onCallbackQueryData('hello', function (Nutgram $bot) {
    $bot->answerCallbackQuery();

    $bot->sendMessage('Привет! Рад тебя видеть!');
});
