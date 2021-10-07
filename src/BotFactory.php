<?php

namespace Tarik02\LaravelTelegram;

use Illuminate\Contracts\Container\Container;
use Tarik02\LaravelTelegram\Contracts\BotFactory as BotFactoryContract;

/**
 * Class BotFactory
 * @package Tarik02\LaravelTelegram
 */
class BotFactory implements BotFactoryContract
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @param Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     * @return Bot
     */
    public function createBot(string $name): Bot
    {
        return $this->container->get("telegram.bots.{$name}");
    }
}
