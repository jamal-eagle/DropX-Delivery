<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $driverUsers = User::where('user_type', 'driver')->get();

        foreach ($driverUsers as $user) {
            $user->driver()->create(Driver::factory()->make()->toArray());
        }
    }
}
