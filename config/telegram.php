<?php

$permanentAdminChatIds = array_values(array_filter(
    array_map(
        trim(...),
        explode(',', (string) env('PERMANENT_ADMIN_CHAT_IDS', '')),
    ),
    fn (string $chatId): bool => $chatId !== '' && is_numeric($chatId),
));

return [

    'permanent_admin_chat_ids' => array_map(intval(...), $permanentAdminChatIds),

];
