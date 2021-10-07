<?php

namespace Tarik02\LaravelTelegram\Console\Commands;

use Illuminate\Console\Command;
use Tarik02\Telegram\Methods\DeleteWebhook;

use Tarik02\LaravelTelegram\Contracts\{
    Bot,
    BotFactory
};

/**
 * Class TelegramWebhookDelete
 * @package Tarik02\LaravelTelegram\Console\Commands
 */
class TelegramWebhookDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook:delete {--bot=main}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete bot webhook';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $botName = $this->input->getOption('bot');

        /** @var Bot $bot */
        $bot = $this->getLaravel()->get(BotFactory::class)->createBot($botName);

        $bot->getApi()->deleteWebhook(DeleteWebhook::make());

        $this->info('Webhook deleted successfully');

        return 0;
    }
}
