<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Route::get('/telegram/set-webhook', [TelegramBotController::class, 'setWebhook']);
    Route::get('/telegram/webhook-info', [TelegramBotController::class, 'getWebhookInfo']);
    Route::get('/telegram/remove-webhook', [TelegramBotController::class, 'removeWebhook']);
});

require __DIR__.'/auth.php';

// Telegram webhook
Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);
