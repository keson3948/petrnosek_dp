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
    <body class="min-h-screen font-sans antialiased bg-base-200"
        x-data="{
            barcode: '',
            lastTime: 0,
            handleKeydown(e) {
                const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
                if (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select') {
                    return;
                }

                const currentTime = new Date().getTime();

                if (currentTime - this.lastTime > 50) {
                    this.barcode = '';
                }

                this.lastTime = currentTime;

                if (e.key === 'Enter' && this.barcode.length > 0) {
                    Livewire.dispatch('qr-scanned', { code: this.barcode });
                    this.barcode = '';
                    return;
                }

                if (e.key.length === 1 && !e.ctrlKey && !e.metaKey) {
                    this.barcode += e.key;
                }
            }
        }"
        @keydown.window="handleKeydown"
    >
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

                        <x-mary-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                            <x-slot:avatar>
                                <x-mary-avatar placeholder="US" class="!w-10"/>
                            </x-slot:avatar>
                            <x-slot:actions>
                                <x-logout-button></x-logout-button>
                            </x-slot:actions>
                        </x-mary-list-item>

                        <x-mary-menu-separator />
                    @endif

                    <x-mary-menu-item icon="o-home" title="Dashboard" link="{{ route('dashboard') }}" />
                    <x-mary-menu-item icon="o-archive-box" title="Položky" link="{{ route('polozka.index') }}" />
                    <x-mary-menu-item icon="o-user" title="Subjekty" link="{{ route('subjekt.index') }}" />
                    <x-mary-menu-item icon="o-rocket-launch" title="Prostředky" link="{{ route('prostredky.index') }}" />
                    <x-mary-menu-item icon="o-hashtag" title="Stav Dokladů" link="{{ route('stadokl.index') }}" />
                    <x-mary-menu-item icon="o-hashtag" title="Stav Položek" link="{{ route('stapo.index') }}" />

                    <x-mary-menu-separator/>
                    @can('manage users')
                        <x-mary-menu-item icon="o-users" title="Uživatelé" link="{{ route('admin.users') }}" />
                    @endcan
                    @can('manage areas')
                        <x-mary-menu-item icon="o-map-pin" title="Oblasti" link="{{ route('admin.areas') }}" />
                    @endcan
                    @can('manage terminals')
                        <x-mary-menu-item icon="o-device-phone-mobile" title="Terminály" link="{{ route('admin.terminals') }}" />
                    @endcan
                    @can('manage printers')
                        <x-mary-menu-item icon="o-printer" title="Tiskárny" link="{{ route('printers.index') }}" />
                    @endcan

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

    </body>
</html>
