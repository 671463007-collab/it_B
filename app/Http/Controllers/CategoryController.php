<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', 1)->get();
        return response()->json($categories);
    }

    public function adminIndex()
    {
        $categories = Category::latest()->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ], [
            'name.required' => 'กรุณากรอกชื่อหมวดหมู่',
            'name.unique' => 'หมวดหมู่นี้มีอยู่แล้ว',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'เพิ่มหมวดหมู่แล้ว',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'กรุณากรอกชื่อหมวดหมู่',
            'name.unique' => 'หมวดหมู่นี้มีอยู่แล้ว',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'บันทึกหมวดหมู่แล้ว',
            'category' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->exchangePosts()->exists() || $category->reviews()->exists()) {
            return response()->json([
                'message' => 'ลบหมวดหมู่นี้ไม่ได้ เพราะยังมีประกาศหรือรีวิวใช้งานอยู่ ลองปิดหมวดหมู่แทน',
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'ลบหมวดหมู่แล้ว']);
    }
}