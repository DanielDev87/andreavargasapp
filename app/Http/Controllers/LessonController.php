<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateCertificate;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Purchase;
use App\Services\BunnyStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LessonController extends Controller
{
    public function show(Request $request, Course $course, Module $module, Lesson $lesson)
    {
        // The route has three segments (course/module/lesson): all three must be declared
        // here, in this order, or Laravel's positional parameter resolution misaligns them.
        if ($lesson->module_id !== $module->id || $module->course_id !== $course->id) {
            abort(404);
        }

        $user = $request->user();

        // Ensure user has access via a completed purchase
        $hasPurchase = $user->purchases()->where('course_id', $course->id)->whereIn('status', ['paid', 'completed'])->exists();
        if (! $hasPurchase) {
            return redirect()->route('courses.show', $course)->with('warning', 'Debes comprar el curso para acceder a las lecciones.');
        }

        // Check sequential order: all previous lessons must be completed
        $ordered = $course->orderedLessons()->get();
        $index = $ordered->search(fn ($l) => $l->id === $lesson->id);

        if ($index === false) {
            abort(404);
        }

        $previous = $ordered->slice(0, $index);

        $incomplete = false;
        foreach ($previous as $pl) {
            $done = LessonProgress::where('user_id', $user->id)->where('lesson_id', $pl->id)->where('completed', true)->exists();
            if (! $done) {
                $incomplete = true;
                break;
            }
        }

        if ($incomplete) {
            return back()->with('warning', 'Debes completar las lecciones anteriores en orden.');
        }

        $comments = Comment::where('course_id', $course->id)->whereNull('parent_id')->with(['user', 'replies.user'])->oldest()->get();

        return view('lessons.show', compact('course', 'lesson', 'comments'));
    }

    public function embed(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        // Authorization: user must have purchase
        if (! $user || ! $user->purchases()->where('course_id', $lesson->module->course->id)->whereIn('status', ['paid', 'completed'])->exists()) {
            return response()->json(['message' => 'forbidden'], 403);
        }

        // Check sequential order same as show
        $course = $lesson->module->course;
        $ordered = $course->orderedLessons()->get();
        $index = $ordered->search(fn ($l) => $l->id === $lesson->id);
        if ($index === false) {
            return response()->json(['message' => 'not found'], 404);
        }

        $previous = $ordered->slice(0, $index);
        foreach ($previous as $pl) {
            $done = LessonProgress::where('user_id', $user->id)->where('lesson_id', $pl->id)->where('completed', true)->exists();
            if (! $done) {
                return response()->json(['message' => 'locked'], 423);
            }
        }

        // The video_id is the Bunny Stream video UUID. Access control (purchase check +
        // sequential order) already ran above. We generate a short-lived signed embed URL
        // here so the client never needs raw Bunny credentials.
        $bunny = app(BunnyStreamService::class);

        if (! $bunny->isConfigured()) {
            Log::warning('Bunny Stream is not configured: set BUNNY_STREAM_LIBRARY_ID in .env.');

            return response()->json(['message' => 'unavailable'], 503);
        }

        // Lessons still holding a legacy YouTube ID (or nothing) cannot be played from Bunny
        if (! $bunny->isValidVideoId($lesson->video_id)) {
            return response()->json(['message' => 'no video'], 404);
        }

        $embedUrl = $bunny->embedUrl($lesson->video_id);

        return response()->json(['embed_url' => $embedUrl]);
    }

    public function complete(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        $progress = LessonProgress::firstOrCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ], [
            'completed' => false,
        ]);

        $progress->update(['completed' => true, 'completed_at' => now()]);

        // After marking, check if the user completed all lessons of the course
        $course = $lesson->module->course;
        $total = $course->orderedLessons()->count();
        $completed = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $course->orderedLessons()->pluck('id'))
            ->where('completed', true)
            ->count();

        $courseCompleted = $completed >= $total && $total > 0;

        if ($courseCompleted && ! $user->certificates()->where('course_id', $course->id)->exists()) {
            $purchase = Purchase::where('user_id', $user->id)->where('course_id', $course->id)->first();

            if ($purchase) {
                GenerateCertificate::dispatch($purchase);
            }

            if (! $user->badges()->where('course_id', $course->id)->exists()) {
                $user->badges()->create([
                    'course_id' => $course->id,
                    'badge_data' => ['title' => 'Completó '.$course->title],
                    'share_url' => route('profile'),
                    'issued_at' => now(),
                ]);
            }
        }

        return response()->json(['ok' => true, 'course_completed' => $courseCompleted]);
    }
}
