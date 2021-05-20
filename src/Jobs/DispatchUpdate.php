<?php

namespace Tarik02\LaravelTelegram\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Tarik02\Telegram\Entities\Update;

use Tarik02\LaravelTelegram\{
    Contracts\Kernel,
    Contracts\RequestFactory,
    ResponseWithReply,
    Telegram
};

/**
 * Class DispatchUpdate
 * @package Tarik02\LaravelTelegram\Jobs
 */
class DispatchUpdate implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * @var string
     */
    protected string $botName;

    /**
     * @var Update
     */
    protected Update $update;

    /**
     * @param string $botName
     * @param Update $update
     * @return void
     */
    public function __construct(string $botName, Update $update)
    {
        $this->botName = $botName;
        $this->update = $update;
    }

    /**
     * @param Kernel $kernel
     * @param RequestFactory $requestFactory
     * @param Telegram $telegram
     * @return void
     */
    public function handle(Kernel $kernel, RequestFactory $requestFactory, Telegram $telegram)
    {
        $bot = $telegram->bot($this->botName);
        $request = $requestFactory->create($bot, $this->update);

        $kernel->withRequest($request, function () use ($kernel, $bot, $request) {
            $response = $kernel->handle($request);

            if ($response instanceof ResponseWithReply) {
                foreach ($response->getReplies() as $reply) {
                    $bot->getApi()->call($reply);
                }
            }
        });

        return 0;
    }
}
