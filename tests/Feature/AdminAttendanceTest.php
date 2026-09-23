<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_non_admin_cannot_view_the_attendance_panel()
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['type' => 'presencial']);

        $response = $this->actingAs($student)->get(route('admin.attendance.index', $course));

        $response->assertForbidden();
    }

    public function test_the_panel_404s_for_a_virtual_course()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create(['type' => 'virtual']);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', $course));

        $response->assertNotFound();
    }

    public function test_the_panel_lists_completed_purchases_with_their_code()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create(['name' => 'Ana Estudiante']);
        $course = Course::factory()->create(['type' => 'presencial']);
        Purchase::create([
            'user_id' => $student->id, 'course_id' => $course->id, 'amount' => 10,
            'status' => 'completed', 'attendance_code' => 'ABCD1234',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', $course));

        $response->assertOk();
        $response->assertSee('Ana Estudiante');
        $response->assertSee('ABCD1234');
    }

    public function test_the_admin_can_manually_check_in_a_purchase()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create();
        $course = Course::factory()->create(['type' => 'presencial']);
        $purchase = Purchase::create([
            'user_id' => $student->id, 'course_id' => $course->id, 'amount' => 10,
            'status' => 'completed', 'attendance_code' => 'ABCD1234',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.attendance.check-in', [$course, $purchase]));

        $response->assertRedirect();
        $this->assertNotNull($purchase->fresh()->checked_in_at);
    }

    public function test_scanning_a_valid_code_checks_in_the_purchase()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create(['name' => 'Ana Estudiante']);
        $course = Course::factory()->create(['type' => 'presencial']);
        $purchase = Purchase::create([
            'user_id' => $student->id, 'course_id' => $course->id, 'amount' => 10,
            'status' => 'completed', 'attendance_code' => 'ABCD1234',
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.attendance.scan', $course), ['code' => 'abcd1234']);

        $response->assertOk();
        $response->assertJson(['ok' => true, 'already' => false, 'name' => 'Ana Estudiante']);
        $this->assertNotNull($purchase->fresh()->checked_in_at);
    }

    public function test_scanning_an_already_checked_in_code_reports_already()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create();
        $course = Course::factory()->create(['type' => 'presencial']);
        Purchase::create([
            'user_id' => $student->id, 'course_id' => $course->id, 'amount' => 10,
            'status' => 'completed', 'attendance_code' => 'ABCD1234', 'checked_in_at' => now(),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.attendance.scan', $course), ['code' => 'ABCD1234']);

        $response->assertOk();
        $response->assertJson(['ok' => true, 'already' => true]);
    }

    public function test_scanning_an_unknown_code_returns_not_found()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create(['type' => 'presencial']);

        $response = $this->actingAs($admin)->postJson(route('admin.attendance.scan', $course), ['code' => 'NOPE0000']);

        $response->assertNotFound();
        $response->assertJson(['ok' => false]);
    }

    public function test_a_code_from_another_course_is_not_found_when_scanning()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create();
        $courseA = Course::factory()->create(['type' => 'presencial']);
        $courseB = Course::factory()->create(['type' => 'presencial']);
        Purchase::create([
            'user_id' => $student->id, 'course_id' => $courseA->id, 'amount' => 10,
            'status' => 'completed', 'attendance_code' => 'ABCD1234',
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.attendance.scan', $courseB), ['code' => 'ABCD1234']);

        $response->assertNotFound();
    }
}
