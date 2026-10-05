<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\ExchangePost;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function index(Request $request, $postId)
    {
        $post = ExchangePost::findOrFail($postId);

        $currentUser = $request->user('sanctum');

        if ($post->status !== 'open') {
            $isOwner = $currentUser && $currentUser->id === $post->user_id;
            $isAdmin = $currentUser && $currentUser->role === 'admin';

            if (!$isOwner && !$isAdmin) {
                abort(404);
            }
        }

        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with(['user:id,name,avatar', 'replies.user:id,name,avatar'])
            ->latest()
            ->paginate(10);

        return response()->json($comments);
    }


    public function store(Request $request, $postId)
    {
        $post = ExchangePost::findOrFail($postId);

        if ($post->status !== 'open') {
            $isOwner = $request->user()->id === $post->user_id;
            $isAdmin = $request->user()->role === 'admin';

            if (!$isOwner && !$isAdmin) {
                abort(404);
            }
        }

        $rules = [
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ];
        if (($post->post_type ?? 'exchange') === 'exchange') {
            $rules['rating'] = 'nullable|integer|min:1|max:5';
        } else {
            $rules['rating'] = 'prohibited';
        }

        $validated = $request->validate($rules, [
            'content.required' => 'กรุณาเขียนความคิดเห็น',
            'content.max' => 'ความคิดเห็นต้องไม่เกิน 1,000 ตัวอักษร',
            'rating.min' => 'คะแนนต้องอย่างน้อย 1 ดาว',
            'rating.max' => 'คะแนนต้องไม่เกิน 5 ดาว',
        ]);

        if (!empty($validated['parent_id'])) {
            $parent = Comment::whereKey($validated['parent_id'])
                ->where('exchange_post_id', $post->id)
                ->whereNull('parent_id')
                ->exists();
            if (!$parent) {
                return response()->json(['message' => 'ไม่พบความคิดเห็นที่ต้องการตอบกลับ'], 422);
            }
        }

        $comment = new Comment($validated);
        $comment->user_id = $request->user()->id;
        $comment->exchange_post_id = $post->id;
        $comment->save();

        return response()->json([
            'message' => 'แสดงความคิดเห็นแล้ว',
            'comment' => $comment->load('user:id,name,avatar'),
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'คุณไม่มีสิทธิ์แก้ไขความคิดเห็นนี้'], 403);
        }

        $rules = [
            'content' => 'required|string|max:1000',
        ];
        if (($comment->exchangePost->post_type ?? 'exchange') === 'exchange') {
            $rules['rating'] = 'sometimes|nullable|integer|min:1|max:5';
        } else {
            $rules['rating'] = 'prohibited';
        }

        $validated = $request->validate($rules, [
            'content.required' => 'กรุณาเขียนความคิดเห็น',
            'content.max' => 'ความคิดเห็นต้องไม่เกิน 1,000 ตัวอักษร',
            'rating.min' => 'คะแนนต้องอย่างน้อย 1 ดาว',
            'rating.max' => 'คะแนนต้องไม่เกิน 5 ดาว',
        ]);

        $comment->update($validated);

        return response()->json([
            'message' => 'แก้ไขความคิดเห็นแล้ว',
            'comment' => $comment->load('user:id,name,avatar'),
        ]);
    }


    public function destroy(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'คุณไม่มีสิทธิ์ลบความคิดเห็นนี้'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'ลบความคิดเห็นแล้ว']);
    }
}
