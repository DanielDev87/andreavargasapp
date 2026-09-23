<?php

namespace Tests\Feature;

use App\Jobs\GenerateCertificate;
use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
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

    public function test_admin_can_upload_a_cover_image_when_creating_a_course()
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Curso con Imagen',
            'description' => 'Descripción',
            'price' => 99.99,
            'type' => 'virtual',
            'image' => UploadedFile::fake()->image('curso.jpg'),
        ]);

        $response->assertRedirect(route('admin.courses.index'));
        $course = Course::where('title', 'Curso con Imagen')->firstOrFail();
        $this->assertStringStartsWith('storage/courses/', $course->image_url);
        Storage::disk('public')->assertExists(substr($course->image_url, strlen('storage/')));
    }

    public function test_uploading_a_new_image_on_update_removes_the_previous_one()
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->create(['type' => 'virtual', 'image_url' => 'storage/courses/old.jpg']);
        Storage::disk('public')->put('courses/old.jpg', 'fake-content');

        $response = $this->actingAs($admin)->put(route('admin.courses.update', $course), [
            'title' => $course->title,
            'price' => $course->price,
            'type' => $course->type,
            'image' => UploadedFile::fake()->image('nuevo.jpg'),
        ]);

        $response->assertRedirect(route('admin.courses.index'));
        Storage::disk('public')->assertMissing('courses/old.jpg');
        $this->assertNotSame('storage/courses/old.jpg', $course->fresh()->image_url);
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

    public function test_approving_a_purchase_unlocks_access_without_issuing_a_certificate()
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $purchase = Purchase::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 10,
            'status' => 'pending',
            'payment_method' => 'transfer',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.purchases.approve', $purchase));

        $response->assertRedirect();
        $this->assertSame('completed', $purchase->fresh()->status);

        // The certificate is only earned by finishing every lesson in order, never at approval time.
        Queue::assertNotPushed(GenerateCertificate::class);
    }
}
