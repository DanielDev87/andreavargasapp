<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Purchase;
use App\Models\User;
use App\Services\BunnyStreamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BunnyLessonVideoTest extends TestCase
{
    use RefreshDatabase;

    private const VIDEO_ID = '0f3c5a2e-8b1d-4c7a-9e6f-2a4b8c1d3e5f';

    private function configureBunny(string $libraryId = '12345', ?string $tokenKey = 'secret'): void
    {
        config([
            'services.bunny_stream.library_id' => $libraryId,
            'services.bunny_stream.token_key' => $tokenKey,
        ]);

        $this->app->forgetInstance(BunnyStreamService::class);
    }

    /**
     * @return array{0: User, 1: Lesson}
     */
    private function purchasedLesson(?string $videoId = self::VIDEO_ID): array
    {
        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create();
        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'order' => 1]);
        $lesson = Lesson::create(['module_id' => $module->id, 'title' => 'L1', 'order' => 1, 'video_id' => $videoId]);

        Purchase::create(['user_id' => $user->id, 'course_id' => $course->id, 'amount' => 10, 'status' => 'completed']);

        return [$user, $lesson];
    }

    public function test_buyer_receives_a_signed_bunny_embed_url(): void
    {
        $this->configureBunny();
        [$user, $lesson] = $this->purchasedLesson();

        $response = $this->actingAs($user)->getJson(route('lessons.embed', $lesson));

        $response->assertOk();
        $this->assertStringStartsWith('https://player.mediadelivery.net/embed/12345/'.self::VIDEO_ID.'?', $response->json('embed_url'));
        $this->assertStringContainsString('token=', $response->json('embed_url'));
    }

    public function test_embed_reports_unavailable_when_bunny_is_not_configured(): void
    {
        $this->configureBunny(libraryId: '');
        [$user, $lesson] = $this->purchasedLesson();

        $this->actingAs($user)->getJson(route('lessons.embed', $lesson))
            ->assertStatus(503)
            ->assertJson(['message' => 'unavailable']);
    }

    public function test_lessons_with_a_legacy_youtube_id_are_not_sent_to_bunny(): void
    {
        $this->configureBunny();
        [$user, $lesson] = $this->purchasedLesson(videoId: '2rcgewG9OII');

        $this->actingAs($user)->getJson(route('lessons.embed', $lesson))
            ->assertNotFound()
            ->assertJson(['message' => 'no video']);
    }

    public function test_admin_can_paste_a_bunny_embed_link_and_only_the_video_id_is_stored(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create();
        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'order' => 1]);

        $this->actingAs($admin)->post(route('admin.lessons.store', [$course, $module]), [
            'title' => 'Intro',
            'video_id' => 'https://player.mediadelivery.net/embed/12345/'.strtoupper(self::VIDEO_ID).'?autoplay=true',
        ])->assertSessionHasNoErrors();

        $this->assertSame(self::VIDEO_ID, $module->lessons()->first()->video_id);
    }

    public function test_admin_cannot_save_a_youtube_id(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create();
        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'order' => 1]);

        $this->actingAs($admin)->post(route('admin.lessons.store', [$course, $module]), [
            'title' => 'Intro',
            'video_id' => '2rcgewG9OII',
        ])->assertSessionHasErrors('video_id');

        $this->assertSame(0, $module->lessons()->count());
    }

    public function test_admin_content_page_offers_a_preview_and_flags_legacy_videos(): void
    {
        $this->configureBunny();
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create();
        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'order' => 1]);
        Lesson::create(['module_id' => $module->id, 'title' => 'Bunny', 'order' => 1, 'video_id' => self::VIDEO_ID]);
        Lesson::create(['module_id' => $module->id, 'title' => 'Legacy', 'order' => 2, 'video_id' => '2rcgewG9OII']);

        $this->actingAs($admin)->get(route('admin.courses.content', $course))
            ->assertOk()
            ->assertSee('data-bunny-preview', escape: false)
            ->assertSee(__('app.admin_lesson_video_legacy'))
            ->assertDontSee(__('app.admin_bunny_not_configured'));
    }

    public function test_admin_content_page_warns_when_bunny_is_not_configured(): void
    {
        $this->configureBunny(libraryId: '');
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create();

        $this->actingAs($admin)->get(route('admin.courses.content', $course))
            ->assertOk()
            ->assertSee(__('app.admin_bunny_not_configured'));
    }
}
