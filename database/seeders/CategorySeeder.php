<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Bahan Baku',
                'description' => 'Material utama yang digunakan dalam produksi'
            ],
            [
                'name' => 'Bahan Penolong',
                'description' => 'Material pendukung proses produksi'
            ],
            [
                'name' => 'Kemasan',
                'description' => 'Material untuk pengemasan produk'
            ],
            [
                'name' => 'Barang Jadi',
                'description' => 'Produk yang telah melalui seluruh rangkaian produksi'
            ],
            
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}