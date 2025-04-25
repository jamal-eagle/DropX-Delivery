<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = Area::all();

        // عملاء
        User::factory(10)->create(['user_type' => 'customer'])->each(function ($user) use ($areas) {
            $user->areas()->attach($areas->random(1)->pluck('id'));
        });

        // مطاعم
        User::factory(5)->create(['user_type' => 'restaurant'])->each(function ($user) use ($areas) {
            $user->areas()->attach($areas->random(rand(1, 2))->pluck('id'));
        });

        // سائقين
        User::factory(5)->create(['user_type' => 'driver'])->each(function ($user) use ($areas) {
            $user->areas()->attach($areas->random(1)->pluck('id'));
        });

        // 🔐 أدمن
        $admin = User::factory()->create([
            'fullname' => 'Super Admin',
            'phone' => '0999999999',
            'password' => bcrypt('admin123'), // كلمة مرور معروفة
            'user_type' => 'admin',
            'is_active' => true,
        ]);

        $admin->areas()->attach($areas->random(1)->pluck('id'));

    }
}
