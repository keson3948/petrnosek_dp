<x-app-layout>
    <x-mary-header title="Položky" separator>
        <x-slot:actions>
            <form action="{{ route('polozka.store') }}" method="POST">
                @csrf
                <x-mary-button icon="o-plus" type="submit" class="btn-primary">
                    Vytvořit testovací položku
                </x-mary-button>
            </form>
            <x-mary-button icon="o-trash" link="{{ route('polozka.delete-form') }}" class="btn-error">
                Smazat položku
            </x-mary-button>
        </x-slot:actions>
    </x-mary-header>

    @if(session('success'))
        <x-mary-alert icon="o-check-circle" class="alert-success" dismissible>
            {{ session('success') }}
        </x-mary-alert>
    @endif

    <livewire:polozka.index :polozky="$polozky"/>
</x-app-layout>
