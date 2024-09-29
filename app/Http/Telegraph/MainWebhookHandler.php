<?php

namespace App\Http\Telegraph;

use App\Http\Services\LanguageService;
use App\Http\Services\LoggerService;
use App\Http\Services\MessageService;
use App\Http\Services\SubscriptionService;
use App\Http\Services\UserService;
use App\Http\Utils\JsonDecoder;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Request;

class MainWebhookHandler extends WebhookHandler
{
    protected TelegraphBot $botModel;
    protected TelegraphChat $chatModel;
    protected string $language_code;
    protected int $telegramChatId;
    protected int $userId;
    protected Request $request;
    protected SubscriptionService $subscriptionService;
    private MessageService $messageService;
    protected JsonDecoder $jsonDecoder;
    protected string $path = "config.json";

    public function __construct()
    {
        parent::__construct();
    }

    protected function getSiteUTM(int $accountId): string
    {
        $channels = $this->jsonDecoder;
        $site = $channels->getChannel('site');
        return $site && $accountId ? "$site/connect?s=tg&id=$accountId" : 'https://wallone.app';
    }

    protected function getPath(): string
    {
        return $this->path;
    }

    public function getImagePath($config, $name): string
    {
        if(file_exists($config->getImages($name)))
        {
            return storage_path($config->getImages($name));
        }
        return storage_path($config->getImages("default"));
    }

    protected function setupChat(): void
    {
        LoggerService::info("Запускаю setupChat");

        $this->jsonDecoder = new JsonDecoder($this->getPath());

        try {
            $this->setUserId();
            $this->setLanguageCode();
            $this->messageService = new MessageService($this->language_code);
            $this->setBotModel();
        } catch (\Exception $exception) {
            LoggerService::info($exception->getMessage());
        }

        parent::setupChat();
    }

    private function setUserId(): void
    {
        $this->userId = $this->message?->from()->id() ?? $this->callbackQuery?->message()?->chat()->id();
        LoggerService::info("Установка userId: $this->userId");
    }

    private function setLanguageCode(): void
    {
        $this->language_code = $this->message?->from()?->languageCode() ?? LanguageService::getLanguage($this->userId);
        LoggerService::info("Установка languageCode: $this->language_code");
        App()->setLocale($this->language_code);
    }

    private function setBotModel(): void
    {
        if (isset($this->bot)) {
            $this->botModel = $this->bot;
            $this->subscriptionService = new SubscriptionService($this->botModel);
            LoggerService::info("Установка botModel: $this->bot");
        }
    }

    public function start()
    {
        LoggerService::info("Запускаю start");

        $config = $this->jsonDecoder;
        $this->chatModel = $this->chat;
        $this->setTelegramChatId();
        $this->registerUser();

        $this->chat->photo($this->getImagePath($config, "start"))
            ->message($this->messageService->message('welcome'))
            ->send();

        $this->chat->message($this->messageService->message('welcome.step1'))->send();

        $this->messageId = $this->chat
            ->message($this->messageService->message('welcome.step2'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make($this->messageService->message('continue'))
                    ->action("continue")
                    ->param('id', "continue")
            ]))->send()->telegraphMessageId();
    }

    private function setTelegramChatId(): void
    {
        $this->telegramChatId = $this->message->chat()->id() ?? $this->callbackQuery?->message()?->chat()->id();
        LoggerService::info("Установка telegramChatID: $this->telegramChatId");
    }

    private function registerUser(): void
    {
        $from = $this->message->from();
        $telegram_user_id = $from->id();

        if (!UserService::exists($telegram_user_id)) {
            UserService::create([
                'firstName' => $from->firstName(),
                'lastName' => $from->lastName(),
                'telegram_id' => $telegram_user_id,
                'telegraph_chat_id' => $this->chat->id,
                'access_token' => '',
                'language_code' => $this->language_code,
                'target' => explode(" ", $this->message->text())[1] ?? 'telegram_target'
            ]);
            LoggerService::info("Добавляю пользователя {$from->username()} в базу данных");
        }
    }

    public function continue()
    {
        $action = $this->data->get('id');
        $config = $this->jsonDecoder;
        $buttons = $this->createSocialButtons($config);

        switch ($action) {
            case "continue":
                $this->sendSubscriptionSteps($this->getImagePath($config, 'continue'), $buttons);
                break;
            case "check":
                $this->checkSubscriptionAndSendResponse($this->getImagePath($config, 'check'));
                break;
            case "verify":
                break;
        }
    }

    private function createSocialButtons(JsonDecoder $channels): Keyboard
    {
        return Keyboard::make()->buttons([
            Button::make(__('messages.social.youtube'))->url($channels->getChannel('youtube')),
            Button::make(__('messages.social.tiktok'))->url($channels->getChannel('tiktok')),
            Button::make(__('messages.social.telegram'))->url($channels->getChannel('telegram')),
            Button::make(__('messages.social.vk'))->url($channels->getChannel('vk')),
        ]);
    }

    private function sendSubscriptionSteps(string $imagePath, Keyboard $buttons): void
    {
        $this->chat->photo($imagePath)
            ->message($this->messageService->message('sub.step1'))
            ->keyboard($buttons)
            ->send();

        $this->chat->message($this->messageService->message('sub.step2'))
            ->keyboard(Keyboard::make()
                ->button($this->messageService->message('sub.check'))
                ->action('continue')->param('id', "check"))
            ->send();
    }

    private function checkSubscriptionAndSendResponse(string $imagePath): void
    {
        if ($this->subscriptionService->checkSubscription(getenv('TG_CHANNEL_URL'), $this->userId)) {
            $this->chat->photo($imagePath)
                ->message($this->messageService->message('register'))
                ->send();

            $this->chat->message($this->messageService->message('register.step1'))
                ->keyboard(Keyboard::make()->buttons([
                    Button::make($this->messageService->message('visit'))
                        ->url($this->getSiteUTM($this->userId)),
                ]))->send();
            $this->sendVerify();
        }
    }

    private function sendVerify()
    {
        $this->chat
            ->message($this->messageService->message('verify'))
            ->keyboard(Keyboard::make()
                ->button($this->messageService->message('sub.check'))
                ->action('continue')->param('id', "verify"))
            ->send();
    }
}
