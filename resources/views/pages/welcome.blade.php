<?php

use App\Models\User;
use App\Models\Terminal;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.welcome')]
class extends Component {

    public string $izo = '';

    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirectRoute('dashboard');
            return;
        }

        if (! Terminal::isTerminal()) {
            $this->redirectRoute('login');
        }
    }

    public function login(): void
    {
        $izo = trim($this->izo);
        $this->izo = '';

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['izo' => $izo],
            ['izo' => ['required', 'string', 'max:10']]
        );

        if ($validator->fails()) {
            $this->addError('izo', 'Neplatný čip.');

            return;
        }

        $user = User::where('izo', $izo)
            ->where('is_active', true)
            ->first();

        if (! $user) {
            $this->addError('izo', 'Neplatný nebo zablokovaný čip.');

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
            this.$watch('$wire.izo', (val) => {
                if (val === '' && this.$refs.cipInput) {
                    this.$refs.cipInput.value = '';
                    this.focusInput();
                }
            });
        },
        tick() {
            const now = new Date();
            this.time = now.toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.date = now.toLocaleDateString('cs-CZ', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        },
        focusInput() {
            this.$nextTick(() => {
                this.$refs.cipInput?.focus();
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
    class="w-full"
>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    if (status === 419) {
                        preventDefault();
                        window.location.reload();
                    }
                });
            });
        });
    </script>
    <div class="grid grid-cols-12 gap-2 md:gap-4">

        @error('izo')
        <div class="col-span-12 bg-white/80 border-2 border-error/30 rounded-xl p-2 flex items-center gap-3">
            <x-mary-icon name="o-x-circle" class="w-4 h-4 text-error shrink-0" />
            <div class="text-error font-semibold text-sm">{{ $message }}</div>
        </div>
        @enderror

        <div class="col-span-12 md:col-span-3 bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-2 sm:p-3 flex items-center gap-4">
            <x-application-logo class="w-10 h-10 sm:w-16 sm:h-16 shrink-0" />
            <div>
                <div class="text-lg sm:text-lg font-bold text-base-content leading-tight">Metal Produkt</div>
                <div class="text-lg sm:text-lg font-bold text-base-content leading-tight">Servis Praha</div>
            </div>
        </div>

        <div class="col-span-12 sm:col-span-7 md:col-span-6 bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-2 sm:p-3 flex flex-col items-center justify-center">
            <div class="text-xl sm:text-2xl md:text-4xl font-mono font-bold text-primary tracking-wider" x-text="time"></div>
            <div class="flex items-center justify-center gap-1">
                <div class="text-center text-base-content/70 text-sm capitalize leading-tight" x-text="date"></div>
            </div>
        </div>

        <div class="col-span-12 sm:col-span-5 md:col-span-3 bg-white/80 backdrop-blur border border-base-200 rounded-2xl p-2 sm:p-3 flex flex-col items-center justify-center gap-1">
            @if(Terminal::isTerminal())
                @php $terminal = \App\Models\Terminal::current(); @endphp
                @if($terminal?->pracoviste)
                    <x-mary-icon name="o-map-pin" class="w-5 h-5 text-primary/40" />
                    <div class="text-center font-bold text-base-content text-lg text-base leading-tight">{{ trim($terminal->pracoviste->NazevUplny ?? '') }}</div>
                @endif
            @endif
        </div>

        @if(Terminal::isTerminal())
            <div class="col-span-12">
                <livewire:terminal.workplace-overview />
            </div>
        @endif

    </div>

    <form wire:submit="login">
        <input
            x-ref="cipInput"
            @blur="handleBlur"
            @input="handleMap"
            wire:model="izo"
            type="password"
            autofocus
            autocomplete="off"
            class="sr-only"
        />
        <button type="submit" class="hidden"></button>
    </form>

    <x-mary-button href="{{ route('login') }}"
                   icon="o-envelope"
       class="fixed bottom-0 right-0 text-xs rounded-none p-3"
       wire:navigate>
    </x-mary-button>
</div>
