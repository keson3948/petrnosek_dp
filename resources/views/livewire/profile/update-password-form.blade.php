<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component
{
    use Toast;
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->success('Nové heslo bylo uloženo.');
    }
}; ?>

<section>
    <x-mary-form wire:submit="updatePassword" class="space-y-3">
        <x-mary-input label="Současné heslo" wire:model="current_password" id="update_password_current_password" name="current_password" type="password" class="block" autocomplete="current-password" />

        <x-mary-input label="Nové heslo" wire:model="password" id="update_password_password" name="password" type="password" class="block" autocomplete="new-password" />

        <x-mary-input label="Potvrzení nového hesla" wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" class="block" autocomplete="new-password" />

        <x-slot:actions>
            <x-mary-button label="Uložit nové heslo" class="btn-primary" type="submit" spinner="updatePassword" />
        </x-slot:actions>
    </x-mary-form>
</section>
