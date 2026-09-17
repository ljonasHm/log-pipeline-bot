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
    // function (Nutgram $bot) {
    //     \Log::debug('start change_user_role');
    //     $telegramId = $bot->user()->id;

    //     $user = TelegramUser::query()
    //         ->where('telegram_id', $telegramId)
    //         ->first();

    //     \Log::debug('user found');

    //     if (!$user || $user->role !== UserRole::ADMIN) {
    //         $bot->answerCallbackQuery(
    //             text: 'No rules.',
    //             show_alert: true,
    //         );

    //         return;
    //     }

    //     \Log::debug('before answer');
    //     $bot->answerCallbackQuery();
    //     \Log::debug('after answer');

    //     ChangeUserRoleConversation::begin(bot: $bot, userId: $telegramId, chatId: $telegramId);
    //     // StartConversation::begin(
    //     //     bot: $bot,
    //     //     conversation: ChangeUserRoleConversation::class,
    //     // );
    //     \Log::debug('after conversation');
    // }
);

$bot->onCallbackQueryData('hello', function (Nutgram $bot) {
    $bot->answerCallbackQuery();

    $bot->sendMessage('Привет! Рад тебя видеть!');
});
