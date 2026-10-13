<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="lg">Nouveau mot de passe</flux:heading>
        <flux:text class="mt-1">12 caractères minimum, avec majuscules, minuscules et chiffres.</flux:text>
    </div>

    <form wire:submit="resetPassword" class="flex flex-col gap-5">
        <flux:input wire:model="email" label="Adresse email" type="email" required />
        <flux:input wire:model="password" label="Nouveau mot de passe" type="password" required autocomplete="new-password" viewable />
        <flux:input wire:model="password_confirmation" label="Confirmation" type="password" required autocomplete="new-password" viewable />
        <flux:button variant="primary" type="submit" class="w-full">Enregistrer</flux:button>
    </form>
</div>
