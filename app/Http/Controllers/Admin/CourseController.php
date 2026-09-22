<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $this->authorize('admin');
        $courses = Course::latest()->paginate(20);

        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        $this->authorize('admin');

        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $this->authorize('admin');

        Course::create($this->validatedData($request));

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

        $course->update($this->validatedData($request));

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

        return $data;
    }
}
