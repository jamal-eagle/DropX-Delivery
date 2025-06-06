<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{

    public function definition(): array
    {
        return [
            'city' => $this->faker->city,
            'neighborhood' => $this->faker->streetName,
        ];

    }
}
