<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Services\BunnyStreamService;
use Illuminate\Http\Request;

class CourseContentController extends Controller
{
    public function __construct(private readonly BunnyStreamService $bunny) {}

    public function index(Course $course)
    {
        $this->authorize('admin');

        $modules = $course->modules()->with('lessons')->get();
        $comments = Comment::where('course_id', $course->id)->whereNull('parent_id')->with(['user', 'replies.user'])->oldest()->get();
        $bunnyConfigured = $this->bunny->isConfigured();

        // Signed player URLs so the admin can check each pasted ID plays the intended video
        $previewUrls = $bunnyConfigured
            ? $modules->flatMap->lessons
                ->filter(fn (Lesson $lesson) => $this->bunny->isValidVideoId($lesson->video_id))
                ->mapWithKeys(fn (Lesson $lesson) => [$lesson->id => $this->bunny->embedUrl($lesson->video_id)])
            : collect();

        return view('admin.courses.content', compact('course', 'modules', 'comments', 'bunnyConfigured', 'previewUrls'));
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
            ...$this->lessonRules($request),
            'duration_seconds' => 'nullable|integer|min:0',
            'is_free' => 'nullable|boolean',
        ], $this->lessonMessages());

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
            ...$this->lessonRules($request),
            'order' => 'required|integer',
            'duration_seconds' => 'nullable|integer|min:0',
            'is_free' => 'nullable|boolean',
        ], $this->lessonMessages());

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

    /**
     * Shared lesson rules. Admins may paste the bare Bunny video ID or any Bunny link
     * containing it; only the normalized ID is validated and stored.
     *
     * @return array<string, mixed>
     */
    private function lessonRules(Request $request): array
    {
        $request->merge([
            'video_id' => $this->bunny->extractVideoId($request->input('video_id')) ?? $request->input('video_id'),
        ]);

        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_id' => ['required', 'string', 'regex:/^'.BunnyStreamService::VIDEO_ID_PATTERN.'$/i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function lessonMessages(): array
    {
        return ['video_id.regex' => __('app.admin_lesson_video_id_invalid')];
    }
}
