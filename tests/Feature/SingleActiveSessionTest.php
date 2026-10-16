<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SingleActiveSessionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Session data held by a device that signed in before the password hash was rotated.
     *
     * @return array<string, string>
     */
    private function sessionFromOtherDevice(string $passwordHash): array
    {
        return ['password_hash_web' => $this->app['auth']->guard('web')->hashPasswordForCookie($passwordHash)];
    }

    public function test_signing_in_rotates_the_password_hash_so_other_devices_are_logged_out(): void
    {
        $user = User::factory()->create();
        $hashBeforeLogin = $user->password;

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($hashBeforeLogin, $user->fresh()->password);
    }

    public function test_a_device_whose_session_was_replaced_is_sent_to_login_with_an_explanation(): void
    {
        $user = User::factory()->create();
        $staleSession = $this->sessionFromOtherDevice($user->password);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->actingAs($user->fresh())
            ->withSession($staleSession)
            ->get(route('profile'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('warning', __('app.session_replaced'));

        $this->assertGuest();
    }

    public function test_the_lesson_heartbeat_reports_a_replaced_session(): void
    {
        $user = User::factory()->create();
        $staleSession = $this->sessionFromOtherDevice($user->password);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->actingAs($user->fresh())
            ->withSession($staleSession)
            ->getJson(route('session.ping'))
            ->assertUnauthorized();
    }

    public function test_the_current_device_stays_signed_in(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->get(route('profile'))->assertOk();
        $this->getJson(route('session.ping'))->assertNoContent();
    }

    public function test_the_lesson_player_shows_the_student_email_as_a_watermark(): void
    {
        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create();
        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'order' => 1]);
        $lesson = Lesson::create(['module_id' => $module->id, 'title' => 'L1', 'order' => 1, 'video_id' => '0f3c5a2e-8b1d-4c7a-9e6f-2a4b8c1d3e5f']);
        Purchase::create(['user_id' => $user->id, 'course_id' => $course->id, 'amount' => 10, 'status' => 'completed']);

        $this->actingAs($user)
            ->get(route('lessons.show', [$course, $module, $lesson]))
            ->assertOk()
            ->assertSee('id="player-watermark"', escape: false)
            ->assertSee($user->email);
    }
}
