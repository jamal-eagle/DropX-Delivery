<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Driver;
use App\Models\DriverAreaTurn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function storeDriver(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:75',
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|min:6',
            'vehicle_type' => 'required|string|max:50',
            'vehicle_number' => 'required|string|max:50',
            'city' => 'required|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $area = Area::firstOrCreate(
                ['city' => $request->city]
            );

            $user = User::create([
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'user_type' => 'driver',
                'is_active' => true,
            ]);

            $driver = Driver::create([
                'user_id' => $user->id,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'is_active' => true,
            ]);

            DB::table('area_user')->insert([
                'user_id' => $user->id,
                'area_id' => $area->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $maxOrder = DriverAreaTurn::where('area_id', $area->id)->max('turn_order') ?? 0;

            DriverAreaTurn::create([
                'driver_id' => $driver->id,
                'area_id' => $area->id,
                'turn_order' => $maxOrder + 1,
                'is_next' => $maxOrder === 0,
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'message' => '✅ تم إنشاء السائق بنجاح.',
                'driver' => $driver,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '❌ حدث خطأ أثناء إنشاء السائق.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function resetDriverPassword(Request $request)
    {
        $request->validate([
            'driver_user_id' => 'required|exists:users,id',
            'new_password' => 'required|string|min:6'
        ]);

        $user = User::find($request->driver_user_id);
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح'], 200);
    }
}
