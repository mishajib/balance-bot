<?php

namespace App\Console\Commands\Telegram\Bot;

use Telegram\Bot\Commands\Command;

class TimeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected string $name = 'time';

    /**
     * The console command description.
     */
    protected string $description = 'Get current server time';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $serverTime = now()->format('Y-m-d h:i:s A');
        $timezone = now()->getTimezone()->getName();

        $text = "🕒 <b>Current Server Time:</b>\n\n".
            "<b>Time:</b> {$serverTime}\n".
            "<b>Timezone:</b> {$timezone}";

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'HTML',  // Use HTML instead of Markdown
        ]);
    }
}
