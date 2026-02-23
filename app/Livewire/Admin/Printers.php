<?php

namespace App\Livewire\Admin;

use App\Models\Printer;
use Livewire\Component;
use Mary\Traits\Toast;

class Printers extends Component
{
    use Toast;

    public $printers;
    public bool $myModal = false;
    public $form = [];
    public $selectedPrinter = null;

    // Možnosti pro selectboxy (Brother QL)
    public $pageSizes = [
        ['id' => '29x62mm', 'name' => '29x62mm'],
        ['id' => '62mm', 'name' => '62mm (Nekonečná)'],
        ['id' => '29mm', 'name' => '29mm (Nekonečná)'],
    ];

    public $orientations = [
        ['id' => '3', 'name' => 'Portrait (3)'],
        ['id' => '4', 'name' => 'Landscape (4)'],
        ['id' => '5', 'name' => 'Landscape Reverse (5)'],
        ['id' => '6', 'name' => 'Portrait Reverse (6)'],
    ];

    public function boot()
    {
        abort_if(!auth()->user()->hasRole('Admin'), 403);
    }

    public function mount()
    {
        $this->refresh();
    }

    public function refresh()
    {
        $this->printers = Printer::all();
    }

    public function create()
    {
        $this->reset(['form', 'selectedPrinter']);
        $this->form = [
            'name' => '',
            'system_name' => '',
            'ip_address' => '',
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
            'form.system_name' => 'required', // Musí sedět s CUPS!
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
