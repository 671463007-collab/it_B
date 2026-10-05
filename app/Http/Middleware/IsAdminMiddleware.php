<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {

        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }


        return response()->json([
            'message' => 'การเข้าถึงถูกปฏิเสธ: สงวนสิทธิ์เฉพาะผู้ดูแลระบบเท่านั้น',
        ], 403);
    }
}
