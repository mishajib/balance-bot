<?php

namespace App\Console\Commands\Telegram\Bot;

use Telegram\Bot\Commands\Command;

class HelpCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected string $name = 'help';

    /**
     * The console command description.
     */
    protected string $description = 'Show help information';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $commands = [
            '/start - Start the bot',
            '/help - Show this help message',
            '/info - Get system information',
            '/user - Get your user information',
            '/time - Get current server time',
            '/dev_info - Get developer information',
            '/home_balance - Get home balance information',
            '/godown_balance - Get godown balance information',
        ];

        $text = "🤖 *Available Commands:*\n\n".implode("\n", $commands);

        $this->replyWithMessage([
            'text' => $text,
        ]);
    }
}
