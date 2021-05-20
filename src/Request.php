<?php

namespace Tarik02\LaravelTelegram;

use Illuminate\Support\Traits\Macroable;
use Tarik02\LaravelTelegram\Contracts\Bot;
use Tarik02\Telegram\Entities\Update;

/**
 * Class Request
 * @package Tarik02\LaravelTelegram
 */
class Request
{
    use Macroable;

    /**
     * @var Bot
     */
    protected Bot $bot;

    /**
     * @var Update
     */
    protected Update $update;

    /**
     * @param Bot $bot
     * @param Update $update
     * @return void
     */
    public function __construct(Bot $bot, Update $update)
    {
        $this->bot = $bot;
        $this->update = $update;
    }

    /**
     * @return Bot
     */
    public function bot(): Bot
    {
        return $this->bot;
    }

    /**
     * @return Update
     */
    public function update(): Update
    {
        return $this->update;
    }
}
