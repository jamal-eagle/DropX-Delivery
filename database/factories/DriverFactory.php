<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


class DriverFactory extends Factory
{

    public function definition(): array
    {
        return [
            'vehicle_type' => 'car',
            'vehicle_number' => strtoupper($this->faker->bothify('###-???')),
            'is_active' => true,
        ];
    }
}
