<?php

namespace App\Livewire\Vedouci;

use App\Models\ProductionRecord;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
class Show extends Component
{
    use Toast;

    public User $operator;

    public string $dateFrom = '';
    public string $dateTo = '';

    public function boot()
    {
        abort_if(! auth()->user()->can('manage production records'), 403);
    }

    public function mount(string $klicSubjektu)
    {
        $this->operator = User::where('klic_subjektu', $klicSubjektu)->firstOrFail();
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    #[Computed]
    public function activeRecord()
    {
        return ProductionRecord::where('user_id', $this->operator->klic_subjektu)
            ->whereIn('status', [0, 1])
            ->with(['doklad', 'machine', 'operation'])
            ->first();
    }

    public function getTableHeaders(): array
    {
        return [
            ['key' => 'started_at', 'label' => 'Začátek'],
            ['key' => 'ended_at', 'label' => 'Konec'],
            ['key' => 'vp', 'label' => 'VP'],
            ['key' => 'machine', 'label' => 'Stroj'],
            ['key' => 'operation', 'label' => 'Operace'],
            ['key' => 'quantity', 'label' => 'Množství'],
            ['key' => 'time', 'label' => 'Čas'],
            ['key' => 'notes', 'label' => 'Poznámka'],
            ['key' => 'actions', 'label' => '', 'class' => 'text-right'],
        ];
    }

    #[Computed]
    public function records()
    {
        $query = $this->operator->productionRecords()
            ->with(['doklad', 'machine', 'operation'])
            ->where('status', 2)
            ->orderByDesc('ended_at');

        if ($this->dateFrom) {
            $query->where('ended_at', '>=', $this->dateFrom . ' 00:00:00');
        }
        if ($this->dateTo) {
            $query->where('ended_at', '<=', $this->dateTo . ' 23:59:59');
        }

        return $query->get();
    }

    public function deleteRecord(int $id): void
    {
        $record = $this->operator->productionRecords()->findOrFail($id);
        $record->delete();
        $this->success('Záznam smazán.');
    }

    public function render()
    {
        return view('livewire.vedouci.show', [
            'headers' => $this->getTableHeaders(),
        ]);
    }
}
