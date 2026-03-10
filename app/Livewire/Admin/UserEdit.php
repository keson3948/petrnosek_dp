<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
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

    public function render()
    {
        return view('livewire.admin.user-edit', [
            'allRoles' => Role::all(),
        ]);
    }
}
