<?php

namespace Tests\Feature;

use App\Livewire\Admin\Account;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\TwoFactorChallenge;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function userWithTwoFactor(): array
    {
        $secret = (new TwoFactor)->generateSecret();
        $user = User::factory()->owner()->create();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['aaaaa-bbbbb', 'ccccc-ddddd'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        return [$user, $secret];
    }

    private function currentCode(string $secret): string
    {
        return (new Google2FA)->getCurrentOtp($secret);
    }

    public function test_owner_can_enable_two_factor_with_a_valid_code(): void
    {
        $user = User::factory()->owner()->create();
        $this->actingAs($user);

        $component = Livewire::test(Account::class)->call('startTwoFactor')->assertSee('Confirmer et activer');
        $secret = session('two_factor.pending_secret');

        $component->set('two_factor_code', '000000')->call('confirmTwoFactor')->assertHasErrors('two_factor_code');
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());

        $component->set('two_factor_code', $this->currentCode($secret))->call('confirmTwoFactor')->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertCount(8, $user->two_factor_recovery_codes);
        $this->assertCount(8, $component->get('recoveryCodes'));
        // Le secret est chiffré en base.
        $this->assertNotSame($secret, $user->getRawOriginal('two_factor_secret'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'auth.two_factor_enabled']);
    }

    public function test_password_alone_no_longer_logs_in(): void
    {
        [$user] = $this->userWithTwoFactor();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_valid_code_completes_login(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();
        session()->put('login.two_factor', ['id' => $user->id, 'remember' => false, 'expires_at' => now()->addMinutes(5)->getTimestamp()]);

        Livewire::test(TwoFactorChallenge::class)
            ->set('code', '123456')
            ->call('verify')
            ->assertHasErrors('code');
        $this->assertGuest();

        Livewire::test(TwoFactorChallenge::class)
            ->set('code', $this->currentCode($secret))
            ->call('verify')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_code_cannot_be_reused(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();
        $twoFactor = new TwoFactor;
        $code = $this->currentCode($secret);

        $this->assertTrue($twoFactor->verify($secret, $code, (string) $user->id));
        $this->assertFalse($twoFactor->verify($secret, $code, (string) $user->id));
    }

    public function test_recovery_code_works_once(): void
    {
        [$user] = $this->userWithTwoFactor();
        $pending = ['id' => $user->id, 'remember' => false, 'expires_at' => now()->addMinutes(5)->getTimestamp()];

        session()->put('login.two_factor', $pending);
        Livewire::test(TwoFactorChallenge::class)
            ->call('toggleRecovery')
            ->set('code', 'AAAAA-BBBBB')
            ->call('verify')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(['ccccc-ddddd'], $user->fresh()->two_factor_recovery_codes);

        auth()->logout();
        session()->put('login.two_factor', $pending);
        Livewire::test(TwoFactorChallenge::class)
            ->call('toggleRecovery')
            ->set('code', 'aaaaa-bbbbb')
            ->call('verify')
            ->assertHasErrors('code');
        $this->assertGuest();
    }

    public function test_challenge_is_rate_limited(): void
    {
        [$user] = $this->userWithTwoFactor();
        session()->put('login.two_factor', ['id' => $user->id, 'remember' => false, 'expires_at' => now()->addMinutes(5)->getTimestamp()]);

        $component = Livewire::test(TwoFactorChallenge::class);
        foreach (range(1, 5) as $i) {
            $component->set('code', '000000')->call('verify');
        }

        $component->set('code', '000000')->call('verify')->assertHasErrors('code');
        $this->assertStringContainsString('Trop de tentatives', $component->errors()->first('code'));
    }

    public function test_expired_challenge_sends_back_to_login(): void
    {
        [$user] = $this->userWithTwoFactor();
        session()->put('login.two_factor', ['id' => $user->id, 'remember' => false, 'expires_at' => now()->subMinute()->getTimestamp()]);

        Livewire::test(TwoFactorChallenge::class)->assertRedirect(route('login'));
        $this->get(route('two-factor.challenge'))->assertRedirect(route('login'));
    }

    public function test_disabling_requires_the_password(): void
    {
        [$user] = $this->userWithTwoFactor();
        $this->actingAs($user);

        Livewire::test(Account::class)
            ->set('two_factor_password', 'wrong-password')
            ->call('disableTwoFactor')
            ->assertHasErrors('two_factor_password');
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        Livewire::test(Account::class)
            ->set('two_factor_password', 'password')
            ->call('disableTwoFactor')
            ->assertHasNoErrors();
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_dashboard_reminds_to_enable_two_factor(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get('/admin')
            ->assertSee('Protégez votre compte avec la double authentification');

        [$user] = $this->userWithTwoFactor();
        $this->actingAs($user)->get('/admin')->assertDontSee('Protégez votre compte avec la double authentification');
    }
}
