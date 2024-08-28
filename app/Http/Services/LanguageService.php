<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

public class LanguageService {
    
    /**
     * Получаем язык пользователя из базы данных, 
     * => в TG можно получить только при входящем сообщении.
     * @param int $telegram_id
     */
    public function getLanguage(int $telegram_id)
    {
        return User::query()->where('telegram_user_id', $telegram_id)->first()->language_code || 'ru';
    }
}