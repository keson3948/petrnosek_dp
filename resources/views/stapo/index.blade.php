<x-app-layout>
    <x-mary-header title="Stav položek" separator></x-mary-header>

    @livewire('polozka.stav', ['polozky' => $polozky])

</x-app-layout>
