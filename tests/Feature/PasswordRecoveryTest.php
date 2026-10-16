<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('es');
    }

    public function test_the_login_page_links_to_password_recovery(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'))
            ->assertSee(__('app.login_forgot_password'));
    }

    public function test_a_registered_user_receives_a_reset_email_in_spanish(): void
    {
        Notification::fake();
        $user = User::factory()->create(['name' => 'Laura Gómez']);

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', __('passwords.sent'));

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $mail = $notification->toMail($user);

            return $mail->subject === __('app.reset_email_subject')
                && $mail->greeting === __('app.reset_email_greeting', ['name' => 'Laura Gómez'])
                && $mail->actionText === __('app.reset_email_action')
                && str_contains($mail->actionUrl, route('password.reset', ['token' => $notification->token]));
        });
    }

    public function test_an_unknown_email_gets_the_same_answer_so_accounts_cannot_be_discovered(): void
    {
        Notification::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'nadie@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', __('passwords.sent'));

        Notification::assertNothingSent();
    }

    public function test_the_user_can_set_a_new_password_and_sees_the_confirmation_on_the_login_page(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => $user->email]);

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nueva-clave-segura',
            'password_confirmation' => 'nueva-clave-segura',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('nueva-clave-segura', $user->fresh()->password));
        $this->get(route('login'))->assertSee(__('passwords.reset'));
    }

    public function test_an_invalid_or_expired_link_is_explained(): void
    {
        $user = User::factory()->create();

        $this->from(route('password.reset', ['token' => 'falso']))
            ->post(route('password.update'), [
                'token' => 'falso',
                'email' => $user->email,
                'password' => 'nueva-clave-segura',
                'password_confirmation' => 'nueva-clave-segura',
            ])
            ->assertSessionHasErrors(['email' => __('passwords.token')]);
    }
}
