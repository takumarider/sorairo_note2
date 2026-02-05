<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['カット', 'カラー', 'パーマ', 'トリートメント']),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(3000, 10000),
            'duration' => fake()->randomElement([30, 60, 90, 120]),
            'is_active' => true,
        ];
    }
}
