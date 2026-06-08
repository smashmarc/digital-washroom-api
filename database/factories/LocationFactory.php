<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'    => fake()->company() . ' ' . fake()->randomElement(['Building', 'Wing', 'Tower', 'Block', 'Centre']),
            'address' => fake()->streetAddress() . ', ' . fake()->city(),
        ];
    }
}
