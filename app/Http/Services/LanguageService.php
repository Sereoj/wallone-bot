<?php

namespace App\Http\Services;

use App\Models\User;

class LanguageService
{
    /**
     * @param int $telegram_id
     * @return bool
     */
    public function getLanguage(int $telegram_id): string
    {
        return User::query()
                ->where('telegram_user_id', $telegram_id)
                ->first()->language_code || 'ru';
    }
}
