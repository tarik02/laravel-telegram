<?php

namespace Tarik02\LaravelTelegram\Contracts;

/**
 * Interface Bot
 * @package Tarik02\LaravelTelegram\Contracts
 */
interface Bot
{
    /**
     * @return TelegramApi
     */
    public function getApi(): TelegramApi;

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string|null
     */
    public function getUsername(): ?string;

    /**
     * @return string
     */
    public function getDispatcherClass(): string;

    /**
     * @return array|null
     */
    public function getWebhookConfig(): ?array;

    /**
     * @return string[]
     */
    public function getMiddleware(): array;
}
