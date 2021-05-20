<?php

namespace Tarik02\LaravelTelegram\Console\Commands;

use Illuminate\Console\Command;
use Tarik02\LaravelTelegram\Telegram;
use Tarik02\Telegram\Methods\SetWebhook;

/**
 * Class TelegramWebhookSet
 * @package Tarik02\LaravelTelegram\Console\Commands
 */
class TelegramWebhookSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook:set {--bot=main} {url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set bot webhook';

    /**
     * @var Telegram
     */
    protected Telegram $telegram;

    /**
     * @param Telegram $telegram
     * @return void
     */
    public function __construct(Telegram $telegram)
    {
        parent::__construct();

        $this->telegram = $telegram;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $botName = $this->input->getOption('bot');
        $url = $this->input->getArgument('url');

        $bot = $this->telegram->bot($botName);

        if (empty($url)) {
            if (! isset($bot->getWebhookConfig()['path'])) {
                $this->error('Specify webhook url');
                return 1;
            }

            $url = \route("telegram.webhook.{$bot->getId()}");
        }

        $bot->getApi()->setWebhook(
            SetWebhook::make()
                ->withUrl($url)
        );

        $this->info('Webhook set successfully');

        return 0;
    }
}
