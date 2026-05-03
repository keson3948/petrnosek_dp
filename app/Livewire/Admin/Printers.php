<?php

namespace App\Livewire\Admin;

use App\Models\Printer;
use Livewire\Component;
use Mary\Traits\Toast;

class Printers extends Component
{
    use Toast;

    public mixed $printers = null;
    public string $search = '';
    public bool $myModal = false;
    public array $form = [];
    public ?Printer $selectedPrinter = null;

    public array $pageSizes = [
        ['id' => '62x29', 'name' => '62x29mm'],
    ];

    public array $orientations = [
        ['id' => '3', 'name' => 'Portrait (3)'],
        ['id' => '4', 'name' => 'Landscape (4)'],
        ['id' => '5', 'name' => 'Landscape Reverse (5)'],
        ['id' => '6', 'name' => 'Portrait Reverse (6)'],
    ];

    public function boot()
    {
        abort_if(!auth()->user()->can('manage printers'), 403);
    }

    public function mount()
    {
        $this->refresh();
    }

    public function refresh()
    {
        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
            $this->printers = Printer::where('name', 'like', $term)
                ->orWhere('system_name', 'like', $term)
                ->orWhere('ip_address', 'like', $term)
                ->get();
        } else {
            $this->printers = Printer::all();
        }
    }

    public function updatedSearch()
    {
        $this->refresh();
    }

    public function create()
    {
        $this->reset(['form', 'selectedPrinter']);
        $this->form = [
            'name' => '',
            'system_name' => '',
            'ip_address' => '',
            'port' => 9100,
            'page_size' => '29x62mm',
            'orientation' => '4',
            'is_active' => true
        ];
        $this->myModal = true;
    }

    public function edit(Printer $printer)
    {
        $this->selectedPrinter = $printer;
        $this->form = $printer->toArray();
        $this->myModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.name' => 'required',
            'form.system_name' => 'required',
            'form.page_size' => 'required',
        ]);

        if ($this->selectedPrinter) {
            $this->selectedPrinter->update($this->form);
            $this->success('Tiskárna aktualizována.');
        } else {
            Printer::create($this->form);
            $this->success('Tiskárna přidána.');
        }

        $this->myModal = false;
        $this->refresh();
    }

    public function delete(Printer $printer)
    {
        $printer->delete();
        $this->refresh();
        $this->warning('Tiskárna smazána.');
    }

    public function render()
    {
        return view('livewire.admin.printers');
    }
}
