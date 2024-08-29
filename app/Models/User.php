<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'firstName',
        'lastName',
        'telegram_id',
        'telegraph_chat_id',
        'access_token',
        'language_code',
        'target',
    ];

    public function chat()
    {
        return $this->belongsTo(TelegraphChat::class, 'telegraph_chat_id', 'id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
