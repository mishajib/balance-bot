<?php

namespace App\Console\Commands\Telegram\Bot;

use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected string $name = 'start';

    /**
     * The console command description.
     */
    protected string $description = 'Start the bot';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->replyWithMessage([
            'text' => "Welcome! 👋\n\nI'm your information bot. Use /help to see available commands.",
        ]);
    }
}
