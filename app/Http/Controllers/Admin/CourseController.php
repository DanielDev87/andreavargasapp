<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin');

        $view = $request->query('view') === 'past' ? 'past' : 'active';

        $query = Course::query();

        if ($view === 'past') {
            $query->where('type', 'presencial')->whereNotNull('event_date')->where('event_date', '<', now());
        } else {
            $query->where(function ($q) {
                $q->where('type', '!=', 'presencial')
                    ->orWhereNull('event_date')
                    ->orWhere('event_date', '>=', now());
            });
        }

        $courses = $query->latest()->paginate(20)->withQueryString();

        return view('admin.courses.index', compact('courses', 'view'));
    }

    public function toggleVisibility(Course $course)
    {
        $this->authorize('admin');

        $course->update(['is_hidden' => ! $course->is_hidden]);

        return back()->with('status', $course->is_hidden ? 'Curso ocultado' : 'Curso visible de nuevo');
    }

    public function create()
    {
        $this->authorize('admin');

        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $this->authorize('admin');

        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $data['image_url'] = 'storage/'.$request->file('image')->store('courses', 'public');
        }

        Course::create($data);

        return redirect()->route('admin.courses.index')->with('status', 'Curso creado');
    }

    public function edit(Course $course)
    {
        $this->authorize('admin');

        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('admin');

        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            if ($course->image_url && str_starts_with($course->image_url, 'storage/')) {
                Storage::disk('public')->delete(substr($course->image_url, strlen('storage/')));
            }

            $data['image_url'] = 'storage/'.$request->file('image')->store('courses', 'public');
        }

        $course->update($data);

        return redirect()->route('admin.courses.index')->with('status', 'Curso actualizado');
    }

    public function destroy(Course $course)
    {
        $this->authorize('admin');
        $course->delete();

        return back()->with('status', 'Curso eliminado');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:4096',
            'type' => 'required|in:virtual,presencial',
            'category' => 'nullable|string|max:100',
            'rating' => 'nullable|numeric|min:0|max:5',
            // Presencial
            'event_date' => 'nullable|required_if:type,presencial|date',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'seats_taken' => 'nullable|integer|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            // Virtual
            'duration_hours' => 'nullable|integer|min:0',
            'level' => 'nullable|string|max:100',
        ]);

        $data['seats_taken'] = $data['seats_taken'] ?? 0;
        unset($data['image']);

        return $data;
    }
}
