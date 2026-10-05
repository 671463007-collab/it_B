<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }


    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',

            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'name.required' => 'กรุณากรอกชื่อ',
            'name.max' => 'ชื่อต้องไม่เกิน 255 ตัวอักษร',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'อีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้มีบัญชีผู้ใช้อยู่แล้ว',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านทั้งสองช่องไม่ตรงกัน',
            'avatar.image' => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพเท่านั้น',
            'avatar.mimes' => 'เลือกรูป JPG หรือ PNG เท่านั้น',
            'avatar.max' => 'รูปต้องมีขนาดไม่เกิน 2MB',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];  

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'บันทึกโปรไฟล์เรียบร้อยแล้ว',
            'user' => $user
        ]);
    }


    public function publicShow(Request $request, $id)
    {
        $user = User::with(['exchangePosts' => function ($query) {
            $query->where('status', 'open')->with('category', 'images');
        }])->findOrFail($id);

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url,
            'exchange_posts' => $user->exchangePosts,
        ];

        return response()->json($data);
    }
}