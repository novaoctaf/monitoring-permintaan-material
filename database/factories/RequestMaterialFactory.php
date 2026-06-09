<?php

namespace Database\Factories;

use App\Models\RequestMaterial;
use App\Models\User;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestMaterial>
 */
class RequestMaterialFactory extends Factory
{
    protected $model = RequestMaterial::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_number' => 'REQ-' . fake()->unique()->numerify('######'),
            'requested_by' => function() {
                return User::role('produksi')->inRandomOrder()->first()->id ?? User::factory();
            },
            'material_id' => Material::factory(),
            'quantity' => fake()->numberBetween(5, 100),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'notes' => fake()->optional(0.7)->sentence(),
            'approved_by' => function(array $attributes) {
                if ($attributes['status'] === 'approved' || $attributes['status'] === 'rejected') {
                    return User::role(['staff', 'store'])->inRandomOrder()->first()->id ?? null;
                }
                return null;
            },
            'approved_at' => function(array $attributes) {
                if ($attributes['status'] === 'approved' || $attributes['status'] === 'rejected') {
                    return now();
                }
                return null;
            },
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
