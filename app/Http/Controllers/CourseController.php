<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Course;
use App\Models\LessonProgress;

class CourseController extends Controller
{
    public function show(Course $course)
    {
        $user = request()->user();
        $progress = [];
        $hasPurchase = false;

        if ($user) {
            $progress = LessonProgress::where('user_id', $user->id)->pluck('lesson_id')->toArray();
            $hasPurchase = $user->purchases()->where('course_id', $course->id)->whereIn('status', ['paid', 'completed'])->exists();
        }

        // Hidden/past-event courses stay reachable for admins and for students who
        // already own them — everyone else gets the same 404 as a non-existent course.
        if (! $course->isPubliclyVisible() && ! $hasPurchase && ! ($user && $user->is_admin)) {
            abort(404);
        }

        $modules = $course->modules()->with('lessons')->orderBy('order')->get();

        $comments = $hasPurchase
            ? Comment::where('course_id', $course->id)->whereNull('parent_id')->with(['user', 'replies.user'])->oldest()->get()
            : collect();

        return view('courses.show', compact('course', 'modules', 'progress', 'hasPurchase', 'comments'));
    }
}
