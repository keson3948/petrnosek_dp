<div>
    <x-mary-header title="Výsledek skenování" subtitle="Obsah načteného QR kódu" separator>
        <x-slot:actions>
            <x-mary-button label="Zpět na přehled" icon="o-arrow-left" link="{{ route('dashboard') }}" class="btn-ghost" />
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-mary-card title="Rozeznaná data" class="shadow-sm">
            <div class="bg-base-200 p-6 rounded-lg break-all font-mono text-lg">
                {{ $code }}
            </div>
            
            <x-slot:actions>
                {{-- Zde se dá do budoucna přidat další logika - podle obsahu kódu nabídnout akce typu "Otevřít doklad", apod. --}}
                <x-mary-button label="Kopírovat do schránky" icon="o-clipboard-document" @click="navigator.clipboard.writeText('{{ addslashes($code) }}'); $wire.success('Zkopírováno!')" class="btn-secondary btn-outline" />
            </x-slot:actions>
        </x-mary-card>
        
        <x-mary-card title="Informace" class="shadow-sm">
            Kód byl úspěšně zaznamenán vaší čtečkou napříč systémem a přesměrován sem pro další zpracování.
            <br><br>
            <i>Nyní můžete pípnutím dalšího kódu rovnou přejít na další položku, stránka se díky globálnímu skeneru automaticky přepne.</i>
        </x-mary-card>
    </div>
</div>
