<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @cannot('simplified layout')
        <x-barcode-body class="min-h-screen font-sans antialiased bg-base-200">
        {{-- NAVBAR mobile only --}}
        <x-mary-nav sticky class="lg:hidden">
            <x-slot:brand>
                <div class="ml-5 pt-5 flex items-center gap-2">
                    <x-application-logo class="w-8 h-8"></x-application-logo>
                    @if($terminal = \App\Models\Terminal::current())
                        <div class="text-sm font-semibold text-gray-500 truncate mt-1">
                            {{ $terminal->name }}
                        </div>
                    @endif
                </div>
            </x-slot:brand>
            <x-slot:actions>
                <label for="main-drawer" class="lg:hidden mr-3">
                    <x-mary-icon name="o-bars-3" class="cursor-pointer" />
                </label>
            </x-slot:actions>
        </x-mary-nav>

        {{-- MAIN --}}
        <x-mary-main full-width>
            {{-- SIDEBAR --}}
            <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit" collapse-text="Skrýt" >

                {{-- BRAND --}}
                <div class="ml-5 pt-2 flex items-center gap-2">
                    <x-application-logo class="w-11 h-11"></x-application-logo>
                    @if($terminal = \App\Models\Terminal::current())
                        <div class="text-sm font-semibold text-gray-500 truncate mt-1">
                            {{ $terminal->name }}
                        </div>
                    @endif
                </div>

                {{-- MENU --}}
                <x-mary-menu activate-by-route class="pt-0">

                    {{-- User --}}
                    @if($user = auth()->user())
                        <x-mary-menu-separator/>

                        <x-mary-list-item :item="$user" value="name" no-separator no-hover class="-mx-2 !-my-2 rounded">
                            <x-slot:avatar>
                                {{-- Inciály použijeme jméno uživatele, pokud není nastaveno, použijeme první písmeno jména a příjmení --}}
                                <x-mary-avatar placeholder="{{ substr($user->name, 0, 1)  }}" class="!w-10"/>
                            </x-slot:avatar>
                            <x-slot:actions>
                                <x-logout-button></x-logout-button>
                            </x-slot:actions>
                        </x-mary-list-item>

                        <x-mary-menu-separator />
                    @endif

                    <x-mary-menu-item icon="o-home" title="Dashboard" link="{{ route('dashboard') }}" />

                    @can('manage zasobovani')
                        <x-mary-menu-item icon="o-truck" title="Zásobování" link="{{ route('zasobovac.index') }}" />
                    @endcan

                    @can('manage production records')
                        <x-mary-menu-item icon="o-clipboard-document-list" title="Vedoucí" link="{{ route('vedouci.index') }}" />
                    @endcan

                    <x-mary-menu-separator/>

                    @cannot('simplified layout')
                        @can('manage users')
                            <x-mary-menu-item icon="o-users" title="Uživatelé" link="{{ route('admin.users') }}" />
                        @endcan
                        @can('manage areas')
                            <x-mary-menu-item icon="o-map-pin" title="Oblasti" link="{{ route('admin.areas') }}" />
                        @endcan
                        @can('manage terminals')
                            <x-mary-menu-item icon="o-device-phone-mobile" title="Terminály" link="{{ route('admin.terminals') }}" />
                        @endcan
                        @can('manage areas')
                            <x-mary-menu-item icon="o-wrench-screwdriver" title="Stroje" link="{{ route('admin.machines') }}" />
                        @endcan
                        @can('manage printers')
                            <x-mary-menu-item icon="o-printer" title="Tiskárny" link="{{ route('printers.index') }}" />
                        @endcan
                    @endcannot

                    <x-mary-menu-item icon="o-user" title="Profil" link="{{ route('profile') }}" />
                </x-mary-menu>
            </x-slot:sidebar>

            {{-- The `$slot` goes here --}}
            <x-slot:content>
                {{ $slot }}
            </x-slot:content>
        </x-mary-main>

        {{-- Toast --}}
        <x-mary-toast />

        <livewire:global-qr-scanner />

        </x-barcode-body>
    @endcannot
    @can('simplified layout')
        <x-barcode-body class="font-sans antialiased bg-base-200 min-h-screen">

        {{-- The navbar with `sticky` and `full-width` --}}
        <x-mary-nav sticky full-width>

            <x-slot:brand>
                {{-- Drawer toggle for "main-drawer" --}}
                <label for="main-drawer" class="lg:hidden mr-3">
                    <x-mary-icon name="o-bars-3" class="cursor-pointer" />
                </label>

                {{-- Brand --}}
                <div class="flex items-center gap-2">
                    <x-application-logo class="w-8 h-8" />
                    @auth
                        <span class="font-semibold text-sm">{{ auth()->user()->name }}</span>
                    @endauth
                </div>
            </x-slot:brand>

            {{-- Right side actions --}}
            <x-slot:actions>
                <x-mary-button label="Dashboard" icon="o-home" link="{{ route('dashboard') }}" class="btn-ghost btn-sm {{ request()->routeIs('dashboard') ? 'btn-active' : '' }}" responsive />
                <x-mary-button label="Profil" icon="o-user" link="{{ route('profile') }}" class="btn-ghost btn-sm {{ request()->routeIs('profile') ? 'btn-active' : '' }}" responsive />
                <x-logout-button />
            </x-slot:actions>
        </x-mary-nav>

        {{-- The main content with `full-width` --}}
        <x-mary-main with-nav full-width>

            {{-- The `$slot` goes here --}}
            <x-slot:content>
                {{ $slot }}
            </x-slot:content>
        </x-mary-main>

        {{--  TOAST area --}}
        <x-mary-toast />

        <livewire:global-qr-scanner />

        </x-barcode-body>
    @endcan

</html>
