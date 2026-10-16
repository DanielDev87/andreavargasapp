<?php

namespace Tests\Feature;

use App\Jobs\ProcessValidationRequest;
use App\Models\User;
use App\Models\ValidationRequest;
use App\Services\AIValidationService;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class DentistDocumentValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    /**
     * A report where every check passes with high confidence.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function report(array $overrides = []): array
    {
        return array_merge([
            'is_dental_degree' => true,
            'is_identity_document' => true,
            'documents_legible' => true,
            'holder_names_match' => true,
            'matches_account_name' => true,
            'tampering_signs' => false,
            'degree_holder_name' => 'Laura Gómez Ruiz',
            'id_holder_name' => 'Laura Gómez Ruiz',
            'id_document_type' => 'Cédula de ciudadanía',
            'id_document_number' => '1020304050',
            'degree_title' => 'Odontóloga',
            'institution' => 'Universidad de Antioquia',
            'graduation_date' => '2018-06-15',
            'issuing_country' => 'Colombia',
            'confidence' => 0.95,
            'recommendation' => 'approve',
            'observations' => 'Documentos coherentes y legibles.',
            'model' => 'claude-haiku-5-5',
        ], $overrides);
    }

    /**
     * Swap only the Claude call; the approval rules in shouldAutoApprove() stay real.
     *
     * @param  array<string, mixed>|null  $report
     */
    private function fakeAi(?array $report, bool $configured = true): void
    {
        $ai = $this->partialMock(AIValidationService::class);
        $ai->shouldReceive('isConfigured')->andReturn($configured);

        if ($report !== null) {
            $ai->shouldReceive('analyze')->andReturn($report);
        }
    }

    private function validationRequest(string $disk = 'local'): ValidationRequest
    {
        $user = User::factory()->create(['name' => 'Laura Gómez Ruiz', 'is_validated' => false]);

        Storage::disk($disk)->put('validations/diploma.pdf', 'diploma');
        Storage::disk($disk)->put('validations/id.jpg', 'id');

        return ValidationRequest::create([
            'user_id' => $user->id,
            'diploma_path' => 'validations/diploma.pdf',
            'id_document_path' => 'validations/id.jpg',
            'status' => 'pending',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function upload(array $overrides = []): array
    {
        return array_merge([
            'diploma' => UploadedFile::fake()->create('diploma.pdf', 200, 'application/pdf'),
            'id_document' => UploadedFile::fake()->image('cedula.jpg'),
            'review_method' => 'ai',
            'data_consent' => '1',
        ], $overrides);
    }

    public function test_uploaded_documents_are_stored_privately_and_queued_for_ai_review(): void
    {
        Queue::fake();
        $this->fakeAi(null);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('validation.store'), $this->upload())
            ->assertRedirect(route('profile'))
            ->assertSessionHas('status', __('app.validation_sent_ai'));

        $validationRequest = ValidationRequest::firstOrFail();

        Storage::disk('local')->assertExists([$validationRequest->diploma_path, $validationRequest->id_document_path]);
        Storage::disk('public')->assertMissing($validationRequest->diploma_path);
        $this->assertSame('ai', $validationRequest->review_method);
        $this->assertNotNull($validationRequest->data_consent_at);
        $this->assertSame(config('app.data_policy_version'), $validationRequest->data_policy_version);
        Queue::assertPushed(ProcessValidationRequest::class);
    }

    public function test_documents_are_not_accepted_without_the_data_processing_authorization(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('validation.store'), $this->upload(['data_consent' => null]))
            ->assertSessionHasErrors('data_consent');

        $this->assertSame(0, ValidationRequest::count());
        $this->assertEmpty(Storage::disk('local')->allFiles());
    }

    public function test_a_dentist_can_choose_manual_review_and_the_ai_is_not_used(): void
    {
        Queue::fake();
        $this->fakeAi(null);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('validation.store'), $this->upload(['review_method' => 'manual']))
            ->assertSessionHas('status', __('app.validation_sent_manual'));

        $this->assertSame('manual', ValidationRequest::firstOrFail()->review_method);
        Queue::assertNothingPushed();
    }

    public function test_choosing_ai_while_it_is_unavailable_falls_back_to_manual_review_with_a_notice(): void
    {
        Queue::fake();
        $this->fakeAi(null, configured: false);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('validation.store'), $this->upload())
            ->assertRedirect(route('profile'))
            ->assertSessionHas('status', __('app.validation_ai_unavailable'));

        $validationRequest = ValidationRequest::firstOrFail();
        $this->assertSame('manual', $validationRequest->review_method);
        $this->assertSame('pending', $validationRequest->status);
        Queue::assertNothingPushed();

        $this->actingAs($user)->get(route('profile'))->assertSee(__('app.profile_validation_pending_ai_unavailable'));
    }

    public function test_the_upload_still_succeeds_when_the_queue_is_down(): void
    {
        $this->fakeAi(null);
        $this->mock(Dispatcher::class)->shouldReceive('dispatch')->andThrow(new RuntimeException('queue down'));
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('validation.store'), $this->upload())
            ->assertRedirect(route('profile'))
            ->assertSessionHas('status', __('app.validation_ai_unavailable'));

        $this->assertSame('manual', ValidationRequest::firstOrFail()->review_method);
    }

    public function test_the_form_disables_ai_review_and_explains_why_when_it_is_unavailable(): void
    {
        $this->fakeAi(null, configured: false);

        $this->actingAs(User::factory()->create())->get(route('validation.create'))
            ->assertOk()
            ->assertSee(__('app.validation_ai_unavailable_notice'))
            ->assertSee(route('data-policy'));
    }

    public function test_the_data_policy_page_is_public_and_names_the_contact_email(): void
    {
        config(['app.data_protection_email' => 'datos@example.co']);

        $this->get(route('data-policy'))
            ->assertOk()
            ->assertSee('datos@example.co')
            ->assertSee('Anthropic');
    }

    public function test_requests_for_manual_review_are_never_sent_to_the_ai(): void
    {
        $ai = $this->partialMock(AIValidationService::class);
        $ai->shouldNotReceive('analyze');
        $validationRequest = $this->validationRequest();
        $validationRequest->update(['review_method' => 'manual']);

        ProcessValidationRequest::dispatchSync($validationRequest);

        $this->assertSame('pending', $validationRequest->fresh()->status);
    }

    public function test_a_clear_case_is_approved_automatically(): void
    {
        $this->fakeAi($this->report());
        $validationRequest = $this->validationRequest();

        ProcessValidationRequest::dispatchSync($validationRequest);

        $validationRequest->refresh();
        $this->assertSame('approved', $validationRequest->status);
        $this->assertSame(0.95, $validationRequest->ai_score);
        $this->assertSame('1020304050', $validationRequest->ai_report['id_document_number']);
        $this->assertTrue($validationRequest->user->is_validated);
    }

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function doubtfulReports(): array
    {
        return [
            'signs of tampering' => [['tampering_signs' => true]],
            'not a dental degree' => [['is_dental_degree' => false]],
            'names differ' => [['holder_names_match' => false]],
            'account name differs' => [['matches_account_name' => false]],
            'low confidence' => [['confidence' => 0.7]],
            'model asks for review' => [['recommendation' => 'manual_review']],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('doubtfulReports')]
    public function test_doubtful_cases_go_to_an_admin_and_are_never_rejected_automatically(array $overrides): void
    {
        $this->fakeAi($this->report($overrides));
        $validationRequest = $this->validationRequest();

        ProcessValidationRequest::dispatchSync($validationRequest);

        $validationRequest->refresh();
        $this->assertSame('pending', $validationRequest->status);
        $this->assertFalse($validationRequest->user->is_validated);
    }

    public function test_without_an_api_key_the_request_waits_for_manual_review(): void
    {
        $this->fakeAi(null, configured: false);
        $validationRequest = $this->validationRequest();

        ProcessValidationRequest::dispatchSync($validationRequest);

        $validationRequest->refresh();
        $this->assertSame('pending', $validationRequest->status);
        $this->assertSame(['error' => 'not_configured'], $validationRequest->ai_report);
        $this->assertSame('manual', $validationRequest->review_method);
    }

    public function test_a_failed_analysis_hands_the_request_to_an_admin(): void
    {
        $validationRequest = $this->validationRequest();
        $validationRequest->update(['status' => 'processing']);

        (new ProcessValidationRequest($validationRequest))->failed(new RuntimeException('API down'));

        $validationRequest->refresh();
        $this->assertSame('pending', $validationRequest->status);
        $this->assertSame(['error' => 'analysis_failed'], $validationRequest->ai_report);
        $this->assertSame('manual', $validationRequest->review_method);
    }

    public function test_a_decision_already_taken_by_an_admin_is_not_overwritten(): void
    {
        $this->fakeAi($this->report());
        $validationRequest = $this->validationRequest();
        $validationRequest->update(['status' => 'rejected']);

        ProcessValidationRequest::dispatchSync($validationRequest);

        $this->assertSame('rejected', $validationRequest->fresh()->status);
    }

    public function test_admin_approval_lets_the_dentist_buy_courses(): void
    {
        $validationRequest = $this->validationRequest();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.validations.review', $validationRequest), ['action' => 'approve'])
            ->assertRedirect();

        $this->assertSame('approved', $validationRequest->fresh()->status);
        $this->assertTrue($validationRequest->user->fresh()->is_validated);
    }

    public function test_only_admins_can_open_the_uploaded_documents(): void
    {
        $validationRequest = $this->validationRequest();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.validations.document', [$validationRequest, 'diploma']))->assertOk();
        $this->actingAs($validationRequest->user)->get(route('admin.validations.document', [$validationRequest, 'id']))->assertForbidden();
    }

    public function test_documents_uploaded_before_the_move_to_private_storage_still_open(): void
    {
        $validationRequest = $this->validationRequest(disk: 'public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.validations.document', [$validationRequest, 'id']))->assertOk();
    }

    public function test_admin_sees_the_ai_findings_and_a_rethus_lookup(): void
    {
        $validationRequest = $this->validationRequest();
        $validationRequest->update(['ai_report' => $this->report(['tampering_signs' => true, 'recommendation' => 'manual_review'])]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.validations.show', $validationRequest))
            ->assertOk()
            ->assertSee(__('app.admin_validation_ai_recommendation_manual_review'))
            ->assertSee('Universidad de Antioquia')
            ->assertSee(config('services.rethus_lookup_url'), escape: false)
            ->assertDontSee('storage/validations', escape: false);
    }
}
