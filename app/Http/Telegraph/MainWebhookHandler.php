<?php

namespace App\Http\Telegraph;


use App\Http\Services\UserSearchService;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Stringable;

class MainWebhookHandler extends WebhookHandler
{
    //wallone_bot
    protected string $language_code = "ru";

    protected function handleUnknownCommand(Stringable $text): void
    {
        $this->chat->message("Не могу понять вашу команду: $text")->send();
    }

    public function start()
    {
            $text = explode(" ", $this->message->text());
            $from = $this->message->from();
            $chat_id = $this->message->chat()->id();

            $telegram_user_id = $from->id();
            $this->language_code = $from->languageCode();

            App::setLocale($this->language_code);

            //Если пользователя нет, то создаём
            if(!UserService::exists($telegram_user_id)){
                UserService::сreate([
                    'firstName' => $from->firstName(),
                    'lastName'=> $from->lastName(),
                    'telegram_user_id' => $telegram_user_id,
                    'chat_id' => $chat_id,
                    'access_token' => '',
                    'language_code' => $this->language_code,
                    'target' => $text[1] ?? 'telegram_target'
                ]);
            }

            $this->chat->message(__('messages.developer'))
                ->send();

            $this->chat->message(__('messages.about'))
                ->keyboard(Keyboard::make()->buttons([
                Button::make(__('messages.subscribe'))->url('https://t.me/+I_Eem62vTU9mZjFi'),
                Button::make(__('messages.visit'))->url('https://wallone.app'),
            ]))->send();
    }

}
