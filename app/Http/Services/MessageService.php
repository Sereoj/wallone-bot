<?php

namespace App\Http\Services;

class MessageService
{
    private string $language;
    public function __construct(string $languageCode) {
        $this->language = $languageCode;
    }

    public function handleUnknownCommand(): string
    {
        return __('bot.handle_unknown_command', locale: $this->language);
    }

    public function message($action)
    {
        switch ($action)
        {
            case "welcome":
                return __('messages.welcome', locale: $this->language);
            case "welcome.step1":
                return __('messages.welcome.step1', locale: $this->language);
            case "welcome.step2":
                return __('messages.welcome.step2', locale: $this->language);
            case "continue":
                return __('messages.continue', locale: $this->language);
            case "sub.step1":
                return __('messages.sub.step1', locale: $this->language);
            case "sub.step2":
                return __('messages.sub.step2', locale: $this->language);
            case "sub.check":
                return __('messages.sub.check', locale: $this->language);
            case "register":
                return __('messages.register', locale: $this->language);
            case "register.step1":
                return __('messages.register.step1', locale: $this->language);
            case "visit":
                return __('messages.visit', locale: $this->language);
            default:
                return 'not found';
        }
    }
}
