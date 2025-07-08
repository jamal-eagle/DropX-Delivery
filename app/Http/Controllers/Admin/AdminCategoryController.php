<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => '✅ تم إنشاء التصنيف بنجاح.',
            'data' => $category,
        ], 201);
    }

    public function deleteCategory($id)
    {
        $category = \App\Models\Category::findOrFail($id);

        $category->delete();

        return response()->json([
            'message' => '🗑️ تم حذف التصنيف بنجاح.',
        ]);
    }

    public function getAllCategories()
    {
        $categories = Category::all();

        return response()->json([
            'status' => true,
            'message' => '📋 قائمة التصنيفات',
            'data' => $categories,
        ]);
    }
}
