<div>
    <x-mary-header title="Uživatelé" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live="search" placeholder="Hledat" />
        </x-slot:middle>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination link="/admin/users/{id}/edit">

            @scope('cell_roles_list', $user)
                @foreach($user->roles as $role)
                    <x-mary-badge :value="$role->name" class="badge-primary badge-sm" />
                @endforeach
            @endscope

            @scope('actions', $user)
                <div class="flex items-center gap-2">
                    <x-mary-button icon="o-eye" link="{{route('admin.users.edit', $user)}}" class="btn-ghost btn-sm " />
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>
</div>
