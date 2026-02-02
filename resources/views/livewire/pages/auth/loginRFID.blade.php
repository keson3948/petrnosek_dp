<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    // No logic needed here, form submits to controller
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <x-input-error :messages="$errors->get('izo')" class="mb-4" />

    <div class="mb-4 p-4 bg-gray-50 border rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Přihlášení čipem') }}</h3>
        <form action="{{ route('rfid.login') }}" method="POST">
            @csrf
            <div>
                <x-input-label for="izo" :value="__('Kód čipu (IZO)')" />
                <x-text-input id="izo" class="block mt-1 w-full" type="password" name="izo" required autofocus />
            </div>
            <div class="mt-2">
                <x-primary-button class="w-full justify-center">
                    {{ __('Přihlásit čipem') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 underline" wire:navigate>
            {{ __('Zpět na přihlášení emailem') }}
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('izo');
            if (!input) return;

            // Focus input immediately
            input.focus();

            // Submission on Enter is standard behavior for forms, no extra script needed unless we want to debounce or prevent default.
            // Leaving it standard for now as it's cleaner.

            // Optional: Keep the CZ mapping if the user had it before, but for now I'll stick to clean implementation.
            // If the scanner acts as a keyboard and types localized characters, the mapping script IS helpful.
            
            const mapCzToNum = {
                '+': '1', 'ě': '2', 'š': '3', 'č': '4', 'ř': '5',
                'ž': '6', 'ý': '7', 'á': '8', 'í': '9', 'é': '0'
            };

            input.addEventListener('input', () => {
                let original = input.value;
                let mapped = original.split('').map(ch => mapCzToNum[ch] ?? ch).join('');
                if (original !== mapped) {
                    input.value = mapped;
                }
            });
        });
    </script>
</div>


