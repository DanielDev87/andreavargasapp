<?php

namespace Tests\Feature;

use App\Jobs\GenerateCertificate;
use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use App\Models\ValidationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_initiate_purchase_requires_validation()
    {
        $user = User::factory()->create(['is_validated' => false]);
        $course = Course::factory()->create(['price' => 10]);

        $response = $this->actingAs($user)->post(route('purchases.initiate', $course));

        $response->assertRedirect(route('validation.create'));
    }

    public function test_webhook_paid_unlocks_course_access_without_issuing_a_certificate()
    {
        Queue::fake();

        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create(['price' => 10]);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 10,
            'status' => 'pending',
            'bold_payment_id' => 'test_123',
        ]);

        // Simulate webhook with simulate=1 so BoldService accepts it
        $resp = $this->postJson(route('webhooks.bold'), ['purchase_id' => $purchase->id, 'status' => 'paid', 'simulate' => true]);
        $resp->assertJson(['ok' => true]);

        $this->assertSame('completed', $purchase->fresh()->status);

        // The certificate is only earned by finishing every lesson in order, never at payment time.
        Queue::assertNotPushed(GenerateCertificate::class);
    }

    public function test_completing_a_presencial_purchase_increments_seats_taken()
    {
        Queue::fake();

        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create(['price' => 10, 'type' => 'presencial', 'capacity' => 5, 'seats_taken' => 0]);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 10,
            'status' => 'pending',
            'bold_payment_id' => 'test_123',
        ]);

        $this->postJson(route('webhooks.bold'), ['purchase_id' => $purchase->id, 'status' => 'paid', 'simulate' => true]);

        $this->assertSame(1, $course->fresh()->seats_taken);
    }

    public function test_a_retried_webhook_does_not_double_count_seats()
    {
        Queue::fake();

        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create(['price' => 10, 'type' => 'presencial', 'capacity' => 5, 'seats_taken' => 0]);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 10,
            'status' => 'pending',
            'bold_payment_id' => 'test_123',
        ]);

        // Bold retries notifications until it gets a 200 — the same "paid" event can arrive twice.
        $this->postJson(route('webhooks.bold'), ['purchase_id' => $purchase->id, 'status' => 'paid', 'simulate' => true]);
        $this->postJson(route('webhooks.bold'), ['purchase_id' => $purchase->id, 'status' => 'paid', 'simulate' => true]);

        $this->assertSame(1, $course->fresh()->seats_taken);
    }

    public function test_a_presencial_course_becomes_unbuyable_once_it_sells_out()
    {
        $buyer = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create(['price' => 10, 'type' => 'presencial', 'capacity' => 1, 'seats_taken' => 1]);
        ValidationRequest::create(['user_id' => $buyer->id, 'status' => 'approved', 'diploma_path' => 'diplomas/test.pdf', 'id_document_path' => 'ids/test.pdf']);

        $response = $this->actingAs($buyer)->post(route('purchases.initiate', $course), ['payment_method' => 'bold']);

        $response->assertRedirect(route('courses.show', $course));
        $this->assertDatabaseMissing('purchases', ['course_id' => $course->id, 'user_id' => $buyer->id]);
    }

    public function test_a_virtual_course_purchase_never_touches_seats_taken()
    {
        Queue::fake();

        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create(['price' => 10, 'type' => 'virtual']);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 10,
            'status' => 'pending',
            'bold_payment_id' => 'test_123',
        ]);

        $this->postJson(route('webhooks.bold'), ['purchase_id' => $purchase->id, 'status' => 'paid', 'simulate' => true]);

        $this->assertSame(0, $course->fresh()->seats_taken);
    }

    public function test_completing_a_presencial_purchase_generates_an_attendance_code()
    {
        Queue::fake();

        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create(['price' => 10, 'type' => 'presencial', 'capacity' => 5, 'seats_taken' => 0]);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 10,
            'status' => 'pending',
            'bold_payment_id' => 'test_123',
        ]);

        $this->postJson(route('webhooks.bold'), ['purchase_id' => $purchase->id, 'status' => 'paid', 'simulate' => true]);

        $this->assertNotNull($purchase->fresh()->attendance_code);
        $this->assertSame(8, strlen($purchase->fresh()->attendance_code));
    }

    public function test_a_virtual_course_purchase_never_gets_an_attendance_code()
    {
        Queue::fake();

        $user = User::factory()->create(['is_validated' => true]);
        $course = Course::factory()->create(['price' => 10, 'type' => 'virtual']);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 10,
            'status' => 'pending',
            'bold_payment_id' => 'test_123',
        ]);

        $this->postJson(route('webhooks.bold'), ['purchase_id' => $purchase->id, 'status' => 'paid', 'simulate' => true]);

        $this->assertNull($purchase->fresh()->attendance_code);
    }

    public function test_check_in_is_idempotent()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['type' => 'presencial']);
        $purchase = Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 10,
            'status' => 'completed',
            'attendance_code' => 'ABCD1234',
        ]);

        $this->assertTrue($purchase->checkIn());
        $firstCheckIn = $purchase->fresh()->checked_in_at;

        $this->assertFalse($purchase->checkIn());
        $this->assertEquals($firstCheckIn, $purchase->fresh()->checked_in_at);
    }
}
