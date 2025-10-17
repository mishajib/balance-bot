<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'telegram/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('telegram:send-balance-info', [
            '--accountNo' => config('services.desco.home_account_no'),
            '--type' => 'home',
        ])->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        $schedule->command('telegram:send-balance-info', [
            '--accountNo' => config('services.desco.godown_account_no'),
            '--type' => 'godown',
        ])->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduler.log'));
    })->create();
