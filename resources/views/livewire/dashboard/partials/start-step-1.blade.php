<div class="flex flex-col h-full">
    {{-- Zobrazení vybraného dokladu (při návratu zpět) --}}
    @if($selectedDokladKey && !$podSearch)
        <div class="mb-4 p-3 border-2 border-primary bg-primary/10 rounded-lg flex items-center justify-between shrink-0">
            <div>
                <div class="text-xs text-gray-500">Vybraný VP</div>
                <div class="font-bold font-mono text-lg text-primary">{{ $selectedDokladKey }}</div>
            </div>
            <div class="flex items-center gap-2">
                <x-mary-icon name="o-check-circle" class="w-6 h-6 text-primary" />
                <button type="button" wire:click="clearDoklad" class="btn btn-ghost btn-xs">
                    <x-mary-icon name="o-x-mark" class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    <x-mary-input
        label="Hledat výrobní příkaz (VP) nebo projekt"
        wire:model.live.debounce.300ms="podSearch"
        placeholder="Zadejte číslo VP nebo MPS..."
        icon="o-magnifying-glass"
        class="input-lg font-mono"
        clearable
    />

    @if($this->podSearchResults->count() > 0)
        <div class="mt-4 space-y-2">
            @foreach($this->podSearchResults as $doklad)
                @php $isSelected = $selectedDokladKey === trim($doklad->KlicDokla); @endphp
                <button type="button"
                    wire:click="selectDoklad('{{ addslashes(trim($doklad->KlicDokla)) }}')"
                    class="w-full min-h-[3.5rem] p-3 text-left border-2 rounded-lg transition-colors flex items-center justify-between {{ $isSelected ? 'border-primary bg-primary/10' : 'border-base-200 hover:border-primary/30' }}">
                    <div>
                        <div class="font-bold font-mono text-lg {{ $isSelected ? 'text-primary' : '' }}">{{ trim($doklad->KlicDokla) }}</div>
                        @if(trim($doklad->MPSProjekt ?? ''))
                            <div class="text-sm text-gray-500">{{ trim($doklad->MPSProjekt) }}</div>
                        @endif
                    </div>
                    @if($isSelected)
                        <x-mary-icon name="o-check-circle" class="w-6 h-6 text-primary" />
                    @else
                        <x-mary-icon name="o-arrow-right" class="w-5 h-5 text-gray-400" />
                    @endif
                </button>
            @endforeach
        </div>
    @elseif(strlen(trim($podSearch)) >= 2 && $this->podSearchResults->count() === 0)
        <div class="text-center border-2 border-dashed border-base-200 rounded-lg text-gray-500 py-8 mt-4 flex-1 flex items-center justify-center">
            Žádný výrobní příkaz nenalezen.
        </div>
    @elseif(!$selectedDokladKey)
        <div class="text-center text-gray-400 py-10 text-sm flex-1 flex items-center justify-center">
            Zadejte alespoň 2 znaky pro vyhledávání
        </div>
    @endif
</div>
