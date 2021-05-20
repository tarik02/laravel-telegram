<?php

namespace Tarik02\LaravelTelegram\Events;

/**
 * Class WebhookMethodCalling
 * @package Tarik02\LaravelTelegram\Events
 */
class WebhookMethodCalling extends MethodCalling
{
    /**
     * @var bool
     */
    public $callWithWebhook = true;
}
