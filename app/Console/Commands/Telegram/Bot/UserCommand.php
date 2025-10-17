<?php

namespace App\Console\Commands\Telegram\Bot;

use Telegram\Bot\Commands\Command;

class UserCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected string $name = 'user';

    /**
     * The console command description.
     */
    protected string $description = 'Get your user information';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $user = $this->getUpdate()->getMessage()->getFrom();

        $info = [
            '👤 *Your Information*',
            '',
            'ID: '.$user->getId(),
            'First Name: '.$user->getFirstName(),
            'Username: @'.($user->getUsername() ?? 'N/A'),
        ];

        $this->replyWithMessage([
            'text' => implode("\n", $info),
            'parse_mode' => 'Markdown',
        ]);
    }
}
