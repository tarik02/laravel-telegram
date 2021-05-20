<?php

namespace Tarik02\LaravelTelegram\Console\Commands;

use Illuminate\Console\Command;

use Tarik02\LaravelTelegram\{
    Contracts\Kernel,
    Contracts\RequestFactory,
    Exceptions\TelegramApiRequestException,
    ResponseWithReply,
    Telegram
};
use Tarik02\Telegram\Methods\{
    DeleteWebhook,
    GetUpdates
};

/**
 * Class TelegramUpdatesGet
 * @package Tarik02\LaravelTelegram\Console\Commands
 */
class TelegramUpdatesGet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:updates:get {--bot=main}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start long polling for updates';

    /**
     * @var Kernel
     */
    protected Kernel $kernel;

    /**
     * @var Telegram
     */
    protected Telegram $telegram;

    /**
     * @var RequestFactory
     */
    protected RequestFactory $requestFactory;

    /**
     * @param Kernel $kernel
     * @param Telegram $telegram
     * @param RequestFactory $requestFactory
     * @return void
     */
    public function __construct(Kernel $kernel, Telegram $telegram, RequestFactory $requestFactory)
    {
        parent::__construct();

        $this->kernel = $kernel;
        $this->telegram = $telegram;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $botName = $this->input->getOption('bot');

        $bot = $this->telegram->bot($botName);

        $this->info('Listening for updates…');

        while (true) {
            try {
                $updates = $bot->getApi()->getUpdates(
                    GetUpdates::make()
                        ->withLimit(1)
                        ->withTimeout(1)
                );
            } catch (TelegramApiRequestException $e) {
                if ($e->getMessage() === 'Conflict: can\'t use getUpdates method while webhook is active; use deleteWebhook to delete the webhook first') {
                    if ($this->confirm('Can\'t get updates while webhook is set. Delete webhook and continue?')) {
                        $this->info('Deleting webhook…');

                        if ($bot->getApi()->deleteWebhook(DeleteWebhook::make())) {
                            $this->info('Webhook was deleted.');
                        } else {
                            $this->info('Webhook was not deleted.');
                        }

                        continue;
                    } else {
                        $this->error('Aborted since webhook is active for this bot');
                        return 1;
                    }
                }

                throw $e;
            }

            if ($updates->count() === 0) {
                continue;
            }

            $this->info(
                \sprintf(
                    'Received %s updates. Dispatching them…',
                    $updates->count()
                )
            );

            foreach ($updates as $update) {
                $request = $this->requestFactory->create($bot, $update);

                $this->kernel->withRequest($request, function () use ($bot, $update, $request) {
                    $response = $this->kernel->handle($request);

                    // Mark the update as successful
                    $bot->getApi()->getUpdates(
                        GetUpdates::make()
                            ->withOffset($update->updateId() + 1)
                            ->withLimit(1)
                    );

                    if ($response instanceof ResponseWithReply) {
                        foreach ($response->getReplies() as $reply) {
                            $bot->getApi()->call($reply);
                        }
                    }
                });
            }
        }

        return 0;
    }
}
