<?php

namespace App\Http\Telegraph;

use App\Http\Services\LoggerService;
use App\Http\Services\SubscriptionService;
use App\Http\Services\UserService;

use App\Http\Utils\JsonDecoder;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class MainWebhookHandler extends WebhookHandler
{
    //@wallone_bot
    protected TelegraphBot $botModel;
    protected TelegraphChat $chatModel;
    protected string $language_code;
    protected int $telegramChatId;
    protected int $userId;
    protected Request $request;
    protected SubscriptionService $subscriptionService;
    protected JsonDecoder $jsonDecoder;

    protected string $path = "app/config.json";

    public function __construct()
    {
        parent::__construct();
    }

    protected function getPath() : string
    {
        return $this->path ?? 'app/config.json';
    }
    protected function setupChat(): void
    {
        LoggerService::info("Запускаю setupChat");

        $jsonFilePath = storage_path($this->path);
        $this->jsonDecoder = new JsonDecoder($jsonFilePath);

        try {

            if(isset($this->message))
            {
                $language = $this->language_code = $this->message->from()?->languageCode();
                App::setLocale($language);
                LoggerService::info("Установка languageCode: $language");
            }


            if (isset($this->message)) {
                $this->userId = $this->message->from()->id();
                LoggerService::info("Установка userId: $this->userId");
            }

            if (isset($this->bot)) {
                $this->botModel = $this->bot;
                $this->subscriptionService = new SubscriptionService($this->botModel);
                LoggerService::info("Установка botModel: $this->bot");
            }

        } catch (\Exception $exception) {
            LoggerService::info($exception->getMessage());
        }

        parent::setupChat();
    }

    public function id(): void
    {
        $this->chat->html("Chat ID: {$this->chat->chat_id}")->send();
    }

    public function start()
    {
        LoggerService::info("Запускаю start");

        if (isset($this->chat)) {
            $this->chatModel = $this->chat;
            LoggerService::info("Установка chat: $this->chat");
        }

        if (isset($this->chat)) {
            $this->telegramChatId = $this->chat->chat_id;
            LoggerService::info("Установка telegramChatID: $this->telegramChatId");
        } else {
            LoggerService::info("Не удалось получить свойство telegramChatId");
        }

        $text = explode(" ", $this->message->text());
        $from = $this->message->from();

        $telegram_user_id = $from->id();

        //Если пользователя нет, то создаём
        if(!UserService::exists($telegram_user_id)){
            UserService::create([
                'firstName' => $from->firstName(),
                'lastName'=> $from->lastName(),
                'telegram_id' => $telegram_user_id,
                'telegraph_chat_id' => $this->chat->id,
                'access_token' => '',
                'language_code' => $this->language_code,
                'target' => $text[1] ?? 'telegram_target'
            ]);
            LoggerService::info("Добавляю пользователя {$from->username()} в базу данных");
        }
        $this->chat
            ->photo(storage_path('app/public/3f736a9b07572e63c1395d87232873cc.jpg'))
            ->message(__('messages.welcome'))
            ->send();

        $this->chat
            ->message(__('messages.welcome.step1'))
            ->send();

        $this->messageId = $this->chat
            ->message(__('messages.welcome.step2'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('messages.continue'))
                    ->action("continue")
                    ->param('id', "continue")
            ]))->send()->telegraphMessageId();
    }

    public function continue()
    {
        $action = $this->data->get('id');
        $channels = $this->jsonDecoder;

        $buttons = Keyboard::make()->buttons([
            Button::make(__('messages.social.youtube'))->url($channels->getChannel('youtube')),
            Button::make(__('messages.social.tiktok'))->url($channels->getChannel('tiktok')),
            Button::make(__('messages.social.telegram'))->url($channels->getChannel('telegram')),
            Button::make(__('messages.social.vk'))->url($channels->getChannel('vk')),
        ]);

        $imagePath = storage_path('app/public/3f736a9b07572e63c1395d87232873cc.jpg');

        switch ($action)
        {
            case "continue":
                $this->chat
                    ->photo($imagePath)
                    ->message(__('messages.sub.step1'))
                    ->keyboard($buttons)
                    ->send();

                $this->chat
                    ->message(__('messages.sub.step2'))
                    ->keyboard(Keyboard::make()
                    ->button(__('messages.sub.check'))
                        ->action('continue')->param('id', "check")
                    )->send();
                break;
            case "check":
                if($this->subscriptionService->checkSubscription(getenv('TG_CHANNEL_URL'), $this->chat->chat_id)){
                    $this->chat
                        ->photo($imagePath)
                        ->message(__('messages.register'))
                        ->send();

                    $this->chat
                        ->message(__('messages.register.step1'))
                        ->keyboard(Keyboard::make()->buttons([
                            Button::make(__('messages.visit'))->url($channels->getChannel('site')),
                        ]))
                        ->send();
                }
                break;
        }
    }

}
