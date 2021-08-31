<?php

namespace Tarik02\LaravelTelegram\Providers;

use Illuminate\Support\ServiceProvider;

use GuzzleHttp\{
    Client,
    ClientInterface
};
use Tarik02\LaravelTelegram\Console\Commands\{
    TelegramUpdatesGet,
    TelegramUpdatesQueue,
    TelegramWebhookDelete,
    TelegramWebhookGetInfo,
    TelegramWebhookSet
};
use Tarik02\LaravelTelegram\{
    Api\GuzzleTelegramApi,
    Contracts\Kernel as KernelContract,
    Contracts\RequestFactory as RequestFactoryContract,
    Contracts\TelegramApi,
    Bot,
    Kernel,
    RequestFactory,
    Telegram
};

/**
 * Class TelegramServiceProvider
 * @package Tarik02\LaravelTelegram\Providers
 */
class TelegramServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(Telegram::class);

        $this->app->singleton(
            KernelContract::class,
            Kernel::class
        );

        $this->app->singleton(
            RequestFactoryContract::class,
            RequestFactory::class
        );

        $this->app->bind(
            TelegramApi::class,
            GuzzleTelegramApi::class
        );

        $this->app->when(GuzzleTelegramApi::class)
            ->needs(ClientInterface::class)
            ->give(fn () => new Client([
                'base_uri' => 'https://api.telegram.org/',
            ]));

        /** @var Telegram $telegram */
        $telegram = $this->app[Telegram::class];

        foreach ($telegram->botConfigs() as $id => $config) {
            $this->app->singleton(
                "telegram.bots.{$id}",
                fn ($app) => new Bot(
                    $app->make(TelegramApi::class, ['token' => $config['token']]),
                    $id,
                    $config
                )
            );
        }
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TelegramUpdatesGet::class,
                TelegramUpdatesQueue::class,
                TelegramWebhookDelete::class,
                TelegramWebhookGetInfo::class,
                TelegramWebhookSet::class,
            ]);
        }
    }
}
