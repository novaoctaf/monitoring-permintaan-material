<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Material;
use App\Models\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run permissions and roles seeders first
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            CategorySeeder::class,
        ]);
        
        // Create admin (staff) user
        $staffUser = User::factory()->create([
            'name' => 'Staff Admin',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
        ]);
        $staffUser->assignRole('staff');
        
        // Create store user
        $storeUser = User::factory()->create([
            'name' => 'Store Manager',
            'email' => 'store@example.com',
            'password' => Hash::make('password'),
        ]);
        $storeUser->assignRole('store');
        
        // Create production user
        $produksiUser = User::factory()->create([
            'name' => 'Production Manager',
            'email' => 'produksi@example.com',
            'password' => Hash::make('password'),
        ]);
        $produksiUser->assignRole('produksi');
        
        // Create some additional users for each role
        User::factory(2)->create()->each(function ($user) {
            $user->assignRole('staff');
        });
        
        User::factory(3)->create()->each(function ($user) {
            $user->assignRole('store');
        });
        
        User::factory(5)->create()->each(function ($user) {
            $user->assignRole('produksi');
        });
        
        // Create materials and stocks
        Material::factory(20)->create()->each(function ($material) {
            Stock::factory()->create(['material_id' => $material->id]);
        });
        
        // Note: We don't seed RequestMaterial and ReturnMaterial models here
        // as they're better created once the app is running
        // to ensure proper relationships
    }
}
