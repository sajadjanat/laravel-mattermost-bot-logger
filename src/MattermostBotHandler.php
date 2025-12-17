
<?php
namespace sajadjanat\MattermostBotLogger;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Http;
use Throwable;

class MattermostBotHandler extends AbstractProcessingHandler
{
    protected string $baseUrl;
    protected string $token;
    protected string $channelId;
    protected ?string $botUsername;

    public function __construct($level = Logger::ERROR, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->baseUrl = rtrim(config('logging.channels.mattermost.base_url') ?? env('MATTERMOST_BASE_URL'), '/');
        $this->token = \App\Logging\config('logging.channels.mattermost.token') ?? \App\Logging\env('MATTERMOST_BOT_TOKEN');
        $this->channelId = \App\Logging\config('logging.channels.mattermost.channel_id') ?? \App\Logging\env('MATTERMOST_CHANNEL_ID');
        $this->botUsername = \App\Logging\config('logging.channels.mattermost.bot_username') ?? 'Laravel Bot';
    }

    protected function write(LogRecord $record): void
    {
        $levelEmoji = $this->getLevelEmoji($record->level->getName());
        $message = "{$levelEmoji} *{$record->level->getName()}* in `{$record->channel}`\n";
        $message .= "> " . str_replace("\n", "\n> ", $record->message);

        $attachments = [];

        if (isset($record->context['exception']) && $record->context['exception'] instanceof Throwable) {
            $exception = $record->context['exception'];

            $attachments[] = [
                'color' => $this->getColor($record->level->getName()),
                'title' => get_class($exception) . ': ' . $exception->getMessage(),
                'text' => "```{$exception->getFile()}:{$exception->getLine()}\n\n{$exception->getTraceAsString()}```",
                'fields' => [
                    ['title' => 'File', 'value' => $exception->getFile() . ':' . $exception->getLine(), 'short' => false],
                ],
            ];

            $extraContext = array_filter($record->context, fn($key) => $key !== 'exception', ARRAY_FILTER_USE_KEY);
            if (!empty($extraContext)) {
                $attachments[] = [
                    'color' => '#aaaaaa',
                    'title' => 'Context',
                    'text' => '```' . json_encode($extraContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '```',
                ];
            }
        }

        $payload = [
            'channel_id' => $this->channelId,
            'message' => $message,
        ];

        if (!empty($attachments)) {
            $payload['props'] = ['attachments' => $attachments];
        }

        try {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/v4/posts", $payload);
        } catch (\Exception $e) {
            error_log("Mattermost log send failed: " . $e->getMessage());
        }
    }

    private function getLevelEmoji(string $level): string
    {
        return match(strtoupper($level)) {
            'DEBUG'     => '🔍',
            'INFO'      => 'ℹ️',
            'NOTICE'    => '📌',
            'WARNING'   => '⚠️',
            'ERROR'     => '🚨',
            'CRITICAL'  => '💥',
            'ALERT'     => '🚨🚨',
            'EMERGENCY' => '🆘',
            default     => '📝',
        };
    }

    private function getColor(string $level): string
    {
        return match(strtoupper($level)) {
            'DEBUG'     => '#3498db',
            'INFO'      => '#2ecc71',
            'NOTICE'    => '#9b59b6',
            'WARNING'   => '#f39c12',
            'ERROR'     => '#e74c3c',
            'CRITICAL',
            'ALERT',
            'EMERGENCY' => '#c0392b',
            default     => '#95a5a6',
        };
    }
}
