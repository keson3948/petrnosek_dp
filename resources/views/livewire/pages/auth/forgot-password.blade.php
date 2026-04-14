<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        Zapomněli jste heslo? Zadejte svůj email a my vám pošleme odkaz pro obnovení hesla.
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <x-mary-form wire:submit="sendPasswordResetLink">
        <x-mary-input label="Email" icon="o-envelope" wire:model="email" error-field="email" type="email" name="email" required autofocus autocomplete="email" />

        <x-slot:actions>
            <div class="w-full flex items-center justify-between">
                <x-mary-button
                    label="Zpět na přihlášení"
                    link="{{ route('login') }}"
                    class="btn-link text-gray-600 underline hover:no-underline p-0 h-auto min-h-0"
                />
                <x-mary-button
                    label="Odeslat odkaz"
                    icon="o-paper-airplane"
                    class="btn-primary"
                    type="submit"
                    spinner="sendPasswordResetLink"
                />
            </div>
        </x-slot:actions>
    </x-mary-form>
</div>
