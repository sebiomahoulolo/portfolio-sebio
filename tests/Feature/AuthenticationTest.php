<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/projets')->assertRedirect(route('login'));
    }

    public function test_there_is_no_public_registration(): void
    {
        $this->get('/register')->assertNotFound();
        $this->get('/admin/inscription')->assertNotFound();
    }

    public function test_owner_can_log_in_and_out(): void
    {
        $user = User::factory()->owner()->create(['password' => 'Secret-Password-2026']);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Secret-Password-2026')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('activity_logs', ['action' => 'auth.login', 'user_id' => $user->id]);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->owner()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'mauvais-mot-de-passe')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_log_in(): void
    {
        $user = User::factory()->owner()->inactive()->create(['password' => 'Secret-Password-2026']);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Secret-Password-2026')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        $user = User::factory()->owner()->create(['password' => 'Secret-Password-2026']);

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Login::class)->set('email', $user->email)->set('password', 'faux')->call('login');
        }

        // Même avec le bon mot de passe, la connexion reste bloquée.
        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Secret-Password-2026')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->editor()->create();
        $this->actingAs($user)->get('/admin')->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $this->actingAs($user)->get('/admin')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
