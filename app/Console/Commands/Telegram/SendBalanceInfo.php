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
                            {--type= : Type of balance info (home/godown, default: home)}';

    protected $description = 'Send DESCO balance information via Telegram bot';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $type = strtolower($this->option('type') ?: 'home');

        if (! in_array($type, ['home', 'godown'])) {
            $this->error('❌ Invalid type. Allowed values: home, godown');
            Log::error('Invalid type provided for Telegram balance info command.', ['type' => $type]);

            return;
        }

        $accountNo = $this->option('accountNo');
        if (! $accountNo) {
            $accountNo = $type === 'godown'
                ? config('services.desco.godown_account_no')
                : config('services.desco.home_account_no');
        }

        if (! $accountNo) {
            $this->error("❌ Account number is not configured for type '{$type}'. Please provide --accountNo or set it in config.");
            Log::error("Account number is missing for Telegram balance info command for type: {$type}.");

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
        $accountNo = $d['accountNo'] ?? 'N/A';
        $customerName = $d['customerName'] ?? 'N/A';
        $contactNo = $d['contactNo'] ?? 'N/A';
        $meterNo = $d['meterNo'] ?? 'N/A';
        $sanctionLoad = $d['sanctionLoad'] ?? 'N/A';
        $readingTime = $d['readingTime'] ?? now()->format('Y-m-d H:i:s');

        $text = "🏢 <b>Godown Balance</b>\n━━━━━━━━━━━━━━━━━━━━\n\n".
            "🔢 <b>Account:</b> <code>{$accountNo}</code>\n".
            "🏗️ <b>Name:</b> {$customerName}\n".
            "📞 <b>Contact:</b> {$contactNo}\n".
            "{$emoji} <b>Balance:</b> ৳ ".number_format($balance, 2)."\n".
            "⚡ <b>Meter:</b> <code>{$meterNo}</code>\n".
            "🏭 <b>Load:</b> {$sanctionLoad} kW\n".
            "📅 <b>Reading:</b> {$readingTime}\n\n";

        if ($balance < config('services.desco.low_balance_threshold')) {
            $text .= "⚠️ <b>Low Balance Alert!</b>\nPlease recharge the godown meter soon.\n\n";
        }

        $text .= "━━━━━━━━━━━━━━━━━━━━\n".
            '<i>'.now()->format('d M Y, h:i A').'</i>';

        return $text;
    }
}
