<?php

namespace Tarik02\LaravelTelegram\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Tarik02\LaravelTelegram\Request;

/**
 * Class RequestHandling
 * @package Tarik02\LaravelTelegram\Events
 */
class RequestHandling
{
    use Dispatchable;

    /**
     * @var Request
     */
    public Request $request;

    /**
     * @param Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
