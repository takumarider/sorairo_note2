<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @livewireScripts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot ?? '' }}
            </main>
        </div>

        <div id="reservation-loading-overlay"
             class="hidden fixed inset-0 z-[100] items-center justify-center bg-slate-950/50 px-6"
             aria-live="polite"
             aria-hidden="true">
            <div class="w-full max-w-sm rounded-3xl bg-white/95 p-6 text-center shadow-2xl ring-1 ring-slate-200 backdrop-blur-sm sm:p-8">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                    <svg class="h-8 w-8 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" class="stroke-sky-200" stroke-width="3"></circle>
                        <path d="M21 12a9 9 0 00-9-9" class="stroke-sky-600" stroke-linecap="round" stroke-width="3"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-bold text-slate-900">処理中です</h2>
                <p id="reservation-loading-message" class="mt-2 text-sm leading-6 text-slate-600">
                    予約情報を確認しています。画面が切り替わるまでそのままお待ちください。
                </p>
            </div>
        </div>
    </body>
</html>
