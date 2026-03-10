<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';
    public bool $showModal = false;

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section>
    <x-mary-button
        label="Smazat účet"
        icon="o-trash"
        class="btn-error text-white"
        @click="$wire.showModal = true"
    />

    <x-mary-modal wire:model="showModal" title="Opravdu chcete smazat svůj účet?" box-class="max-w-2xl">
        <x-mary-form wire:submit="deleteUser">
            <p class="text-sm text-gray-600">
                Po smazání účtu budou veškerá data trvale odstraněna. Pro potvrzení, že si přejete trvale smazat svůj účet, zadejte prosím své heslo.
            </p>

            <x-mary-input
                label="Heslo"
                wire:model="password"
                id="password"
                name="password"
                type="password"
                placeholder="Zadejte své heslo"
            />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.showModal = false" />
                <x-mary-button label="Smazat účet" class="btn-error text-white" type="submit" spinner="deleteUser" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
</section>
