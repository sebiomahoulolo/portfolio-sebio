<?php

namespace App\Livewire\Admin;

use App\Support\ActivityLogger;
use App\Support\TwoFactor;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Mon compte')]
class Account extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** Double authentification : code saisi pour confirmer l'activation. */
    public string $two_factor_code = '';

    /** Mot de passe demandé pour désactiver ou régénérer les codes de secours. */
    public string $two_factor_password = '';

    /** Codes de secours affichés une seule fois, juste après leur création. */
    public array $recoveryCodes = [];

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->fill($validated)->save();

        Flux::toast('Informations enregistrées.', variant: 'success');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        Auth::user()->forceFill(['password' => $this->password])->save();

        // Déconnecte les autres appareils qui utilisaient l'ancien mot de passe.
        Auth::logoutOtherDevices($this->password);

        ActivityLogger::log('auth.password_changed', 'Mot de passe modifié', Auth::user());
        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast('Mot de passe modifié. Les autres sessions ont été fermées.', variant: 'success');
    }

    /* ---------- Double authentification ---------- */

    // Le secret en cours d'activation reste côté serveur (session), jamais dans la page.
    private const PENDING_SECRET = 'two_factor.pending_secret';

    public function startTwoFactor(TwoFactor $twoFactor): void
    {
        abort_if(Auth::user()->hasTwoFactorEnabled(), 403);

        session()->put(self::PENDING_SECRET, $twoFactor->generateSecret());
        $this->reset('two_factor_code', 'recoveryCodes');
        $this->resetValidation();
    }

    public function cancelTwoFactor(): void
    {
        session()->forget(self::PENDING_SECRET);
        $this->reset('two_factor_code');
        $this->resetValidation();
    }

    public function confirmTwoFactor(TwoFactor $twoFactor): void
    {
        $user = Auth::user();
        $secret = session(self::PENDING_SECRET);

        if (! $secret || $user->hasTwoFactorEnabled()) {
            return;
        }

        $this->validate(['two_factor_code' => ['required', 'string']], [], ['two_factor_code' => 'code']);

        if (! $twoFactor->verify($secret, $this->two_factor_code, (string) $user->getKey())) {
            $this->addError('two_factor_code', 'Code incorrect. Saisissez le code actuellement affiché dans l’application.');

            return;
        }

        $this->recoveryCodes = $twoFactor->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $this->recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        session()->forget(self::PENDING_SECRET);
        $this->reset('two_factor_code');

        ActivityLogger::log('auth.two_factor_enabled', 'Double authentification activée', $user);
        Flux::toast('Double authentification activée. Conservez vos codes de secours.', variant: 'success');
    }

    public function regenerateRecoveryCodes(TwoFactor $twoFactor): void
    {
        $this->validate(['two_factor_password' => ['required', 'current_password']], [], ['two_factor_password' => 'mot de passe']);

        $this->recoveryCodes = $twoFactor->generateRecoveryCodes();
        Auth::user()->forceFill(['two_factor_recovery_codes' => $this->recoveryCodes])->save();
        $this->reset('two_factor_password');

        ActivityLogger::log('auth.recovery_codes_regenerated', 'Nouveaux codes de secours générés', Auth::user());
        Flux::toast('Nouveaux codes de secours générés. Les anciens ne fonctionnent plus.', variant: 'success');
    }

    public function disableTwoFactor(): void
    {
        $this->validate(['two_factor_password' => ['required', 'current_password']], [], ['two_factor_password' => 'mot de passe']);

        Auth::user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        $this->reset('two_factor_password', 'recoveryCodes');

        ActivityLogger::log('auth.two_factor_disabled', 'Double authentification désactivée', Auth::user());
        Flux::toast('Double authentification désactivée.', variant: 'warning');
    }

    public function render(TwoFactor $twoFactor)
    {
        $pending = session(self::PENDING_SECRET);

        return view('livewire.admin.account', [
            'twoFactorEnabled' => Auth::user()->hasTwoFactorEnabled(),
            'pendingSecret' => $pending,
            'qrCode' => $pending ? $twoFactor->qrCodeSvg(Auth::user(), $pending) : null,
            'recoveryCodesLeft' => count(Auth::user()->two_factor_recovery_codes ?? []),
        ]);
    }
}
