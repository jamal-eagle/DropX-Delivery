<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{

    public function definition(): array
    {
        return [
            'fullname' => $this->faker->name,
            'phone' => $this->faker->unique()->numerify('09########'),
            'password' => bcrypt('password'),
            'user_type' => 'customer', 
            'is_active' => true,
            'fcm_token' => Str::random(20),
        ];
    }
}
