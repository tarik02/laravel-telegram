<?php

namespace Tarik02\LaravelTelegram;

use Tarik02\Telegram\Methods\Method;

/**
 * Class ResponseWithReply
 * @package Tarik02\LaravelTelegram
 */
class ResponseWithReply extends Response
{
    /**
     * @var array
     */
    protected array $replies;

    /**
     * @param array $replies
     * @return void
     */
    public function __construct(array $replies)
    {
        $this->replies = $replies;
    }

    /**
     * @return Method[]
     */
    public function getReplies(): array
    {
        return $this->replies;
    }
}
