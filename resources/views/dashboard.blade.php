<x-app-layout>
    <x-mary-header title="Dashboard" separator />

    <div class="mb-6">
        <livewire:dashboard.production-tracker />
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            {{ __("You're logged in!") }}
        </div>
    </div>
</x-app-layout>
