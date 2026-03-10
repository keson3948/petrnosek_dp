<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'manage terminals']);
        Permission::firstOrCreate(['name' => 'manage printers']);
        Permission::firstOrCreate(['name' => 'manage areas']);
        Permission::firstOrCreate(['name' => 'manage users']);

        $roleOperator = Role::firstOrCreate(['name' => 'Operator']);
        //$roleOperator->givePermissionTo(['view terminals', 'view items']);

        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleAdmin->givePermissionTo(Permission::all());

        $user = User::firstOrCreate([
            'email' => 'admin@mps.cz',
        ], [
            'name' => 'Admin',
            'password' => bcrypt('admin1234'),
        ]);

        $user->assignRole($roleAdmin);
    }
}
