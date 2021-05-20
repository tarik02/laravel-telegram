<?php

namespace Tarik02\LaravelTelegram;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\SortedMiddleware;
use Illuminate\Support\Facades\Facade;
use Tarik02\LaravelTelegram\Contracts\Kernel as KernelContract;
use Tarik02\LaravelTelegram\Exceptions\ResponseException;
use Tarik02\Telegram\Entities\Update;

use Tarik02\LaravelTelegram\Events\{
    RequestHandled,
    RequestHandling
};

/**
 * Class Kernel
 * @package Tarik02\LaravelTelegram
 */
class Kernel implements KernelContract
{
    /**
     * @var Application
     */
    protected Application $app;

    /**
     * @var string[]
     */
    protected array $middleware = [
        //
    ];

    /**
     * @var array[]
     */
    protected array $middlewareGroups = [
        //
    ];

    /**
     * @var string[]
     */
    protected array $namedMiddleware = [
        //
    ];

    /**
     * @var string[]
     */
    protected array $middlewarePriority = [
        //
    ];

    /**
     * @param Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param Bot $bot
     * @param callable $callback
     * @return mixed
     */
    public function withBot(Bot $bot, callable $callback)
    {
        $request = new Request($bot, Update::make());

        return $this->withRequest($request, $callback);
    }

    /**
     * @param Request $request
     * @param callable $callback
     * @return mixed
     */
    public function withRequest(Request $request, callable $callback)
    {
        $oldRequest = ($this->app->has('telegram.request')
            ? $this->app->get('telegram.request')
            : null
        );
        $oldBot = ($this->app->has('telegram.bot')
            ? $this->app->get('telegram.bot')
            : null
        );
        $oldUpdate = ($this->app->has('telegram.update')
            ? $this->app->get('telegram.update')
            : null
        );

        $this->app->instance('telegram.request', $request);
        $this->app->instance('telegram.bot', $request->bot());
        $this->app->instance('telegram.update', $request->update());

        Facade::clearResolvedInstance('telegram.request');
        Facade::clearResolvedInstance('telegram.bot');
        Facade::clearResolvedInstance('telegram.update');

        try {
            return $callback();
        } finally {
            $this->app->bind('telegram.request', fn () => $oldRequest);
            $this->app->bind('telegram.bot', fn () => $oldBot);
            $this->app->bind('telegram.update', fn () => $oldUpdate);

            Facade::clearResolvedInstance('telegram.request');
            Facade::clearResolvedInstance('telegram.bot');
            Facade::clearResolvedInstance('telegram.update');
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): ?Response
    {
        return $this->withRequest($request, function () use ($request) {
            $dispatcher = $this->app->make($request->bot()->getDispatcherClass(), [
                'bot' => $request->bot(),
            ]);

            $middleware = $this->prepareMiddlewareList(
                $request->bot()
            );

            $event = new RequestHandling($request);
            $this->app['events']->dispatch($event);
            $request = $event->request;

            try {
                $response = (new Pipeline($this->app))
                    ->send($request)
                    ->through($middleware)
                    ->then(fn ($it) => $dispatcher->dispatch($it));
            } catch (ResponseException $exception) {
                $response = $exception->getResponse();
            }

            $event = new RequestHandled($request, $response);
            $this->app['events']->dispatch($event);
            return $event->response;
        });
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * @param Bot $bot
     * @return array
     */
    protected function prepareMiddlewareList(Bot $bot): array
    {
        $middleware = \array_merge(
            $this->middleware,
            $bot->getMiddleware()
        );

        $middleware = \array_merge(
            ...\array_map(
                function (string $item): array {
                    $resolved = $this->getMiddlewareByName($item);

                    if (count($resolved) !== 0) {
                        return $resolved;
                    }

                    return [$item];
                },
                $middleware
            ),
        );

        return (new SortedMiddleware($this->middlewarePriority, $middleware))->all();
    }

    /**
     * @param string $name
     * @return array
     */
    protected function getMiddlewareByName(string $name): array
    {
        if (isset($this->middlewareGroups[$name])) {
            return $this->middlewareGroups[$name];
        }

        if (isset($this->namedMiddleware[$name])) {
            return [
                $this->namedMiddleware[$name],
            ];
        }

        return [];
    }
}
