<?php

return [
    'mattermost' => [
        'driver' => 'monolog',
        'handler' => \sajadjanat\MattermostBotLogger\MattermostBotHandler::class,
        'level' => env('LOG_LEVEL', 'error'),
        'base_url' => env('MATTERMOST_BASE_URL'),
        'token' => env('MATTERMOST_BOT_TOKEN'),
        'channel_id' => env('MATTERMOST_CHANNEL_ID'),
        'bot_username' => env('MATTERMOST_BOT_USERNAME', 'Laravel Bot'),
    ],
];