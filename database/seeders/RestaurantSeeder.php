<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurantUsers = User::where('user_type', 'restaurant')->get();

        foreach ($restaurantUsers as $user) {
            $user->restaurant()->create(Restaurant::factory()->make()->toArray());
    }
}
}
