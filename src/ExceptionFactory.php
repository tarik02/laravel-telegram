<?php

namespace Tarik02\LaravelTelegram;

use Tarik02\LaravelTelegram\Contracts\TelegramApi;
use Tarik02\Telegram\Methods\Method;

use Tarik02\LaravelTelegram\Exceptions\{
    Telegram\BotWasBlockedByUser,
    Telegram\QueryResponseOutdated,
    MethodCallException,
    TelegramApiException
};

/**
 * Class ExceptionFactory
 * @package Tarik02\LaravelTelegram
 */
class ExceptionFactory
{
    const EXCEPTION_MAP = [
        400 => [
            'Bad Request: query is too old and response timeout expired or query ID is invalid' => QueryResponseOutdated::class,
        ],
        403 => [
            'Forbidden: bot was blocked by the user' => BotWasBlockedByUser::class,
        ],
    ];

    /**
     * @param TelegramApi $api
     * @param Method $method
     * @param int $code
     * @param string $message
     * @return TelegramApiException
     */
    public function createTelegramException(TelegramApi $api, Method $method, int $code, string $message): TelegramApiException
    {
        if (isset(static::EXCEPTION_MAP[$code][$message])) {
            $exceptionClass = static::EXCEPTION_MAP[$code][$message];

            return new $exceptionClass($api, $method, $message, $code);
        }

        return new MethodCallException($api, $method, $message, $code);
    }
}
