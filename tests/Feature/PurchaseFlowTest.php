<?php

namespace Tests\Feature;

use App\Jobs\GenerateCertificate;
use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
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
}
