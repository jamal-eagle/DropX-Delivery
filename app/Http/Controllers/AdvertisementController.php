<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdRequest;
use App\Models\Advertisement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    public function storeAds(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('ads', 'public');
        }

        $ad = Advertisement::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $path,
        ]);

        return response()->json([
            'status' => true,
            'message' => '✅ تم إضافة الإعلان بنجاح',
            'data' => $ad,
        ], 201);
    }

    public function get_all_ads()
    {
        $ads = Advertisement::select('id', 'title', 'description', 'image')->get();

        $ads->transform(function ($ad) {
            $ad->image = $ad->image ? asset('storage/' . $ad->image) : null;
            return $ad;
        });

        return response()->json($ads, 200);
    }

    public function update_Ads(UpdateAdRequest $request, $id)
    {
        $ad = Advertisement::findOrFail($id);
        if ($request->has('title')) {
            $ad->title = $request->title;
        }

        if ($request->has('description')) {
            $ad->description = $request->description;
        }

        if ($request->hasFile('image')) {
            if ($ad->image && Storage::disk('public')->exists($ad->image)) {
                Storage::disk('public')->delete($ad->image);
            }

            $path = $request->file('image')->store('ads', 'public');
            $ad->image = $path;
        }

        $ad->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تعديل الإعلان بنجاح',
            'ad' => $ad
        ], 200);
    }

    public function deleteads($ads_id)
    {
        $ads = Advertisement::find($ads_id);
        $ads->delete();
        return response()->json("تم حذف الاعلان بنجاح ");
    }
}
