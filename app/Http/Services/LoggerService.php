<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Log;

class LoggerService
{
    protected static string $mode = 'local';
    public static function debug(string $message, array $context = []): void
    {
        if (app()->environment(self::$mode)) {
            Log::debug($message, $context);
        }
    }

    public static function warning(string $message, array $context = []): void
    {
        if (app()->environment(self::$mode)) {
            Log::warning($message, $context);
        }
    }

    public static function info(string $message, array $context = []): void
    {
        if (app()->environment(self::$mode)) {
            Log::info($message, $context);
        }
    }
}
