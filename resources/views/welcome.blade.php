<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">
<!-- Header -->
<header class="w-full lg:max-w-4xl max-w-[335px] mx-auto text-sm mb-6 px-6 pt-10 not-has-[nav]:hidden">
    @if (Route::has('login'))
        <nav class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br !text-green-600 from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-semibold !text-green-600 dark:text-green-400">
                        BalanceBot
                    </span>
                </div>
            </a>

            <div class="flex items-center gap-4">
                <!-- Theme Switcher -->
                <div x-data="{
    init() {
        // Sync with Flux appearance on mount
        this.$watch('$flux.appearance', value => {
            this.setTheme(value);
        });

        // Initialize theme from localStorage or Flux
        const savedTheme = localStorage.getItem('theme') || $flux.appearance || 'system';
        this.setTheme(savedTheme);
    },
    setTheme(value) {
        $flux.appearance = value;
        localStorage.setItem('theme', value);
        if (value === 'dark' || (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}" class="flex items-center bg-neutral-100 dark:bg-neutral-800 rounded-lg p-1">
                    <!-- Light Mode -->
                    <button
                        @click="setTheme('light')"
                        :class="$flux.appearance === 'light' ? 'bg-white dark:bg-neutral-700 shadow-sm' : ''"
                        class="p-1.5 rounded transition-all duration-200 hover:bg-white/50 dark:hover:bg-neutral-700/50"
                        title="Light mode">
                        <svg class="w-4 h-4 text-[#1b1b18] dark:text-[#EDEDEC]" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>

                    <!-- Dark Mode -->
                    <button
                        @click="setTheme('dark')"
                        :class="$flux.appearance === 'dark' ? 'bg-white dark:bg-neutral-700 shadow-sm' : ''"
                        class="p-1.5 rounded transition-all duration-200 hover:bg-white/50 dark:hover:bg-neutral-700/50"
                        title="Dark mode">
                        <svg class="w-4 h-4 text-[#1b1b18] dark:text-[#EDEDEC]" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>

                    <!-- System Mode -->
                    <button
                        @click="setTheme('system')"
                        :class="$flux.appearance === 'system' ? 'bg-white dark:bg-neutral-700 shadow-sm' : ''"
                        class="p-1.5 rounded transition-all duration-200 hover:bg-white/50 dark:hover:bg-neutral-700/50"
                        title="System preference">
                        <svg class="w-4 h-4 text-[#1b1b18] dark:text-[#EDEDEC]" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>

                @auth
                    <a
                        href="{{ url('/dashboard') }}"
                        class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                    >
                        Dashboard
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                    >
                        Log in
                    </a>
                @endauth
            </div>
        </nav>
    @endif
</header>

<!-- Hero Section -->
<div class="flex items-center justify-center w-full min-h-screen p-6 lg:p-8">
    <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row gap-0">
        <!-- Left Content -->
        <div
            class="text-[13px] leading-[20px] flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-es-lg rounded-ee-lg lg:rounded-ss-lg lg:rounded-ee-none">
            <div
                class="inline-flex items-center space-x-2 bg-green-100 dark:bg-green-900/30 px-3 py-1 rounded-full mb-4">
                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                <span class="text-xs font-medium !text-green-600 dark:text-green-400">Powered by Telegram</span>
            </div>

            <h1 class="mb-2 font-semibold text-lg">Check Your DESCO Balance Instantly</h1>
            <p class="mb-4 text-[#706f6c] dark:text-[#A1A09A]">
                Monitor your electricity balance, recharge history, and consumption in real-time through our intelligent
                Telegram bot. Simple, fast, and always available.
            </p>

            <ul class="flex flex-col mb-6 space-y-3">
                <li class="flex items-start gap-3">
                    <span
                        class="flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 w-6 h-6 shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 !text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span>
                        <strong>Real-time Balance:</strong> Check your current electricity balance instantly with a simple command
                    </span>
                </li>
                <li class="flex items-start gap-3">
                    <span
                        class="flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 w-6 h-6 shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 !text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span>
                        <strong>Low Balance Alerts:</strong> Get automatic warnings when your balance is running low
                    </span>
                </li>
                <li class="flex items-start gap-3">
                    <span
                        class="flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 w-6 h-6 shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 !text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span>
                        <strong>24/7 Available:</strong> Access your information anytime, anywhere, instantly
                    </span>
                </li>
            </ul>

            <ul class="flex gap-3 text-sm leading-normal flex-wrap">
                <li>
                    <a href="https://t.me/midescobot" target="_blank"
                       class="inline-flex items-center dark:bg-[#eeeeec] dark:border-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white dark:hover:border-white hover:bg-green-600 hover:border-green-600 px-5 py-1.5 bg-green-500 rounded-sm border border-green-500 text-white text-sm leading-normal transition-all">
                        <svg class="w-4 h-4 me-2" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                        </svg>
                        Start on Telegram
                    </a>
                </li>
                <li>
                    <a href="{{ route('home') }}"
                       class="inline-block dark:text-[#EDEDEC] text-[#1b1b18] border border-[#19140035] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b] px-5 py-1.5 rounded-sm text-sm leading-normal transition-all"
                       wire:navigate>
                        Documentation
                    </a>
                </li>
            </ul>

            <div class="flex gap-6 mt-6 pt-6 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
                <div>
                    <div class="text-lg font-semibold !text-green-600 dark:text-green-400">24/7</div>
                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Available</div>
                </div>
                <div>
                    <div class="text-lg font-semibold !text-green-600 dark:text-green-400">Instant</div>
                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Updates</div>
                </div>
                <div>
                    <div class="text-lg font-semibold !text-green-600 dark:text-green-400">Free</div>
                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Forever</div>
                </div>
            </div>
        </div>

        <!-- Right Content - Bot Preview -->
        <div
            class="bg-[#e8f5e9] dark:bg-[#0d1f12] relative lg:-ms-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-e-lg! aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden p-6 lg:p-8 flex items-center justify-center">
            <div class="w-full max-w-sm">
                <!-- Bot Chat Preview -->
                <div class="bg-white dark:bg-[#161615] rounded-2xl shadow-2xl overflow-hidden">
                    <!-- Chat Header -->
                    <div class="flex items-center space-x-3 px-4 py-3 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] text-sm">BalanceBot</div>
                            <div class="text-xs !text-green-600 dark:text-green-400 flex items-center">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full me-1.5"></span>
                                Online
                            </div>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div class="p-4 space-y-3 min-h-[200px]">
                        <!-- User Message -->
                        <div class="flex justify-end">
                            <div
                                class="bg-green-500 text-white px-3 py-2 rounded-2xl rounded-br-sm text-xs max-w-[80%]">
                                /home_balance
                            </div>
                        </div>

                        <!-- Bot Response -->
                        <div class="flex justify-start">
                            <div
                                class="bg-neutral-100 dark:bg-neutral-800 text-[#1b1b18] dark:text-[#EDEDEC] px-3 py-2 rounded-2xl rounded-bl-sm text-xs max-w-[85%]">
                                <div class="font-semibold mb-1.5">🏠 Home Balance</div>
                                <div class="space-y-0.5 text-[11px]">
                                    <div><strong>Account:</strong> 12345678</div>
                                    <div><strong>Name:</strong> John Doe</div>
                                    <div><strong>Balance:</strong> <span
                                            class="!text-green-600 dark:text-green-400 font-semibold">৳ 250.50</span>
                                    </div>
                                    <div><strong>Meter:</strong> MTR123456</div>
                                    <div class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] mt-1.5">Just now</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="flex items-center space-x-2 px-4 py-3 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <div
                            class="flex-1 bg-neutral-100 dark:bg-neutral-800 text-[#706f6c] dark:text-[#A1A09A] px-3 py-2 rounded-full text-xs">
                            Type a command...
                        </div>
                        <button class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Available Commands -->
                <div class="mt-4 bg-white/50 dark:bg-[#161615]/50 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Quick Commands:</div>
                    <div class="flex flex-wrap gap-1.5">
                        <span
                            class="inline-block px-2 py-1 bg-green-100 dark:bg-green-900/30 !text-green-600 dark:text-green-400 rounded text-[10px] font-medium">/start</span>
                        <span
                            class="inline-block px-2 py-1 bg-green-100 dark:bg-green-900/30 !text-green-600 dark:text-green-400 rounded text-[10px] font-medium">/help</span>
                        <span
                            class="inline-block px-2 py-1 bg-green-100 dark:bg-green-900/30 !text-green-600 dark:text-green-400 rounded text-[10px] font-medium">/home_balance</span>
                    </div>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div
                class="absolute -top-10 -right-10 w-32 h-32 bg-green-200 dark:bg-green-900/20 rounded-full blur-3xl opacity-50"></div>
            <div
                class="absolute -bottom-10 -left-10 w-40 h-40 bg-emerald-200 dark:bg-emerald-900/20 rounded-full blur-3xl opacity-50"></div>
        </div>
    </main>
</div>

@if (Route::has('login'))
    <div class="h-14.5 hidden lg:block"></div>
@endif

@fluxScripts
</body>
</html>
