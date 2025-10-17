<?php

namespace App\Console\Commands\Telegram;

use Illuminate\Console\Command;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;

class GetWebhookInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:get-webhook-info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the Telegram bot webhook information';

    /**
     * Execute the console command.
     *
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $response = Telegram::getWebhookInfo();

        $this->info('Webhook Information:');
        $this->line(json_encode($response, JSON_PRETTY_PRINT));
    }
}
