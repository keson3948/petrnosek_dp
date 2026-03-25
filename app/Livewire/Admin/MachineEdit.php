<?php

namespace App\Livewire\Admin;

use App\Models\Operace;
use App\Models\Pracoviste;
use App\Models\PrednOperProstr;
use App\Models\Prostredek;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
class MachineEdit extends Component
{
    use Toast;

    public string $machineKey = '';
    public ?string $pracoviste = '';

    public bool $operationModal = false;
    public string $operationKey = '';

    public function boot()
    {
        abort_if(! auth()->user()->can('manage areas'), 403);
    }

    public function mount(string $machineKey)
    {
        $machine = Prostredek::dbcnt(730550)
            ->where('KlicProstredku', $machineKey)
            ->firstOrFail();

        $this->machineKey = trim($machine->KlicProstredku);
        $this->pracoviste = trim($machine->Pracoviste ?? '');
    }

    public function savePracoviste(): void
    {
        $this->validate(['pracoviste' => 'nullable|string|max:15']);

        Prostredek::where('KlicProstredku', $this->machineKey)
            ->update(['Pracoviste' => $this->pracoviste ?: null]);

        $this->success('Pracoviště uloženo.');
    }

    public function createOperation(): void
    {
        $this->reset('operationKey');
        $this->resetValidation('operationKey');
        $this->operationModal = true;
    }

    public function saveOperation(): void
    {
        $this->validate([
            'operationKey' => 'required|string|max:15',
        ]);

        $exists = PrednOperProstr::where('Prostredek', $this->machineKey)
            ->where('Operace', $this->operationKey)
            ->exists();

        if ($exists) {
            $this->addError('operationKey', 'Tato operace je již přiřazena.');
            return;
        }

        $nextId = PrednOperProstr::nextId();
        $nextPriority = (PrednOperProstr::where('Prostredek', $this->machineKey)->max('Priorita') ?? -1) + 1;

        PrednOperProstr::create([
            'ID' => $nextId,
            'Operace' => $this->operationKey,
            'Prostredek' => $this->machineKey,
            'Priorita' => $nextPriority,
        ]);

        $this->operationModal = false;
        $this->success('Operace přiřazena.');
    }

    public function removeOperation(int $id): void
    {
        PrednOperProstr::where('ID', $id)->delete();
        $this->warning('Operace odebrána.');
    }

    #[Renderless]
    public function reorderOperations(array $ids): void
    {
        foreach ($ids as $index => $id) {
            PrednOperProstr::where('ID', $id)->update(['Priorita' => $index]);
        }
    }

    public function render()
    {
        $machine = Prostredek::dbcnt(730550)
            ->where('KlicProstredku', $this->machineKey)
            ->first();

        $operations = PrednOperProstr::where('Prostredek', $this->machineKey)
            ->with('operace')
            ->orderBy('Priorita')
            ->get()
            ->map(function ($r) {
                $r->operace_kod = trim($r->Operace ?? '');
                $r->operace_nazev = trim($r->operace?->Nazev1 ?? '');
                return $r;
            });

        $pracovisteOptions = Pracoviste::orderBy('KlicPracoviste')
            ->get()
            ->map(fn($p) => [
                'id' => trim($p->KlicPracoviste),
                'name' => trim($p->KlicPracoviste) . ' — ' . trim($p->NazevUplny ?? ''),
            ]);

        $operaceOptions = Operace::orderBy('KlicPoloz')
            ->get()
            ->map(fn($o) => [
                'id' => trim($o->KlicPoloz),
                'name' => trim($o->KlicPoloz) . ' — ' . trim($o->Nazev1 ?? ''),
            ]);

        return view('livewire.admin.machine-edit', [
            'machine' => $machine,
            'operations' => $operations,
            'pracovisteOptions' => $pracovisteOptions,
            'operaceOptions' => $operaceOptions,
        ]);
    }
}
