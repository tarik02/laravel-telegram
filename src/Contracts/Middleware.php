<?php

namespace Tarik02\LaravelTelegram\Contracts;

use Closure;

use Tarik02\LaravelTelegram\{
    Request,
    Response
};

/**
 * Interface Middleware
 * @package Tarik02\LaravelTelegram\Contracts
 */
interface Middleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return Response|null
     */
    public function handle(Request $request, Closure $next): ?Response;
}
