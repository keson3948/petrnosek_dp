<div>
    @if($activeRecord)
        <x-mary-card title="Aktuální výrobní operace" class="shadow-sm border-l-4 {{ $activeRecord->status === 'in_progress' ? 'border-primary' : 'border-warning' }}">
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
        <x-mary-card title="Výrobní operace" class="shadow-sm border-l-4 border-gray-300 bg-gray-50/50">
            <p class="text-gray-500 mb-4">Momentálně nemáte aktivní žádnou výrobní operaci.</p>
            <x-mary-button label="Začít novou operaci" icon="o-play" wire:click="openStartModal" class="btn-primary" />
        </x-mary-card>
    @endif

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
</div>
