<?php

namespace App\Console\Commands\Telegram\Bot;

use Telegram\Bot\Commands\Command;

class InfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected string $name = 'info';

    /**
     * The console command description.
     */
    protected string $description = 'Get system information';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $phpVersion = phpversion();
        $laravelVersion = app()->version();
        $botName = $this->getTelegram()->getMe()->getFirstName();
        $serverTime = now()->format('Y-m-d h:i:s A');
        $timezone = now()->getTimezone()->getName();

        // Use HTML instead - more reliable!
        $text = "🤖 <b>Bot Information:</b>\n\n".
            "<b>Bot Name:</b> {$botName}\n".
            "<b>PHP Version:</b> {$phpVersion}\n".
            "<b>Laravel Version:</b> {$laravelVersion}\n".
            "<b>Server Time:</b> {$serverTime}\n".
            "<b>Timezone:</b> {$timezone}";

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'HTML',  // Use HTML instead of Markdown
        ]);
    }
}
