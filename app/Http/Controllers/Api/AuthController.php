<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required'      => 'กรุณากรอกชื่อ',
            'email.required'     => 'กรุณากรอกอีเมล',
            'email.email'        => 'อีเมลไม่ถูกต้อง',
            'email.unique'       => 'อีเมลนี้มีบัญชีผู้ใช้อยู่แล้ว',
            'password.required'  => 'กรุณากรอกรหัสผ่าน',
            'password.min'       => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านทั้งสองช่องไม่ตรงกัน',
        ]);

        $user = User::create($validated);

        $user->refresh();

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
            ], 401);
        }

        if ($user->status === 'banned') {
            return response()->json([
                'message' => 'บัญชีนี้ถูกระงับการใช้งาน',
            ], 403);
        }

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ออกจากระบบแล้ว']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
