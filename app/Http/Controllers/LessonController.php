<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateCertificate;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Purchase;
use App\Services\PlayerTokenService;
use Illuminate\Http\Request;

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

        return view('lessons.show', compact('course', 'lesson'));
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

        // Return a signed player URL that redirects to YouTube nocookie (prevents exposing video_id directly)
        if (empty($lesson->video_id)) {
            return response()->json(['message' => 'no video'], 404);
        }

        $service = new PlayerTokenService;
        $token = $service->generate($lesson->id, $user->id, 300);
        $playerUrl = route('lessons.player', ['lesson' => $lesson->id, 'token' => $token]);

        return response()->json(['embed_url' => $playerUrl]);
    }

    public function player(Request $request, Lesson $lesson, string $token)
    {
        $user = $request->user();

        // Allow token verification without session if token encodes user, but require matching user for safety
        $service = new PlayerTokenService;

        // If no user in session, try to extract user id from token payload during verification step
        if (! $user) {
            return response('Forbidden', 403);
        }

        if (! $service->verify($token, $lesson->id, $user->id)) {
            return response('Forbidden', 403);
        }

        // All good — redirect to youtube nocookie embed. controls=0 hides YouTube's own
        // control bar (including the "watch on YouTube" link); the page builds its own
        // play/pause controls via the IFrame Player API, driven through postMessage rather
        // than by exposing the raw video ID in our own page source.
        $url = 'https://www.youtube-nocookie.com/embed/'.$lesson->video_id
            .'?enablejsapi=1&rel=0&modestbranding=1&controls=0&iv_load_policy=3&disablekb=1&playsinline=1'
            .'&origin='.urlencode($request->getSchemeAndHttpHost());

        return redirect()->away($url);
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
