<?php

namespace App\Livewire\Admin;

use App\Models\Operace;
use App\Models\PrednOperProstr;
use App\Models\Prostredek;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
class MachineOperationIndex extends Component
{
    use Toast;

    public string $filterProstredek = '';
    public bool $drawer = false;

    public string $prostredek = '';
    public string $operace = '';

    public function boot()
    {
        abort_if(!auth()->user()->can('manage areas'), 403);
    }

    public function rules(): array
    {
        return [
            'prostredek' => 'required|string|max:15',
            'operace' => 'required|string|max:15',
        ];
    }

    public function create(): void
    {
        $this->reset(['prostredek', 'operace']);
        $this->resetValidation();
        $this->drawer = true;
    }

    public function save(): void
    {
        $this->validate();

        // Check for duplicate
        $exists = PrednOperProstr::where('Prostredek', $this->prostredek)
            ->where('Operace', $this->operace)
            ->exists();

        if ($exists) {
            $this->addError('operace', 'Tento vztah již existuje.');
            return;
        }

        $nextId = (PrednOperProstr::max('ID') ?? 0) + 1;

        PrednOperProstr::create([
            'ID' => $nextId,
            'Operace' => $this->operace,
            'Prostredek' => $this->prostredek,
        ]);

        $this->drawer = false;
        $this->success('Vztah přidán.');
    }

    public function delete(int $id): void
    {
        PrednOperProstr::where('ID', $id)->delete();
        $this->warning('Vztah smazán.');
    }

    public function render()
    {
        $headers = [
            ['key' => 'prostredek_kod', 'label' => 'Kód prostředku', 'class' => 'w-32'],
            ['key' => 'prostredek_nazev', 'label' => 'Prostředek'],
            ['key' => 'operace_kod', 'label' => 'Kód operace', 'class' => 'w-32'],
            ['key' => 'operace_nazev', 'label' => 'Operace'],
        ];

        $query = PrednOperProstr::with(['prostredek', 'operace']);

        if ($this->filterProstredek) {
            $query->where('Prostredek', $this->filterProstredek);
        }

        $records = $query->get()->map(function ($r) {
            $r->prostredek_kod = trim($r->Prostredek ?? '');
            $r->prostredek_nazev = trim($r->prostredek?->NazevUplny ?? '');
            $r->operace_kod = trim($r->Operace ?? '');
            $r->operace_nazev = trim($r->operace?->Nazev1 ?? '');
            return $r;
        })->sortBy(['prostredek_kod', 'operace_kod'])->values();

        // Build options for selects
        $prostredkyOptions = Prostredek::dbcnt(730550)
            ->where('KlicProstredku', 'like', '20%')
            ->orderBy('KlicProstredku')
            ->get()
            ->map(fn($p) => [
                'id' => trim($p->KlicProstredku),
                'name' => trim($p->KlicProstredku) . ' — ' . trim($p->NazevUplny ?? ''),
            ]);

        $operaceOptions = Operace::orderBy('KlicPoloz')
            ->get()
            ->map(fn($o) => [
                'id' => trim($o->KlicPoloz),
                'name' => trim($o->KlicPoloz) . ' — ' . trim($o->Nazev1 ?? ''),
            ]);

        return view('livewire.admin.machine-operation-index', [
            'records' => $records,
            'headers' => $headers,
            'prostredkyOptions' => $prostredkyOptions,
            'operaceOptions' => $operaceOptions,
        ]);
    }
}
