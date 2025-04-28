<?php

namespace App\Http\Controllers\area;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use App\Models\User;

class AreaController extends Controller
{

public function addAddress(Request $request)
{
    $request->validate([
        'city' => 'required|string|exists:areas,city',
        'neighborhood' => 'required|string|max:100',
    ]);

    $user = auth()->user();
    if ($user->areas()->count() >= 5) {
        return response()->json([
            'message' => 'لا يمكنك إضافة أكثر من 5 عناوين يرجى حذف عنوان سابق.',
        ], 400);
    }

    $area = Area::where('city', $request->city)
                ->where('neighborhood', $request->neighborhood)
                ->first();

    if (!$area) {
        $area = Area::create([
            'city' => $request->city,
            'neighborhood' => $request->neighborhood,
        ]);
    }
    if ($user->areas()->where('area_id', $area->id)->exists()) {
        return response()->json([
            'message' => 'هذا العنوان مضاف مسبقًا.',
        ], 400);

    }

    $user->areas()->syncWithoutDetaching([$area->id]);

    return response()->json([
        'message' => 'تمت إضافة العنوان بنجاح.',
        'area' => $area,
    ],201);
}
public function deleteAddress($areaId)
    {
        $user = auth()->user();

        $deleted = $user->areas()->detach($areaId);

        if (!$deleted) {
            return response()->json(['message' => 'العنوان غير موجود أو غير مرتبط بك.'], 404);
        }

        return response()->json(['message' => 'تم حذف العنوان بنجاح.'],204);
}

public function getMyAddresses()
    {
        $user = auth()->user();

        $areas = $user->areas()->get(['areas.id', 'city', 'neighborhood']);

        return response()->json([$areas,200]);
}



}
