<?php

namespace Tarik02\LaravelTelegram\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Tarik02\Telegram\Methods\Method;

/**
 * Class MethodCalled
 * @package Tarik02\LaravelTelegram\Events
 */
class MethodCalled
{
    use Dispatchable;

    /**
     * @var Method
     */
    public Method $method;

    /**
     * @var mixed
     */
    public $result;

    /**
     * @param Method $method
     * @param mixed $result
     * @return void
     */
    public function __construct(Method $method, $result)
    {
        $this->method = $method;
        $this->result = $result;
    }
}
