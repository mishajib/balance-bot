<?php

namespace App\Console\Commands\Telegram;

use Illuminate\Console\Command;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;

class RemoveWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:remove-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the Telegram bot webhook';

    /**
     * Execute the console command.
     *
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $response = Telegram::removeWebhook();

        $this->info('Webhook removed successfully.');
        $this->line('Response: '.json_encode($response));
    }
}
