<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DescoService
{
    private string $baseUrl;

    const CUSTOMER_ENDPOINT = '/getCustomerInfo';

    const BALANCE_ENDPOINT = '/getBalance';

    const RECHARGE_HISTORY_ENDPOINT = '/getRechargeHistory';

    const MONTHLY_CONSUMPTION_ENDPOINT = '/getCustomerMonthlyConsumption';

    public function __construct()
    {
        $this->baseUrl = config('services.desco.base_url');
    }

    /**
     * @throws ConnectionException
     */
    public function getBalance($accountNo): ?array
    {
        try {
            $responses = Http::pool(fn ($pool) => [
                $pool->as('balance')
                    ->withOptions(['verify' => false])
                    ->timeout(30)
                    ->get($this->baseUrl.self::BALANCE_ENDPOINT, [
                        'accountNo' => $accountNo,
                    ]),
                $pool->as('customer')
                    ->withOptions(['verify' => false])
                    ->timeout(30)
                    ->get($this->baseUrl.self::CUSTOMER_ENDPOINT, [
                        'accountNo' => $accountNo,
                    ]),
            ]);

            $balanceResponse = $responses['balance'];
            $customerResponse = $responses['customer'];

            // ✅ Force reading all content to close the stream
            $balanceBody = $balanceResponse->body();
            $customerBody = $customerResponse->body();

            // ✅ Decode safely
            $balanceData = $balanceResponse->successful()
                ? (json_decode($balanceBody, true)['data'] ?? json_decode($balanceBody, true))
                : [];

            $customerData = $customerResponse->successful()
                ? (json_decode($customerBody, true)['data'] ?? json_decode($customerBody, true))
                : [];

            // ✅ Explicitly destroy the responses and collected garbage
            unset($balanceResponse, $customerResponse, $responses, $balanceBody, $customerBody);
            gc_collect_cycles();

            // ✅ Merge data (balance first)
            return array_merge($customerData, $balanceData);
        } catch (Exception $e) {
            Log::error('Failed to fetch balance data', [
                'account_no' => $accountNo,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
