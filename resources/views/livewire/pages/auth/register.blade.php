<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>

    <x-mary-form wire:submit="register">
        <x-mary-input label="Jméno" icon="o-user" wire:model="name" error-field="name" type="text" name="name" required autofocus autocomplete="name"/>
        <x-mary-input label="Email" icon="o-envelope" wire:model="email" error-field="email" type="email" name="email" required autofocus autocomplete="username"/>
        <x-mary-input label="Heslo" icon="o-key" wire:model="password" type="password" name="password" error-field="password" required autocomplete="new-password"/>
        <x-mary-input label="Potvrdit heslo" icon="o-key" wire:model="password_confirmation" type="password" name="password_confirmation" error-field="password_confirmation" required autocomplete="new-password"/>

        <x-slot:actions>
            {{-- Obalovací DIV, který zajistí roztažení a zarovnání --}}
            <div class="w-full flex items-center justify-between">

                <x-mary-button
                    label="Už máte účet?"
                    link="{{ route('login') }}"
                    class="btn-link text-gray-600 underline hover:no-underline p-0 h-auto min-h-0"
                />

                {{-- 2. Tlačítko (bude vpravo) --}}
                <x-mary-button
                    label="ZAREGISTROVAT SE"
                    icon="o-paper-airplane"
                    class="btn-primary"
                    type="submit"
                    spinner="save" />

            </div>
        </x-slot:actions>
    </x-mary-form>
</div>
