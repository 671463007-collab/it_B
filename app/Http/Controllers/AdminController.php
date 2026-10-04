<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\ExchangePost;
use App\Models\Like;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // 1. ดึงสถิติภาพรวมระบบสำหรับหน้า Dashboard
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'banned_users' => User::where('status', 'banned')->count(),
            'new_users_7_days' => User::where('created_at', '>=', now()->subDays(7))->count(), // [เพิ่มใหม่]
            'total_posts' => ExchangePost::count(),
            'exchange_posts' => ExchangePost::where('post_type', 'exchange')->count(),
            'discussion_posts' => ExchangePost::where('post_type', 'discussion')->count(),
            'open_posts' => ExchangePost::where('status', 'open')->count(),
            'pending_posts' => ExchangePost::where('status', 'pending')->count(), // [เพิ่มใหม่]
            'total_reviews' => Review::count(),
            'total_comments' => Comment::count(),
            'total_likes' => Like::count(), // [เพิ่มใหม่]
            'average_rating' => round(Review::avg('rating') ?? 0, 2),
            'average_post_rating' => round(Comment::whereNotNull('rating')->avg('rating') ?? 0, 2),
            'pending_reports' => Report::where('status', 'pending')->count(),
            // [เพิ่มใหม่] จำนวนโพสต์แยกตามหมวดหมู่ ไว้ทำกราฟในหน้าแอดมิน
            'posts_by_category' => ExchangePost::selectRaw('category_id, count(*) as total')
                ->with('category:id,name')
                ->groupBy('category_id')
                ->get(),
        ];

        return response()->json($stats);
    }

    // 2. ดึงรายชื่อสมาชิกทั้งหมด
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(10);

        return response()->json($users);
    }

    // 3. เปลี่ยนสถานะสมาชิก (แบน หรือ ปลดแบน)
    public function toggleUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'ไม่สามารถเปลี่ยนสถานะบัญชีของตัวเองได้'], 422);
        }

        $user->status = ($user->status === 'active') ? 'banned' : 'active';
        $user->save();

        return response()->json([
            'message' => 'อัปเดตสถานะสมาชิกเรียบร้อยแล้ว',
            'user' => $user
        ]);
    }

    // 4. ดึงรายการรายงานปัญหาทั้งหมด
    public function reports(Request $request)
    {
        $query = Report::with([
            'reporter:id,name,email',
            'reportedUser:id,name,email,status',
            'exchangePost:id,title'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(10);

        return response()->json($reports);
    }

    // 5. ปรับอัปเดตสถานะรายงาน
    public function updateReportStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,resolved,dismissed',
        ], [
            'status.required' => 'กรุณาระบุสถานะ',
            'status.in' => 'สถานะไม่ถูกต้อง',
        ]);

        $report = Report::findOrFail($id);
        $report->status = $request->status;
        $report->save();

        return response()->json([
            'message' => 'อัปเดตสถานะการรายงานเรียบร้อยแล้ว',
            'report' => $report
        ]);
    }

    // 6. จุดเดียวที่เปลี่ยนสถานะโพสต์ได้ครบทุกแบบ (รวมอนุมัติ pending → open)
    public function updatePostStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,open,closed,hidden',
        ], [
            'status.required' => 'กรุณาระบุสถานะ',
            'status.in' => 'สถานะไม่ถูกต้อง',
        ]);

        $post = ExchangePost::findOrFail($id);
        $post->status = $request->status;
        $post->save();

        return response()->json([
            'message' => 'อัปเดตสถานะโพสต์เรียบร้อยแล้ว',
            'post' => $post,
        ]);
    }

    // 7. [เพิ่มใหม่] ดึงโพสต์ทั้งหมดทุกสถานะ (ไว้ทำตารางจัดการ+อนุมัติในหน้าแอดมิน)
    public function posts(Request $request)
    {
        $query = ExchangePost::with(['user:id,name,email', 'category']);

        if ($request->filled('post_type')) {
            $request->validate(['post_type' => 'in:exchange,discussion']);
            $query->where('post_type', $request->post_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                                    ->orWhere('description', 'like', '%' . $keyword . '%')
                                    ->orWhere('looking_for', 'like', '%' . $keyword . '%')
                                    ->orWhere('gadget_name', 'like', '%' . $keyword . '%');
            });
        }

        $posts = $query->latest()->paginate(10);

        return response()->json($posts);
    }

    // 8. [เพิ่มใหม่] ดึงคอมเมนต์ทั้งหมดในระบบ (ไว้ทำตารางลบคอมเมนต์ไม่เหมาะสม)
    public function comments(Request $request)
    {
        $comments = Comment::with(['user:id,name,email', 'exchangePost:id,title'])
            ->latest()
            ->paginate(10);

        return response()->json($comments);
    }
}
