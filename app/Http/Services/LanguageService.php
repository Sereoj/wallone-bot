<?php

namespace App\Http\Services;

use App\Models\User;

class LanguageService
{
    /**
     * @param int $telegram_id
     * @return string
     */
    public static function getLanguage(int $telegram_id): string
    {
        return User::query()
                ->where('telegram_id', $telegram_id)
                ->first()->language_code;
    }
}
