<div>
    @if($activeTrips->isNotEmpty())
        <div class="mt-8">
            <x-mary-card title="Naplánované služební cesty" class="bg-base-200 border-0 shadow-none p-0!">
                @foreach($activeTrips as $trip)
                    <div
                        wire:click="startTrip('{{ trim($trip->KlicSluzebniCesty) }}')"
                        class="mb-2 p-4 border border-base-300 bg-white rounded-box cursor-pointer hover:bg-info/5 transition flex items-center gap-4"
                    >
                        <x-mary-icon name="o-truck" class="text-info w-8 h-8 shrink-0" />
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-info">
                                {{ trim($trip->Nazev ?? '') ?: 'Služební cesta' }}
                            </div>
                            <div class="text-sm text-gray-500 flex flex-wrap gap-x-3">
                                <span>{{ $trip->DatumACasOd?->format('d.m.') ?? '' }} – {{ $trip->DatumACasDo?->format('d.m.') ?? '' }}</span>
                                @if(trim($trip->zakaznikSubjekt->Nazev1 ?? ''))
                                    <span>{{ trim($trip->zakaznikSubjekt->Nazev1) }}</span>
                                @endif
                                @if($trip->doklad)
                                    <span class="font-mono">{{ trim($trip->doklad->MPSProjekt ?? '') }} {{ trim($trip->doklad->KlicDokla ?? '') }}</span>
                                @endif
                            </div>
                        </div>
                        <x-mary-icon name="o-play" class="text-info w-6 h-6 shrink-0" />
                    </div>
                @endforeach
            </x-mary-card>
        </div>
    @endif

    <div class="mt-8">
        <x-mary-card title="Dnešní směna" class="bg-transparent border-0 shadow-none p-0!">
            @forelse($today as $record)
                @include('livewire.dashboard.partials.record-row', ['record' => $record, 'isHistory' => false])
            @empty
                <div class="text-center py-6 text-gray-500 bg-white rounded-lg border border-dashed">
                    Dnes zatím nemáte žádné dokončené operace.
                </div>
            @endforelse
        </x-mary-card>

        @if($historical->count() > 0)
            <x-mary-card title="Historie (Posledních 5 dní)" class="bg-transparent border-0 shadow-none p-0! mt-6">
                @foreach($historical as $record)
                    @include('livewire.dashboard.partials.record-row', ['record' => $record, 'isHistory' => true])
                @endforeach
            </x-mary-card>
        @endif
    </div>

    @include('livewire.dashboard.partials.edit-modals')

</div>
