<?php

namespace App\Http\Services;

use DefStudio\Telegraph\Facades\Telegraph;
use Exception;

class SubscriptionService
{
    protected $botModel;

    public function __construct($botModel)
    {
        $this->botModel = $botModel;
    }

    public function checkSubscription($channelName, $userId): bool
    {
        try {
            $response = Telegraph::bot($this->botModel)
                ->chat($channelName)
                ->chatMember($userId)
                ->send();

            $memberInfo = $response->json();

            LoggerService::info('Ответ', ['response' => $response]);

            // Проверяем статус пользователя
            if (isset($memberInfo['result']['status'])) {
                $status = $memberInfo['result']['status'];

                if ($status === 'member' || $status === 'administrator' || $status === 'creator') {
                    return true;
                }
            } else {
                LoggerService::info('Не удалось получить статус пользователя', ['memberInfo' => $memberInfo]);
            }
            return false;
        } catch (Exception $e) {
            LoggerService::info('Ошибка при проверке подписки на канал', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
