<?php

namespace Database\Seeders;

use App\Models\Meal;
use App\Models\Restaurant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurants = Restaurant::all();

        foreach ($restaurants as $restaurant) {
            Meal::factory(5)->create([
                'restaurant_id' => $restaurant->id,
                'category_id' => 1 
            ]);
        }
    }
}
