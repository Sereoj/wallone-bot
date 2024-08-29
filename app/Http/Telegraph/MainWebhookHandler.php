<?php

namespace App\Http\Telegraph;


use App\Http\Services\LoggerService;
use App\Http\Services\UserService;

use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class MainWebhookHandler extends WebhookHandler
{
    //@wallone_bot
    protected TelegraphBot $botModel;
    protected TelegraphChat $chatModel;
    protected string $language_code = "ru";
    protected int $telegramChat;
    protected Request $request;


//    public function handle(Request $request, TelegraphBot $bot): void
//    {
//        Log::build(['driver' => 'single', 'path' => storage_path('logs/telegram.log')])->info($request);
//
//        $this->botModel = $bot;
//        $this->request = $request;
//
//        LoggerService::info('handle');
//    }
//    protected function setupChat(): void
//    {
//        $this->language_code = $this->message?->from()?->languageCode();
//        //$this->telegramChat = $this->message?->chat()->id();
//        $this->chatModel = $this->chat;
//
//        App::setLocale($this->language_code);
//
//        LoggerService::info('setupChat');
//        parent::setupChat();
//    }

    public function id(): void
    {
        $this->chat->html("Chat ID: {$this->chat->chat_id}")->send();
    }

    public function test()
    {
        //TelegraphBot::approveChatJoin( $chat_id, $tg_user_id)->send();
    }

    public function start()
    {
            $text = explode(" ", $this->message->text());
            $from = $this->message->from();
            $chat_id = $this->message->chat()->id();

            $telegram_user_id = $from->id();

            //Если пользователя нет, то создаём
            if(!UserService::exists($telegram_user_id)){
                UserService::create([
                    'firstName' => $from->firstName(),
                    'lastName'=> $from->lastName(),
                    'telegram_id' => $telegram_user_id,
                    'chat_id' => $chat_id,
                    'access_token' => '',
                    'language_code' => $this->language_code,
                    'target' => $text[1] ?? 'telegram_target'
                ]);
            }
            $this->chat->message(__('messages.welcome'))
                ->send();

            $this->chat->message(__('messages.welcome.step1'))
                ->send();

            $this->messageId = $this->chat->message(__('messages.welcome.step2'))
                ->keyboard(Keyboard::make()->buttons([
                    Button::make(__('messages.continue'))
                        ->action("continue")
                        ->param('id', "continue-$telegram_user_id")
                ]))->send()->telegraphMessageId();
    }

    public function continue()
    {
//        switch ($action[0])
//        {
//            case "continue":
//                $this->chat->message(__('messages.sub.step1'))->keyboard(
//                    Keyboard::make()->buttons([
//                        Button::make(__('messages.social.vk'))->url('https://test.it'),
//                        Button::make(__('messages.social.youtube'))->url('https://test.it'),
//                        Button::make(__('messages.social.tiktok'))->url('https://test.it'),
//                        Button::make(__('messages.social.telegram'))->url('https://test.it'),
//                        Button::make(__('messages.social.telegram'))->url('https://test.it')
//                    ])
//                )->send();
//
//                $this->chat->message(__('messages.sub.step2'))
//                    ->keyboard(Keyboard::make()
//                    ->button(__('messages.sub.check'))
//                        ->action('check')->param('id', "check")
//                    )->send();
//                break;
//        }
    }

    public function check()
    {
//        if(env('TG_CHANNEL_ID'))
//        $value = $this->data->get('id');
//        $action = explode('-',$value);
//        $this->chat->message($value)->send();
    }

}
