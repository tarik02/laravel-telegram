<?php

namespace Tarik02\LaravelTelegram;

use Tarik02\Telegram\Entities\Update;

use Tarik02\LaravelTelegram\Contracts\{
    Bot,
    RequestFactory as RequestFactoryContract
};

/**
 * Class RequestFactory
 * @package Tarik02\LaravelTelegram
 */
final class RequestFactory implements RequestFactoryContract
{
    /**
     * @param Bot $bot
     * @param Update $update
     * @return Request
     */
    public function create(Bot $bot, Update $update): Request
    {
        return new Request($bot, $update);
    }
}
