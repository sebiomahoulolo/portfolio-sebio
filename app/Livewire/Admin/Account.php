<?php

namespace App\Livewire\Admin;

use App\Support\ActivityLogger;
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

    public function render()
    {
        return view('livewire.admin.account');
    }
}
