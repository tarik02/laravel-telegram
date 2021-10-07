<?php

namespace Tarik02\LaravelTelegram\Contracts;

/**
 * Interface BotFactory
 * @package Tarik02\LaravelTelegram\Contracts
 */
interface BotFactory
{
    /**
     * @param string $name
     * @return Bot
     */
    public function createBot(string $name): Bot;
}
