<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Log;

class LoggerService
{
    protected static string $mode = 'local';
    protected static string $channelName = 'telegram';
    public static function debug(string $message, array $context = []): void
    {
        if (app()->environment(self::$mode)) {
            Log::channel(self::$channelName)->debug($message, $context);
        }
    }

    public static function warning(string $message, array $context = []): void
    {
        if (app()->environment(self::$mode)) {
            Log::channel(self::$channelName)->warning($message, $context);
        }
    }

    public static function info(string $message, array $context = []): void
    {
        if (app()->environment(self::$mode)) {
            Log::channel(self::$channelName)->info($message, $context);
        }
    }
}
