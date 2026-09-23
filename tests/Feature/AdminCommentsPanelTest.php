<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommentsPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_non_admin_cannot_view_the_comments_panel()
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('admin.comments.index'));

        $response->assertForbidden();
    }

    public function test_the_panel_lists_comments_and_flags_unanswered_ones()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create();
        $course = Course::factory()->create();
        Purchase::create(['user_id' => $student->id, 'course_id' => $course->id, 'amount' => 10, 'status' => 'completed']);

        $question = Comment::create(['course_id' => $course->id, 'user_id' => $student->id, 'body' => '¿Incluye certificado?']);

        $response = $this->actingAs($admin)->get(route('admin.comments.index'));

        $response->assertOk();
        $response->assertSee('¿Incluye certificado?');
        $response->assertSee(__('app.admin_comments_pending_badge'));
    }

    public function test_the_admin_can_reply_from_the_panel()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create();
        $course = Course::factory()->create();
        Purchase::create(['user_id' => $student->id, 'course_id' => $course->id, 'amount' => 10, 'status' => 'completed']);

        $question = Comment::create(['course_id' => $course->id, 'user_id' => $student->id, 'body' => '¿Incluye certificado?']);

        $response = $this->actingAs($admin)->post(route('admin.comments.reply', $question), [
            'body' => 'Sí, al finalizar el curso.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'course_id' => $course->id,
            'user_id' => $admin->id,
            'parent_id' => $question->id,
            'body' => 'Sí, al finalizar el curso.',
        ]);
    }
}
