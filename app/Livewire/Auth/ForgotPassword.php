<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Mot de passe oublié')]
class ForgotPassword extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate();

        $key = 'password-reset|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('email', 'Trop de demandes. Réessayez dans quelques minutes.');

            return;
        }

        RateLimiter::hit($key, 600);

        Password::sendResetLink(['email' => $this->email]);

        // Message identique que le compte existe ou non, pour ne pas
        // révéler quelles adresses sont enregistrées.
        session()->flash('status', 'Si un compte correspond à cette adresse, un lien de réinitialisation vient d\'être envoyé.');
        $this->reset('email');
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
