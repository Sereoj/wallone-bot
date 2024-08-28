<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserService
{
    /**
     * @param $telegram_user_id
     * @return bool
     */
    public static function exists($telegram_user_id) : bool
    {
        return User::query()
            ->where('telegram_user_id',$telegram_user_id)
            ->exists();
    }

    /**
     * @param $telegramId
     * @return Builder|Model|object|null
     */
    public function getUserByTelegramId($telegram_user_id)
    {
        return User::query()
            ->where('telegram_user_id', $telegram_user_id)
            ->first();
    }

    /**
     * @param array $values
     * @return Builder|Model
     */
    public static function updateOrCreate(array $values)
    {
        return User::query()
            ->updateOrCreate($values);
    }


    /**
     * Create a new user.
     *
     * @param array $values An associative array of column-value pairs.
     * @return Builder|Model The newly created user model.
     */
    public static function create(array $values)
    {
        return User::query()
            ->create($values);
    }
}
