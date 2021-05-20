<?php

namespace Tarik02\LaravelTelegram\Events;

use Illuminate\Foundation\Events\Dispatchable;

use Tarik02\LaravelTelegram\{
    Request,
    Response
};

/**
 * Class RequestHandled
 * @package Tarik02\LaravelTelegram\Events
 */
class RequestHandled
{
    use Dispatchable;

    /**
     * @var Request
     */
    public Request $request;

    /**
     * @var Response|null
     */
    public ?Response $response;

    /**
     * @param Request $request
     * @param Response|null $response
     * @return void
     */
    public function __construct(Request $request, ?Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
