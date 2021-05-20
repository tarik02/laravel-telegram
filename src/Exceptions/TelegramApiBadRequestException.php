<?php

namespace Tarik02\LaravelTelegram\Exceptions;

use Tarik02\LaravelTelegram\Contracts\TelegramApi;
use Throwable;

/**
 * Class TelegramApiBadRequestException
 * @package Tarik02\LaravelTelegram\Exceptions
 */
final class TelegramApiBadRequestException extends TelegramApiRequestException
{
    /**
     * @var string
     */
    protected string $description;

    /**
     * @param TelegramApi $api
     * @param array $request
     * @param string $description
     * @param int $code
     * @param Throwable|null $previous
     * @return void
     */
    public function __construct(TelegramApi $api, array $request, string $description, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            $api,
            $request,
            $description,
            $code,
            $previous
        );

        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
