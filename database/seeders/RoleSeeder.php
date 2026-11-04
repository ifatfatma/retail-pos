<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]
            ->forgetCachedPermissions();

        $permissions = [
            'manage users',
            'manage products',
            'manage inventory',
            'manage purchases',
            'manage pos',
            'manage online orders',
            'view reports',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web',
        ]);

        $cashier = Role::firstOrCreate([
            'name' => 'Cashier',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions($permissions);

        $manager->syncPermissions([
            'manage products',
            'manage inventory',
            'manage purchases',
            'manage pos',
            'manage online orders',
            'view reports',
        ]);

        $cashier->syncPermissions([
            'manage pos',
        ]);
    }
}
