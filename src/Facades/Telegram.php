<?php

namespace Tarik02\LaravelTelegram\Facades;

use Illuminate\Support\Facades\Facade;
use Tarik02\LaravelTelegram\Telegram as TelegramHelper;

/**
 * Class Telegram
 * @package Tarik02\LaravelTelegram\Facades
 */
class Telegram extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return TelegramHelper::class;
    }
}
