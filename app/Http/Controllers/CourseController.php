<?php

namespace App\Http\Controllers;

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

        $modules = $course->modules()->with('lessons')->orderBy('order')->get();

        return view('courses.show', compact('course', 'modules', 'progress', 'hasPurchase'));
    }
}
