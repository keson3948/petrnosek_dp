<?php

namespace App\Livewire\Admin;

use App\Models\PrednOsobProstr;
use App\Models\Prostredek;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class UserEdit extends Component
{
    use Toast;

    public User $user;

    public string $name = '';
    public string $email = '';
    public ?string $izo = null;
    public ?string $klic_subjektu = null;

    public array $selectedRoles = [];

    public bool $confirmModal = false;

    public string $machineKey = '';
    public bool $machineModal = false;

    public function boot()
    {
        abort_if(! auth()->user()->can('manage users'), 403);
    }

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->izo = $user->izo;
        $this->klic_subjektu = $user->klic_subjektu;

        $this->selectedRoles = $user->roles->pluck('id')->toArray();
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'izo' => ['nullable', 'string', 'max:255'],
            'klic_subjektu' => ['nullable', 'string', 'max:255'], // Optional: could add an exists rule if we are certain about Firebird connection 'exists:firebird.eca_Subjekty,KlicSubjektu'
            'selectedRoles' => ['array']
        ];
    }

    public function save()
    {
        $this->validate();

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'izo' => $this->izo,
            'klic_subjektu' => $this->klic_subjektu,
        ]);

        $roles = Role::whereIn('id', $this->selectedRoles)->get();
        $this->user->syncRoles($roles);

        $this->success('Uživatel byl úspěšně upraven.');

        return redirect()->route('admin.users');
    }

    public function sendResetLink(User $user): void
    {
        $status = Password::broker()->sendResetLink(
            ['email' => $user->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            $this->success("Odkaz pro obnovu hesla odeslán na {$user->email}.");
        } else {
            $this->error('Nepodařilo se odeslat odkaz pro obnovu hesla. Zkontrolujte e-mailovou adresu a zkuste to znovu později.');
        }

        $this->confirmModal = false;
    }

    public function createMachine(): void
    {
        $this->reset('machineKey');
        $this->resetValidation('machineKey');
        $this->machineModal = true;
    }

    public function saveMachine(): void
    {
        $this->validate([
            'machineKey' => 'required|string|max:15',
        ]);

        $osoba = $this->user->klic_subjektu;

        $exists = PrednOsobProstr::where('Osoba', $osoba)
            ->where('Prrostredek', $this->machineKey)
            ->exists();

        if ($exists) {
            $this->addError('machineKey', 'Tento stroj je již přiřazen.');
            return;
        }

        $nextId = PrednOsobProstr::nextId();
        $nextPriority = (PrednOsobProstr::where('Osoba', $osoba)->max('Priorita') ?? -1) + 1;

        PrednOsobProstr::create([
            'ID' => $nextId,
            'Osoba' => $osoba,
            'Prrostredek' => $this->machineKey,
            'Priorita' => $nextPriority,
        ]);

        $this->machineModal = false;
        $this->success('Stroj přiřazen.');
    }

    public function removeMachine(int $id): void
    {
        PrednOsobProstr::where('ID', $id)->delete();
        $this->warning('Stroj odebrán.');
    }

    #[Renderless]
    public function reorderMachines(array $ids): void
    {
        foreach ($ids as $index => $id) {
            PrednOsobProstr::where('ID', $id)->update(['Priorita' => $index]);
        }
    }

    public function render()
    {
        $machines = $this->user->assignedMachines()
            ->with('prostredek')
            ->orderBy('Priorita')
            ->get()
            ->map(function ($r) {
                $r->prostredek_kod = trim($r->Prrostredek ?? '');
                $r->prostredek_nazev = trim($r->prostredek?->NazevUplny ?? '');
                return $r;
            });

        $prostredkyOptions = $this->user->klic_subjektu
            ? Prostredek::dbcnt(730550)
                ->where('KlicProstredku', '>=', '10000')
                ->orderBy('KlicProstredku')
                ->get()
                ->map(fn($p) => [
                    'id' => trim($p->KlicProstredku),
                    'name' => trim($p->KlicProstredku) . ' — ' . trim($p->NazevUplny ?? ''),
                ])
            : collect();

        return view('livewire.admin.user-edit', [
            'allRoles' => Role::all(),
            'machines' => $machines,
            'prostredkyOptions' => $prostredkyOptions,
        ]);
    }
}
