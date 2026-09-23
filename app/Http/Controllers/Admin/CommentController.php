<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        $this->authorize('admin');

        $comments = Comment::whereNull('parent_id')
            ->with(['user', 'course', 'replies' => fn ($query) => $query->with('user')->oldest()])
            ->withCount(['replies as admin_replies_count' => fn ($query) => $query->whereHas('user', fn ($q) => $q->where('is_admin', true)),
            ])
            ->orderBy('admin_replies_count')
            ->latest()
            ->paginate(20);

        return view('admin.comments.index', compact('comments'));
    }

    public function reply(Request $request, Comment $comment)
    {
        $this->authorize('admin');

        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        Comment::create([
            'course_id' => $comment->course_id,
            'user_id' => $request->user()->id,
            'parent_id' => $comment->parent_id ?? $comment->id,
            'body' => $data['body'],
        ]);

        return back()->with('status', __('app.admin_comments_reply_posted'));
    }
}
