<?php

use App\Services\DescoService;
use Telegram\Bot\Laravel\Facades\Telegram;

beforeEach(function () {
    $this->fakeTelegram = new class
    {
        public array $messages = [];

        public function sendMessage(array $params)
        {
            $this->messages[] = $params;

            return true;
        }
    };

    Telegram::swap($this->fakeTelegram);
});

test('telegram:send-balance-info uses home account from config when accountNo is not provided', function () {
    config()->set('services.desco.home_account_no', '25043133');
    config()->set('telegram.bots.mybot.chat_id', '123456');

    $mockService = Mockery::mock(DescoService::class);
    $mockService->shouldReceive('getBalance')
        ->once()
        ->with('25043133')
        ->andReturn([
            'accountNo' => '25043133',
            'customerName' => 'MAMUNUL BARI SOHEL',
            'contactNo' => '01713368847',
            'meterNo' => '066110026076',
            'sanctionLoad' => 3,
            'balance' => 142.04,
            'readingTime' => '2026-09-13 00:20:50',
        ]);
    app()->instance(DescoService::class, $mockService);

    $this->artisan('telegram:send-balance-info', ['--type' => 'home'])
        ->expectsOutputToContain('home balance info sent successfully')
        ->assertExitCode(0);

    expect($this->fakeTelegram->messages)->toHaveCount(1)
        ->and($this->fakeTelegram->messages[0]['chat_id'])->toBe('123456')
        ->and($this->fakeTelegram->messages[0]['text'])->toContain('Home Balance')
        ->and($this->fakeTelegram->messages[0]['text'])->toContain('25043133')
        ->and($this->fakeTelegram->messages[0]['text'])->toContain('MAMUNUL BARI SOHEL');
});

test('telegram:send-balance-info uses godown account from config when type is godown', function () {
    config()->set('services.desco.godown_account_no', '99887766');
    config()->set('telegram.bots.mybot.chat_id', '123456');

    $mockService = Mockery::mock(DescoService::class);
    $mockService->shouldReceive('getBalance')
        ->once()
        ->with('99887766')
        ->andReturn([
            'accountNo' => '99887766',
            'customerName' => 'Godown Owner',
            'contactNo' => '01811111111',
            'meterNo' => '99999999',
            'sanctionLoad' => 10,
            'balance' => 350.00,
            'readingTime' => '2026-09-13 00:20:50',
        ]);
    app()->instance(DescoService::class, $mockService);

    $this->artisan('telegram:send-balance-info', ['--type' => 'godown'])
        ->expectsOutputToContain('godown balance info sent successfully')
        ->assertExitCode(0);

    expect($this->fakeTelegram->messages)->toHaveCount(1)
        ->and($this->fakeTelegram->messages[0]['chat_id'])->toBe('123456')
        ->and($this->fakeTelegram->messages[0]['text'])->toContain('Godown Balance')
        ->and($this->fakeTelegram->messages[0]['text'])->toContain('99887766');
});

test('telegram:send-balance-info respects explicit accountNo argument if provided', function () {
    config()->set('services.desco.home_account_no', '25043133');
    config()->set('telegram.bots.mybot.chat_id', '123456');

    $mockService = Mockery::mock(DescoService::class);
    $mockService->shouldReceive('getBalance')
        ->once()
        ->with('55555555')
        ->andReturn([
            'accountNo' => '55555555',
            'customerName' => 'Custom User',
            'contactNo' => '01700000000',
            'meterNo' => '123123123',
            'sanctionLoad' => 5,
            'balance' => 200.00,
            'readingTime' => '2026-09-13 00:20:50',
        ]);
    app()->instance(DescoService::class, $mockService);

    $this->artisan('telegram:send-balance-info', [
        '--accountNo' => '55555555',
        '--type' => 'home',
    ])->assertExitCode(0);

    expect($this->fakeTelegram->messages)->toHaveCount(1)
        ->and($this->fakeTelegram->messages[0]['text'])->toContain('55555555');
});

test('telegram:send-balance-info fails if no account is configured and none passed', function () {
    config()->set('services.desco.home_account_no', '');

    $this->artisan('telegram:send-balance-info', ['--type' => 'home'])
        ->expectsOutputToContain("Account number is not configured for type 'home'")
        ->assertExitCode(0);

    expect($this->fakeTelegram->messages)->toBeEmpty();
});
