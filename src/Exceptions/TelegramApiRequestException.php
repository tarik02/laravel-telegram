<?php

namespace Tarik02\LaravelTelegram\Exceptions;

use Tarik02\LaravelTelegram\Contracts\TelegramApi;
use Throwable;

/**
 * Class TelegramApiRequestException
 * @package Tarik02\LaravelTelegram\Exceptions
 */
class TelegramApiRequestException extends TelegramApiException
{
    /**
     * @var array
     */
    protected array $request;

    /**
     * @param TelegramApi $api
     * @param array $request
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @return void
     */
    public function __construct(TelegramApi $api, array $request, string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            $api,
            $message,
            $code,
            $previous
        );

        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getRequest(): array
    {
        return $this->request;
    }
}
