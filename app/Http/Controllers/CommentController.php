<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Course;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $user = $request->user();

        $hasAccess = $user->is_admin || $user->purchases()
            ->where('course_id', $course->id)
            ->whereIn('status', ['paid', 'completed'])
            ->exists();

        if (! $hasAccess) {
            abort(403);
        }

        $data = $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        Comment::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
        ]);

        return back()->with('status', __('app.comments_posted'));
    }
}
