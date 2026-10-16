<?php

namespace Tests\Feature;

use App\Jobs\GenerateCertificate;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    private function certificate(): Certificate
    {
        $purchase = Purchase::factory()->create(['status' => 'completed']);
        (new GenerateCertificate($purchase))->handle();

        return Certificate::firstOrFail();
    }

    public function test_certificates_are_written_to_private_storage(): void
    {
        $certificate = $this->certificate();

        Storage::disk('local')->assertExists($certificate->certificate_path);
        Storage::disk('public')->assertMissing($certificate->certificate_path);
    }

    public function test_only_the_owner_or_an_admin_can_open_a_certificate(): void
    {
        $certificate = $this->certificate();

        $this->actingAs($certificate->user)->get(route('certificates.show', $certificate))->assertOk();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get(route('certificates.show', $certificate))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('certificates.show', $certificate))->assertForbidden();
    }

    public function test_the_profile_links_to_the_protected_certificate_route(): void
    {
        $certificate = $this->certificate();

        $this->actingAs($certificate->user)->get(route('profile'))
            ->assertOk()
            ->assertSee(route('certificates.show', $certificate))
            ->assertDontSee('storage/certificates', escape: false);
    }

    public function test_existing_public_documents_are_moved_to_private_storage(): void
    {
        Storage::disk('public')->put('certificates/certificate_7.pdf', 'pdf');
        Storage::disk('public')->put('validations/cedula.jpg', 'id');
        Storage::disk('public')->put('courses/portada.jpg', 'course image');

        $migration = require database_path('migrations/2026_10_16_192455_move_private_documents_off_the_public_disk.php');
        $migration->up();

        Storage::disk('local')->assertExists(['certificates/certificate_7.pdf', 'validations/cedula.jpg']);
        Storage::disk('public')->assertMissing(['certificates/certificate_7.pdf', 'validations/cedula.jpg']);
        // Course images are meant to be public and stay where they are
        Storage::disk('public')->assertExists('courses/portada.jpg');
    }

    public function test_a_certificate_not_yet_moved_can_still_be_opened(): void
    {
        $user = User::factory()->create();
        Storage::disk('public')->put('certificates/certificate_old.pdf', 'pdf');
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => Course::factory()->create()->id,
            'certificate_path' => 'certificates/certificate_old.pdf',
            'issued_at' => now(),
        ]);

        $this->actingAs($user)->get(route('certificates.show', $certificate))->assertOk();
    }

    public function test_mass_sign_ups_from_one_connection_are_throttled(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post('/register', [
                'name' => "Usuario {$attempt}",
                'email' => "usuario{$attempt}@example.com",
                'password' => 'clave-segura-123',
                'password_confirmation' => 'clave-segura-123',
            ]);
            auth()->logout();
        }

        $this->post('/register', [
            'name' => 'Usuario 6',
            'email' => 'usuario6@example.com',
            'password' => 'clave-segura-123',
            'password_confirmation' => 'clave-segura-123',
        ])->assertTooManyRequests();
    }

    public function test_password_reset_requests_are_throttled(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(route('password.email'), ['email' => "persona{$attempt}@example.com"]);
        }

        $this->post(route('password.email'), ['email' => 'persona6@example.com'])->assertTooManyRequests();
    }

    public function test_the_too_many_requests_page_is_in_spanish(): void
    {
        app()->setLocale('es');

        foreach (range(1, 6) as $attempt) {
            $response = $this->post(route('password.email'), ['email' => "persona{$attempt}@example.com"]);
        }

        $response->assertTooManyRequests()->assertSee(__('Too Many Requests'));
        $this->assertNotSame('Too Many Requests', __('Too Many Requests'));
    }

    public function test_pages_carry_browser_security_headers(): void
    {
        $this->get('/')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=()');
    }

    public function test_https_requests_get_strict_transport_security(): void
    {
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
    }
}
