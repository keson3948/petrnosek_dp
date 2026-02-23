<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - 403 Přístup odepřen</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200 flex items-center justify-center">
    <div class="text-center px-4">
        <x-mary-icon name="o-shield-exclamation" class="w-32 h-32 mx-auto text-error opacity-80" />
        
        <h1 class="text-9xl font-bold text-base-content/20 mt-4">403</h1>
        
        <p class="text-3xl font-semibold mt-6 text-base-content">
            Přístup odepřen
        </p>
        
        <p class="mt-4 text-lg text-base-content/70 max-w-md mx-auto">
            Omlouváme se, ale k zobrazení této sekce nemáte dostatečná oprávnění.
        </p>
        
        <div class="mt-10">
            <x-mary-button 
                label="Zpět na Dashboard" 
                icon="o-arrow-left" 
                link="{{ route('dashboard') }}" 
                class="btn-primary" 
            />
        </div>
    </div>
</body>
</html>
