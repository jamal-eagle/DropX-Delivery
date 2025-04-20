<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdRequest;
use App\Models\Advertisement;
use Exception;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    public function storeAds(Request $request)
        {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|max:2048',
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
                'message' => 'تم إضافة الإعلان بنجاح',
                'data' => $ad,
            ]);
        }

    public function get_all_ads()
        {
            $ads = Advertisement::select('id', 'title', 'description','image')->get();
            return  response()->json([$ads]);
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
                $path = $request->file('image')->store('ads', 'public');
                $ad->image = $path;
            }
            $ad->save();
            return response()->json([
                'status' => true,
                'message' => 'تم تعديل الإعلان بنجاح',
                'ad' => $ad
            ]);


        }

}
