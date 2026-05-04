<div>
    <x-mary-header title="Úprava uživatele: {{ $user->name }}" separator progress-indicator>
        <x-slot:actions>
            <x-mary-button label="Odeslat obnovu hesla" icon="o-envelope" @click="$wire.confirmModal = true" />
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
        <x-mary-card title="Osobní údaje a přístupy">
            <x-mary-form wire:submit="save">

                <x-mary-input label="Jméno" wire:model="name" />
                <x-mary-input label="E-mail" wire:model="email" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-mary-input label="Čip (IZO)" wire:model="izo" placeholder="Nelze načíst / Prázdné" />
                    <x-mary-input label="Klíč Subjektu" wire:model="klic_subjektu" placeholder="Např. 12345" />
                </div>

                @if($subjekt)
                    <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                        <x-mary-icon name="o-user-group" class="w-5 h-5 text-base-content/60 shrink-0" />
                        <div>
                            <div class="text-xs text-base-content/60">Skupina subjektu (Economy)</div>
                            <div class="font-semibold">
                                @if($subjekt->skupinaSubjektu)
                                    {{ $subjekt->skupinaSubjektu->NazevUplny ?? $subjekt->skupinaSubjektu->Zkratka ?? '—' }}
                                    <span class="text-xs text-base-content/50 ml-1">({{ $subjekt->SkupinSubjektu }})</span>
                                @elseif($subjekt->SkupinSubjektu)
                                    <span class="text-base-content/60">{{ $subjekt->SkupinSubjektu }}</span>
                                @else
                                    <span class="text-base-content/40">—</span>
                                @endif
                            </div>
                        </div>
                        @if($subjekt->isMistr())
                            <x-mary-badge value="Mistr" class="badge-warning ml-auto" />
                        @endif
                    </div>
                @endif

                {{-- Pracovní zařazení (read-only z Economy) --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="p-3 bg-base-200 rounded-lg">
                        <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                            <x-mary-icon name="o-users" class="w-4 h-4" />
                            <span>Skupina zaměstnanců</span>
                        </div>
                        <div class="font-semibold">
                            {{ $user->vztah?->skupinaZamestnancu ? trim($user->vztah->skupinaZamestnancu->Nazev ?? '') : '—' }}
                        </div>
                    </div>
                    <div class="p-3 bg-base-200 rounded-lg">
                        <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                            <x-icons.food class="w-4 h-4" />
                            <span>Čas obědu</span>
                        </div>
                        <div class="font-semibold font-mono">
                            {{ $user->vztah?->skupinaZamestnancu?->lunchCarbon()?->format('H:i') ?? '—' }}
                        </div>
                    </div>
                    <div class="p-3 bg-base-200 rounded-lg">
                        <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                            <x-mary-icon name="o-user" class="w-4 h-4" />
                            <span>Vedoucí</span>
                        </div>
                        <div class="font-semibold">
                            {{ trim(($user->vztah?->vedouciSubjekt?->Jmeno ?? '') . ' ' . ($user->vztah?->vedouciSubjekt?->Prijmeni ?? '')) ?: '—' }}
                        </div>
                    </div>
                </div>

                @if($subjekt?->canHaveColorAndNumber())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-mary-colorpicker
                            label="Barva mistra"
                            wire:model="color"
                            hint="Barva pro vizuální rozlišení v aplikaci"
                        />
                        <x-mary-input
                            label="Číslo mistra"
                            wire:model="cislo_mistra"
                            type="number"
                            min="1"
                            max="9999"
                            hint="Např. Mistr Bořil = 3"
                        />
                    </div>
                @endif

                <x-mary-choices
                    label="Role uživatele"
                    wire:model="selectedRoles"
                    :options="$allRoles"
                    option-label="name"
                    option-value="id"
                    no-result-text="Nenalezeno"
                    placeholder="Vyberte role..."
                />

                <x-slot:actions>
                    <x-mary-button label="Uložit změny" class="btn-primary" type="submit" spinner="save" />
                </x-slot:actions>
            </x-mary-form>
        </x-mary-card>
    </div>

    {{-- Přiřazené stroje --}}
    @if($user->klic_subjektu)
        <x-mary-card title="Přiřazené stroje" class="mt-8">
            <x-slot:menu>
                <x-mary-button icon="o-plus" class="btn-primary btn-sm" wire:click="createMachine" />
            </x-slot:menu>

            @if($machines->isEmpty())
                <div class="text-gray-500 text-sm py-4">Žádné stroje nejsou přiřazeny.</div>
            @else
                <div
                    x-data
                    x-init="Sortable.create($el, {
                        handle: '.sortable-handle',
                        animation: 150,
                        onEnd() {
                            const ids = [...$el.children].map(c => parseInt(c.dataset.sortableId));
                            $wire.reorderMachines(ids);
                        }
                    })"
                    class="space-y-2"
                >
                    @foreach($machines as $machine)
                        <div data-sortable-id="{{ $machine->ID }}"
                             class="flex items-center gap-3 p-3 bg-base-100 border border-base-300 rounded-lg">
                            <div class="sortable-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600">
                                <x-mary-icon name="o-bars-3" class="w-5 h-5" />
                            </div>
                            <span class="font-mono text-sm w-24 shrink-0">{{ $machine->prostredek_kod }}</span>
                            <span class="font-semibold flex-1">{{ $machine->prostredek_nazev }}</span>
                            <x-mary-button
                                icon="o-trash"
                                wire:click="removeMachine({{ $machine->ID }})"
                                wire:confirm="Opravdu odebrat tento stroj?"
                                class="btn-ghost btn-sm text-red-500"
                            />
                        </div>
                    @endforeach
                </div>
            @endif
        </x-mary-card>
    @else
        <x-mary-card title="Přiřazené stroje" class="mt-8">
            <div class="text-gray-500 text-sm py-4">
                Pro správu strojů je nutné vyplnit <strong>Klíč Subjektu</strong> u tohoto uživatele.
            </div>
        </x-mary-card>
    @endif

    {{-- Modal: přidat stroj --}}
    <x-mary-modal wire:model="machineModal" title="Přidat stroj" separator with-close-button>
        <x-mary-form wire:submit="saveMachine">
            <x-mary-select
                label="Prostředek (stroj)"
                icon="o-wrench-screwdriver"
                :options="$prostredkyOptions"
                wire:model="machineKey"
                placeholder="Vyberte prostředek..."
            />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.machineModal = false" />
                <x-mary-button label="Přiřadit" class="btn-primary" type="submit" spinner="saveMachine" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    <x-mary-modal wire:model="confirmModal" class="backdrop-blur">
        Opravdu chcete odeslat odkaz pro obnovení hesla na e-mail {{ $user->email }}?

        <x-slot:actions>
            <x-mary-button label="Zrušit" @click="$wire.confirmModal = false" />
            <x-mary-button label="Odeslat" class="btn-primary" wire:click="sendResetLink({{ $user->id }})" />
        </x-slot:actions>
    </x-mary-modal>
</div>
