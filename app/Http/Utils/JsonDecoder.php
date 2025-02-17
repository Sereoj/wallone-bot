<?php

namespace App\Http\Utils;

use App\Http\Services\LoggerService;
use Exception;
use Illuminate\Support\Facades\Storage;

class JsonDecoder
{
    protected array $data;
    public function __construct(string $jsonFilePath)
    {
        try {
            $path = Storage::path($jsonFilePath);

            if (!file_exists($path)) {
                LoggerService::info("Файл JSON не найден: $path");
            }else{
                $jsonContent = file_get_contents($path);
                $this->data = json_decode($jsonContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    LoggerService::info('Ошибка при декодировании JSON: ' . json_last_error_msg());
                }
            }

        }catch (Exception $exception)
        {
            LoggerService::debug($exception);
        }
    }

    public function getChannels(): ?array
    {
        return $this->data['channels'] ?? null;
    }

    public function getChats(): ?array
    {
        return $this->data['chats'] ?? null;
    }

    public function getBots(): ?array
    {
        return $this->data['bots'] ?? null;
    }

    public function getChannel(string $key): ?string
    {
        return $this->data['channels'][$key] ?? null;
    }

    public function getImages(string $key): ?string
    {
        return $this->data['images'][$key] ?? null;
    }

    public function getTelegramChat(string $language): ?string
    {
        return $this->data['chats']['telegram'][$language] ?? null;
    }

    public function getTelegramBot(string $botName): ?string
    {
        return $this->data['bots']['telegram'][$botName] ?? null;
    }
}
