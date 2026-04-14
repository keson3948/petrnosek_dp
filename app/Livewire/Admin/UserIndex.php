<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
class UserIndex extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function boot()
    {
        abort_if(! auth()->user()->can('manage users'), 403);
    }

    public function syncUsers(): void
    {
        Artisan::call('economy:sync-users');
        $this->success('Synchronizace dokončena.');
    }

    public function edit(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function render()
    {
        $headers = [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Jméno'],
            ['key' => 'email', 'label' => 'E-mail'],
            ['key' => 'roles_list', 'label' => 'Role', 'sortable' => false],
        ];

        $users = User::query()
            ->with('roles')
            ->when($this->search, fn (Builder $q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);

        $users->getCollection()->transform(function ($user) {
            $user->roles_list = $user->roles->pluck('name')->join(', ');

            return $user;
        });

        return view('livewire.admin.user-index', [
            'users' => $users,
            'headers' => $headers,
        ]);
    }
}
