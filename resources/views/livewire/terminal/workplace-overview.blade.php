<div wire:poll.60s class="bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-4 sm:p-3">
    @if($noWorkplace)
        <div class="flex items-center gap-3 text-warning">
            <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6 shrink-0" />
            <div>
                <div class="font-semibold text-lg">Terminál nemá přiřazené pracoviště</div>
                <div class="text-base text-base-content/60">Kontaktujte správce, aby pracoviště nastavil v admin sekci.</div>
            </div>
        </div>
    @else
        @if($rows->isEmpty())
            <div class="flex items-center gap-3 text-base-content/50 py-6">
                <x-mary-icon name="o-moon" class="w-6 h-6" />
                <span class="text-lg">Nikdo nepracuje.</span>
            </div>
        @else
            <div class="overflow-auto" style="max-height: calc(100dvh - 18rem)">
                <table class="w-full border-collapse">
                    <tbody>
                        @foreach($rows as $row)
                            <tr class="{{ $row->is_paused ? 'bg-error/30' : '' }}">
                                <td class="py-2 px-3 w-10">
                                    @if($row->mistr_cislo)
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0"
                                             style="background-color: {{ $row->mistr_color }}">
                                            {{ $row->mistr_cislo }}
                                        </div>
                                    @else
                                        <span class="text-base-content/30">—</span>
                                    @endif
                                </td>

                                <td class="py-2 px-3">
                                    <div class="flex items-center gap-2">
                                        @if($row->is_paused)
                                            <x-mary-icon name="o-pause-circle" class="w-5 h-5 text-error shrink-0" />
                                        @endif
                                        <span class="text-xl font-bold {{ $row->is_paused ? 'text-error' : 'text-base-content' }} leading-tight">
                                            {{ $row->operator }}
                                        </span>
                                    </div>
                                </td>

                                <td class="py-2 px-3">
                                    @if($row->vp)
                                        <span class="text-lg font-bold font-mono text-base-content leading-tight">{{ $row->vp }}</span>
                                    @else
                                        <span class="text-base-content/30 text-base">—</span>
                                    @endif
                                </td>

                                <td class="py-2 px-3">
                                    @if($row->podsestava)
                                        <span class="text-base font-bold font-mono text-base-content leading-tight">{{ $row->podsestava }}</span>
                                    @endif
                                    @if($row->drawing_number)
                                        <span class="text-sm text-base-content/60 mt-0.5"><span class="font-semibold text-base-content/80">({{ $row->drawing_number }})</span></span>
                                    @elseif(!$row->podsestava)
                                        <span class="text-base-content/30 text-base">—</span>
                                    @endif
                                </td>

                                <td class="py-2 px-3 max-w-[10rem]">
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
