<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_hidden_course_does_not_appear_on_the_homepage()
    {
        Course::factory()->create(['title' => 'Curso Visible', 'is_hidden' => false]);
        Course::factory()->create(['title' => 'Curso Oculto', 'is_hidden' => true]);

        $response = $this->get('/');

        $response->assertSee('Curso Visible');
        $response->assertDontSee('Curso Oculto');
    }

    public function test_a_past_presencial_event_does_not_appear_on_the_homepage()
    {
        Course::factory()->create([
            'title' => 'Evento Futuro',
            'type' => 'presencial',
            'event_date' => now()->addWeek(),
        ]);
        Course::factory()->create([
            'title' => 'Evento Pasado',
            'type' => 'presencial',
            'event_date' => now()->subWeek(),
        ]);

        $response = $this->get('/');

        $response->assertSee('Evento Futuro');
        $response->assertDontSee('Evento Pasado');
    }

    public function test_a_hidden_course_404s_for_a_guest()
    {
        $course = Course::factory()->create(['is_hidden' => true]);

        $response = $this->get(route('courses.show', $course));

        $response->assertNotFound();
    }

    public function test_a_hidden_course_still_works_for_a_student_who_already_bought_it()
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['is_hidden' => true]);
        Purchase::create(['user_id' => $student->id, 'course_id' => $course->id, 'amount' => 10, 'status' => 'completed']);

        $response = $this->actingAs($student)->get(route('courses.show', $course));

        $response->assertOk();
    }

    public function test_a_hidden_course_still_works_for_the_admin()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create(['is_hidden' => true]);

        $response = $this->actingAs($admin)->get(route('courses.show', $course));

        $response->assertOk();
    }

    public function test_only_an_admin_can_toggle_course_visibility()
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['is_hidden' => false]);

        $response = $this->actingAs($student)->post(route('admin.courses.toggle-visibility', $course));

        $response->assertForbidden();
    }

    public function test_the_admin_can_toggle_course_visibility()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create(['is_hidden' => false]);

        $this->actingAs($admin)->post(route('admin.courses.toggle-visibility', $course));
        $this->assertTrue($course->fresh()->is_hidden);

        $this->actingAs($admin)->post(route('admin.courses.toggle-visibility', $course));
        $this->assertFalse($course->fresh()->is_hidden);
    }

    public function test_the_admin_courses_index_past_tab_only_lists_finished_presencial_events()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Course::factory()->create(['title' => 'Evento Pasado', 'type' => 'presencial', 'event_date' => now()->subWeek()]);
        Course::factory()->create(['title' => 'Evento Futuro', 'type' => 'presencial', 'event_date' => now()->addWeek()]);
        Course::factory()->create(['title' => 'Curso Virtual', 'type' => 'virtual']);

        $response = $this->actingAs($admin)->get(route('admin.courses.index', ['view' => 'past']));

        $response->assertSee('Evento Pasado');
        $response->assertDontSee('Evento Futuro');
        $response->assertDontSee('Curso Virtual');
    }
}
