<?php

use App\Models\User;
use App\Models\Terminal;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.welcome')]
class extends Component {

    public string $izo = '';

    public function login(): void
    {
        $this->validate([
            'izo' => ['required', 'string', 'max:10']
        ]);

        $user = User::where('izo', $this->izo)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            $this->addError('izo', 'Neplatný nebo zablokovaný čip.');
            $this->izo = '';
            return;
        }

        Auth::login($user);

        session()->regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

}; ?>

<div
    x-data="{
        time: '',
        date: '',
        shift: '',
        shiftIcon: '',
        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
            this.focusInput();
        },
        tick() {
            const now = new Date();
            this.time = now.toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.date = now.toLocaleDateString('cs-CZ', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            const h = now.getHours();
            if (h >= 6 && h < 14) {
                this.shift = 'Ranní (6:00–14:00)';
                this.shiftIcon = 'sun';
            } else if (h >= 14 && h < 22) {
                this.shift = 'Odpolední (14:00–22:00)';
                this.shiftIcon = 'sunset';
            } else {
                this.shift = 'Noční (22:00–6:00)';
                this.shiftIcon = 'moon';
            }
        },
        focusInput() {
            this.$nextTick(() => {
                if (this.$refs.cipInput) {
                    this.$refs.cipInput.querySelector('input')?.focus();
                }
            });
        },
        handleBlur() {
            setTimeout(() => {
                const active = document.activeElement;
                const isInteractive = active?.tagName === 'BUTTON' ||
                                      active?.tagName === 'A' ||
                                      active?.closest('a') ||
                                      active?.closest('button');
                if (!isInteractive) {
                    this.focusInput();
                }
            }, 10);
        },
        handleMap(e) {
            const map = {
                '+': '1', 'ě': '2', 'š': '3', 'č': '4', 'ř': '5',
                'ž': '6', 'ý': '7', 'á': '8', 'í': '9', 'é': '0'
            };
            let val = e.target.value;
            let mapped = val.split('').map(c => map[c] || c).join('');
            if (val !== mapped) {
                e.target.value = mapped;
                e.target.dispatchEvent(new Event('input'));
                $wire.izo = mapped;
            }
        }
    }"
    @click.document="handleBlur"
    class="w-full max-w-4xl"
>
    {{-- Bento Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Logo + název --}}
        <div class="border border-base-300 bg-base-100 rounded-2xl p-6 flex items-center gap-4">
            <x-application-logo class="w-16 h-16 shrink-0" />
            <div>
                <div class="text-lg font-bold text-base-content">Metal Product</div>
                <div class="text-lg font-bold text-base-content">Servis Praha</div>
            </div>
        </div>

        {{-- Hodiny --}}
        <div class="border border-base-300 bg-base-100 rounded-2xl p-6 flex flex-col items-center justify-center">
            <div class="text-3xl md:text-5xl font-mono font-bold text-primary tracking-wider" x-text="time"></div>
        </div>

        {{-- Datum --}}
        <div class="border border-base-300 bg-base-100 rounded-2xl p-6 flex flex-col items-center justify-center gap-2">
            <x-mary-icon name="o-calendar" class="w-8 h-8 text-base-content/40" />
            <div class="text-center text-base-content/80 text-lg capitalize" x-text="date"></div>
        </div>

        {{-- RFID přihlášení --}}
        <div class="border border-base-300 bg-base-100 rounded-2xl p-6 flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-4">
                <x-mary-icon name="o-key" class="w-6 h-6 text-primary" />
                <span class="text-sm font-medium text-base-content/60">Přiložte čip k přihlášení</span>
            </div>

            <form wire:submit="login">
                <div x-ref="cipInput">
                    <x-mary-input
                        @blur="handleBlur"
                        @input="handleMap"
                        wire:model="izo"
                        icon="o-finger-print"
                        type="password"
                        placeholder="Čekám na čip..."
                        error-field="izo"
                        required
                        autofocus
                        autocomplete="off"
                    />
                </div>

                <button type="submit" class="hidden"></button>
            </form>
        </div>

        {{-- Terminál + Směna --}}
        <div class="md:col-span-2 border border-base-300 bg-base-100 rounded-2xl p-6 flex flex-col justify-center gap-4">
            @if($terminal = Terminal::current())
                <div class="flex items-center gap-3">
                    <x-mary-icon name="o-building-office" class="w-6 h-6 text-primary" />
                    <div>
                        <div class="text-xs uppercase tracking-wider text-base-content/40">Terminál</div>
                        <div class="text-lg font-semibold">{{ $terminal->name }}</div>
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-3">
                <template x-if="shiftIcon === 'sun'">
                    <x-mary-icon name="o-sun" class="w-6 h-6 text-warning" />
                </template>
                <template x-if="shiftIcon === 'sunset'">
                    <x-mary-icon name="o-sun" class="w-6 h-6 text-orange-500" />
                </template>
                <template x-if="shiftIcon === 'moon'">
                    <x-mary-icon name="o-moon" class="w-6 h-6 text-info" />
                </template>
                <div>
                    <div class="text-xs uppercase tracking-wider text-base-content/40">Směna</div>
                    <div class="text-lg font-semibold" x-text="shift"></div>
                </div>
            </div>
        </div>

        {{-- Patička --}}
        <div class="md:col-span-3 border border-base-300 bg-base-100 rounded-2xl p-4 flex items-center justify-center">
            <a href="{{ route('login') }}" class="text-sm text-base-content/50 hover:text-primary transition-colors" wire:navigate>
                Přihlásit se pomocí emailu
            </a>
        </div>
    </div>
</div>
