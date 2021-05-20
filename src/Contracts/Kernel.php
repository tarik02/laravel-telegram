<?php

namespace Tarik02\LaravelTelegram\Contracts;

use Illuminate\Contracts\Foundation\Application;

use Tarik02\LaravelTelegram\{
    Request,
    Response
};

/**
 * Interface Kernel
 * @package Tarik02\LaravelTelegram\Contracts
 */
interface Kernel
{
    /**
     * @param Request $request
     * @param callable $callback
     * @return mixed
     */
    public function withRequest(Request $request, callable $callback);

    /**
     * @param Request $request
     * @return Response|null
     */
    public function handle(Request $request): ?Response;

    /**
     * @return Application
     */
    public function getApplication(): Application;
}
