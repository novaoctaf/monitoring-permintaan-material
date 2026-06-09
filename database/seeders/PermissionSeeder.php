<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Inventory Management
        Permission::create(['name' => 'view-inventory']);
        Permission::create(['name' => 'create-inventory']);
        Permission::create(['name' => 'edit-inventory']);
        Permission::create(['name' => 'delete-inventory']);
        
        // Stock Management
        Permission::create(['name' => 'view-stocks']);
        Permission::create(['name' => 'create-stocks']);
        Permission::create(['name' => 'edit-stocks']);
        Permission::create(['name' => 'delete-stocks']);
        
        // Material Request Management
        Permission::create(['name' => 'view-requests']);
        Permission::create(['name' => 'create-requests']);
        Permission::create(['name' => 'edit-requests']);
        Permission::create(['name' => 'delete-requests']);
        Permission::create(['name' => 'approve-requests']);
        Permission::create(['name' => 'reject-requests']);
        
        // Material Return Management
        Permission::create(['name' => 'view-returns']);
        Permission::create(['name' => 'create-returns']);
        Permission::create(['name' => 'edit-returns']);
        Permission::create(['name' => 'delete-returns']);
        Permission::create(['name' => 'approve-returns']);
        Permission::create(['name' => 'reject-returns']);
        
        // User Management
        Permission::create(['name' => 'view-users']);
        Permission::create(['name' => 'create-users']);
        Permission::create(['name' => 'edit-users']);
        Permission::create(['name' => 'delete-users']);
        
        // Role Management
        Permission::create(['name' => 'view-roles']);
        Permission::create(['name' => 'create-roles']);
        Permission::create(['name' => 'edit-roles']);
        Permission::create(['name' => 'delete-roles']);
    }
}