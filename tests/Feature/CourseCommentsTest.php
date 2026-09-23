<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_without_the_course_cannot_post_a_comment()
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($student)->post(route('comments.store', $course), ['body' => 'Hola']);

        $response->assertForbidden();
    }

    public function test_a_student_who_purchased_the_course_can_post_a_comment()
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();
        Purchase::create(['user_id' => $student->id, 'course_id' => $course->id, 'amount' => 10, 'status' => 'completed']);

        $response = $this->actingAs($student)->post(route('comments.store', $course), ['body' => '¿Cuándo abren el siguiente módulo?']);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', ['course_id' => $course->id, 'user_id' => $student->id]);
    }

    public function test_the_admin_can_reply_to_a_students_comment_without_owning_the_course()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create();
        $course = Course::factory()->create();
        Purchase::create(['user_id' => $student->id, 'course_id' => $course->id, 'amount' => 10, 'status' => 'completed']);

        $question = Comment::create(['course_id' => $course->id, 'user_id' => $student->id, 'body' => '¿Tienen certificado?']);

        $response = $this->actingAs($admin)->post(route('comments.store', $course), [
            'body' => 'Sí, al completar el curso.',
            'parent_id' => $question->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'course_id' => $course->id,
            'user_id' => $admin->id,
            'parent_id' => $question->id,
        ]);
    }
}
