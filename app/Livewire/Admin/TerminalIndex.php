<?php

namespace App\Livewire\Admin;

use App\Models\Terminal;
use App\Models\Area;
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

    public $area_id = null;
    public string $name = '';
    public string $slug = '';
    public ?string $ip_address = '';
    public bool $is_active = true;

    public function boot()
    {
        abort_if(!auth()->user()->can('manage terminals'), 403);
    }

    public function rules(): array
    {
        return [
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:terminals,slug,' . ($this->terminal?->id ?? 'NULL'),
            'ip_address' => 'nullable|ip',
            'is_active' => 'boolean',
        ];
    }

    public function create(): void
    {
        $this->reset(['terminal', 'area_id', 'name', 'slug', 'ip_address', 'is_active']);
        $this->resetValidation();
        $this->is_active = true;
        $this->drawer = true;
    }

    public function edit(Terminal $terminal): void
    {
        $this->resetValidation();
        $this->terminal = $terminal;
        $this->area_id = $terminal->area_id;
        $this->name = $terminal->name;
        $this->slug = $terminal->slug;
        $this->ip_address = $terminal->ip_address;
        $this->is_active = $terminal->is_active;
        $this->drawer = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->terminal) {
            $this->terminal->update([
                'area_id' => $this->area_id,
                'name' => $this->name,
                'slug' => $this->slug,
                'ip_address' => $this->ip_address,
                'is_active' => $this->is_active,
            ]);
            $this->success('Terminál upraven.');
        } else {
            Terminal::create([
                'area_id' => $this->area_id,
                'name' => $this->name,
                'slug' => $this->slug,
                'ip_address' => $this->ip_address,
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
            ['key' => 'ip_address', 'label' => 'IP adresa'],
            ['key' => 'area.name', 'label' => 'Oblast', 'sortable' => false],
            ['key' => 'is_active', 'label' => 'Aktivní'],
        ];

        $terminals = Terminal::query()
            ->with('area')
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%{$this->search}%")
                                                      ->orWhere('slug', 'like', "%{$this->search}%")
                                                      ->orWhere('ip_address', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);

        return view('livewire.admin.terminal-index', [
            'terminals' => $terminals,
            'headers' => $headers,
            'areas' => Area::all(),
        ]);
    }
}
