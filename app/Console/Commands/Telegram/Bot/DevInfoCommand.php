<?php

namespace App\Console\Commands\Telegram\Bot;

use Telegram\Bot\Commands\Command;

class DevInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected string $name = 'dev_info';

    /**
     * The console command description.
     */
    protected string $description = 'Get developer information';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $developerName = 'MD. MAZHARUL ISLAM (SHAJIB)';
        $developerWebsite = 'https://mi-shajib.com';

        $text = "👨‍💻 <b>Developer Information:</b>\n\n".
            "<b>Name:</b> {$developerName}\n".
            "<b>Website:</b> {$developerWebsite}";

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }
}
