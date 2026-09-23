<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Purchase;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Course $course)
    {
        $this->authorize('admin');
        abort_unless($course->isPresencial(), 404);

        $purchases = Purchase::where('course_id', $course->id)
            ->where('status', 'completed')
            ->with('user')
            ->orderByRaw('checked_in_at is not null')
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.index', compact('course', 'purchases'));
    }

    public function checkIn(Course $course, Purchase $purchase)
    {
        $this->authorize('admin');
        abort_unless($purchase->course_id === $course->id, 404);

        $purchase->checkIn();

        return back()->with('status', 'Asistencia registrada: '.$purchase->user->name);
    }

    public function scan(Request $request, Course $course)
    {
        $this->authorize('admin');

        $data = $request->validate(['code' => 'required|string']);

        $purchase = Purchase::where('course_id', $course->id)
            ->where('attendance_code', strtoupper(trim($data['code'])))
            ->with('user')
            ->first();

        if (! $purchase) {
            return response()->json(['ok' => false, 'message' => 'Código no encontrado para este evento.'], 404);
        }

        $wasAlreadyCheckedIn = $purchase->checked_in_at !== null;
        $purchase->checkIn();

        return response()->json([
            'ok' => true,
            'already' => $wasAlreadyCheckedIn,
            'name' => $purchase->user->name,
        ]);
    }
}
