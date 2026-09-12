<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DescoService
{
    /**
     * @var string[]
     */
    private array $baseUrls;

    const CUSTOMER_ENDPOINT = '/getCustomerInfo';

    const BALANCE_ENDPOINT = '/getBalance';

    const RECHARGE_HISTORY_ENDPOINT = '/getRechargeHistory';

    const MONTHLY_CONSUMPTION_ENDPOINT = '/getCustomerMonthlyConsumption';

    public function __construct()
    {
        $configuredUrls = config('services.desco.base_urls');

        if (is_array($configuredUrls) && ! empty($configuredUrls)) {
            $this->baseUrls = array_values(array_filter($configuredUrls));
        } else {
            $primary = config('services.desco.base_url', 'https://prepaid.desco.org.bd/api/unified/customer');
            $fallback = str_contains($primary, '/unified/')
                ? str_replace('/unified/', '/tkdes/', $primary)
                : str_replace('/tkdes/', '/unified/', $primary);
            $this->baseUrls = array_values(array_unique([$primary, $fallback]));
        }
    }

    /**
     * Get candidate base URLs for a specific account (prioritizing cached working system if known).
     *
     * @return string[]
     */
    public function getBaseUrlsForAccount(?string $accountNo = null): array
    {
        if (! $accountNo) {
            return $this->baseUrls;
        }

        $cachedUrl = Cache::get("desco_base_url_{$accountNo}");
        if ($cachedUrl && in_array($cachedUrl, $this->baseUrls)) {
            return array_values(array_unique(array_merge([$cachedUrl], $this->baseUrls)));
        }

        return $this->baseUrls;
    }

    /**
     * Remember the working base URL for an account to speed up subsequent requests.
     */
    protected function rememberWorkingBaseUrl(string $accountNo, string $baseUrl): void
    {
        Cache::put("desco_base_url_{$accountNo}", $baseUrl, now()->addDays(7));
    }

    /**
     * @throws ConnectionException
     */
    public function getBalance($accountNo): ?array
    {
        $accountNo = (string) $accountNo;
        $urlsToTry = $this->getBaseUrlsForAccount($accountNo);

        foreach ($urlsToTry as $baseUrl) {
            try {
                $responses = Http::pool(fn ($pool) => [
                    $pool->as('balance')
                        ->withOptions(['verify' => false])
                        ->timeout(15)
                        ->get(rtrim($baseUrl, '/').self::BALANCE_ENDPOINT, [
                            'accountNo' => $accountNo,
                        ]),
                    $pool->as('customer')
                        ->withOptions(['verify' => false])
                        ->timeout(15)
                        ->get(rtrim($baseUrl, '/').self::CUSTOMER_ENDPOINT, [
                            'accountNo' => $accountNo,
                            'meterNo' => '',
                        ]),
                ]);

                $balanceResponse = $responses['balance'] ?? null;
                $customerResponse = $responses['customer'] ?? null;

                if ($balanceResponse instanceof \Throwable || $customerResponse instanceof \Throwable) {
                    Log::warning("DescoService pool request threw exception on {$baseUrl}", [
                        'account_no' => $accountNo,
                        'balance_error' => $balanceResponse instanceof \Throwable ? $balanceResponse->getMessage() : null,
                        'customer_error' => $customerResponse instanceof \Throwable ? $customerResponse->getMessage() : null,
                    ]);

                    continue;
                }

                $balanceData = null;
                $customerData = null;

                if ($balanceResponse && $balanceResponse->successful()) {
                    $json = $balanceResponse->json();
                    if (isset($json['data']) && is_array($json['data']) && ! empty($json['data'])) {
                        $balanceData = $json['data'];
                    }
                }

                if ($customerResponse && $customerResponse->successful()) {
                    $json = $customerResponse->json();
                    if (isset($json['data']) && is_array($json['data']) && ! empty($json['data'])) {
                        $customerData = $json['data'];
                    }
                }

                // Explicitly cleanup
                unset($balanceResponse, $customerResponse, $responses);
                gc_collect_cycles();

                // If at least one returned valid data
                if (! empty($customerData) || ! empty($balanceData)) {
                    $this->rememberWorkingBaseUrl($accountNo, $baseUrl);

                    $customerData = $customerData ?? [];
                    $balanceData = $balanceData ?? [];

                    $merged = array_merge($customerData, $balanceData);
                    if (empty($merged['accountNo'])) {
                        $merged['accountNo'] = $accountNo;
                    }

                    return $merged;
                }
            } catch (Exception $e) {
                Log::warning("DescoService getBalance attempt failed on {$baseUrl}", [
                    'account_no' => $accountNo,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::error('DescoService: Failed to fetch balance data from all available URLs', [
            'account_no' => $accountNo,
            'urls_tried' => $urlsToTry,
        ]);

        return null;
    }

    /**
     * Get customer info with fallback between unified and tkdes.
     */
    public function getCustomerInfo($accountNo, ?string $meterNo = null): ?array
    {
        $accountNo = (string) $accountNo;
        $urlsToTry = $this->getBaseUrlsForAccount($accountNo);

        foreach ($urlsToTry as $baseUrl) {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->timeout(15)
                    ->get(rtrim($baseUrl, '/').self::CUSTOMER_ENDPOINT, [
                        'accountNo' => $accountNo,
                        'meterNo' => $meterNo ?? '',
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (isset($json['data']) && is_array($json['data']) && ! empty($json['data'])) {
                        $this->rememberWorkingBaseUrl($accountNo, $baseUrl);

                        return $json['data'];
                    }
                }
            } catch (Exception $e) {
                Log::warning("DescoService getCustomerInfo failed on {$baseUrl}", [
                    'account_no' => $accountNo,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Get customer recharge history with fallback between unified and tkdes.
     */
    public function getRechargeHistory($accountNo, array $params = []): ?array
    {
        $accountNo = (string) $accountNo;
        $params['accountNo'] = $accountNo;
        $urlsToTry = $this->getBaseUrlsForAccount($accountNo);

        foreach ($urlsToTry as $baseUrl) {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->timeout(15)
                    ->get(rtrim($baseUrl, '/').self::RECHARGE_HISTORY_ENDPOINT, $params);

                if ($response->successful()) {
                    $json = $response->json();
                    if (isset($json['code']) && $json['code'] == 200 && isset($json['data']) && is_array($json['data'])) {
                        if (! empty($json['data'])) {
                            $this->rememberWorkingBaseUrl($accountNo, $baseUrl);

                            return $json['data'];
                        }
                    }
                }
            } catch (Exception $e) {
                Log::warning("DescoService getRechargeHistory failed on {$baseUrl}", [
                    'account_no' => $accountNo,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [];
    }

    /**
     * Get customer monthly consumption with fallback between unified and tkdes.
     */
    public function getCustomerMonthlyConsumption($accountNo, array $params = []): ?array
    {
        $accountNo = (string) $accountNo;
        $params['accountNo'] = $accountNo;
        $urlsToTry = $this->getBaseUrlsForAccount($accountNo);

        foreach ($urlsToTry as $baseUrl) {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->timeout(15)
                    ->get(rtrim($baseUrl, '/').self::MONTHLY_CONSUMPTION_ENDPOINT, $params);

                if ($response->successful()) {
                    $json = $response->json();
                    if (isset($json['code']) && $json['code'] == 200 && isset($json['data']) && is_array($json['data'])) {
                        if (! empty($json['data'])) {
                            $this->rememberWorkingBaseUrl($accountNo, $baseUrl);

                            return $json['data'];
                        }
                    }
                }
            } catch (Exception $e) {
                Log::warning("DescoService getCustomerMonthlyConsumption failed on {$baseUrl}", [
                    'account_no' => $accountNo,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Generic request with fallback between unified and tkdes.
     */
    public function requestWithFallback(string $endpoint, array $query = []): ?array
    {
        $accountNo = isset($query['accountNo']) ? (string) $query['accountNo'] : null;
        $urlsToTry = $this->getBaseUrlsForAccount($accountNo);

        foreach ($urlsToTry as $baseUrl) {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->timeout(15)
                    ->get(rtrim($baseUrl, '/').$endpoint, $query);

                if ($response->successful()) {
                    $json = $response->json();
                    if (isset($json['data']) && is_array($json['data']) && ! empty($json['data'])) {
                        if ($accountNo) {
                            $this->rememberWorkingBaseUrl($accountNo, $baseUrl);
                        }

                        return $json['data'];
                    }
                }
            } catch (Exception $e) {
                Log::warning("DescoService requestWithFallback failed on {$baseUrl}{$endpoint}", [
                    'error' => $e->getMessage(),
                    'query' => $query,
                ]);
            }
        }

        return null;
    }
}
