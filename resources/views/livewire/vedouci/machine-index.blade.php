<div>
    <x-mary-header title="Přehled strojů" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Hledat stroj..." clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button label="Operátoři" icon="o-users" link="{{ route('vedouci.index') }}" class="btn-outline" />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <div class="overflow-x-auto">
            <table class="table table-sm w-full">
                <thead>
                    <tr>
                        <th>Stroj</th>
                        <th>Stav</th>
                        <th>Operátor</th>
                        <th>VP</th>
                        <th>Operace</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $machine)
                        <tr class="hover cursor-pointer {{ $machine->is_active ? ($machine->status_label === 'Pauza' ? 'bg-warning/10' : 'bg-success/10') : '' }}"
                            onclick="window.location='{{ route('vedouci.machine', $machine->key) }}'">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full shrink-0 {{ $machine->is_active ? ($machine->status_label === 'Pauza' ? 'bg-warning' : 'bg-success animate-pulse') : 'bg-base-300' }}"></div>
                                    <span class="font-semibold">{{ $machine->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($machine->is_active)
                                    <x-mary-badge :value="$machine->status_label"
                                        class="{{ $machine->status_label === 'Pauza' ? 'badge-warning' : 'badge-success' }} badge-sm" />
                                @else
                                    <span class="text-gray-400 text-sm">Volný</span>
                                @endif
                            </td>
                            <td>
                                @if($machine->active_user)
                                    <span class="font-semibold">{{ $machine->active_user }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td>
                                @if($machine->active_vp)
                                    <span class="font-mono text-sm">{{ $machine->active_vp }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td>
                                @if($machine->active_operation)
                                    <span class="text-sm">{{ $machine->active_operation }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-mary-card>
</div>
