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
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'banned_users' => User::where('status', 'banned')->count(),
            'new_users_7_days' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'total_posts' => ExchangePost::count(),
            'exchange_posts' => ExchangePost::where('post_type', 'exchange')->count(),
            'discussion_posts' => ExchangePost::where('post_type', 'discussion')->count(),
            'open_posts' => ExchangePost::where('status', 'open')->count(),
            'pending_posts' => ExchangePost::where('status', 'pending')->count(),
            'total_reviews' => Review::count(),
            'total_comments' => Comment::count(),
            'total_likes' => Like::count(),
            'average_rating' => round(Review::avg('rating') ?? 0, 2),
            'average_post_rating' => round(Comment::whereNotNull('rating')->avg('rating') ?? 0, 2),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'posts_by_category' => ExchangePost::selectRaw('category_id, count(*) as total')
                ->with('category:id,name')
                ->groupBy('category_id')
                ->get(),
        ];

        return response()->json($stats);
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }

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


    public function toggleUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'เปลี่ยนสถานะบัญชีตัวเองไม่ได้'], 422);
        }

        $user->status = ($user->status === 'active') ? 'banned' : 'active';
        $user->save();

        return response()->json([
            'message' => 'อัปเดตสถานะบัญชีแล้ว',
            'user' => $user
        ]);
    }

    public function deleteUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'ลบบัญชีตัวเองไม่ได้'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'ย้ายบัญชีไปถังขยะแล้ว']);
    }

    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'message' => 'กู้คืนบัญชีแล้ว',
            'user' => $user,
        ]);
    }


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


    public function updateReportStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,resolved,dismissed',
        ], [
            'status.required' => 'กรุณาเลือกสถานะ',
            'status.in' => 'สถานะไม่ถูกต้อง',
        ]);

        $report = Report::findOrFail($id);
        $report->status = $request->status;
        $report->save();

        return response()->json([
            'message' => 'บันทึกสถานะรายงานแล้ว',
            'report' => $report
        ]);
    }


    public function updatePostStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,open,closed,hidden',
        ], [
            'status.required' => 'กรุณาเลือกสถานะ',
            'status.in' => 'สถานะไม่ถูกต้อง',
        ]);

        $post = ExchangePost::findOrFail($id);
        $post->status = $request->status;
        $post->save();

        return response()->json([
            'message' => 'บันทึกสถานะประกาศแล้ว',
            'post' => $post,
        ]);
    }


    public function posts(Request $request)
    {
        $query = ExchangePost::with(['user:id,name,email', 'category']);

        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }

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

    public function restorePost($id)
    {
        $post = ExchangePost::onlyTrashed()->findOrFail($id);
        $post->restore();

        return response()->json([
            'message' => 'กู้คืนประกาศแล้ว',
            'post' => $post,
        ]);
    }


    public function comments(Request $request)
    {
        $comments = Comment::with(['user:id,name,email', 'exchangePost:id,title'])
            ->latest()
            ->paginate(10);

        return response()->json($comments);
    }
}
