<?php

use App\Models\Printer;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component
{
    use Toast;

    public ?int $printer_id = null;

    public function mount(): void
    {
        $this->printer_id = Auth::user()->printer_id;
    }

    public function updatePrinter(): void
    {
        $this->validate([
            'printer_id' => ['nullable', 'exists:printers,id'],
        ]);

        Auth::user()->update([
            'printer_id' => $this->printer_id,
        ]);

        $this->success('Preferovaná tiskárna byla uložena.');
    }

    public function with(): array
    {
        return [
            'printers' => Printer::where('is_active', true)->get(),
        ];
    }
}; ?>

<section>
    <x-mary-form wire:submit="updatePrinter" class="space-y-3">
        <x-mary-select
            label="Preferovaná tiskárna"
            icon="o-printer"
            :options="$printers"
            option-label="name"
            option-value="id"
            wire:model="printer_id"
            placeholder="Vyberte tiskárnu..."
            placeholder-value=""
        />

        <x-slot:actions>
            <x-mary-button label="Uložit tiskárnu" class="btn-primary" type="submit" spinner="updatePrinter" />
        </x-slot:actions>
    </x-mary-form>
</section>
