<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\On;
use Livewire\Component;

class ProductionTracker extends Component
{
    public bool $hasActiveRecord = false;

    public function mount()
    {
        $this->checkActiveRecord();
    }

    #[On('operation-started')]
    #[On('operation-completed')]
    public function checkActiveRecord(): void
    {
        $this->hasActiveRecord = auth()->user()->productionRecords()
            ->whereIn('status', ['in_progress', 'paused'])
            ->exists();
    }

    public function openStartDrawer(): void
    {
        $this->dispatch('open-start-drawer');
    }

    public function render()
    {
        return view('livewire.dashboard.production-tracker');
    }
}
