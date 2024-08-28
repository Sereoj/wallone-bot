<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'content', 'user_id'];

    protected $attributes = [
        'title' => '{"ru": "", "en": ""}',
        'content' => '{"ru": "", "en": ""}'
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
