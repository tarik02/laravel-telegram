<?php

namespace Tarik02\LaravelTelegram\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Tarik02\Telegram\Methods\Method;

/**
 * Class MethodCalling
 * @package Tarik02\LaravelTelegram\Events
 */
class MethodCalling
{
    use Dispatchable;

    /**
     * @var Method
     */
    public Method $method;

    /**
     * @param Method $method
     * @return void
     */
    public function __construct(Method $method)
    {
        $this->method = $method;
    }
}
