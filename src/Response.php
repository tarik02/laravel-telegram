<?php

namespace Tarik02\LaravelTelegram;

use Tarik02\LaravelTelegram\Exceptions\ResponseException;
use Tarik02\Telegram\Contracts\Response as ResponseContract;
use Tarik02\Telegram\Methods\Method;

/**
 * Class Response
 * @package Tarik02\LaravelTelegram
 */
abstract class Response implements ResponseContract
{
    /**
     * @return void
     */
    public function throwResponse(): void
    {
        throw new ResponseException($this);
    }

    /**
     * @param Method ...$replies
     * @return self
     */
    public static function reply(Method ...$replies): self
    {
        return new ResponseWithReply($replies);
    }

    /**
     * @return self
     */
    public static function handled(): self
    {
        return new ResponseHandled();
    }
}
