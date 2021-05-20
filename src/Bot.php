<?php

namespace Tarik02\LaravelTelegram;

use Tarik02\LaravelTelegram\Contracts\{
    Bot as BotContract,
    TelegramApi
};

/**
 * Class Bot
 * @package Tarik02\LaravelTelegram
 */
class Bot implements BotContract
{
    /**
     * @var TelegramApi
     */
    protected TelegramApi $api;

    /**
     * @var string
     */
    protected string $id;

    /**
     * @var array
     */
    protected array $config;

    /**
     * @param TelegramApi $api
     * @param string $id
     * @param array $config
     * @return void
     */
    public function __construct(
        TelegramApi $api,
        string $id,
        array $config
    ) {
        $this->api = $api;
        $this->id = $id;
        $this->config = $config;
    }

    /**
     * @return TelegramApi
     */
    public function getApi(): TelegramApi
    {
        return $this->api;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->config['username'] ?? null;
    }

    /**
     * @return string
     */
    public function getDispatcherClass(): string
    {
        return $this->config['dispatcher'];
    }

    /**
     * @return array|null
     */
    public function getWebhookConfig(): ?array
    {
        return $this->config['webhook'] ?? null;
    }

    /**
     * @return string[]
     */
    public function getMiddleware(): array
    {
        if (! isset($this->config['middleware'])) {
            return [];
        }

        if (\is_string($this->config['middleware'])) {
            return [
                $this->config['middleware'],
            ];
        }

        return $this->config['middleware'];
    }
}
