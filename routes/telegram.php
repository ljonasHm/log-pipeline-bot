<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use App\Models\TelegramUser;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot->onCommand('start', function (Nutgram $bot) {
    $telegramUser = $bot->user();
    $chat = $bot->chat();

    \Log::debug(json_encode($telegramUser, JSON_PRETTY_PRINT));
    \Log::debug(json_encode($chat, JSON_PRETTY_PRINT));

    $user = TelegramUser::firstOrCreate(
        
        [
            'telegram_id' => $telegramUser->id
        ],
        [
            'chat_id' => $chat->id,
            'name' => $telegramUser->username
        ]
    );

    $bot->sendMessage(
        $user->name
    );
})->description('The start command!');

$bot->onCallbackQueryData('hello', function (Nutgram $bot) {
    $bot->answerCallbackQuery();

    $bot->sendMessage('Привет! Рад тебя видеть!');
});
