<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


class RestaurantFactory extends Factory
{

    public function definition(): array
    {
        return [
            'image' => $this->faker->imageUrl(),
            'description' => $this->faker->sentence,
            'working_hours_start' => '10:00:00',
            'working_hours_end' => '22:00:00',
            'status' => 'open',
        ];
    }
}
