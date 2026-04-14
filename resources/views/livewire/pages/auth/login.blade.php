<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <x-mary-form wire:submit="login">
        <x-mary-input label="Email" icon="o-envelope" wire:model="form.email" error-field="form.email" type="email" name="email" required autofocus autocomplete="username"/>
        <x-mary-input label="Heslo" icon="o-key" wire:model="form.password" type="password" name="password" error-field="form.password" required autocomplete="current-password"/>

        <x-slot:actions>
            <div class="w-full flex items-center justify-between">

                @if(\App\Models\Terminal::isTerminal())
                    <x-mary-button
                        label="Přihlásit se pomocí čipu"
                        link="{{ route('welcome') }}"
                        class="btn-link text-gray-600 underline hover:no-underline p-0 h-auto min-h-0"
                    />
                @else
                    <span></span>
                @endif

                <x-mary-button
                    label="PŘIHLÁSIT SE"
                    icon="o-paper-airplane"
                    class="btn-primary"
                    type="submit"
                    spinner="save" />

            </div>
        </x-slot:actions>
    </x-mary-form>

    <!-- Reset password-->

        <div class="mt-4 text-center">
            <x-mary-button
                label="Zapomněli jste heslo?"
                link="{{ route('password.request') }}"
                class="btn-link text-gray-600 underline hover:no-underline p-0 h-auto min-h-0"
            />
        </div>


</div>
