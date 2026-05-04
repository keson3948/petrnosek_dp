<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MPS') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .grid-bg {
                background-size: 60px 60px;
                background-image:
                    linear-gradient(to right, rgba(0, 44, 87, 0.06) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(0, 44, 87, 0.06) 1px, transparent 1px);
            }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-y-auto grid-bg bg-gray-50">



        {{-- Robot background --}}
        <div class="fixed inset-0 pointer-events-none select-none" aria-hidden="true">
            <img src="/images/bcg_mps.jpg" class="w-full h-full object-cover" alt="">
        </div>

        {{-- Overlay --}}
        <div class="fixed inset-0 bg-white/10 pointer-events-none" aria-hidden="true"></div>
        <div class="relative z-10 w-full">
            {{ $slot }}
        </div>

        <x-mary-toast />
    </body>
</html>
