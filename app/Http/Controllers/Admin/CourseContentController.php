<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\Request;

class CourseContentController extends Controller
{
    public function index(Course $course)
    {
        $this->authorize('admin');

        $modules = $course->modules()->with('lessons')->get();
        $comments = Comment::where('course_id', $course->id)->whereNull('parent_id')->with(['user', 'replies.user'])->oldest()->get();

        return view('admin.courses.content', compact('course', 'modules', 'comments'));
    }

    public function storeModule(Request $request, Course $course)
    {
        $this->authorize('admin');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['order'] = $course->modules()->max('order') + 1;
        $data['course_id'] = $course->id;

        Module::create($data);

        return back()->with('status', 'Módulo creado.');
    }

    public function updateModule(Request $request, Course $course, Module $module)
    {
        $this->authorize('admin');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
        ]);

        $module->update($data);

        return back()->with('status', 'Módulo actualizado.');
    }

    public function destroyModule(Course $course, Module $module)
    {
        $this->authorize('admin');

        $module->delete();

        return back()->with('status', 'Módulo eliminado.');
    }

    public function storeLesson(Request $request, Course $course, Module $module)
    {
        $this->authorize('admin');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_id' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{11}$/'],
            'duration_seconds' => 'nullable|integer|min:0',
            'is_free' => 'nullable|boolean',
        ], [
            'video_id.regex' => __('app.admin_lesson_video_id_invalid'),
        ]);

        $data['is_free'] = $request->boolean('is_free');
        $data['order'] = $module->lessons()->max('order') + 1;
        $data['module_id'] = $module->id;

        Lesson::create($data);

        return back()->with('status', 'Lección creada.');
    }

    public function updateLesson(Request $request, Course $course, Module $module, Lesson $lesson)
    {
        $this->authorize('admin');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_id' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{11}$/'],
            'order' => 'required|integer',
            'duration_seconds' => 'nullable|integer|min:0',
            'is_free' => 'nullable|boolean',
        ], [
            'video_id.regex' => __('app.admin_lesson_video_id_invalid'),
        ]);

        $data['is_free'] = $request->boolean('is_free');

        $lesson->update($data);

        return back()->with('status', 'Lección actualizada.');
    }

    public function destroyLesson(Course $course, Module $module, Lesson $lesson)
    {
        $this->authorize('admin');

        $lesson->delete();

        return back()->with('status', 'Lección eliminada.');
    }
}
