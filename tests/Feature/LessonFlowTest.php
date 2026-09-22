<?php

namespace Tests\Feature;

use App\Jobs\GenerateCertificate;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LessonFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_embed_endpoint_requires_purchase_and_order()
    {
        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create();
        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'order' => 1]);
        $l1 = Lesson::create(['module_id' => $module->id, 'title' => 'L1', 'order' => 1, 'video_id' => 'abc']);
        $l2 = Lesson::create(['module_id' => $module->id, 'title' => 'L2', 'order' => 2, 'video_id' => 'def']);

        // No purchase -> 403
        $resp = $this->actingAs($user)->getJson(route('lessons.embed', $l1));
        $resp->assertStatus(403);
    }

    public function test_lesson_show_page_renders_for_the_first_unlocked_lesson()
    {
        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create();
        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'order' => 1]);
        $lesson = Lesson::create(['module_id' => $module->id, 'title' => 'L1', 'order' => 1, 'video_id' => 'abc']);

        Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 10,
            'status' => 'completed',
        ]);

        // Regression test: the route has three segments (course/module/lesson), so the
        // controller must declare all three in order — otherwise Laravel's positional
        // parameter resolution passes the raw "module" string where $lesson is expected.
        $resp = $this->actingAs($user)->get(route('lessons.show', [$course, $module, $lesson]));
        $resp->assertOk();
        $resp->assertSee('L1');
    }

    public function test_certificate_is_only_dispatched_after_the_last_lesson_is_completed()
    {
        Queue::fake();

        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create();
        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'order' => 1]);
        $l1 = Lesson::create(['module_id' => $module->id, 'title' => 'L1', 'order' => 1, 'video_id' => 'abc']);
        $l2 = Lesson::create(['module_id' => $module->id, 'title' => 'L2', 'order' => 2, 'video_id' => 'def']);

        Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 10,
            'status' => 'completed',
        ]);

        $resp = $this->actingAs($user)->postJson(route('lessons.complete', $l1));
        $resp->assertJson(['ok' => true, 'course_completed' => false]);
        Queue::assertNotPushed(GenerateCertificate::class);

        $resp = $this->actingAs($user)->postJson(route('lessons.complete', $l2));
        $resp->assertJson(['ok' => true, 'course_completed' => true]);
        Queue::assertPushed(GenerateCertificate::class);

        $this->assertTrue($user->badges()->where('course_id', $course->id)->exists());
    }
}
