<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Création contrôlée du compte propriétaire : il n'existe pas de page
 * d'inscription publique.
 */
class CreateOwner extends Command
{
    protected $signature = 'portfolio:create-owner
        {--name= : Nom affiché}
        {--email= : Adresse email de connexion}';

    protected $description = "Crée le compte propriétaire de l'administration";

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nom');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->secret('Mot de passe (12 caractères minimum)');
        $confirmation = $this->secret('Confirmez le mot de passe');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User(compact('name', 'email', 'password'));
        $user->role = UserRole::Owner;
        $user->email_verified_at = now();
        $user->save();

        $this->info("Compte propriétaire créé pour {$email}. Connexion : /admin");

        return self::SUCCESS;
    }
}
