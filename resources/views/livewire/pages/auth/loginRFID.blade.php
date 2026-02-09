<?php

use App\Models\Subjekt;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')]
class extends Component {

    public string $izo = '';

    public function login(): void
    {
        $this->validate([
            'izo' => ['required', 'string', 'max:10']
        ]);

        $izo = $this->izo;

        $subjekt = Subjekt::where('Izo', $izo)->first();

        if (!$subjekt) {
            $this->addError('izo', 'Neplatný čip.');
            $this->izo = '';
            return;
        }

        $user = User::where('izo', $izo)->first();

        if (!$user) {
            $user = User::create([
                'name' => trim($subjekt->Prijmeni . ' ' . $subjekt->Jmeno),
                'email' => $subjekt->emailKontakt->Hodnota ?? ($izo . '@rfid.local'),
                'password' => Hash::make(Str::random(16)),
                'izo' => $izo,
                'klic_subjektu' => $subjekt->KlicSubjektu,
            ]);
        } else {
            $newName = trim($subjekt->Prijmeni . ' ' . $subjekt->Jmeno);
            if ($user->klic_subjektu !== $subjekt->KlicSubjektu || $user->name !== $newName) {
                $user->update([
                    'klic_subjektu' => $subjekt->KlicSubjektu,
                    'name' => $newName
                ]);
            }
        }

        Auth::login($user);

        session()->regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

}; ?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')"/>

    <div x-data="{
        init() {
            this.focusInput();
        },
        focusInput() {
            if (this.$refs.cipInput) {
                this.$refs.cipInput.focus();
            }
        },
        handleBlur() {
            setTimeout(() => {
                const active = document.activeElement;

                const isInteractive = active.tagName === 'BUTTON' ||
                                      active.tagName === 'A' ||
                                      active.closest('a') ||
                                      active.closest('button');

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
                this.$wire.izo = mapped;
            }
        }
    }"
         @click.document="handleBlur"
    >
        <x-mary-form wire:submit="login">

            <x-mary-input
                x-ref="cipInput"
                @blur="handleBlur"
                @input="handleMap"
                wire:model="izo"
                label="Přiložte svůj čip k přihlášení"
                icon="o-key"
                type="password"
                name="izo"
                error-field="izo"
                required
                autofocus
                autocomplete="off"
            />

            <x-slot:actions>
                <div class="w-full flex items-center justify-center">
                    <x-mary-button
                        label="Přihlásit se pomocí emailu"
                        link="{{ route('login') }}"
                        class="btn-link text-gray-600 underline hover:no-underline p-0 h-auto min-h-0"
                    />

                    <x-mary-button
                        label="PŘIHLÁSIT SE"
                        icon="o-paper-airplane"
                        class="btn-primary hidden"
                        type="submit"
                        spinner="save"/>
                </div>
            </x-slot:actions>
        </x-mary-form>
    </div>
</div>
