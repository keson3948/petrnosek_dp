<div wire:poll.60s class="bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-4 sm:p-5">
    @if($noWorkplace)
        <div class="flex items-center gap-3 text-warning">
            <x-mary-icon name="o-exclamation-triangle" class="w-5 h-5 shrink-0" />
            <div>
                <div class="font-semibold">Terminál nemá přiřazené pracoviště</div>
                <div class="text-sm text-base-content/60">Kontaktujte správce, aby pracoviště nastavil v admin sekci.</div>
            </div>
        </div>
    @else
        <div class="flex items-center gap-2 mb-3">
            <x-mary-icon name="o-bolt" class="w-5 h-5 text-primary" />
            <div class="text-sm uppercase tracking-wider text-base-content/50 font-bold">Aktuálně se pracuje</div>
            @if($pracovisteName)
                <div class="text-sm font-semibold text-base-content/80 ml-auto truncate">{{ $pracovisteName }}</div>
            @endif
        </div>

        @if($rows->isEmpty())
            <div class="flex items-center gap-3 text-base-content/50 py-4">
                <x-mary-icon name="o-moon" class="w-5 h-5" />
                <span>Nikdo nepracuje.</span>
            </div>
        @else
            <x-mary-table
                class="table-xs"
                container-class="overflow-auto max-h-96 [&_thead_th]:sticky [&_thead_th]:top-0 [&_thead_th]:bg-base-100 [&_thead_th]:z-10 [&_thead_th]:text-[11px] [&_thead_th]:font-semibold"
                no-hover
                :headers="[
                    ['key' => 'operator', 'label' => 'Operátor'],
                    ['key' => 'machine', 'label' => 'Stroj'],
                    ['key' => 'operation', 'label' => 'Operace'],
                    ['key' => 'vp', 'label' => 'VP'],
                ]"
                :rows="$rows"
            />
        @endif
    @endif
</div>
