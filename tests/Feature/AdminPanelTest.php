<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_course()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Curso Test',
            'description' => 'Descripción',
            'price' => 99.99,
            'type' => 'virtual',
        ]);

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseHas('courses', ['title' => 'Curso Test']);
    }

    public function test_presencial_course_requires_an_event_date()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Arte & Naturalidad',
            'description' => 'Descripción',
            'price' => 3500000,
            'type' => 'presencial',
        ]);

        $response->assertSessionHasErrors('event_date');
    }

    public function test_a_presencial_course_reports_sold_out_when_no_seats_remain()
    {
        $soldOut = Course::factory()->create([
            'type' => 'presencial',
            'capacity' => 15,
            'seats_taken' => 15,
        ]);

        $available = Course::factory()->create([
            'type' => 'presencial',
            'capacity' => 15,
            'seats_taken' => 6,
        ]);

        $this->assertTrue($soldOut->isSoldOut());
        $this->assertSame(0, $soldOut->seatsAvailable());

        $this->assertFalse($available->isSoldOut());
        $this->assertSame(9, $available->seatsAvailable());
    }
}
