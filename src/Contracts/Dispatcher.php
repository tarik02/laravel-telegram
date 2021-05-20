<?php

namespace Tarik02\LaravelTelegram\Contracts;

use Tarik02\LaravelTelegram\{
    Request,
    Response
};

/**
 * Interface Dispatcher
 * @package Tarik02\LaravelTelegram\Contracts
 */
interface Dispatcher
{
    /**
     * @param Request $request
     * @return Response|null
     */
    public function dispatch(Request $request): ?Response;
}
