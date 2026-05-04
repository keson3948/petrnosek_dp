<div wire:poll.60s class="bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-4 sm:p-5">
    @if($noWorkplace)
        <div class="flex items-center gap-3 text-warning">
            <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6 shrink-0" />
            <div>
                <div class="font-semibold text-lg">Terminál nemá přiřazené pracoviště</div>
                <div class="text-base text-base-content/60">Kontaktujte správce, aby pracoviště nastavil v admin sekci.</div>
            </div>
        </div>
    @else
        <div class="flex items-center gap-2 mb-4">
            <x-mary-icon name="o-bolt" class="w-4 h-4 text-primary" />
            <div class="uppercase tracking-wider text-base-content/50 font-bold text-sm">Aktuálně se pracuje</div>
            @if($pracovisteName)
                <div class="text-sm font-semibold text-base-content/80 ml-auto">{{ $pracovisteName }}</div>
            @endif
        </div>

        @if($rows->isEmpty())
            <div class="flex items-center gap-3 text-base-content/50 py-6">
                <x-mary-icon name="o-moon" class="w-6 h-6" />
                <span class="text-lg">Nikdo nepracuje.</span>
            </div>
        @else
            <div class="overflow-auto" style="max-height: calc(100dvh - 18rem)">
                <table class="w-full border-collapse">
                    <thead class="sticky top-0 z-10 bg-white/95">
                        <tr class="border-b-2 border-base-200">
                            <th class="text-left py-2 px-3 text-sm font-bold uppercase tracking-wide text-base-content/50 w-40">Operátor</th>
                            <th class="text-left py-2 px-3 text-sm font-bold uppercase tracking-wide text-base-content/50">Operace</th>
                            <th class="text-left py-2 px-3 text-sm font-bold uppercase tracking-wide text-base-content/50">VP</th>
                            <th class="text-left py-2 px-3 text-sm font-bold uppercase tracking-wide text-base-content/50">Podsestava</th>
                            <th class="text-left py-2 px-3 text-sm font-bold uppercase tracking-wide text-base-content/50">Stroj</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr class="border-b border-base-200 {{ $row->is_paused ? 'bg-error/10' : '' }}">
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-2">
                                        @if($row->is_paused)
                                            <x-mary-icon name="o-pause-circle" class="w-5 h-5 text-error shrink-0" />
                                        @endif
                                        <span class="text-xl font-bold {{ $row->is_paused ? 'text-error' : 'text-base-content' }} leading-tight">
                                            {{ $row->operator }}
                                        </span>
                                    </div>
                                    @if($row->is_paused)
                                        <div class="text-xs text-error/70 font-semibold mt-0.5 ml-7">Pozastaveno</div>
                                    @endif
                                </td>

                                <td class="py-3 px-3">
                                    <span class="text-lg font-semibold text-base-content leading-tight">{{ $row->operation }}</span>
                                </td>

                                <td class="py-3 px-3">
                                    @if($row->vp)
                                        <div class="text-lg font-bold font-mono text-base-content leading-tight">{{ $row->vp }}</div>
                                    @else
                                        <span class="text-base-content/30 text-base">—</span>
                                    @endif
                                    @if($row->radek_pozice)
                                        <div class="text-sm text-base-content/60 mt-0.5">Řád. <span class="font-semibold text-base-content/80">{{ $row->radek_pozice }}</span></div>
                                    @endif
                                </td>

                                <td class="py-3 px-3">
                                    @if($row->podsestava)
                                        <div class="text-base font-bold font-mono text-base-content leading-tight">{{ $row->podsestava }}</div>
                                    @endif
                                    @if($row->drawing_number)
                                        <div class="text-sm text-base-content/60 mt-0.5"><span class="font-semibold text-base-content/80">{{ $row->drawing_number }}</span></div>
                                    @elseif(!$row->podsestava)
                                        <span class="text-base-content/30 text-base">—</span>
                                    @endif
                                </td>

                                <td class="py-3 px-3 max-w-[10rem]">
                                    <span class="text-base font-semibold text-base-content/80 leading-tight block truncate" title="{{ $row->machine }}">{{ $row->machine }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
