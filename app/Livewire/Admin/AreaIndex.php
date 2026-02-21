<?php

namespace App\Livewire\Admin;

use App\Models\Area;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;

#[Layout('layouts.app')]
class AreaIndex extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    
    public ?Area $area = null;

    public string $name = '';
    public string $code = '';
    public ?string $description = '';

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:areas,code,' . ($this->area?->id ?? 'NULL'),
            'description' => 'nullable|string',
        ];
    }

    public function create(): void
    {
        $this->reset(['area', 'name', 'code', 'description']);
        $this->resetValidation();
        $this->drawer = true;
    }

    public function edit(Area $area): void
    {
        $this->resetValidation();
        $this->area = $area;
        $this->name = $area->name;
        $this->code = $area->code;
        $this->description = $area->description;
        $this->drawer = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->area) {
            $this->area->update([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
            ]);
            $this->success('Oblast upravena.');
        } else {
            Area::create([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
            ]);
            $this->success('Oblast vytvořena.');
        }

        $this->drawer = false;
    }

    public function delete(Area $area): void
    {
        $area->delete();
        $this->warning('Oblast smazána.');
    }

    public function render()
    {
        $headers = [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Název'],
            ['key' => 'code', 'label' => 'Kód'],
        ];

        $areas = Area::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%{$this->search}%")
                                                      ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);

        return view('livewire.admin.area-index', [
            'areas' => $areas,
            'headers' => $headers,
        ]);
    }
}
