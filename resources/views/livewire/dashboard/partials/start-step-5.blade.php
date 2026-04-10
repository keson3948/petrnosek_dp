<div class="flex flex-col h-full">
    <div class="flex-1 flex flex-col overflow-hidden">
        <label class="label shrink-0"><span class="label-text font-bold text-base text-gray-700 pb-2">Stroj</span></label>
        @if($this->userMachines->count() > 0)
            <div class="space-y-2 overflow-y-auto pr-2">
                @foreach($this->userMachines as $machine)
                    <button type="button"
                        wire:click="startSelectMachine('{{ $machine->machine_key }}')"
                        class="w-full min-h-[3.5rem] text-left border-2 rounded-lg p-3 transition-colors flex items-center {{ $machine_id === $machine->machine_key ? 'border-secondary bg-secondary/10 text-secondary' : 'border-base-200 hover:border-secondary/30 text-gray-700' }}">
                        <x-mary-icon name="o-wrench-screwdriver" class="w-6 h-6 mr-3 {{ $machine_id === $machine->machine_key ? 'text-secondary' : 'text-gray-400' }}" />
                        <span class="text-base font-semibold">{{ $machine->machine_name ?: $machine->machine_key }}</span>
                    </button>
                @endforeach
            </div>
        @else
            <x-mary-input wire:model.live.debounce.300ms="machine_id" placeholder="Zadejte stroj" class="input-lg" />
        @endif
    </div>
</div>
