<x-app-layout>
    <x-mary-header title="Stav dokladů" separator></x-mary-header>

    @livewire('doklad.index', ['staDoklady' => $staDoklady])
</x-app-layout>
