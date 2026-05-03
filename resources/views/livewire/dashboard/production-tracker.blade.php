<div wire:poll.15s>
    @if($activeLunch)
        <x-mary-card class="border-2 border-warning bg-warning/10">
            <div class="flex flex-col items-center justify-center py-10 sm:py-16 text-center gap-4">
                <x-mary-icon name="o-cake" class="w-20 h-20 sm:w-24 sm:h-24 text-warning" />
                <div class="text-2xl sm:text-3xl font-bold text-warning">Probíhá oběd</div>
                <div class="text-base sm:text-lg text-base-content/70">
                    Končí v <span class="font-mono font-bold tabular-nums text-warning">{{ $lunchEndsAt->format('H:i') }}</span>
                </div>
                @php
                    $remainingMin = max(0, (int) ceil(now()->diffInSeconds($lunchEndsAt, false) / 60));
                @endphp
                <div class="text-sm text-base-content/50">
                    Zbývá ~{{ $remainingMin }} min
                </div>
            </div>
        </x-mary-card>
    @else
        <x-mary-header title="Dashboard" separator>
            <x-slot:actions class="!justify-end">
                @if($hasLunchGroup)
                    @php
                        $lunchTooltip = $hasLunchToday
                            ? 'Dnes jste už měl oběd'
                            : ($canStartLunchNow
                                ? '30 minut, jednou denně'
                                : 'Oběd máte v ' . $lunchTime->format('H:i'));
                    @endphp
                    <x-mary-button
                        label="Oběd"
                        icon="o-cake"
                        wire:click="confirmStartLunch"
                        class="btn-warning md:btn-lg"
                        :disabled="$hasLunchToday || ! $canStartLunchNow"
                        tooltip-bottom="{{ $lunchTooltip }}"
                        responsive
                    />
                @endif
                <x-mary-button
                    label="Začít novou operaci"
                    icon="o-play"
                    wire:click="openStartDrawer"
                    class="btn-primary md:btn-lg"
                    :disabled="$hasActiveRecord"
                    responsive
                />
            </x-slot:actions>
        </x-mary-header>

        <livewire:dashboard.active-record />

        <livewire:dashboard.history />

        <livewire:dashboard.start-drawer
            :qr-start="request()->query('start')"
            :qr-d="request()->query('d')"
        />
    @endif

    <x-mary-modal wire:model="showLunchConfirm" title="Zahájit oběd?" separator>
        <div class="space-y-3">
            <p>Opravdu chcete zahájit <span class="font-bold">30minutový oběd</span>?</p>
            <ul class="text-sm text-base-content/70 list-disc list-inside space-y-1">
                <li>Pokud máte aktivní operaci, bude automaticky pozastavena.</li>
            </ul>
        </div>
        <x-slot:actions>
            <x-mary-button label="Zrušit" wire:click="cancelLunch" />
            <x-mary-button label="Zahájit oběd" icon="o-cake" class="btn-warning" wire:click="startLunch" spinner="startLunch" />
        </x-slot:actions>
    </x-mary-modal>
</div>
