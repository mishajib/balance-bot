<?php

namespace App\Console\Commands\Telegram;

use Illuminate\Console\Command;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;

class SetWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the Telegram bot webhook';

    /**
     * Execute the console command.
     *
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $url = url('/telegram/webhook');
        $response = Telegram::setWebhook(['url' => $url]);

        $this->info('Webhook set successfully.');
        $this->line('URL: '.$url);
        $this->line('Response: '.json_encode($response));
    }
}
