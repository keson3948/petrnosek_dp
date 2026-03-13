<div>
    @if($activeRecord)
        <x-mary-card title="Aktuální výrobní operace" class="{{ $activeRecord->status === 'in_progress' ? 'border-primary' : 'border-warning' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div><strong>Zakázka:</strong> {{ $activeRecord->order_number }}</div>
                    <div><strong>Operace:</strong> {{ $activeRecord->operation_id }}</div>
                    @if($activeRecord->drawing_number)
                        <div><strong>Výkres:</strong> {{ $activeRecord->drawing_number }}</div>
                    @endif
                    @if($activeRecord->machine_id)
                        <div><strong>Stroj:</strong> {{ $activeRecord->machine_id }}</div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-lg">
                        <strong>Stav:</strong>
                        @if($activeRecord->status === 'in_progress')
                            <span class="text-primary font-bold flex items-center justify-end gap-2">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                                </span>
                                Probíhá
                            </span>
                        @else
                            <span class="text-warning font-bold">Pozastaveno</span>
                        @endif
                    </div>
                    <div class="text-sm mt-1 text-gray-500">Zahájeno: {{ $activeRecord->started_at->format('H:i') }}</div>
                </div>
            </div>

            <x-slot:actions>
                @if($activeRecord->status === 'in_progress')
                    <x-mary-button label="Pozastavit" icon="o-pause" wire:click="pauseOperation" class="btn-warning btn-outline" spinner="pauseOperation" />
                @else
                    <x-mary-button label="Obnovit" icon="o-play" wire:click="resumeOperation" class="btn-primary" spinner="resumeOperation" />
                @endif
                <x-mary-button label="Ukončit operaci" icon="o-check" wire:click="openCompleteModal" class="btn-success text-white" />
            </x-slot:actions>
        </x-mary-card>
    @else
        <x-mary-card title="Výrobní operace" class="bg-white">
            <p class="text-gray-500 mb-4">Momentálně nemáte aktivní žádnou výrobní operaci.</p>
            <x-mary-button label="Začít novou operaci" icon="o-play" wire:click="openStartModal" class="btn-primary" />
        </x-mary-card>
    @endif

    <!-- HISTORY SECTION -->
    <div class="mt-8">
        <x-mary-card title="Dnešní směna" class="bg-transparent border-0 shadow-none !p-0">
            @forelse($today as $record)
                <x-mary-collapse class="mb-2 border border-base-200 bg-white">
                    <x-slot:heading>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-4 w-1/4">
                                <x-mary-avatar placeholder="??" class="bg-success text-white !w-10 !h-10" />
                                <div>
                                    <span class="font-bold" x-data x-tooltip="Výrobní příkaz: {{ $record->order_number }}">{{ $record->order_number }}</span>
                                    <div class="text-xs text-gray-500">Výkres: {{ $record->drawing_number ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="flex-1 px-4">
                                <div class="flex justify-between text-xs mb-1">
                                    <span>Hotovo: {{ $record->processed_quantity }} ks</span>
                                    <span>Zahájeno: {{ $record->started_at->format('H:i') }}</span>
                                </div>
                                <x-mary-progress class="progress-primary h-2" value="100" max="100" />
                            </div>
                            <div class="w-1/6 flex justify-end items-center gap-2">
                                <x-mary-icon name="o-check-circle" class="text-success w-8 h-8" tooltip="Dokončeno" />
                            </div>
                        </div>
                    </x-slot:heading>
                    <x-slot:content>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 border-t bg-base-50">
                            <div><strong>Zakázka:</strong> {{ $record->order_number }}</div>
                            <div><strong>Operace:</strong> {{ $record->operation_id }}</div>
                            <div><strong>Stroj:</strong> {{ $record->machine_id ?? 'Nezadáno' }}</div>
                            <div><strong>Operátor:</strong> {{ auth()->user()->name }}</div>
                            
                            @if($record->notes)
                                <div class="col-span-4 mt-2">
                                    <strong>Poznámka:</strong> <span class="text-gray-600">{{ $record->notes }}</span>
                                </div>
                            @endif

                            <div class="col-span-4 mt-4 flex justify-between items-end">
                                <div>
                                   <strong>Záznamy o činnosti:</strong>
                                   <ul class="list-disc pl-5 text-sm text-gray-600">
                                       <li>{{ $record->started_at->format('d.m.Y H:i') }} - {{ $record->ended_at?->format('H:i') ?? 'Neznámé' }} 
                                            (Pauza: {{ gmdate("H:i:s", $record->total_paused_seconds) }})
                                       </li>
                                   </ul>
                                </div>
                                <x-mary-button label="Upravit záznam" icon="o-pencil" wire:click="openEditModal({{ $record->id }})" class="btn-sm btn-outline btn-primary" />
                            </div>
                        </div>
                    </x-slot:content>
                </x-mary-collapse>
            @empty
                <div class="text-center py-6 text-gray-500 bg-white rounded-lg border border-dashed">
                    Dnes zatím nemáte žádné dokončené operace.
                </div>
            @endforelse
        </x-mary-card>

        @if($historical->count() > 0)
            <x-mary-card title="Historie (Posledních 5 dní)" class="bg-transparent border-0 shadow-none !p-0 mt-6">
                @foreach($historical as $record)
                    <x-mary-collapse class="bg-base-200 opacity-80 mb-2 border border-base-300">
                        <x-slot:heading>
                            <div class="flex items-center justify-between w-full grayscale-[50%]">
                                <div class="flex items-center gap-4 w-1/4">
                                    <x-mary-avatar placeholder="??" class="bg-neutral text-white !w-10 !h-10" />
                                    <div>
                                        <span class="font-bold">{{ $record->order_number }}</span>
                                        <div class="text-xs text-gray-500">Výkres: {{ $record->drawing_number ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="flex-1 px-4">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span>Hotovo: {{ $record->processed_quantity }} ks</span>
                                        <span>Dne: {{ $record->started_at->format('d.m.Y H:i') }}</span>
                                    </div>
                                    <x-mary-progress class="progress-neutral h-1" value="100" max="100" />
                                </div>
                                <div class="w-1/6 flex justify-end items-center gap-2">
                                    <x-mary-badge value="Vyřešeno" class="badge-neutral badge-sm" />
                                </div>
                            </div>
                        </x-slot:heading>
                        <x-slot:content>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 border-t border-base-300">
                                <div><strong>Zakázka:</strong> {{ $record->order_number }}</div>
                                <div><strong>Operace:</strong> {{ $record->operation_id }}</div>
                                <div><strong>Stroj:</strong> {{ $record->machine_id ?? 'Nezadáno' }}</div>
                                <div><strong>Operátor:</strong> {{ auth()->user()->name }}</div>
                                
                                @if($record->notes)
                                    <div class="col-span-4 mt-2">
                                        <strong>Poznámka:</strong> <span class="text-gray-600">{{ $record->notes }}</span>
                                    </div>
                                @endif

                                <div class="col-span-4 mt-4">
                                   <strong>Záznamy o činnosti:</strong>
                                   <ul class="list-disc pl-5 text-sm text-gray-600">
                                       <li>{{ $record->started_at->format('d.m.Y H:i') }} - {{ $record->ended_at?->format('H:i') ?? 'Neznámé' }} 
                                            (Pauza: {{ gmdate("H:i:s", $record->total_paused_seconds) }})
                                       </li>
                                   </ul>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-mary-collapse>
                @endforeach
            </x-mary-card>
        @endif
    </div>

    <!-- START MODAL -->
    <x-mary-modal wire:model="showStartModal" title="Nová operace" separator>
        <x-mary-form wire:submit="startOperation">
            <x-mary-input label="Číslo zakázky *" wire:model="order_number" placeholder="Např. 2026/001" />
            <x-mary-input label="Výrobní operace *" wire:model="operation_id" placeholder="Např. Řezání" />
            <x-mary-input label="Číslo výkresu (volitelné)" wire:model="drawing_number" />
            <x-mary-input label="Stroj (volitelné)" wire:model="machine_id" />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.showStartModal = false" />
                <x-mary-button label="Zahájit" class="btn-primary" type="submit" spinner="startOperation" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    <!-- COMPLETE MODAL -->
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

    <!-- EDIT MODAL -->
    <x-mary-modal wire:model="showEditModal" title="Úprava záznamu" separator>
        <x-mary-form wire:submit="updateRecord">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-mary-input label="Číslo zakázky *" wire:model="edit_order_number" placeholder="Např. 2026/001" />
                <x-mary-input label="Výrobní operace *" wire:model="edit_operation_id" placeholder="Např. Řezání" />
                <x-mary-input label="Číslo výkresu (volitelné)" wire:model="edit_drawing_number" />
                <x-mary-input label="Stroj (volitelné)" wire:model="edit_machine_id" />
                <x-mary-input label="Množství zpracované *" type="number" wire:model="edit_processed_quantity" min="0" />
                <x-mary-input label="Zahájení operace *" type="datetime-local" wire:model="edit_started_at" />
                <x-mary-input label="Ukončení operace" type="datetime-local" wire:model="edit_ended_at" />
            </div>
            <x-mary-textarea label="Poznámka / Problémy (volitelné)" wire:model="edit_notes" rows="4" class="mt-4" />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.showEditModal = false" />
                <x-mary-button label="Uložit změny" class="btn-primary" type="submit" spinner="updateRecord" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
</div>
