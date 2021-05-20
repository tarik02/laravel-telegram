<?php

namespace Tarik02\LaravelTelegram;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\RouteRegistrar;
use Tarik02\LaravelTelegram\Contracts\Bot;
use Tarik02\LaravelTelegram\Http\Controllers\WebhookController;

/**
 * Class Telegram
 * @package Tarik02\LaravelTelegram
 */
class Telegram
{
    /**
     * @var Application
     */
    protected Application $app;

    /**
     * @var RouteRegistrar
     */
    protected RouteRegistrar $routeRegistrar;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param Application $app
     * @return void
     */
    public function __construct(Application $app, RouteRegistrar $routeRegistrar)
    {
        $this->app = $app;
        $this->routeRegistrar = $routeRegistrar;

        $this->config = $app['config']->get('telegram', []);
    }

    /**
     * @param string $name
     * @return Bot
     */
    public function bot(string $name): Bot
    {
        return $this->app["telegram.bots.{$name}"];
    }

    /**
     * @return array[]
     */
    public function botConfigs(): array
    {
        return $this->config['bots'] ?? [];
    }

    /**
     * @return string[]
     */
    public function botNames(): array
    {
        return \array_keys($this->botConfigs());
    }

    /**
     * @return void
     */
    public function webhookRoutes(): void
    {
        foreach ($this->botConfigs() as $name => $config) {
            $webhook = $config['webhook'] ?? null;

            if ($webhook === null) {
                continue;
            }

            $route = $this->routeRegistrar->post(
                $webhook['path'] ?? "/{$config['token']}"
            );

            if (isset($webhook['domain'])) {
                $route->domain($webhook['domain']);
            }

            $route->name('telegram.webhook.' . $name);

            $route->uses([WebhookController::class, 'index']);
        }
    }
}
