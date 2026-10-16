<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use App\Models\ValidationRequest;
use App\Services\BoldService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as BoldRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BoldPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-secret-key';

    private const LINK_API = 'https://integrations.api.bold.co/online/link/v1';

    private function configureBold(bool $sandbox = false, string $apiKey = 'identity-key'): void
    {
        config([
            'services.bold.key' => $apiKey,
            'services.bold.webhook_secret' => self::SECRET,
            'services.bold.sandbox' => $sandbox,
        ]);

        $this->app->forgetInstance(BoldService::class);
    }

    private function validatedDentist(): User
    {
        $user = User::factory()->create(['is_validated' => true]);
        ValidationRequest::create([
            'user_id' => $user->id,
            'diploma_path' => 'validations/d.pdf',
            'id_document_path' => 'validations/i.pdf',
            'status' => 'approved',
        ]);

        return $user;
    }

    private function fakeLinkCreation(): void
    {
        Http::fake([
            self::LINK_API => Http::response([
                'payload' => ['payment_link' => 'LNK_TEST123', 'url' => 'https://checkout.bold.co/LNK_TEST123'],
                'errors' => [],
            ]),
        ]);
    }

    /**
     * A notification shaped like https://developers.bold.co/webhook.
     *
     * @param  array<string, mixed>  $data
     */
    private function boldEvent(string $type, array $data = []): string
    {
        return json_encode([
            'id' => 'b5f1d9d6-1111-2222-3333-444455556666',
            'type' => $type,
            'subject' => 'TX_9F8E7D',
            'source' => '/payments/links',
            'spec_version' => '1.0',
            'time' => 1760632000000000000,
            'data' => array_replace_recursive([
                'payment_id' => 'TX_9F8E7D',
                'merchant_id' => 'MERCHANT1',
                'amount' => ['currency' => 'COP', 'total' => 350000, 'taxes' => [], 'tip' => 0],
                'metadata' => ['reference' => 'LNK_TEST123'],
                'payment_method' => 'PSE',
                'integration' => 'LINK',
            ], $data),
            'datacontenttype' => 'application/json',
        ]);
    }

    private function sendWebhook(string $body, ?string $secret = self::SECRET): TestResponse
    {
        $signature = $secret === null ? 'not-a-signature' : hash_hmac('sha256', base64_encode($body), $secret);

        return $this->call('POST', route('webhooks.bold'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_BOLD_SIGNATURE' => $signature,
        ], $body);
    }

    private function pendingBoldPurchase(array $courseAttributes = []): Purchase
    {
        $course = Course::factory()->create(array_merge(['price' => 350000], $courseAttributes));

        return Purchase::create([
            'user_id' => User::factory()->create()->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'status' => 'pending',
            'payment_method' => 'bold',
            'bold_reference' => 'AV-1-20261016120000',
            'bold_payment_link' => 'LNK_TEST123',
            'bold_payment_url' => 'https://checkout.bold.co/LNK_TEST123',
        ]);
    }

    public function test_paying_with_bold_creates_a_payment_link_for_the_full_price(): void
    {
        $this->configureBold();
        $this->fakeLinkCreation();
        $course = Course::factory()->create(['price' => 350000, 'title' => str_repeat('Curso avanzado de carillas ', 6)]);

        $this->actingAs($this->validatedDentist())
            ->post(route('purchases.initiate', $course), ['payment_method' => 'bold'])
            ->assertRedirect('https://checkout.bold.co/LNK_TEST123');

        $purchase = Purchase::firstOrFail();
        $this->assertSame('LNK_TEST123', $purchase->bold_payment_link);
        $this->assertMatchesRegularExpression('/^AV-\d+-\d{14}$/', $purchase->bold_reference);

        Http::assertSent(function (BoldRequest $request) use ($purchase) {
            return $request->url() === self::LINK_API
                && $request->hasHeader('Authorization', 'x-api-key identity-key')
                && $request['amount_type'] === 'CLOSE'
                && $request['amount']['currency'] === 'COP'
                && $request['amount']['total_amount'] === 350000
                && $request['reference'] === $purchase->bold_reference
                && mb_strlen($request['description']) <= 100;
        });
    }

    /**
     * @param  array<string, mixed>  $linkStatus  Bold's response for GET /online/link/v1/{payment_link}
     */
    private function fakeLinkCreationAndStatus(array $linkStatus): void
    {
        Http::fake([
            self::LINK_API.'/LNK_TEST123' => Http::response(array_merge(['id' => 'LNK_TEST123', 'total' => 350000, 'transaction_id' => null, 'is_sandbox' => true], $linkStatus)),
            self::LINK_API => Http::sequence()
                ->push(['payload' => ['payment_link' => 'LNK_TEST123', 'url' => 'https://checkout.bold.co/LNK_TEST123'], 'errors' => []])
                ->push(['payload' => ['payment_link' => 'LNK_NEW456', 'url' => 'https://checkout.bold.co/LNK_NEW456'], 'errors' => []]),
        ]);
    }

    public function test_going_back_to_pay_reuses_a_link_that_can_still_be_paid(): void
    {
        $this->configureBold();
        $this->fakeLinkCreationAndStatus(['status' => 'ACTIVE']);
        $course = Course::factory()->create(['price' => 350000]);
        $dentist = $this->validatedDentist();

        $this->actingAs($dentist)->post(route('purchases.initiate', $course), ['payment_method' => 'bold']);
        $this->actingAs($dentist)->post(route('purchases.initiate', $course), ['payment_method' => 'bold'])
            ->assertRedirect('https://checkout.bold.co/LNK_TEST123');

        $this->assertSame(1, Purchase::count());
    }

    public function test_a_payment_in_progress_is_not_offered_a_second_link(): void
    {
        $this->configureBold();
        $this->fakeLinkCreationAndStatus(['status' => 'PROCESSING']);
        $course = Course::factory()->create(['price' => 350000]);
        $dentist = $this->validatedDentist();

        $this->actingAs($dentist)->post(route('purchases.initiate', $course), ['payment_method' => 'bold']);
        $purchase = Purchase::firstOrFail();

        $this->actingAs($dentist)->post(route('purchases.initiate', $course), ['payment_method' => 'bold'])
            ->assertRedirect(route('purchases.result', $purchase));

        $this->assertSame(1, Purchase::count());
    }

    public function test_an_expired_link_is_replaced_by_a_new_sale(): void
    {
        $this->configureBold();
        $this->fakeLinkCreationAndStatus(['status' => 'EXPIRED']);
        $course = Course::factory()->create(['price' => 350000]);
        $dentist = $this->validatedDentist();

        $this->actingAs($dentist)->post(route('purchases.initiate', $course), ['payment_method' => 'bold']);
        $this->actingAs($dentist)->post(route('purchases.initiate', $course), ['payment_method' => 'bold'])
            ->assertRedirect('https://checkout.bold.co/LNK_NEW456');

        $this->assertSame(['cancelled', 'pending'], Purchase::orderBy('id')->pluck('status')->all());
    }

    public function test_when_bold_is_down_the_buyer_gets_a_message_and_no_purchase_is_left_behind(): void
    {
        $this->configureBold();
        Http::fake([self::LINK_API => Http::response(['errors' => ['boom']], 500)]);
        $course = Course::factory()->create(['price' => 350000]);

        $this->actingAs($this->validatedDentist())
            ->from(route('purchases.create', $course))
            ->post(route('purchases.initiate', $course), ['payment_method' => 'bold'])
            ->assertRedirect(route('purchases.create', $course))
            ->assertSessionHas('warning', __('app.checkout_bold_unavailable'));

        $this->assertSame(0, Purchase::count());
    }

    public function test_an_approved_sale_unlocks_the_course(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase();

        $this->sendWebhook($this->boldEvent('SALE_APPROVED'))->assertOk();

        $purchase->refresh();
        $this->assertSame('completed', $purchase->status);
        $this->assertSame('TX_9F8E7D', $purchase->bold_payment_id);
    }

    public function test_a_retried_notification_is_acknowledged_without_reprocessing(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase(['type' => 'presencial', 'capacity' => 5, 'seats_taken' => 0]);

        $this->sendWebhook($this->boldEvent('SALE_APPROVED'))->assertOk();
        $this->sendWebhook($this->boldEvent('SALE_APPROVED'))->assertOk();

        $this->assertSame(1, $purchase->course->fresh()->seats_taken);
    }

    public function test_a_forged_signature_is_rejected(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase();

        $this->sendWebhook($this->boldEvent('SALE_APPROVED'), secret: null)->assertStatus(400);

        $this->assertSame('pending', $purchase->fresh()->status);
    }

    public function test_the_empty_key_test_signature_is_rejected_in_production_mode(): void
    {
        $this->configureBold(sandbox: false);
        $purchase = $this->pendingBoldPurchase();

        $this->sendWebhook($this->boldEvent('SALE_APPROVED'), secret: '')->assertStatus(400);

        $this->assertSame('pending', $purchase->fresh()->status);
    }

    public function test_the_empty_key_test_signature_is_accepted_in_sandbox_mode(): void
    {
        $this->configureBold(sandbox: true);
        $purchase = $this->pendingBoldPurchase();

        $this->sendWebhook($this->boldEvent('SALE_APPROVED'), secret: '')->assertOk();

        $this->assertSame('completed', $purchase->fresh()->status);
    }

    public function test_a_payment_for_a_different_amount_does_not_unlock_the_course(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase();

        $this->sendWebhook($this->boldEvent('SALE_APPROVED', ['amount' => ['total' => 1000]]))->assertOk();

        $this->assertSame('pending', $purchase->fresh()->status);
    }

    public function test_sales_from_outside_the_platform_are_acknowledged_and_ignored(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase();

        $this->sendWebhook($this->boldEvent('SALE_APPROVED', ['metadata' => ['reference' => 'POS-SALE-77']]))->assertOk();

        $this->assertSame('pending', $purchase->fresh()->status);
    }

    public function test_a_rejected_sale_keeps_the_purchase_pending_so_the_buyer_can_retry(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase();

        $this->sendWebhook($this->boldEvent('SALE_REJECTED'))->assertOk();

        $this->assertSame('pending', $purchase->fresh()->status);
    }

    public function test_a_voided_payment_revokes_access_and_frees_the_seat(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase(['type' => 'presencial', 'capacity' => 5, 'seats_taken' => 0]);

        $this->sendWebhook($this->boldEvent('SALE_APPROVED'))->assertOk();
        $this->sendWebhook($this->boldEvent('VOID_APPROVED'))->assertOk();

        $purchase->refresh();
        $this->assertSame('cancelled', $purchase->status);
        $this->assertNull($purchase->attendance_code);
        $this->assertSame(0, $purchase->course->fresh()->seats_taken);
    }

    public function test_the_simulated_payment_shortcut_is_closed_once_bold_keys_are_configured(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase();

        $this->get(route('webhooks.bold', ['simulate' => 1, 'status' => 'paid', 'purchase_id' => $purchase->id]))->assertForbidden();

        $this->assertSame('pending', $purchase->fresh()->status);
    }

    public function test_the_simulated_payment_shortcut_never_works_in_production(): void
    {
        $this->configureBold(apiKey: '');
        $this->app->detectEnvironment(fn () => 'production');
        $purchase = $this->pendingBoldPurchase();

        $this->get(route('webhooks.bold', ['simulate' => 1, 'status' => 'paid', 'purchase_id' => $purchase->id]))->assertForbidden();

        $this->assertSame('pending', $purchase->fresh()->status);
    }

    public function test_the_return_page_confirms_a_paid_link_when_the_webhook_is_late(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase();
        Http::fake([
            self::LINK_API.'/LNK_TEST123' => Http::response([
                'id' => 'LNK_TEST123', 'status' => 'PAID', 'total' => 350000, 'transaction_id' => 'TX_9F8E7D', 'is_sandbox' => false,
            ]),
        ]);

        $this->actingAs($purchase->user)
            ->get(route('purchases.result', $purchase))
            ->assertOk()
            ->assertSee(__('app.payment_result_paid_title'));

        $this->assertSame('completed', $purchase->fresh()->status);
    }

    public function test_the_return_page_keeps_checking_while_the_payment_is_processing(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase();
        Http::fake([self::LINK_API.'/LNK_TEST123' => Http::response(['id' => 'LNK_TEST123', 'status' => 'PROCESSING', 'total' => 350000])]);

        $this->actingAs($purchase->user)
            ->get(route('purchases.result', $purchase))
            ->assertOk()
            ->assertSee(__('app.payment_result_pending_title'))
            ->assertSee('http-equiv="refresh"', escape: false);
    }

    public function test_the_return_page_explains_a_rejected_payment(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase();
        Http::fake([self::LINK_API.'/LNK_TEST123' => Http::response(['id' => 'LNK_TEST123', 'status' => 'ACTIVE', 'total' => 350000])]);

        $this->actingAs($purchase->user)
            ->get(route('purchases.result', ['purchase' => $purchase, 'bold-tx-status' => 'rejected']))
            ->assertOk()
            ->assertSee(__('app.payment_result_rejected_title'))
            ->assertDontSee('http-equiv="refresh"', escape: false);
    }

    public function test_buyers_cannot_see_someone_elses_payment_result(): void
    {
        $this->configureBold();
        $purchase = $this->pendingBoldPurchase();

        $this->actingAs(User::factory()->create())
            ->get(route('purchases.result', $purchase))
            ->assertForbidden();
    }
}
