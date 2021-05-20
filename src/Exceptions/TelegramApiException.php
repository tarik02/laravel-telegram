<?php

namespace Tarik02\LaravelTelegram\Exceptions;

use Exception;
use Tarik02\LaravelTelegram\Contracts\TelegramApi;
use Throwable;

/**
 * Class TelegramApiException
 * @package Tarik02\LaravelTelegram\Exceptions
 */
class TelegramApiException extends Exception
{
    /**
     * @var TelegramApi
     */
    protected TelegramApi $api;

    /**
     * @param TelegramApi $api
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @return void
     */
    public function __construct(TelegramApi $api, string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->api = $api;
    }

    /**
     * @return TelegramApi
     */
    public function getApi(): TelegramApi
    {
        return $this->api;
    }
}
