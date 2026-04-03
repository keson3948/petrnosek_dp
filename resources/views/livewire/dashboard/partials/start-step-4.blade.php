<div>
    <div class="py-4">
        <x-mary-input
            label="Číslo referenčního výkresu"
            wire:model.live.debounce.100ms="drawing_number"
            placeholder="Např. VYK-2026-001"
            class="input-lg font-mono"
            hint="Volitelné – můžete pokračovat bez vyplnění."
            autofocus
            clearable
        />
    </div>
</div>
