<?php

namespace Tarik02\LaravelTelegram\Console\Commands;

use Illuminate\Console\Command;
use Tarik02\Telegram\Methods\GetWebhookInfo;

use Tarik02\LaravelTelegram\Contracts\{
    Bot,
    BotFactory
};

/**
 * Class TelegramWebhookGetInfo
 * @package Tarik02\LaravelTelegram\Console\Commands
 */
class TelegramWebhookGetInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook:get-info {--bot=main}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get bot webhook';

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

        $info = $bot->getApi()->getWebhookInfo(
            GetWebhookInfo::make()
        );

        $this->table(
            ['key', 'value'],
            [
                ['url', $info->url()],
                ['has custom certificate', $info->hasCustomCertificate() ? 'true' : 'false'],
                ['pending update count', $info->pendingUpdateCount()],
                ['ip address', $info->ipAddress() ?? '<null>'],
                ['last error date', $info->lastErrorDate() ? date('Y.m.d H:i:s', $info->lastErrorDate()) : '<null>'],
                ['last error message', $info->lastErrorMessage() ?? '<null>'],
                ['max connections', $info->maxConnections() ?? '<null>'],
                ['allowed updates', $info->allowedUpdates() ?? '<null>'],
            ]
        );

        return 0;
    }
}
