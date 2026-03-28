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
        Permission::firstOrCreate(['name' => 'manage zasobovani']);

        // Layout permission
        Permission::firstOrCreate(['name' => 'simplified layout']);

        // Per-page permissions
        Permission::firstOrCreate(['name' => 'view polozky']);
        Permission::firstOrCreate(['name' => 'view operace']);
        Permission::firstOrCreate(['name' => 'view subjekty']);
        Permission::firstOrCreate(['name' => 'view prostredky']);
        Permission::firstOrCreate(['name' => 'view stadokl']);
        Permission::firstOrCreate(['name' => 'view stapo']);
        Permission::firstOrCreate(['name' => 'edit profile']);

        $roleOperator = Role::firstOrCreate(['name' => 'Operator']);
        $roleOperator->givePermissionTo('simplified layout');

        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleAdmin->syncPermissions(Permission::where('name', '!=', 'simplified layout')->get());

        $roleZasobovac = Role::firstOrCreate(['name' => 'Zásobovač']);
        $roleZasobovac->givePermissionTo('manage zasobovani');

        $user = User::firstOrCreate([
            'email' => 'admin@mps.cz',
        ], [
            'name' => 'Admin',
            'password' => bcrypt('admin1234'),
        ]);

        $user->assignRole($roleAdmin);
    }
}
