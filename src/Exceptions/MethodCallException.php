<?php

namespace Tarik02\LaravelTelegram\Exceptions;

use Tarik02\LaravelTelegram\Contracts\TelegramApi;
use Tarik02\Telegram\Methods\Method;
use Throwable;

/**
 * Class MethodCallException
 * @package Tarik02\LaravelTelegram\Exceptions
 */
class MethodCallException extends TelegramApiRequestException
{
    /**
     * @var Method
     */
    protected Method $method;

    /**
     * @param TelegramApi $api
     * @param array $request
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @return void
     */
    public function __construct(TelegramApi $api, Method $method, string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            $api,
            $method->toPayload(),
            $message,
            $code,
            $previous
        );

        $this->method = $method;
    }

    /**
     * @return Method
     */
    public function getMethod(): Method
    {
        return $this->method;
    }
}
