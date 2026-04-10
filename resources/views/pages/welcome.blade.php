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
        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
            this.focusInput();
        },
        tick() {
            const now = new Date();
            this.time = now.toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.date = now.toLocaleDateString('cs-CZ', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
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
    class="w-full max-w-5xl"
>
    {{-- Bento Grid --}}
    <div class="grid grid-cols-12 gap-3 md:gap-4">

        {{-- Logo + název — wide --}}
        <div class="col-span-12 md:col-span-5 bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-6 flex items-center gap-5">
            <x-application-logo class="w-16 h-16 shrink-0" />
            <div>
                <div class="text-xl font-bold text-base-content leading-tight">Metal Product</div>
                <div class="text-xl font-bold text-base-content leading-tight">Servis Praha</div>
            </div>
        </div>

        {{-- Hodiny — velké --}}
        <div class="col-span-7 md:col-span-4 bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-6 flex flex-col items-center justify-center">
            <div class="text-4xl md:text-5xl font-mono font-bold text-primary tracking-wider" x-text="time"></div>
        </div>

        {{-- Datum --}}
        <div class="col-span-5 md:col-span-3 bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-6 flex flex-col items-center justify-center gap-1">
            <x-mary-icon name="o-calendar" class="w-7 h-7 text-primary/40" />
            <div class="text-center text-base-content/70 text-sm capitalize leading-tight" x-text="date"></div>
        </div>

        {{-- RFID přihlášení — hlavní karta --}}
        <div class="col-span-12 md:col-span-6 bg-white/90 backdrop-blur border-2 border-primary/20 rounded-2xl p-8 flex flex-col justify-center">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                    <x-mary-icon name="o-finger-print" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <div class="font-semibold text-base-content">Přihlášení</div>
                    <div class="text-xs text-base-content/50">Přiložte čip ke čtečce</div>
                </div>
            </div>

            <form wire:submit="login">
                <div x-ref="cipInput">
                    <x-mary-input
                        @blur="handleBlur"
                        @input="handleMap"
                        wire:model="izo"
                        icon="o-key"
                        type="password"
                        placeholder="Čekám na čip..."
                        error-field="izo"
                        required
                        autofocus
                        autocomplete="off"
                        class="input-lg"
                    />
                </div>

                <button type="submit" class="hidden"></button>
            </form>
        </div>

        {{-- Terminál --}}
        @if($terminal = Terminal::current())
            <div class="col-span-12 md:col-span-3 bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-6 flex flex-col justify-center">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                        <x-mary-icon name="o-building-office" class="w-5 h-5 text-primary" />
                    </div>
                    <div>
                        <div class="text-[10px] uppercase tracking-wider text-base-content/40 font-bold">Terminál</div>
                        <div class="text-lg font-semibold">{{ $terminal->name }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Status --}}
        <div class="{{ ($terminal = Terminal::current()) ? 'md:col-span-3' : 'md:col-span-6' }} col-span-12 bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-6 flex items-center justify-center gap-3">
            <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
            <span class="text-sm text-base-content/50">Systém online</span>
        </div>

        {{-- Patička --}}
        <div class="col-span-12 bg-white/60 backdrop-blur border border-base-200 rounded-2xl p-3 flex items-center justify-center">
            <a href="{{ route('login') }}" class="text-sm text-base-content/40 hover:text-primary transition-colors" wire:navigate>
                Přihlásit se pomocí emailu
            </a>
        </div>
    </div>
</div>
