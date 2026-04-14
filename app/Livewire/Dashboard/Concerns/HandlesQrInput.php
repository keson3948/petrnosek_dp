<?php

namespace App\Livewire\Dashboard\Concerns;

use App\Models\EvPodsestav;

trait HandlesQrInput
{
    public ?string $qrStart = null;

    public ?string $qrD = null;

    protected function processQrParams(): void
    {
        if ($this->qrStart) {
            $this->handleQrPodsestava((int) $this->qrStart);
            $this->cleanUrl();
        } elseif ($this->qrD) {
            $this->handleQrDoklad($this->qrD);
            $this->cleanUrl();
        }
    }

    protected function cleanUrl(): void
    {
        $this->js("history.replaceState({}, '', window.location.pathname)");
    }

    protected function handleQrPodsestava(int $evPodsId): void
    {
        $evPods = EvPodsestav::find($evPodsId);
        if (! $evPods) {
            $this->error('Podsestava nebyla nalezena.');

            return;
        }

        $this->evPodsestavId = $evPods->ID;
        $this->drawing_number = trim($evPods->CisloVykresu ?? '');
        $this->selectedDokladRadekEntita = $evPods->EntitaRadkuVP;

        $vp = trim($evPods->VyrobniPrikaz ?? '');
        if ($vp) {
            $this->selectedSysPrimKlic = $vp;
            if ($this->selectedDokladRadekEntita) {
                $this->pozice_radku = trim($evPods->Pozice ?? '');
            }
        }

        $this->startStep = 5;
        $this->minStep = 5;
        $this->showStartDrawer = true;

        $this->autoSelectMachine();

        $this->qrStart = null;
        $this->cleanUrl();
    }

    protected function handleQrDoklad(string $d): void
    {
        $radekEntita = null;

        if (str_contains($d, '.')) {
            [$sysPrimKlic, $radekEntita] = explode('.', $d, 2);
            $radekEntita = (int) $radekEntita;
        } else {
            $sysPrimKlic = $d;
        }

        $this->selectedSysPrimKlic = $sysPrimKlic;

        $doklad = $this->selectedDoklad;
        if (! $doklad) {
            $this->selectedSysPrimKlic = null;
            $this->error('Výrobní příkaz nebyl nalezen.');

            return;
        }

        if ($radekEntita) {
            $this->selectedDokladRadekEntita = $radekEntita;
            $radek = $this->selectedDokladRadky->firstWhere('EntitaRad', $radekEntita);
            $this->pozice_radku = $radek ? $radek->Pozice : null;

            $podsCount = EvPodsestav::where('EntitaRadkuVP', $radekEntita)->count();
            $this->startStep = $podsCount > 0 ? 3 : 4;
            $this->minStep = $this->startStep;
        } else {
            $this->startStep = 2;
            $this->minStep = 2;
        }

        $this->showStartDrawer = true;
    }
}
