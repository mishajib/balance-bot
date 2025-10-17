<?php

namespace App\Console\Commands\Telegram;

use App\Services\DescoService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendBalanceInfo extends Command
{
    protected $signature = 'telegram:send-balance-info
                            {--accountNo= : The DESCO account number}
                            {--type=godown : Type of balance info (home/godown)}';

    protected $description = 'Send DESCO balance information via Telegram bot';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $accountNo = $this->option('accountNo');
        $type = strtolower($this->option('type') ?? 'home'); // ✅ Default type is home

        if (! $accountNo) {
            $this->error('❌ Account number is required. Example: php artisan telegram:send-balance-info --accountNo=123456789 --type=home');
            Log::error('Account number is missing for Telegram balance info command.');

            return;
        }

        if (! in_array($type, ['home', 'godown'])) {
            $this->error('❌ Invalid type. Allowed values: home, godown');
            Log::error('Invalid type provided for Telegram balance info command.', ['type' => $type]);

            return;
        }

        try {
            /** @var DescoService $descoService */
            $descoService = app(DescoService::class);

            $balanceInfo = $descoService->getBalance($accountNo);

            if (! is_array($balanceInfo)) {
                $this->error('❌ Invalid data received from DESCO API.');
                Log::error('Invalid data type from DescoService::getBalance', ['data' => $balanceInfo]);

                return;
            }

            // ✅ Choose message format based on type
            $messageText = $type === 'godown'
                ? $this->godownFormat($balanceInfo)
                : $this->homeFormat($balanceInfo);

            Telegram::sendMessage([
                'chat_id' => config('telegram.bots.mybot.chat_id'),
                'text' => (string) $messageText,
                'parse_mode' => 'HTML',
            ]);

            $this->info("✅ {$type} balance info sent successfully for account: {$accountNo}");
            Log::info("Balance info ({$type}) sent successfully for account: {$accountNo}");
        } catch (ConnectionException $e) {
            $this->error('⚠️ Network connection issue while fetching DESCO balance.');
            Log::error('ConnectionException in SendBalanceInfo: '.$e->getMessage());
        } catch (Exception $e) {
            $this->error('❌ Failed to retrieve or send balance information.');
            Log::error('SendBalanceInfo Exception: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function homeFormat(array $d): string
    {
        $balance = (float) ($d['balance'] ?? 0);
        $emoji = $balance > 0 ? '💚' : '❤️';

        $text = "🏠 <b>Home Balance</b>\n━━━━━━━━━━━━━━━━━━━━\n\n".
            "🔢 <b>Account:</b> <code>{$d['accountNo']}</code>\n".
            "👤 <b>Name:</b> {$d['customerName']}\n".
            "📞 <b>Contact:</b> {$d['contactNo']}\n".
            "{$emoji} <b>Balance:</b> ৳ ".number_format($balance, 2)."\n".
            "⚡ <b>Meter:</b> <code>{$d['meterNo']}</code>\n".
            "🔌 <b>Load:</b> {$d['sanctionLoad']} kW\n".
            "📅 <b>Reading:</b> {$d['readingTime']}\n\n";

        if ($balance < config('services.desco.low_balance_threshold')) {
            $text .= "⚠️ <b>Low Balance Alert!</b>\nPlease recharge soon to avoid disconnection.\n\n";
        }

        $text .= "━━━━━━━━━━━━━━━━━━━━\n".
            '<i>'.now()->format('d M Y, h:i A').'</i>';

        return $text;
    }

    private function godownFormat(array $d): string
    {
        $balance = (float) ($d['balance'] ?? 0);
        $emoji = $balance > 0 ? '💙' : '🖤';

        $text = "🏢 <b>Godown Balance</b>\n━━━━━━━━━━━━━━━━━━━━\n\n".
            "🔢 <b>Account:</b> <code>{$d['accountNo']}</code>\n".
            "🏗️ <b>Name:</b> {$d['customerName']}\n".
            "📞 <b>Contact:</b> {$d['contactNo']}\n".
            "{$emoji} <b>Balance:</b> ৳ ".number_format($balance, 2)."\n".
            "⚡ <b>Meter:</b> <code>{$d['meterNo']}</code>\n".
            "🏭 <b>Load:</b> {$d['sanctionLoad']} kW\n".
            "📅 <b>Reading:</b> {$d['readingTime']}\n\n";

        if ($balance < config('services.desco.low_balance_threshold')) {
            $text .= "⚠️ <b>Low Balance Alert!</b>\nPlease recharge the godown meter soon.\n\n";
        }

        $text .= "━━━━━━━━━━━━━━━━━━━━\n".
            '<i>'.now()->format('d M Y, h:i A').'</i>';

        return $text;
    }
}
