<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_name' => fake()->name(),
            'ingredients' => json_encode([
                fake()->name() => $this->faker->numberBetween(1, 10),
                fake()->name() => $this->faker->numberBetween(1, 10),
                fake()->name() => $this->faker->numberBetween(1, 10),
            ]),
            'status' => fake()->randomElement(['pending', 'preparing', 'completed']),
        ];
    }
}
