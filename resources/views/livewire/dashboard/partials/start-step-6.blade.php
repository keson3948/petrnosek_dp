<div class="flex flex-col h-full">
    <div class="flex-1 flex flex-col overflow-hidden">
        <label class="label shrink-0"><span class="label-text font-bold text-base text-gray-700 pb-2">Operace</span></label>
        @if($machine_id && $this->startMachineOperations->count() > 0)
            <div class="space-y-2 overflow-y-auto pr-2">
                @foreach($this->startMachineOperations as $op)
                    <button type="button"
                        wire:click="startSelectOperation('{{ $op->operation_key }}')"
                        class="w-full min-h-[3.5rem] text-left border-2 rounded-lg p-3 transition-colors flex items-center {{ $operation_id === $op->operation_key ? 'border-primary bg-primary/10 text-primary' : 'border-base-200 hover:border-primary/30 text-gray-700' }}">
                        <x-mary-icon name="o-cog-6-tooth" class="w-6 h-6 mr-3 {{ $operation_id === $op->operation_key ? 'text-primary' : 'text-gray-400' }}" />
                        <span class="text-base font-semibold">{{ $op->operation_name ?: $op->operation_key }}</span>
                    </button>
                @endforeach
            </div>
        @else
            <x-mary-input wire:model="operation_id" placeholder="Zadejte operaci" class="input-lg" />
        @endif
    </div>
</div>
