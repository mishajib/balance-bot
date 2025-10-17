<?php

namespace App\Console\Commands\Telegram\Bot;

use App\Services\DescoService;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class GodownBalance extends Command
{
    protected string $name = 'godown_balance';

    protected string $description = 'Get godown balance information';

    public function handle(): void
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        try {
            $data = app(DescoService::class)->getBalance(config('services.desco.godown_account_no'));

            if (! $data) {
                $this->replyWithMessage([
                    'text' => 'Unable to fetch godown balance information.',
                ]);

                return;
            }

            $this->replyWithMessage([
                'text' => $this->format($data),
                'parse_mode' => 'HTML',
            ]);

        } catch (\Exception $e) {
            $this->replyWithMessage([
                'text' => 'An error occurred while fetching godown balance information.',
            ]);
        }
    }

    private function format(array $d): string
    {
        $balance = $d['balance'] ?? 0;
        $emoji = $balance > 0 ? '💚' : '❤️';

        $text = "🏭 <b>Godown Balance</b>\n━━━━━━━━━━━━━━━━━━━━\n\n".
            "🔢 <b>Account:</b> <code>{$d['accountNo']}</code>\n".
            "👤 <b>Name:</b> {$d['customerName']}\n".
            "📞 <b>Contact:</b> {$d['contactNo']}\n".
            "{$emoji} <b>Balance:</b> ৳ ".number_format($balance, 2)."\n".
            "⚡ <b>Meter:</b> <code>{$d['meterNo']}</code>\n".
            "🔌 <b>Load:</b> {$d['sanctionLoad']} kW\n".
            "📅 <b>Reading:</b> {$d['readingTime']}\n\n";

        // Add warning if balance is low
        if ($balance < config('services.desco.low_balance_threshold')) {
            $text .= "⚠️ <b>Low Balance Alert!</b>\n";
            $text .= "Your balance is running low. Please recharge soon to avoid disconnection.\n\n";
        }

        $text .= "━━━━━━━━━━━━━━━━━━━━\n".
            '<i>'.now()->format('d M Y, h:i A').'</i>';

        return $text;
    }
}
