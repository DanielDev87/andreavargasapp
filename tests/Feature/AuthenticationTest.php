<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_regular_users_to_their_profile_not_the_admin_dashboard(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password'), 'is_admin' => false]);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertRedirect(route('profile'));
    }

    public function test_login_redirects_admins_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->create(['password' => bcrypt('password'), 'is_admin' => true]);

        $response = $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_regular_users_cannot_access_the_admin_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }
}
