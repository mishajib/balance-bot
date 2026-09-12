<?php

namespace App\Console\Commands\Telegram\Bot;

use App\Services\DescoService;
use Exception;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class HomeBalance extends Command
{
    protected string $name = 'home_balance';

    protected string $description = 'Get home balance information';

    public function handle(): void
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        try {
            $accountNo = config('services.desco.home_account_no');

            if (! $accountNo) {
                $this->replyWithMessage([
                    'text' => 'Home account number is not configured.',
                ]);

                return;
            }

            $data = app(DescoService::class)->getBalance($accountNo);

            if (! $data) {
                $this->replyWithMessage([
                    'text' => 'Unable to fetch balance information.',
                ]);

                return;
            }

            $this->replyWithMessage([
                'text' => $this->format($data),
                'parse_mode' => 'HTML',
            ]);

        } catch (Exception $e) {
            $this->replyWithMessage([
                'text' => 'An error occurred while fetching balance information.',
            ]);
        }
    }

    private function format(array $d): string
    {
        $balance = (float) ($d['balance'] ?? 0);
        $emoji = $balance > 0 ? '💚' : '❤️';
        $accountNo = $d['accountNo'] ?? 'N/A';
        $customerName = $d['customerName'] ?? 'N/A';
        $contactNo = $d['contactNo'] ?? 'N/A';
        $meterNo = $d['meterNo'] ?? 'N/A';
        $sanctionLoad = $d['sanctionLoad'] ?? 'N/A';
        $readingTime = $d['readingTime'] ?? now()->format('Y-m-d H:i:s');

        $text = "🏠 <b>Home Balance</b>\n━━━━━━━━━━━━━━━━━━━━\n\n".
            "🔢 <b>Account:</b> <code>{$accountNo}</code>\n".
            "👤 <b>Name:</b> {$customerName}\n".
            "📞 <b>Contact:</b> {$contactNo}\n".
            "{$emoji} <b>Balance:</b> ৳ ".number_format($balance, 2)."\n".
            "⚡ <b>Meter:</b> <code>{$meterNo}</code>\n".
            "🔌 <b>Load:</b> {$sanctionLoad} kW\n".
            "📅 <b>Reading:</b> {$readingTime}\n\n";

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
