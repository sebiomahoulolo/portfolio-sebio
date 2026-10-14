<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\TwoFactor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Seconde étape de connexion : code à 6 chiffres de l'application
 * d'authentification, ou l'un des codes de secours.
 */
#[Layout('components.layouts.auth')]
#[Title('Vérification en deux étapes')]
class TwoFactorChallenge extends Component
{
    public string $code = '';

    public bool $useRecoveryCode = false;

    public function mount(): void
    {
        if (! $this->pendingUser()) {
            $this->expire();
        }
    }

    public function toggleRecovery(): void
    {
        $this->useRecoveryCode = ! $this->useRecoveryCode;
        $this->reset('code');
        $this->resetValidation();
    }

    public function verify(TwoFactor $twoFactor): void
    {
        $this->validate(['code' => ['required', 'string', 'max:20']]);

        $user = $this->pendingUser();
        if (! $user) {
            $this->expire();

            return;
        }

        $key = 'two-factor:'.$user->getKey().'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($key).' secondes.',
            ]);
        }

        $valid = $this->useRecoveryCode
            ? $twoFactor->useRecoveryCode($user, $this->code)
            : $twoFactor->verify($user->two_factor_secret, $this->code, (string) $user->getKey());

        if (! $valid) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'code' => $this->useRecoveryCode
                    ? 'Ce code de secours est invalide ou a déjà été utilisé.'
                    : 'Code incorrect. Vérifiez l’heure de votre téléphone et saisissez le code affiché maintenant.',
            ]);
        }

        RateLimiter::clear($key);
        $remember = (bool) session('login.two_factor.remember');
        session()->forget('login.two_factor');

        Auth::login($user, $remember);
        session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();
        ActivityLogger::log('auth.login', 'Connexion à l\'administration (double authentification)', $user);

        if ($this->useRecoveryCode) {
            $left = count($user->two_factor_recovery_codes ?? []);
            ActivityLogger::log('auth.recovery_code_used', "Code de secours utilisé ({$left} restant(s))", $user);
            session()->flash('two_factor_warning', "Vous vous êtes connecté avec un code de secours. Il vous en reste {$left} : pensez à en générer de nouveaux depuis « Mon compte ».");
        }

        $this->redirectIntended(route('admin.dashboard'), navigate: true);
    }

    private function pendingUser(): ?User
    {
        $pending = session('login.two_factor');

        if (! $pending || ($pending['expires_at'] ?? 0) < now()->getTimestamp()) {
            return null;
        }

        $user = User::find($pending['id'] ?? null);

        return $user && $user->is_active && $user->hasTwoFactorEnabled() ? $user : null;
    }

    private function expire(): void
    {
        session()->forget('login.two_factor');
        session()->flash('status', 'Pour votre sécurité, la vérification a expiré. Reconnectez-vous.');
        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.two-factor-challenge');
    }
}
