<?php

namespace App\Livewire\Admin;

use App\Models\Terminal;
use App\Models\Pracoviste;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;

#[Layout('layouts.app')]
class TerminalIndex extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public ?Terminal $terminal = null;

    public ?string $klic_pracoviste = null;
    public string $name = '';
    public string $slug = '';
    public bool $is_active = true;

    public function boot()
    {
        abort_if(!auth()->user()->can('manage terminals'), 403);
    }

    public function rules(): array
    {
        return [
            'klic_pracoviste' => 'nullable|string|max:15',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:terminals,slug,' . ($this->terminal?->id ?? 'NULL'),
            'is_active' => 'boolean',
        ];
    }

    public function create(): void
    {
        $this->reset(['terminal', 'klic_pracoviste', 'name', 'slug', 'is_active']);
        $this->resetValidation();
        $this->is_active = true;
        $this->drawer = true;
    }

    public function edit(Terminal $terminal): void
    {
        $this->resetValidation();
        $this->terminal = $terminal;
        $this->klic_pracoviste = $terminal->klic_pracoviste;
        $this->name = $terminal->name;
        $this->slug = $terminal->slug;
        $this->is_active = $terminal->is_active;
        $this->drawer = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->terminal) {
            $this->terminal->update([
                'klic_pracoviste' => $this->klic_pracoviste,
                'name' => $this->name,
                'slug' => $this->slug,
                'is_active' => $this->is_active,
            ]);
            $this->success('Terminál upraven.');
        } else {
            Terminal::create([
                'klic_pracoviste' => $this->klic_pracoviste,
                'name' => $this->name,
                'slug' => $this->slug,
                'is_active' => $this->is_active,
            ]);
            $this->success('Terminál vytvořen.');
        }

        $this->drawer = false;
    }

    public function delete(Terminal $terminal): void
    {
        $terminal->delete();
        $this->warning('Terminál smazán.');
    }

    public function render()
    {
        $headers = [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Název'],
            ['key' => 'slug', 'label' => 'Identifikátor (Slug)'],
            ['key' => 'klic_pracoviste', 'label' => 'Pracoviště'],
            ['key' => 'is_active', 'label' => 'Aktivní'],
        ];

        $terminals = Terminal::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%{$this->search}%")
                                                      ->orWhere('slug', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);

        // Build pracoviště options for select dropdown
        $pracoviste = Pracoviste::all()->map(function ($p) {
            return [
                'id' => trim($p->KlicPracoviste),
                'name' => trim($p->NazevUplny),
            ];
        });

        return view('livewire.admin.terminal-index', [
            'terminals' => $terminals,
            'headers' => $headers,
            'pracoviste' => $pracoviste,
        ]);
    }
}
