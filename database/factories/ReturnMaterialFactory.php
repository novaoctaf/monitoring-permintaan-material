<?php

namespace Database\Factories;

use App\Models\ReturnMaterial;
use App\Models\RequestMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnMaterial>
 */
class ReturnMaterialFactory extends Factory
{
    protected $model = ReturnMaterial::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'return_number' => 'RET-' . fake()->unique()->numerify('######'),
            'request_id' => RequestMaterial::where('status', 'approved')->inRandomOrder()->first()->id ?? null,
            'returned_by' => function() {
                return User::role('produksi')->inRandomOrder()->first()->id ?? User::factory();
            },
            'quantity' => function(array $attributes) {
                $request = RequestMaterial::find($attributes['request_id']);
                if ($request) {
                    return fake()->numberBetween(1, $request->quantity - 1);
                }
                return fake()->numberBetween(1, 10);
            },
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
