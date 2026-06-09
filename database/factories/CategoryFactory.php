<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the category is for raw materials.
     */
    public function rawMaterial(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Bahan Baku',
            'description' => 'Material utama yang digunakan dalam produksi',
        ]);
    }

    /**
     * Indicate that the category is for supporting materials.
     */
    public function supportingMaterial(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Bahan Penolong',
            'description' => 'Material pendukung proses produksi',
        ]);
    }

    /**
     * Indicate that the category is for packaging materials.
     */
    public function packaging(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Kemasan',
            'description' => 'Material untuk pengemasan produk',
        ]);
    }

    /**
     * Indicate that the category is for spare parts.
     */
    public function sparePart(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Sparepart',
            'description' => 'Suku cadang dan peralatan',
        ]);
    }
}