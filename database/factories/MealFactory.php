<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


class MealFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'original_price' => $this->faker->randomFloat(2, 5, 50),
            'is_available' => true,
        ];
    }
}
