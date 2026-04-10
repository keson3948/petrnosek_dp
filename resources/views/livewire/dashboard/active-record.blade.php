<div>
    @if($activeRecord)
        @php
            $isRunning = $activeRecord->status === 0;
            $isPaused = $activeRecord->status === 1;
        @endphp
        <x-mary-card
            class="border {{ $isRunning ? 'border-primary bg-primary/5' : 'border-warning bg-warning/5' }}"
        >
            <x-slot:title>
                <div class="flex items-center gap-3">
                    @if($isRunning)
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                        </span>
                        <span class="text-primary">Probíhající operace</span>
                    @else
                        <x-mary-icon name="o-pause-circle" class="w-5 h-5 text-warning" />
                        <span class="text-warning">Pozastavená operace</span>
                    @endif
                    @if($activeRecord->SluzebniCesta)
                        <x-mary-badge value="Služební cesta" icon="o-truck" class="badge-info badge-sm" />
                    @endif
                </div>
            </x-slot:title>

            {{-- Info grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="flex items-center gap-3 p-3 rounded-lg bg-white border border-base-200">
                    <x-mary-icon name="o-document-text" class="w-6 h-6 text-gray-400 shrink-0" />
                    <div class="min-w-0">
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Výrobní příkaz</div>
                        <div class="font-bold text-lg truncate">
                            @if($activeRecord->ZakVP_SysPrimKlic)
                                <a href="{{ route('vp.show', trim($activeRecord->ZakVP_SysPrimKlic)) }}" class="z-50 relative hover:underline hover:text-primary">{{ trim($activeRecord->doklad->MPSProjekt) }} {{ $klicDokla ?? '—' }}</a>
                            @else
                                {{ $klicDokla ?? '—' }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-lg bg-white border border-base-200">
                    <x-mary-icon name="o-cog-6-tooth" class="w-6 h-6 text-gray-400 shrink-0" />
                    <div class="min-w-0">
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Operace</div>
                        <div class="font-bold truncate">{{ trim($activeRecord->operation?->Nazev1 ?? $activeRecord->operation_id) }}</div>
                    </div>
                </div>

                @if($activeRecord->machine_id)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-white border border-base-200">
                        <x-mary-icon name="o-wrench-screwdriver" class="w-6 h-6 text-gray-400 shrink-0" />
                        <div class="min-w-0">
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Stroj</div>
                            <div class="font-bold truncate">{{ trim($activeRecord->machine?->NazevUplny ?? $activeRecord->machine_id) }}</div>
                        </div>
                    </div>
                @endif

                @if($activeRecord->drawing_number)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-white border border-base-200">
                        <x-mary-icon name="o-pencil-square" class="w-6 h-6 text-gray-400 shrink-0" />
                        <div class="min-w-0">
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Výkres</div>
                            <div class="font-bold truncate">{{ $activeRecord->drawing_number }}</div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Timer --}}
            <div class="mt-4 flex items-center justify-between p-4 rounded-lg {{ $isRunning ? 'bg-primary/10' : 'bg-warning/10' }}">
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Zahájeno</div>
                    <div class="text-lg font-mono font-bold">{{ $activeRecord->started_at->format('H:i') }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">
                        {{ $isRunning ? 'Probíhá' : 'Pozastaveno' }}
                    </div>
                    <div class="text-3xl font-bold font-mono tabular-nums {{ $isRunning ? 'text-primary' : 'text-warning' }}"
                         wire:poll.10s="$refresh">
                        @php
                            $now = now();
                            $started_at = \Carbon\Carbon::parse($activeRecord->started_at);
                            $elapsed = (int) $started_at->diffInMinutes($now);
                            $paused = (int) ($activeRecord->total_paused_min ?? 0);
                            if ($isPaused && $activeRecord->last_paused_at) {
                                $paused += (int) \Carbon\Carbon::parse($activeRecord->last_paused_at)->diffInMinutes($now);
                            }
                            $worked = max(0, $elapsed - $paused);
                            $h = intdiv($worked, 60);
                            $m = $worked % 60;
                        @endphp
                        {{ $h }}<span class="{{ $isRunning ? 'animate-pulse text-primary' : '' }}">:</span>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
            </div>

            <x-slot:actions>
                @if($isRunning)
                    <x-mary-button label="Pozastavit" icon="o-pause" wire:click="pauseOperation" class="btn-warning btn-outline btn-lg" spinner="pauseOperation" />
                @else
                    <x-mary-button label="Obnovit" icon="o-play" wire:click="resumeOperation" class="btn-primary btn-lg" spinner="resumeOperation" />
                @endif
                <x-mary-button label="Ukončit operaci" icon="o-check" wire:click="openCompleteModal" class="btn-success text-white btn-lg" />
            </x-slot:actions>
        </x-mary-card>
    @else
        <x-mary-card class="bg-white border border-base-300">
            <div class="text-center py-4">
                <x-mary-icon name="o-play-circle" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                <p class="text-gray-500">Momentálně nemáte aktivní žádnou výrobní operaci.</p>
            </div>
        </x-mary-card>
    @endif

    {{-- Complete modal --}}
    <x-mary-modal wire:model="showCompleteModal" title="Dokončení operace" separator>
        <x-mary-form wire:submit="completeOperation">
            <x-mary-input label="Množství zpracovaných jednotek *" type="number" wire:model.defer="processed_quantity" min="0" />
            <x-mary-textarea label="Poznámka / Problémy (volitelné)" wire:model.defer="notes" rows="4" />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.showCompleteModal = false" />
                <x-mary-button label="Ukončit operaci a uložit" class="btn-success text-white" type="submit" spinner="completeOperation" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
</div>
