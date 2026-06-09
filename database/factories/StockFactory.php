<?php

namespace Database\Factories;

use App\Models\Stock;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    protected $model = Stock::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'material_id' => Material::factory(),
            'quantity' => fake()->numberBetween(10, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}