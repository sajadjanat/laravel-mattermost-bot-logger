# Laravel Mattermost Bot Logger

A custom logging channel for **Laravel** and **Lumen** that sends logs and exceptions directly to **Mattermost** using a **Bot Token** via the Mattermost API v4.

This package provides structured, readable log messages with severity indicators and full exception stack traces, making it suitable for production monitoring and error reporting.

**Keywords:** laravel logging, lumen logging, mattermost logger, mattermost bot, monolog handler, error monitoring

---

## Features

- Compatible with **Laravel 9, 10, 11+** and **Lumen 8, 9, 10+**
- Full support for **Monolog 3.x**
- Sends all log levels with severity-based formatting
- Detailed exception reporting including full stack trace
- Secure authentication via **Mattermost Bot Token**
- No external dependencies beyond the framework and Guzzle
- Simple `.env`-based configuration
- Works with both Laravel and Lumen without code duplication

---

## Requirements

- PHP 8.1 or higher
- Laravel 9+ or Lumen 8+
- Mattermost Bot Account with Access Token

---

## Installation

composer require sajadjanat/laravel-mattermost-bot-logger

The package uses Composer autoloading and works out-of-the-box with both Laravel and Lumen.

---

## Configuration

### 1. Publish configuration (Laravel only – optional)

```php
php artisan vendor:publish --tag=mattermost-logging-config
```
This will create:

config/logging-mattermost.php

> **Lumen note:** Lumen does not support vendor:publish.
> You can manually copy the configuration array into your logging setup.

---

### 2. Environment variables

Add the following to your .env file:

```dotenv
MATTERMOST_BASE_URL=https://your-mattermost-instance.com
MATTERMOST_BOT_TOKEN=your_bot_token_here
MATTERMOST_CHANNEL_ID=your_channel_id
MATTERMOST_BOT_USERNAME=Laravel/Lumen Errors
MATTERMOST_LOG_LEVEL=error
```
How to obtain values:

- Base URL: Your Mattermost server URL (without trailing slash)
- Bot Token: System Console → Integrations → Bot Accounts → Add Bot
- Channel ID: Channel menu → View Info → Copy Channel ID

---

### 3. Laravel logging channel setup

In config/logging.php:

```php
'channels' => [

    'mattermost' => config('logging-mattermost.mattermost'),

    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'daily', 'mattermost'],
        'ignore_exceptions' => false,
    ],
];
```
Set default channel in .env:

```dotenv
LOG_CHANNEL=stack
```
---

### 4. Lumen setup (bootstrap/app.php)

```php
$app->configure('logging');

$app->configureMonologUsing(function (Monolog\Logger $monolog) {
    $monolog->pushHandler(
        new \Sajadabasi\MattermostBotLogger\MattermostBotHandler(
            level: \Monolog\Logger::toMonologLevel(
                env('MATTERMOST_LOG_LEVEL', 'error')
            )
        )
    );

    return $monolog;
});
```
You may also use the handler manually via Log::channel('mattermost').

---

## Manual Configuration Array

If you prefer not to publish a config file, use the following channel definition:
```php
'mattermost' => [
    'driver' => 'monolog',
    'handler' => \Sajadabasi\MattermostBotLogger\MattermostBotHandler::class,
    'level' => env('MATTERMOST_LOG_LEVEL', 'error'),
    'base_url' => env('MATTERMOST_BASE_URL'),
    'token' => env('MATTERMOST_BOT_TOKEN'),
    'channel_id' => env('MATTERMOST_CHANNEL_ID'),
    'bot_username' => env('MATTERMOST_BOT_USERNAME', 'Laravel/Lumen Bot'),
],
```
---

## License

MIT License

---

## Contributing

Pull requests are welcome.
For major changes, please open an issue first.

---

Made for the Laravel & Lumen ecosystem.
