<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="3;url={{ url('/') }}">
    <title>{{ config('app.name', 'Laravel') }} - Stránka vypršela</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200 flex items-center justify-center">
    <div class="text-center px-4">
        <x-mary-icon name="o-clock" class="w-32 h-32 mx-auto text-warning opacity-80" />

        <h1 class="text-9xl font-bold text-base-content/20 mt-4">419</h1>

        <p class="text-3xl font-semibold mt-6 text-base-content">
            Stránka vypršela
        </p>

        <p class="mt-4 text-lg text-base-content/70 max-w-md mx-auto">
            Stránka byla otevřená příliš dlouho. Za chvíli vás přesměrujeme.
        </p>

        <div class="mt-10">
            <x-mary-button
                label="Pokračovat"
                icon="o-arrow-path"
                link="{{ url('/') }}"
                class="btn-primary"
            />
        </div>
    </div>
</body>
</html>
