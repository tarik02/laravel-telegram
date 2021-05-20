<?php

namespace Tarik02\LaravelTelegram\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;

use Tarik02\LaravelTelegram\{
    Contracts\Kernel,
    Exceptions\TelegramApiRequestException,
    Jobs\DispatchUpdate,
    Telegram
};
use Tarik02\Telegram\Methods\{
    DeleteWebhook,
    GetUpdates
};

/**
 * Class TelegramUpdatesQueue
 * @package Tarik02\LaravelTelegram\Console\Commands
 */
class TelegramUpdatesQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:updates:queue {--bot=main} {--connection=} {--queue=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start long polling for updates and dispatching them to queues';

    /**
     * @var Kernel
     */
    protected Kernel $kernel;

    /**
     * @var Telegram
     */
    protected Telegram $telegram;

    /**
     * @var Dispatcher
     */
    protected Dispatcher $dispatcher;

    /**
     * @param Kernel $kernel
     * @param Telegram $telegram
     * @param Dispatcher $dispatcher
     * @return void
     */
    public function __construct(Kernel $kernel, Telegram $telegram, Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->kernel = $kernel;
        $this->telegram = $telegram;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $botName = $this->input->getOption('bot');
        $connectionName = $this->input->getOption('connection');
        $queueName = $this->input->getOption('queue');

        $bot = $this->telegram->bot($botName);

        $this->info('Listening for updates…');

        while (true) {
            try {
                $updates = $bot->getApi()->getUpdates(
                    GetUpdates::make()
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
                    'Received %s updates. Dispatching them to queue…',
                    $updates->count()
                )
            );

            foreach ($updates as $update) {
                $job = new DispatchUpdate($bot->getId(), $update);
                if ($connectionName !== null) {
                    $job->onConnection($connectionName);
                }
                if ($queueName !== null) {
                    $job->onQueue($queueName);
                }
                $this->dispatcher->dispatch($job);
            }

            // Mark the update as successful
            $bot->getApi()->getUpdates(
                GetUpdates::make()
                    ->withOffset($update->updateId() + 1)
                    ->withLimit(\count($updates))
            );
        }

        return 0;
    }
}
