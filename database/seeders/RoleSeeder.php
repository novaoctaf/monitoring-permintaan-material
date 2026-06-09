<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Staff Role (Admin)
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->syncPermissions(Permission::all());

        // Create Store Role
        $storeRole = Role::firstOrCreate(['name' => 'store']);
        $storeRole->syncPermissions([
            'view-inventory',
            'view-stocks',
            'view-requests',
            'view-returns',
        ]);

        // Create Production Role
        $produksiRole = Role::firstOrCreate(['name' => 'produksi']);
        $produksiRole->syncPermissions([
            'view-inventory',
            'view-stocks',
            'view-requests',
            'create-requests',
            'edit-requests',
            'view-returns',
            'create-returns',
            'edit-returns',
        ]);
    }
}