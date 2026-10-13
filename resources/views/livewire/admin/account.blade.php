<div class="flex max-w-2xl flex-col gap-8">
    <x-admin.page-header title="Mon compte" description="Vos informations de connexion et la sécurité de votre compte." />

    <flux:card>
        <form wire:submit="updateProfile" class="flex flex-col gap-5">
            <flux:heading>Informations</flux:heading>
            <flux:input wire:model="name" label="Nom" required />
            <flux:input wire:model="email" label="Adresse email" type="email" required />
            <div><flux:button variant="primary" type="submit">Enregistrer</flux:button></div>
        </form>
    </flux:card>

    <flux:card>
        <form wire:submit="updatePassword" class="flex flex-col gap-5">
            <flux:heading>Mot de passe</flux:heading>
            <flux:input wire:model="current_password" label="Mot de passe actuel" type="password" required autocomplete="current-password" />
            <flux:input wire:model="password" label="Nouveau mot de passe" type="password" required autocomplete="new-password" viewable
                description="12 caractères minimum, avec majuscules, minuscules et chiffres." />
            <flux:input wire:model="password_confirmation" label="Confirmation" type="password" required autocomplete="new-password" />
            <div><flux:button variant="primary" type="submit">Modifier le mot de passe</flux:button></div>
        </form>
    </flux:card>
</div>
