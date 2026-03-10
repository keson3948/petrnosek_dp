<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component
{
    use Toast;

    /**
     * Send a password reset link to the authenticated user's email.
     */
    public function sendResetLink(): void
    {
        $status = Password::broker()->sendResetLink(
            ['email' => Auth::user()->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            $this->success('Odkaz pro obnovení hesla byl odeslán na váš e-mail.');
        } else {
            $this->error('Odkaz se nepodařilo odeslat. Zkuste to prosím znovu.');
        }
    }
}; ?>

<section>
    <p class="text-sm text-gray-600 mb-6">
        Pokud jste zapomněli své současné heslo a nemůžete jej tak upravit přes formulář výše, můžete si zde nechat poslat odkaz na e-mail pro bezpečné nastavení nového hesla.
    </p>

    <x-mary-button 
        label="Poslat odkaz na e-mail" 
        icon="o-envelope" 
        class="btn-primary" 
        wire:click="sendResetLink" 
        spinner="sendResetLink" 
    />
</section>
