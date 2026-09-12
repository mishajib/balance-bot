<?php

use App\Services\DescoService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.desco.base_urls', [
        'unified' => 'https://prepaid.desco.org.bd/api/unified/customer',
        'tkdes' => 'https://prepaid.desco.org.bd/api/tkdes/customer',
    ]);
});

test('getBalance falls back to tkdes when unified returns null or error for account 25043133', function () {
    Http::fake([
        'https://prepaid.desco.org.bd/api/unified/customer/getBalance*' => Http::response([
            'code' => 200,
            'desc' => 'OK',
            'data' => null,
        ], 200),
        'https://prepaid.desco.org.bd/api/unified/customer/getCustomerInfo*' => Http::response([
            'code' => 16002,
            'desc' => 'Meter or Customer Number Does not Exists',
            'data' => null,
        ], 200),
        'https://prepaid.desco.org.bd/api/tkdes/customer/getBalance*' => Http::response([
            'code' => 200,
            'desc' => 'OK',
            'data' => [
                'accountNo' => '25043133',
                'meterNo' => '066110026076',
                'balance' => 142.04,
                'currentMonthConsumption' => 567.05,
                'readingTime' => '2026-09-13 00:20:50',
            ],
        ], 200),
        'https://prepaid.desco.org.bd/api/tkdes/customer/getCustomerInfo*' => Http::response([
            'code' => 200,
            'desc' => 'OK',
            'data' => [
                'accountNo' => '25043133',
                'contactNo' => '01713368847',
                'customerName' => 'MAMUNUL BARI SOHEL',
                'meterNo' => '066110026076',
                'sanctionLoad' => 3,
            ],
        ], 200),
    ]);

    $service = new DescoService;
    $data = $service->getBalance('25043133');

    expect($data)->not->toBeNull()
        ->and($data['accountNo'])->toBe('25043133')
        ->and($data['customerName'])->toBe('MAMUNUL BARI SOHEL')
        ->and($data['balance'])->toBe(142.04)
        ->and($data['meterNo'])->toBe('066110026076');
});

test('getBalance uses unified directly if unified succeeds', function () {
    Http::fake([
        'https://prepaid.desco.org.bd/api/unified/customer/getBalance*' => Http::response([
            'code' => 200,
            'desc' => 'OK',
            'data' => [
                'accountNo' => '11112222',
                'balance' => 500.00,
            ],
        ], 200),
        'https://prepaid.desco.org.bd/api/unified/customer/getCustomerInfo*' => Http::response([
            'code' => 200,
            'desc' => 'OK',
            'data' => [
                'accountNo' => '11112222',
                'customerName' => 'Unified User',
            ],
        ], 200),
    ]);

    $service = new DescoService;
    $data = $service->getBalance('11112222');

    expect($data)->not->toBeNull()
        ->and($data['accountNo'])->toBe('11112222')
        ->and($data['customerName'])->toBe('Unified User')
        ->and((float) $data['balance'])->toEqual(500.0);

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/tkdes/'));
});

test('getCustomerInfo falls back across base URLs', function () {
    Http::fake([
        'https://prepaid.desco.org.bd/api/unified/customer/getCustomerInfo*' => Http::response([
            'code' => 16002,
            'desc' => 'Meter or Customer Number Does not Exists',
            'data' => null,
        ], 200),
        'https://prepaid.desco.org.bd/api/tkdes/customer/getCustomerInfo*' => Http::response([
            'code' => 200,
            'desc' => 'OK',
            'data' => [
                'accountNo' => '25043133',
                'customerName' => 'MAMUNUL BARI SOHEL',
            ],
        ], 200),
    ]);

    $service = new DescoService;
    $data = $service->getCustomerInfo('25043133');

    expect($data)->not->toBeNull()
        ->and($data['customerName'])->toBe('MAMUNUL BARI SOHEL');
});

test('getRechargeHistory falls back across base URLs', function () {
    Http::fake([
        'https://prepaid.desco.org.bd/api/unified/customer/getRechargeHistory*' => Http::response([
            'code' => 200,
            'desc' => 'OK',
            'data' => [],
        ], 200),
        'https://prepaid.desco.org.bd/api/tkdes/customer/getRechargeHistory*' => Http::response([
            'code' => 200,
            'desc' => 'OK',
            'data' => [
                [
                    'accountNo' => '25043133',
                    'totalAmount' => 1500,
                    'tokenNo' => '24045438780029912336',
                ],
            ],
        ], 200),
    ]);

    $service = new DescoService;
    $data = $service->getRechargeHistory('25043133', ['dateFrom' => '2026-08-01', 'dateTo' => '2026-09-13']);

    expect($data)->toHaveCount(1)
        ->and($data[0]['accountNo'])->toBe('25043133')
        ->and($data[0]['totalAmount'])->toBe(1500);
});

test('getCustomerMonthlyConsumption falls back across base URLs', function () {
    Http::fake([
        'https://prepaid.desco.org.bd/api/unified/customer/getCustomerMonthlyConsumption*' => Http::response([
            'code' => 901,
            'desc' => 'The Start Month cant be null!',
            'data' => null,
        ], 200),
        'https://prepaid.desco.org.bd/api/tkdes/customer/getCustomerMonthlyConsumption*' => Http::response([
            'code' => 200,
            'desc' => 'OK',
            'data' => [
                'accountNo' => '25043133',
                'monthlyData' => [],
            ],
        ], 200),
    ]);

    $service = new DescoService;
    $data = $service->getCustomerMonthlyConsumption('25043133', ['startMonth' => '2026-01']);

    expect($data)->not->toBeNull()
        ->and($data['accountNo'])->toBe('25043133');
});
