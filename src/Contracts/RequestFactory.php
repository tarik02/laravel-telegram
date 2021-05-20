<?php

namespace Tarik02\LaravelTelegram\Contracts;

use Tarik02\LaravelTelegram\Request;
use Tarik02\Telegram\Entities\Update;

/**
 * Interface RequestFactory
 * @package Tarik02\LaravelTelegram\Contracts
 */
interface RequestFactory
{
    /**
     * @param Bot $bot
     * @param Update $update
     * @return Request
     */
    public function create(Bot $bot, Update $update): Request;
}
